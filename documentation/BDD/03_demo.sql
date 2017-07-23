-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Généré le: Dimanche 22 Juillet 2017 à 14:00
-- Server version: 5.5.47-0+deb7u1
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
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `id_profil`, `id_grade`, `login_utilisateur`, `password_utilisateur`, `nom_utilisateur`, `prenom_utilisateur`, `editable_utilisateur`, `modifiable_utilisateur`, `date_modification_utilisateur`) VALUES
('123456', 2, 20, 'utilisateur', 'fe01ce2a7fbac8fafaed7c982a04e229', 'DOE', 'John', 1, 0, '2016-05-10 20:23:00'),
('333333', 3, 11, 'redacteur', 'fe01ce2a7fbac8fafaed7c982a04e229', 'RÉDACTEUR', 'CNFSICG', 1, 0, '2016-05-10 20:23:00'),
('444444', 4, 13, 'valideur', 'fe01ce2a7fbac8fafaed7c982a04e229', 'VALIDEUR', 'CNFSICG', 1, 0, '2016-05-10 20:23:06'),
('555555', 5, 15, 'administrateur', 'fe01ce2a7fbac8fafaed7c982a04e229', 'ADMINISTRATEUR', 'CNFSICG', 1, 0, '2016-05-10 20:23:12');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
