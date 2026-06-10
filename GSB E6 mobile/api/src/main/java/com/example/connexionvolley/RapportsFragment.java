package com.example.connexionvolley;

import android.app.AlertDialog;
import android.graphics.Color;
import android.graphics.Typeface;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ScrollView;
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
    private boolean chargementEnCours = false;

    private void showToast(String msg) {
        if (isAdded() && getContext() != null) {
            Toast.makeText(getContext(), msg, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_rapports, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        activity = (DrawerActivity) getActivity();
        queue    = Volley.newRequestQueue(requireContext());
        tableau  = view.findViewById(R.id.tableauRapports);

        TextView tvTitre = view.findViewById(R.id.tvTitreRapports);
        tvTitre.setText(activity.userRole.equals("visiteur") ? "Mes comptes-rendus" : "Tous les comptes-rendus");

        chargerRapports();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        if (queue != null) queue.cancelAll(this);
    }

    private void chargerRapports() {
        if (chargementEnCours) return;
        chargementEnCours = true;

        StringRequest requete = new StringRequest(Request.Method.POST, urlRapports,
                response -> {
                    chargementEnCours = false;
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            afficherRapports(json.getJSONArray("rapports"));
                        } else {
                            showToast("Impossible de charger les rapports");
                        }
                    } catch (JSONException e) {
                        showToast("Erreur de réponse serveur");
                    }
                },
                error -> {
                    chargementEnCours = false;
                    if (!isAdded()) return;
                    showToast(error.networkResponse != null
                            ? "Erreur HTTP " + error.networkResponse.statusCode
                            : "Pas de connexion au serveur");
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                if (activity.userRole.equals("visiteur")) {
                    params.put("action", "liste_visiteur");
                    params.put("id_visiteur", String.valueOf(activity.userId));
                } else {
                    params.put("action", "liste_all");
                }
                return params;
            }
        };
        requete.setTag(this);
        queue.add(requete);
    }

    private void afficherRapports(JSONArray liste) {
        tableau.removeAllViews();
        boolean ligneGrise = false;

        for (int i = 0; i < liste.length(); i++) {
            try {
                JSONObject r = liste.getJSONObject(i);

                final String crId     = r.optString("id", "0");
                String date           = r.optString("date_visite", "");
                String praticien      = r.optString("p_nom", "") + " " + r.optString("p_prenom", "");
                String visiteur       = r.optString("v_nom", "") + " " + r.optString("v_prenom", "");

                LinearLayout row = new LinearLayout(getContext());
                row.setOrientation(LinearLayout.HORIZONTAL);
                row.setPadding(8, 10, 8, 10);
                row.setBackgroundColor(ligneGrise ? Color.parseColor("#F5F5F5") : Color.WHITE);
                ligneGrise = !ligneGrise;

                // Colonne date
                TextView tvDate = new TextView(getContext());
                tvDate.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvDate.setText(date);
                tvDate.setTextSize(12f);
                tvDate.setTextColor(Color.parseColor("#212121"));
                row.addView(tvDate);

                // Colonne praticien
                TextView tvPrat = new TextView(getContext());
                tvPrat.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.2f));
                tvPrat.setText(praticien);
                tvPrat.setTextSize(12f);
                tvPrat.setTextColor(Color.parseColor("#212121"));
                row.addView(tvPrat);

                // Colonne visiteur (masquée pour les visiteurs)
                if (!activity.userRole.equals("visiteur")) {
                    TextView tvVis = new TextView(getContext());
                    tvVis.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                    tvVis.setText(visiteur);
                    tvVis.setTextSize(12f);
                    tvVis.setTextColor(Color.parseColor("#212121"));
                    row.addView(tvVis);
                }

                // Bouton "Voir"
                Button btnVoir = new Button(getContext());
                btnVoir.setLayoutParams(new LinearLayout.LayoutParams(
                        LinearLayout.LayoutParams.WRAP_CONTENT,
                        LinearLayout.LayoutParams.WRAP_CONTENT));
                btnVoir.setText("Voir");
                btnVoir.setTextSize(11f);
                btnVoir.setBackgroundColor(Color.parseColor("#1565C0"));
                btnVoir.setTextColor(Color.WHITE);
                btnVoir.setPadding(16, 4, 16, 4);
                row.addView(btnVoir);

                tableau.addView(row);

                // Clic → charger le détail et afficher le dialog
                btnVoir.setOnClickListener(v -> chargerDetailEtAfficher(crId, row));

            } catch (JSONException e) {
                // ligne mal formée, on passe
            }
        }

        if (liste.length() == 0) {
            TextView tvVide = new TextView(getContext());
            tvVide.setText("Aucun compte-rendu trouvé");
            tvVide.setTextColor(Color.parseColor("#757575"));
            tvVide.setPadding(16, 16, 16, 16);
            tableau.addView(tvVide);
        }
    }

    // charge le detail d'un CR et ouvre le dialog
    private void chargerDetailEtAfficher(String crId, LinearLayout rowToRemove) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlRapports,
                response -> {
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            afficherDialogDetail(json.getJSONObject("cr"), crId, rowToRemove);
                        } else {
                            showToast("Impossible de charger le détail");
                        }
                    } catch (JSONException e) {
                        showToast("Erreur de réponse serveur");
                    }
                },
                error -> {
                    if (!isAdded()) return;
                    showToast("Erreur réseau");
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "detail");
                params.put("id_cr", crId);
                return params;
            }
        };
        queue.add(requete);
    }

    // affiche le dialog de detail du CR
    private void afficherDialogDetail(JSONObject cr, String crId, LinearLayout rowToRemove) {
        if (!isAdded()) return;

        try {
            String date      = cr.optString("date_visite", "-");
            String motif     = cr.optString("motif", "-");
            String bilan     = cr.optString("bilan", "-");
            String praticien = cr.optString("p_nom", "") + " " + cr.optString("p_prenom", "");
            String visiteur  = cr.optString("v_nom", "") + " " + cr.optString("v_prenom", "");
            String remplacant = cr.optString("id_remplacant", "");
            JSONArray echs   = cr.optJSONArray("echantillons");

            // contenu du dialog
            ScrollView scroll = new ScrollView(getContext());
            LinearLayout layout = new LinearLayout(getContext());
            layout.setOrientation(LinearLayout.VERTICAL);
            layout.setPadding(32, 24, 32, 24);
            scroll.addView(layout);

            // Chaque ligne d'info
            ajouterLigneInfo(layout, "Date de visite", date);
            ajouterLigneInfo(layout, "Praticien", praticien);
            ajouterLigneInfo(layout, "Visiteur", visiteur);
            ajouterLigneInfo(layout, "Motif", motif);
            if (!remplacant.isEmpty()) {
                ajouterLigneInfo(layout, "Remplaçant", remplacant);
            }

            // Bilan
            TextView tvBilanLabel = new TextView(getContext());
            tvBilanLabel.setText("Bilan");
            tvBilanLabel.setTypeface(null, Typeface.BOLD);
            tvBilanLabel.setTextColor(Color.parseColor("#1565C0"));
            tvBilanLabel.setTextSize(13f);
            tvBilanLabel.setPadding(0, 12, 0, 2);
            layout.addView(tvBilanLabel);

            TextView tvBilan = new TextView(getContext());
            tvBilan.setText(bilan.isEmpty() ? "—" : bilan);
            tvBilan.setTextSize(13f);
            tvBilan.setTextColor(Color.parseColor("#212121"));
            tvBilan.setBackgroundColor(Color.parseColor("#F5F5F5"));
            tvBilan.setPadding(12, 8, 12, 8);
            layout.addView(tvBilan);

            // Échantillons distribués
            if (echs != null && echs.length() > 0) {
                TextView tvEchLabel = new TextView(getContext());
                tvEchLabel.setText("Échantillons distribués");
                tvEchLabel.setTypeface(null, Typeface.BOLD);
                tvEchLabel.setTextColor(Color.parseColor("#1565C0"));
                tvEchLabel.setTextSize(13f);
                tvEchLabel.setPadding(0, 12, 0, 4);
                layout.addView(tvEchLabel);

                for (int i = 0; i < echs.length(); i++) {
                    JSONObject ech = echs.getJSONObject(i);
                    String produit = ech.optString("produit", "?");
                    String qte     = ech.optString("quantite", "0");

                    TextView tvEch = new TextView(getContext());
                    tvEch.setText("• " + produit + " : " + qte + " unité(s)");
                    tvEch.setTextSize(13f);
                    tvEch.setTextColor(Color.parseColor("#212121"));
                    tvEch.setPadding(8, 2, 0, 2);
                    layout.addView(tvEch);
                }
            } else {
                TextView tvNoEch = new TextView(getContext());
                tvNoEch.setText("Aucun échantillon distribué");
                tvNoEch.setTextSize(13f);
                tvNoEch.setTextColor(Color.parseColor("#9E9E9E"));
                tvNoEch.setPadding(0, 8, 0, 0);
                layout.addView(tvNoEch);
            }

            // le dialog en lui meme
            AlertDialog.Builder builder = new AlertDialog.Builder(getContext());
            builder.setTitle("Compte-rendu #" + crId);
            builder.setView(scroll);
            builder.setPositiveButton("Fermer", null);

            // Bouton Supprimer (seulement pour admin et responsable)
            if (activity.userRole.equals("administrateur") || activity.userRole.equals("responsable")) {
                builder.setNegativeButton("Supprimer", (dialog, which) -> {
                    // Confirmation avant suppression
                    new AlertDialog.Builder(getContext())
                            .setTitle("Confirmer la suppression")
                            .setMessage("Supprimer définitivement ce compte-rendu ?")
                            .setPositiveButton("Oui, supprimer", (d, w) -> supprimerCR(crId, rowToRemove))
                            .setNegativeButton("Annuler", null)
                            .show();
                });
            }

            AlertDialog dialog = builder.create();
            // Colorier le bouton Supprimer en rouge
            dialog.setOnShowListener(d -> {
                Button btn = dialog.getButton(AlertDialog.BUTTON_NEGATIVE);
                if (btn != null) btn.setTextColor(Color.parseColor("#E53935"));
            });
            dialog.show();

        } catch (JSONException e) {
            showToast("Erreur d'affichage");
        }
    }

    // ajoute une ligne label/valeur dans le dialog
    private void ajouterLigneInfo(LinearLayout parent, String label, String valeur) {
        LinearLayout ligne = new LinearLayout(getContext());
        ligne.setOrientation(LinearLayout.HORIZONTAL);
        ligne.setPadding(0, 6, 0, 6);

        TextView tvLabel = new TextView(getContext());
        tvLabel.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
        tvLabel.setText(label);
        tvLabel.setTypeface(null, Typeface.BOLD);
        tvLabel.setTextSize(13f);
        tvLabel.setTextColor(Color.parseColor("#1565C0"));

        TextView tvValeur = new TextView(getContext());
        tvValeur.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.5f));
        tvValeur.setText(valeur.isEmpty() ? "—" : valeur);
        tvValeur.setTextSize(13f);
        tvValeur.setTextColor(Color.parseColor("#212121"));

        ligne.addView(tvLabel);
        ligne.addView(tvValeur);
        parent.addView(ligne);
    }

    // supprime le CR côté serveur
    private void supprimerCR(String crId, LinearLayout rowToRemove) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlRapports,
                response -> {
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            showToast("Compte-rendu supprimé");
                            tableau.removeView(rowToRemove);
                        } else {
                            showToast("Impossible de supprimer");
                        }
                    } catch (JSONException e) {
                        showToast("Erreur de réponse");
                    }
                },
                error -> {
                    if (!isAdded()) return;
                    showToast("Erreur réseau");
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "supprimer");
                params.put("id_cr", crId);
                return params;
            }
        };
        queue.add(requete);
    }
}
