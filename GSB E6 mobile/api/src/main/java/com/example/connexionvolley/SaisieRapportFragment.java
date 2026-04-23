package com.example.connexionvolley;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class SaisieRapportFragment extends Fragment {

    // URL de l'API compte-rendu
    String urlCr = "https://portfoliokball.fr/portofolio/gsbcompterendu/crapi.php";

    RequestQueue queue;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_saisie_rapport, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        DrawerActivity activity = (DrawerActivity) getActivity();
        queue = Volley.newRequestQueue(getContext());

        // --- spinner praticien avec IDs correspondant à la BDD ---
        // ordre : affichage / id en BDD
        Spinner spPraticien = view.findViewById(R.id.spinnerPraticien);
        String[] nomsPraticiens = {"Sélectionnez", "Delahaye Didier", "Gosselin Hélène", "Nahdel Jean", "Notini Alain"};
        int[]    idsPraticiens  = {0,               4,                 3,                 1,             2};
        ArrayAdapter<String> adapterPrat = new ArrayAdapter<>(getContext(), android.R.layout.simple_spinner_item, nomsPraticiens);
        adapterPrat.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spPraticien.setAdapter(adapterPrat);

        // --- spinner motif ---
        Spinner spMotif = view.findViewById(R.id.spinnerMotif);
        String[] motifs = {"Sélectionnez un motif", "periodicite", "remontage"};
        ArrayAdapter<String> adapterMotif = new ArrayAdapter<>(getContext(), android.R.layout.simple_spinner_item, motifs);
        adapterMotif.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spMotif.setAdapter(adapterMotif);

        // --- champs de saisie ---
        EditText etDate      = view.findViewById(R.id.etDateVisite);
        EditText etBilan     = view.findViewById(R.id.etBilan);
        EditText etRemplacant = view.findViewById(R.id.etRemplacant);

        // --- échantillons (id produit 1=Doliprane, 2=Lysopaïne, 3=Smecta) ---
        EditText etEch1 = view.findViewById(R.id.etEch1);
        EditText etEch2 = view.findViewById(R.id.etEch2);
        EditText etEch3 = view.findViewById(R.id.etEch3);

        Button btnValider = view.findViewById(R.id.btnValiderSaisie);

        btnValider.setOnClickListener(v -> {

            // vérifications
            int indexPrat = spPraticien.getSelectedItemPosition();
            if (indexPrat == 0) {
                Toast.makeText(getContext(), "Choisissez un praticien", Toast.LENGTH_SHORT).show();
                return;
            }
            String dateVisite = etDate.getText().toString().trim();
            if (dateVisite.isEmpty()) {
                Toast.makeText(getContext(), "Entrez la date de la visite", Toast.LENGTH_SHORT).show();
                return;
            }

            int    idPraticien = idsPraticiens[indexPrat];
            String motif       = spMotif.getSelectedItem().toString();
            String bilan       = etBilan.getText().toString().trim();
            String remplacant  = etRemplacant.getText().toString().trim();

            // conversion date jj/mm/aaaa → aaaa-mm-jj pour MySQL
            String dateMysql = convertirDate(dateVisite);

            // quantités échantillons
            String ech1 = etEch1.getText().toString().trim().isEmpty() ? "0" : etEch1.getText().toString().trim();
            String ech2 = etEch2.getText().toString().trim().isEmpty() ? "0" : etEch2.getText().toString().trim();
            String ech3 = etEch3.getText().toString().trim().isEmpty() ? "0" : etEch3.getText().toString().trim();

            // envoi de la requête POST à crapi.php
            StringRequest requete = new StringRequest(Request.Method.POST, urlCr,
                    response -> {
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");
                            if (status == 200) {
                                Toast.makeText(getContext(), "Compte-rendu enregistré !", Toast.LENGTH_SHORT).show();
                                NavHostFragment.findNavController(this).navigate(R.id.accueilFragment);
                            } else {
                                String msg = json.optString("message", "Erreur lors de l'enregistrement");
                                Toast.makeText(getContext(), msg, Toast.LENGTH_LONG).show();
                            }
                        } catch (JSONException e) {
                            Toast.makeText(getContext(), "Erreur de réponse serveur", Toast.LENGTH_LONG).show();
                        }
                    },
                    error -> {
                        if (error.networkResponse != null) {
                            Toast.makeText(getContext(), "Erreur serveur HTTP " + error.networkResponse.statusCode, Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(getContext(), "Fichier crapi.php introuvable ou pas de connexion", Toast.LENGTH_LONG).show();
                        }
                    }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    params.put("action", "ajouter");
                    params.put("id_visiteur",  String.valueOf(activity.userId));
                    params.put("id_praticien", String.valueOf(idPraticien));
                    params.put("date_visite",  dateMysql);
                    params.put("motif",        motif);
                    params.put("bilan",        bilan);
                    params.put("id_remplacant", remplacant);
                    params.put("ech_1", ech1); // Doliprane
                    params.put("ech_2", ech2); // Lysopaïne
                    params.put("ech_3", ech3); // Smecta
                    return params;
                }
            };

            queue.add(requete);
        });
    }

    // convertit "jj/mm/aaaa" en "aaaa-mm-jj" pour MySQL
    private String convertirDate(String date) {
        // si déjà au bon format ou vide, on renvoie tel quel
        if (date.length() != 10) {
            return date;
        }
        if (date.contains("/")) {
            // format jj/mm/aaaa
            String[] parts = date.split("/");
            if (parts.length == 3) {
                return parts[2] + "-" + parts[1] + "-" + parts[0];
            }
        }
        return date;
    }
}
