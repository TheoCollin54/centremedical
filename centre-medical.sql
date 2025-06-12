-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 02 juin 2025 à 08:33
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `centremedical`
--

CREATE DATABASE centremedical;

USE centremedical;


-- --------------------------------------------------------

--
-- Structure de la table `infos`
--

CREATE TABLE infos (
  info_id int(11) NOT NULL,
  title varchar(100) NOT NULL,
  description varchar(255) NOT NULL,
  PRIMARY KEY (info_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --
-- -- Déchargement des données de la table `infos`
-- --

-- INSERT INTO `infos` (`info_id`, `patient_id`, `title`, `description`) VALUES
-- (1, 2, `Vos vaccins ne sont pas à jour !`, `Il vous est recommandé de mettre à jour vos vaccins en prenant rendez-vous avec votre médecin.`);

-- --------------------------------------------------------

--
-- Structure de la table `rdv2`
--

CREATE TABLE rdv2 (
  rdv_id int(11) NOT NULL,
  patient_nom varchar(100) NOT NULL,
  patient_prenom varchar(100) NOT NULL,
  patient_tel varchar(10) NOT NULL,
  num_secu varchar(15) NOT NULL,
  doctor_id int(11) NOT NULL,
  date date NOT NULL,
  PRIMARY KEY (rdv_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rdv2`
--

INSERT INTO rdv2 (rdv_id, patient_nom, patient_prenom, patient_tel, num_secu, doctor_id, date) VALUES
(1, 'Dupont', 'Jean', '0708093949', '19847582957481928', 1, '2025-06-23'),
(2, 'Martin', 'Claire', '0601020304', '284759382000112', 3, '2025-05-28'),
(3, 'Lemoine', 'Julien', '0623456789', '123456789123456', 1, '2025-05-29');

-- --------------------------------------------------------

--
-- Structure de la table `users'
--

CREATE TABLE users (
  users_id int(11) NOT NULL,
  username varchar(50) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  speciality varchar(255),
  PRIMARY KEY (users_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table 'users'
--

INSERT INTO users (users_id, username, email, password, speciality) VALUES
(1, 'adminThéo', 'theo.collin054@gmail.com', '$2y$10$3KiZ0sd1hx/pmHbO.9oe/ewommL/zliA.AKmDkmp50WrUPkL1.IB.','dentiste'),
(2, 'admin', 'admin@gmail.com', '$2y$10$E3PukcsjOtwWHaiS1a4Ggu2GUsgqDvtCXZqQCrs6hj/QnL2gjn3qy', NULL),
(3, 'Dr Maboul', 'maboul@gmail.com', '$2y$10$Ug7PnAD18P.InNNGx1TVLOVmp6xqptk6DHUAp7e1b8lbI1Bo1dBu.','dermatologue');

--
-- AUTO_INCREMENT pour la table `infos`
--

ALTER TABLE infos
  MODIFY info_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT pour la table `rdv2`
--
ALTER TABLE rdv2
  MODIFY rdv_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE users
  MODIFY users_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- Relation entre `rdv2` pour la table `users` (clé étrangère `doctor_id` faisant référence à `users_id`)
--
ALTER TABLE rdv2
  ADD CONSTRAINT `rdv2_ibfk_2` FOREIGN KEY (doctor_id) REFERENCES users (users_id);
COMMIT;