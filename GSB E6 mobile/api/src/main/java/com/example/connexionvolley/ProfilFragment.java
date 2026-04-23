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

public class ProfilFragment extends Fragment {

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_profil, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        DrawerActivity activity = (DrawerActivity) getActivity();

        TextView tvRole   = view.findViewById(R.id.tvProfilRole);
        TextView tvNom    = view.findViewById(R.id.tvProfilNom);
        TextView tvPrenom = view.findViewById(R.id.tvProfilPrenom);
        TextView tvEmail  = view.findViewById(R.id.tvProfilEmail);
        Button btnDeconnexion = view.findViewById(R.id.btnDeconnexion);

        tvRole.setText(activity.userRole);
        tvNom.setText(activity.userNom);
        tvPrenom.setText(activity.userPrenom);
        tvEmail.setText(activity.userEmail);

        btnDeconnexion.setOnClickListener(v -> activity.seDeconnecter());
    }
}
