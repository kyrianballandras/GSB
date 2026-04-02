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
    </div>
    </nav>
    <div class="gsb">
        <h2>Profil</h2>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role'] ?? '') ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
    </div>
     <a href="welcome.php"><button class="boutton">Retour</button></a>
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
            color:#fff;
    background-color: #3498db;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    margin-bottom:15px;
    text-align: center;
        }
        
        .boutton{
     display: flex;
      margin: 15px auto;
     padding: 12px 24px;
     font-size: 25px;
     background: #3498db;
     color: white;
     border: none;
     border-radius: 8px;
     cursor: pointer;
     transition: 0.2s;
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
