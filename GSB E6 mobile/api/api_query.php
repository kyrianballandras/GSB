<?php
/**
 * api_query.php — Client PHP qui appelle l'API login.php
 * Reçoit les données du formulaire et envoie à l'API.
 */

header("Content-Type: text/html; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Méthode de requête non autorisée.";
    exit;
}

// ─────────────────────────────────────────────
// URL de l'API — à adapter selon votre environnement
// En local (WAMP/XAMPP) :
// $url = 'http://localhost/api/login.php';
// En production (hébergement OVH) :
$url = 'https://portfoliokball.fr/portofolio/gsbcompterendu/loginapi.php'; // ← remplacez par votre URL
// ─────────────────────────────────────────────

$data = [
    'login'    => $_POST['login'],
    'password' => $_POST['password']
];

// Appel cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
curl_close($ch);

// Traitement de la réponse
if ($response === false) {
    echo "<p style='color:red;'>Erreur : impossible de contacter l'API.</p>";
    exit;
}

$responseData = json_decode($response, true);

if ($responseData === null) {
    echo "<p style='color:red;'>Erreur : réponse invalide de l'API.</p>";
    exit;
}

$status = $responseData['status'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Résultat connexion</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; padding: 40px; background: #f0f2f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 420px; width: 100%; }
        h2 { margin-top: 0; }
        .success { color: #2e7d32; }
        .error   { color: #c62828; }
        .badge   { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 13px; background: #e8f5e9; color: #2e7d32; font-weight: bold; }
        code     { background: #f5f5f5; padding: 8px; display: block; border-radius: 4px; word-break: break-all; font-size: 12px; margin-top: 6px; }
        a        { color: #555; font-size: 14px; }
        table    { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td       { padding: 6px 4px; border-bottom: 1px solid #eee; font-size: 14px; }
        td:first-child { color: #888; width: 80px; }
    </style>
</head>
<body>
<div class="box">
<?php if ($status === 200): ?>
    <h2 class="success">✓ Connexion réussie</h2>
    <table>
        <tr><td>Nom</td><td><?= htmlspecialchars($responseData['user']['prenom'] . ' ' . $responseData['user']['nom']) ?></td></tr>
        <tr><td>Login</td><td><?= htmlspecialchars($responseData['user']['login']) ?></td></tr>
        <tr><td>Rôle</td><td><span class="badge"><?= htmlspecialchars($responseData['user']['role']) ?></span></td></tr>
        <tr><td>ID</td><td><?= htmlspecialchars($responseData['user']['id']) ?></td></tr>
    </table>
    <p style="margin-top:16px; font-size:13px; color:#666;">Token d'authentification :</p>
    <code><?= htmlspecialchars($responseData['token']) ?></code>
<?php else: ?>
    <h2 class="error">✗ Échec de la connexion</h2>
    <p><?= htmlspecialchars($responseData['message'] ?? 'Erreur inconnue') ?></p>
<?php endif; ?>
    <br><a href="login.html">← Retour au formulaire</a>
</div>
</body>
</html>
