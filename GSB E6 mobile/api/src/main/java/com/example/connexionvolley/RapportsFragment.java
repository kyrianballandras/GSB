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

public class RapportsFragment extends Fragment {

    String urlRapports = "https://portfoliokball.fr/portofolio/gsbcompterendu/rapportsapi.php";

    RequestQueue queue;
    LinearLayout tableau;
    DrawerActivity activity;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_rapports, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        activity = (DrawerActivity) getActivity();
        queue    = Volley.newRequestQueue(getContext());
        tableau  = view.findViewById(R.id.tableauRapports);

        // titre selon le rôle
        TextView tvTitre = view.findViewById(R.id.tvTitreRapports);
        if (activity.userRole.equals("visiteur")) {
            tvTitre.setText("Mes comptes-rendus");
        } else {
            tvTitre.setText("Tous les comptes-rendus");
        }

        // on charge les rapports depuis l'API
        chargerRapports();
    }

    private void chargerRapports() {

        StringRequest requete = new StringRequest(Request.Method.POST, urlRapports,
                response -> {
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            JSONArray liste = json.getJSONArray("rapports");
                            afficherRapports(liste);
                        } else {
                            Toast.makeText(getContext(), "Impossible de charger les rapports", Toast.LENGTH_SHORT).show();
                        }
                    } catch (JSONException e) {
                        Toast.makeText(getContext(), "Erreur de réponse serveur", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> {
                    if (error.networkResponse != null) {
                        Toast.makeText(getContext(), "Erreur serveur HTTP " + error.networkResponse.statusCode, Toast.LENGTH_LONG).show();
                    } else {
                        Toast.makeText(getContext(), "Fichier rapportsapi.php introuvable ou pas de connexion", Toast.LENGTH_LONG).show();
                    }
                }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();

                // visiteur → ses propres rapports seulement
                if (activity.userRole.equals("visiteur")) {
                    params.put("action", "liste_visiteur");
                    params.put("id_visiteur", String.valueOf(activity.userId));
                } else {
                    // délégué, responsable, admin → tous les rapports
                    params.put("action", "liste_all");
                }
                return params;
            }
        };

        queue.add(requete);
    }

    private void afficherRapports(JSONArray liste) {
        tableau.removeAllViews();
        boolean ligneGrise = false;

        for (int i = 0; i < liste.length(); i++) {
            try {
                JSONObject r = liste.getJSONObject(i);

                String date      = r.optString("date_visite", "");
                String praticien = r.optString("p_nom", "") + " " + r.optString("p_prenom", "");
                String visiteur  = r.optString("v_nom", "") + " " + r.optString("v_prenom", "");

                LinearLayout row = new LinearLayout(getContext());
                row.setOrientation(LinearLayout.HORIZONTAL);
                row.setPadding(8, 12, 8, 12);
                row.setBackgroundColor(ligneGrise ? Color.parseColor("#F5F5F5") : Color.WHITE);
                ligneGrise = !ligneGrise;

                // colonne date
                TextView tvDate = new TextView(getContext());
                tvDate.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvDate.setText(date);
                tvDate.setTextSize(12f);
                tvDate.setTextColor(Color.parseColor("#212121"));
                row.addView(tvDate);

                // colonne praticien
                TextView tvPrat = new TextView(getContext());
                tvPrat.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvPrat.setText(praticien);
                tvPrat.setTextSize(12f);
                tvPrat.setTextColor(Color.parseColor("#212121"));
                row.addView(tvPrat);

                // colonne visiteur
                TextView tvVis = new TextView(getContext());
                tvVis.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvVis.setText(visiteur);
                tvVis.setTextSize(12f);
                tvVis.setTextColor(Color.parseColor("#212121"));
                row.addView(tvVis);

                tableau.addView(row);

            } catch (JSONException e) {
                // on passe si une ligne est mal formée
            }
        }

        // message si aucun rapport
        if (liste.length() == 0) {
            TextView tvVide = new TextView(getContext());
            tvVide.setText("Aucun compte-rendu trouvé");
            tvVide.setTextColor(Color.parseColor("#757575"));
            tvVide.setPadding(16, 16, 16, 16);
            tableau.addView(tvVide);
        }
    }
}
