<?php
session_start();
require_once "bdd.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($login === "" || $password === "") {
        $errors[] = "Tous les champs sont requis.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM visiteur WHERE login = ? LIMIT 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user["password"])) {
            $errors[] = "Email ou mot de passe incorrect.";
        } else {
            header("Location: welcome.php");
            $_SESSION["id"] = $user["id"];
            $_SESSION["name"] = $user["prenom"];
            $_SESSION["surname"] = $user["nom"];
            $_SESSION["role"] = $user["role"];

        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-4 p-4 border rounded bg-white shadow">

        <h3 class="text-center mb-4">Connexion</h3>
        <img src="assets/image/logo.jpg" alt=" logo" width="200" height="200" >

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email / Login</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button class="btn btn-primary w-100">Se connecter</button>
        </form>

    </div>
</div>

</body>
</html>
