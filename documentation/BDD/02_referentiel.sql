-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Généré le: Dimanche 22 Juillet 2017 à 14:00
-- Server version: 5.5.53-0+deb8u1
-- PHP Version: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `amc-builder`
--

-- --------------------------------------------------------

--
-- Dumping data for table `domaine`
--

INSERT INTO `domaine` (`id_domaine`, `libelle_domaine`, `description_domaine`, `date_debut_domaine`, `date_fin_domaine`, `date_modification_domaine`) VALUES
(1, 'Culture générale', '', '2017-01-25', '9999-12-31', '2017-01-25 17:53:04'),
(2, 'Politique', '', '2016-02-19', '9999-12-31', '2017-01-25 18:02:21'),
(3, 'Sciences', '', '2017-01-25', '9999-12-31', '2017-01-25 17:53:45'),
(4, 'Judiciaire', '', '2017-01-25', '9999-12-31', '2017-01-25 17:54:33'),
(5, 'Informatique', '', '2017-01-25', '9999-12-31', '2017-01-25 17:59:12'),
(6, 'Présentation', '', '2017-01-25', '9999-12-31', '2017-01-25 18:14:05');

-- --------------------------------------------------------

--
-- Dumping data for table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `id_domaine`, `libelle_categorie`, `description_categorie`, `date_debut_categorie`, `date_fin_categorie`, `date_modification_categorie`) VALUES
(1, 5, 'Système d&#39;exploitation', '', '2016-09-23', '9999-12-31', '2017-01-25 17:59:34'),
(2, 5, 'Programmation', '', '2017-01-25', '9999-12-31', '2017-01-25 18:04:21');

-- --------------------------------------------------------

--
-- Dumping data for table `salle`
--

INSERT INTO `salle` (`id_salle`, `libelle_salle`, `description_salle`, `date_debut_salle`, `date_fin_salle`, `date_modification_salle`) VALUES
(1, 'SALLE 01', 'Salle de réunion', '2016-09-26', '9999-12-31', '2016-09-26 10:56:57'),
(2, 'SALLE 02', '', '2017-01-25', '9999-12-31', '2017-01-25 17:57:25'),
(3, 'SALLE 03', '', '2017-01-25', '9999-12-31', '2017-01-25 17:57:53'),
(4, 'SALLE 04', '', '2017-01-25', '9999-12-31', '2017-01-25 17:58:03'),
(5, 'SALLE 05', '', '2017-01-25', '9999-12-31', '2017-01-25 17:58:14'),
(6, 'SALLE 06', '', '2017-01-25', '9999-12-31', '2017-01-25 17:58:25');

-- --------------------------------------------------------

--
-- Dumping data for table `sous_categorie`
--

INSERT INTO `sous_categorie` (`id_sous_categorie`, `id_categorie`, `libelle_sous_categorie`, `description_sous_categorie`, `date_debut_sous_categorie`, `date_fin_sous_categorie`, `date_modification_sous_categorie`) VALUES
(1, 1, 'Linux', '', '2016-09-23', '9999-12-31', '2017-01-25 17:56:43'),
(4, 1, 'Windows', '', '2017-01-25', '9999-12-31', '2017-01-25 17:56:53');

-- --------------------------------------------------------

--
-- Dumping data for table `sous_domaine`
--

INSERT INTO `sous_domaine` (`id_sous_domaine`, `id_domaine`, `libelle_sous_domaine`, `description_sous_domaine`, `date_debut_sous_domaine`, `date_fin_sous_domaine`, `date_modification_sous_domaine`) VALUES
(1, 1, 'Histoire - Géographie', '', '2016-02-19', '9999-12-31', '2017-01-25 18:01:17'),
(2, 1, 'Français', '', '2017-01-25', '9999-12-31', '2017-01-25 18:06:32'),
(3, 1, 'Anglais', '', '2017-01-25', '9999-12-31', '2017-01-25 18:06:43'),
(4, 1, 'Cinéma', '', '2017-01-25', '9999-12-31', '2017-01-25 18:07:07'),
(5, 1, 'Sport', '', '2017-01-25', '9999-12-31', '2017-01-25 18:07:33');

-- --------------------------------------------------------

--
-- Dumping data for table `statut_salle`
--

INSERT INTO `statut_salle` (`id_statut_salle`, `id_salle`, `capacite_statut_salle`, `informatique_statut_salle`, `reseau_statut_salle`, `examen_statut_salle`, `reservable_statut_salle`, `date_modification_statut_salle`) VALUES
(1, 1, 60, 0, 0, 1, 1, '2016-09-26 10:56:57'),
(2, 2, 20, 1, 1, 1, 1, '2017-01-25 17:57:25'),
(3, 3, 20, 1, 1, 1, 1, '2017-01-25 17:57:53'),
(4, 4, 20, 1, 1, 1, 1, '2017-01-25 17:58:04'),
(5, 5, 20, 1, 1, 1, 1, '2017-01-25 17:58:14'),
(6, 6, 20, 1, 1, 1, 1, '2017-01-25 17:58:25');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
