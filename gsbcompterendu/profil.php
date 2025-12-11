<?php
session_start();
require_once "bdd.php"; 

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
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
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand h1">Profil (GSB)</span>
        <a href="welcome.php" class="btn btn-warning float-end">Retour</a>
    </div>
</nav>
<h1>Profil</h1>
