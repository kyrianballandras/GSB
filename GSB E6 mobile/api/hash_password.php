<?php
/**
 * hash_password.php — Outil pour générer un hash de mot de passe
 *
 * UTILISATION :
 *   Ouvrez ce fichier dans votre navigateur (via WAMP/XAMPP).
 *   Tapez le mot de passe en clair, récupérez le hash,
 *   et copiez-le dans votre base de données.
 *
 * ⚠️ SUPPRIMEZ CE FICHIER en production (sur le vrai serveur) !
 */

$hash = '';
$motDePasse = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['mot_de_passe'])) {
    $motDePasse = $_POST['mot_de_passe'];
    $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Générateur de hash</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; max-width: 600px; }
        input[type=text] { width: 100%; padding: 10px; font-size: 14px; margin: 10px 0; box-sizing: border-box; }
        button { padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .result { background: #f0f0f0; padding: 15px; border-radius: 6px; margin-top: 20px; word-break: break-all; }
        .warning { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Générateur de hash de mot de passe</h2>
    <p class="warning">⚠️ Supprimez ce fichier en production !</p>

    <form method="post">
        <label>Mot de passe en clair :</label>
        <input type="text" name="mot_de_passe" value="<?= htmlspecialchars($motDePasse) ?>" placeholder="Ex: MonMotDePasse123">
        <button type="submit">Générer le hash</button>
    </form>

    <?php if ($hash): ?>
    <div class="result">
        <p><strong>Hash à copier dans la base de données :</strong></p>
        <code><?= htmlspecialchars($hash) ?></code>
        <p style="margin-top:15px;">Commande SQL à utiliser :</p>
        <code>
            INSERT INTO clients (Nom, Email, Password) VALUES ('Prénom Nom', 'email@example.com', '<?= htmlspecialchars($hash) ?>');
        </code>
    </div>
    <?php endif; ?>
</body>
</html>
