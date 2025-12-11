<?php
session_start();
require_once "../bdd.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$errors = [];
$success= '';
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $nom=trimp($_POST["nom"]);
    $prenom=trimp($_POST["prenom"]);
    $login=trim($_POST)["Login"]);
    $password_raw=trim($_POST["password"]);
    $role=trim($_POST["role"]);

    if ($nom == ""|| $prenom == "" || $login == "" || $password_raw == "" || $role == ""){
        $errors[]="Tous les champs doivent être remplis.";
    }

    if (!filter_vas($login, FILTER_VALIDATE_EMAIL)){
        $errors[]="Le format de l'adresse e-mail est invalide.";
    }
    if (strlen($password_raw) < 6){
        $errors[]="Le mot de passe doit contenir au moins 6 caractères.";
    }
}