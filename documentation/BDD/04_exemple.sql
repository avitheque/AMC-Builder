-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Client: 127.0.0.1:3306
-- Généré le: Lun 23 Janvier 2017 à 14:44
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

-- --------------------------------------------------------

--
-- Contenu de la table `formulaire`
--

INSERT INTO `formulaire` (`id_formulaire`, `id_domaine`, `id_sous_domaine`, `id_categorie`, `id_sous_categorie`, `titre_formulaire`, `presentation_formulaire`, `strict_formulaire`, `note_finale_formulaire`, `penalite_formulaire`, `id_redacteur`, `validation_formulaire`, `id_valideur`, `date_modification_formulaire`) VALUES
(1, 0, 0, 0, 0, 'Test', 'Veuillez répondre aux questions ci-dessous du mieux que vous le pouvez.', 0, 20, 0, '333333', 2, '444444', '2017-01-11 11:20:07');

-- --------------------------------------------------------

--
-- Contenu de la table `question`
--

INSERT INTO `question` (`id_question`, `titre_question`, `stricte_question`, `enonce_question`, `correction_question`, `bareme_question`, `penalite_question`, `libre_question`, `lignes_question`, `id_redacteur`, `date_modification_question`) VALUES
(1, 'Question libre', 0, 'Question libre :&#10;&#10;Le candidat doit répondre à la question avec ses mots.&#10;C&#39;est le correcteur qui cochera la case correspondante selon son appréciation.', 'Cette zone n&#39;est visible que par le correcteur.&#10;Elle permet notamment de donner les éléments de réponse attendus.', 1, 0, 1, 10, '333333', '2017-01-10 09:23:44'),
(2, 'Question multiple - stricte', 1, 'Question à choix multiple avec réponse stricte attendue (tout ou rien) :&#10;&#10;Le candidat doit cocher la totalité des bonnes réponse pour se voir accorder le(s) point(s) du barème.', '', 1, 0, 0, NULL, '333333', '2017-01-10 10:26:03'),
(3, 'Question multiple - facteur de pénalité', 0, 'Question à choix multiple AVEC facteur de pénalité en cas de mauvaise réponse :&#10;&#10;Le(s) point(s) des réponses valides sont accordés pour chaque bonne réponse cochée.&#10;Cependant, des points sont retirés pour chaque mauvaise réponse selon le facteur de pénalité.', '', 1, 50, 0, NULL, '333333', '2017-01-10 10:31:43'),
(4, 'Question simple - stricte', 1, 'Question à choix unique sans pénalité en cas de mauvaise réponse :&#10;&#10;Le candidat doit cocher la bonne réponse pour se voir accorder le(s) point(s) du barème.&#10;Aucune pénalité n&#39;est affectée en cas de mauvaise réponse.', '', 1, 0, 0, NULL, '333333', '2017-01-10 10:31:43'),
(5, 'Question simple - facteur de pénalité', 0, 'Question à choix unique AVEC pénalité en cas de mauvaise réponse :&#10;&#10;Le(s) point(s) des réponses valides sont accordés pour chaque bonne réponse cochée.&#10;Cependant, des points sont retirés pour chaque mauvaise réponse selon le facteur de pénalité.', '', 1, 50, 0, NULL, '333333', '2017-01-10 10:31:43'),
(6, 'Question simple - sanction', 0, 'Question à choix simple avec une SANCTION sur une mauvaise réponse :&#10;&#10;Même principe de fonctionnement qu&#39;une question à choix unique, mais cette fois-ci une mauvaise réponse retire des points !', '', 1, 0, 0, NULL, '333333', '2017-01-10 11:29:02'),
(7, 'Question libre - stricte', 1, 'Question libre à réponse STRICTE :&#10;&#10;Le candidat doit répondre à la question avec ses mots.&#10;C&#39;est le correcteur qui cochera la case correspondante selon son appréciation.', 'La réponse est correcte ou fausse...', 1, 0, 1, NULL, '333333', '2017-01-11 11:14:02'),
(8, 'Question multiple - aucune', 0, 'Question multiple SANS bonne réponse :&#10;&#10;Aucune des réponses proposées ci-dessous n&#39;est juste !', '', 1, 0, 0, NULL, '333333', '2017-01-11 11:19:51'),
(9, 'Question multiple - normale', 0, 'Question à choix multiple normale :&#10;&#10;Le candidat doit cocher la totalité des bonnes réponses pour se voir accorder la totalité des points du barème.&#10;Cependant, s&#39;il ne coche qu&#39;une seule bonne réponse, il n&#39;obtiendra que la valeur attribuée à son choix.&#10;Aucune pénalité n&#39;est affectée en cas de mauvaise réponse.', '', 2, 0, 0, NULL, '333333', '2017-06-17 10:16:40');

-- --------------------------------------------------------

--
-- Contenu de la table `formulaire_question`
--

INSERT INTO `formulaire_question` (`id_formulaire_question`, `id_formulaire`, `id_question`, `date_modification_question`) VALUES
(1, 1, 1, '2017-01-11 11:19:51'),
(2, 1, 2, '2017-01-11 11:19:51'),
(3, 1, 3, '2017-01-11 11:19:51'),
(4, 1, 4, '2017-01-11 11:19:51'),
(5, 1, 5, '2017-01-11 11:19:51'),
(6, 1, 6, '2017-01-11 11:19:51'),
(7, 1, 7, '2017-01-11 11:19:51'),
(8, 1, 8, '2017-01-11 11:19:51'),
(9, 1, 9, '2017-01-11 11:19:51');

-- --------------------------------------------------------

--
-- Contenu de la table `groupe`
--

INSERT INTO `groupe` (`id_groupe`, `libelle_groupe`, `borne_gauche`, `borne_droite`) VALUES
(1, '1', 3, 8),
(2, '1.1', 4, 5),
(3, '1.2', 6, 7),
(4, '2', 9, 28),
(5, '2.1', 10, 15),
(6, '2.1.1', 11, 12),
(7, '2.1.2', 13, 14),
(8, '2.2', 16, 27),
(9, '2.2.1', 17, 24),
(10, '2.2.1.1', 18, 19),
(11, '2.2.1.2', 20, 21),
(12, '2.2.1.3', 22, 23),
(13, '2.2.2', 25, 26),
(14, '3', 29, 36),
(15, '3.1', 30, 31),
(16, '3.2', 32, 33),
(17, '3.3', 34, 35),
(18, '4', 37, 38);

-- --------------------------------------------------------

--
-- Contenu de la table `reponse`
--

INSERT INTO `reponse` (`id_reponse`, `texte_reponse`, `valide_reponse`, `valeur_reponse`, `sanction_reponse`, `penalite_reponse`, `id_redacteur`, `date_modification_reponse`) VALUES
(1, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-10 09:13:03'),
(2, 'Bonne réponse 1/2', 1, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(3, 'Bonne réponse 2/2', 1, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(4, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-10 09:13:03'),
(5, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-10 09:15:17'),
(6, 'Bonne réponse 1/2', 1, 50, 0, 0, '333333', '2017-01-10 10:31:43'),
(7, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-10 09:29:13'),
(8, 'Bonne réponse 2/2', 1, 50, 0, 0, '333333', '2017-01-10 10:31:43'),
(9, 'Vrai', 1, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(10, 'Faux', 0, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(11, 'Mauvais', 0, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(12, 'Pas bon', 0, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(13, 'Vrai', 1, 100, 0, 0, '333333', '2017-01-10 10:31:43'),
(14, 'Faux', 0, 0, 0, 0, '333333', '2017-01-10 10:31:43'),
(15, 'Vrai', 1, 100, 0, 0, '333333', '2017-01-10 11:29:02'),
(16, 'Faux', 0, 0, 0, 0, '333333', '2017-01-10 11:29:02'),
(17, 'Tellement faux qu&#39;on se doit de retirer des points !', 0, 0, 1, 1, '333333', '2017-01-10 11:29:02'),
(18, 'Faux 1/4', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24'),
(19, 'Faux 2/4', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24'),
(20, 'Faux 3/4', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24'),
(21, 'Faux 4/4', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24'),
(22, 'Bonne réponse qui rapporte 30% des points', 1, 30, 0, 0, '333333', '2017-01-11 11:19:24'),
(23, 'Bonne réponse qui rapporte 70% des points', 1, 70, 0, 0, '333333', '2017-01-11 11:19:24'),
(24, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24'),
(25, 'Mauvaise réponse', 0, 0, 0, 0, '333333', '2017-01-11 11:19:24');

-- --------------------------------------------------------

--
-- Contenu de la table `question_reponse`
--

INSERT INTO `question_reponse` (`id_question_reponse`, `id_question`, `id_reponse`, `date_modification_question_reponse`) VALUES
(1, 2, 1, '2017-01-10 09:13:03'),
(2, 2, 2, '2017-01-10 09:13:03'),
(3, 2, 3, '2017-01-10 09:13:03'),
(4, 2, 4, '2017-01-10 09:13:03'),
(5, 3, 5, '2017-01-10 09:15:17'),
(6, 3, 6, '2017-01-10 09:15:17'),
(7, 3, 7, '2017-01-10 09:15:17'),
(8, 3, 8, '2017-01-10 09:15:17'),
(9, 4, 9, '2017-01-10 10:26:04'),
(10, 4, 10, '2017-01-10 10:26:04'),
(11, 4, 11, '2017-01-10 10:26:04'),
(12, 4, 12, '2017-01-10 10:26:04'),
(13, 5, 13, '2017-01-10 10:31:43'),
(14, 5, 14, '2017-01-10 10:31:43'),
(15, 6, 15, '2017-01-10 11:29:02'),
(16, 6, 16, '2017-01-10 11:29:02'),
(17, 6, 17, '2017-01-10 11:29:02'),
(18, 8, 18, '2017-01-11 11:19:24'),
(19, 8, 19, '2017-01-11 11:19:24'),
(20, 8, 20, '2017-01-11 11:19:24'),
(21, 8, 21, '2017-01-11 11:19:24'),
(22, 9, 22, '2017-01-11 11:19:24'),
(23, 9, 23, '2017-01-11 11:19:24'),
(24, 9, 24, '2017-01-11 11:19:24'),
(25, 9, 25, '2017-01-11 11:19:24');

--
-- Contenu de la table `stage`
--

INSERT INTO `stage` (`id_stage`, `libelle_stage`, `id_domaine`, `id_sous_domaine`, `id_categorie`, `id_sous_categorie`, `description_stage`, `date_debut_stage`, `date_fin_stage`, `date_modification_stage`) VALUES
(1, 'Exemple de stage', 6, 0, 0, 0, NULL, '2017-02-20', '2017-02-20', '2017-02-20 13:29:24');


--
-- Contenu de la table `candidat`
--

INSERT INTO `candidat` (`id_candidat`, `id_grade`, `nom_candidat`, `prenom_candidat`, `unite_candidat`, `date_modification_candidat`) VALUES
('111111', 19, 'MARTIN', 'Martin', '-', '2017-02-05 13:17:13'),
('123456', 19, 'DOE', 'John', '-', '2017-02-05 10:36:44'),
('222222', 20, 'MARTINE', 'Martine', '-', '2017-02-05 13:17:55'),
('234567', 20, 'DUPONT', 'Martine', '-', '2017-02-05 13:13:59');

--
-- Contenu de la table `stage_candidat`
--

INSERT INTO `stage_candidat` (`id_stage_candidat`, `id_stage`, `id_candidat`, `code_candidat`, `date_modification_stage_candidat`) VALUES
(1, 1, '222222', '4067', '2017-02-20 13:29:44'),
(2, 1, '234567', '5736', '2017-02-20 13:29:44'),
(3, 1, '111111', '6873', '2017-02-20 13:29:44'),
(4, 1, '123456', '0974', '2017-02-20 13:29:44');

--
-- Contenu de la table `generation`
--

INSERT INTO `generation` (`id_generation`, `id_formulaire`, `langue_generation`, `format_generation`, `separate_generation`, `seed_generation`, `nom_epreuve_generation`, `date_epreuve_generation`, `consignes_generation`, `exemplaires_generation`, `code_candidat_generation`, `cartouche_candidat_generation`, `id_valideur`, `date_modification_generation`) VALUES
(1, 1, 'francais', 'a4paper', 0, 1237893, 'Exemple de stage (20/02/2017 - 20/02/2017)', '2017-02-20', 'Veuillez utiliser un stylo à encre noir ou bleu-noir afin de reporter vos choix. Les encres claires, fluorescentes ou effaçables sont interdites.&#10;Pour toute correction, veuillez utiliser du blanc correcteur exclusivement.&#10;DANS CE DERNIER CAS, NE REDESSINEZ PAS LA CASE !', 20, 6, 'Codez votre code candidat à l&#39;aide des cases ci-contre en reportant chaque numéro de gauche à droite', '444444', '2017-07-05 00:14:10');

--
-- Contenu de la table `epreuve`
--

INSERT INTO `epreuve` (`id_epreuve`, `type_epreuve`, `libelle_epreuve`, `date_epreuve`, `heure_epreuve`, `duree_epreuve`, `id_stage`, `id_generation`, `liste_salles_epreuve`, `table_affectation_epreuve`, `table_aleatoire_epreuve`, `id_valideur`, `date_modification_epreuve`) VALUES
(1, 'Contrôle', 'Exemple de stage (20/02/2017 - 20/02/2017)', '2017-02-20', '09:00:00', 50, 1, 1, '3', 1, 1, '444444', '2017-07-05 00:14:16');

-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
