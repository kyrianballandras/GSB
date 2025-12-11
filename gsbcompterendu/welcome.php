<?php
session_start();
if
(!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
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
                <li><a href="cr_list.php"> Rapport</a></li>
                <li><a href="praticiens.php"> praticiens</a></li>
                <li><a href="produits.php"> produits</a></li>
                <li><a href="profil.php"> Mon profil</a></li>
            </ul>
        </nav>

        <div style="margin-top:auto">
            <div style="font-size:0.85rem;color:#600">Connecté en tant que</div>
            <div style="font-weight:600;margin-top:6px"><?= $name . ' ' . $sname ?></div>
            <div class="role"><?= $role ?></div>
            <div class="menu-actions" style="margin-top:12px">
            <a class="btn ghost" href="profil.php">Mon profil</a>
            <form action="logout.php" method="post" style="margin:0">
            <button class="btn danger" type="submit"> Déconnexion</button>
            </form>
            </div>
        </div>
    </aside>

    <main class="content">
        <section class="card welcome">
            <div>                <h2>Bonjour <?= $name . ' ' . $sname ?></h2>
            <div class="role">Rôle : <strong><?= $role ?></strong></div>
            </div>
            <div style="min-width:140px;text-align:right">
            <a class="btn" href="cr_new.php">Nouvelle saisie</a>
            </div>
        </section>

        <section class="card">
            <h3>Raccourcis </h3>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
            <a class="btn ghost" href="cr_list.php">Voir mes comptes-rendus</a>
            <a class="btn ghost" href="praticiens.php">Liste des praticiens</a>
            <a class="btn ghost" href="produits.php">Produits</a>
            </div>
        </section>  

        <section class="card">
        <h3>Informations</h3>
        <p> Barre de tâche</p>
        </section>
    </main>
</body>
<head>
    <meta charset="UTF-8">
    <title>Accueil - GSB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{
            --bg:#f5f6fa;
            --card:#ffffff;
            --accent:#3498db;
            --danger:#e74c3c;
            --text:#222;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: Arial, sans-serif;
            color:var(--text);
            background:var(--bg);
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
            width:48px;height:48px;border-radius:8px;object-fit:cover;
        }
        .brand h1{font-size:1rem;margin:0}
        nav{margin-top:6px}
        .nav-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px}
        .nav-list a{
            display:block;padding:10px 12px;border-radius:8px;color:var(--text);text-decoration:none;
            transition:background .15s,transform .12s;
        }
        .nav-list a:hover{background:rgba(52,152,219,0.08);transform:translateX(4px)}
        .nav-list a.active{background:var(--accent);color:#fff}

        /* Main content */
        .content{
            flex:1;padding:28px;
            display:flex;flex-direction:column;gap:20px;
        }
        .card{
            background:var(--card);padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(15,23,42,0.04);
        }
        .welcome{
            display:flex;align-items:center;justify-content:space-between;gap:12px;
        }
        .welcome h2{margin:0;font-size:1.25rem}
        .role{color:#666;margin-top:6px;font-size:0.95rem}

        .menu-actions{display:flex;flex-direction:column;gap:10px;margin-top:10px}
        .btn{
            display:inline-block;padding:10px 12px;border-radius:8px;text-decoration:none;text-align:center;
            background:var(--accent);color:#fff;border:none;cursor:pointer;
        }
        .btn.ghost{background:transparent;color:var(--accent);border:1px solid rgba(52,152,219,0.14)}
        .btn.danger{background:var(--danger)}

        /* responsive: sidebar collapses */
        @media (max-width:820px){
            body{flex-direction:column}
            .sidebar{width:100%;flex-direction:row;align-items:center;padding:12px;gap:12px;overflow:auto}
            .nav-list{flex-direction:row;gap:6px}
            .content{padding:16px}
        }
    </style>
</head>
</html>
