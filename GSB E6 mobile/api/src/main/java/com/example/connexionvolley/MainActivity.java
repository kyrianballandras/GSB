package com.example.connexionvolley;

import androidx.appcompat.app.AppCompatActivity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class MainActivity extends AppCompatActivity {

    // IDs exacts de activity_main.xml
    // champ email  → R.id.login
    // champ mdp    → R.id.password
    // bouton       → R.id.connexion
    EditText etUser, etMdp;
    Button btnLogin;
    ProgressDialog chargement;
    RequestQueue queue;

    // URL de la vraie API GSB
    String urlApi = "https://portfoliokball.fr/portofolio/gsbcompterendu/loginapi.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        etUser   = findViewById(R.id.login);      // champ email dans le XML
        etMdp    = findViewById(R.id.password);   // champ mot de passe
        btnLogin = findViewById(R.id.connexion);  // bouton connexion

        queue = Volley.newRequestQueue(this);

        chargement = new ProgressDialog(this);
        chargement.setMessage("Connexion en cours...");
        chargement.setCancelable(false);

        btnLogin.setOnClickListener(v -> {

            String userVar = etUser.getText().toString().trim();
            String mdpVar  = etMdp.getText().toString().trim();

            if (userVar.isEmpty()) {
                Toast.makeText(this, "Veuillez entrer votre email", Toast.LENGTH_SHORT).show();
                return;
            }
            if (mdpVar.isEmpty()) {
                Toast.makeText(this, "Veuillez entrer votre mot de passe", Toast.LENGTH_SHORT).show();
                return;
            }

            chargement.show();

            // requête POST vers l'API GSB
            StringRequest requete = new StringRequest(Request.Method.POST,
                    urlApi,
                    response -> {
                        chargement.dismiss();
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");

                            if (status == 200) {
                                // les données utilisateur sont dans l'objet "user"
                                JSONObject user = json.getJSONObject("user");

                                int    userId = user.optInt("id", 0);
                                String prenom = user.optString("prenom", "");
                                String nom    = user.optString("nom", "");
                                String email  = user.optString("login", userVar);
                                String role   = user.optString("role", "visiteur");
                                // le token est à la racine du JSON
                                String token  = json.optString("token", "");

                                // on lance DrawerActivity avec les infos utilisateur
                                Intent intent = new Intent(MainActivity.this, DrawerActivity.class);
                                intent.putExtra("userId", userId);
                                intent.putExtra("prenom", prenom);
                                intent.putExtra("nom", nom);
                                intent.putExtra("email", email);
                                intent.putExtra("role", role);
                                intent.putExtra("token", token);
                                startActivity(intent);

                            } else {
                                // status 401 = identifiants incorrects
                                String msg = json.optString("message", "Email ou mot de passe incorrect");
                                Toast.makeText(this, msg, Toast.LENGTH_LONG).show();
                            }

                        } catch (JSONException e) {
                            Toast.makeText(this, "Erreur lors de la lecture de la réponse", Toast.LENGTH_LONG).show();
                        }
                    },
                    error -> {
                        chargement.dismiss();
                        Toast.makeText(this, "Erreur réseau. Vérifiez votre connexion.", Toast.LENGTH_LONG).show();
                    }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    // l'API attend "login" (pas "email") et "password"
                    params.put("login", userVar);
                    params.put("password", mdpVar);
                    return params;
                }
            };

            queue.add(requete);
        });
    }

    // quand on revient sur MainActivity (déconnexion)  on vide le mot de passe
    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        etMdp.setText("");
    }
}
