-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1:3306
-- Généré le: Dimanche 22 Juillet 2017 à 14:00
-- Version du serveur: 5.5.35-0+wheezy1
-- Version de PHP: 5.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `amc-builder`
--
CREATE DATABASE IF NOT EXISTS `amc-builder` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `amc-builder`;

-- --------------------------------------------------------

--
-- Structure de la table `candidat`
--

CREATE TABLE IF NOT EXISTS `candidat` (
  `id_candidat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `id_grade` int(11) NOT NULL,
  `nom_candidat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `prenom_candidat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `unite_candidat` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_candidat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_candidat`),
  KEY `id_grade` (`id_grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- RELATIONS POUR LA TABLE `candidat`:
--   `id_grade`
--       `grade` -> `id_grade`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `id_domaine` int(11) DEFAULT NULL,
  `libelle_categorie` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_categorie` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_categorie` date DEFAULT NULL,
  `date_fin_categorie` date DEFAULT NULL,
  `date_modification_categorie` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categorie`),
  KEY `id_domaine` (`id_domaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `categorie`:
--   `id_domaine`
--       `domaine` -> `id_domaine`
--

--
-- Contenu de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `id_domaine`, `libelle_categorie`, `description_categorie`, `date_debut_categorie`, `date_fin_categorie`, `date_modification_categorie`) VALUES
(0, NULL, 'aucun', NULL, NULL, NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `controle`
--

CREATE TABLE IF NOT EXISTS `controle` (
  `id_controle` int(11) NOT NULL AUTO_INCREMENT,
  `id_epreuve` int(11) NOT NULL,
  `date_debut_controle` datetime DEFAULT NULL,
  `id_candidat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `modifiable_controle` tinyint(1) NOT NULL DEFAULT '1',
  `date_modification_controle` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_controle`),
  KEY `id_epreuve` (`id_epreuve`),
  KEY `id_candidat` (`id_candidat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `controle`:
--   `id_epreuve`
--       `epreuve` -> `id_epreuve`
--   `id_candidat`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `controle_reponse_candidat`
--

CREATE TABLE IF NOT EXISTS `controle_reponse_candidat` (
  `id_controle_reponse_candidat` int(11) NOT NULL AUTO_INCREMENT,
  `id_controle` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `ordre_question` int(3) NOT NULL,
  `libre_reponse_candidat` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `liste_reponses_candidat` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_correcteur` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resultat_reponse_candidat` float DEFAULT NULL,
  `date_modification_controle_reponse_candidat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_controle_reponse_candidat`),
  KEY `id_controle` (`id_controle`),
  KEY `id_question` (`id_question`),
  KEY `id_correcteur` (`id_correcteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `controle_reponse_candidat`:
--   `id_controle`
--       `controle` -> `id_controle`
--   `id_question`
--       `question` -> `id_question`
--   `id_correcteur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `domaine`
--

CREATE TABLE IF NOT EXISTS `domaine` (
  `id_domaine` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_domaine` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_domaine` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_domaine` date DEFAULT NULL,
  `date_fin_domaine` date DEFAULT NULL,
  `date_modification_domaine` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_domaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Contenu de la table `domaine`
--

INSERT INTO `domaine` (`id_domaine`, `libelle_domaine`, `description_domaine`, `date_debut_domaine`, `date_fin_domaine`, `date_modification_domaine`) VALUES
(0, 'aucun', NULL, NULL, NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `epreuve`
--

CREATE TABLE IF NOT EXISTS `epreuve` (
  `id_epreuve` int(11) NOT NULL AUTO_INCREMENT,
  `type_epreuve` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `libelle_epreuve` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_epreuve` date NOT NULL,
  `heure_epreuve` time NOT NULL,
  `duree_epreuve` int(3) NOT NULL,
  `id_stage` int(11) NOT NULL,
  `id_generation` int(11) NOT NULL,
  `liste_salles_epreuve` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `table_affectation_epreuve` tinyint(1) COLLATE utf8_unicode_ci DEFAULT '0',
  `table_aleatoire_epreuve` tinyint(1) COLLATE utf8_unicode_ci DEFAULT '0',
  `id_valideur` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_modification_epreuve` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_epreuve`),
  KEY `id_stage` (`id_stage`),
  KEY `id_generation` (`id_generation`),
  KEY `epreuve_utilisateur` (`id_valideur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `epreuve`:
--   `id_generation`
--       `generation` -> `id_generation`
--   `id_stage`
--       `stage` -> `id_stage`
--   `id_valideur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `formulaire`
--

CREATE TABLE IF NOT EXISTS `formulaire` (
  `id_formulaire` int(11) NOT NULL AUTO_INCREMENT,
  `id_domaine` int(11) NOT NULL,
  `id_sous_domaine` int(11) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `id_sous_categorie` int(11) DEFAULT NULL,
  `titre_formulaire` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `presentation_formulaire` text COLLATE utf8_unicode_ci NOT NULL,
  `strict_formulaire` tinyint(1) NOT NULL DEFAULT '0',
  `note_finale_formulaire` int(3) NOT NULL,
  `penalite_formulaire` float NOT NULL,
  `id_redacteur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `validation_formulaire` tinyint(1) NOT NULL,
  `id_valideur` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_modification_formulaire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_formulaire`),
  KEY `id_sous_domaine` (`id_sous_domaine`),
  KEY `id_categorie` (`id_categorie`),
  KEY `id_sous_categorie` (`id_sous_categorie`),
  KEY `id_redacteur` (`id_redacteur`),
  KEY `id_valideur` (`id_valideur`),
  KEY `id_domaine` (`id_domaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `formulaire`:
--   `id_categorie`
--       `categorie` -> `id_categorie`
--   `id_domaine`
--       `domaine` -> `id_domaine`
--   `id_redacteur`
--       `utilisateur` -> `id_utilisateur`
--   `id_sous_categorie`
--       `sous_categorie` -> `id_sous_categorie`
--   `id_sous_domaine`
--       `sous_domaine` -> `id_sous_domaine`
--   `id_valideur`
--       `utilisateur` -> `id_utilisateur`
--

--
-- Contenu de la table `formulaire`
--

INSERT INTO `formulaire` (`id_formulaire`, `id_domaine`, `id_sous_domaine`, `id_categorie`, `id_sous_categorie`, `titre_formulaire`, `presentation_formulaire`, `strict_formulaire`, `note_finale_formulaire`, `penalite_formulaire`, `id_redacteur`, `validation_formulaire`, `id_valideur`, `date_modification_formulaire`) VALUES
(0, 0, 0, 0, 0, 'Système', 'Entré réservée au système', 0, 0, 0, '0', 0, '0', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `formulaire_question`
--

CREATE TABLE IF NOT EXISTS `formulaire_question` (
  `id_formulaire_question` int(11) NOT NULL AUTO_INCREMENT,
  `id_formulaire` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `date_modification_question` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_formulaire_question`),
  KEY `id_formulaire` (`id_formulaire`),
  KEY `id_question` (`id_question`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `formulaire_question`:
--   `id_formulaire`
--       `formulaire` -> `id_formulaire`
--   `id_question`
--       `question` -> `id_question`
--

--
-- Contenu de la table `formulaire_question`
--

INSERT INTO `formulaire_question` (`id_formulaire_question`, `id_formulaire`, `id_question`, `date_modification_question`) VALUES
(0, 0, 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `generation`
--

CREATE TABLE IF NOT EXISTS `generation` (
  `id_generation` int(11) NOT NULL AUTO_INCREMENT,
  `id_formulaire` int(11) NOT NULL,
  `langue_generation` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `format_generation` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `separate_generation` tinyint(1) NOT NULL DEFAULT '0',
  `seed_generation` int(7) NOT NULL,
  `nom_epreuve_generation` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_epreuve_generation` date NOT NULL,
  `consignes_generation` text COLLATE utf8_unicode_ci NOT NULL,
  `exemplaires_generation` int(3) NOT NULL,
  `code_candidat_generation` int(1) NOT NULL,
  `cartouche_candidat_generation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_valideur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_generation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_generation`),
  KEY `id_formulaire` (`id_formulaire`,`id_valideur`),
  KEY `id_valideur` (`id_valideur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `generation`:
--   `id_formulaire`
--       `formulaire` -> `id_formulaire`
--   `id_valideur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE IF NOT EXISTS `grade` (
  `id_grade` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_grade` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `libelle_court_grade` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `description_grade` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordre_grade` int(2) NOT NULL,
  `date_debut_grade` date DEFAULT NULL,
  `date_fin_grade` date DEFAULT NULL,
  `date_modification_grade` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_grade`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Contenu de la table `grade`
--

INSERT INTO `grade` (`id_grade`, `libelle_grade`, `libelle_court_grade`, `description_grade`, `ordre_grade`, `date_debut_grade`, `date_fin_grade`, `date_modification_grade`) VALUES
(0, 'Aucun', '-', NULL, 0, NULL, NULL, '0000-00-00 00:00:00'),
(1, 'N1A', 'N1A', NULL, 1, NULL, NULL, '0000-00-00 00:00:00'),
(2, 'N2A', 'N2A', NULL, 2, NULL, NULL, '0000-00-00 00:00:00'),
(3, 'N3A', 'N3A', NULL, 3, NULL, NULL, '0000-00-00 00:00:00'),
(4, 'TSEF', 'TSEF', NULL, 4, NULL, NULL, '0000-00-00 00:00:00'),
(5, 'GENDARME ADJOINT', 'GAV', NULL, 5, NULL, NULL, '0000-00-00 00:00:00'),
(6, 'BRIGADIER', 'BRI', NULL, 6, NULL, NULL, '0000-00-00 00:00:00'),
(7, 'BRIGADIER CHEF', 'BRC', NULL, 7, NULL, NULL, '0000-00-00 00:00:00'),
(8, 'MARECHAL DES LOGIS', 'MDL', NULL, 8, NULL, NULL, '0000-00-00 00:00:00'),
(9, 'GENDARME', 'GND', NULL, 9, NULL, NULL, '0000-00-00 00:00:00'),
(10, 'GARDE', 'GRD', NULL, 10, NULL, NULL, '0000-00-00 00:00:00'),
(11, 'MARECHAL DES LOGIS CHEF', 'MDC', NULL, 11, NULL, NULL, '0000-00-00 00:00:00'),
(12, 'ADJUDANT', 'ADJ', NULL, 12, NULL, NULL, '0000-00-00 00:00:00'),
(13, 'ADJUDANT CHEF', 'ADC', NULL, 13, NULL, NULL, '0000-00-00 00:00:00'),
(14, 'MAJOR', 'MAJ', NULL, 14, NULL, NULL, '0000-00-00 00:00:00'),
(15, 'LIEUTENANT', 'LTN', NULL, 15, NULL, NULL, '0000-00-00 00:00:00'),
(16, 'CAPITAINE', 'CNE', NULL, 16, NULL, NULL, '0000-00-00 00:00:00'),
(17, 'CHEF D''ESCADRON', 'CEN', NULL, 17, NULL, NULL, '0000-00-00 00:00:00'),
(18, 'LIEUTENANT COLONEL', 'LTC', NULL, 18, NULL, NULL, '0000-00-00 00:00:00'),
(19, 'COLONEL', 'COL', NULL, 19, NULL, NULL, '0000-00-00 00:00:00'),
(20, 'Monsieur', 'Mr.', NULL, 20, NULL, NULL, '0000-00-00 00:00:00'),
(21, 'Madame', 'Mme.', NULL, 21, NULL, NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

CREATE TABLE IF NOT EXISTS `groupe` (
  `id_groupe` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_groupe` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `borne_gauche` int(11) NOT NULL,
  `borne_droite` int(11) NOT NULL,
  `date_modification_groupe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_groupe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Contenu de la table `groupe`
--

INSERT INTO `groupe` (`id_groupe`, `libelle_groupe`, `borne_gauche`, `borne_droite`, `date_modification_groupe`) VALUES
(0, 'public', 1, 2, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `log_connexion`
--

CREATE TABLE IF NOT EXISTS `log_connexion` (
  `id_log_connexion` int(11) NOT NULL AUTO_INCREMENT,
  `id_session` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ip_adresse` varchar(39) COLLATE utf8_unicode_ci NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_connexion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_connexion`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_connexion`:
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_controle`
--

CREATE TABLE IF NOT EXISTS `log_controle` (
  `id_log_controle` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_controle` int(32) NOT NULL,
  `id_candidat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_controle` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_controle`),
  KEY `id_candidat` (`id_candidat`),
  KEY `id_controle` (`id_controle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `id_controle`:
--   `id_controle`
--       `controle` -> `id_controle`
--   `id_candidat`
--       `candidat` -> `id_candidat`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_epreuve`
--

CREATE TABLE IF NOT EXISTS `log_epreuve` (
  `id_log_epreuve` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_epreuve` int(32) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_epreuve` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_epreuve`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_epreuve` (`id_epreuve`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_epreuve`:
--   `id_epreuve`
--       `epreuve` -> `id_epreuve`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_formulaire`
--

CREATE TABLE IF NOT EXISTS `log_formulaire` (
  `id_log_formulaire` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_formulaire` int(32) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_formulaire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_formulaire`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_formulaire` (`id_formulaire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_formulaire`:
--   `id_formulaire`
--       `formulaire` -> `id_formulaire`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_generation`
--

CREATE TABLE IF NOT EXISTS `log_generation` (
  `id_log_generation` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_generation` int(32) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_generation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_generation`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_generation` (`id_generation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_generation`:
--   `id_generation`
--       `generation` -> `id_generation`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_groupe`
--

CREATE TABLE IF NOT EXISTS `log_groupe` (
  `id_log_groupe` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_groupe` int(32) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_groupe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_groupe`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_groupe` (`id_groupe`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_groupe`:
--   `id_groupe`
--       `groupe` -> `id_groupe`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_question`
--

CREATE TABLE IF NOT EXISTS `log_question` (
  `id_log_question` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_question` int(32) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_question` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_question`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_question` (`id_question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_question`:
--   `id_question`
--       `question` -> `id_question`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_referentiel`
--

CREATE TABLE IF NOT EXISTS `log_referentiel` (
  `id_log_referentiel` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `table_referentiel` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_referentiel` int(11) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_referentiel` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_referentiel`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_referentiel`:
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_reponse`
--

CREATE TABLE IF NOT EXISTS `log_reponse` (
  `id_log_reponse` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_reponse` int(11) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_reponse` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_reponse`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_reponse` (`id_reponse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_reponse`:
--   `id_reponse`
--       `reponse` -> `id_reponse`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_reservation`
--

CREATE TABLE IF NOT EXISTS `log_reservation` (
  `id_log_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_reservation` int(11) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_reservation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_reservation`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_reservation` (`id_reservation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_reservation`:
--   `id_reservation`
--       `reservation` -> `id_reservation`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `log_validation`
--

CREATE TABLE IF NOT EXISTS `log_validation` (
  `id_log_validation` int(11) NOT NULL AUTO_INCREMENT,
  `type_action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_formulaire` int(11) NOT NULL,
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_log_validation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_validation`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_formulaire` (`id_formulaire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `log_validation`:
--   `id_formulaire`
--       `formulaire` -> `id_formulaire`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `profil`
--

CREATE TABLE IF NOT EXISTS `profil` (
  `id_profil` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_profil` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description_profil` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_profil` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `date_debut_profil` date DEFAULT NULL,
  `date_fin_profil` date DEFAULT NULL,
  `date_modification_profil` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_profil`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Contenu de la table `profil`
--

INSERT INTO `profil` (`id_profil`, `libelle_profil`, `description_profil`, `role_profil`, `date_modification_profil`) VALUES
(1, 'Utilisateur non authentifié', NULL, 'guest', '0000-00-00 00:00:00'),
(2, 'Utilisateur', NULL, 'user', '0000-00-00 00:00:00'),
(3, 'Rédacteur', NULL, 'editor', '0000-00-00 00:00:00'),
(4, 'Valideur', NULL, 'validator', '0000-00-00 00:00:00'),
(5, 'Administrateur', NULL, 'administrator', '0000-00-00 00:00:00'),
(6, 'Webmaster', NULL, 'webmaster', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `question`
--

CREATE TABLE IF NOT EXISTS `question` (
  `id_question` int(11) NOT NULL AUTO_INCREMENT,
  `titre_question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `stricte_question` tinyint(1) NOT NULL DEFAULT '0',
  `enonce_question` text COLLATE utf8_unicode_ci NOT NULL,
  `correction_question` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `bareme_question` float NOT NULL,
  `penalite_question` float NOT NULL,
  `libre_question` tinyint(1) NOT NULL DEFAULT '0',
  `lignes_question` int(3) DEFAULT NULL,
  `id_redacteur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_question` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_question`),
  KEY `id_redacteur` (`id_redacteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `question`:
--   `id_redacteur`
--       `utilisateur` -> `id_utilisateur`
--

--
-- Contenu de la table `question`
--

INSERT INTO `question` (`id_question`, `titre_question`, `stricte_question`, `enonce_question`, `correction_question`, `bareme_question`, `penalite_question`, `libre_question`, `lignes_question`, `id_redacteur`, `date_modification_question`) VALUES
(0, 'Système', 0, 'Réservé au système', 'aucun', 0, 0, 0, 0, '0', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `question_reponse`
--

CREATE TABLE IF NOT EXISTS `question_reponse` (
  `id_question_reponse` int(11) NOT NULL AUTO_INCREMENT,
  `id_question` int(11) NOT NULL,
  `id_reponse` int(11) NOT NULL,
  `date_modification_question_reponse` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_question_reponse`),
  KEY `id_question` (`id_question`),
  KEY `id_reponse` (`id_reponse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `question_reponse`:
--   `id_question`
--       `question` -> `id_question`
--   `id_reponse`
--       `reponse` -> `id_reponse`
--

--
-- Contenu de la table `question_reponse`
--

INSERT INTO `question_reponse` (`id_question_reponse`, `id_question`, `id_reponse`, `date_modification_question_reponse`) VALUES
(0, 0, 0, '0000-00-00 00:00:00');


-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE IF NOT EXISTS `reponse` (
  `id_reponse` int(11) NOT NULL AUTO_INCREMENT,
  `texte_reponse` text COLLATE utf8_unicode_ci NOT NULL,
  `valide_reponse` tinyint(1) DEFAULT '0',
  `valeur_reponse` float DEFAULT NULL,
  `sanction_reponse` tinyint(1) DEFAULT '0',
  `penalite_reponse` float NOT NULL,
  `id_redacteur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_reponse` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reponse`),
  KEY `id_redacteur` (`id_redacteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `reponse`:
--   `id_redacteur`
--       `utilisateur` -> `id_utilisateur`
--

--
-- Contenu de la table `reponse`
--

INSERT INTO `reponse` (`id_reponse`, `texte_reponse`, `valide_reponse`, `valeur_reponse`, `sanction_reponse`, `penalite_reponse`, `id_redacteur`, `date_modification_reponse`) VALUES
(0, 'Système', 0, 0, 0, 0, '0', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE IF NOT EXISTS `reservation` (
  `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
  `id_salle` int(11) NOT NULL,
  `motif_reservation` text COLLATE utf8_unicode_ci NOT NULL,
  `id_epreuve` int(11) DEFAULT NULL,
  `date_debut_reservation` datetime NOT NULL,
  `date_fin_reservation` datetime NOT NULL,
  `etat_reservation` tinyint(1) DEFAULT '0',
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_reservation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reservation`),
  KEY `id_epreuve` (`id_epreuve`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `reservation_salle` (`id_salle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `reservation`:
--   `id_salle`
--       `salle` -> `id_salle`
--   `id_epreuve`
--       `epreuve` -> `id_epreuve`
--   `id_utilisateur`
--       `utilisateur` -> `id_utilisateur`
--

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

CREATE TABLE IF NOT EXISTS `salle` (
  `id_salle` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_salle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_salle` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_salle` date NOT NULL,
  `date_fin_salle` date NOT NULL,
  `date_modification_salle` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_salle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `sous_categorie`
--

CREATE TABLE IF NOT EXISTS `sous_categorie` (
  `id_sous_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `id_categorie` int(11) NOT NULL,
  `libelle_sous_categorie` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_sous_categorie` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_sous_categorie` date DEFAULT NULL,
  `date_fin_sous_categorie` date DEFAULT NULL,
  `date_modification_sous_categorie` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sous_categorie`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `sous_categorie`:
--   `id_categorie`
--       `categorie` -> `id_categorie`
--

--
-- Contenu de la table `sous_categorie`
--

INSERT INTO `sous_categorie` (`id_sous_categorie`, `id_categorie`, `libelle_sous_categorie`, `description_sous_categorie`, `date_debut_sous_categorie`, `date_fin_sous_categorie`, `date_modification_sous_categorie`) VALUES
(0, 0, 'aucun', NULL, NULL, NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `sous_domaine`
--

CREATE TABLE IF NOT EXISTS `sous_domaine` (
  `id_sous_domaine` int(11) NOT NULL AUTO_INCREMENT,
  `id_domaine` int(11) NOT NULL,
  `libelle_sous_domaine` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_sous_domaine` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_sous_domaine` date DEFAULT NULL,
  `date_fin_sous_domaine` date DEFAULT NULL,
  `date_modification_sous_domaine` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sous_domaine`),
  KEY `id_domaine` (`id_domaine`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `sous_domaine`:
--   `id_domaine`
--       `domaine` -> `id_domaine`
--

--
-- Contenu de la table `sous_domaine`
--

INSERT INTO `sous_domaine` (`id_sous_domaine`, `id_domaine`, `libelle_sous_domaine`, `description_sous_domaine`, `date_debut_sous_domaine`, `date_fin_sous_domaine`, `date_modification_sous_domaine`) VALUES
(0, 0, 'aucun', NULL, NULL, NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `stage`
--

CREATE TABLE IF NOT EXISTS `stage` (
  `id_stage` int(11) NOT NULL AUTO_INCREMENT,
  `libelle_stage` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_domaine` int(11) NOT NULL,
  `id_sous_domaine` int(11) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `id_sous_categorie` int(11) DEFAULT NULL,
  `description_stage` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_debut_stage` date NOT NULL,
  `date_fin_stage` date NOT NULL,
  `date_modification_stage` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_stage`),
  KEY `id_domaine` (`id_domaine`),
  KEY `stage_sous_domaine` (`id_sous_domaine`),
  KEY `stage_categorie` (`id_categorie`),
  KEY `stage_sous_categorie` (`id_sous_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `stage`:
--   `id_domaine`
--       `domaine` -> `id_domaine`
--   `id_sous_domaine`
--       `sous_domaine` -> `id_sous_domaine`
--   `id_categorie`
--       `categorie` -> `id_categorie`
--   `id_sous_categorie`
--       `sous_categorie` -> `id_sous_categorie`
--

-- --------------------------------------------------------

--
-- Structure de la table `stage_candidat`
--

CREATE TABLE IF NOT EXISTS `stage_candidat` (
  `id_stage_candidat` int(11) NOT NULL AUTO_INCREMENT,
  `id_stage` int(11) NOT NULL,
  `id_candidat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `code_candidat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `date_modification_stage_candidat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_stage_candidat`),
  KEY `id_stage` (`id_stage`),
  KEY `id_candidat` (`id_candidat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `stage_candidat`:
--   `id_stage`
--       `stage` -> `id_stage`
--   `id_candidat`
--       `candidat` -> `id_candidat`
--

-- --------------------------------------------------------

--
-- Structure de la table `statut_salle`
--

CREATE TABLE IF NOT EXISTS `statut_salle` (
  `id_statut_salle` int(11) NOT NULL AUTO_INCREMENT,
  `id_salle` int(11) NOT NULL,
  `capacite_statut_salle` int(3) DEFAULT NULL,
  `informatique_statut_salle` tinyint(1) DEFAULT NULL,
  `reseau_statut_salle` tinyint(1) DEFAULT NULL,
  `examen_statut_salle` tinyint(1) DEFAULT NULL,
  `reservable_statut_salle` tinyint(1) DEFAULT NULL,
  `date_modification_statut_salle` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_statut_salle`),
  KEY `id_salle` (`id_salle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- RELATIONS POUR LA TABLE `statut_salle`:
--   `id_salle`
--       `salle` -> `id_salle`
--

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `id_profil` int(11) NOT NULL DEFAULT '1',
  `id_groupe` int(11) NOT NULL DEFAULT '0',
  `id_grade` int(11) NOT NULL,
  `login_utilisateur` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `password_utilisateur` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `nom_utilisateur` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `prenom_utilisateur` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `editable_utilisateur` tinyint(1) NOT NULL DEFAULT '1',
  `modifiable_utilisateur` tinyint(1) NOT NULL DEFAULT '1',
  `date_modification_utilisateur` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`),
  KEY `id_profil` (`id_profil`),
  KEY `id_groupe` (`id_groupe`),
  KEY `id_grade` (`id_grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- RELATIONS POUR LA TABLE `utilisateur`:
--   `id_grade`
--       `grade` -> `id_grade`
--   `id_groupe`
--       `groupe` -> `id_groupe`
--   `id_profil`
--       `profil` -> `id_profil`
--

--
-- Contenu de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `id_profil`, `id_grade`, `login_utilisateur`, `password_utilisateur`, `nom_utilisateur`, `prenom_utilisateur`, `editable_utilisateur`, `modifiable_utilisateur`, `date_modification_utilisateur`) VALUES
('0', 6, 0, 'system', '', '', '', '0', '0', '0000-00-00 00:00:00'),
('1', 6, 0, 'webmaster', 'fe01ce2a7fbac8fafaed7c982a04e229', 'AMC-BUILDER', 'Webmaster', '0', '1', '0000-00-00 00:00:00');

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `candidat`
--
ALTER TABLE `candidat`
  ADD CONSTRAINT `candidat_grade` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`);

--
-- Contraintes pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD CONSTRAINT `domaine_categorie` FOREIGN KEY (`id_domaine`) REFERENCES `domaine` (`id_domaine`);

--
-- Contraintes pour la table `controle`
--
ALTER TABLE `controle`
  ADD CONSTRAINT `controle_epreuve` FOREIGN KEY (`id_epreuve`) REFERENCES `epreuve` (`id_epreuve`),
  ADD CONSTRAINT `controle_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`);

--
-- Contraintes pour la table `controle_reponse_candidat`
--
ALTER TABLE `controle_reponse_candidat`
  ADD CONSTRAINT `controle` FOREIGN KEY (`id_controle`) REFERENCES `controle` (`id_controle`),
  ADD CONSTRAINT `controle_question` FOREIGN KEY (`id_question`) REFERENCES `question` (`id_question`),
  ADD CONSTRAINT `controle_correcteur` FOREIGN KEY (`id_correcteur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `epreuve`
--
ALTER TABLE `epreuve`
  ADD CONSTRAINT `epreuve_generation` FOREIGN KEY (`id_generation`) REFERENCES `generation` (`id_generation`),
  ADD CONSTRAINT `epreuve_stage` FOREIGN KEY (`id_stage`) REFERENCES `stage` (`id_stage`),
  ADD CONSTRAINT `epreuve_utilisateur` FOREIGN KEY (`id_valideur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `formulaire`
--
ALTER TABLE `formulaire`
  ADD CONSTRAINT `formulaire_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`),
  ADD CONSTRAINT `formulaire_domaine` FOREIGN KEY (`id_domaine`) REFERENCES `domaine` (`id_domaine`),
  ADD CONSTRAINT `formulaire_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `formulaire_sous_categorie` FOREIGN KEY (`id_sous_categorie`) REFERENCES `sous_categorie` (`id_sous_categorie`),
  ADD CONSTRAINT `formulaire_sous_domaine` FOREIGN KEY (`id_sous_domaine`) REFERENCES `sous_domaine` (`id_sous_domaine`),
  ADD CONSTRAINT `formulaire_valideur` FOREIGN KEY (`id_valideur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `formulaire_question`
--
ALTER TABLE `formulaire_question`
  ADD CONSTRAINT `formulaire` FOREIGN KEY (`id_formulaire`) REFERENCES `formulaire` (`id_formulaire`),
  ADD CONSTRAINT `formulaire_question` FOREIGN KEY (`id_question`) REFERENCES `question` (`id_question`);

--
-- Contraintes pour la table `generation`
--
ALTER TABLE `generation`
  ADD CONSTRAINT `generation_formulaire` FOREIGN KEY (`id_formulaire`) REFERENCES `formulaire` (`id_formulaire`),
  ADD CONSTRAINT `generation_utilisateur` FOREIGN KEY (`id_valideur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `question_reponse`
--
ALTER TABLE `question_reponse`
  ADD CONSTRAINT `question` FOREIGN KEY (`id_question`) REFERENCES `question` (`id_question`),
  ADD CONSTRAINT `question_reponse` FOREIGN KEY (`id_reponse`) REFERENCES `reponse` (`id_reponse`);

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `reponse_redacteur` FOREIGN KEY (`id_redacteur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id_salle`),
  ADD CONSTRAINT `reservation_epreuve` FOREIGN KEY (`id_epreuve`) REFERENCES `epreuve` (`id_epreuve`),
  ADD CONSTRAINT `reservation_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `sous_categorie`
--
ALTER TABLE `sous_categorie`
  ADD CONSTRAINT `categorie_parent` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`);

--
-- Contraintes pour la table `sous_domaine`
--
ALTER TABLE `sous_domaine`
  ADD CONSTRAINT `domaine_parent` FOREIGN KEY (`id_domaine`) REFERENCES `domaine` (`id_domaine`);

--
-- Contraintes pour la table `stage`
--
ALTER TABLE `stage`
  ADD CONSTRAINT `stage_domaine` FOREIGN KEY (`id_domaine`) REFERENCES `domaine` (`id_domaine`),
  ADD CONSTRAINT `stage_sous_domaine` FOREIGN KEY (`id_sous_domaine`) REFERENCES `sous_domaine` (`id_sous_domaine`),
  ADD CONSTRAINT `stage_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`),
  ADD CONSTRAINT `stage_sous_categorie` FOREIGN KEY (`id_sous_categorie`) REFERENCES `sous_categorie` (`id_sous_categorie`);

--
-- Contraintes pour la table `stage_candidat`
--
ALTER TABLE `stage_candidat`
  ADD CONSTRAINT `stage` FOREIGN KEY (`id_stage`) REFERENCES `stage` (`id_stage`),
  ADD CONSTRAINT `stage_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`);

--
-- Contraintes pour la table `statut_salle`
--
ALTER TABLE `statut_salle`
  ADD CONSTRAINT `statut_salle` FOREIGN KEY (`id_salle`) REFERENCES `salle` (`id_salle`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_grade` FOREIGN KEY (`id_grade`) REFERENCES `grade` (`id_grade`),
  ADD CONSTRAINT `utilisateur_groupe` FOREIGN KEY (`id_groupe`) REFERENCES `groupe` (`id_groupe`),
  ADD CONSTRAINT `utilisateur_profil` FOREIGN KEY (`id_profil`) REFERENCES `profil` (`id_profil`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
