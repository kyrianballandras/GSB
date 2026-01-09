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
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $login    = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleUser = $_POST["role"] ?? "visiteur";

    /* Vérifications */
    if ($nom == "" || $prenom == "" || $login == "" || $password == "") {
        $errors[] = "Tous les champs doivent être remplis.";
    }

    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    /* Insertion */
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST["user_id"];
    try {
        $sql = "DELETE FROM visiteur WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $success = "Utilisateur supprimé avec succès.";
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la suppression de l'utilisateur.";
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les utilisateurs</title>
</head>
<body>
    <div class="container mt-5">
        <h1 class="titres">Gérer les utilisateurs</h1>
        <a href="welcome.php"><button class="retor" style="padding: 12px 24px; font-size: 16px;">Retour</button>
</a>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-edanger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
            <?php if ($success): ?>
                <div class="alert alert-sssuccess"><?= htmlspecialchars($success) ?></div>
             <?php endif; ?>
        <table class="table ">
            <thead>
                <tr>
                    <th>ID</th> 
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Login</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT id, nom, prenom, login, role FROM visiteur ORDER BY nom ASC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['nom']) ?></td>
                        <td><?= htmlspecialchars($user['prenom']) ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                <a href="admin_modfi_user.php?user_id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-warning btn-sm">Modifier</a>
                            </form>
                        </td>
                    </tr>
                 <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <style>
       
        .container {
            max-width: 900px; /*tabbleau*/
            margin: 40px auto;
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #3498db;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-e    danger {
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }.alert-sssuccess {
            background: #eaf6ff;
            color: #1f618d;
            border: 1px solid #b8daff;
        }

        

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .btn-primary, .btn-secondary {
            background: #3498db;
            color: #fff;
            margin-bottom: 20px;
        }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        
        .btn:hover { opacity: 0.8; }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        .table tr:hover {
            background-color: #fcfcfc;
        }
    </style>
</body>
</html>