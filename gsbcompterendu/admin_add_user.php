<?php
session_start();
require_once "bdd.php";

$errors = [];
$success = "";

/* Sécurité : seul un admin peut accéder */
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower($_SESSION['role']);
if ($role != "admin" && $role != "administrateur") {
    header("Location: login.php");
    exit;
}

/* Traitement du formulaire */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $login = trim($_POST["login"]);
    $password = trim($_POST["password"]);
    $roleUser = $_POST["role"] ?? "visiteur";

    /* Vérifications */
    if ($nom == "" || $prenom == "" || $login == "" || $password == "") {
        $errors[] = "Tous les champs doivent être rempli";
    }

    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide";
    }

    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir minimum 6 caractères";
    }

    if (count($errors) == 0) {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO visiteur (nom, prenom, login, password, role)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prenom, $login, $passwordHash, $roleUser]);

            $success = "utilisateur ajouté";

        } catch (PDOException $e) {
            $errors[] = "erreur lors de l'ajout";
        }}}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter un Utilisateur</title>
<link rel="stylesheet" href="assets/css/admin_simple.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary">
  <div class="container-fluid"> <span class="navbar-brand">GSB - Ajouter un utilisateur</span>
    <a href="welcome.php" class="btn btn-light">Accueil</a>
  </div>


</nav>
<div class="container mt-5 col-md-6">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4>Créer un utilisateur</h4></div>
        <div class="card-body">

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required></div> <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label">Email (login)</label>
                    <input type="email" name="login" class="form-control" required> </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required> </div>
                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select">
                        <option value="visiteur">Visiteur</option>
                        <option value="administrateur">Administrateur</option>
                        <option value="responsable">Responsable</option>
                        <option value="delegue">Délégué</option></select></div>
                <button class="btn btn-primary w-100">Créer l'utilisateur</button></form>

<style>
    html,body{height:100%;margin:0;font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;color:#222}
.navbar{background:#2b6ea3;padding:10px}
.navbar .navbar-brand{color:#fff;font-weight:700}
.container{max-width:720px;margin:36px auto;padding:0 16px}
.card{background:#fff;border-radius:8px;padding:18px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
.card-header{background:#2b6ea3;color:#fff;padding:12px;border-radius:6px 6px 0 0}
.card-body{padding:16px}
.form-label{display:block;margin-bottom:6px;font-weight:600}
.form-control{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px}
.form-select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px}
.btn{display:inline-block;padding:10px 14px;border-radius:6px;background:#2b6ea3;color:#fff;border:0;cursor:pointer}
.alert{padding:10px 12px;border-radius:6px;margin-bottom:12px}
.alert-danger{background:#fdecea;color:#8a1f14;border:1px solid #f1b0ab}
.alert-success{background:#edf7ed;color:#1b6d2e;border:1px solid #bfe6bf}

</style>
</body>
</html>