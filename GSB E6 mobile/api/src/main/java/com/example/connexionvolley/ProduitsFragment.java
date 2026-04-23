package com.example.connexionvolley;

import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class ProduitsFragment extends Fragment {

    String urlProduits = "https://portfoliokball.fr/portofolio/gsbcompterendu/produitsapi.php";

    RequestQueue queue;
    LinearLayout tableau;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_produits, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        queue   = Volley.newRequestQueue(getContext());
        tableau = view.findViewById(R.id.tableauProduits);

        // on charge la liste des produits depuis la BDD
        chargerProduits();
    }

    private void chargerProduits() {

        StringRequest requete = new StringRequest(Request.Method.POST, urlProduits,
                response -> {
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            JSONArray liste = json.getJSONArray("produits");
                            afficherProduits(liste);
                        } else {
                            Toast.makeText(getContext(), "Impossible de charger les produits", Toast.LENGTH_SHORT).show();
                        }
                    } catch (JSONException e) {
                        Toast.makeText(getContext(), "Erreur de réponse serveur", Toast.LENGTH_SHORT).show();
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
                params.put("action", "liste");
                return params;
            }
        };

        queue.add(requete);
    }

    private void afficherProduits(JSONArray liste) {
        tableau.removeAllViews();
        boolean ligneGrise = false;
        float[] poids = {0.4f, 0.8f, 1.5f, 1.5f, 1.5f};

        for (int i = 0; i < liste.length(); i++) {
            try {
                JSONObject p = liste.getJSONObject(i);

                String id               = p.optString("id", "");
                String nom              = p.optString("nom", "");
                String composition      = p.optString("composition", "");
                String effets           = p.optString("effets", "");
                String contreIndic      = p.optString("contre_indications", "");

                LinearLayout row = new LinearLayout(getContext());
                row.setOrientation(LinearLayout.HORIZONTAL);
                row.setPadding(8, 12, 8, 12);
                row.setBackgroundColor(ligneGrise ? Color.parseColor("#F5F5F5") : Color.WHITE);
                ligneGrise = !ligneGrise;

                String[] cellules = {id, nom, composition, effets, contreIndic};

                for (int j = 0; j < cellules.length; j++) {
                    TextView tv = new TextView(getContext());
                    tv.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, poids[j]));
                    tv.setText(cellules[j]);
                    tv.setTextSize(12f);
                    tv.setTextColor(Color.parseColor("#212121"));
                    tv.setPadding(4, 0, 4, 0);
                    row.addView(tv);
                }

                tableau.addView(row);

            } catch (JSONException e) {
                // on passe si une ligne est mal formée
            }
        }

        if (liste.length() == 0) {
            TextView tvVide = new TextView(getContext());
            tvVide.setText("Aucun produit disponible");
            tvVide.setTextColor(Color.parseColor("#757575"));
            tvVide.setPadding(16, 16, 16, 16);
            tableau.addView(tvVide);
        }
    }
}
