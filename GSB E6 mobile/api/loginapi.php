<?php
/**
 * login.php — API REST de connexion
 * Base de données : portfoo521
 * Table : visiteur (login, password, nom, prenom, role)
 *
 * Méthode : POST
 * Paramètres : login (email), password
 * Réponse   : JSON
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// ─────────────────────────────────────────────
// CONFIGURATION BASE DE DONNÉES
// Hôte distant OVH : portfoo521.mysql.db
// ─────────────────────────────────────────────
$host       = "portfoo521.mysql.db"; // hôte MySQL fourni par ton hébergeur
$dbname     = "portfoo521";          // nom de la base de données
$dbuser     = "portfoo521";          // nom d'utilisateur MySQL (souvent identique au nom de la BDD chez OVH)
$dbpassword = "A1b2c3d4dtlk";   // ← remplace par ton vrai mot de passe MySQL

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ─────────────────────────────────────────────
// CONNEXION À LA BASE DE DONNÉES
// ─────────────────────────────────────────────
try {
    $pdo = new PDO($dsn, $dbuser, $dbpassword, $options);
} catch (PDOException $e) {
    echo json_encode(["status" => 500, "message" => "Erreur serveur : connexion impossible."]);
    exit;
}

// ─────────────────────────────────────────────
// VÉRIFICATION MÉTHODE HTTP
// ─────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => 405, "message" => "Méthode non autorisée. Utilisez POST."]);
    exit;
}

// ─────────────────────────────────────────────
// RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
// ─────────────────────────────────────────────
$login    = isset($_POST['login'])    ? trim($_POST['login'])    : null;
$password = isset($_POST['password']) ? trim($_POST['password']) : null;

if (!$login || !$password) {
    echo json_encode(["status" => 400, "message" => "Champs requis manquants."]);
    exit;
}

// ─────────────────────────────────────────────
// VALIDATION FORMAT EMAIL
// ─────────────────────────────────────────────
if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => 400, "message" => "Adresse e-mail invalide."]);
    exit;
}

// ─────────────────────────────────────────────
// REQUÊTE SQL — récupération du visiteur par login
// ─────────────────────────────────────────────
$sql = $pdo->prepare("SELECT id, nom, prenom, login, password, role FROM visiteur WHERE login = :login");
$sql->execute(['login' => $login]);
$user = $sql->fetch();

// ─────────────────────────────────────────────
// VÉRIFICATION DU MOT DE PASSE HASHÉ
// ─────────────────────────────────────────────
if ($user && password_verify($password, $user['password'])) {

    // Génération d'un token d'authentification
    $token = bin2hex(openssl_random_pseudo_bytes(32)); // 64 caractères hex

    // (Optionnel) Stocker le token en base si vous avez une colonne "token"
    // $stmt = $pdo->prepare("UPDATE visiteur SET token = :token WHERE id = :id");
    // $stmt->execute(['token' => $token, 'id' => $user['id']]);

    echo json_encode([
        "status"  => 200,
        "message" => "Connexion réussie.",
        "token"   => $token,
        "user"    => [
            "id"     => $user['id'],
            "nom"    => $user['nom'],
            "prenom" => $user['prenom'],
            "login"  => $user['login'],
            "role"   => $user['role']
        ]
    ]);

} else {
    echo json_encode(["status" => 401, "message" => "Identifiants incorrects."]);
}

// ─────────────────────────────────────────────
// FERMETURE CONNEXION
// ─────────────────────────────────────────────
$pdo = null;
