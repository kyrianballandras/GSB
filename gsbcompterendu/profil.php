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
<?php
// Traitement du formulaire d'ajout produit pour les responsables / admins
$message = '';
if (in_array($user['role'] ?? '', ['responsable', 'admin', 'administrateur']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_produit'])) {
    $nom = trim($_POST['nom'] ?? '');
    $composition = trim($_POST['composition'] ?? '');
    $effets = trim($_POST['effets'] ?? '');
    $contre = trim($_POST['contre_indications'] ?? '');

    if ($nom === '') {
        $message = '<div class="alert alert-danger">Le nom du produit est requis.</div>';
    } else {
        try {
            $ins = $pdo->prepare('INSERT INTO produit (nom, composition, effets, contre_indications) VALUES (?, ?, ?, ?)');
            $ins->execute([$nom, $composition, $effets, $contre]);
            $message = '<div class="alert alert-success">Produit ajouté avec succès.</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Erreur lors de l\'ajout : ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
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
    
    <div class="gsb">
        <h2>Profil</h2>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role'] ?? '') ?></p>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
    </div>
    
    <?php if (in_array($user['role'] ?? '', ['responsable', 'admin', 'administrateur'])): ?>
    <div class="gsb" style="max-width:700px; margin:20px auto;">
        <h3>Ajouter un produit</h3>
        <?= $message ?>
        <form method="post">
            <div style="margin-bottom:8px;">
                <label>Nom</label><br>
                <input type="text" name="nom" required style="width:100%; padding:8px;" />
            </div>
            <div style="margin-bottom:8px;">
                <label>Composition</label><br>
                <textarea name="composition" rows="3" style="width:100%; padding:8px;"></textarea>
            </div>
            <div style="margin-bottom:8px;">
                <label>Effets</label><br>
                <textarea name="effets" rows="2" style="width:100%; padding:8px;"></textarea>
            </div>
            <div style="margin-bottom:12px;">
                <label>Contre-indications</label><br>
                <textarea name="contre_indications" rows="2" style="width:100%; padding:8px;"></textarea>
            </div>
            <button type="submit" name="add_produit" class="boutton">Ajouter le produit</button>
        </form>
    </div>
    <?php endif; ?>
    <style>
        .profil-gsb{
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
