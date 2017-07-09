-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1:3306
-- Généré le: Dimanche 21 Août 2016 à 16:51
-- Version du serveur: 5.5.50-0+deb7u2
-- Version de PHP: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `phpmyadmin` pour l'affichage de la base dans l'onglet "Designer"
--

-- --------------------------------------------------------

--
-- Contenu de la table `pma__designer_coords`
--

INSERT INTO `pma__designer_coords` (`db_name`, `table_name`, `x`, `y`, `v`, `h`) VALUES
('amc-builder', 'candidat', 470, 810, 1, 1),
('amc-builder', 'categorie', 20, 490, 1, 1),
('amc-builder', 'domaine', 20, 50, 1, 1),
('amc-builder', 'epreuve', 1370, 810, 1, 1),
('amc-builder', 'formulaire', 470, 60, 1, 1),
('amc-builder', 'formulaire_question', 1370, 50, 1, 1),
('amc-builder', 'generation', 900, 170, 1, 1),
('amc-builder', 'grade', 20, 1190, 1, 1),
('amc-builder', 'groupe', 900, 1474, 1, 1),
('amc-builder', 'log_connexion', 1790, 1474, 1, 1),
('amc-builder', 'log_epreuve', 2260, 1050, 1, 1),
('amc-builder', 'log_formulaire', 2260, 50, 1, 1),
('amc-builder', 'log_generation', 2260, 850, 1, 1),
('amc-builder', 'log_groupe', 1370, 1474, 1, 1),
('amc-builder', 'log_question', 2260, 240, 1, 1),
('amc-builder', 'log_referentiel', 2260, 1450, 1, 1),
('amc-builder', 'log_reponse', 2260, 440, 1, 1),
('amc-builder', 'log_reservation', 2260, 1250, 1, 1),
('amc-builder', 'log_validation', 2260, 640, 1, 1),
('amc-builder', 'profil', 20, 950, 1, 1),
('amc-builder', 'question', 1370, 290, 1, 1),
('amc-builder', 'question_reponse', 1790, 270, 1, 1),
('amc-builder', 'reponse', 1852, 487, 1, 1),
('amc-builder', 'reservation', 900, 1130, 1, 1),
('amc-builder', 'salle', 20, 1450, 1, 1),
('amc-builder', 'sous_categorie', 20, 700, 1, 1),
('amc-builder', 'sous_domaine', 20, 240, 1, 1),
('amc-builder', 'stage', 470, 480, 1, 1),
('amc-builder', 'stage_candidat', 900, 680, 1, 1),
('amc-builder', 'statut_salle', 470, 1400, 1, 1),
('amc-builder', 'utilisateur', 470, 1060, 1, 1);

-- --------------------------------------------------------

--
-- Contenu de la table `pma__relation`
--

INSERT INTO `pma__relation` (`master_db`, `master_table`, `master_field`, `foreign_db`, `foreign_table`, `foreign_field`) VALUES
('amc-builder', 'log_connexion', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_epreuve', 'id_epreuve', 'amc-builder', 'epreuve', 'id_epreuve'),
('amc-builder', 'log_epreuve', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_formulaire', 'id_formulaire', 'amc-builder', 'formulaire', 'id_formulaire'),
('amc-builder', 'log_formulaire', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_generation', 'id_generation', 'amc-builder', 'generation', 'id_generation'),
('amc-builder', 'log_generation', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_groupe', 'id_groupe', 'amc-builder', 'groupe', 'id_groupe'),
('amc-builder', 'log_groupe', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_question', 'id_question', 'amc-builder', 'question', 'id_question'),
('amc-builder', 'log_question', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_referentiel', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_reponse', 'id_reponse', 'amc-builder', 'reponse', 'id_reponse'),
('amc-builder', 'log_reponse', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_reservation', 'id_reservation', 'amc-builder', 'reservation', 'id_reservation'),
('amc-builder', 'log_reservation', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur'),
('amc-builder', 'log_validation', 'id_formulaire', 'amc-builder', 'formulaire', 'id_formulaire'),
('amc-builder', 'log_validation', 'id_utilisateur', 'amc-builder', 'utilisateur', 'id_utilisateur');

-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
