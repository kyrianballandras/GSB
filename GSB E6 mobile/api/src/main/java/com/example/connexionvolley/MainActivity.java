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
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import android.util.Log;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.HashMap;
import java.util.Map;

public class MainActivity extends AppCompatActivity {

    EditText etUser, etMdp;
    Button btnLogin;
    ProgressDialog chargement;
    RequestQueue queue;

    String urlApi = "https://portfoliokball.fr/portofolio/gsbcompterendu/loginapi.php";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        etUser   = findViewById(R.id.login);
        etMdp    = findViewById(R.id.password);
        btnLogin = findViewById(R.id.connexion);

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

            StringRequest requete = new StringRequest(Request.Method.POST, urlApi,
                    response -> {
                        chargement.dismiss();
                        try {
                            JSONObject json = new JSONObject(response);
                            int status = json.getInt("status");

                            if (status == 200) {
                                JSONObject user = json.getJSONObject("user");

                                int    userId = user.optInt("id", 0);
                                String prenom = user.optString("prenom", "");
                                String nom    = user.optString("nom", "");
                                String email  = user.optString("login", userVar);
                                String role   = user.optString("role", "visiteur");
                                String token  = json.optString("token", "");

                                // on passe les infos a DrawerActivity
                                Intent intent = new Intent(MainActivity.this, DrawerActivity.class);
                                intent.putExtra("userId", userId);
                                intent.putExtra("prenom", prenom);
                                intent.putExtra("nom", nom);
                                intent.putExtra("email", email);
                                intent.putExtra("role", role);
                                intent.putExtra("token", token);
                                startActivity(intent);

                            } else {
                                String msg = json.optString("message", "Email ou mot de passe incorrect");
                                Toast.makeText(this, msg, Toast.LENGTH_LONG).show();
                            }

                        } catch (JSONException e) {
                            Toast.makeText(this, "Erreur lecture réponse", Toast.LENGTH_LONG).show();
                        }
                    },
                    error -> {
                        chargement.dismiss();
                        String message = "Erreur réseau";

                        if (error.networkResponse != null) {
                            int statusCode = error.networkResponse.statusCode;
                            message = "Erreur " + statusCode;
                            try {
                                String body = new String(error.networkResponse.data, "UTF-8");
                                JSONObject res = new JSONObject(body);
                                if (res.has("message")) message += " : " + res.getString("message");
                            } catch (Exception e) {
                                Log.e("LOGIN", e.toString());
                            }
                        } else if (error.getMessage() != null) {
                            message = error.getMessage();
                        }

                        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
                    }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<>();
                    params.put("login", userVar);
                    params.put("password", mdpVar);
                    return params;
                }
            };

            queue.add(requete);
        });
    }

    // retour sur le login = on vide le mdp
    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        etMdp.setText("");
    }
}
