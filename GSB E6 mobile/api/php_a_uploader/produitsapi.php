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

// ---- LISTE TOUS LES PRODUITS ----
if ($action == "liste") {

    $sql    = "SELECT id, nom, composition, effets, contre_indications FROM produit ORDER BY nom";
    $result = $conn->query($sql);

    $produits = [];
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }

    echo json_encode(["status" => 200, "produits" => $produits]);

// ---- AJOUTER UN PRODUIT ----
} elseif ($action == "ajouter") {

    $nom               = isset($_POST["nom"])               ? trim($_POST["nom"])               : "";
    $composition       = isset($_POST["composition"])       ? trim($_POST["composition"])       : "";
    $effets            = isset($_POST["effets"])            ? trim($_POST["effets"])            : "";
    $contreIndications = isset($_POST["contre_indications"]) ? trim($_POST["contre_indications"]) : "";

    if (empty($nom)) {
        echo json_encode(["status" => 400, "message" => "Le nom est obligatoire"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO produit (nom, composition, effets, contre_indications) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $composition, $effets, $contreIndications);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Produit ajouté"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Erreur lors de l'ajout"]);
    }
    $stmt->close();

} else {
    echo json_encode(["status" => 400, "message" => "Action inconnue"]);
}

$conn->close();
?>
