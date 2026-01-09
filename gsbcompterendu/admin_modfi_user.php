<?php   
session_start();
require_once "bdd.php";
$errors = [];
$success = '';
/*   admin  acced */
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
$role = strtolower((string)($_SESSION['role'] ?? ''));
if ($role != "admin" && $role != "administrateur") {
    header("Location: login.php");
    exit;
}

/* Traite du formulaire */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // suppression
    if (isset($_POST['delete_user']) && !empty($_POST['user_id'])) {
        $userId = (int) $_POST['user_id'];
        try {
            $sql = "DELETE FROM visiteur WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $success = "Utilisateur supprimé avec succès.";
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
        }
    }

    // modification du rôle
    if (isset($_POST['change_role']) && !empty($_POST['user_id']) && isset($_POST['new_role'])) {
        $userId = (int) $_POST['user_id'];
        $newRole = trim($_POST['new_role']);
        if ($newRole === '') {
            $errors[] = 'Rôle invalide.';
        } else {
            try {
                $sql = "UPDATE visiteur SET role = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$newRole, $userId]);
                $success = "Rôle mis à jour avec succès.";
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
            }
         }
        }
}
?>  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur</title>
</head>
<body>
    <div class="container mt-5">
        <h1>Modifier l'utilisateur</h1>




        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>



        <form method="POST" action="admin_modfi_user.php">
            <div class="mb-3">
                <label for="user_id" class="form-label">ID de l'utilisateur :</label>
                <input type="number" class="form-control" id="user_id" name="user_id" required></div>
            <div class="mb-3">
                <label for="new_role" class="form-label">Nouveau rôle</label>
                <select name="new_role" id="new_role" class="form-control">
                    <option value="visiteur">Visiteur</option>
                    <option value="administrateur">Administrateur</option>
                    <option value="responsable">Responsable</option>
                    <option value="delegue">Délégué</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <button type="submit" name="change_role" class="btn btn-primary">Modifier le rôle</button>
                <a href="admin_dashboard.php" class="btn btn-secondary" style="margin-left:8px">Retour au tableau de bord</a></div>
        </form>
    </div>
    <style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f2f6fb;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 480px;
    margin: 80px auto;
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

.form-label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
    color: #444;
}

.form-control {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 15px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

.mb-3 {
    margin-bottom: 18px;
}

.btn {
    padding: 9px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3498db;
    color: #fff;
}

.btn-primary:hover {
    background: #2e86c1;
}

.btn-secondary {
    background: #7f8c8d;
    color: #fff;
}

.btn-secondary:hover {
    background: #6c7a7a;
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
}

.alert-success {
    background: #eaf6ff;
    color: #1f618d;
}
</style>

</body>
</html>