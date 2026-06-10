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
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class SaisieRapportFragment extends Fragment {

    String urlCr       = "https://portfoliokball.fr/portofolio/gsbcompterendu/crapi.php";
    String urlProduits = "https://portfoliokball.fr/portofolio/gsbcompterendu/produitsapi.php";

    RequestQueue queue;

    // liste des produits chargés depuis l'API
    List<String>  nomsProduits = new ArrayList<>();
    List<Integer> idsProduits  = new ArrayList<>();

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_saisie_rapport, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        DrawerActivity activity = (DrawerActivity) getActivity();
        queue = Volley.newRequestQueue(requireContext());

        // ── Spinner praticien ──
        Spinner spPraticien = view.findViewById(R.id.spinnerPraticien);
        String[] nomsPraticiens = {"Sélectionnez", "Delahaye Didier", "Gosselin Hélène", "Nahdel Jean", "Notini Alain"};
        int[]    idsPraticiens  = {0, 4, 3, 1, 2};
        ArrayAdapter<String> adapterPrat = new ArrayAdapter<>(requireContext(),
                android.R.layout.simple_spinner_item, nomsPraticiens);
        adapterPrat.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spPraticien.setAdapter(adapterPrat);

        // ── Spinner motif ──
        Spinner spMotif = view.findViewById(R.id.spinnerMotif);
        String[] motifs = {"Sélectionnez un motif", "periodicite", "remontage"};
        ArrayAdapter<String> adapterMotif = new ArrayAdapter<>(requireContext(),
                android.R.layout.simple_spinner_item, motifs);
        adapterMotif.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spMotif.setAdapter(adapterMotif);

        // ── Spinners produits (remplis après chargement API) ──
        Spinner  spProduit1 = view.findViewById(R.id.spinnerProduit1);
        Spinner  spProduit2 = view.findViewById(R.id.spinnerProduit2);
        EditText etQte1     = view.findViewById(R.id.etQteProduit1);
        EditText etQte2     = view.findViewById(R.id.etQteProduit2);

        // ── Autres champs ──
        EditText etDate      = view.findViewById(R.id.etDateVisite);
        EditText etBilan     = view.findViewById(R.id.etBilan);
        EditText etRemplacant = view.findViewById(R.id.etRemplacant);
        Button   btnValider  = view.findViewById(R.id.btnValiderSaisie);

        // ── Charger les produits depuis l'API ──
        chargerProduits(spProduit1, spProduit2);

        // ── Bouton valider ──
        btnValider.setOnClickListener(v -> {

            int indexPrat = spPraticien.getSelectedItemPosition();
            if (indexPrat == 0) { showToast("Choisissez un praticien"); return; }

            String dateVisite = etDate.getText().toString().trim();
            if (dateVisite.isEmpty()) { showToast("Entrez la date de la visite"); return; }

            int    idPraticien = idsPraticiens[indexPrat];
            String motif       = spMotif.getSelectedItem().toString();
            String bilan       = etBilan.getText().toString().trim();
            String remplacant  = etRemplacant.getText().toString().trim();
            String dateMysql   = convertirDate(dateVisite);

            // Construire la map des échantillons sélectionnés
            final Map<Integer, Integer> echantillons = new HashMap<>();

            int indexP1 = spProduit1.getSelectedItemPosition();
            int indexP2 = spProduit2.getSelectedItemPosition();

            // position 0 = "— Aucun —", donc on décale de 1
            if (!idsProduits.isEmpty() && indexP1 > 0) {
                String qteStr = etQte1.getText().toString().trim();
                int qte = qteStr.isEmpty() ? 0 : Integer.parseInt(qteStr);
                if (qte > 0) echantillons.put(idsProduits.get(indexP1 - 1), qte);
            }
            if (!idsProduits.isEmpty() && indexP2 > 0) {
                String qteStr = etQte2.getText().toString().trim();
                int qte = qteStr.isEmpty() ? 0 : Integer.parseInt(qteStr);
                if (qte > 0) echantillons.put(idsProduits.get(indexP2 - 1), qte);
            }

            // Envoi du compte-rendu
            StringRequest requete = new StringRequest(Request.Method.POST, urlCr,
                    response -> {
                        if (!isAdded()) return;
                        try {
                            JSONObject json = new JSONObject(response);
                            if (json.getInt("status") == 200) {
                                showToast("Compte-rendu enregistré !");
                                NavHostFragment.findNavController(this).navigate(R.id.accueilFragment);
                            } else {
                                showToast(json.optString("message", "Erreur lors de l'enregistrement"));
                            }
                        } catch (JSONException e) {
                            showToast("Erreur de réponse serveur");
                        }
                    },
                    error -> {
                        if (!isAdded()) return;
                        if (error.networkResponse != null) {
                            showToast("Erreur HTTP " + error.networkResponse.statusCode);
                        } else {
                            showToast("Pas de connexion au serveur");
                        }
                    }) {
                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    params.put("action",        "ajouter");
                    params.put("id_visiteur",   String.valueOf(activity.userId));
                    params.put("id_praticien",  String.valueOf(idPraticien));
                    params.put("date_visite",   dateMysql);
                    params.put("motif",         motif);
                    params.put("bilan",         bilan);
                    params.put("id_remplacant", remplacant);
                    // envoyer chaque échantillon sélectionné : ech_<id_produit> = quantité
                    for (Map.Entry<Integer, Integer> e : echantillons.entrySet()) {
                        params.put("ech_" + e.getKey(), String.valueOf(e.getValue()));
                    }
                    return params;
                }
            };

            queue.add(requete);
        });
    }

    // charge les produits et remplit sp1 + sp2
    private void chargerProduits(Spinner sp1, Spinner sp2) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlProduits,
                response -> {
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            JSONArray liste = json.getJSONArray("produits");

                            nomsProduits.clear();
                            idsProduits.clear();
                            nomsProduits.add("— Aucun —"); // position 0 = pas de produit

                            for (int i = 0; i < liste.length(); i++) {
                                JSONObject p = liste.getJSONObject(i);
                                nomsProduits.add(p.optString("nom", "Produit"));
                                idsProduits.add(p.optInt("id", i + 1));
                            }

                            ArrayAdapter<String> adapter = new ArrayAdapter<>(requireContext(),
                                    android.R.layout.simple_spinner_item, nomsProduits);
                            adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
                            sp1.setAdapter(adapter);
                            sp2.setAdapter(adapter);
                        }
                    } catch (JSONException e) {
                        showToast("Erreur chargement produits");
                    }
                },
                error -> {
                    if (!isAdded()) return;
                    showToast("Impossible de charger les produits");
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "liste");
                return params;
            }
        };
        queue.add(requete);
    }

    private void showToast(String msg) {
        if (isAdded() && getContext() != null) {
            Toast.makeText(getContext(), msg, Toast.LENGTH_SHORT).show();
        }
    }

    // convertit "jj/mm/aaaa" en "aaaa-mm-jj" pour MySQL
    private String convertirDate(String date) {
        if (date.length() == 10 && date.contains("/")) {
            String[] parts = date.split("/");
            if (parts.length == 3) return parts[2] + "-" + parts[1] + "-" + parts[0];
        }
        return date;
    }
}
