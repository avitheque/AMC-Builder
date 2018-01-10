<?php

/**
 * @brief	Ségments de construction du document LaTeX.
 * @var		file
 */
require_once('LatexFormManager/Header.php');
require_once('LatexFormManager/Exemplaire.php');
require_once('LatexFormManager/Candidat.php');
require_once('LatexFormManager/Consignes.php');
require_once('LatexFormManager/Instructions.php');
require_once('LatexFormManager/Presentation.php');
require_once('LatexFormManager/Closure.php');

/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Pour des raisons de maintenabilité, la construction du document complet est réalisé en plusieurs ségments.
 * Chaque ségment correspond à une classe d'objet comme suit :
 * 	- Entête du document avec déclaration des bibliothèques LaTeX					: 'LatexFormManager/Header.php'
 * 	- Détermination du nombre d'exemplaires avec la zone date et durée de l'épreuve	: 'LatexFormManager/Exemplaire.php'
 * 	- Zone d'identification des codes candidats										: 'LatexFormManager/Candidat.php'
 * 	- Renseignement sur la façon de remplire le QCM par les candidats				: 'LatexFormManager/Consignes.php'
 * 	- Informations sur l'identification des questions à choix multiples				: 'LatexFormManager/Instructions.php'
 * 	- Présentation de l'objectif de l'épreuve										: 'LatexFormManager/Presentation.php'
 * 	- Finalisation du document et instruction de mélange des copies					: 'LatexFormManager/Closure.php'
 *
 * L'ensemble du formulaire est parcouru afin de générer un fichier LaTeX directement
 * exploitable par l'application Auto-Multiple-Choice.
 *
 * @li ATTENTION : La construction des groupes de questions est limité à 20 éléments !
 *
 * Réalisation du formulaire en plusieurs bloques.
 * @li Initialisation du document
 * @li Customisation de la génération des codes candidats
 * @li Construction des questions
 * @li Construction du format pour l'édition des exemplaires
 * @li Construction de l'identification des candidats
 * @li Construction des instructions pour l'aide à la rédaction
 * @li Finalisation du document
 *
 * La méthode render() permet de générer le document au format LaTeX à télécharger par le navigateur client.
 * @code
 * 		\documentclass[a4paper]{article}
 *
 * 		\usepackage[utf8x]{inputenc}
 * 		\usepackage[T1]{fontenc}
 * 		\usepackage[francais,bloc,completemulti]{automultiplechoice}
 * 		\usepackage{multicol}
 *
 * 		\begin{document}
 * 		\AMCrandomseed{1237893}
 *
 * 		\element{%question_groupe} {
 * 		\begin{question}{%question_titre}
 * 			%question_enonce
 * 			\begin{multicols}{2}
 * 				\begin{reponses}
 * 					\bonne{%reponse_texte[1]}\bareme{b=3}
 * 					\mauvaise{%reponse_texte[2]}\bareme{b=0,m=-1}
 * 					\mauvaise{%reponse_texte[3]}\bareme{b=0,m=-1}
 * 					\mauvaise{%reponse_texte[4]}\bareme{b=0,m=-1}
 * 				\end{reponses}
 * 			\end{multicols}
 * 		\end{question}
 * 		}
 *
 * 		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 *
 * 		\exemplaire{20}{
 * 			\noindent{\bf Libellé du stage \hfill{} Titre de l'épreuve \\ Examen du 04/09/2015 \hfill{} Durée : 50 minutes}
 *
 * 			\begin{minipage}{.4\linewidth}
 * 				\champnom{
 * 					\fbox{
 * 						\begin{minipage}{.9\linewidth}
 * 							Code candidat :
 *
 * 							\vspace*{5mm}\dotfill
 * 							\vspace*{1mm}
 * 						\end{minipage}
 * 					}
 * 				}
 * 				\vspace{3ex}
 * 				Codez votre code candidat à l’aide
 * 				des cases ci-contre en reportant chaque
 * 				numéro de gauche à droite
 * 			\end{minipage}
 * 			\begin{minipage}{.1\linewidth}
 * 				\vspace{2cm}
 * 				$\longrightarrow{}$
 * 			\end{minipage}
 * 			\begin{minipage}{.5\linewidth}
 * 				\noindent\AMCcode{code}{%d}\hspace*{\fill}
 * 			\end{minipage}
 *
 * 			\vspace{5mm}
 * 			\noindent\hrulefill
 * 			\begin{center}
 * 				Les questions faisant apparaître le symbole \multiSymbole{} peuvent présenter zéro, une ou plusieurs bonnes réponses. Les autres ont une unique bonne réponse.
 * 			\end{center}
 * 			\noindent\hrulefill
 * 			\vspace{5mm}
 *
 * 			\begin{center}
 * 				Veuillez répondre aux questions ci-dessous du mieux que vous le pouvez.
 * 			\end{center}
 *
 * 			\vspace{5mm}
 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 * 			\melangegroupe{amc}
 * 			\restituegroupe{amc}
 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 * 			\clearpage
 * 		}
 * 		\end{document}
 * @endcode
 *
 * Étend la classe abstraite DocumentManager.
 * @see			{ROOT_PATH}/libraries/models/DocumentManager.php
 *
 * @name		LatexFormManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 100 $
 * @since		$LastChangedDate: 2018-01-10 19:53:46 +0100 (Wed, 10 Jan 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class LatexFormManager extends DocumentManager {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * Déclaration de l'ensemble des dépendances pour les fonctionnalités d'AMC
	 *
	 * @code
	 * 	\documentclass[a4paper]{article}
	 *
	 * 	\usepackage[utf8x]{inputenc}
	 * 	\usepackage[T1]{fontenc}
	 * 	\usepackage[francais,bloc,completemulti]{automultiplechoice}
	 * 	\usepackage{multicol}
	 *
	 * 	\begin{document}
	 * @endcode
	 *
	 * @var		string
	 */
	const DOCUMENT_MULTICOLONNE					= true;				// Active les réponses sur plusieurs colonnes

	/**
	 * @brief	Constante de génération des copies aléatoires.
	 *
	 * @code
	 * 	\AMCrandomseed{1237893}
	 * @endcode
	 *
	 * @var		string
	 */
	const DOCUMENT_RANDOMISEED_DEFAUT			= 1237893;
	const DOCUMENT_RANDOMISEED_MIN				= 1;
	const DOCUMENT_RANDOMISEED_MAX				= 4194303;
	const DOCUMENT_STATICSEED					= true;
	const DOCUMENT_LN							= "\\\\";

	/**
	 * @brief	Constantes du format de création des éléments constituant les questions
	 *
	 * @li Construction d'une question à réponse SIMPLE
	 * @code
	 * 	\element{amc}{
	 * 	\begin{question}{Q01}
	 * 		Énoncé de la question
	 * 			\begin{reponses}
	 *
	 * 				% Éléments de réponses construits à part
	 *
	 * 			\end{reponses}
	 * 	\end{question}
	 * 	}
	 * @endcode
	 *
	 * @li Construction d'une question à réponses MULTIPLES
	 * @code
	 * 	\element{amc}{
	 * 	\begin{questionmult}{Q01}
	 * 		Énoncé de la question
	 * 		\begin{multicols}{2}
	 * 			\begin{reponses}
	 *
	 * 				% Éléments de réponses construits à part
	 *
	 * 			\end{reponses}
	 * 		\end{multicols}
	 * 	\end{questionmult}
	 * 	}
	 * @endcode
	 *
	 * @li Construction d'une question STRICTE à réponses MULTIPLES
	 * @code
	 * 	\element{amc}{
	 * 	\begin{questionmult}{Q01}\bareme{mz=%bareme}
	 * 		Énoncé de la question
	 * 		\begin{multicols}{2}
	 * 			\begin{reponses}
	 *
	 * 				% Éléments de réponses construits à part
	 *
	 * 			\end{reponses}
	 * 		\end{multicols}
	 * 	\end{questionmult}
	 * 	}
	 * @endcode
	 *
	 * @li Construction d'une question LIBRE
	 * @code
	 * 	\element{ouvert}{
	 * 	\begin{question}{Q01}
	 * 		Énoncé de la question libre~?
	 * 		\\								% Retour à la ligne
	 * 		\fbox{							% Cadre matérialisant la zone de réponse
	 * 		\begin{minipage}{1\textwidth}
	 * 			\begin{center}
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 *				~\\
	 * 			\end{center}
	 * 		\end{minipage}
	 * 		}
	 * 		\begin{choicescustom}[o]
	 * 			\color{red}
	 * 			\AMCboxColor{red}
	 * 			\bf{Réservé à la correction}~~~~~~~~~~~~~~~~~~~~
	 * 			\bonne{juste}\bareme{1}
	 * 			\mauvaise{partiel}\bareme{0.5}
	 *			\mauvaise{faux}
	 * 		\end{reponseshoriz}
	 * 	\end{question}
	 * 	}
	 * @endcode
	 *
	 * @var		string
	 */
	const DOCUMENT_QUESTIONS_ID					= "Q%03d";
	const QUESTION_FORMAT_DEFAUT				= "/latex/Structure/Question/defaut.tex";
	const QUESTION_FORMAT_BAREME				= "/latex/Structure/Question/bareme.tex";
	const QUESTION_FORMAT_BONUS					= "/latex/Structure/Question/bonus.tex";
	const QUESTION_FORMAT_MALUS					= "/latex/Structure/Question/malus.tex";
	const QUESTION_FORMAT_HAUT					= "/latex/Structure/Question/haut.tex";
	const QUESTION_FORMAT_MAX					= "/latex/Structure/Question/max.tex";
	const QUESTION_FORMAT_MZ					= "/latex/Structure/Question/mz.tex";

	const DOCUMENT_COLONNES_LIBRECADRE			= "/latex/Structure/Colonne/fbox.tex";
	const DOCUMENT_COLONNES_MULTICOLS			= "/latex/Structure/Colonne/multicols.tex";

	/**
	 * @brief	Constantes des paramètres de construction des bloques de question.
	 *
	 * @var		string
	 */
	const QUESTIONS_GROUPE_LIMIT				= 20;				// Limite du nombre de questions par groupe			/!\ DANS LA DOCUMENTATION, IL SEMBLERAIT QU'UN BUG APPARAÎTRAIT AU DELÀ...
	const QUESTIONS_GROUPE_PARAM				= "amc";			// Nom des groupes de questions
	const QUESTIONS_GROUPE_START				= 1;				// Identifiant des groupes limités à 20 questions chacun
	const QUESTION_MULTIPLE_PARAM				= "questionmult";	// Label des questions à choix multiple
	const QUESTION_SIMPLE_PARAM					= "question";		// Label des questions à choix unique

	/**
	 * @brief	Constante pour le nombre de réponse minimum afin de passer à un affichage sur 2 colonnes.
	 *
	 * @var		integer
	 */
	const REPONSES_COLONNE_MULTIPLE				= 3;
	const REPONSES_COLONNE_MAXLENGTH			= 35;

	const DOCUMENT_REPONSES_LIBRE				= "/latex/Structure/Reponse/choicescustom.tex";
	const DOCUMENT_REPONSES_FORMAT				= "/latex/Structure/Reponse/reponses.tex";

	/**
	 * @brief	Constantes du format de création des réponses.
	 *
	 * @li	Format par défaut
	 * @code
	 * 	\bonne{%reponse_texte[1][1]}
	 * 	\mauvaise{%reponse_texte[1][2]}
	 * 	\bonne{%reponse_texte[1][3]}
	 * 	\mauvaise{%reponse_texte[1][4]}
	 * @endcode
	 *
	 * @li	Format avec barème
	 * @code
	 * 	\bonne{%reponse_texte[1][1]}\bareme{b=2}
	 * 	\mauvaise{%reponse_texte[1][2]}\bareme{b=0,m=-1}
	 * 	\bonne{%reponse_texte[1][3]}\bareme{b=3}
	 * 	\mauvaise{%reponse_texte[1][4]}
	 * @endcode
	 *
	 * @var		string
	 */
	const REPONSE_FORMAT_BONUS					= "/latex/Structure/Reponse/Item/bonus.tex";
	const REPONSE_FORMAT_DEFAUT					= "/latex/Structure/Reponse/Item/defaut.tex";
	const REPONSE_FORMAT_MALUS					= "/latex/Structure/Reponse/Item/malus.tex";
	const REPONSE_FORMAT_NEGATIF				= "/latex/Structure/Reponse/Item/negatif.tex";
	const REPONSE_FORMAT_PERDU					= "/latex/Structure/Reponse/Item/perdu.tex";
	const REPONSE_FORMAT_VIDE					= "/latex/Structure/Reponse/Item/vide.tex";

	/**
	 * @brief	Constantes du format de création des parties réserviées à la corrections.
	 *
	 * @li	Format souple
	 * @code
	 *	\begin{choicescustom}[o]
	 *		\color{red}
	 * 		\AMCboxColor{red}
	 *		\bf{Réservé à la correction}\hfill{}
	 *		\correctchoice[A]{excellent}\scoring{1}~~
	 *		\wrongchoice[B]{très bien}\scoring{1}~~
	 *		\wrongchoice[C]{moyen}\scoring{1}~~
	 *		\wrongchoice[D]{insuffisant}\scoring{1}~~
	 *		\wrongchoice[E]{mauvais}\scoring{0}
	 *	\end{choicescustom}
	 * @endcode
	 *
	 * @li	Format strict
	 * @code
	 *	\begin{choicescustom}[o]
	 *		\color{red}
	 * 		\AMCboxColor{red}
	 *		\bf{Réservé à la correction}\hfill{}
	 *		\correctchoice[A]{exact}\scoring{1}~~
	 *		\wrongchoice[E]{faux}\scoring{0}
	 *	\end{choicescustom}
	 * @endcode
	 *
	 * @var		string
	 */
	const CORRECTION_FORMAT_SOUPLE				= "/latex/Structure/Correction/souple.tex";
	const CORRECTION_FORMAT_STRICT				= "/latex/Structure/Correction/stricte.tex";

	const REPONSE_OUVERTE						= "libre";
	const REPONSE_VRAIE							= "bonne";
	const REPONSE_FAUSSE						= "mauvaise";
	const REPONSE_AUCUNE						= "Aucune de ces réponses n'est correcte.";

	/**
	 * @brief	Constantes pour les instructions d'aide à la rédaction.
	 *
	 * @var		string
	 */
	const DOCUMENT_INSTRUCTIONS_TXT				= "Les questions faisant apparaître le symbole \\multiSymbole{} peuvent présenter zéro, une ou plusieurs bonnes réponses. Les autres ont une unique bonne réponse.";

	/**
	 * @brief	Constante de construction de la fin du document.
	 *
	 * @var		string
	 */
	const DOCUMENT_MELANGEGROUPE				= "/latex/Structure/Document/melangegroupe.tex";
	const DOCUMENT_RESTITUEGROUPE				= "/latex/Structure/Document/restituegroupe.tex";

	/**
	 * @brief	Constante de messages d'avertissement.
	 *
	 * @var		string
	 */
	const ERROR_ENONCE							= "\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n%% ATTENTION : QUESTION N°%02d NON PRISE EN CHARGE !\n%% Veuillez renseigner l'énoncé...\n";
	const ERROR_REPONSE							= "\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n%% ATTENTION : QUESTION N°%02d NON PRISE EN CHARGE !\n%% Veuillez proposer au moins une réponse...\n";

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $_aQCM								= array();
	private $_labelGroupe						= null;
	private $_idGroupe							= self::QUESTIONS_GROUPE_START;
	private $_current							= 0;		// Nombre d'élément dans le groupe AMC
	private $_libre								= false;	// Présence d'une réponse libre
	private	$_questionBareme					= true;		// Affiche le barème de la question
	private $_separate							= false;	// Réponses sur feuilles séparées

	/**
	 * @brief	Parcours du formulaire HTML du type :
	 *
	 * @code
	 * 	$aQCM = array(
	 * 		// GÉNÉRALITÉS ************************************************************************
	 * 		'formulaire_id'					=> "Identifiant du questionnaire (en BDD)",
	 * 		'formulaire_titre'				=> "Nom du questionnaire",
	 * 		'formulaire_validation'			=> "Mise en validation du questionnaire",
	 * 		'formulaire_presentation'		=> "Présentation du questionnaire",
	 * 		'formulaire_domaine'			=> "Identifiant du domaine du formulaire (en BDD)",
	 * 		'formulaire_sous_domaine'		=> "Identifiant du sous-domaine du formulaire en (BDD)",
	 * 		'formulaire_categorie'			=> "Identifiant de la catégorie du formulaire en (BDD)",
	 * 		'formulaire_sous_categorie'		=> "Identifiant de la sous-catégorie du formulaire (en BDD)",
	 * 		'formulaire_note_finale'		=> "Note du questionnaire, par défaut sur 20 points",
	 * 		'formulaire_penalite'			=> "Facteur de pénalité pour une mauvaise réponse aux questions à choix multiple",
	 * 		'formulaire_nb_max_reponses'	=> "Nombre de réponses maximum par question",
	 * 		'formulaire_nb_total_questions'	=> "Nombre total de questions",
	 *
	 * 		// QUESTIONNAIRE **********************************************************************
	 * 		'question_id'					=> array(
	 * 				0	=> "Identifiant de la première question (en BDD)",
	 * 				1	=> "Identifiant de la deuxième question (en BDD)",
	 * 				...
	 * 				N-1	=> "Identifiant de la Nième question (en BDD)"
	 * 		),
	 * 		'question_titre'				=> array(
	 * 				0	=> "Titre de la première question",
	 * 				1	=> "Titre de la deuxième question",
	 * 				...
	 * 				N-1	=> "Titre de la Nième question"
	 * 		),
	 * 		'question_stricte'				=> array(
	 * 				0	=> "Attente d'une réponse stricte à la première question",
	 * 				1	=> "Attente d'une réponse stricte à la deuxième question",
	 * 				...
	 * 				N-1	=> "Attente d'une réponse stricte à la Nième question"
	 * 		),
	 * 		'question_bareme'				=> array(
	 * 				0	=> "Barème attribué à la première question",
	 * 				1	=> "Barème attribué à la deuxième question",
	 * 				...
	 * 				N-1	=> "Barème attribué à la Nième question"
	 * 		),
	 * 		'question_enonce'				=> array(
	 * 				0	=> "Énoncé de la première question",
	 * 				1	=> "Énoncé de la deuxième question",
	 * 				...
	 * 				N-1	=> "Énoncé de la Nième question"
	 * 		),
	 *
	 * 		// RÉPONSES ***************************************************************************
	 * 		'reponse_id'					=> array(
	 * 				// Réponses concernant la 1ère question
	 * 				0	=> array(
	 * 						0	=>	"Identifiant de la première réponse (en BDD)",
	 * 						1	=>	"Identifiant de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Identifiant de la dernière réponse (en BDD)"
	 * 				),
	 * 				// Réponses concernant la 2ème question
	 * 				1	=> array(
	 * 						0	=>	"Identifiant de la première réponse (en BDD)",
	 * 						1	=>	"Identifiant de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Identifiant de la dernière réponse (en BDD)"
	 * 				),
	 * 				...
	 * 				// Réponses concernant la Nème question
	 * 				N-1	=> array(
	 * 						0	=>	"Identifiant de la première réponse (en BDD)",
	 * 						1	=>	"Identifiant de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Identifiant de la dernière réponse (en BDD)"
	 * 				)
	 * 		),
	 * 		'reponse_texte'					=> array(
	 * 				// Réponses concernant la 1ère question
	 * 				0	=> array(
	 * 						0	=>	"Texte de la première réponse (en BDD)",
	 * 						1	=>	"Texte de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Texte de la dernière réponse (en BDD)"
	 * 				),
	 * 				// Réponses concernant la 2ème question
	 * 				1	=> array(
	 * 						0	=>	"Texte de la première réponse (en BDD)",
	 * 						1	=>	"Texte de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Texte de la dernière réponse (en BDD)"
	 * 				),
	 * 				...
	 * 				// Réponses concernant la Nème question
	 * 				N-1	=> array(
	 * 						0	=>	"Texte de la première réponse (en BDD)",
	 * 						1	=>	"Texte de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Texte de la dernière réponse (en BDD)"
	 * 				)
	 * 		),
	 * 		'reponse_valide'		=> array(
	 * 				// Réponses concernant la 1ère question
	 * 				0	=> array(
	 * 						0	=>	"Validation de la première réponse (en BDD)",
	 * 						1	=>	"Validation de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Validation de la dernière réponse (en BDD)"
	 * 				),
	 * 				// Réponses concernant la 2ème question
	 * 				1	=> array(
	 * 						0	=>	"Validation de la première réponse (en BDD)",
	 * 						1	=>	"Validation de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Validation de la dernière réponse (en BDD)"
	 * 				),
	 * 				...
	 * 				// Réponses concernant la Nème question
	 * 				N-1	=> array(
	 * 						0	=>	"Validation de la première réponse (en BDD)",
	 * 						1	=>	"Validation de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Validation de la dernière réponse (en BDD)"
	 * 				)
	 * 		),
	 * 		'reponse_valeur'		=> array(
	 * 				// Réponses concernant la 1ère question
	 * 				0	=> array(
	 * 						0	=>	"Valeur de la première réponse (en BDD)",
	 * 						1	=>	"Valeur de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Valeur de la dernière réponse (en BDD)"
	 * 				),
	 * 				// Réponses concernant la 2ème question
	 * 				1	=> array(
	 * 						0	=>	"Valeur de la première réponse (en BDD)",
	 * 						1	=>	"Valeur de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Valeur de la dernière réponse (en BDD)"
	 * 				),
	 * 				...
	 * 				// Réponses concernant la Nème question
	 * 				N-1	=> array(
	 * 						0	=>	"Valeur de la première réponse (en BDD)",
	 * 						1	=>	"Valeur de la deuxième réponse (en BDD)",
	 * 						...
	 * 						D-1	=>	"Valeur de la dernière réponse (en BDD)"
	 * 				)
	 * 		)
	 * );
	 * @endcode
	 *
	 * @param	array	$aQCM				: tableau de construction du formulaire QCM.
	 * @return	void
	 */
	public function __construct(array $aQCM = array()) {
		// Initialisation de la variable d'instance
		$this->_aQCM	= $aQCM;

		/**
		 * @li Mélange aléatoire des questions / réponses.
		 *
		 * Valeur de la graine du générateur aléatoire utilisé pour le mélange, comprise entre 1 et 4194303.
		 */
		if ((bool) self::DOCUMENT_STATICSEED) {
			// Graine déterminée
			$nIdSeed	= self::DOCUMENT_RANDOMISEED_DEFAUT;
		} else {
			// Graine aléatoire à chaque génération du document
			$nIdSeed	= rand(self::DOCUMENT_RANDOMISEED_MIN, self::DOCUMENT_RANDOMISEED_MAX);
		}

		// Récupération des variables du document
		$this->_name	= DataHelper::get($this->_aQCM, 'formulaire_titre',				DataHelper::DATA_TYPE_LATEX,	FormulaireManager::TITRE_DEFAUT);

		// Initialisation des paramètres d'export
		$this->setFilename($this->_name);
		$this->setContentType("text/plain");
		$this->setExtension("tex");

		// Récupération du format de sortie
		$sPaperSize 	= DataHelper::get($this->_aQCM, 'generation_format',			DataHelper::DATA_TYPE_LATEX,	FormulaireManager::GENERATION_FORMAT_DEFAUT);
		$sLangue	 	= DataHelper::get($this->_aQCM, 'generation_langue',			DataHelper::DATA_TYPE_LATEX,	FormulaireManager::GENERATION_LANGUE_DEFAUT);
		$this->_separate= DataHelper::get($this->_aQCM, 'generation_separate',			DataHelper::DATA_TYPE_BOOL,		FormulaireManager::GENERATION_SEPARATE_DEFAUT);
		$nIdCustomSeed 	= DataHelper::get($this->_aQCM, 'generation_seed',				DataHelper::DATA_TYPE_ANY,		$nIdSeed);

		// Informations sur l'épreuve
		$nExemplaires	= DataHelper::get($this->_aQCM, 'generation_exemplaires',		DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::GENERATION_EXEMPLAIRES_DEFAUT);
		$sTypeEpreuve	= DataHelper::get($this->_aQCM, 'epreuve_type',					DataHelper::DATA_TYPE_LATEX,	FormulaireManager::EPREUVE_TYPE_DEFAUT);
		$sDateEpreuve	= DataHelper::get($this->_aQCM, 'epreuve_date',					DataHelper::DATA_TYPE_DATE,		date(FormulaireManager::EPREUVE_DATE_FORMAT));
		$sDureeEpreuve	= DataHelper::get($this->_aQCM, 'epreuve_duree',				DataHelper::DATA_TYPE_LATEX,	FormulaireManager::EPREUVE_DUREE_DEFAUT);
		$sLibelleEpreuve= DataHelper::get($this->_aQCM, 'epreuve_libelle',				DataHelper::DATA_TYPE_LATEX,	FormulaireManager::GENERATION_NOM_DEFAUT);

		// Suppression éventuelle des DATEs de début et de fin entre parenthèses : "Nom du stage (01/01/1970 - 31/12/9999)"
		if (preg_match("@^(.*)\s\([0-9\/\-]{2,4}.[0-9]{2}.[0-9]{2,4}@", $sLibelleEpreuve, $aMatches)) {
			// Récupération de la première partie de la chaîne
			$sLibelleEpreuve= $aMatches[1];
		}

		// Format d'identification des candidats
		$nCodeCandidat	= DataHelper::get($this->_aQCM, 'generation_code_candidat',		DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::CANDIDATS_CODE_DEFAUT);
		$sTexteCandidat	= DataHelper::get($this->_aQCM, 'generation_cartouche_candidat',DataHelper::DATA_TYPE_LATEX,	FormulaireManager::CANDIDATS_CARTOUCHE_DEFAUT);

		// Informations complémentaires sur l'épreuve destinées aux candidats
		$sPresentation	= DataHelper::get($this->_aQCM, 'formulaire_presentation',		DataHelper::DATA_TYPE_LATEX,	FormulaireManager::PRESENTATION_DEFAUT);
		$sConsignes		= DataHelper::get($this->_aQCM, 'generation_consignes',			DataHelper::DATA_TYPE_LATEX,	FormulaireManager::GENERATION_CONSIGNES_DEFAUT);

		// Affichage du barème dans la question par défaut
		$bVisibleBareme = true;

		/**
		 * @li Initialisation du document
		 *
		 * @li	Réponses intégrées au questionnaire
		 * @code
		 * 		\documentclass[%generation_format]{article}
		 *
		 * 		\usepackage[utf8x]{inputenc}
		 * 		\usepackage[T1]{fonctenc}
		 * 		\usepackage[francais,bloc,completemulti]{automultiplechoice}
		 *
		 * 		\begin{document}
		 *
		 *		\AMCrandomseed{1237893}
		 * @endcode
		 *
		 * @li	Réponses des candidats sur feuilles séparées
		 * @code
		 * 		\documentclass[%generation_format]{article}
		 *
		 * 		\usepackage[utf8x]{inputenc}
		 * 		\usepackage[T1]{fonctenc}
		 * 		\usepackage[francais,bloc,completemulti,ensemble]{automultiplechoice}
		 *
		 * 		\begin{document}
		 *
		 *		\AMCrandomseed{1237893}
		 * @endcode
		 */
		$oHeader = new LatexFormManager_Header();
		$oHeader->setPaperSize($sPaperSize);
		$oHeader->setLanguage($sLangue);
		$oHeader->setRandomSeed($nIdCustomSeed);
		$this->_document = $oHeader->render($this->_separate);

		/**
		 * @li Construction des questions
		 *
		 * @li Question à choix multiple avec le mot-clé {questionmult}.
		 * 	Le bonus {b} donne des points alors que le malus {m} en retire.
		 * @code
		 * 		\element{%question_groupe} {
		 * 		\begin{questionmult}{%question_titre[1]}
		 * 			%question_enonce[1]
		 * 				\begin{reponses}
		 * 					\bonne{%reponse_texte[1][1]}\bareme{b=2}
		 * 					\mauvaise{%reponse_texte[1][2]}\bareme{b=0,m=-1}
		 * 					\bonne{%reponse_texte[1][3]}\bareme{b=3}
		 * 					\mauvaise{%reponse_texte[1][4]}
		 * 				\end{reponses}
		 * 		\end{questionmult}
		 * 		}
		 * @endcode
		 *
		 * @li Question à choix multiple avec le mot-clé {questionmult}.
		 * 	La réponse attendue est stricte : c'est tout ou rien !
		 * @code
		 * 		\element{%question_groupe} {
		 * 		\begin{questionmult}{%question_titre[1]}\bareme{mz=2}
		 * 			%question_enonce[1]
		 * 				\begin{reponses}
		 * 					\bonne{%reponse_texte[1][1]}
		 * 					\mauvaise{%reponse_texte[1][2]}
		 * 					\bonne{%reponse_texte[1][3]}
		 * 					\mauvaise{%reponse_texte[1][4]}
		 * 				\end{reponses}
		 * 		\end{questionmult}
		 * 		}
		 * @endcode
		 *
		 * @li Question à choix unique avec le mot-clé {question}
		 * 	Le bonus {b} donne des points alors que le malus {m} en retire.
		 * @code
		 * 		\element{%question_groupe} {
		 * 		\begin{question}{%question_titre}
		 * 			%question_enonce
		 * 				\begin{reponses}
		 * 					\bonne{%reponse_texte[1]}\bareme{b=3}
		 * 					\mauvaise{%reponse_texte[2]}\bareme{b=0,m=-1}
		 * 					\mauvaise{%reponse_texte[3]}\bareme{b=0,m=-1}
		 * 					\mauvaise{%reponse_texte[4]}\bareme{b=0,m=-1}
		 * 				\end{reponses}
		 * 		\end{question}
		 * 		}
		 * @endcode
		 *
		 * @li Affichage des réponses sur 2 colonnes
		 * @code
		 * 		\element{%question_groupe} {
		 * 		\begin{question}{%question_titre}
		 * 			%question_enonce
		 * 			\begin{multicols}{2}
		 * 				\begin{reponses}
		 * 					\bonne{%reponse_texte[1]}\bareme{b=3}
		 * 					\mauvaise{%reponse_texte[2]}\bareme{b=0,m=-1}
		 * 					\mauvaise{%reponse_texte[3]}\bareme{b=0,m=-1}
		 * 					\mauvaise{%reponse_texte[4]}\bareme{b=0,m=-1}
		 * 				\end{reponses}
		 * 			\end{multicols}
		 * 		\end{question}
		 * 		}
		 * @endcode
		 */
		for ($nQuestion = 0 ; $nQuestion < $this->_aQCM['formulaire_nb_total_questions'] ; $nQuestion++) {
			// Construction du questionnaire
			$sQuestionnaire = $this->buildQuestion($nQuestion, $bVisibleBareme);

			// Arrêt de la construction s'il n'y a pas de questionnaire
			if (empty($sQuestionnaire)) {
				continue;
			} else {
				// Incrémentation du nombre de question
				$this->_current++;
				// Fonctionnalité réalisée si le groupe actuel possède déjà 20 questions
				if ($this->_current > self::QUESTIONS_GROUPE_LIMIT && ($this->_current%self::QUESTIONS_GROUPE_LIMIT == 0)) {
					$this->_idGroupe++;
				}
			}

			// Ajout du questionnaire au document
			$this->_document .= $sQuestionnaire;
		}

		/**
		 * @li Construction du format pour l'édition des exemplaires
		 *
		 * @code
		 * 		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		 *
		 * 		\exemplaire{20}{
		 * 		\noindent{\bf Titre QCM \hfill{} Test \\ Examen du  01/09/2015 \hfill{} Durée : 50 minutes}
		 *
		 * 		\vspace{2ex}
		 * @endcode
		 */
		$oExemplaire = new LatexFormManager_Exemplaire();
		$oExemplaire->setNumber($nExemplaires);
		$oExemplaire->setLabel($sLibelleEpreuve);
		// Ajout du titre de l'épreuve s'il est différent du nom du stage
		if ($sLibelleEpreuve != $this->_name) {
			$oExemplaire->setTitle($this->_name);
		}
		$oExemplaire->setDate("$sTypeEpreuve du $sDateEpreuve");
		$oExemplaire->setTime($sDureeEpreuve);
		$this->_document .= $oExemplaire->render();

		if ($this->_separate) {
			// Construction de la présentation de la feuille des énoncés
			$oEnonce = new LatexFormManager_Enonce();
			$this->_document .= $oEnonce->render();
		}

		/**
		 * @li Construction des instructions pour l'aide à la rédaction
		 *
		 * @code
		 *
		 *			\begin{center}
		 * 				Veuillez répondre aux questions du mieux que vous pouvez.
		 * 			\end{center}
		 *
		 * 			\vspace*{5mm}
		 * @endcode
		 */
		$oPresentation = new LatexFormManager_Presentation();
		$oPresentation->setText($sPresentation);
		$this->_document .= $oPresentation->render();

		/**
		 * @li Construction des instructions pour l'aide à la rédaction
		 *
		 * @code
		 *		\begin{center}
		 *			Les questions faisant apparaître le symbole \multiSymbole{} peuvent présenter zéro, une ou plusieurs bonnes réponses. Les autres ont une unique bonne réponse.
		 *		\end{center}
		 *
		 *		\noindent\hrulefill
		 * @endcode
		 */
		$oInstructions = new LatexFormManager_Instructions();
		// Récupération du contenu du fichier
		$oInstructions->setText(self::DOCUMENT_INSTRUCTIONS_TXT);
		$this->_document .= $oInstructions->render();

		/**
		 * @li Mélange du questionnaire
		 *
		 * @code
		 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		 * 			\melangegroupe{amc}
		 * 			\restituegroupe{amc}
		 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
		 * @endcode
		 */
		$oRestitue = new LatexFormManager_Restituegroupe();

		// Récupération du contenu du fichier
		$sRestitueGroupe			= file_get_contents(FW_HELPERS . self::DOCUMENT_RESTITUEGROUPE);

		// Fonctionnalité réalisée si au moins un questionnaire est présent
		if ($this->_current && !empty($this->_labelGroupe)) {
			// Parcours l'ensembre des groupes
			for ($nGroupe = 1 ; $nGroupe <= $this->_idGroupe ; $nGroupe++) {
				// Label du groupe
				$sLabelGroupe		= self::QUESTIONS_GROUPE_PARAM . $nGroupe;

				// Récupération du contenu du fichier
				$sMelangeGroupe		= file_get_contents(FW_HELPERS . self::DOCUMENT_MELANGEGROUPE);
				$oRestitue->addMixedGroup(sprintf($sMelangeGroupe, $sLabelGroupe));
				$oRestitue->addMixedGroup(sprintf($sRestitueGroupe, $sLabelGroupe));
			}
		}

		if ($this->_separate) {
			// Rendu des questions avant la génération de l'entête de la feuille des réponses séparées
			$this->_document .= $oRestitue->render();

			/**
			 * @li Construction du format pour la page de garde des réponses séparées
			 *
			 * @code
			 *		\AMCcleardoublepage
			 *		\AMCdebutFormulaire
			 *		\noindent{\bf %s \hfill %s \\ %s \hfill Durée : %d minutes}
			 *
			 *		\noindent\hrulefill
			 *		\vspace*{5mm}
			 *
			 *		\begin{center}
			 *			{\large\bf Feuille des réponses :}
			 *		\end{center}
			 * @endcode
			 */
			$oSeparate = new LatexFormManager_Separate();
			$oSeparate->setTitle($this->_name);
			$oSeparate->setLabel($sLibelleEpreuve);
			$oSeparate->setDate("$sTypeEpreuve du $sDateEpreuve");
			$oSeparate->setTime($sDureeEpreuve);
			$this->_document .= $oSeparate->render();
		}

		/**
		 * @li Construction de l'identification des candidats
		 *
		 * @code
		 * 			\begin{center}
		 * 				Veuillez remplir complètement chaque case au stylo à encre noir ou bleu-noir afin de reporter vos choix de réponse. Les encres de couleur claires, fluorescentes ou effaçables sont interdites.
		 * 				Pour toute correction, veuillez utiliser du blanc correcteur exclusivement.
		 * 				DANS CE DERNIER CAS, NE REDESSINEZ PAS LA CASE !
		 * 			\end{center}
		 * @endcode
		 */
		$oConsignes = new LatexFormManager_Consignes();
		$oConsignes->setText($sConsignes);
		$this->_document .= $oConsignes->render();

		/**
		 * @li Construction de l'identification des candidats
		 *
		 * @code
		 * 			\begin{minipage}{.4\linewidth}
		 * 				\champnom{
		 * 					\fbox{
		 * 						\begin{minipage}{.9\linewidth}
		 * 							Code candidat :
		 *
		 * 							\vspace*{5mm}\dotfill
		 * 							\vspace*{1mm}
		 * 						\end{minipage}
		 * 					}
		 * 				}
		 * 				\vspace{3ex}
		 * 				Codez votre code candidat à l’aide
		 * 				des cases ci-contre en reportant chaque
		 * 				numéro de gauche à droite
		 * 			\end{minipage}
		 * 			\begin{minipage}{.1\linewidth}
		 * 				$\longrightarrow{}$
		 * 			\end{minipage}
		 * 			\begin{minipage}{.5\linewidth}
		 * 				\noindent\AMCcode{code}{%d}\hspace*{\fill}
		 * 			\end{minipage}
		 * @endcode
		 */
		$oCandidat = new LatexFormManager_Candidat();
		$oCandidat->setText($sTexteCandidat);
		$oCandidat->setCountCode($nCodeCandidat);
		$this->_document .= $oCandidat->render();

		if ($this->_separate) {
			$oRestitue = new LatexFormManager_Restituegroupe();

			// Ajout d'une ligne pleine sur la largeur de la page
			$oLigne	= new LatexFormManager_Hrulefill();
			$oLigne->addContent("\t\\formulaire\n");
			//$this->_document .= $oLigne->render(1, "cm");
			$this->_document .= $oLigne->render();
		}

		// Fonctionnalité réalisée si au moins un questionnaire LIBRE est présent
		if ($this->_libre) {
			$oRestitue->addMixedGroup(sprintf($sRestitueGroupe, self::REPONSE_OUVERTE));
			// Fermeture du document
			$this->_document .= $oRestitue->render();
		}

		// Fermeture du document
		$oEnder = new LatexFormManager_Closure();
		$this->_document .= $oEnder->render();
	}

	/**
	 * @brief	Récupère le nombre de réponses renseignées à la question.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @return	integer
	 */
	private function _getNbReponses($nQuestion) {
		// Initialisation du résultat
		$nCount = 0;
		// Boucle de parcours des réponses
		for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
			// Fonctionnalité réalisée pour chaque réponse valide
			if (isset($this->_aQCM['reponse_texte'][$nQuestion][$nReponse]) && !empty($this->_aQCM['reponse_texte'][$nQuestion][$nReponse])) {
				$nCount++;
			}
		}
		// Renvoi du résultat
		return $nCount;
	}

	/**
	 * @brief	Vérifie si la longueur des réponses est compatible avec les colonnes.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @return	boolean
	 */
	private function _isValideReponsesMaxLenght($nQuestion) {
		// Initialisation du résultat
		$bValide = true;
		// Boucle de parcours des réponses
		for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
			// Fonctionnalité réalisée pour chaque réponse valide
			$sReponse = DataHelper::get($this->_aQCM['reponse_texte'][$nQuestion], $nReponse, DataHelper::DATA_TYPE_TXT);
			if (strlen($sReponse) > self::REPONSES_COLONNE_MAXLENGTH) {
				$bValide = false;
			}
		}
		// Renvoi du résultat
		return $bValide;
	}

	/**
	 * @brief	Récupère le nombre de réponses valides proposées dans la question.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @return	integer
	 */
	private function _getValideResponses($nQuestion) {
		// Initialisation du résultat
		$nCount = 0;

		// Boucle de parcours des réponses
		for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
			// Fonctionnalité réalisée pour chaque réponse
			if (isset($this->_aQCM['reponse_valide'][$nQuestion][$nReponse])) {
				// Le contenu de la réponse n'est pas vide : TRUE
				$nCount += (int) !empty($this->_aQCM['reponse_valide'][$nQuestion][$nReponse]);
			}
		}

		// Renvoi du résultat
		return $nCount;
	}

	/**
	 * @brief	Récupère le nombre total de réponses proposées dans la question.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @return	integer
	 */
	private function _getNombreResponses($nQuestion) {
		// Initialisation du résultat
		$nCount = 0;

		// Boucle de parcours des réponses
		for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
			// Fonctionnalité réalisée pour chaque réponse
			if (isset($this->_aQCM['reponse_texte'][$nQuestion][$nReponse])) {
				// Le contenu de la réponse n'est pas vide : TRUE
				$nCount += (int) !empty($this->_aQCM['reponse_texte'][$nQuestion][$nReponse]);
			}
		}

		// Renvoi du résultat
		return $nCount;
	}

	/**
	 * @brief	Calcul du bonus d'une réponse.
	 *
	 * @param	string	$sStatus			: status de la réponse.
	 * @param	decimal	$dBareme			: barème de la question.
	 * @param	percent	$pValeur			: la valeur de la réponse, en pourcentage.
	 * @return	float
	 */
	static private function setBonus($sStatus, $dBareme, $pValeur) {
		$fBonus	= 0;
		// Détermination du bonus en %
		if ($sStatus == self::REPONSE_VRAIE) {
			$fBonus	= (float) ($dBareme * $pValeur) / 100;
		}
		// Renvoi de la valeur absolue
		return abs($fBonus);
	}

	/**
	 * @brief	Calcul du malus d'une réponse.
	 *
	 * @param	string	$sStatus			: status de la réponse.
	 * @param	decimal	$dBareme			: barème de la question.
	 * @param	percent	$pPenalite			: pénalité de la réponse, en pourcentage.
	 * @return	float
	 */
	static private function setMalus($sStatus, $dBareme, $pPenalite) {
		$fMalus	= 0;
		// Détermination du malus en %
		if ($sStatus == self::REPONSE_FAUSSE) {
			$fMalus	= (float) ($dBareme * $pPenalite) / 100;
		}
		// Renvoi de la valeur absolue
		return abs($fMalus);
	}

	/**
	 * @brief	Construction de la réponse à la question.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @param	integer	$nReponse			: occurrence de la réponse à la question passée en paramètre.
	 * @param	string	$sType				: type de réponses attendues.
	 * @param	integer	$nNombreValides		: nombre de réponses valides à la question.
	 * @param	boolean	$bStricte			: (optionnel) si une réponse stricte est attendue (tout ou rien)
	 * @return	string
	 */
	protected function buildReponse($nQuestion, $nReponse, $sType, $nNombreValides, $bStricte = false) {
		// Initialisation du status de la réponse avec un caractère d'indicateur de `bonne` ou `mauvaise` réponse
		$sStatus				= empty($this->_aQCM['reponse_valide'][$nQuestion][$nReponse]) ? self::REPONSE_FAUSSE : self::REPONSE_VRAIE;
		$fBonus					= 0;
		$fMalus					= 0;
		$fPenaliteReponse		= 0;

		// Récupération de la sanction de la réponse (en nombre de points)
		$bSanction				= DataHelper::get($this->_aQCM['reponse_sanction'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_BOOL);
		if ($bSanction) {
			$fPenaliteReponse	= DataHelper::get($this->_aQCM['reponse_penalite'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS);
		}

		// Fonctionnalité réalisée si aucune sanction ne concerne la réponse
		if (empty($fPenaliteReponse)) {
			// Récupération du barème de la question
			$dBareme			= DataHelper::get($this->_aQCM['question_bareme'],					$nQuestion,	DataHelper::DATA_TYPE_MYFLT_ABS);
			// Récupération du facteur de pénalité de la question (en %)
			$pPenalite			= DataHelper::get($this->_aQCM['question_penalite'],				$nQuestion,	DataHelper::DATA_TYPE_MYFLT_ABS);
			// Récupération de la valeur de la réponse (en %)
			$pValeur			= DataHelper::get($this->_aQCM['reponse_valeur'][$nQuestion], 		$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS);
			// Calcul du bonus
			$fBonus				= self::setBonus($sStatus, $dBareme, $pValeur);
			// Calcul du malus
			$fMalus				= self::setMalus($sStatus, $dBareme, $pPenalite);
		}

		// Texte de la réponse
		$sTexte					= DataHelper::get($this->_aQCM['reponse_texte'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_LATEX);

 		$sReponse = "";
		// Fonctionnalité réalisé si le texte est renseigné correctement
		if (! empty($sTexte)) {
			switch ($sType) {

				// Réponse MULTIPLES
				case self::QUESTION_MULTIPLE_PARAM:
					if ($fPenaliteReponse && !$bStricte) {
						// Retrait de points
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_PERDU);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $fPenaliteReponse);
					} elseif ($bStricte || (empty($fBonus) && empty($fMalus))) {
						// AUCUN barème attribué à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_VIDE);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte);
					} elseif ($nNombreValides > 0 && empty($fMalus)) {
						// Barème POSITIF attribué à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_BONUS);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $fBonus);
					} elseif ($nNombreValides > 0) {
						// Barème NÉGATIF attribué à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_MALUS);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $fBonus, $fMalus);
					} else {
						// AUCUN barème attribué à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_VIDE);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte);
					}
					break;

				// Réponse SIMPLE
				default:
					if ($fPenaliteReponse) {
						// Retrait de points
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_NEGATIF);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $fPenaliteReponse);
					} elseif ($sStatus == self::REPONSE_VRAIE) {
						// Total du BARÈME attribuée à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_DEFAUT);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $dBareme);
					} elseif ($fMalus) {
						// Valeur NÉGATIVE attribuée à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_NEGATIF);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte, $fMalus);
					} else {
						// AUCUN barème attribué à la réponse
						$sFileContents = file_get_contents(FW_HELPERS . self::REPONSE_FORMAT_VIDE);
						// Récupération du contenu du fichier
						$sReponse .= sprintf($sFileContents,	$sStatus, $sTexte);
					}
				break;
			}
		}
		// Renvoi du résultat
		return $sReponse;
	}

	/**
	 * @brief	Construction de l'ensembe des réponses à la question.
	 *
	 * @li La construction est annulée si aucune réponse n'est rédigée.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @param	string	$sType				: type de réponses attendues.
	 * @param	integer	$nNombreValides		: nombre de réponses valides à la question.
	 * @param	boolean	$bStricte			: (optionnel) si une réponse stricte est attendue (tout ou rien)
	 * @return	string
	 */
	protected function buildEnsembleReponses($nQuestion, $sType, $nNombreValides, $bStricte = false) {
		$nCount			= 0;
		$sReponse		= "";
		// Boucle de parcours des réponses
		for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
			// Construction de la réponse
			$sReponse .= $this->buildReponse($nQuestion, $nReponse, $sType, $nNombreValides, $bStricte);
			$nCount++;
		}

		// Arrêt de la construction s'il n'y a pas de réponse
		if (empty($sReponse)) {
			return false;
		}

		// Construction de l'ensemble des réponses
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_REPONSES_FORMAT);
		// Récupération du contenu du fichier
		return sprintf($sFileContents, $sReponse);
	}

	/**
	 * @brief	Détermine le nombre de colonne selon le nombre de réponses à la question.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @param	integer	$nMaxReponses		: nombre de réponses à partir du quel l'affichage passe sur 2 colonnes, par défaut [3].
	 * @return	string
	 */
	public function setNbColonnes($nQuestion, $nMaxReponses = self::REPONSES_COLONNE_MULTIPLE) {
		// Initialisation du nombre de colonnes par défaut
		$nColonnes = 1;
		// Passage à une colonne supplémentaire si le nombre de réponses est atteint
		if ((bool) self::DOCUMENT_MULTICOLONNE && $this->_getNbReponses($nQuestion) > $nMaxReponses && $this->_isValideReponsesMaxLenght($nQuestion)) {
			$nColonnes++;
		}
		// Renvoi du nombre de colonnes
		return $nColonnes;
	}

	/**
	 * @brief	Construction des colonnes multiples.
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @param	string	$sEnsembleReponses	: ensemble des réponses à la question;
	 * @param	integer	$nMaxReponses		: nombre de réponses à partir du quel l'affichage passe sur 2 colonnes, par défaut [3].
	 * @return	string
	 */
	public function buildColonnes($nQuestion, $sEnsembleReponses, $nMaxReponses = self::REPONSES_COLONNE_MULTIPLE) {
		// Détermination du nombre de colonnes
		$nColonnes	= $this->setNbColonnes($nQuestion, $nMaxReponses);

		if ($nColonnes > 1) {
			// Injection des informations de colonnes
			$sFileContents		= file_get_contents(FW_HELPERS . self::DOCUMENT_COLONNES_MULTICOLS);
			// Récupération du contenu du fichier
			$sEnsembleReponses	= sprintf($sFileContents, $nColonnes, $sEnsembleReponses);
		}

		// Renvoi du résultat
		return $sEnsembleReponses;
	}

	/**
	 * @brief	Construction de la question.
	 *
	 * @li La construction est annulée si aucune réponse n'est rédigée.
	 *
	 * @li ATTENTION : La construction des groupes de question est limité à 20 éléments.
	 *
	 * @li Question construite avec une seule colonne
	 * @code
	 * 		\element{%question_groupe}{
	 *			\begin{questionmult}{Q01}
	 *				Énoncé de la question
	 *					\begin{reponses}
	 *						\mauvaise{Oui}\bareme{b=0,m=-0.1}
	 *						\bonne{Non}\bareme{b=1}
	 *					\end{reponses}
	 *			\end{questionmult}
	 *		}
	 * @endcode
	 *
	 * @li Question construite avec deux colonnes
	 * @code
	 * 		\element{%question_groupe}{
	 *			\begin{questionmult}{Q01}
	 *				Énoncé de la question
	 *				\begin{multicols}{2}
	 *					\begin{reponses}
	 *						\mauvaise{Réponse 1}\bareme{b=0,m=-0.1}
	 *						\bonne{Réponse 2}\bareme{b=0.5}
	 *						\mauvaise{Réponse 3}\bareme{b=0,m=-0.1}
	 *						\bonne{Réponse 4}\bareme{b=0.5}
	 *					\end{reponses}
	 *				\end{multicols}
	 *			\end{questionmult}
	 *		}
	 * @endcode
	 *
	 * @li Question libre construite avec une zone réservée à la correction
	 * @li L'option [o] permet de préserver l'odre des cases à cocher
	 * @code
	 * 		\element{%question_groupe}{
	 *			\begin{questionmult}{Q01}
	 *				Énoncé de la question
	 *				\\
	 *				\fbox{
	 *					\begin{minipage}{1\textwidth}
	 *						\begin{center}~\\~\\~\\~\\~\\~\\~\\~\\~\\~\\~\\~\\~\\~\\~\end{center}
	 *					\end{minipage}
	 *				}
	 *				\begin{choicescustom}[o]
	 *					\color{red}
	 * 					\AMCboxColor{red}
	 *					\bf{Réservé à la correction}\hfill{}
	 *					\correctchoice[A]{exact}\scoring{1}~~
	 *					\wrongchoice[E]{faux}\scoring{0}
	 *				\end{choicescustom}
	 *			\end{questionmult}
	 *		}
	 * @endcode
	 *
	 * @param	integer	$nQuestion			: occurrence de la question.
	 * @return	string
	 */
	public function buildQuestion($nQuestion, $bVisibleBareme = true) {
		// Identifiant de la question
		$idQuestion		= sprintf(self::DOCUMENT_QUESTIONS_ID, intval($nQuestion + 1));

		// Nombre de réponses valides proposées à la question
		$nNombreValides	= $this->_getValideResponses($nQuestion);

		// Nombre total de réponses proposées à la question
		$nNombreReponses= $this->_getNombreResponses($nQuestion);

		// Type de question selon le nombre de réponses valides (AUCUNE ou au minimum 2)
		$sType			= ($nNombreValides == 0 || $nNombreValides > 1) ? self::QUESTION_MULTIPLE_PARAM : self::QUESTION_SIMPLE_PARAM;
		// Titre de la question
		$sTitre			= DataHelper::get($this->_aQCM['question_titre'],		$nQuestion, DataHelper::DATA_TYPE_TXT);
		// Attente d'une réponse stricte à la question
		$bStricte		= DataHelper::get($this->_aQCM['question_stricte'],		$nQuestion, DataHelper::DATA_TYPE_BOOL);
		// Attente d'une réponse libre à la question
		$bLibre			= DataHelper::get($this->_aQCM['question_libre'],		$nQuestion, DataHelper::DATA_TYPE_BOOL);
		// Récupération du barème globale pour la question
		$fBareme		= DataHelper::get($this->_aQCM['question_bareme'],		$nQuestion, DataHelper::DATA_TYPE_MYFLT);
		// Récupération du pourcentage de la pénalité à la question
		$pPenalite		= DataHelper::get($this->_aQCM['question_penalite'],	$nQuestion, DataHelper::DATA_TYPE_INT_ABS);

		// Initialisation de l'énoncé avec le barème de la question, sinon retourne à la ligne au format LaTeX
		$sEnonce		= "";
		if ($bVisibleBareme) {
			$sPluriel	= $fBareme > 1 ? "s" : "";
			$sEnonce	= sprintf("(%s point" . $sPluriel . ")", str_replace(".", ",", $fBareme));
		}

		// Forçage du retour à la ligne pour l'énoncé
		$sEnonce		.= self::DOCUMENT_LN;

		// Ajout de l'énoncé de la question
		$sSujetQuestion	= DataHelper::get($this->_aQCM['question_enonce'],		$nQuestion, DataHelper::DATA_TYPE_LATEX);

		// Construction du sujet avec l'énoncé
		$sEnonceQuestion	= $sEnonce . $sSujetQuestion;

		// Fonctionnalité réalisée si aucun énoncé n'est présent
		if (empty($sSujetQuestion)) {
			// Affichage d'un message d'avertissement afin de préciser que l'énoncé est vide
			$sQuestion = sprintf(self::ERROR_ENONCE, intval($nQuestion + 1));
		} elseif ($bLibre) {
			// Fonctionnalité réalisée si la saisie de la réponse est libre
			$this->_libre	= true;
			$sType			= self::QUESTION_SIMPLE_PARAM;

			// Récupération du barème de la question
			$fNoteA			= $this->_aQCM['question_bareme'][$nQuestion];	// *****
			$fNodeD			= $fNoteA/4;									// **
			$fNodeC			= $fNoteA/2;									// ***
			$fNoteB			= $fNoteA - $fNodeD;							// ****
			$fNoteE			= 0;											// *

			// Fonctionnalité réalisée si la mauvaise note doit subir un facteur de pénalité
			if ($fNoteE == 0 && $pPenalite > 0) {
				// Attibution d'une note négative
				$fNoteE		= - $fNoteA * $pPenalite / 100;
			}

			// Récupération du nombre de lignes pour la réponse libre
			$nLignes		= DataHelper::get($this->_aQCM['question_lignes'],	$nQuestion, DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::QUESTION_LIBRE_LIGNES_DEFAUT);

			$sLignesCadre	= "~";
			// Ajout du nombre de lignes dans le cadre réservé à la réponse
			for ($col = 1 ; $col < $nLignes ; $col++) {
				// Ajout d'une ligne vide
				$sLignesCadre	.= self::DOCUMENT_LN . "~";
			}

			// Ajout du cadre destiné à la réponse libre à la suite de l'énoncé
			$sFileContents		= file_get_contents(FW_HELPERS . self::DOCUMENT_COLONNES_LIBRECADRE);
			// Construction du cadre de réponse
			$sEnonceQuestion	.= sprintf($sFileContents, $sLignesCadre);

			// Ajout de la zone réservée au correcteur
			if ($bStricte) {
				// Réponse STRICTE	: 2 notes possibles (tout ou rien)
				$sFileContents	= file_get_contents(FW_HELPERS . self::CORRECTION_FORMAT_STRICT);
				// Récupération du contenu du fichier
				$sCadreReserve	= sprintf($sFileContents, $fNoteA, $fNoteE);
			} else {
				// Réponse SOUPLE	: 5 notations possibles
				$sFileContents	= file_get_contents(FW_HELPERS . self::CORRECTION_FORMAT_SOUPLE);
				// Récupération du contenu du fichier
				$sCadreReserve	= sprintf($sFileContents, $fNoteA, $fNoteB, $fNodeC, $fNodeD, $fNoteE);
			}

			// Mise en forme
			$sFileContents		= file_get_contents(FW_HELPERS . self::DOCUMENT_REPONSES_LIBRE);
			// Récupération du contenu du fichier
			$sContenuColonnes	= sprintf($sFileContents, $sCadreReserve);

			// Construction de la question
			$sFileContents		= file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_DEFAUT);
			// Récupération du contenu du fichier
			$sQuestion			= sprintf($sFileContents, self::REPONSE_OUVERTE, $sType, $idQuestion, $sEnonceQuestion, $sContenuColonnes, $sType);
		} else {
			// Label du groupe de question
			$this->_labelGroupe = self::QUESTIONS_GROUPE_PARAM . $this->_idGroupe;

			// Construction de l'ensemble des réponses
			$sEnsembleReponses	= $this->buildEnsembleReponses($nQuestion, $sType, $nNombreValides, $bStricte);

			// Fonctionnalité réalisée si l'énoncé et les réponses sont valides
			if (!empty($sEnsembleReponses) && !empty($sSujetQuestion)) {
				// Construction des colonnes selon le nombre de réponses
				$sContenuColonnes = $this->buildColonnes($nQuestion, $sEnsembleReponses);

				// Construction de la question
				if ((bool) $bStricte && $sType == self::QUESTION_MULTIPLE_PARAM) {
					// Ajout de l'ensemble des réponses à la question avec entête TOUT OU RIEN
					$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_MZ);
					// Récupération du contenu du fichier
					$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $fBareme, $sEnonceQuestion, $sContenuColonnes, $sType);
				}
				// Question à réponses multiples
				elseif ($sType == self::QUESTION_MULTIPLE_PARAM && $nNombreValides > 1) {
					// Ajout de l'ensemble des réponses à la question avec entête MAX
					$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_MAX);
					// Récupération du contenu du fichier
					$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $fBareme, $sEnonceQuestion, $sContenuColonnes, $sType);
				}
				// Question à réponse "Aucune de ces réponses n'est correcte."
				elseif ($sType == self::QUESTION_MULTIPLE_PARAM && $nNombreValides == 0) {
					// Détermination du BONUS global pour la question
					$fBonus		= $fBareme / $nNombreReponses;

					// Détermination du MALUS global pour la question
					$fMalus		= self::setMalus(self::REPONSE_FAUSSE, $fBareme, $pPenalite);

					if ($fMalus != 0) {
						// Ajout de l'ensemble des réponses à la question avec entête MALUS
						$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_MALUS);

						// Récupération du contenu du fichier
						$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $fBonus, $fMalus, $sEnonceQuestion, $sContenuColonnes, $sType);
					} elseif ($fBonus > 0) {
						// Ajout de l'ensemble des réponses à la question avec entête BONUS
						$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_BONUS);

						// Récupération du contenu du fichier
						$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $fBonus, $sEnonceQuestion, $sContenuColonnes, $sType);
					} else {
						// Ajout de l'ensemble des réponses à la question sans entête
						$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_DEFAUT);

						// Récupération du contenu du fichier
						$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $sEnonceQuestion, $sContenuColonnes, $sType);
					}
				}
				// Question sans entête
				else {
					// Ajout de l'ensemble des réponses à la question sans entête
					$sFileContents = file_get_contents(FW_HELPERS . self::QUESTION_FORMAT_DEFAUT);
					// Récupération du contenu du fichier
					$sQuestion = sprintf($sFileContents, $this->_labelGroupe, $sType, $idQuestion, $sEnonceQuestion, $sContenuColonnes, $sType);
				}
			} else {
				// Affichage d'un message d'avertissement afin de préciser que l'énoncé est vide
				$sQuestion = sprintf(self::ERROR_REPONSE, intval($nQuestion + 1));
			}
		}

		// Renvoi du résultat
		return $sQuestion;
	}

}
