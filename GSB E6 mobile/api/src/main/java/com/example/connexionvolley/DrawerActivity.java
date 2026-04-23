package com.example.connexionvolley;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import androidx.drawerlayout.widget.DrawerLayout;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.AppBarConfiguration;
import androidx.navigation.ui.NavigationUI;
import android.content.Intent;
import android.os.Bundle;
import android.view.Menu;
import com.google.android.material.navigation.NavigationView;

public class DrawerActivity extends AppCompatActivity {

    // infos utilisateur accessibles depuis tous les fragments
    public int    userId    = 0;
    public String userPrenom = "";
    public String userNom    = "";
    public String userEmail  = "";
    public String userRole   = "visiteur";
    public String userToken  = "";

    NavController navController;
    AppBarConfiguration appBarConfiguration;
    DrawerLayout drawerLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_drawer);

        // récupération des infos depuis MainActivity (login)
        userId    = getIntent().getIntExtra("userId", 0);
        userPrenom = getIntent().getStringExtra("prenom");
        userNom    = getIntent().getStringExtra("nom");
        userEmail  = getIntent().getStringExtra("email");
        userRole   = getIntent().getStringExtra("role");
        userToken  = getIntent().getStringExtra("token");

        if (userRole == null) userRole = "visiteur";

        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        drawerLayout = findViewById(R.id.drawer_layout);

        NavHostFragment navHostFragment = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        navController = navHostFragment.getNavController();

        appBarConfiguration = new AppBarConfiguration.Builder(
                R.id.accueilFragment,
                R.id.rapportsFragment,
                R.id.saisieRapportFragment,
                R.id.profilFragment,
                R.id.produitsFragment,
                R.id.gestionUsersFragment,
                R.id.ajouterProduitFragment
        ).setOpenableLayout(drawerLayout).build();

        NavigationUI.setupActionBarWithNavController(this, navController, appBarConfiguration);

        NavigationView navigationView = findViewById(R.id.navigation_view);
        NavigationUI.setupWithNavController(navigationView, navController);

        // on masque les items du menu selon le rôle
        Menu menu = navigationView.getMenu();

        // "Gestion utilisateurs" → admin seulement
        if (!userRole.equals("administrateur")) {
            menu.findItem(R.id.gestionUsersFragment).setVisible(false);
        }

        // "Ajouter un produit" → admin et responsable
        if (!userRole.equals("administrateur") && !userRole.equals("responsable")) {
            menu.findItem(R.id.ajouterProduitFragment).setVisible(false);
        }
    }

    @Override
    public boolean onSupportNavigateUp() {
        return NavigationUI.navigateUp(navController, appBarConfiguration)
                || super.onSupportNavigateUp();
    }

    // déconnexion → retour au login
    public void seDeconnecter() {
        Intent intent = new Intent(this, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_SINGLE_TOP);
        startActivity(intent);
        finish();
    }
}
