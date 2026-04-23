package com.example.connexionvolley;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
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

public class AjouterProduitFragment extends Fragment {

    // URL de l'API produits (même serveur que le login)
    String urlProduits = "https://portfoliokball.fr/portofolio/gsbcompterendu/produitsapi.php";

    RequestQueue queue;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_ajouter_produit, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        DrawerActivity activity = (DrawerActivity) getActivity();
        queue = Volley.newRequestQueue(getContext());

        EditText etNom              = view.findViewById(R.id.etNomProduit);
        EditText etComposition      = view.findViewById(R.id.etComposition);
        EditText etEffets           = view.findViewById(R.id.etEffets);
        EditText etContreIndications = view.findViewById(R.id.etContreIndications);
        Button   btnAjouter         = view.findViewById(R.id.btnAjouterProduit);

        btnAjouter.setOnClickListener(v -> {

            String nom               = etNom.getText().toString().trim();
            String composition       = etComposition.getText().toString().trim();
            String effets            = etEffets.getText().toString().trim();
            String contreIndications = etContreIndications.getText().toString().trim();

            // vérification minimale
            if (nom.isEmpty()) {
                Toast.makeText(getContext(), "Veuillez entrer un nom de produit", Toast.LENGTH_SHORT).show();
                return;
            }

            // envoi POST à l'API
            StringRequest requete = new StringRequest(Request.Method.POST, urlProduits,
                    response -> {
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");
                            if (status == 200) {
                                Toast.makeText(getContext(), "Produit ajouté avec succès !", Toast.LENGTH_SHORT).show();
                                // on revient à la liste des produits
                                NavHostFragment.findNavController(this).navigate(R.id.produitsFragment);
                            } else {
                                String msg = json.optString("message", "Erreur lors de l'ajout");
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
                            Toast.makeText(getContext(), "Fichier produitsapi.php introuvable ou pas de connexion", Toast.LENGTH_LONG).show();
                        }
                    }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    params.put("action", "ajouter");
                    params.put("token", activity.userToken);
                    params.put("nom", nom);
                    params.put("composition", composition);
                    params.put("effets", effets);
                    params.put("contre_indications", contreIndications);
                    return params;
                }
            };

            queue.add(requete);
        });
    }
}
