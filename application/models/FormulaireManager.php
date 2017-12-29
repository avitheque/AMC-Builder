<?php
/**
 * @brief	Classe de gestion des formulaires QCM.
 *
 * L'ensemble du formulaire est parcouru afin de générer un tableau associatif entre
 * les champs du formulaire et ceux de la base de données.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * Étend la classe d'accès à la base de données MySQLManager.
 * @see			{ROOT_PATH}/libraries/models/MySQLManager.php
 *
 * @name		FormulaireManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 94 $
 * @since		$LastChangedDate: 2017-12-29 17:27:29 +0100 (Fri, 29 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class FormulaireManager extends MySQLManager {

	/**
	 * @brief	Constantes d'accès aux formulaires QCM.
	 *
	 * @li	Seuls les membres du groupe supérieurs au rédacteur peuvent avoir accès aux formulaires par héritage.
	 *
	 * @var		boolean
	 */
	const ACCESS_GROUP_BY_DEFAULT			= true;			// TRUE : restriction hiérarchique ; FALSE : accès libre

	/**
	 * @brief	Constantes des formulaires QCM.
	 * @var		string
	 */
	const TAB_DEFAULT						= 0;

	const TITRE_DEFAUT						= "Projet QCM";
	const FORMULAIRE_NOM_MAXLENGTH			= 255;
	const QUESTION_TITRE_MAXLENGTH			= 255;

	const EPREUVE_TYPE_DEFAUT				= "Contrôle";
	const EPREUVE_DATE_FORMAT				= "d/m/Y";
	const EPREUVE_DUREE_DEFAUT				= 50;
	const EPREUVE_HEURE_DEFAUT				= "09:00";
	const EPREUVE_HEURE_MAXLENGTH			= 5;

	const GENERATION_NOM_MAXLENGTH			= 50;
	const GENERATION_NOM_DEFAUT				= "Nom du stage";
	const GENERATION_LANGUE_DEFAUT			= "francais";
	const GENERATION_FORMAT_DEFAUT			= "a4paper";
	const GENERATION_SEPARATE_DEFAUT		= false;
	const GENERATION_CONSIGNES_DEFAUT		= "Veuillez remplir complètement chaque case au stylo à encre noir ou bleu-noir afin de reporter vos choix de réponse. Les encres de couleur claires, fluorescentes ou effaçables sont interdites.<br />Pour toute correction, veuillez utiliser du blanc correcteur exclusivement.<br />DANS CE DERNIER CAS, NE REDESSINEZ PAS LA CASE !";
	const GENERATION_EXEMPLAIRES_DEFAUT		= 20;
	const CANDIDATS_CODE_DEFAUT				= 4;				// Nombre de caractères formant le code candidat par défaut
	const CANDIDATS_CODE_MAXLENGTH			= 8;				// Nombre de caractères formant le code candidat au maximum
	const CANDIDATS_LABEL_DEFAULT			= "Code candidat :";
	const CANDIDATS_CARTOUCHE_DEFAUT		= "Codez votre code candidat à l'aide des cases ci-contre en reportant chaque numéro de gauche à droite";
	const CANDIDATS_CARTOUCHE_MAXLENGTH		= 255;

	const NOTE_FINALE_DEFAUT				= 20;				// Valeur maximale de la note du questionnaire QCM
	const QUESTION_STRICTE_DEFAUT			= false;			// Attente d'une réponse stricte par défaut lors de la création
	const QUESTION_STRICTE_IMPORT			= false;			// Attente d'une réponse stricte lors de l'import
	const QUESTION_LIBRE_LIGNES_DEFAUT		= 15;				// Nombre de lignes par défaut pour les réponses libres
	const QUESTION_LIBRE_LIGNES_MAX			= 3;
	const QUESTION_BAREME_DEFAUT			= 1;				// Valeur de la question
	const QUESTION_BAREME_MAXLENGTH			= 3;				// Longueur du barème de la question en nombre de caractères (3 pour 1,5)
	const QUESTION_PENALITE_MAXLENGTH		= 5;				// Longueur du pourcentage de la pénalité à la question en nombre de caractères (5 pour 33,33)
	const PENALITE_DEFAUT					= 0;				// Pénalité en pourcentage de la note
	const BONUS_MAX							= 100;				// Valeur maximum d'une bonne réponse (en pourcentage)
	const NB_TOTAL_QUESTIONS_DEFAUT			= 0;
	const NB_MAX_REPONSES_DEFAUT			= 4;
	const PRESENTATION_DEFAUT				= "Veuillez répondre aux questions ci-dessous du mieux que vous le pouvez.";
	const TEXTE_REPONSE_TRUE				= "Vrai";
	const TEXTE_REPONSE_FALSE				= "Faux";

	/**
	 * Valeur des états de la validation
	 * @var		integer
	 */
	const VALIDATION_DEFAUT					= 0;				// Non terminé
	const VALIDATION_ATTENTE				= 1;				// En attente de validation
	const VALIDATION_REALISEE				= 2;				// Validé

	/**
	 * Format des libellés pour la requête SQL
	 * @var		string
	 */
	const LIBELLE_CANDIDAT					= "CONCAT(grade.libelle_court_grade, \" \", candidat.nom_candidat, \" \", candidat.prenom_candidat)";
	const LIBELLE_REDACTEUR					= "CONCAT(redacteur.nom_utilisateur, \" \", redacteur.prenom_utilisateur)";
	const LIBELLE_VALIDEUR					= "CONCAT(valideur.nom_utilisateur, \" \", valideur.prenom_utilisateur)";
	const LIBELLE_STAGE_SPRINTF				= "CONCAT(stage.libelle_stage, \"<br />(\", DATE_FORMAT(stage.date_debut_stage, '%s'), \" - \", DATE_FORMAT(stage.date_fin_stage, '%s'), \")\")";

	/**
	 * Format des DATETIME pour la requête SQL
	 * @var		string
	 */
	const DATETIME_EPREUVE					= "DATE_FORMAT(CONCAT(date_epreuve, ' ', heure_epreuve), '%Y-%m-%d %H:%i:%s')";

	/**
	 * @brief	Liste de correspondance des noms de champ du formulaire et ceux en base de données.
	 *
	 * @var		array	: au format array('nom_du_champ' => `table.field`)
	 */
	static protected $LIST_CHAMPS_FORM_DB	= array(
		// FORMAT ************************************************************ (ordre alphabétique)
		'generation_cartouche_candidat'		=> "generation.cartouche_candidat_generation",
		'generation_code_candidat'			=> "generation.code_candidat_generation",
		'generation_consignes'				=> "generation.consignes_generation",
		'generation_date_epreuve'			=> "generation.date_epreuve_generation",
		'generation_exemplaires'			=> "generation.exemplaires_generation",
		'generation_format'					=> "generation.format_generation",
		'generation_id'						=> "generation.id_generation",
		'generation_langue'					=> "generation.langue_generation",
		'generation_nom_epreuve'			=> "generation.nom_epreuve_generation",
		'generation_seed'					=> "generation.seed_generation",
		'generation_separate'				=> "generation.separate_generation",

		// ÉPREUVE *********************************************************** (ordre alphabétique)
		'epreuve_date'						=> "epreuve.date_epreuve",
		'epreuve_duree'						=> "epreuve.duree_epreuve",
		'epreuve_heure'						=> "epreuve.heure_epreuve",
		'epreuve_id'						=> "epreuve.id_epreuve",
		'epreuve_libelle'					=> "epreuve.libelle_epreuve",
		'epreuve_stage'						=> "epreuve.id_stage",

		// GÉNÉRALITÉS ******************************************************* (ordre alphabétique)
		'formulaire_validation'				=> "formulaire.validation_formulaire",
		'formulaire_categorie'				=> "formulaire.id_categorie",
		'formulaire_domaine'				=> "formulaire.id_domaine",
		'formulaire_id'						=> "formulaire.id_formulaire",
		'formulaire_nb_max_reponses'		=> null,								// Champ calculé
		'formulaire_nb_total_questions'		=> null,								// Champ calculé
		'formulaire_note_finale'			=> "formulaire.note_finale_formulaire",
		'formulaire_penalite'				=> "formulaire.penalite_formulaire",
		'formulaire_presentation'			=> "formulaire.presentation_formulaire",
		'formulaire_sous_categorie'			=> "formulaire.id_sous_categorie",
		'formulaire_sous_domaine'			=> "formulaire.id_sous_domaine",
		'formulaire_strict'					=> "formulaire.strict_formulaire",
		'formulaire_titre'					=> "formulaire.titre_formulaire",

		// QUESTIONNAIRE ***************************************************** (ordre alphabétique)
		'question_bareme'					=> "question.bareme_question",
		'question_correction'				=> "question.correction_question",
		'question_enonce'					=> "question.enonce_question",
		'question_id'						=> "question.id_question",
		'question_libre'					=> "question.libre_question",
		'question_lignes'					=> "question.lignes_question",
		'question_penalite'					=> "question.penalite_question",
		'question_stricte'					=> "question.stricte_question",
		'question_titre'					=> "question.titre_question",

		// RÉPONSES ********************************************************** (ordre alphabétique)
		'reponse_id'						=> "reponse.id_reponse",
		'reponse_texte'						=> "reponse.texte_reponse",
		'reponse_penalite'					=> "reponse.penalite_reponse",
		'reponse_sanction'					=> "reponse.sanction_reponse",
		'reponse_valeur'					=> "reponse.valeur_reponse",
		'reponse_valide'					=> "reponse.valide_reponse",

		// CONTROLE ********************************************************** (ordre alphabétique)
		'controle_id'						=> "controle.id_controle",
		'controle_date_debut'				=> "controle.date_debut_controle",
		'controle_candidat_libre_reponse'	=> "controle_reponse_candidat.libre_reponse_candidat",
		'controle_candidat_liste_reponses'	=> "controle_reponse_candidat.liste_reponses_candidat"
	);

	/**
	 * @brief	Tableau de champs du formulaire HTML.
	 * @var		array
	 */
	protected $_aQCM						= array();

	/******************************************************************************************************
	 * @todo RECHERCHES
	 ******************************************************************************************************/


	/**
	 * @brief	Recherche si un contrôle est en cours selon l'identifiant de l'épreuve.
	 *
	 * @li	Un contrôle est enregistré dès qu'un candidat entamme une épreuve.
	 *
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	boolean
	 */
	public function isControleExistsByIdEpreuve($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *",
			1	=> "FROM controle",
			2	=> "WHERE id_epreuve = :id_epreuve",
			4	=> "ORDER BY id_controle DESC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_epreuve'					=> $nIdEpreuve
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return DataHelper::isValidArray($aResultat);
	}

	/**
	 * @brief	Recherche de toutes les programmations.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @remark	Une épreuve n'est pas liée à un formulaire (v2.17.12.26).
	 *
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllProgrammations($bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> self::DATETIME_EPREUVE . " AS datetime_epreuve,",
			4	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") AS debut_epreuve,",
			5	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") + 60 * duree_epreuve AS fin_epreuve,",
			6	=> "UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant,",
			7	=> sprintf(self::LIBELLE_STAGE_SPRINTF, $this->_dateFormat, $this->_dateFormat) . " AS libelle_stage_complet,",
			8	=> "COUNT(id_stage_candidat)",
			9	=> "FROM epreuve",
			10	=> "INNER JOIN generation USING(id_generation)",
			11	=> "LEFT  JOIN formulaire USING(id_formulaire)",
			12	=> "LEFT  JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = formulaire.id_redacteur)",
			13	=> "LEFT  JOIN utilisateur AS valideur ON(valideur.id_utilisateur = generation.id_valideur)",
			14	=> "LEFT  JOIN groupe ON(valideur.id_groupe = groupe.id_groupe)",
			15	=> "INNER JOIN stage USING(id_stage)",
			16	=> "LEFT  JOIN stage_candidat USING(id_stage)",
			17	=> "LEFT  JOIN candidat USING(id_candidat)",
			'X'	=> null,
			18	=> "GROUP BY id_epreuve"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array();

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "WHERE borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de toutes les épreuves générées.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @remark	Une épreuve n'est pas liée à un formulaire (v2.17.12.26).
	 *
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllEpreuves($bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> self::DATETIME_EPREUVE . " AS datetime_epreuve,",
			4	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") AS debut_epreuve,",
			5	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") + 60 * duree_epreuve AS fin_epreuve,",
			6	=> "UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant,",
			7	=> sprintf(self::LIBELLE_STAGE_SPRINTF, $this->_dateFormat, $this->_dateFormat) . " AS libelle_stage_complet,",
			8	=> "COUNT(id_stage_candidat)",
			9	=> "FROM epreuve",
			10	=> "INNER JOIN generation USING(id_generation)",
			11	=> "LEFT  JOIN formulaire USING(id_formulaire)",
			12	=> "LEFT  JOIN domaine USING(id_domaine)",
			13	=> "LEFT  JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = formulaire.id_redacteur)",
			14	=> "LEFT  JOIN utilisateur AS valideur ON(valideur.id_utilisateur = generation.id_valideur)",
			15	=> "LEFT  JOIN groupe ON(valideur.id_groupe = groupe.id_groupe)",
			16	=> "INNER JOIN stage USING(id_stage)",
			17	=> "LEFT  JOIN stage_candidat USING(id_stage)",
			18	=> "LEFT  JOIN candidat USING(id_candidat)",
			'X'	=> null,
			19	=> "GROUP BY id_epreuve"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array();

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "WHERE borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de toutes les épreuves accessibles par un candidat.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @param	integer	$nIdCandidat		: identifiant du candidat.
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllEpreuvesByIdCandidat($nIdCandidat, $bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdCandidat, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> self::DATETIME_EPREUVE . " AS datetime_epreuve,",
			4	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") AS debut_epreuve,",
			5	=> "UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") + 60 * duree_epreuve AS fin_epreuve,",
			6	=> "UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant",
			7	=> "FROM formulaire",
			8	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			9	=> "INNER JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			10	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			11	=> "INNER JOIN generation USING(id_formulaire)",
			12	=> "INNER JOIN epreuve USING(id_generation)",
			13	=> "INNER JOIN stage USING(id_stage)",
			14	=> "INNER JOIN stage_candidat USING(id_stage)",
			15	=> "INNER JOIN candidat USING(id_candidat)",
			16	=> "WHERE candidat.id_candidat = :id_candidat",
			'X'	=> null,
			17	=> "GROUP BY id_epreuve"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_candidat'	=> $nIdCandidat
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche des capacités d'accueil selon l'identifiant d'une épreuve.
	 *
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	integer, résultat de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getCapacitesByEpreuveId($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Requête SELECT
		$aQuery = array(
			"SELECT SUM(capacite_statut_salle)",
			"FROM epreuve, salle",
			"INNER JOIN statut_salle USING(id_salle)",
			"WHERE FIND_IN_SET(salle.id_salle, epreuve.liste_salles_epreuve) != 0",
			"AND id_epreuve = :id_epreuve",
			"GROUP BY id_epreuve"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_epreuve'					=> $nIdEpreuve
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la capacité
		return $aResultat['SUM(capacite_statut_salle)'];
	}

	/**
	 * @brief	Recherche d'une épreuve par son identifiant.
	 *
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getEpreuveById($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Requête SELECT
		$aQuery	= array(
			"SELECT *,",
			self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			sprintf(self::LIBELLE_STAGE_SPRINTF, $this->_dateFormat, $this->_dateFormat) . " AS libelle_stage_complet,",
			"COUNT(id_stage_candidat)",
			"FROM formulaire",
			"INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			"INNER JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			"INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			"INNER JOIN generation USING(id_formulaire)",
			"INNER JOIN epreuve USING(id_generation)",
			"INNER JOIN stage USING(id_stage)",
			"LEFT  JOIN stage_candidat USING(id_stage)",
			"LEFT  JOIN candidat USING(id_candidat)",
			"WHERE id_epreuve = :id_epreuve",
			"GROUP BY id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_epreuve'					=> $nIdEpreuve
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Récupère d'identifiant du formulaire à partir de l'identifiant d'une épreuve.
	 *
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	integer, résultat de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getIdFormulaireFromIdEpreuve($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Récupération de l'épreuve par son identifiant
		$aEpreuve = $this->getEpreuveById($nIdEpreuve);

		// Renvoi de l'identifiant du formulaire si l'épreuve est valide
		return DataHelper::get($aEpreuve, 'id_formulaire', DataHelper::DATA_TYPE_INT, null);
	}

	/**
	 * @brief	Recherche d'une salle d'épreuve par son identifiant.
	 *
	 * @param	integer	$nIdSalle			: identifiant de la salle.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getSalleById($nIdSalle) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdSalle);

		// Requête SELECT
		$aQuery	= array(
			"SELECT * FROM salle",
			"INNER JOIN statut_salle USING(id_salle)",
			"WHERE id_salle = :id_salle",
			"GROUP BY id_salle"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_salle'						=> $nIdSalle
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de tous candidats d'une épreuve par son identifiant.
	 *
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllCandidatsByEpreuveId($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Requête SELECT
		$aQuery	= array(
			"SELECT *,",
			self::LIBELLE_CANDIDAT . " AS libelle_candidat",
			"FROM epreuve",
			"INNER JOIN stage USING(id_stage)",
			"INNER JOIN stage_candidat USING(id_stage)",
			"INNER JOIN candidat USING(id_candidat)",
			"INNER JOIN grade USING(id_grade)",
			"WHERE id_epreuve = :id_epreuve",
			"GROUP BY id_candidat",
			"ORDER BY nom_candidat ASC, prenom_candidat ASC, ordre_grade DESC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_epreuve'					=> $nIdEpreuve
		);

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	//*********************************************************************************************

	/**
	 * @brief	Recherche de tous les formulaires enregistrés.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllFormulaires($bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> "COUNT(id_question) AS total_questions",
			4	=> "FROM formulaire",
			5	=> "LEFT  JOIN domaine USING(id_domaine)",
			6	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			7	=> "LEFT  JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			8	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			9	=> "LEFT  JOIN formulaire_question USING(id_formulaire)",
			10	=> "WHERE id_formulaire <> :id_formulaire_system",
			'X'	=> null,
			11	=> "GROUP BY id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_formulaire_system'			=> 0
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de tous les formulaires en attente de validation.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllFormulairesForValidation($bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> "COUNT(id_question) AS total_questions",
			4	=> "FROM formulaire",
			5	=> "LEFT  JOIN domaine USING(id_domaine)",
			6	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			7	=> "LEFT  JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			8	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			9	=> "LEFT  JOIN formulaire_question USING(id_formulaire)",
			10	=> "WHERE validation_formulaire >= :validation_formulaire",
			'X'	=> null,
			11	=> "GROUP BY id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':validation_formulaire'		=> FormulaireManager::VALIDATION_ATTENTE
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de tous les formulaires en attente de génération.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 * @li	L'entrée $aQuery['X'] correspond à une zone d'injection pour la clause WHERE de recherche intervallaire.
	 *
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllFormulairesForGeneration($bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $bGroupAccess);

		// Requête SELECT
		$aQuery	= array(
			0	=> "SELECT *,",
			1	=> self::LIBELLE_REDACTEUR . " AS libelle_redacteur,",
			2	=> self::LIBELLE_VALIDEUR . " AS libelle_valideur,",
			3	=> "COUNT(id_question) AS total_questions",
			4	=> "FROM formulaire",
			5	=> "LEFT  JOIN domaine USING(id_domaine)",
			6	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			7	=> "INNER JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			8	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			9	=> "LEFT  JOIN formulaire_question USING(id_formulaire)",
			10	=> "WHERE validation_formulaire = :validation_formulaire",
			'X'	=> null,
			11	=> "GROUP BY id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':validation_formulaire'		=> FormulaireManager::VALIDATION_REALISEE
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/**
	 * @brief	Recherche de toutes les questions selon les critères.
	 *
	 * @li	Les critères sont appliqués sur les formulaires permettant de récupérer l'ensemble des questions y référant.
	 * @li	Les questions sont triées par le titre dans ordre alphabétique.
	 *
	 * @li	Si aucun critère n'est demandé, les jointures LEFT seront appliquées, sinon INNER.
	 *
	 * @li	Possibilité de limiter les questions selon le groupe d'appartenance de l'utilisateur connecté.
	 * 		- Cas A :	La recherche porte sur des critères de recherche,
	 * 					=> si le filtre $bGroupAccess est actif
	 * 						alors la jointure sera faite entre le groupe et le rédacteur du formulaire
	 * 		- Cas B :	La recherche porte sur les questions orphelines,
	 * 					=> si le filtre $bGroupAccess est actif
	 * 						alors la jointure sera faite entre le groupe et le rédacteur de la question.
	 *
	 * @param	array	$aCriteres			: tableau de critères à rechercher.
	 * @param	string	$aListeExcludeId	: tableau des identifiants à exclure.
	 * @param	boolean	$bOrphelin			: (optionnel) recherche des questions non associées à un formulaire.
	 * @param	boolean	$bGroupAccess		: (optionnel) filtre sur les groupes du rédacteur.
	 *
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aQCM = array(
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
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllQuestionsByCriteres($aCriteres = array(), $aListeExcludeId = array(), $bOrphelin = false, $bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $aCriteres, $aListeExcludeId, $bOrphelin, $bGroupAccess);

		// Initialisation de la liste
		$aResultat						= array();

		// Initialisation des paramètres la requête
		$sSelectFrom					= "SELECT question.id_question FROM question";
		$sFormatJoinQuestion			= "%s JOIN formulaire_question USING(id_question)";
		$sFormatJoinFormulaire			= "%s JOIN formulaire USING(id_formulaire)";
		$sJoinRedacteurFormulaire		= null;
		$sJoinRedacteurQuestion			= null;
		$sJoinGroupe					= null;

		// Initialisation de la clause WHERE
		$aWhere							= array();

		// Initialisation du tableau associatif des étiquettes et leurs valeurs
		$aBind							= array();
		foreach ($aCriteres as $sChamp => $sValue) {
			// Fonctionnalité réalisée si la valeur n'est pas NULL
			if (!empty($sValue) || $bOrphelin) {
				// Initialisation du format
				$sEtiquette				= ":" . $sChamp;

				// Ajout du critère à la collection de la clause WHERE
				$aWhere[]				= sprintf("formulaire.%s = %s", $sChamp, $sEtiquette);

				// Ajout à la collection des étiquettes
				$aBind[$sEtiquette]		= $sValue;
			}
		}

		// Construction de la recherche des questions selon les critères
		if ($bOrphelin) {
			// Initialisation de la requête LEFT
			$aQuery = array(
				$sSelectFrom,
				sprintf($sFormatJoinQuestion,	"LEFT")
			);

			// ATTENTION : Réinitialisation de la clause WHERE : les critères ne sont plus pris en compte
			$aBind						= array();
			$aWhere						= array(
				"formulaire_question.id_formulaire IS NULL",
			);
		} elseif (DataHelper::isValidArray($aWhere)) {
			// Initialisation de la requête INNER
			$aQuery = array(
				$sSelectFrom,
				sprintf($sFormatJoinQuestion,	"INNER"),
				sprintf($sFormatJoinFormulaire,	"INNER")
			);
		} else {
			// Initialisation de la requête LEFT
			$aQuery = array(
				$sSelectFrom,
				sprintf($sFormatJoinQuestion,	"LEFT"),
				sprintf($sFormatJoinFormulaire,	"LEFT")
			);
		}

		// Ajout de la liste des identifiant à exclure du résultat
		if (DataHelper::isValidArray($aListeExcludeId)) {
			$aWhere[]					= sprintf("question.id_question NOT IN(%s)", implode(",", $aListeExcludeId));
		}

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Jointure sur la table `formulaire` ou la table `question`
			$sJoinTableName				= $bOrphelin ? "question" : "formulaire";

			// Jointure avec la table `utilisateur`
			$aQuery[]					= "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = $sJoinTableName.id_redacteur)";

			// Jointure avec la table `groupe`
			$aQuery[]					= "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)";

			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aWhere[]					= "borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']		= $this->_borneGauche;
			$aBind[':borne_droite']		= $this->_borneDroite;
		}

		// Ajout de la clause WHERE
		$aQuery[]						= "WHERE " . implode(" AND ", $aWhere);

		// Ajout de la clause GROUP BY
		$aQuery[]						= "GROUP BY question.id_question";

		// Ajout du tri sur le titre
		$aQuery[]						= "ORDER BY question.titre_question ASC";

		// Recherche des questions
		try {
			// Exécution de la requête
			$aRequest					= $this->executeSQL($aQuery, $aBind);

			// Récupération de la liste des identifiants de questions
			$aListeIdQuestions			= DataHelper::requestToList($aRequest, "id_question");

			// Parcours de la liste des questions
			foreach ($aListeIdQuestions as $nIdQuestion) {
				// Ajout de la question à la liste des résultats
				$aResultat = array_merge_recursive($aResultat, $this->getQuestionReponsesByIdQuestion($nIdQuestion));
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat;
	}

	/******************************************************************************************************/

	/**
	 * @brief	Récupère un formulaire QCM enregistré par son identifiant.
	 *
	 * @param	integer	$nIdFormulaire		: identifiant du formulaire QCM.
	 * @return	array, tableau contenant l'ensemble des données du formulaire QCM.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getFormulaireById($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Requête SELECT
		$sQuery	= "SELECT *, (
							SELECT libelle_stage FROM stage
							WHERE stage.id_stage = epreuve.id_stage
						) AS libelle_stage
					FROM formulaire
					LEFT JOIN generation USING(id_formulaire)
					LEFT JOIN epreuve USING(id_generation)
					WHERE id_formulaire = :id_formulaire
					ORDER BY id_epreuve DESC";

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_formulaire'				=> $nIdFormulaire
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($sQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($sQuery, $aBind));
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Recherche toutes les questions associées à un formulaire QCM enregistré par son identifiant.
	 *
	 * @param	integer	$nIdFormulaire		: identifiant du formulaire QCM.
	 * @param	array	$aFiltre			: (optionnel) liste des colonnes à filtrer.
	 * @return	array, tableau contenant l'ensemble des questions du formulaire QCM passé en paramètre.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findQuestionsByIdFormulaire($nIdFormulaire, $aFiltre = array()) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $aFiltre);

		// Requête SELECT
		$aQuery	= array(
			"SELECT * FROM question",
			"JOIN formulaire_question USING(id_question)",
			"WHERE id_formulaire = :id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_formulaire'				=> $nIdFormulaire
		);

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind);

			// Fonctionnalité réalisée si le filtre est renseigné
			if (!empty($aFiltre)) {
				// Extraction de(s) colonne(s) présente(s) dans le filtre
				$aResultat = DataHelper::extractArrayFromRequestByLabel($aResultat, (array) $aFiltre);
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Recherche toutes les réponses associées à une question enregistrée par son identifiant.
	 *
	 * @param	integer	$nIdQuestion		: identifiant de la question.
	 * @param	array	$aFiltre			: (optionnel) liste des colonnes à filtrer.
	 * @return	array, tableau contenant l'ensemble des réponses à la question passée en paramètre.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findReponsesByIdQuestion($nIdQuestion, $aFiltre = array()) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $aFiltre);

		// Requête SELECT
		$aQuery	= array(
			"SELECT * FROM reponse",
			"JOIN question_reponse USING(id_reponse)",
			"WHERE id_question = :id_question"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_question'					=> $nIdQuestion
		);

		try {
			// Exécution de la requête sous forme de tableau
			$aResultat = $this->executeSQL($aQuery, $aBind);

			// Fonctionnalité réalisée si le filtre est renseigné
			if (!empty($aFiltre)) {
				// Extraction de(s) colonne(s) présente(s) dans le filtre
				$aResultat = DataHelper::extractArrayFromRequestByLabel($aResultat, (array) $aFiltre);
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Recherche une question par son identifiant.
	 *
	 * @param	integer	$nIdQuestion		: identifiant de la question.
	 * @return	array, tableau contenant l'ensemble des données à la question passée en paramètre.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getQuestionById($nIdQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion);

		// Requête SELECT
		$aQuery	= array(
			"SELECT * FROM question",
			"WHERE id_question = :id_question"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_question'					=> $nIdQuestion
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Recherche la question et ses réponses associées par son identifiant.
	 *
	 * @li	Méthode exploité par la recherche.
	 *
	 * @param	integer	$nIdQuestion		: identifiant de la question.
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aQCM = array(
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
	 * @param	integer	$nQuestion			: occurence de la question, 0 par défaut.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getQuestionReponsesByIdQuestion($nIdQuestion, $nQuestion = 0) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $nQuestion);

		// Initialisation du résultat
		$aResultat = array();

		// Récupération de la question par son identifiant
		$aQuestion = $this->getQuestionById($nIdQuestion);

		if (DataHelper::isValidArray($aQuestion)) {
			// Initialisation du nombre maximal de réponses par question
			$nNombreMaxReponses	= FormulaireManager::NB_MAX_REPONSES_DEFAUT;

			// Chargement des données de la question courante
			$aResultat['question_id'][$nQuestion]								= $aQuestion['id_question'];
			$aResultat['question_titre'][$nQuestion]							= $aQuestion['titre_question'];
			$aResultat['question_stricte'][$nQuestion]							= $aQuestion['stricte_question'];
			$aResultat['question_libre'][$nQuestion]							= $aQuestion['libre_question'];
			$aResultat['question_lignes'][$nQuestion]							= $aQuestion['lignes_question'];

			// Question stricte
			$bStricte		= false;
			if ($aQuestion['stricte_question']) {
				$bStricte	= true;
			}
			$aResultat['question_stricte_checked'][$nQuestion]					= $bStricte;

			// Question libre
			$bLibre			= false;
			if ($aQuestion['libre_question']) {
				$bLibre		= true;
			}
			$aResultat['question_libre_checked'][$nQuestion]					= $bLibre;

			$aResultat['question_enonce'][$nQuestion]							= DataHelper::get($aQuestion, 'enonce_question',	DataHelper::DATA_TYPE_TXT);
			$aResultat['question_correction'][$nQuestion]						= DataHelper::get($aQuestion, 'correction_question',DataHelper::DATA_TYPE_TXT);
			$aResultat['question_bareme'][$nQuestion]							= DataHelper::get($aQuestion, 'bareme_question',	DataHelper::DATA_TYPE_MYFLT_ABS);
			$aResultat['question_penalite'][$nQuestion]							= DataHelper::get($aQuestion, 'penalite_question',	DataHelper::DATA_TYPE_INT_ABS);

			// Récupération de la liste des réponses associées à la question courante
			$aListeReponses	= $this->findReponsesByIdQuestion($aQuestion['id_question']);
			if (count($aListeReponses) > $nNombreMaxReponses) {
				// Mise à jour du nombre maximal de réponses
				$nNombreMaxReponses = count($aListeReponses);
			}

			// Déclaration des éléments de réponse à la question
			$aResultat['reponse_id'][$nQuestion]								= array();
			$aResultat['reponse_texte'][$nQuestion]								= array();
			$aResultat['reponse_valide'][$nQuestion]							= array();
			$aResultat['reponse_valeur'][$nQuestion]							= array();
			$aResultat['reponse_sanction'][$nQuestion]							= array();
			$aResultat['reponse_penalite'][$nQuestion]							= array();

			// Parcours de la liste des réponses
			foreach ($aListeReponses as $nReponse => $aReponse) {
				// Chargement des éléments de réponse
				$aResultat['reponse_id'][$nQuestion][$nReponse]					= DataHelper::get($aReponse, 'id_reponse',			DataHelper::DATA_TYPE_INT);
				$aResultat['reponse_texte'][$nQuestion][$nReponse]				= DataHelper::get($aReponse, 'texte_reponse',		DataHelper::DATA_TYPE_TXT);
				$aResultat['reponse_valide'][$nQuestion][$nReponse]				= DataHelper::get($aReponse, 'valide_reponse',		DataHelper::DATA_TYPE_BOOL);
				$aResultat['reponse_valeur'][$nQuestion][$nReponse]				= 0;
				$aResultat['reponse_sanction'][$nQuestion][$nReponse]			= DataHelper::get($aReponse, 'sanction_reponse',	DataHelper::DATA_TYPE_BOOL);
				$aResultat['reponse_penalite'][$nQuestion][$nReponse]			= 0;

				// Fonctionnalité réalisée si la réponse est valide
				if ($aReponse['valide_reponse']) {
					$aResultat['reponse_valeur'][$nQuestion][$nReponse]			= DataHelper::get($aReponse, 'valeur_reponse',		DataHelper::DATA_TYPE_MYFLT_ABS);
				}

				// Fonctionnalité réalisée si la réponse est pénalisée
				if ($aReponse['sanction_reponse']) {
					$aResultat['reponse_penalite'][$nQuestion][$nReponse]		= DataHelper::get($aReponse, 'penalite_reponse',	DataHelper::DATA_TYPE_MYFLT_ABS);
				}
			}
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `epreuve`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `epreuve`.
	 *
	 * @param	integer	$nIdEpreuve			: Identifiant de l'épreuve en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logEpreuve($nIdEpreuve, $aQuery, $bFinalCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_epreuve = :id_epreuve,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_epreuve'					=> $nIdEpreuve,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_epreuve', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Enregistrement de l'épreuve.
	 *
	 * Méthode exploitant une requête préparées pour l'enregistrement dans la table `epreuve`.
	 *
	 * @li	L'enregistrement de la génération du formulaire doit être réalisée en amont.
	 *
	 * @return	integer, identifiant de la table `epreuve`.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 * @see		FormulaireManager.enregistrer()
	 */
	protected function enregistrerEpreuve() {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__);

		// Identifiant de l'épreuve
		$nIdEpreuve							= DataHelper::get($this->_aQCM,	'epreuve_id',						DataHelper::DATA_TYPE_INT);

		// Identifiant de la génération
		$nIdGeneration						= DataHelper::get($this->_aQCM, 'generation_id',					DataHelper::DATA_TYPE_INT);

		// Construction du tableau associatif les champs à enregistrer
		$aSet	= array(
			"id_generation = :id_generation,",
			"id_stage = :id_stage,",

			"type_epreuve = :type_epreuve,",
			"date_epreuve = DATE_FORMAT(:date_epreuve, '%Y-%m-%d'),",
			"heure_epreuve = TIME_FORMAT(:heure_epreuve, '%H:%i'),",
			"duree_epreuve = :duree_epreuve,",
			"liste_salles_epreuve = :liste_salles_epreuve,",
			"table_affectation_epreuve = :table_affectation_epreuve,",
			"table_aleatoire_epreuve = :table_aleatoire_epreuve,",
			"libelle_epreuve = :libelle_epreuve,",

			"id_valideur = :id_valideur",
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_generation'				=> DataHelper::get($this->_aQCM, 'generation_id',					DataHelper::DATA_TYPE_INT),
			':id_stage'						=> DataHelper::get($this->_aQCM, 'epreuve_stage',					DataHelper::DATA_TYPE_INT),

			':type_epreuve'					=> DataHelper::get($this->_aQCM, 'epreuve_type',					DataHelper::DATA_TYPE_MYTXT),
			':date_epreuve'					=> DataHelper::get($this->_aQCM, 'epreuve_date',					DataHelper::DATA_TYPE_MYDATE),
			':heure_epreuve'				=> DataHelper::get($this->_aQCM, 'epreuve_heure',					DataHelper::DATA_TYPE_TIME),
			':duree_epreuve'				=> DataHelper::get($this->_aQCM, 'epreuve_duree',					DataHelper::DATA_TYPE_INT_ABS),
			':liste_salles_epreuve'			=> DataHelper::get($this->_aQCM, 'epreuve_liste_salles',			DataHelper::DATA_TYPE_MYARRAY_NUM,		0),
			':table_affectation_epreuve'	=> DataHelper::get($this->_aQCM, 'epreuve_table_affectation',		DataHelper::DATA_TYPE_MYBOOL,			false),
			':table_aleatoire_epreuve'		=> DataHelper::get($this->_aQCM, 'epreuve_table_aleatoire',			DataHelper::DATA_TYPE_MYBOOL,			false),
			':libelle_epreuve'				=> DataHelper::get($this->_aQCM, 'epreuve_libelle',					DataHelper::DATA_TYPE_MYTXT),

			':id_valideur'					=> $this->_idUtilisateur
		);

		// Fonctionnalité réalisée si l'identifiant de l'épreuve est présent
		if (!empty($nIdEpreuve)) {
			// Requête UPDATE
			$aInitQuery						= array("UPDATE epreuve SET");
			$aSet[]							= "WHERE id_epreuve = :id_epreuve";
			$aBind[':id_epreuve']			= $nIdEpreuve;

			// Exécution de la requête UPDATE
			$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		} else {
			// Requête INSERT
			$aInitQuery						= array("INSERT INTO epreuve SET");

			// Exécution de la requête INSERT
			$nIdEpreuve = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		}

		// Enregistrement de l'action dans les LOGs avec COMMIT
		$this->logEpreuve($nIdEpreuve, $aInitQuery);

		// Renvoi de l'identifiant
		return $nIdEpreuve;
	}

	/**
	 * @brief	Suppression des relations GENERATION / EPREUVE.
	 *
	 * @li	Vérification qu'une épreuve ne soit pas déjà été réalisée.
	 *
	 * @param	integer	$nIdEpreuve			: Identifiant de l'épreuve.
	 * @return	boolean
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function supprimerEpreuve($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		try {
			// Requête DELETE
			$sQuery = "DELETE FROM epreuve WHERE id_epreuve = :id_epreuve";

			// Construction du tableau associatif de l'étiquette du formulaire
			$aBind = array(":id_epreuve" => $nIdEpreuve);

			// Suppression de toutes les précédentes relations avec le formulaire
			$this->executeSQL($sQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException('EQueryDelete', DataHelper::queryToString($sQuery, $aBind));
		}
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `generation`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `generation`.
	 *
	 * @param	integer	$nIdGeneration		: Identifiant de la génération en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logGeneration($nIdGeneration, $aQuery, $bFinalCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdGeneration, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_generation = :id_generation,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_generation'				=> $nIdGeneration,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_generation', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Enregistrement d'une génération de formulaire QCM.
	 *
	 * Méthode exploitant une requête préparées pour l'enregistrement dans la table `generation`.
	 *
	 * @li	Enregistrement de la date de l'épreuve dans le champ `date_epreuve_generation` de la table.
	 * @li	Enregistrement du libellé de l'épreuve dans le champ `nom_epreuve_generation` de la table.
	 *
	 * @return	integer, identifiant de la table `generation`.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function enregistrerGeneration() {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__);

		// Identifiant de la génération
		$nIdGeneration						= DataHelper::get($this->_aQCM, 'generation_id',					DataHelper::DATA_TYPE_INT);

		// Identifiant du formulaire
		$nIdFormulaire						= DataHelper::get($this->_aQCM, 'formulaire_id',					DataHelper::DATA_TYPE_INT);

		// Construction du tableau associatif les champs à enregistrer
		$aSet	= array(
			"id_formulaire = :id_formulaire,",
			"code_candidat_generation = :code_candidat_generation,",
			"consignes_generation = :consignes_generation,",
			"exemplaires_generation = :exemplaires_generation,",
			"format_generation = :format_generation,",
			"separate_generation = :separate_generation,",
			"langue_generation = :langue_generation,",
			"nom_epreuve_generation = :libelle_epreuve,",
			"date_epreuve_generation = :date_epreuve,",
			"seed_generation = :seed_generation,",
			"cartouche_candidat_generation = :cartouche_candidat_generation,",

			"id_valideur = :id_valideur",
		);

		// Si le formulaire a été validé, le repasser en attente de validation
		$nValidationFormulaire				= DataHelper::get($this->_aQCM, 'formulaire_validation',			DataHelper::DATA_TYPE_INT,	self::VALIDATION_DEFAUT);
		if ($nValidationFormulaire > self::VALIDATION_ATTENTE) {
			// Écrasement de la valeure avec celle par défaut
			$nValidationFormulaire			= self::VALIDATION_ATTENTE;
		}

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_formulaire'				=> DataHelper::get($this->_aQCM, 'formulaire_id',					DataHelper::DATA_TYPE_INT),
			':code_candidat_generation'		=> DataHelper::get($this->_aQCM, 'generation_code_candidat',		DataHelper::DATA_TYPE_INT_ABS),
			':consignes_generation'			=> DataHelper::get($this->_aQCM, 'generation_consignes',			DataHelper::DATA_TYPE_MYTXT),
			':exemplaires_generation'		=> DataHelper::get($this->_aQCM, 'generation_exemplaires',			DataHelper::DATA_TYPE_INT_ABS),
			':format_generation'			=> DataHelper::get($this->_aQCM, 'generation_format',				DataHelper::DATA_TYPE_MYTXT),
			':separate_generation'			=> DataHelper::get($this->_aQCM, 'generation_separate',				DataHelper::DATA_TYPE_MYBOOL),
			':langue_generation'			=> DataHelper::get($this->_aQCM, 'generation_langue',				DataHelper::DATA_TYPE_MYTXT),
			':libelle_epreuve'				=> DataHelper::get($this->_aQCM, 'epreuve_libelle',					DataHelper::DATA_TYPE_MYTXT),
			':date_epreuve'					=> DataHelper::get($this->_aQCM, 'epreuve_date',					DataHelper::DATA_TYPE_MYDATE),
			':seed_generation'				=> DataHelper::get($this->_aQCM, 'generation_seed',					DataHelper::DATA_TYPE_INT_ABS),
			':cartouche_candidat_generation'=> DataHelper::get($this->_aQCM, 'generation_cartouche_candidat',	DataHelper::DATA_TYPE_MYTXT),

			':id_valideur'					=> $this->_idUtilisateur
		);

		// Fonctionnalité réalisée si l'identifiant de la génération est présent
		if (!empty($nIdGeneration)) {
			// Requête UPDATE
			$aInitQuery						= array("UPDATE generation SET");
			$aSet[]							= "WHERE id_generation = :id_generation";
			$aBind[':id_generation']		= $nIdGeneration;

			// Exécution de la requête UPDATE
			$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		} else {
			// Requête INSERT
			$aInitQuery						= array("INSERT INTO generation SET");

			// Exécution de la requête INSERT
			$nIdGeneration = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		}

		// Enregistrement de l'action dans les LOGs avec COMMIT
		$this->logGeneration($nIdGeneration, $aInitQuery);

		// Renvoi de l'identifiant
		return $nIdGeneration;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `formulaire_question`
	 ******************************************************************************************************/

	/**
	 * @brief	Suppression des relations FORMULAIRE / QUESTION.
	 *
	 * Suppression de toutes les relations entre les tables `formulaire` et `question`.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire en base.
	 * @return	boolean
	 */
	protected function supprimerFormulaireQuestion($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Requête DELETE
		$sQuery = "DELETE FROM formulaire_question WHERE id_formulaire = :id_formulaire";
		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(":id_formulaire" => $nIdFormulaire);
		// Suppression de toutes les précédentes relations avec le formulaire
		$this->executeSQL($sQuery, $aBind);
	}

	/**
	 * @brief	Recherche si une relation FORMULAIRE / QUESTION existe.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire en base.
	 * @param	integer	$nIdQuestion		: Identifiant de la question en base.
	 * @return	integer, identifiant de la relation FORMULAIRE / QUESTION.
	 */
	private function getIdFormulaireQuestion($nIdFormulaire, $nIdQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $nIdQuestion);

		// Requête SELECT
		$aQuery = array(
			"SELECT * FROM formulaire_question",
			"WHERE id_formulaire = :id_formulaire",
			"  AND id_question = :id_question"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_formulaire'				=> $nIdFormulaire,
			':id_question'					=> $nIdQuestion
		);

		// Recherche si l'enregistrement existe déjà
		$aSearch = $this->executeSQL($aQuery, $aBind, 0);
		if (DataHelper::isValidArray($aSearch)) {
			// Récupération de l'identifiant
			$nIdFormulaireQuestion			= $aSearch['id_formulaire_question'];
		} else {
			$nIdFormulaireQuestion			= false;
		}

		// Renvoi de l'identifiant
		return $nIdFormulaireQuestion;
	}

	/**
	 * @brief	Enregistrement de la relation FORMULAIRE / QUESTION.
	 *
	 * Recherche l'identifiant de la relation entre la table `formulaire` et `question`.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la relation QUESTION.
	 * @return	boolean
	 */
	protected function enregistrerFormulaireQuestion($nIdQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion);

		// Identifiant du formulaire
		$nIdFormulaire	= DataHelper::get($this->_aQCM, 'formulaire_id', DataHelper::DATA_TYPE_INT);

		// Requête INSERT
		$aQuery = array(
			"INSERT INTO formulaire_question SET",
			"id_formulaire = :id_formulaire,",
			"id_question = :id_question"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_formulaire"				=> $nIdFormulaire,
			":id_question"					=> $nIdQuestion
		);

		// Exécution de la requête
		return $this->executeSQL($aQuery, $aBind);
	}

	/******************************************************************************************************/

	/**
	 * @brief	Recherche si une relation QUESTION / REPONSE existe.
	 *
	 * Recherche l'identifiant de la relation entre la table `question` et `reponse`.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la question en base.
	 * @param	integer	$nIdReponse			: Identifiant de la réponse en base.
	 * @return	integer, identifiant de la table `question_reponse`.
	 */
	private function getIdQuestionReponse($nIdQuestion, $nIdReponse) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $nIdReponse);

		// Initialisation de l'identifiant
		$nIdQuestionReponse = false;

		// Requête SELECT
		$aQuery = array(
			"SELECT * FROM question_reponse",
			"WHERE id_question = :id_question",
			"  AND id_reponse = :id_reponse"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_question'					=> $nIdQuestion,
			':id_reponse'					=> $nIdReponse
		);

		// Recherche si l'enregistrement existe déjà
		$aSearch = $this->executeSQL($aQuery, $aBind, 0);
		if (DataHelper::isValidArray($aSearch)) {
			$nIdQuestionReponse = $aSearch['id_question_reponse'];
		}

		// Renvoi de l'identifiant
		return $nIdQuestionReponse;
	}

	/**
	 * @brief	Enregistrement de la relation QUESTION / REPONSE.
	 *
	 * Recherche l'identifiant de la relation entre la table `question` et `reponse`.
	 *
	 * @param	integer	$nQuestion			: Occurrence de la question dans $_aQCM.
	 * @param	integer	$nReponse			: Occurrence de la réponse à la question passée en paramètre dans $_aQCM.
	 * @return	integer, identifiant de la table `question_reponse`.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function enregistrerQuestionReponse($nQuestion, $nReponse) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nQuestion, $nReponse);

		// Initialisation de l'identifiant de la table `question_reponse`
		$nIdQuestionReponse						= null;
		// Identifiant de la question
		$nIdQuestion							= DataHelper::get($this->_aQCM['question_id'],					$nQuestion,	DataHelper::DATA_TYPE_INT);
		// Identifiant de la réponse
		$nIdReponse								= DataHelper::get($this->_aQCM['reponse_id'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_INT);

		// Fonctionnalité réalisée si les identifiants sont valides
		if (!empty($nIdQuestion) && !empty($nIdReponse)) {
			// Construction du tableau associatif des champs à enregistrer
			$aSet	= array(
				"id_question = :id_question,",
				"id_reponse = :id_reponse"
			);

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind	= array(
				":id_question"					=> $nIdQuestion,
				":id_reponse"					=> $nIdReponse
			);

			// Contrôle de l'existence d'une relation entre les tables `question` et `reponse`
			$nIdQuestionReponse = $this->getIdQuestionReponse($nIdQuestion, $nIdReponse);
			if (!empty($nIdQuestionReponse)) {
				// Requête UPDATE
				$aInitQuery						= array("UPDATE question_reponse SET");
				$aSet[]							= "WHERE id_question_reponse = :id_question_reponse";
				$aBind[':id_question_reponse']	= $nIdQuestionReponse;

				// Exécution de la requête UPDATE
				$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			} else {
				// Requête INSERT
				$aInitQuery						= array("INSERT INTO question_reponse SET");

				// Exécution de la requête INSERT
				$nIdQuestionReponse = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			}
		}

		// Renvoi de l'identifiant
		return $nIdQuestionReponse;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `reponse`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `reponse`.
	 *
	 * @param	integer	$nIdReponse			: Identifiant de la réponse en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logReponse($nIdReponse, $aQuery, $bFinalCommit = false) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdReponse, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_reponse = :id_reponse,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_reponse'					=> $nIdReponse,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_reponse', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Enregistrement d'une réponse.
	 *
	 * Méthode exploitant une requête préparées pour l'enregistrement dans la table `reponse`.
	 *
	 * @li La réponse n'est pas enregistrée si le texte est vide.
	 *
	 * @li L'identifiant de l'utilisateur connecté est enregistré en tant que rédacteur.
	 *
	 * @li Requête INSERT si l'identifiant de la question est vide
	 * @code
	 * 		INSERT INTO reponse SET
	 * 		texte_reponse = :texte_reponse,
	 * 		valeur_reponse = :valeur_reponse,
	 * 		id_redacteur = :id_redacteur
	 * @endcode
	 *
	 * @li Requête UPDATE si l'identifiant de la question est vide
	 * @code
	 * 		UPDATE reponse SET
	 * 		texte_reponse = :texte_reponse,
	 * 		valeur_reponse = :valeur_reponse,
	 * 		id_redacteur = :id_redacteur
	 * 		WHERE id_reponse = :id_reponse
	 * @endcode
	 *
	 * @param	integer	$nQuestion			: Occurrence de la question dans $_aQCM.
	 * @param	integer	$nReponse			: Occurrence de la réponse à la question passée en paramètre dans $_aQCM.
	 * @return	integer, identifiant de la question.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function enregistrerReponse($nQuestion, $nReponse) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nQuestion, $nReponse);

		// Identifiant de la question
		$nIdQuestion						= DataHelper::get($this->_aQCM['question_id'],						$nQuestion,	DataHelper::DATA_TYPE_INT);

		// Identifiant de la réponse
		$sTexteReponse	= null;
		$nIdReponse							= DataHelper::get($this->_aQCM['reponse_id'][$nQuestion],			$nReponse,	DataHelper::DATA_TYPE_INT);

		// Vérification de la présence d'un texte
		$sTexteReponse	= null;
		if (isset($this->_aQCM['reponse_texte'][$nQuestion])) {
			$sTexteReponse					= DataHelper::get($this->_aQCM['reponse_texte'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYTXT);
		}

		// Fonctionnalité réalisée si le texte de la réponse est vide
		if (strlen($sTexteReponse) == 0) {
			// Suppression de la réponse si elle existe
			$this->supprimerReponseById($nIdQuestion, $nIdReponse, false);

			// Suppression de l'identifiant
			$nIdReponse = null;
		} else {
			// Construction du tableau associatif des champs à enregistrer
			$aSet	= array(
				"texte_reponse = :texte_reponse,",
				"valide_reponse = :valide_reponse,",
				"valeur_reponse = :valeur_reponse,",
				"sanction_reponse = :sanction_reponse,",
				"penalite_reponse = :penalite_reponse,",
				"id_redacteur = :id_redacteur"
			);

			// Vérification de la validité de la réponse
			$bValideReponse					= false;
			if (isset($this->_aQCM['reponse_valide'][$nQuestion])) {
				$bValideReponse				= DataHelper::get($this->_aQCM['reponse_valide'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYBOOL);
			}

			// Vérification de la pénalité de la réponse
			$bSanctionReponse				= false;
			if (isset($this->_aQCM['reponse_sanction'][$nQuestion])) {
				$bSanctionReponse			= DataHelper::get($this->_aQCM['reponse_sanction'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYBOOL);
			}

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind	= array(
				':texte_reponse'			=> $sTexteReponse,
				':valide_reponse'			=> (int) $bValideReponse,
				':valeur_reponse'			=> DataHelper::get($this->_aQCM['reponse_valeur'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS),
				':sanction_reponse'			=> (int) $bSanctionReponse,
				':penalite_reponse'			=> DataHelper::get($this->_aQCM['reponse_penalite'][$nQuestion],	$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS),
				':id_redacteur'				=> $this->_idUtilisateur
			);

			// Écrasement de la valeur de la réponse si cette dernière n'est pas valide
			if (!$bValideReponse) {
				$aBind[':valeur_reponse']	= 0;
			}

			// Écrasement de la sanction de la réponse si cette dernière n'est pas pénalisé
			if (!$bSanctionReponse) {
				$aBind[':penalite_reponse']	= 0;
			}

			// Fonctionnalité réalisée si l'identifiant de la réponse est présent
			if (!empty($nIdReponse)) {
				// Initialisation de la requête UPDATE
				$aInitQuery					= array("UPDATE reponse SET");
				$aSet[]						= "WHERE id_reponse = :id_reponse";
				$aBind[':id_reponse']		= $nIdReponse;

				// Exécution de la requête UPDATE
				$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			} else {
				// Initialisation de la requête INSERT
				$aInitQuery = array("INSERT INTO reponse SET");

				// Exécution de la requête INSERT
				$nIdReponse = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			}

			// Enregistrement de l'action dans les LOGs
			$this->logReponse($nIdReponse, $aInitQuery);
		}

		// Renvoi de l'identifiant
		return $nIdReponse;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `question`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `question`.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la question en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logQuestion($nIdQuestion, $aQuery, $bFinalCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_question = :id_question,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_question'					=> $nIdQuestion,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_question', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Enregistrement d'une question.
	 *
	 * Méthode exploitant une requête préparées pour l'enregistrement dans la table `question`.
	 *
	 * @li L'identifiant de l'utilisateur connecté est enregistré en tant que rédacteur.
	 *
	 * @li Requête INSERT si l'identifiant de la question est vide
	 * @code
	 * 		INSERT INTO question SET
	 * 			titre_question = :titre_question,
	 * 			enonce_question = :enonce_question,
	 * 			correction_question = :correction_question,
	 * 			bareme_question = :bareme_question,
	 * 			penalite_question = :penalite_question,
	 * 			libre_question = :libre_question,
	 * 			lignes_question = :lignes_question,
	 * 			id_redacteur = :id_redacteur
	 * @endcode
	 *
	 * @li Requête UPDATE si l'identifiant de la question est vide
	 * @code
	 * 		UPDATE question SET
	 * 			titre_question = :titre_question,
	 * 			enonce_question = :enonce_question,
	 * 			correction_question = :correction_question,
	 * 			bareme_question = :bareme_question,
	 * 			penalite_question = :penalite_question,
	 * 			libre_question = :libre_question,
	 * 			lignes_question = :lignes_question,
	 * 			id_redacteur = :id_redacteur
	 * 		WHERE id_reponse = :id_reponse
	 * @endcode
	 *
	 * @param	integer	$nQuestion			: Occurrence de la question dans $_aQCM.
	 * @return	integer, identifiant de la question.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function enregistrerQuestion($nQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nQuestion);

		// Identifiant de la question
		$nIdQuestion	= DataHelper::get($this->_aQCM['question_id'], $nQuestion, DataHelper::DATA_TYPE_INT);

		// Construction du tableau associatif les champs à enregistrer
		$aSet	= array(
			"titre_question = :titre_question,",
			"stricte_question = :stricte_question,",
			"enonce_question = :enonce_question,",
			"correction_question = :correction_question,",
			"bareme_question = :bareme_question,",
			"penalite_question = :penalite_question,",
			"libre_question = :libre_question,",
			"lignes_question = :lignes_question,",
			"id_redacteur = :id_redacteur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':titre_question'				=> DataHelper::get($this->_aQCM['question_titre'],					$nQuestion,	DataHelper::DATA_TYPE_MYTXT),
			':stricte_question'				=> DataHelper::get($this->_aQCM['question_stricte'],				$nQuestion, DataHelper::DATA_TYPE_MYBOOL),
			':enonce_question'				=> DataHelper::get($this->_aQCM['question_enonce'],					$nQuestion, DataHelper::DATA_TYPE_MYTXT),
			':correction_question'			=> DataHelper::get($this->_aQCM['question_correction'],				$nQuestion, DataHelper::DATA_TYPE_MYTXT),
			':bareme_question'				=> DataHelper::get($this->_aQCM['question_bareme'],					$nQuestion, DataHelper::DATA_TYPE_MYFLT_ABS),
			':penalite_question'			=> DataHelper::get($this->_aQCM['question_penalite'],				$nQuestion, DataHelper::DATA_TYPE_INT_ABS),
			':libre_question'				=> DataHelper::get($this->_aQCM['question_libre'],					$nQuestion, DataHelper::DATA_TYPE_MYBOOL),
			':lignes_question'				=> DataHelper::get($this->_aQCM['question_lignes'],					$nQuestion, DataHelper::DATA_TYPE_INT_ABS),
			':id_redacteur'					=> $this->_idUtilisateur
		);

		// Enregistrement du nombre de lignes uniquement s'il est différent de la valeur par défaut
		if ($aBind[':lignes_question'] == self::QUESTION_LIBRE_LIGNES_DEFAUT || empty($aBind[':lignes_question'])) {
			$aBind[':lignes_question']		= null;
		}

		// Fonctionnalité réalisée si l'identifiant de la question est présent
		if (!empty($nIdQuestion)) {
			// Requête UPDATE
			$aInitQuery						= array("UPDATE question SET");
			$aSet[]							= "WHERE id_question = :id_question";
			$aBind[':id_question']			= $nIdQuestion;

			// Exécution de la requête UPDATE
			$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		} else {
			// Requête INSERT
			$aInitQuery = array("INSERT INTO question SET");

			// Exécution de la requête INSERT
			$nIdQuestion = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		}

		// Enregistrement de la relation FORMULAIRE / QUESTION dans la table `formulaire_question`
		$this->enregistrerFormulaireQuestion($nIdQuestion);

		// Enregistrement de l'action dans les LOGs avec COMMIT
		$this->logQuestion($nIdQuestion, $aInitQuery);

		// Renvoi de l'identifiant
		return $nIdQuestion;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `formulaire`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `formulaire`.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logFormulaire($nIdFormulaire, $aQuery, $bFinalCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_formulaire = :id_formulaire,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_formulaire'				=> $nIdFormulaire,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_formulaire', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Enregistrement du formulaire.
	 *
	 * Méthode exploitant une requête préparées pour l'enregistrement dans la table `formulaire`.
	 *
	 * @li L'identifiant de l'utilisateur connecté est enregistré en tant que rédacteur.
	 *
	 * @li Requête INSERT si l'identifiant de la question est vide
	 * @code
	 * 		INSERT INTO formulaire SET
	 * 			titre_formulaire = :titre_formulaire,
	 * 			id_domaine = :id_domaine,
	 * 			id_sous_domaine = :id_sous_domaine,
	 * 			id_categorie = :id_categorie,
	 * 			id_sous_categorie = :id_sous_categorie,
	 * 			note_finale_formulaire = :note_finale_formulaire,
	 * 			penalite_formulaire = :penalite_formulaire,
	 * 			presentation_formulaire = :presentation_formulaire,
	 * 			id_redacteur = :id_redacteur
	 * @endcode
	 *
	 * @li Requête UPDATE si l'identifiant de la question est vide
	 * @code
	 * 		UPDATE formulaire SET
	 * 			titre_formulaire = :titre_formulaire,
	 * 			id_domaine = :id_domaine,
	 * 			id_sous_domaine = :id_sous_domaine,
	 * 			id_categorie = :id_categorie,
	 * 			id_sous_categorie = :id_sous_categorie,
	 * 			note_finale_formulaire = :note_finale_formulaire,
	 * 			penalite_formulaire = :penalite_formulaire,
	 * 			presentation_formulaire = :presentation_formulaire,
	 * 			id_redacteur = :id_redacteur
	 * 		WHERE formulaire = :id_formulaire
	 * @endcode
	 *
	 * @return	integer, identifiant du formulaire QCM enregistré en base.
	 * @throws	ApplicationException gérée par la méthode enregistrer() en amont.
	 */
	protected function enregistrerFormulaire() {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__);

		// Identifiant du formulaire
		$nIdFormulaire	= DataHelper::get($this->_aQCM, 'formulaire_id', DataHelper::DATA_TYPE_INT);

		// Construction du tableau associatif les champs à enregistrer
		$aSet	= array(
			"titre_formulaire = :titre_formulaire,",
			"id_domaine = :id_domaine,",
			"id_sous_domaine = :id_sous_domaine,",
			"id_categorie = :id_categorie,",
			"id_sous_categorie = :id_sous_categorie,",

			"strict_formulaire = :strict_formulaire,",
			"note_finale_formulaire = :note_finale_formulaire,",
			"penalite_formulaire = :penalite_formulaire,",
			"presentation_formulaire = :presentation_formulaire,",

			"validation_formulaire = :validation_formulaire,",
			"id_redacteur = :id_redacteur,",
			"id_valideur = :id_valideur",
		);

		// Si le formulaire a été validé, le repasser en attente de validation
		$nValidationFormulaire				= DataHelper::get($this->_aQCM, 'formulaire_validation',		DataHelper::DATA_TYPE_INT_ABS,		self::VALIDATION_DEFAUT);
		if ($nValidationFormulaire > self::VALIDATION_ATTENTE) {
			$nValidationFormulaire			= self::VALIDATION_ATTENTE;
		}

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':titre_formulaire'				=> DataHelper::get($this->_aQCM, 'formulaire_titre',			DataHelper::DATA_TYPE_MYTXT,		self::TITRE_DEFAUT,			true),
			':id_domaine'					=> DataHelper::get($this->_aQCM, 'formulaire_domaine',			DataHelper::DATA_TYPE_INT_ABS),
			':id_sous_domaine'				=> DataHelper::get($this->_aQCM, 'formulaire_sous_domaine',		DataHelper::DATA_TYPE_INT_ABS),
			':id_categorie'					=> DataHelper::get($this->_aQCM, 'formulaire_categorie',		DataHelper::DATA_TYPE_INT_ABS),
			':id_sous_categorie'			=> DataHelper::get($this->_aQCM, 'formulaire_sous_categorie',	DataHelper::DATA_TYPE_INT_ABS),

			':strict_formulaire'			=> DataHelper::get($this->_aQCM, 'formulaire_strict',			DataHelper::DATA_TYPE_MYBOOL),
			':note_finale_formulaire'		=> DataHelper::get($this->_aQCM, 'formulaire_note_finale',		DataHelper::DATA_TYPE_INT_ABS,		self::NOTE_FINALE_DEFAUT),
			':penalite_formulaire'			=> DataHelper::get($this->_aQCM, 'formulaire_penalite',			DataHelper::DATA_TYPE_INT_ABS,		self::PENALITE_DEFAUT),
			':presentation_formulaire'		=> DataHelper::get($this->_aQCM, 'formulaire_presentation',		DataHelper::DATA_TYPE_MYTXT,		self::PRESENTATION_DEFAUT),

			':validation_formulaire'		=> $nValidationFormulaire,
			':id_redacteur'					=> $this->_idUtilisateur,
			':id_valideur'					=> null,
		);

		// Fonctionnalité réalisée si l'identifiant de la question est présent
		if (!empty($nIdFormulaire)) {
			// Requête UPDATE
			$aInitQuery						= array("UPDATE formulaire SET");
			$aSet[]							= "WHERE id_formulaire = :id_formulaire";
			$aBind[':id_formulaire']		= $nIdFormulaire;

			// Exécution de la requête UPDATE
			$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		} else {
			// Requête INSERT
			$aInitQuery = array("INSERT INTO formulaire SET");

			// Exécution de la requête INSERT
			$nIdFormulaire = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
		}

		// Suppression de toutes les précédentes relations avec le formulaire courant
		$this->supprimerFormulaireQuestion($nIdFormulaire);

		// Enregistrement de l'action dans les LOGs
		$this->logFormulaire($nIdFormulaire, $aInitQuery);

		// Renvoi de l'identifiant
		return $nIdFormulaire;
	}

	/******************************************************************************************************
	 * @todo ENREGISTRER
	 ******************************************************************************************************/

	/**
	 * @brief	Parcours du formulaire afin d'enregistrer chaque partie.
	 *
	 * @li Si l'identifiant existe déjà en base, l'enregistrement sera redirigé vers une méthode UPDATE,
	 * sinon, vers une méthode INSERT.
	 *
	 * @li Commit final si toutes les phases d'enregistrement se déroulent correctement.
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
	 * @param	boolean	$bGeneration		: (optionnel) enregistrement de la génération du formulaire QCM.
	 * @return	array, tableau contenant l'ensemble des données du formulaire QCM.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrer(array $aQCM, $bGeneration = false) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $aQCM, $bGeneration);

		// Initialisation du formulaire
		$this->_aQCM = $aQCM;

		// Force le mode transactionnel
		$this->beginTransaction();

		$aErrorMessage								= array();
		try {
			// Enregistrement du formulaire QCM sans la génération
			if (!$bGeneration) {
				// Initialisation de la liste des identifiants de questions
				$aListeQuestion	= array();

				// Enregistrement dans la table `formulaire`
				$this->_aQCM['formulaire_id']		= $this->enregistrerFormulaire();

				// Enregistrement dans les tables `question`, `reponse` et `formulaire_question_reponse`
				for ($nQuestion = 0 ; $nQuestion < $this->_aQCM['formulaire_nb_total_questions'] ; $nQuestion++) {
					// Enregistrement du questionnaire
					$nIdQuestion					= $this->enregistrerQuestion($nQuestion);

					// Ajout de l'identifiant de la question dans le formulaire
					$this->_aQCM['question_id'][$nQuestion] = $nIdQuestion;
					$aListeQuestion[$nQuestion]		= $nIdQuestion;

					// Boucle de parcours des réponses
					for ($nReponse = 0 ; $nReponse < $this->_aQCM['formulaire_nb_max_reponses'] ; $nReponse++) {
						// Enregistrement de la réponse
						if (isset($this->_aQCM['reponse_id'][$nQuestion])) {
							$this->_aQCM['reponse_id'][$nQuestion][$nReponse] = $this->enregistrerReponse($nQuestion, $nReponse);

							// Enregistrement dans la table `question_reponse`
							$nIdQuestionReponse		= $this->enregistrerQuestionReponse($nQuestion, $nReponse);
						}
					}
				}

				// Récupération des question dans la bibliothèque
				$aListeBibliotheque = DataHelper::get($this->_aQCM, 'bibliotheque_id',	DataHelper::DATA_TYPE_ARRAY,	array());
				// Parcours l'ensemble de la bibliothèque à ajouter
				foreach ($aListeBibliotheque as $nOccurrence => $nIdBibliotheque) {
					// Fonctionnalité réalisée si l'identifiant de la question n'existe pas déjà
					if (!in_array($nIdBibliotheque, $aListeQuestion)) {
						// Récupération du contenu de la question / réponse et ajout au formulaire
						$aQuestion = $this->getQuestionReponsesByIdQuestion($nIdBibliotheque, 0);

						// Fonctionnalité réalisée si la question est valide
						if (DataHelper::isValidArray($aQuestion)) {
							// Enregistrement de la relation FORMULAIRE / QUESTION.
							$this->enregistrerFormulaireQuestion($nIdBibliotheque);
						} else {
							continue;
						}

						// Ajout du questionnaire au formulaire
						if (! isset($this->_aQCM['reponse_id']) || is_null($this->_aQCM['reponse_id'][0])) {
							// Le formulaire est vierge
							$this->_aQCM = array_merge($this->_aQCM, $aQuestion);
						} else {
							// Le formulaire contient déjà des questions
							$this->_aQCM = array_merge_recursive($this->_aQCM, $aQuestion);
						}

						// Actualisation du nombre de questions dans le formulaire
						if (isset($this->_aQCM['formulaire_nb_total_questions'])) {
							$this->_aQCM['formulaire_nb_total_questions']++;
						} else {
							$this->_aQCM['formulaire_nb_total_questions'] = 1;
						}

						$nNbMaxResponses = 0;
						// Détermination du nombre de réponses maximum
						for ($nReponse = 0 ; $nReponse < count($aQuestion['reponse_id'][0]) ; $nReponse++) {
							// Vérification de la présence d'un énoncé
							if (isset($aQuestion['reponse_texte'][0][$nReponse]) && !is_null($aQuestion['reponse_texte'][0][$nReponse])) {
								$nNbMaxResponses++;
							}
						}

						// Actualisation du nombre de réponses maximum
						if ($nNbMaxResponses > $this->_aQCM['formulaire_nb_max_reponses']) {
							$this->_aQCM['formulaire_nb_max_reponses'] = $nNbMaxResponses;
						}
					}

					// Purge de la question en mémoire une fois le traitement terminé
					unset($this->_aQCM['bibliotheque_id'][$nOccurrence]);
				}
			} else {
				// Enregistrement de la génération selon l'identifiant du formulaire
				$this->_aQCM['generation_id']		= $this->enregistrerGeneration();

				// Fonctionnalité réalisée si un stage est sélectionné
				if (isset($this->_aQCM['epreuve_stage']) && !empty($this->_aQCM['epreuve_stage'])) {
					// Enregistrement de l'épreuve
					$this->_aQCM['epreuve_id']		= $this->enregistrerEpreuve();
				} elseif (!empty($this->_aQCM['epreuve_id'])) {
					// Suppression de l'épreuve associée
					$this->supprimerEpreuve($this->_aQCM['epreuve_id']);
					unset($this->_aQCM['epreuve_id']);
				} else {
					// Ajout d'un message d'erreur à la collection
					$aErrorMessage[]				= "Veuillez renseigner un stage valide...";
					// Le champ du stage n'est pas correct
					throw new ApplicationException('EQueryData');
				}
			}

			// Validation des modifications
			$this->oSQLConnector->commit();

			// Affichage d'un message de confirmation
			ViewRender::setMessageSuccess("Enregistrement réalisé avec succès !");
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();

			// Affichage d'un message d'erreur
			ViewRender::setMessageError("Erreur rencontrée lors de l'enregistrement...", $aErrorMessage);
			//throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Récupération de l'ensemble des champs de la bibliothèque
		$aBibliothequeFields		= DataHelper::getLinesFromArrayLike($this->_aQCM, "bibliotheque_");
		// Parcours des éléments de la bibliothèque
		foreach ($aBibliothequeFields as $sField) {
			// Réinitialisation du champ du formulaire
			$this->_aQCM[$sField]	= array();
		}

		// Renvoi du formulaire
		return $this->_aQCM;
	}

	/******************************************************************************************************
	 * @todo CHARGER
	 ******************************************************************************************************/

	/**
	 * @brief	Chargement d'un formulaire QCM enregistré en base de données.
	 *
	 * @param	integer	$nIdFormulaire	: Identifiant du formulaire à charger.
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
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function charger($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Initialisation du formulaire
		$this->_aQCM = array();

		try {
			// Initialisation du formulaire
			$this->_aQCM['formulaire_id']										= $nIdFormulaire;

			// Récupération des données du formulaire
			$aFormulaire		= $this->getFormulaireById($nIdFormulaire);
			// Fonctionnalité réalisée si le formulaire n'est pas valide
			if (!DataHelper::isValidArray($aFormulaire)) {
				// Initialisation de l'identifiant
				$this->_aData['formulaire_id']									= null;
				// Génération d'une exception
				throw new ApplicationException('EParamBadValue');
			}

			// Chargement des données du formulaire
			$this->_aQCM['formulaire_titre']									= DataHelper::get($aFormulaire, 'titre_formulaire', 				DataHelper::DATA_TYPE_STR,		FormulaireManager::TITRE_DEFAUT);
			$this->_aQCM['formulaire_presentation']								= DataHelper::get($aFormulaire, 'presentation_formulaire', 			DataHelper::DATA_TYPE_TXT,		FormulaireManager::PRESENTATION_DEFAUT);
			$this->_aQCM['formulaire_validation']								= DataHelper::get($aFormulaire, 'validation_formulaire', 			DataHelper::DATA_TYPE_INT,		FormulaireManager::VALIDATION_DEFAUT);
			$this->_aQCM['formulaire_domaine']									= DataHelper::get($aFormulaire, 'id_domaine', 						DataHelper::DATA_TYPE_INT);
			$this->_aQCM['formulaire_sous_domaine']								= DataHelper::get($aFormulaire, 'id_sous_domaine', 					DataHelper::DATA_TYPE_INT);
			$this->_aQCM['formulaire_categorie']								= DataHelper::get($aFormulaire, 'id_categorie', 					DataHelper::DATA_TYPE_INT);
			$this->_aQCM['formulaire_sous_categorie']							= DataHelper::get($aFormulaire, 'id_sous_categorie', 				DataHelper::DATA_TYPE_INT);
			$this->_aQCM['formulaire_strict']									= DataHelper::get($aFormulaire, 'strict_formulaire', 				DataHelper::DATA_TYPE_BOOL,		FormulaireManager::QUESTION_STRICTE_DEFAUT);
			$this->_aQCM['formulaire_note_finale']								= DataHelper::get($aFormulaire, 'note_finale_formulaire', 			DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::NOTE_FINALE_DEFAUT);
			$this->_aQCM['formulaire_penalite']									= DataHelper::get($aFormulaire, 'penalite_formulaire', 				DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::PENALITE_DEFAUT);

			// Récupération de la liste des questions associées au formulaire
			$aListeQuestions	= $this->findQuestionsByIdFormulaire($nIdFormulaire);

			// Initialisation du nombre total de questions dans le formulaire
			$this->_aQCM['formulaire_nb_total_questions']						= count($aListeQuestions);

			// Initialisation du nombre maximal de réponses par question
			$nNombreMaxReponses	= FormulaireManager::NB_MAX_REPONSES_DEFAUT;

			// Parcours de la liste des questions
			foreach ($aListeQuestions as $nQuestion => $aQuestion) {
				// Chargement des données de la question courante
				$this->_aQCM['question_id'][$nQuestion]							= $aQuestion['id_question'];
				$this->_aQCM['question_titre'][$nQuestion]						= $aQuestion['titre_question'];
				$this->_aQCM['question_stricte'][$nQuestion]					= $aQuestion['stricte_question'];
				$this->_aQCM['question_libre'][$nQuestion]						= $aQuestion['libre_question'];
				$this->_aQCM['question_lignes'][$nQuestion]						= $aQuestion['lignes_question'];

				// Question stricte
				$bStricte		= false;
				if ($aQuestion['stricte_question']) {
					$bStricte	= true;
				}
				$this->_aQCM['question_stricte_checked'][$nQuestion]			= $bStricte;

				// Question libre
				$bLibre			= false;
				if ($aQuestion['libre_question']) {
					$bLibre		= true;
				}
				$this->_aQCM['question_libre_checked'][$nQuestion]				= $bLibre;

				$this->_aQCM['question_enonce'][$nQuestion]						= DataHelper::get($aQuestion, 'enonce_question',	DataHelper::DATA_TYPE_TXT);
				$this->_aQCM['question_correction'][$nQuestion]					= DataHelper::get($aQuestion, 'correction_question',DataHelper::DATA_TYPE_TXT);
				$this->_aQCM['question_bareme'][$nQuestion]						= DataHelper::get($aQuestion, 'bareme_question',	DataHelper::DATA_TYPE_MYFLT_ABS);
				$this->_aQCM['question_penalite'][$nQuestion]					= DataHelper::get($aQuestion, 'penalite_question',	DataHelper::DATA_TYPE_INT_ABS);

				// Récupération de la liste des réponses associées à la question courante
				$aListeReponses	= $this->findReponsesByIdQuestion($aQuestion['id_question']);
				if (count($aListeReponses) > $nNombreMaxReponses) {
					// Mise à jour du nombre maximal de réponses
					$nNombreMaxReponses = count($aListeReponses);
				}

				// Déclaration des éléments de réponse à la question
				$this->_aQCM['reponse_id'][$nQuestion]							= array();
				$this->_aQCM['reponse_texte'][$nQuestion]						= array();
				$this->_aQCM['reponse_valide'][$nQuestion]						= array();
				$this->_aQCM['reponse_valeur'][$nQuestion]						= array();
				$this->_aQCM['reponse_sanction'][$nQuestion]					= array();
				$this->_aQCM['reponse_penalite'][$nQuestion]					= array();

				// Parcours de la liste des réponses
				foreach ($aListeReponses as $nReponse => $aReponse) {
					// Chargement des éléments de réponse
					$this->_aQCM['reponse_id'][$nQuestion][$nReponse]			= DataHelper::get($aReponse, 'id_reponse',			DataHelper::DATA_TYPE_INT);
					$this->_aQCM['reponse_texte'][$nQuestion][$nReponse]		= DataHelper::get($aReponse, 'texte_reponse',		DataHelper::DATA_TYPE_TXT);
					$this->_aQCM['reponse_valide'][$nQuestion][$nReponse]		= DataHelper::get($aReponse, 'valide_reponse',		DataHelper::DATA_TYPE_BOOL);
					$this->_aQCM['reponse_valeur'][$nQuestion][$nReponse]		= 0;
					$this->_aQCM['reponse_sanction'][$nQuestion][$nReponse]		= DataHelper::get($aReponse, 'sanction_reponse',	DataHelper::DATA_TYPE_BOOL);
					$this->_aQCM['reponse_penalite'][$nQuestion][$nReponse]		= 0;

					// Fonctionnalité réalisée si la réponse est valide
					if ($aReponse['valide_reponse']) {
						$this->_aQCM['reponse_valeur'][$nQuestion][$nReponse]	= DataHelper::get($aReponse, 'valeur_reponse',		DataHelper::DATA_TYPE_MYFLT_ABS);
					}

					// Fonctionnalité réalisée si la réponse est pénalisée
					if ($aReponse['sanction_reponse']) {
						$this->_aQCM['reponse_penalite'][$nQuestion][$nReponse]	= DataHelper::get($aReponse, 'penalite_reponse',	DataHelper::DATA_TYPE_MYFLT_ABS);
					}
				}
			}

			// Initialisation du nombre maximal de réponses par question
			$this->_aQCM['formulaire_nb_max_reponses']							= $nNombreMaxReponses;

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aQCM;
	}

	/******************************************************************************************************
	 * @todo GÉNÉRATION D'UNE ÉPREUVE
	 ******************************************************************************************************/

	/**
	 * @brief	Génération d'une épreuve QCM enregistré en base de données.
	 *
	 * @param	integer	$nIdEpreuve	: Identifiant de l'épreuve à générer.
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
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function generer($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Initialisation du formulaire
		$this->_aQCM = array();

		try {
			// Initialisation du formulaire
			$this->_aQCM['epreuve_id']											= $nIdEpreuve;

			// Récupération des données de l'épreuve
			$aEpreuve			= $this->getEpreuveById($nIdEpreuve);

			// Fonctionnalité réalisée si le formulaire n'est pas valide
			if (!DataHelper::isValidArray($aEpreuve)) {
				// Initialisation de l'identifiant
				$this->_aData['epreuve_id']										= null;
				// Génération d'une exception
				throw new ApplicationException('EParamBadValue');
			}

			// Récupération du formulaire associé
			$nIdFormulaire														= DataHelper::get($aEpreuve,	'id_formulaire',					DataHelper::DATA_TYPE_INT);
			$this->_aQCM		= $this->charger($nIdFormulaire);

			// Chargement des données de génération (optionnelles)
			$this->_aQCM['generation_id']										= DataHelper::get($aEpreuve,	'id_generation', 					DataHelper::DATA_TYPE_INT,		null);
			$this->_aQCM['generation_langue']									= DataHelper::get($aEpreuve,	'langue_generation', 				DataHelper::DATA_TYPE_STR,		FormulaireManager::GENERATION_LANGUE_DEFAUT);
			$this->_aQCM['generation_format']									= DataHelper::get($aEpreuve,	'format_generation', 				DataHelper::DATA_TYPE_STR,		FormulaireManager::GENERATION_FORMAT_DEFAUT);
			$this->_aQCM['generation_separate']									= DataHelper::get($aEpreuve,	'separate_generation', 				DataHelper::DATA_TYPE_BOOL,		FormulaireManager::GENERATION_SEPARATE_DEFAUT);
			$this->_aQCM['generation_seed']										= DataHelper::get($aEpreuve,	'seed_generation', 					DataHelper::DATA_TYPE_ANY,		LatexFormManager::DOCUMENT_RANDOMISEED_DEFAUT);
			$this->_aQCM['generation_consignes']								= DataHelper::get($aEpreuve,	'consignes_generation', 			DataHelper::DATA_TYPE_TXT,		FormulaireManager::GENERATION_CONSIGNES_DEFAUT);
			$this->_aQCM['generation_exemplaires']								= DataHelper::get($aEpreuve,	'exemplaires_generation', 			DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::GENERATION_EXEMPLAIRES_DEFAUT);
			$this->_aQCM['generation_nom_epreuve']								= DataHelper::get($aEpreuve,	'nom_epreuve_generation', 			DataHelper::DATA_TYPE_STR,		FormulaireManager::GENERATION_NOM_DEFAUT);
			$this->_aQCM['generation_date_epreuve']								= DataHelper::get($aEpreuve,	'date_epreuve_generation', 			DataHelper::DATA_TYPE_DATE,		date(FormulaireManager::EPREUVE_DATE_FORMAT));
			$this->_aQCM['generation_code_candidat']							= DataHelper::get($aEpreuve,	'code_candidat_generation', 		DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::CANDIDATS_CODE_DEFAUT);
			$this->_aQCM['generation_cartouche_candidat']						= DataHelper::get($aEpreuve,	'cartouche_candidat_generation', 	DataHelper::DATA_TYPE_TXT,		FormulaireManager::CANDIDATS_CARTOUCHE_DEFAUT);

			// Chargement des données de l'épreuve (optionnelles)
			$this->_aQCM['epreuve_id']											= DataHelper::get($aEpreuve,	'id_epreuve',				 		DataHelper::DATA_TYPE_INT,		null);
			$this->_aQCM['epreuve_stage']										= DataHelper::get($aEpreuve,	'id_stage',					 		DataHelper::DATA_TYPE_INT,		null);
			$this->_aQCM['epreuve_stage_libelle']								= DataHelper::get($aEpreuve,	'libelle_stage_complet',		 		DataHelper::DATA_TYPE_STR,		'-');
			$this->_aQCM['epreuve_type']										= DataHelper::get($aEpreuve,	'type_epreuve',				 		DataHelper::DATA_TYPE_STR,		FormulaireManager::EPREUVE_TYPE_DEFAUT);
			$this->_aQCM['epreuve_date']										= DataHelper::get($aEpreuve,	'date_epreuve',						DataHelper::DATA_TYPE_DATE,		$this->_aQCM['generation_date_epreuve']);
			$this->_aQCM['epreuve_heure']										= DataHelper::get($aEpreuve,	'heure_epreuve',					DataHelper::DATA_TYPE_TIME,		FormulaireManager::EPREUVE_HEURE_DEFAUT);
			$this->_aQCM['epreuve_duree']										= DataHelper::get($aEpreuve,	'duree_epreuve',					DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::EPREUVE_DUREE_DEFAUT);
			$this->_aQCM['epreuve_libelle']										= DataHelper::get($aEpreuve,	'libelle_epreuve',					DataHelper::DATA_TYPE_STR,		$this->_aQCM['formulaire_titre']);
			$this->_aQCM['epreuve_liste_salles']								= DataHelper::get($aEpreuve,	'liste_salles_epreuve',				DataHelper::DATA_TYPE_ARRAY,	null);
			$this->_aQCM['epreuve_table_affectation']							= DataHelper::get($aEpreuve,	'table_affectation_epreuve',		DataHelper::DATA_TYPE_BOOL,		false);
			$this->_aQCM['epreuve_table_aleatoire']								= DataHelper::get($aEpreuve,	'table_aleatoire_epreuve',			DataHelper::DATA_TYPE_BOOL,		false);

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aQCM;
	}

	/******************************************************************************************************
	 * @todo TERMINER
	 ******************************************************************************************************/

	/**
	 * @brief	Finalisation d'un formulaire QCM.
	 *
	 * @li Commit final si l'enregistrement se déroule correctement.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire à finaliser.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function terminer($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Initialisation du résultat
		$bValide = false;

		// Requête UPDATE
		$aQuery	= array(
			"UPDATE formulaire SET",
			"validation_formulaire = :validation_formulaire",
			"WHERE id_formulaire = :id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':validation_formulaire'		=> self::VALIDATION_ATTENTE,
			':id_formulaire'				=> $nIdFormulaire
		);

		try {
			// Exécution de la requête
			$bValide = $this->executeSQL($aQuery, $aBind);

			// Validation des modifications
			$this->oSQLConnector->commit();
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/******************************************************************************************************
	 * @todo VALIDER
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `log_validation`.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logValidation($nIdFormulaire, $aQuery, $bFinalCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_formulaire = :id_formulaire,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'					=> $sTypeAction,
			':id_formulaire'				=> $nIdFormulaire,
			':id_utilisateur'				=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_validation', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Validation d'un formulaire QCM.
	 *
	 * @li Commit final si l'enregistrement se déroule correctement.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire à valider.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function valider($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Initialisation du résultat
		$bValide = false;

		// Requête UPDATE
		$aQuery	= array(
			"UPDATE formulaire SET",
			"id_valideur = :id_valideur,",
			"validation_formulaire = :validation_formulaire",
			"WHERE id_formulaire = :id_formulaire",
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_formulaire'				=> $nIdFormulaire,
			':validation_formulaire'		=> self::VALIDATION_REALISEE,
			':id_valideur'					=> $this->_idUtilisateur
		);

		try {
			// Exécution de la requête
			$bValide = $this->executeSQL($aQuery, $aBind);

			// Enregistrement de l'action dans les LOGs avec COMMIT
			$this->logValidation($nIdFormulaire, $aQuery);
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/******************************************************************************************************
	 * @todo RETIRER
	 ******************************************************************************************************/

	/**
	 * @brief	Retrait d'une question au formulaire.
	 *
	 * @li	Seule la relation dans la table `formulaire_question` est supprimée.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire à valider.
	 * @param	integer	$nIdQuestion		: Identifiant de la question dans le formulaire.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function retirerQuestion($nIdFormulaire, $nIdQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $nIdQuestion);

		// Requête DELETE
		$aQuery = array(
			"DELETE FROM formulaire_question",
			"WHERE id_formulaire = :id_formulaire",
			"AND id_question = :id_question"
		);

		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(
			":id_formulaire"				=> $nIdFormulaire,
			":id_question"					=> $nIdQuestion
		);

		// Ajout d'un suivit pour le debuggage
		ViewRender::addToDebug(DataHelper::queryToString($aQuery, $aBind));

		// Initialisation du résultat
		$bValide = false;
		try {
			// Exécution de la requête
			$bValide = $this->executeSQL($aQuery, $aBind);

			// Enregistrement de l'action dans les LOGs avec COMMIT
			$this->logQuestion($nIdQuestion, $aQuery);
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/******************************************************************************************************
	 * @todo SUPPRIMER
	 ******************************************************************************************************/

	/**
	 * @brief	Vérifie si la question est orpheline.
	 *
	 * @li	Vérification dans la relation `formulaire_question`.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la question à rechercher.
	 * @return	boolean, résultat de la vérification.
	 */
	protected function isOrphanQuestion($nIdQuestion) {
		try {
			// Requête SELECT
			$aQuery	= array(
				"SELECT * FROM formulaire_question",
				"WHERE id_question = :id_question",
			);

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind	= array(
				':id_question'				=> $nIdQuestion
			);

			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi si la liste est vide
		return empty($aResultat);
	}

	/**
	 * @brief	Suppression définitive d'une épreuve d'un formulaire.
	 *
	 * @li	Une génération ne peut être supprimée que s'il n'y a plus d'épreuve associée.
	 *
	 * @li	Suppression de l'entrée dans la table `epreuve` relative à la table `generation`.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire à supprimer.
	 * @param	integer	$bCommit			: (optionnel) Lance l'instruction COMMIT à la fin du traitement.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function supprimerEpreuveByIdFormulaire($nIdFormulaire, $bCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $bCommit);

		// Initialisation du résultat
		$bValide = false;

		// Force le mode transactionnel
		$this->beginTransaction();

		// Recherche de la relation `epreuve/generation`
		$aSelect= array(
			"SELECT * FROM epreuve",
			"LEFT JOIN generation USING(id_generation)",
			"WHERE id_formulaire = :id_formulaire"
		);

		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(
			":id_formulaire"				=> $nIdFormulaire
		);

		try {
			// Recherche de l'entrée dans la table `epreuve`
			$aSearch		= $this->executeSQL($aSelect, $aBind);

			var_dump($aSearch);

			// Fonctionnalité réalisée si une épreuve est présente
			if (DataHelper::isValidArray($aSearch)) {
				// Suppression de l'épreuve liée à l'entrée `generation`
				foreach ($aSearch as $nOccurrence => $aEpreuve) {
					// Exécution de la requête de suppression de l'épreuve
					$bValide = $this->supprimerEpreuveById($aEpreuve['id_epreuve'], false);
				}
			}

			// Validation des modifications
			if ($bCommit) {
				$this->oSQLConnector->commit();
			}
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aSelect, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/**
	 * @brief	Suppression définitive d'une épreuve.
	 *
	 * @li	À cause de la protection des données apportée par le moteur InnoDB associé aux clés étrangères,
	 * il est nécessaire de supprimer dans un premier temps la relation dans les tables `reservation` et `generation`.
	 *
	 * @param	integer	$nIdEpreuve			: Identifiant de l'épreuve à supprimer.
	 * @param	integer	$bCommit			: (optionnel) Lance l'instruction COMMIT à la fin du traitement.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function supprimerEpreuveById($nIdEpreuve, $bCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve, $bCommit);

		// Initialisation du résultat
		$bValide = false;

		// Force le mode transactionnel
		$this->beginTransaction();

		// Recherche de la relation `epreuve/generation`
		$aSelect			= array(
			"SELECT * FROM epreuve",
			"LEFT JOIN generation USING(id_generation)",
			"WHERE id_epreuve = :id_epreuve"
		);

		// Suppression dans la table `reservation`
		$aReservation		= array(
			"DELETE FROM reservation",
			"WHERE id_epreuve = :id_epreuve"
		);

		// Suppression dans la table `epreuve`
		$aEpreuve			= array(
			"DELETE FROM epreuve",
			"WHERE id_epreuve = :id_epreuve"
		);

		// Construction du tableau associatif de l'étiquette de l'épreuve
		$aBindEpreuve		= array(
			":id_epreuve"					=> $nIdEpreuve
		);

		// Suppression dans la table `generation`
		$aGeneration		= array(
			"DELETE FROM generation",
			"WHERE id_generation = :id_generation"
		);
		// Préparation du tableau associatif de l'étiquette de la génération
		$aBindGeneration	= array();

		try {
			// Recherche de l'entrée dans la table `epreuve`
			$aSearch		= $this->executeSQL($aSelect, $aBindEpreuve, 0);

			// Exécution de la requête de suppression dans les réservations
			$this->executeSQL($aReservation, $aBindEpreuve);

			// Exécution de la requête de suppression de l'épreuve
			$bValide		= $this->executeSQL($aEpreuve, $aBindEpreuve);
			// Enregistrement de l'action dans les LOGs SANS COMMIT
			$this->logEpreuve($nIdEpreuve, $aEpreuve, false);

			// Fonctionnalité réalisée si une génération est présente
			if (DataHelper::isValidArray($aSearch)) {
				// Renseignement du tableau associatif de l'étiquette de la génération
				$aBindGeneration[":id_generation"] = $aSearch['id_generation'];

				// Exécution de la requête de suppression dans la table `generation`
				$bValide	= $this->executeSQL($aGeneration, $aBindGeneration);
				// Enregistrement de l'action dans les LOGs SANS COMMIT
				$this->logGeneration($aSearch['id_generation'], $aGeneration, false);
			}

			// Validation des modifications
			if ($bCommit) {
				$this->oSQLConnector->commit();
			}
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException('EQueryDelete', DataHelper::queryToString($aEpreuve, $aBindEpreuve));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/**
	 * @brief	Suppression définitive d'une réponse dans une question.
	 *
	 * @li	À cause de la protection des données apportée par le moteur InnoDB associé aux clés étrangères,
	 * il est nécessaire de supprimer dans un premier temps la relation dans la table `question_reponse`.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la question dans le formulaire.
	 * @param	integer	$nIdReponse			: Identifiant de la réponse à supprimer.
	 * @param	integer	$bCommit			: (optionnel) Lance l'instruction COMMIT à la fin du traitement.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	protected function supprimerReponseById($nIdQuestion, $nIdReponse, $bCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $nIdReponse, $bCommit);

		// Initialisation du résultat
		$bValide = false;

		// Force le mode transactionnel
		$this->beginTransaction();

		// ================================================================================
		// SUPPRESSION DE LA RELATION QUESTION - REPONSE
		// ================================================================================
		// Requête DELETE
		$aQuery = array(
			"DELETE FROM question_reponse",
			"WHERE id_question = :id_question",
			"AND id_reponse = :id_reponse"
		);

		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(
			":id_question"					=> $nIdQuestion,
			":id_reponse"					=> $nIdReponse
		);

		try {
			// Fonctionnalité réalisée si les identifiants sont renseignés
			if (!empty($nIdQuestion) && !empty($nIdReponse)) {
				// Exécution de la requête
				$bValide = $this->executeSQL($aQuery, $aBind);

				// ================================================================================
				// SUPPRESSION DE LA RELATION QUESTION - REPONSE
				// ================================================================================
				if ($bValide) {
					// Requête DELETE
					$aQuery = array(
						"DELETE FROM reponse",
						"WHERE id_reponse = :id_reponse"
					);

					// Construction du tableau associatif de l'étiquette du formulaire
					$aBind	= array(
						":id_reponse"		=> $nIdReponse
					);

					// Exécution de la requête
					$bValide = $this->executeSQL($aQuery, $aBind);

					// Enregistrement de l'action dans les LOGs avec COMMIT
					$this->logReponse($nIdReponse, $aQuery, $bCommit);
				}
			}
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}

	/**
	 * @brief	Suppression définitive d'une question vide.
	 *
	 * @li	Une question ne peut être supprimée que s'il n'y a plus de réponse rattachée.
	 *
	 * @li	À cause de la protection des données apportée par le moteur InnoDB associé aux clés étrangères,
	 * il est nécessaire de supprimer dans un premier temps la relation dans la table `formulaire_question`.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire.
	 * @param	integer	$nIdQuestion		: Identifiant de la question à supprimer.
	 * @param	integer	$bCommit			: (optionnel) Lance l'instruction COMMIT à la fin du traitement.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	protected function supprimerQuestionById($nIdFormulaire, $nIdQuestion, $bCommit = true) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire, $nIdQuestion, $bCommit);

		// Initialisation du résultat
		$bValide = false;

		// ================================================================================
		// SUPPRESSION DE LA RELATION FORMULAIRE - QUESTION
		// ================================================================================
		// Requête DELETE
		$aQuery = array(
			"DELETE FROM formulaire_question",
			"WHERE id_formulaire = :id_formulaire",
			"AND id_question = :id_question"
		);

		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(
			":id_formulaire"				=> $nIdFormulaire,
			":id_question"					=> $nIdQuestion
		);

		try {
			// Fonctionnalité réalisée si les identifiants sont renseignés
			if (!empty($nIdFormulaire) && !empty($nIdQuestion)) {
				// Exécution de la relation dans la table `formulaire_question`
				$bValide = $this->executeSQL($aQuery, $aBind);

				// ================================================================================
				// SUPPRESSION DE LA QUESTION
				// ================================================================================
				// Requête DELETE
				$aQuery = array(
					"DELETE FROM question",
					"WHERE id_question = :id_question"
				);

				// Construction du tableau associatif de l'étiquette du formulaire
				$aBind	= array(
					":id_question"			=> $nIdQuestion
				);

				// Exécution de la requête
				$bValide = $this->executeSQL($aQuery, $aBind);

				// Enregistrement de l'action dans les LOGs avec COMMIT
				$this->logQuestion($nIdQuestion, $aQuery, $bCommit);
			}
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}


	/**
	 * @brief	Suppression définitive d'un formulaire.
	 *
	 * @li	À cause de la protection des données apportée par le moteur InnoDB associé aux clés étrangères,
	 * il est nécessaire de supprimer dans un premier temps la relation dans la table `formulaire_question`.
	 *
	 * @li	Si chaque question est orpheline, elle seront chacune supprimées tour à tour.
	 *
	 * @param	integer	$nIdFormulaire		: Identifiant du formulaire à supprimer.
	 * @return	boolean, résultat de l'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function supprimerFormulaireById($nIdFormulaire) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdFormulaire);

		// Initialisation du résultat
		$bValide = false;

		// Force le mode transactionnel
		$this->beginTransaction();

		$aQuery = array(
			"DELETE FROM formulaire",
			"WHERE id_formulaire = :id_formulaire"
		);

		// Construction du tableau associatif de l'étiquette du formulaire
		$aBind	= array(
			":id_formulaire"				=> $nIdFormulaire
		);

		// Récupération de la liste des questions associées au formulaire
		$aListeQuestions = $this->findQuestionsByIdFormulaire($nIdFormulaire, 'id_question');

		try {
			// Suppression de la relation dans la table `generation`
			$this->supprimerEpreuveByIdFormulaire($nIdFormulaire);

			// Suppression de la relation dans la table `formulaire_question`
			$this->supprimerFormulaireQuestion($nIdFormulaire);

			// Fonctionnalité réalisée pour chaque question du formulaire
			foreach ($aListeQuestions as $aQuestion) {
				// Récupération de l'identifiant de la question
				$nIdQuestion = $aQuestion['id_question'];

				// Fonctionnalité réalisée si la question est orpheline
				if ($this->isOrphanQuestion($nIdQuestion)) {
					// Récupération de la liste des réponses à la question
					$aListeReponses = $this->findReponsesByIdQuestion($nIdQuestion, 'id_reponse');

					// Fonctionnalité réalisée pour chaque réponse à la question
					foreach ($aListeReponses as $aReponse) {
						// Récupération de l'identifiant de la réponse
						$nIdReponse = $aReponse['id_reponse'];

						// Suppression de la question sans COMMIT
						$this->supprimerReponseById($nIdQuestion, $nIdReponse, false);
					}

					// Suppression de la question orpheline une fois vide
					$this->supprimerQuestionById($nIdFormulaire, $nIdQuestion, false);
				}
			}

			// Exécution de la requête de suppression du formulaire
			$bValide = $this->executeSQL($aQuery, $aBind);

			// Enregistrement de l'action dans les LOGs avec COMMIT
			$this->logFormulaire($nIdFormulaire, $aQuery);
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bValide;
	}
}
