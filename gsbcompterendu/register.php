<?php
session_start();
require_once "bdd.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST["lastname"]);
    $prenom = trim($_POST["firstname"]);
    $login = trim($_POST["email"]);
    $password_raw = $_POST["password"];

    if ($nom === "" || $prenom === "" || $login === "" || $password_raw === "") {
        $errors[] = "Tous les champs sont requis.";
    }

    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (strlen($password_raw) < 6) {
        $errors[] = "Mot de passe trop court (min 6).";
    }
//table visiteur a crée 
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM visiteur WHERE login = ? LIMIT 1");
        $check->execute([$login]);
        if ($check->fetch()) {
            $errors[] = "Email déjà utilisé.";
        } else {
            $hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO visiteur (nom, prenom, login, password, role)
                                     VALUES (?, ?, ?, ?, 'visiteur')");
            $insert->execute([$nom, $prenom, $login, $hash]);
            $success = "Compte créé ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-4 p-4 border rounded bg-white shadow">

        <h3 class="text-center mb-4">Créer un compte</h3>
        <img src="assets/image/logo.jpg" alt=" logo" width="80" height="80">

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <a href="login.php" class="btn btn-success w-100">Se connecter</a>
        <?php else: ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Prénom</label>
                <input type="text" name="firstname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="lastname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email / Login</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">Créer le compte</button>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
