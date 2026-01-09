<?php
session_start();
require_once "bdd.php"; 

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT nom, prenom, role FROM visiteur WHERE id = ?');
$stmt->execute([$_SESSION['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'Utilisateur introuvable';
    exit;
} 


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="profil-gsb">
        <h1>Profil (GSB)</h1>
        <a href="welcome.php"><button class="boutton">Retour</button></a>
    </div>
    </nav>
    <div class="gsb">
        <h2>Profil</h2>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role'] ?? '') ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
    </div>
    <style>
        .profil{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin:20px;
            padding:10px;
            border-bottom:2px solid #3498db;
        }
        
        h1{
            font-family:Arial,sans-serif;
            margin:0;
            color:#3498db;
            font-size:32px;
            text-align: center;
        }
        
        .boutton{
            background-color:#3498db;
            color:white;
            padding:10px 16px;
            font-size:14px;
            border:none;
            border-radius:6px;
            cursor:pointer;
        }
        
        .gsb{
            background-color:#f9f9f9;
            border:1px solid #ddd;
            padding:20px;
            border-radius:8px;
            max-width:400px;
            margin:20px auto;
            font-family:Arial,sans-serif;
        }
    </style>
</body>
</html>
