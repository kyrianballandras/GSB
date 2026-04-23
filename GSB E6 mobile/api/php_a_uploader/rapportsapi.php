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

// ---- LISTE TOUS LES RAPPORTS (délégué, responsable, admin) ----
if ($action == "liste_all") {

    $sql = "SELECT cr.id, cr.date_visite, cr.motif, cr.bilan,
                   v.nom AS v_nom, v.prenom AS v_prenom,
                   p.nom AS p_nom, p.prenom AS p_prenom
            FROM compterendu cr
            JOIN visiteur v  ON cr.id_visiteur  = v.id
            JOIN praticien p ON cr.id_praticien = p.id
            ORDER BY cr.date_visite DESC";

    $result = $conn->query($sql);

    $rapports = [];
    while ($row = $result->fetch_assoc()) {
        $rapports[] = $row;
    }

    echo json_encode(["status" => 200, "rapports" => $rapports]);

// ---- LISTE LES RAPPORTS D'UN VISITEUR (visiteur) ----
} elseif ($action == "liste_visiteur") {

    $idVisiteur = isset($_POST["id_visiteur"]) ? intval($_POST["id_visiteur"]) : 0;

    if ($idVisiteur == 0) {
        echo json_encode(["status" => 400, "message" => "ID visiteur manquant"]);
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT cr.id, cr.date_visite, cr.motif, cr.bilan,
                v.nom AS v_nom, v.prenom AS v_prenom,
                p.nom AS p_nom, p.prenom AS p_prenom
         FROM compterendu cr
         JOIN visiteur v  ON cr.id_visiteur  = v.id
         JOIN praticien p ON cr.id_praticien = p.id
         WHERE cr.id_visiteur = ?
         ORDER BY cr.date_visite DESC"
    );
    $stmt->bind_param("i", $idVisiteur);
    $stmt->execute();
    $result = $stmt->get_result();

    $rapports = [];
    while ($row = $result->fetch_assoc()) {
        $rapports[] = $row;
    }

    echo json_encode(["status" => 200, "rapports" => $rapports]);
    $stmt->close();

} else {
    echo json_encode(["status" => 400, "message" => "Action inconnue"]);
}

$conn->close();
?>
