package com.example.connexionvolley;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.navigation.fragment.NavHostFragment;

public class AccueilFragment extends Fragment {

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_accueil, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        DrawerActivity activity = (DrawerActivity) getActivity();

        TextView tvBonjour = view.findViewById(R.id.tvBonjour);
        TextView tvRole    = view.findViewById(R.id.tvRole);

        tvBonjour.setText("Bonjour " + activity.userPrenom + " " + activity.userNom);
        tvRole.setText("Rôle : " + activity.userRole);

        Button btnProduits       = view.findViewById(R.id.btnProduits);
        Button btnRapports       = view.findViewById(R.id.btnVoirRapports);
        Button btnSaisie         = view.findViewById(R.id.btnNouvelleSaisie);
        Button btnProfil         = view.findViewById(R.id.btnMonProfil);
        Button btnAjouterProduit = view.findViewById(R.id.btnAjouterProduit);
        Button btnGestionUsers   = view.findViewById(R.id.btnGestionUsers);

        // les produits et les rapport visible pour tout le monde
        btnProduits.setOnClickListener(v ->
            NavHostFragment.findNavController(this).navigate(R.id.produitsFragment));

        btnRapports.setOnClickListener(v ->
            NavHostFragment.findNavController(this).navigate(R.id.rapportsFragment));

        btnSaisie.setOnClickListener(v ->
            NavHostFragment.findNavController(this).navigate(R.id.saisieRapportFragment));

        btnProfil.setOnClickListener(v ->
            NavHostFragment.findNavController(this).navigate(R.id.profilFragment));

        // admin et responsable pour ajouter un produit
        if (activity.userRole.equals("administrateur") || activity.userRole.equals("responsable")) {
            btnAjouterProduit.setVisibility(View.VISIBLE);
            btnAjouterProduit.setOnClickListener(v ->
                NavHostFragment.findNavController(this).navigate(R.id.ajouterProduitFragment));
        }

        //  ladmin peut gérer les utilisateur
        if (activity.userRole.equals("administrateur")) {
            btnGestionUsers.setVisibility(View.VISIBLE);
            btnGestionUsers.setOnClickListener(v ->
                NavHostFragment.findNavController(this).navigate(R.id.gestionUsersFragment));
        }
    }
}
