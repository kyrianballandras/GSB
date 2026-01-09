<?php
session_start();
require_once "bdd.php"; 

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
} 
// ajout du produit: accessible aux responsable / admin
$message = '';
$role = $_SESSION['role'] ?? '';
if (in_array($role, ['responsable','administrateur']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_produit'])) {
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
    <title>Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/produits.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand h1">Produit (GSB)</span>
        <a href="welcome.php" class="btn btn-warning float-end">Retour</a>
    </div>
</nav>

    <table class="table table-striped table-sm table-custom">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Composition</th>
            <th>Effets</th>
            <th>Contre-indications</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = $pdo->query("SELECT id, nom, composition, effets, contre_indications FROM produit ORDER BY nom");
        while ($row = $sql->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['nom']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['composition'])) ?></td>
                <td><?= nl2br(htmlspecialchars($row['effets'])) ?></td>
                <td><?= nl2br(htmlspecialchars($row['contre_indications'])) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
    </table>
</div>
<div class="container">
    <h1>Produits</h1>
    <p>Liste des produits disponibles :</p>
    <?php if (in_array($role, ['responsable','admin','administrateur'])): ?>
        <?= $message ?>
        <div style="margin-bottom:18px;">
            <form method="post" class="mb-3">
                <div style="margin-bottom:8px;">
                    <label>Nom</label><br>
                    <input type="text" name="nom" required style="width:100%; padding:8px;" />
                </div>
                <div style="margin-bottom:8px;">
                    <label>Composition</label><br>
                    <textarea name="composition" rows="2" style="width:100%; padding:8px;"></textarea>
                </div>
                <div style="margin-bottom:8px;">
                    <label>Effets</label><br>
                    <textarea name="effets" rows="2" style="width:100%; padding:8px;"></textarea>
                </div>
                <div style="margin-bottom:8px;">
                    <label>Contre-indications</label><br>
                    <textarea name="contre_indications" rows="2" style="width:100%; padding:8px;"></textarea>
                </div>
                <button type="submit" name="add_produit" class="boutton">Ajouter le produit</button>
            </form>
        </div>
    <?php endif; ?>
<style>
   .container {
    max-width: 900px; /*tableau*/
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
.alert-danger {
    background: #fdecea;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.alert-success {
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

.table-custom {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.table-custom th, .table-custom td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.table-custom th {
    background-color: #f8f9fa;
    color: #333;
    font-weight: 600;
}
.table-custom tr:hover { background-color: #fcfcfc; }

</style>


</body>
</html>
