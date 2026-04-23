package com.example.connexionvolley;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

public class DashboardActivity extends AppCompatActivity {

    TextView tvMessage, tvId, tvPrenom, tvNom, tvEmail, tvRole, tvToken;
    Button btnDeconnexion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        tvMessage = findViewById(R.id.tvMessage);
        tvId = findViewById(R.id.tvId);
        tvPrenom = findViewById(R.id.tvFirstName);
        tvNom = findViewById(R.id.tvLastName);
        tvEmail = findViewById(R.id.tvEmail);
        tvRole = findViewById(R.id.tvRole);
        tvToken = findViewById(R.id.tvToken);
        btnDeconnexion = findViewById(R.id.btnDeconnexion);

        String message = getIntent().getStringExtra("message");
        String id = getIntent().getStringExtra("id");
        String prenom = getIntent().getStringExtra("prenom");
        String nom = getIntent().getStringExtra("nom");
        String email = getIntent().getStringExtra("email");
        String role = getIntent().getStringExtra("role");
        String token = getIntent().getStringExtra("token");

        tvMessage.setText(message);
        tvId.setText(id);
        tvPrenom.setText(prenom);
        tvNom.setText(nom);
        tvEmail.setText(email);
        tvRole.setText(role);


        if (token != null && token.length() > 10) {
            tvToken.setText(token.substring(0, 10) + "...");
        } else {
            tvToken.setText(token);
        }

        btnDeconnexion.setOnClickListener(v -> {
            Intent intent = new Intent(DashboardActivity.this, MainActivity.class);
            intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
            intent.putExtra("resetPassword", true);
            startActivity(intent);
            finish();
        });
    }
}