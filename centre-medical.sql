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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `centre-medical`
--

-- --------------------------------------------------------

--
-- Structure de la table `infos`
--

CREATE TABLE `infos` (
  `info_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `infos`
--

INSERT INTO `infos` (`info_id`, `patient_id`, `title`, `description`) VALUES
(1, 2, 'Vos vaccins ne sont pas à jour !', 'Il vous est recommandé de mettre à jour vos vaccins en prenant rendez-vous avec votre médecin.');

-- --------------------------------------------------------

--
-- Structure de la table `rdv2`
--

CREATE TABLE `rdv2` (
  `rdv_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `place` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rdv2`
--

INSERT INTO `rdv2` (`rdv_id`, `patient_id`, `doctor_id`, `title`, `date`, `place`) VALUES
(1, 2, 5, 'Mise à jour des vaccins', '2025-06-23', 'Nancy'),
(3, 2, 5, 'test', '2025-05-28', 'Nancy'),
(4, 2, 5, 'azerty', '2025-05-29', 'Nancy');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `users_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `doctor` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`users_id`, `username`, `email`, `password`, `doctor`) VALUES
(2, 'adminThéo', 'theo.collin054@gmail.com', '$2y$10$3KiZ0sd1hx/pmHbO.9oe/ewommL/zliA.AKmDkmp50WrUPkL1.IB.', 0),
(4, 'admin', 'admin@gmail.com', '$2y$10$E3PukcsjOtwWHaiS1a4Ggu2GUsgqDvtCXZqQCrs6hj/QnL2gjn3qy', 0),
(5, 'Dr Maboul', 'maboul@gmail.com', '$2y$10$Ug7PnAD18P.InNNGx1TVLOVmp6xqptk6DHUAp7e1b8lbI1Bo1dBu.', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `infos`
--
ALTER TABLE `infos`
  ADD PRIMARY KEY (`info_id`),
  ADD KEY `user_id` (`patient_id`);

--
-- Index pour la table `rdv2`
--
ALTER TABLE `rdv2`
  ADD PRIMARY KEY (`rdv_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `infos`
--
ALTER TABLE `infos`
  MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `rdv2`
--
ALTER TABLE `rdv2`
  MODIFY `rdv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `infos`
--
ALTER TABLE `infos`
  ADD CONSTRAINT `infos_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`users_id`);

--
-- Contraintes pour la table `rdv2`
--
ALTER TABLE `rdv2`
  ADD CONSTRAINT `rdv2_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`users_id`),
  ADD CONSTRAINT `rdv2_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`users_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
