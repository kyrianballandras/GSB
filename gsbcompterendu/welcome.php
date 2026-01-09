<?php
session_start();
if
(!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}
require_once "bdd.php";

$role_raw = $_SESSION['role'] ?? '';
// des statistique (pourcentage) si role responsable/admin
$pctThis = 0; $pctLast = 0; $thisMonth = 0; $lastMonth = 0; $total = 0;
if (in_array($role_raw, ['responsable','admin','administrateur'])) {
    try {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM compterendu")->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM compterendu WHERE MONTH(date_visite)=MONTH(CURDATE()) AND YEAR(date_visite)=YEAR(CURDATE()))");
        $stmt->execute();
        $thisMonth = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM compterendu WHERE MONTH(date_visite)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(date_visite)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
        $stmt->execute();
        $lastMonth = (int)$stmt->fetchColumn();
        if ($total > 0) {
            $pctThis = round($thisMonth / $total * 100, 1);
            $pctLast = round($lastMonth / $total * 100, 1);
        }
    } catch (Exception $e) {
        // ignore erreurs de stats
    }
}
$name  = htmlspecialchars($_SESSION['name'] ?? '');
$sname = htmlspecialchars($_SESSION['surname'] ?? '');
$role  = htmlspecialchars($_SESSION['role'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
    <body>
    <aside class="bar">
        <div class="brand">
                <img src="assets/image/logo.jpg" alt=" logo" width="180" height="50" >
            </a>
             <div>
             <h1>GSB</h1>
            <div style="font-size:0.85rem;color:#1288">Bienvenue</div>
             </div>
         </div>

        <nav aria-label="Menu principal">
            <ul class="nav-list">
                <li><a href="cr_new.php"> Saisit de Rapport</a></li>
                 <?php if ($role === 'admin' || $role === 'administrateur'): ?>
                <li><a href="cr_list.php"> Rapport</a></li>
                  <?php endif; ?>
                
                  <?php if ($role === 'responsable' || $role === 'responsable'): ?>
                <li><a href="cr_list.php"> Rapport</a></li> 
                    <?php endif; ?>
                     <?php if ($role === 'delegue' || $role === 'delegue'): ?>
                <li><a href="cr_list.php"> Rapport</a></li> 
                    <?php endif; ?>

                <li><a href="praticiens.php"> praticiens</a></li>
                <li><a href="produits.php"> produits</a></li>
                <li><a href="profil.php"> Mon profil</a></li>
                 <?php if ($role === 'admin' || $role === 'administrateur'): ?>
                <li><a href="admin_add_user.php"> Ajouter un utilisateur</a></li>
                <lil><a href=admin_dashboard.php> Gerer-Utilisateur</a><lil>
                 <?php endif; ?>
            </ul>
        </nav>

        <div style="margin-top:auto">
            <div style="font-size:0.85rem;color:#600">Connecté en tant que</div>
            <div style="font-weight:600;margin-top:6px"><?= $name . ' ' . $sname ?></div>
            <div class="role"><?= $role ?></div>
            <div class="menu-actions" style="margin-top:12px">
            <a class="gsb " href="profil.php">Mon profil</a>
            <form action="logout.php" method="post" style="margin:0">
            <button class="gsb dec" type="submit"> Déconnexion</button>
            </form>
            </div>
        </div>
    </aside>

    <main class="content">
        <section class="h33rd welcome">
            <div>                <h2>Bonjour <?= $name . ' ' . $sname ?></h2>
            <div class="role">Rôle : <strong><?= $role ?></strong></div>
            </div>
            <div style="min-width:140px;text-align:right">
            <a class="gsb" href="cr_new.php">Nouvelle saisie</a>
            </div>
        </section>

        <section class="h33rd">
            <h3>Raccourcis </h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
            <a class="gsb " href="cr_list.php">Voir mes comptes-rendus</a>
            <a class="gsb " href="praticiens.php">Liste des praticiens</a>
            <a class="gsb " href="produits.php">Produits</a>
            <?php if ($role === 'admin' || $role === 'administrateur'): ?>
            <a class="admin" href="admin_add_user.php">Ajouter un utilisateur</a>
            <?php endif; ?><!--que les admin qui voient-->
            </div>
        </section>  


        
        <section class="h33rd">
        <h3>Informations</h3>
        <?php if (in_array($role_raw, ['responsable','admin','administrateur'])): ?>
        <div style="display:flex;gap:12px;align-items:center;margin-top:10px;margin-bottom:12px">
            <div style="flex:0 0 220px;padding:12px;border-radius:8px;background:#eef9ff;border:1px solid #d6ecff">
                <div style="font-size:13px;color:#666">Rapports ce mois</div>
                <div style="font-size:22px;font-weight:700;color:#3498db"><?= htmlspecialchars($pctThis) ?>%</div>
                <div style="font-size:12px;color:#666"><?= htmlspecialchars($thisMonth) ?> / <?= htmlspecialchars($total) ?> total</div>
            </div>



            <div style="flex:0 0 220px;padding:12px;border-radius:8px;background:#fff7e6;border:1px solid #ffe7c6">
                <div style="font-size:13px;color:#666">Rapports mois précédent</div>
                <div style="font-size:22px;font-weight:700;color:#f39c12"><?= htmlspecialchars($pctLast) ?>%</div>
                <div style="font-size:12px;color:#666"><?= htmlspecialchars($lastMonth) ?> / <?= htmlspecialchars($total) ?> total</div>
            </div>
        </div>
        <?php endif; ?>
        <p> Bienvenue sur le Projet Appli-CR</p>
        </section>
    </main>
</body>
<head>
    <meta charset="UTF-8">
    <title>Accueil - GSB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: Arial, sans-serif;
            color:#222);
            background:#f5f6fa;
            min-height:100vh;
            display:flex;
        }

            .bar{
            width:260px;
            background:linear-gradient(180deg,#ffffff,#f7fbff);
            border-right:1px solid rgba(0,0,0,0.06);
            padding:22px;
            display:flex;
            flex-direction:column;
            gap:18px;
            flex-shrink:0;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .brand img{
            width:48px;height:33px;border-radius:3px;object-fit:cover;
        }
        .brand h1{font-size:1rem;margin:0}
        nav{margin-top:6px}
        .nav-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px}
        .nav-list a{
            display:block;padding:10px 12px;border-radius:8px;color:#222;text-decoration:none;
            transition:background .15s,transform .12s;
        }
        .nav-list a:hover{background:rgba(52,152,219,0.08);transform:translateX(4px)}
        .nav-list a.active{background:#3498db;color:#fff}

        .content{
            flex:1;padding:28px;
            display:flex;flex-direction:column;gap:20px;
        }
        .h33rd{
            background:#ffffff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(15,23,42,0.04);
        }
        .welcome{
            display:flex;align-items:center;justify-content:space-between;gap:12px;
        }
        .welcome h2{margin:10;font-size:1.25rem}
        .role{color:#66;margin-top:6px;font-size:0.95rem}

        .menu-actions{display:flex;flex-direction:column;gap:10px;margin-top:10px}
        .gsb{
            display:inline-block;padding:10px 12px;border-radius:8px;text-decoration:none;text-align:center;
            background-color: #3498db;color:#fff;font-weight:600;
        }
        .gsb.dec{
            background-color:#e74c3c;
        }   
        .admin{
            display:inline-block;padding:10px 12px;border-radius:8px;text-decoration:none;text-align:center;
            background-color: #3498db;color:#fff;font-weight:600;
        }
       
    </style>
</head>
</html>
