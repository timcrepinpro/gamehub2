-- Création de la base de données GameHub
CREATE DATABASE IF NOT EXISTS gamehub CHARACTER SET utf8 COLLATE utf8_general_ci;
USE gamehub;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    login    VARCHAR(50)  NOT NULL UNIQUE,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table des jeux
CREATE TABLE IF NOT EXISTS games (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(150) NOT NULL,
    genre       VARCHAR(50)  NOT NULL,
    description TEXT,
    image       VARCHAR(200),
    user_id     INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
