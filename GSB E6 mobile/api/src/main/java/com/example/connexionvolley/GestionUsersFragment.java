package com.example.connexionvolley;

import android.app.AlertDialog;
import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Spinner;
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

public class GestionUsersFragment extends Fragment {

    // URL de l'API utilisateurs (même serveur que le login)
    String urlUsers = "https://portfoliokball.fr/portofolio/gsbcompterendu/usersapi.php";

    RequestQueue queue;
    LinearLayout tableauUsers;
    DrawerActivity activity;

    // liste des rôles possibles
    String[] roles = {"visiteur", "delegue", "responsable", "administrateur"};

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_users, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        activity = (DrawerActivity) getActivity();
        queue = Volley.newRequestQueue(getContext());
        tableauUsers = view.findViewById(R.id.tableauUsers);

        // spinner rôle pour la création
        Spinner spinnerRole = view.findViewById(R.id.spinnerRoleUser);
        ArrayAdapter<String> adapterRole = new ArrayAdapter<>(getContext(),
                android.R.layout.simple_spinner_item, roles);
        adapterRole.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinnerRole.setAdapter(adapterRole);

        // bouton créer utilisateur
        EditText etNom    = view.findViewById(R.id.etNomUser);
        EditText etPrenom = view.findViewById(R.id.etPrenomUser);
        EditText etEmail  = view.findViewById(R.id.etEmailUser);
        EditText etMdp    = view.findViewById(R.id.etMdpUser);
        Button btnCreer   = view.findViewById(R.id.btnCreerUser);

        btnCreer.setOnClickListener(v -> {
            String nom    = etNom.getText().toString().trim();
            String prenom = etPrenom.getText().toString().trim();
            String email  = etEmail.getText().toString().trim();
            String mdp    = etMdp.getText().toString().trim();
            String role   = spinnerRole.getSelectedItem().toString();

            if (nom.isEmpty() || prenom.isEmpty() || email.isEmpty() || mdp.isEmpty()) {
                Toast.makeText(getContext(), "Remplissez tous les champs", Toast.LENGTH_SHORT).show();
                return;
            }

            // envoi de la requête POST pour créer l'utilisateur
            StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                    response -> {
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");
                            if (status == 200) {
                                Toast.makeText(getContext(), "Utilisateur créé !", Toast.LENGTH_SHORT).show();
                                etNom.setText("");
                                etPrenom.setText("");
                                etEmail.setText("");
                                etMdp.setText("");
                                // on recharge la liste
                                chargerListeUsers();
                            } else {
                                String msg = json.optString("message", "Erreur lors de la création");
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
                            Toast.makeText(getContext(), "Fichier usersapi.php introuvable ou pas de connexion", Toast.LENGTH_LONG).show();
                        }
                    }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    params.put("action", "creer");
                    params.put("token", activity.userToken);
                    params.put("nom", nom);
                    params.put("prenom", prenom);
                    params.put("login", email);
                    params.put("password", mdp);
                    params.put("role", role);
                    return params;
                }
            };

            queue.add(requete);
        });

        // on charge la liste des utilisateurs au démarrage
        chargerListeUsers();
    }

    // charge la liste depuis l'API et l'affiche dans tableauUsers
    private void chargerListeUsers() {
        tableauUsers.removeAllViews();

        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            JSONArray listeUsers = json.getJSONArray("users");
                            afficherUsers(listeUsers);
                        }
                    } catch (JSONException e) {
                        Toast.makeText(getContext(), "Erreur lors du chargement", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(getContext(), "Erreur réseau", Toast.LENGTH_LONG).show()) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "liste");
                params.put("token", activity.userToken);
                return params;
            }
        };

        queue.add(requete);
    }

    // crée les lignes du tableau avec les boutons Supprimer et Modifier
    private void afficherUsers(JSONArray listeUsers) {
        boolean ligneGrise = false;

        for (int i = 0; i < listeUsers.length(); i++) {
            try {
                JSONObject user = listeUsers.getJSONObject(i);
                String userId   = String.valueOf(user.getInt("id"));
                String nom      = user.optString("nom", "");
                String prenom   = user.optString("prenom", "");
                String login    = user.optString("login", "");
                String role     = user.optString("role", "visiteur");

                LinearLayout row = new LinearLayout(getContext());
                row.setOrientation(LinearLayout.HORIZONTAL);
                row.setPadding(8, 10, 8, 10);
                row.setBackgroundColor(ligneGrise ? Color.parseColor("#F5F5F5") : Color.WHITE);
                ligneGrise = !ligneGrise;

                // colonne nom + prénom
                TextView tvNom = new TextView(getContext());
                tvNom.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.2f));
                tvNom.setText(nom + "\n" + prenom);
                tvNom.setTextSize(12f);
                tvNom.setTextColor(Color.parseColor("#212121"));
                row.addView(tvNom);

                // colonne login
                TextView tvLogin = new TextView(getContext());
                tvLogin.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.5f));
                tvLogin.setText(login);
                tvLogin.setTextSize(12f);
                tvLogin.setTextColor(Color.parseColor("#212121"));
                row.addView(tvLogin);

                // colonne rôle
                TextView tvRole = new TextView(getContext());
                tvRole.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvRole.setText(role);
                tvRole.setTextSize(12f);
                tvRole.setTextColor(Color.parseColor("#212121"));
                row.addView(tvRole);

                // colonne boutons
                LinearLayout colBoutons = new LinearLayout(getContext());
                colBoutons.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.3f));
                colBoutons.setOrientation(LinearLayout.VERTICAL);

                Button btnSupprimer = new Button(getContext());
                btnSupprimer.setText("Supprimer");
                btnSupprimer.setTextSize(11f);
                btnSupprimer.setBackgroundColor(Color.parseColor("#E53935"));
                btnSupprimer.setTextColor(Color.WHITE);
                btnSupprimer.setPadding(4, 4, 4, 4);

                Button btnModifier = new Button(getContext());
                btnModifier.setText("Modifier");
                btnModifier.setTextSize(11f);
                btnModifier.setBackgroundColor(Color.parseColor("#FB8C00"));
                btnModifier.setTextColor(Color.WHITE);
                btnModifier.setPadding(4, 4, 4, 4);

                colBoutons.addView(btnSupprimer);
                colBoutons.addView(btnModifier);
                row.addView(colBoutons);

                tableauUsers.addView(row);

                // clic Supprimer → requête POST action=supprimer
                btnSupprimer.setOnClickListener(v -> supprimerUser(userId, row));

                // clic Modifier → dialog avec spinner de rôle
                btnModifier.setOnClickListener(v -> ouvrirDialogModifier(userId, role));

            } catch (JSONException e) {
                // on passe si une ligne est mal formée
            }
        }
    }

    // supprime un utilisateur via l'API
    private void supprimerUser(String userId, LinearLayout row) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            Toast.makeText(getContext(), "Utilisateur supprimé", Toast.LENGTH_SHORT).show();
                            tableauUsers.removeView(row);
                        } else {
                            Toast.makeText(getContext(), "Impossible de supprimer", Toast.LENGTH_SHORT).show();
                        }
                    } catch (JSONException e) {
                        Toast.makeText(getContext(), "Erreur de réponse", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(getContext(), "Erreur réseau", Toast.LENGTH_LONG).show()) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "supprimer");
                params.put("token", activity.userToken);
                params.put("id", userId);
                return params;
            }
        };

        queue.add(requete);
    }

    // ouvre un dialog pour choisir le nouveau rôle
    private void ouvrirDialogModifier(String userId, String roleActuel) {
        // on crée un spinner à mettre dans le dialog
        Spinner spinnerDialog = new Spinner(getContext());
        ArrayAdapter<String> adapter = new ArrayAdapter<>(getContext(),
                android.R.layout.simple_spinner_item, roles);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinnerDialog.setAdapter(adapter);

        // on présélectionne le rôle actuel
        for (int i = 0; i < roles.length; i++) {
            if (roles[i].equals(roleActuel)) {
                spinnerDialog.setSelection(i);
                break;
            }
        }

        AlertDialog.Builder dialog = new AlertDialog.Builder(getContext());
        dialog.setTitle("Changer le rôle");
        dialog.setView(spinnerDialog);
        dialog.setPositiveButton("Modifier", (dialogInterface, which) -> {
            String nouveauRole = spinnerDialog.getSelectedItem().toString();
            modifierRoleUser(userId, nouveauRole);
        });
        dialog.setNegativeButton("Annuler", null);
        dialog.show();
    }

    // modifie le rôle d'un utilisateur via l'API
    private void modifierRoleUser(String userId, String nouveauRole) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            Toast.makeText(getContext(), "Rôle modifié !", Toast.LENGTH_SHORT).show();
                            chargerListeUsers(); // on recharge la liste
                        } else {
                            Toast.makeText(getContext(), "Impossible de modifier", Toast.LENGTH_SHORT).show();
                        }
                    } catch (JSONException e) {
                        Toast.makeText(getContext(), "Erreur de réponse", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> Toast.makeText(getContext(), "Erreur réseau", Toast.LENGTH_LONG).show()) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "modifier_role");
                params.put("token", activity.userToken);
                params.put("id", userId);
                params.put("role", nouveauRole);
                return params;
            }
        };

        queue.add(requete);
    }
}
