<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

// =========================================================
// REMPLACEZ LE MOT DE PASSE PAR CELUI DE VOTRE loginapi.php
// =========================================================
$host   = "portfoo521.mysql.db";
$dbname = "portfoo521";
$user   = "portfoo521";
$pass   = "VOTRE_MDP_ICI";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["status" => 500, "message" => "Connexion impossible"]);
    exit;
}

$action = isset($_POST["action"]) ? $_POST["action"] : "";

// ---- AJOUTER UN COMPTE-RENDU ----
if ($action == "ajouter") {

    $idVisiteur  = isset($_POST["id_visiteur"])  ? intval($_POST["id_visiteur"])  : 0;
    $idPraticien = isset($_POST["id_praticien"]) ? intval($_POST["id_praticien"]) : 0;
    $dateVisite  = isset($_POST["date_visite"])  ? trim($_POST["date_visite"])    : "";
    $motif       = isset($_POST["motif"])        ? trim($_POST["motif"])          : "";
    $bilan       = isset($_POST["bilan"])        ? trim($_POST["bilan"])          : "";
    $remplacant  = isset($_POST["id_remplacant"]) ? trim($_POST["id_remplacant"]) : "";

    if ($idVisiteur == 0 || $idPraticien == 0 || empty($dateVisite)) {
        echo json_encode(["status" => 400, "message" => "Données manquantes"]);
        exit;
    }

    // on insère le compte-rendu dans la table compterendu
    $stmt = $conn->prepare(
        "INSERT INTO compterendu (id_visiteur, id_praticien, date_visite, motif, bilan, id_remplacant)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iissss", $idVisiteur, $idPraticien, $dateVisite, $motif, $bilan, $remplacant);

    if (!$stmt->execute()) {
        echo json_encode(["status" => 500, "message" => "Erreur lors de l'enregistrement"]);
        exit;
    }

    // on récupère l'id du compte-rendu qu'on vient d'insérer
    $idCr = $conn->insert_id;
    $stmt->close();

    // on insère les échantillons (si quantité > 0)
    // les produits en base : 1=Doliprane, 2=Lysopaïne, 3=Smecta
    $echantillons = [
        1 => isset($_POST["ech_1"]) ? intval($_POST["ech_1"]) : 0,
        2 => isset($_POST["ech_2"]) ? intval($_POST["ech_2"]) : 0,
        3 => isset($_POST["ech_3"]) ? intval($_POST["ech_3"]) : 0,
    ];

    foreach ($echantillons as $idProduit => $quantite) {
        if ($quantite > 0) {
            $stmtEch = $conn->prepare(
                "INSERT INTO echantillon (id_cr, id_produit, quantite) VALUES (?, ?, ?)"
            );
            $stmtEch->bind_param("iii", $idCr, $idProduit, $quantite);
            $stmtEch->execute();
            $stmtEch->close();
        }
    }

    echo json_encode(["status" => 200, "message" => "Compte-rendu enregistré", "id_cr" => $idCr]);

} else {
    echo json_encode(["status" => 400, "message" => "Action inconnue"]);
}

$conn->close();
?>
