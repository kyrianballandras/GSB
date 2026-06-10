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

    String urlUsers = "https://portfoliokball.fr/portofolio/gsbcompterendu/usersapi.php";

    RequestQueue queue;
    LinearLayout tableauUsers;
    DrawerActivity activity;

    String[] roles = {"visiteur", "delegue", "responsable", "administrateur"};

    // flag pour eviter les requetes en double
    private boolean chargementEnCours = false;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_users, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        activity = (DrawerActivity) getActivity();
        queue = Volley.newRequestQueue(requireContext());
        tableauUsers = view.findViewById(R.id.tableauUsers);

        Spinner spinnerRole = view.findViewById(R.id.spinnerRoleUser);
        ArrayAdapter<String> adapterRole = new ArrayAdapter<>(requireContext(),
                android.R.layout.simple_spinner_item, roles);
        adapterRole.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinnerRole.setAdapter(adapterRole);

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
                showToast("Remplissez tous les champs");
                return;
            }

            StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                    response -> {
                        if (!isAdded()) return;
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");
                            if (status == 200) {
                                showToast("Utilisateur créé !");
                                etNom.setText("");
                                etPrenom.setText("");
                                etEmail.setText("");
                                etMdp.setText("");
                                chargerListeUsers();
                            } else {
                                showToast(json.optString("message", "Erreur lors de la création"));
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

        chargerListeUsers();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        // on coupe les requetes en cours
        if (queue != null) queue.cancelAll(this);
    }

    private void showToast(String msg) {
        if (isAdded() && getContext() != null) {
            Toast.makeText(getContext(), msg, Toast.LENGTH_SHORT).show();
        }
    }

    private void chargerListeUsers() {
        if (chargementEnCours) return; // évite les appels multiples en rafale
        chargementEnCours = true;
        tableauUsers.removeAllViews();

        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    chargementEnCours = false;
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        int status = json.getInt("status");
                        if (status == 200) {
                            JSONArray listeUsers = json.getJSONArray("users");
                            afficherUsers(listeUsers);
                        }
                    } catch (JSONException e) {
                        showToast("Erreur lors du chargement");
                    }
                },
                error -> {
                    chargementEnCours = false;
                    if (!isAdded()) return;
                    showToast("Erreur réseau");
                }) {
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("action", "liste");
                params.put("token", activity.userToken);
                return params;
            }
        };

        requete.setTag(this);
        queue.add(requete);
    }

    private void afficherUsers(JSONArray listeUsers) {
        boolean ligneGrise = false;

        for (int i = 0; i < listeUsers.length(); i++) {
            try {
                JSONObject user = listeUsers.getJSONObject(i);
                String userId = String.valueOf(user.getInt("id"));
                String nom    = user.optString("nom", "");
                String prenom = user.optString("prenom", "");
                String login  = user.optString("login", "");
                String role   = user.optString("role", "visiteur");

                LinearLayout row = new LinearLayout(getContext());
                row.setOrientation(LinearLayout.HORIZONTAL);
                row.setPadding(8, 10, 8, 10);
                row.setBackgroundColor(ligneGrise ? Color.parseColor("#F5F5F5") : Color.WHITE);
                ligneGrise = !ligneGrise;

                TextView tvNom = new TextView(getContext());
                tvNom.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.2f));
                tvNom.setText(nom + "\n" + prenom);
                tvNom.setTextSize(12f);
                tvNom.setTextColor(Color.parseColor("#212121"));
                row.addView(tvNom);

                TextView tvLogin = new TextView(getContext());
                tvLogin.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.5f));
                tvLogin.setText(login);
                tvLogin.setTextSize(12f);
                tvLogin.setTextColor(Color.parseColor("#212121"));
                row.addView(tvLogin);

                TextView tvRole = new TextView(getContext());
                tvRole.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f));
                tvRole.setText(role);
                tvRole.setTextSize(12f);
                tvRole.setTextColor(Color.parseColor("#212121"));
                row.addView(tvRole);

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

                btnSupprimer.setOnClickListener(v -> supprimerUser(userId, row));
                btnModifier.setOnClickListener(v -> ouvrirDialogModifier(userId, role));

            } catch (JSONException e) {
                // ligne mal formée, on passe
            }
        }
    }

    private void supprimerUser(String userId, LinearLayout row) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            showToast("Utilisateur supprimé");
                            tableauUsers.removeView(row);
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
                params.put("token", activity.userToken);
                params.put("id", userId);
                return params;
            }
        };
        queue.add(requete);
    }

    private void ouvrirDialogModifier(String userId, String roleActuel) {
        Spinner spinnerDialog = new Spinner(getContext());
        ArrayAdapter<String> adapter = new ArrayAdapter<>(getContext(),
                android.R.layout.simple_spinner_item, roles);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinnerDialog.setAdapter(adapter);

        for (int i = 0; i < roles.length; i++) {
            if (roles[i].equals(roleActuel)) {
                spinnerDialog.setSelection(i);
                break;
            }
        }

        new AlertDialog.Builder(getContext())
                .setTitle("Changer le rôle")
                .setView(spinnerDialog)
                .setPositiveButton("Modifier", (d, w) ->
                        modifierRoleUser(userId, spinnerDialog.getSelectedItem().toString()))
                .setNegativeButton("Annuler", null)
                .show();
    }

    private void modifierRoleUser(String userId, String nouveauRole) {
        StringRequest requete = new StringRequest(Request.Method.POST, urlUsers,
                response -> {
                    if (!isAdded()) return;
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getInt("status") == 200) {
                            showToast("Rôle modifié !");
                            chargerListeUsers();
                        } else {
                            showToast("Impossible de modifier");
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
