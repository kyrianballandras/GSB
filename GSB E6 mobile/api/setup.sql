-- ============================================================
-- setup.sql — Création de la base de données et de la table
-- À exécuter dans phpMyAdmin ou en ligne de commande MySQL
-- ============================================================

-- 1. Créer la base de données (si elle n'existe pas déjà)
CREATE DATABASE IF NOT EXISTS ma_base
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ma_base;

-- 2. Créer la table clients
CREATE TABLE IF NOT EXISTS clients (
    ID       INT AUTO_INCREMENT PRIMARY KEY,
    Nom      VARCHAR(100)  NOT NULL,
    Email    VARCHAR(150)  NOT NULL UNIQUE,
    Password VARCHAR(255)  NOT NULL,   -- stocke le hash (jamais le mot de passe en clair !)
    token    VARCHAR(64)   DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- IMPORTANT : les mots de passe doivent être hashés avec PHP.
-- Ne PAS insérer de mots de passe en clair ici.
-- Utilisez hash_password.php pour générer les hashs.
-- ============================================================

-- Exemple d'insertion avec hash pré-généré (mot de passe : "1234")
-- INSERT INTO clients (Nom, Email, Password) VALUES
-- ('Jean Dupont',  'jean@example.com',  '$2y$10$VOTRE_HASH_ICI'),
-- ('Julie Dupont', 'julie@example.com', '$2y$10$VOTRE_HASH_ICI');
