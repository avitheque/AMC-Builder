<?php
/**
 * @brief	Classe de gestion des imports de fichiers au format AMC-TXT.
 *
 * L'ensemble du fichier est parcouru afin de générer un tableau associatif exploité par le formulaire HTML.
 *
 * @li	L'application AMC pouvant interpréter certains caractères spéciaux, ceux-ci doivent être éliminés dès l'importation...
 *
 * @name		ImportQuestionnaireManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 101 $
 * @since		$LastChangedDate: 2018-01-13 17:07:07 +0100 (Sat, 13 Jan 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ImportQuestionnaireManager extends ImportManager {

	/**
	 * @brief	Format de recherche d'une catégorie dans le fichier d'importation.
	 * @var		regexp
	 */
	const		CATEGORY_PATTERN	= "@CATEGORY:\s.*\/(.*)$@";

	/**
	 * @brief	Détermination des éléments du référentiel.
	 * @var		integer
	 */
	private		$_nDomaine			= null;
	private		$_nCategorie		= null;

	/**
	 * @brief	Caractères de remplacement du titre.
	 * @var		array
	 */
	private		$_aTitreReplace		= array(
		"?"		=> "",
		":"		=> "",
	);

	/**
	 * @brief	Caractères de remplacement de l'énoncé.
	 * @var		array
	 */
	private		$_aEnonceReplace	= array(
		"::"	=> ""
	);

	/**
	 * @brief	Caractères de remplacement dans chaque correction de réponse.
	 * @var		array
	 */
	private		$_aCorrectionReplace= array(
		"#"		=> ""
	);

	/**
	 * @brief	Caractères de remplacement dans chaque réponse proposée.
	 * @var		array
	 */
	private		$_aReponseReplace	= array(
		"#"		=> ""
	);

	/**
	 * @brief	Formatage d'une chaîne au format LaTeX.
	 *
	 * @param	string	$sString		: Chaîne de caractères à formater.
	 * @param	array	$aReplace		: Tableau de substitution des caractères interprétés par LaTeX de l'application AMC.
	 * @return	string
	 */
	protected function formatTextToLatex($sString = null, array $aReplace = array()) {
		// Traitement commun
		$sString = strtr($sString, array(
				"\\n"			=> chr(10),
				"\\:"			=> ":",
				"\\="			=> "=",
				" "				=> chr(32)			// ATTENTION : caractère semi-graphique !
			)
		);

		// Traitement ciblé
		return DataHelper::convertToText(strtr(strip_tags($sString), $aReplace));
	}

	/**
	 * @brief	Extraction du titre de la question.
	 *
	 * @code
	 * 	::Question 1:: ﻿L'interface graphique par défaut sous Linux se nomme ?
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant le titre de la question.
	 * @return	string
	 */
	protected function extractTitre($sString) {
		// Initialisation du titre
		$sTitre = null;

		// Extraction du titre
		if (preg_match('@^\:\:(.*)\:\:.*$@', $sString, $aMatches)) {
			$sTitre = $aMatches[1];
		}

		// Renvoi du résultat adapté au format LaTeX
		return $this->formatTextToLatex($sTitre, $this->_aTitreReplace);
	}

	/**
	 * @brief	Extraction de l'énoncé de la question.
	 *
	 * @code
	 * 	::﻿Interface Linux::[html]﻿L'interface graphique par défaut sous <em>Linux</em> se nomme \:{
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant l'énoncé de la question.
	 * @return	string
	 */
	protected function extractEnonce($sString) {
		// Initialisation de l'énoncé
		$sEnonce = null;

		if (preg_match('@.*\:\:\[.*\](.*)\{+.*$@', $sString, $aMatches)) {
			$sEnonce = $aMatches[1];
		} elseif (preg_match('@.*\:\:(.*)\{+.*$@', $sString, $aMatches)) {
			$sEnonce = $aMatches[1];
		} elseif (preg_match('@.*\:\:(.*)$@', $sString, $aMatches)) {
			$sEnonce = $aMatches[1];
		} elseif (preg_match('@(.*)\{+.*$@', $sString, $aMatches)) {
			$sEnonce = $aMatches[1];
		} else {
			$sEnonce = $sString;
		}

		// Renvoi du résultat adapté au format LaTeX
		return $this->formatTextToLatex($sEnonce, $this->_aEnonceReplace);
	}

	/**
	 * @brief	Extraction du texte de la correction.
	 *
	 * Il est possible qu'une correction soit ajoutée après la réponse destinée au candidat.
	 * Dans ce cas, la chaîne de la correction commence après le caractère [#].
	 *
	 * @li Différenciation du caractère [#] réservé à la correction de celui éventuellement présent dans la réponse [\#].
	 * @code
	 * 		// Réponse proposée				| Correction				# Commentaire
	 * 		[a-zA-Z0-9]+[\\#*]Ceci est la partie à prendre en compte ! [#]Commentaire de la réponse\n
	 * @endcode
	 *
	 * @li	Réponse unique
	 * @code
	 * 	=La réponse présentée au candidat# Correction de la réponse
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant l'énoncé de la question.
	 * @return	string
	 */
	protected function extractCorrection($sString) {
		// Initialisation de l'énoncé
		$sTexteCorrection = null;
		// Extraction de la correction de la réponse proposée au candidat
		if (preg_match("@^[\=|\~](\%.*\%){0,1}(.*)[#]\s{0,1}(.+)$@", trim($sString), $aMatches)) {
			$sTexteCorrection = $aMatches[2];
		}

		// Renvoi du résultat adapté au format LaTeX
		return $this->formatTextToLatex($sTexteCorrection, $this->_aCorrectionReplace);
	}

	/**
	 * @brief	Extraction du texte de la réponse.
	 *
	 * Dans le cas d'une réponse ne contenant pas de caractère [espace],
	 * la chaîne peut se terminer éventuellement par le caractère [#].
	 *
	 * @li	Réponse unique
	 * @code
	 * 	=La réponse est unique et fait gagner tous les points du barème.
	 * @endcode
	 *
	 * @li	Réponse mauvaise
	 * @code
	 * 	~La réponse est mauvaise mais ne retire aucun point.
	 * @endcode
	 *
	 * @li	Réponse BONUS
	 * @code
	 * 	~%33.333%La réponse est bonne, mais il y en a encore deux autres...
	 * @endcode
	 *
	 * @li	Réponse MALUS
	 * @code
	 * 	~%-50%La réponse est mauvaise mais fait retirer la moitié des points du barème.
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant l'énoncé de la question.
	 * @return	string
	 */
	protected function extractReponse($sString) {
		// Initialisation de la réponse
		$sTexteReponse = null;
		// Extraction de la réponse proposée au candidat
		if (preg_match("@^[\=|\~](\%.*\%){0,1}(.*)[#]\s{0,1}.+$@", trim($sString), $aMatches)) {
			$sTexteReponse = $aMatches[2];
		} elseif (preg_match("@^[\=|\~](\%.*\%)*(.*)$@", trim($sString), $aMatches)) {
			$sTexteReponse = $aMatches[2];
		}

		// Renvoi du résultat adapté au format LaTeX
		return $this->formatTextToLatex($sTexteReponse, $this->_aReponseReplace);
	}

	/**
	 * @brief	Extraction du BONUS de la réponse.
	 *
	 * @li	Réponse unique
	 * @code
	 * 	=La réponse est unique et fait gagner tous les points du barème.
	 * @endcode
	 *
	 * @li	Réponse mauvaise
	 * @code
	 * 	~La réponse est mauvaise mais ne retire aucun point.
	 * @endcode
	 *
	 * @li	Réponse BONUS
	 * @code
	 * 	~%33.333%La réponse est bonne, mais il y en a encore deux autres...
	 * @endcode
	 *
	 * @li	Réponse MALUS
	 * @code
	 * 	~%-50%La réponse est mauvaise mais fait retirer la moitié des points du barème.
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant une réponse à la question.
	 * @return	float
	 */
	protected function extractBonus($sString) {
		// Initialisation du BONUS
		$fBonus = 0;
		// Extraction BONUS dans le texte
		if (preg_match("@^[\=\~]\%([^\-][0-9\.]+)\%.*$@", trim($sString), $aMatches)) {
			$fBonus = $aMatches[1];
		} elseif (preg_match("@^\=(.*)$@", trim($sString))) {
			$fBonus = FormulaireManager::BONUS_MAX;
		}

		// Renvoi du résultat
		return $fBonus;
	}

	/**
	 * @brief	Extraction du MALUS de la réponse.
	 *
	 * @li	Réponse mauvaise
	 * @code
	 * 	~La réponse est mauvaise mais ne retire aucun point.
	 * @endcode
	 *
	 * @li	Réponse MALUS
	 * @code
	 * 	~%-50%La réponse est mauvaise mais fait retirer la moitié des points du barème.
	 * @endcode
	 *
	 * @param	string	$sString		: Chaîne de caractères comportant le texte de la réponse.
	 * @return	string
	 */
	protected function extractMalus($sString) {
		// Initialisation du MALUS
		$fMalus = 0;
		// Extraction MALUS dans le texte
		if (preg_match("@^\~\%\-([0-9\.]*)\%.*$@", trim($sString), $aMatches)) {
			$fMalus = $aMatches[1];
		}
		// Renvoi du résultat
		return $fMalus;
	}

	/**
	 * @brief	Extraction de la SANCTION de la réponse.
	 */
	protected function extractSanction($sString) {
		// Initialisation de la sanction
		$fSanction = 0;

		/** @todo Fonctionnalité à réaliser selon les importations */

		return $fSanction;
	}

	/**
	 * @brief	Extraction des réponses à la question.
	 *
	 * @li	Calcul la répartition des BONUS / MALUS sur chaque réponse.
	 * @li	S'il n'y a qu'une seule réponse, il s'agit d'un VRAI / FAUX.
	 *
	 * @exemple	Une seule réponse valide
	 * 	::Quelle est l'environnement graphique d'Ubuntu 12.04LTS par défaut \:::
	 * @code
	 * 	{
	 * 		=Unity
	 * 		~KDE
	 * 		~XFCE
	 * 		~Gnome
	 * 	}
	 * @endcode
	 *
	 * @exemple	Plusieurs réponses possibles
	 * @code
	 * 	::Quelles sont les distributions Linux utilisant les paquet *.DEB \:::
	 * 	{
	 * 		~RedHat
	 * 		~%33.333%Ubuntu
	 * 		~%33.333%Debian
	 * 		~%33.333%XuBuntu
	 * 		~OpenSuze
	 * 	}
	 * @endcode
	 *
	 * @exemple	Réponse BONUS / MALUS
	 * @code
	 * 	::Quelles sont les distributions Linux utilisant les paquet *.DEB \:::
	 * 	{
	 * 		~%-50%RedHat
	 * 		~%50%Ubuntu
	 * 		~%50%Debian
	 * 		~%-50%OpenSuze
	 * 	}
	 * @endcode
	 *
	 * @exemple	Réponse VRAI / FAUX
	 * @code
	 * 	::Ubuntu est une distribution mono-utilisateur et multi-tâches{FALSE}
	 * @endcode
	 *
	 * @param	array	$nQuestion		: Occurrence de la question.
	 * @param	array	$aReponses		: Liste des réponses à la question.
	 * @return	array, tableau de chaque réponse composés de 3 éléments
	 * @code
	 * 		$aListeReponses	= array(
	 * 			'texte_reponse'	=> array(
	 * 				0	=> "Texte de la première réponse",
	 * 				1	=> "Texte de la deuxième réponse",
	 * 				...
	 * 				D-1	=> "Texte de la dernière réponse",
	 * 			),
	 * 			'bonus_reponse'	=> array(
	 * 				0	=> "Bonus de la première réponse",
	 * 				1	=> "Bonus de la deuxième réponse",
	 * 				...
	 * 				D-1	=> "Bonus de la dernière réponse",
	 * 			),
	 * 			'malus_reponse'	=> array(
	 * 				0	=> "Malus de la première réponse",
	 * 				1	=> "Malus de la deuxième réponse",
	 * 				...
	 * 				D-1	=> "Malus de la dernière réponse",
	 * 			)
	 * 		);
	 * @endcode
	 */
	protected function extractListeReponses($nQuestion, $aReponses) {
		// Réaffectation des clés du tableau dans l'ordre
		$aReponses = array_values($aReponses);

		// Suppression des lignes de commentaire commençant par `//`
		while (count($aReponses[0]) > 1 && preg_match("@^//.*@", $aReponses[0])) {
			// Suppression de la première ligne
			unset($aReponses[0]);
			// Réaffectation des clés du tableau dans l'ordre
			$aReponses = array_values($aReponses);
		}

		// Initialisation du résultat
		$aListeReponses	= array();
		// Fonctionnalité réalisée en cas de réponses multiples
		if (count($aReponses) > 1) {
			// Parcours de l'ensemble des réponses
			foreach ($aReponses as $nReponse => $sString) {
				// Récupère le texte de la réponse à ajouter à la liste des propositions
				$sTexteReponse	= $this->extractReponse($sString);

				// Fonctionnalité réalisée si le texte de la réponse est valide
				if (!empty($sTexteReponse)) {
					$aListeReponses[$nReponse]['texte_reponse']			= $sTexteReponse;
					$aListeReponses[$nReponse]['bonus_reponse']			= $this->extractBonus($sString);
					$aListeReponses[$nReponse]['malus_reponse']			= $this->extractMalus($sString);
					$aListeReponses[$nReponse]['sanction_reponse']		= $this->extractSanction($sString);
				}
			}
		} else {
			// Fonctionnalité réalisée en cas de réponse dans la question VRAI / FAUX
			$fBonusTRUE			= 0;
			$fBonusFALSE		= 0;

			// Le format [@FALSE.*#(.*)#(.*)$@] ou [@TRUE.*#(.*)#(.*)$@
			if (preg_match("@\{F\}.*$@", $aReponses[0]) || preg_match("@\{FALSE\}.*$@", $aReponses[0]) || preg_match("@FALSE.*#(.*)#(.*)$@", $aReponses[0], $aMatches)) {
				$fBonusFALSE	= FormulaireManager::BONUS_MAX;
			} elseif (preg_match("@\{T\}.*$@", $aReponses[0]) || preg_match("@\{TRUE\}.*$@", $aReponses[0]) || preg_match("@TRUE.*#(.*)#(.*)$@", $aReponses[0], $aMatches)) {
				$fBonusTRUE		= FormulaireManager::BONUS_MAX;
			}

			// Fonctionnalité réalisée si la réponse est valide
			if ($fBonusTRUE || $fBonusFALSE) {
				// Réponse VRAI
				$aListeReponses[0]['texte_reponse']						= FormulaireManager::TEXTE_REPONSE_TRUE;
				$aListeReponses[0]['bonus_reponse']						= $fBonusTRUE;
				$aListeReponses[0]['malus_reponse']						= 0;
				$aListeReponses[0]['sanction_reponse']					= 0;
				// Réponse FAUX
				$aListeReponses[1]['texte_reponse']						= FormulaireManager::TEXTE_REPONSE_FALSE;
				$aListeReponses[1]['bonus_reponse']						= $fBonusFALSE;
				$aListeReponses[1]['malus_reponse']						= 0;
				$aListeReponses[1]['sanction_reponse']					= 0;
			} else {
				// Récupération de la correction
				$this->_aQCM['question_correction'][$nQuestion]			= $this->extractCorrection($aReponses[0]);
			}
		}

		// Renvoi du résultat
		return $aListeReponses;
	}

	/**
	 * @brief	Extraction du fichier en tableau.
	 *
	 * @li	Chaque bloc du fichier correspond à une question, délimité par une ou plusieurs ligne(s) vide(s) de séparation.
	 * @li	La première ligne du bloc correspond au titre de la question [name:], avec éventuellement son identifiant [question:].
	 * @li	L'ensemble des réponses est contenu entre les caractères [{] et [}].
	 * @code
	 * 	// question: 725	name: ﻿Interface graphique Linux
	 * 	::﻿Interface graphique Linux::[html]﻿L'interface graphique par défaut sous <em>Linux</em> se nomme \:{
	 * 		=Unity
	 * 		~KDE
	 * 		~XFCE
	 * 		~Gnome
	 * 	}
	 * @endcode
	 *
	 * @li	Si aucune réponse n'est détectée, il s'agit d'une question libre.
	 *
	 * @param	string	$sFileName		: Chemin du fichier sélectionné pour l'importation.
	 * @param	string	$sTitre			: Titre à partir du nom du fichier à importer.
	 * @param	boolean	$bStrict		: (optionnel) formulaire à réponses strictes par défaut (tout ou rien).
	 * @param	float	$pPenalite		: (optionnel) pénalité par défaut des questions à choix multiples, en pourcentage.
	 * @return	array, tableau au format attendu par le formulaire HTML
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
	 */
	public function importer($sFileName, $sTitre = FormulaireManager::TITRE_DEFAUT, $bStrict = FormulaireManager::QUESTION_STRICTE_IMPORT, $pPenalite = FormulaireManager::PENALITE_DEFAUT) {
		// Initialisation du TEMP
		$aTemp				= array();
		$nQuestion			= 0;
		$aListeQuestions	= array();

		// Initialisation du nombre maximal de réponses par question
		$nNombreMaxReponses	= FormulaireManager::NB_MAX_REPONSES_DEFAUT;

		// Ouverture du fichier
		$oFile = fopen($sFileName, 'r');

		// Parcours du fichier ligne par ligne
		while (!feof($oFile)) {
			// Récupération de la ligne sans les retours Windows
			$sLine = str_replace("\r\n", "\n", fgets($oFile));

			// Fonctionnalité réalisée lors du passage à une nouvelle question
			if (DataHelper::isValidArray($aTemp) && (strlen($sLine) <= 1)) {
				// Réponse stricte par défaut
				$aListeQuestions[$nQuestion]['stricte_question'] = FormulaireManager::QUESTION_STRICTE_IMPORT;

				// Suppression de la ligne de commentaire commençant par `//`
				while (count($aTemp) > 0 && (empty($aTemp[0]) || preg_match("@^//.*@", $aTemp[0]))) {
					// Suppression de la première ligne
					unset($aTemp[0]);
					// Réaffectation des valeurs
					$aTemp = array_values($aTemp);
				}

				// Récupération de la catégorie dans l'export Moodle avec le nom de la catégorie
				if (empty($nQuestion) && (bool) preg_match(self::CATEGORY_PATTERN, $this->extractEnonce($aTemp[0]), $aMatched)) {
					// Instance du référentiel de l'application
					$oReferenciel	= new ReferentielManager();

					// Récupération du libellé de la catégorie
					$sNomCategorie	= $aMatched[1];

					// Recherche de la catégorie par son libellé
					$aListeCategory = $oReferenciel->findCategoriesByLabel($sNomCategorie);

					// Fonctionnalité réalisée s'il n'y a qu'une catégorie trouvée dans le référentiel
					if (DataHelper::isValidArray($aListeCategory, 1)) {
						// Initialisation de l'identifiant du domaine
						$this->_nDomaine							= (int) $aListeCategory[0]['id_parent'];
						// Initialisation de l'identifiant de la catégorie
						$this->_nCategorie							= (int) $aListeCategory[0]['id_referentiel'];
					}

					// Suppression de la première ligne
					unset($aTemp[0]);
					// Réaffectation des valeurs
					$aTemp = array_values($aTemp);

					// Passage à la ligne suivante
					continue;
				}

				// Récupération du titre de la question
				$aListeQuestions[$nQuestion]['titre_question']		= $this->extractTitre($aTemp[0]);

				// Récupération de l'énoncé de la question
				$aListeQuestions[$nQuestion]['enonce_question']		= $this->extractEnonce($aTemp[0]);

				// Récupération de la correction de la question
				$aListeQuestions[$nQuestion]['correction_question']	= null;

				// Récupération de la première ligne avant de supprimer la valeur dans le tableau
				$sFirstLine = $aTemp[0];
				// Suppression des lignes tant que son sontenu ne correspond pas à un début de réponse avec le caractère [{]
				while (count($aTemp) > 1 && ((empty($sFirstLine) || preg_match("@^//.*@", $sFirstLine)) && !preg_match("@\{@", $sFirstLine) || !preg_match("@\{@", $aTemp[0]))) {
					// Suppression de la première ligne
					unset($aTemp[0]);
					// Réaffectation des clés du tableau dans l'ordre
					$aTemp = array_values($aTemp);
					// Récupération de la première ligne
					$sFirstLine = $aTemp[0];
				}
				// Suppression définitive de la première ligne
				unset($aTemp[0]);
				// Réaffectation des clés du tableau dans l'ordre
				$aTemp = array_values($aTemp);

				/**
				 * @brief	Extraction des réponses sur le format compressé
				 *
				 * ::Q1::Question ? { ~Wrong choice A# A comment =Correct choice B# B comment ~Wrong choice C# C comment ~Wrong choice D# D comment }
				 */
				if (preg_match("@\{(.*)\}(.*)$@", $sFirstLine, $aMatches) || preg_match("@\{(.*)$@", $sFirstLine, $aMatches)) {
					// Finalisation de la réponse du type `Il y a {~une =deux =trois} bonnes réponses` où la chaîne finale doit apparaître pour chaque réponse
					$sRecurrent	= isset($aMatches[2]) ? trim($aMatches[2]) : "";

					// Préparation de la chaîne de caractères
					$sContent	= strtr($aMatches[1], array(
						" ~"	=> "*|*~",
						" ="	=> "*|*=",
					));

					// Extraction des réponses
					$aReponses	= preg_split("@[\*\|\*]+@",	$sContent);

					// Parcours de chaque réponse
					foreach ($aReponses as $i => $sContent) {
						// Cas particulier des questions VRAI/FAUX
						if ($sContent == "T" || preg_match("@^T\s#@", $sContent) || preg_match("@^TRUE@", $sContent)) {
							$sContent = "{TRUE}";
						} elseif ($sContent == "F" || preg_match("@^F\s#@", $sContent) || preg_match("@^FALSE@", $sContent)) {
							$sContent = "{FALSE}";
						}

						// Fonctionnalité réalisée si le contenu est valide
						if (strlen($sContent) > 0) {
							// Ajout du contenu à la liste des réponses précédentes
							$aTemp[] = trim(strlen($sRecurrent) > 0 ? ($sContent . " -> " . trim($sRecurrent)) : $sContent);
						}
					}
				}

				// Récupération des réponses à la question
				$aListeQuestions[$nQuestion]['liste_reponses']		= $this->extractListeReponses($nQuestion, $aTemp);
				$nNombreReponses	= count($aListeQuestions[$nQuestion]['liste_reponses']);
				$bLibreQuestion		= false;
				if ($nNombreReponses > $nNombreMaxReponses) {
					// Mise à jour du nombre maximal de réponses
					$nNombreMaxReponses = $nNombreReponses;
				} elseif (empty($nNombreReponses)) {
					// Saisie de la réponse libre
					$bLibreQuestion = true;
				}

				// Saisie d'une question libre : pas de case à cocher
				$aListeQuestions[$nQuestion]['libre_question']		= $bLibreQuestion;

				// Fin du traitement de TEMP
				$aTemp = array();

				// Incrémentation de l'occurrence de la question
				$nQuestion++;
			} else {
				// Fonctionnalité réalisée si le contenu de la ligne est valide
				if (strlen($sLine) > 2) {
					// Ajout de la ligne au TEMP
					$aTemp[]	= $sLine;
				}
			}
		}

		// Fermeture du fichier
		fclose($oFile);

		// Fonctionnalité réalisée si aucune question n'a été trouvée
		if (! DataHelper::isValidArray($aListeQuestions)) {
			return false;
		}

		try {
			// Initialisation du formulaire
			$this->_aQCM['formulaire_id']										= null;

			// Chargement des données du formulaire
			$this->_aQCM['formulaire_titre']									= $sTitre;
			$this->_aQCM['formulaire_presentation']								= FormulaireManager::PRESENTATION_DEFAUT;
			$this->_aQCM['formulaire_domaine']									= null;
			$this->_aQCM['formulaire_sous_domaine']								= null;
			$this->_aQCM['formulaire_categorie']								= null;
			$this->_aQCM['formulaire_sous_categorie']							= null;
			$this->_aQCM['formulaire_note_finale']								= FormulaireManager::NOTE_FINALE_DEFAUT;
			$this->_aQCM['formulaire_penalite']									= $pPenalite;
			$this->_aQCM['formulaire_strict']									= $bStrict;

			// Initialisation du nombre total de questions dans le formulaire
			$this->_aQCM['formulaire_nb_total_questions']						= count($aListeQuestions);

			// Parcours de la liste des questions
			foreach ($aListeQuestions as $nQuestion => $aQuestion) {
				// Chargement des données de la question courante
				$this->_aQCM['question_id'][$nQuestion]							= null;
				$this->_aQCM['question_titre'][$nQuestion]						= $aQuestion['titre_question'];
				$this->_aQCM['question_enonce'][$nQuestion]						= $aQuestion['enonce_question'];
				$aListeReponses													= $aQuestion['liste_reponses'];

				// Attente d'une réponse stricte à la question
				$bStricte		= false;
				if ($aQuestion['stricte_question']) {
					$bStricte	= true;
				}
				$this->_aQCM['question_stricte'][$nQuestion]					= $aQuestion['stricte_question'];
				$this->_aQCM['question_stricte_checked'][$nQuestion]			= $bStricte;

				// Question libre (pas de case à cocher)
				$bLibre			= false;
				if ($aQuestion['libre_question']) {
					$bLibre		= true;
				}
				$this->_aQCM['question_lignes'][$nQuestion]						= FormulaireManager::QUESTION_LIBRE_LIGNES_DEFAUT;
				$this->_aQCM['question_libre'][$nQuestion]						= $aQuestion['libre_question'];
				$this->_aQCM['question_libre_checked'][$nQuestion]				= $bLibre;

				// Parcours de la liste des réponses
				$fBareme			= FormulaireManager::QUESTION_BAREME_DEFAUT;
				$fPenaliteQuestion	= $pPenalite;
				$bLibreQuestion		= true;
				foreach ($aListeReponses as $nReponse => $aReponse) {
					// Récupération du contenu de la réponse
					$sTexte		= $aReponse['texte_reponse'];
					$fBonus		= $aReponse['bonus_reponse'];
					$fMalus		= $aReponse['malus_reponse'];
					$fPenalite	= $aReponse['sanction_reponse'];

					// Fonctionnalité réalisée si au moins une réponse est détectée
					if (!empty($sTexte)) {
						$bLibreQuestion = false;
					}

					// Vérification de la validité de la réponse
					$bValide	= 0;
					if ($fBonus > 0) {
						$bValide = 1;
					}

					// Vérification de la sanction de la mauvaise réponse
					$bSanction	= 0;
					if ($fPenalite > 0) {
						$bSanction = 1;
					}

					// Vérification de la pénalité maximale de la question
					if ($fMalus > $fPenaliteQuestion) {
						$fPenaliteQuestion = $fMalus;
					}

					// Initialisation des champs de la réponse par défaut
					$this->_aQCM['reponse_id'][$nQuestion][$nReponse]			= null;
					$this->_aQCM['reponse_texte'][$nQuestion][$nReponse]		= $sTexte;
					$this->_aQCM['reponse_valide'][$nQuestion][$nReponse]		= $bValide;
					$this->_aQCM['reponse_valeur'][$nQuestion][$nReponse]		= $fBonus;
					$this->_aQCM['reponse_sanction'][$nQuestion][$nReponse]		= $bSanction;
					$this->_aQCM['reponse_penalite'][$nQuestion][$nReponse]		= $fPenalite;
				}

				// Mise à jour
				$this->_aQCM['question_bareme'][$nQuestion]						= $fBareme;
				$this->_aQCM['question_penalite'][$nQuestion]					= $fPenaliteQuestion;
				$this->_aQCM['question_libre'][$nQuestion]						= $bLibreQuestion;
			}

			// Initialisation du nombre maximal de réponses par question
			$this->_aQCM['formulaire_nb_max_reponses']							= $nNombreMaxReponses;

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aQCM;
	}

	/**
	 * @brief	Récupération de l'identifiant du domaine du questionnaire.
	 */
	public function getDomaine() {
		return $this->_nDomaine;
	}

	/**
	 * @brief	Récupération de l'identifiant de la catégorie du questionnaire.
	 */
	public function getCategorie() {
		return $this->_nCategorie;
	}
}
