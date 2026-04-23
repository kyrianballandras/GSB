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

// ---- LISTE TOUS LES UTILISATEURS ----
if ($action == "liste") {

    $sql = "SELECT id, nom, prenom, login, role FROM visiteur ORDER BY nom";
    $result = $conn->query($sql);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode(["status" => 200, "users" => $users]);

// ---- CRÉER UN UTILISATEUR ----
} elseif ($action == "creer") {

    $nom    = isset($_POST["nom"])    ? trim($_POST["nom"])    : "";
    $prenom = isset($_POST["prenom"]) ? trim($_POST["prenom"]) : "";
    $login  = isset($_POST["login"])  ? trim($_POST["login"])  : "";
    $mdp    = isset($_POST["password"]) ? $_POST["password"]  : "";
    $role   = isset($_POST["role"])   ? trim($_POST["role"])   : "visiteur";

    if (empty($nom) || empty($prenom) || empty($login) || empty($mdp)) {
        echo json_encode(["status" => 400, "message" => "Champs manquants"]);
        exit;
    }

    // on vérifie que le login n'existe pas déjà
    $check = $conn->prepare("SELECT id FROM visiteur WHERE login = ?");
    $check->bind_param("s", $login);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["status" => 400, "message" => "Cet email existe déjà"]);
        exit;
    }
    $check->close();

    // on hash le mot de passe comme loginapi.php
    $mdpHash = password_hash($mdp, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO visiteur (nom, prenom, login, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nom, $prenom, $login, $mdpHash, $role);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Utilisateur créé"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Erreur lors de la création"]);
    }
    $stmt->close();

// ---- SUPPRIMER UN UTILISATEUR ----
} elseif ($action == "supprimer") {

    $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;

    if ($id == 0) {
        echo json_encode(["status" => 400, "message" => "ID manquant"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM visiteur WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Utilisateur supprimé"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Erreur lors de la suppression"]);
    }
    $stmt->close();

// ---- MODIFIER LE RÔLE ----
} elseif ($action == "modifier_role") {

    $id   = isset($_POST["id"])   ? intval($_POST["id"])   : 0;
    $role = isset($_POST["role"]) ? trim($_POST["role"])   : "";

    if ($id == 0 || empty($role)) {
        echo json_encode(["status" => 400, "message" => "Données manquantes"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE visiteur SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => 200, "message" => "Rôle modifié"]);
    } else {
        echo json_encode(["status" => 500, "message" => "Erreur lors de la modification"]);
    }
    $stmt->close();

} else {
    echo json_encode(["status" => 400, "message" => "Action inconnue"]);
}

$conn->close();
?>
