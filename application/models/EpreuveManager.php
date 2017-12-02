<?php
/**
 * @brief	Classe de gestion des contrôles QCM.
 *
 * L'ensemble du formulaire est parcouru afin de générer un tableau associatif entre
 * les champs du formulaire et ceux de la base de données.
 *
 * @li	Par convention d'écriture, les méthodes
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
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EpreuveManager extends FormulaireManager {

	/**
	 * @brief	Statut de la programmation d'une épreuve.
	 *
	 * Nom du champ SQL calculé afin de déterminer l'état d'une épreuve, selon 3 états
	 * 		- `statut_programmation`	= -1	: la date de validité est dépassée (trop tard !) ;
	 * 		- `statut_programmation`	= 0		: la date de validité permet au candidat de s'inscrire ;
	 * 		- `statut_programmation`	= +1	: la date de validité n'est pas encore atteinte (patience !) ;
	 *
	 * @var		string
	 */
	const STATUT_PROGRAMMATION	= 'statut_programmation';

	/**
	 * @brief	Champ relatif à une question sans aucune bonne réponse.
	 * @var		string
	 */
	const AUCUNE_REPONSE		= 'X';

	/******************************************************************************************************
	 * @todo RECHERCHES
	 ******************************************************************************************************/


	/**
	 * @brief	Récupère l'état de la programmation d'une épreuve.
	 *
	 * @li	La requête SQL injecte un champ calculé à 3 états
	 * 			- `statut_programmation`	= -1	: la date de validité est dépassée (trop tard !) ;
	 * 			- `statut_programmation`	= 0		: la date de validité permet au candidat de s'inscrire ;
	 * 			- `statut_programmation`	= +1	: la date de validité n'est pas encore atteinte (patience !).
	 *
	 * @param	integer	$nIdEpreuve		: identifiant de l'épreuve.
	 * @return	integer, état de la programmation.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getProgrammationSatementByIdEpreuve($nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdEpreuve);

		// Requête SELECT
		$aQuery	= array(
			"SELECT id_epreuve,",
			"UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") AS debut_epreuve,",
			"UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") + 60 * duree_epreuve AS fin_epreuve,",
			"UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant,",
			"IF('maintenant' BETWEEN 'debut_epreuve' AND 'fin_epreuve', 0, IF('maintenant' < 'debut_epreuve', 1, -1)) AS 'statut_programmation'",
			"FROM epreuve",
			"WHERE id_epreuve = :id_epreuve"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_epreuve'			=> $nIdEpreuve
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery[]				= "  AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

			// Ajout des étiquette de la clause WHERE
			$aBind[':borne_gauche']	= $this->_borneGauche;
			$aBind[':borne_droite']	= $this->_borneDroite;
		}

		try {
			// Récupération de la liste des formulaires
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de la liste
		return $aResultat['statut_programmation'];
	}

	/**
	 * @brief	Recherche de toutes les épreuves modifiables par un candidat.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 *
	 * @li	La requête SQL injecte un champ calculé à 3 états
	 * 			- `statut_programmation`	= -1	: la date de validité est dépassée (trop tard !) ;
	 * 			- `statut_programmation`	= 0		: la date de validité permet au candidat de s'inscrire ;
	 * 			- `statut_programmation`	= +1	: la date de validité n'est pas encore atteinte (patience !) ;
	 *
	 * @param	integer	$nIdCandidat		: identifiant du candidat.
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllEpreuvesModifiablesByIdCandidat($nIdCandidat, $bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
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
			6	=> "UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant,",
			7	=> "IF('maintenant' BETWEEN 'debut_epreuve' AND 'fin_epreuve', 0, IF('maintenant' < 'debut_epreuve', 1, -1)) AS 'statut_programmation'",
			8	=> "FROM formulaire",
			9	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			10	=> "INNER JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			11	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			12	=> "INNER JOIN generation USING(id_formulaire)",
			13	=> "INNER JOIN epreuve USING(id_generation)",
			14	=> "LEFT  JOIN controle USING(id_epreuve)",
			15	=> "INNER JOIN stage USING(id_stage)",
			16	=> "INNER JOIN stage_candidat USING(id_stage)",
			17	=> "INNER JOIN candidat ON(stage_candidat.id_candidat = candidat.id_candidat)",
			18	=> "WHERE candidat.id_candidat = :id_candidat",
			19	=> "  AND (modifiable_controle IS NULL OR modifiable_controle = 1)",
			'X'	=> null,
			20	=> "GROUP BY id_formulaire"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_candidat'			=> $nIdCandidat
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "  AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

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
	 * @brief	Recherche de toutes les épreuves corrigées d'un candidat.
	 *
	 * @li	Possibilité de limiter les formulaires selon le groupe d'appartenance de l'utilisateur connecté.
	 *
	 * @li	La requête SQL injecte un champ calculé à 3 états
	 * 			- `statut_programmation`	= -1	: la date de validité est dépassée (trop tard !) ;
	 * 			- `statut_programmation`	= 0		: la date de validité permet au candidat de s'inscrire ;
	 * 			- `statut_programmation`	= +1	: la date de validité n'est pas encore atteinte (patience !) ;
	 *
	 * @param	integer	$nIdCandidat		: identifiant du candidat.
	 * @param	boolean	$bGroupAccess		: (optionnel) Filtre sur les groupes du rédacteur.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllEpreuvesCorrectionByIdCandidat($nIdCandidat, $bGroupAccess = self::ACCESS_GROUP_BY_DEFAULT) {
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
			6	=> "UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant,",
			7	=> "IF('maintenant' BETWEEN 'debut_epreuve' AND 'fin_epreuve', 0, IF('maintenant' < 'debut_epreuve', 1, -1)) AS 'statut_programmation'",
			8	=> "FROM formulaire",
			9	=> "INNER JOIN utilisateur AS redacteur ON(redacteur.id_utilisateur = id_redacteur)",
			10	=> "INNER JOIN utilisateur AS valideur ON(valideur.id_utilisateur = id_valideur)",
			11	=> "INNER JOIN groupe ON(redacteur.id_groupe = groupe.id_groupe)",
			12	=> "INNER JOIN generation USING(id_formulaire)",
			13	=> "INNER JOIN epreuve USING(id_generation)",
			14	=> "INNER JOIN controle USING(id_epreuve)",
			15	=> "LEFT  JOIN controle_reponse_candidat USING(id_controle)",
			16	=> "INNER JOIN stage USING(id_stage)",
			17	=> "INNER JOIN stage_candidat USING(id_stage)",
			18	=> "INNER JOIN candidat ON(stage_candidat.id_candidat = candidat.id_candidat)",
			19	=> "WHERE candidat.id_candidat = :id_candidat",
			20	=> "  AND (modifiable_controle = 0)",
			'X'	=> null,
			21	=> "GROUP BY id_controle"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
				':id_candidat'		=> $nIdCandidat
		);

		// Fonctionnalité réalisée si l'accès aux formulaires est limité au groupe d'utilisateurs du rédacteur
		if ($bGroupAccess) {
			// Ajout d'une clause WHERE selon les bornes GAUCHE / DROITE
			$aQuery['X']			= "  AND borne_gauche BETWEEN :borne_gauche AND :borne_droite AND borne_droite BETWEEN :borne_gauche AND :borne_droite";

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
	 * @brief	Recherche du contrôle pour l'épreuve d'un candidat.
	 *
	 * @param	integer	$nIdCandidat		: identifiant du candidat.
	 * @param	integer	$nIdEpreuve			: identifiant de l'épreuve.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getControleByCandidatEpreuve($nIdCandidat, $nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdCandidat, $nIdEpreuve);

		// Requête SELECT
		$aQuery	= array(
			"SELECT *,",
			self::LIBELLE_CANDIDAT . " AS libelle_candidat,",
			self::DATETIME_EPREUVE . " AS datetime_epreuve,",
			"UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") AS debut_epreuve,",
			"UNIX_TIMESTAMP(" . self::DATETIME_EPREUVE . ") + 60 * duree_epreuve AS fin_epreuve,",
			"UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AS maintenant",
			"FROM controle",
			"INNER JOIN candidat USING(id_candidat)",
			"INNER JOIN grade USING(id_grade)",
			"INNER JOIN epreuve USING(id_epreuve)",
			"WHERE id_candidat = :id_candidat",
			"  AND id_epreuve = :id_epreuve",
			"ORDER BY id_controle DESC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_candidat'			=> $nIdCandidat,
			':id_epreuve'			=> $nIdEpreuve
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
	 * @brief	Recherche si une relation CONTROLE / QUESTION / CONTROLE_REPONSE_CANDIDAT existe.
	 *
	 * @param	integer	$nIdControle		: Identifiant du contrôle en base.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findControleReponseCandidatByIdControle($nIdControle) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdControle);

		// Requête SELECT
		$aQuery	= array(
			"SELECT *",
			"FROM controle_reponse_candidat",
			"WHERE id_controle = :id_controle",
			"ORDER BY ordre_question ASC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_controle'			=> $nIdControle,
		);

		try {
			// Exécution de la requête sous forme de tableau
			$aResultat = $this->executeSQL($aQuery, $aBind);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Recherche si une relation CONTROLE / QUESTION / CONTROLE_REPONSE_CANDIDAT existe.
	 *
	 * @param	integer	$nIdControle		: Identifiant du contrôle en base.
	 * @param	integer	$nIdQuestion		: Identifiant de la question en base.
	 * @return	integer, identifiant de la table `controle_reponse_candidat`.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getIdControleReponseCandidat($nIdControle, $nIdQuestion) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdControle, $nIdQuestion);

		// Requête SELECT
		$aQuery	= array(
			"SELECT *",
			"FROM controle_reponse_candidat",
			"WHERE id_controle = :id_controle",
			"  AND id_question = :id_question",
			"ORDER BY ordre_question ASC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind = array(
			':id_controle'			=> $nIdControle,
			':id_question'			=> $nIdQuestion
		);

		try {
			// Exécution de la requête et récupération du premier résultat
			$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi de l'identifiant de la réponse au contrôle si l'épreuve est valide
		return DataHelper::get($aResultat, 'id_controle_reponse_candidat', DataHelper::DATA_TYPE_INT, null);
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
	public function chargerControle($aQCM, $nIdControle) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $aQCM, $nIdControle);

		try {
			// Récupération de la liste des questions associées au formulaire
			$aListeQuestions	= $this->findControleReponseCandidatByIdControle($nIdControle);

			// Parcours de la liste des questions
			foreach ($aListeQuestions as $nOccurrence => $aQuestion) {
				// Récupération de l'identifiant de la question
				$nIdQuestion	= $aQuestion['id_question'];

				// Recherche de l'occurrence de la question dans le formulaire
				$nQuestion		= array_search($nIdQuestion, $aQCM['question_id']);

				// Fonctionnalité réalisée si les données du candidat ne sont pas déjà chargées
				if (!isset($aQCM["controle_candidat_libre_reponse"][$nQuestion])) {
					// Chargement du formulaire à partir de la base de données
					$aQCM["controle_candidat_libre_reponse"][$nQuestion] = DataHelper::get($aQuestion, "libre_reponse_candidat", DataHelper::DATA_TYPE_TXT);
				}

				// Fonctionnalité réalisée si les données du candidat ne sont pas déjà chargées
				if (!isset($aQCM["controle_candidat_liste_reponses"][$nQuestion])) {
					// Récupération des questions sélectionnées
					$sEnsembleReponses	= DataHelper::get($aQuestion, "liste_reponses_candidat", DataHelper::DATA_TYPE_TXT);

					// Fonctionnalité réalisée si au moins une réponse est sélectionnée
					if ($sEnsembleReponses != self::AUCUNE_REPONSE) {
						// Récupération de la liste des questions sélectionnées sous forme de tableau
						$aListeReponses = explode(DataHelper::ARRAY_SEPARATOR, $sEnsembleReponses);

						// Parcours de la liste des réponses
						foreach ($aListeReponses as $nIdReponse) {
							// Recherche de l'occurrence de la question dans le formulaire
							$nReponse = array_search($nIdReponse, $aQCM['reponse_id'][$nQuestion]);

							$aQCM["controle_candidat_liste_reponses"][$nQuestion][$nReponse] = true;
						}
					} else {
						// Sélection de la dernière occurrence
						$aQCM["controle_candidat_liste_reponses"][$nQuestion][self::AUCUNE_REPONSE] = true;
					}
				}
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $aQCM;
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `controle`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `controle`.
	 *
	 * @param	integer	$nIdControle		: Identifiant du controle en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logControle($nIdControle, $aQuery, $bFinalCommit = false) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdControle, $aQuery, $bFinalCommit);

		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"id_controle = :id_controle,",
			"id_candidat = :id_candidat"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'			=> $sTypeAction,
			':id_controle'			=> $nIdControle,
			':id_candidat'			=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_controle', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Initialise le contrôle d'un candidat.
	 *
	 * @li	Recherche dans un premier temps si un contrôle est déjà en cours.
	 *
	 * @li	Vérifie le statut de la programation avec un champ calculé à 3 états
	 * 			- `statut_programmation`	= -1	: la date de validité est dépassée (trop tard !) ;
	 * 			- `statut_programmation`	= 0		: la date de validité permet au candidat de s'inscrire ;
	 * 			- `statut_programmation`	= +1	: la date de validité n'est pas encore atteinte (patience !).
	 *
	 * @param	string	$nIdCandidat		: Identifiant du candidat.
	 * @param	integer	$nIdEpreuve			: Identifiant de l'épreuve.
	 * @return	integer, identifiant de la table `controle`.
	 */
	public function initControleByCandidatEpreuve($nIdCandidat, $nIdEpreuve) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdCandidat, $nIdEpreuve);

		// Initialisation de l'idendifiant du contrôle
		$nIdControle		= false;

		// Recherche du contrôle en cours pour le candidat
		$aControle			= $this->getControleByCandidatEpreuve($nIdCandidat, $nIdEpreuve);

		// Fonctionnalité désactivée en MODE_DEBUG
		if (!defined('MODE_DEBUG') || !(bool) MODE_DEBUG) {
			// Récupération de l'état de la programmation
			$nStatutEpreuve	= $this->getProgrammationSatementByIdEpreuve($nIdEpreuve);

			// Contrôle de la validité de la programmation
			if ($nStatutEpreuve > 0) {
				throw new ApplicationException("La programmation ne permet pas encore l'accès à l'épreuve !");
			} elseif ($nStatutEpreuve < 0) {
				throw new ApplicationException("La programmation ne permet plus l'accès à l'épreuve !");
			}
		}

		// Fonctionnalité réalisée si aucun contrôle n'a été trouvé
		if (!DataHelper::isValidArray($aControle)) {
			// Requête INSERT
			$aInsertQuery	= array(
				"INSERT INTO controle SET",
				"id_candidat = :id_candidat,",
				"id_epreuve = :id_epreuve,",
				"date_debut_controle = :date_debut"
			);

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind			= array(
				':id_candidat'		=> $nIdCandidat,
				':id_epreuve'		=> $nIdEpreuve,
				':date_debut'		=> DataHelper::timesampToMyDatetime()
			);

			// Exécution de la requête INSERT
			$nIdControle	= $this->executeSQL($aInsertQuery, $aBind);

			// Enregistrement de l'action dans les LOGs
			$this->logControle($nIdControle, $aInsertQuery, true);
		} else {
			// Récupération de l'identifiant du contrôle
			$nIdControle	= DataHelper::get($aControle, 'id_controle', DataHelper::DATA_TYPE_INT);
		}

		// Renvoi de l'identifiant
		return $nIdControle;
	}

	/**
	 * @brief	Finalise le contrôle d'un candidat.
	 *
	 * @param	integer	$nIdControle		: Identifiant du contrôle.
	 * @return	integer, identifiant de la table `controle`.
	 */
	public function finalizeControleById($nIdControle) {
		// Requête UPDATE
		$aUpdateQuery		= array(
			"UPDATE controle SET",
			"modifiable_controle = :modifiable_controle",
			"WHERE id_controle = :id_controle"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind				= array(
			':modifiable_controle'	=> 0,
			':id_controle'			=> $nIdControle,
		);

		// Exécution de la requête INSERT
		$this->executeSQL($aUpdateQuery, $aBind);

		// Enregistrement de l'action dans les LOGs
		$this->logControle($nIdControle, $aUpdateQuery, true);
	}

	/******************************************************************************************************
	 * @todo ENREGISTREMENT TABLE `controle_reponse_candidat`
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement de la réponse selon la relation CONTROLE / QUESTION / CANDIDAT.
	 *
	 * Recherche l'identifiant de la relation entre les tables `controle`, `question` et `controle_reponse_candidat`.
	 *
	 * @param	integer	$nQuestion			: Occurrence de la question dans $_aQCM.
	 * @param	integer	$nIdControle		: Identifiant du contrôle en base de données.
	 * @return	integer, identifiant de la table `controle_reponse_candidat`.
	 * @throws	ApplicationException gérée par la méthode enregistrerControle() en amont.
	 */
	protected function enregistrerReponseControle($nQuestion, $nIdControle) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nQuestion, $nIdControle);

		// Initialisation de l'identifiant de la table `controle_reponse_candidat`
		$nIdControleReponseCandidat				= null;
		// Identifiant de la question
		$nIdQuestion							= DataHelper::get($this->_aQCM['question_id'],						$nQuestion,	DataHelper::DATA_TYPE_INT);
		// Liste des réponses sélectionnées
		$aListeReponseChecked					= DataHelper::get($this->_aQCM['controle_candidat_liste_reponses'],	$nQuestion,	DataHelper::DATA_TYPE_ARRAY);

		// Initialisation de la liste des identifiants de réponse sélectionnées
		$aListeReponses							= array(self::AUCUNE_REPONSE);
		$sLibreReponse							= null;

		// Fonctionnalité réalisée en cas de question libre
		if (isset($this->_aQCM['controle_candidat_libre_reponse'][$nQuestion])) {
			// Identifiant de la réponse
			$sLibreReponse						= DataHelper::get($this->_aQCM['controle_candidat_libre_reponse'],	$nQuestion,	DataHelper::DATA_TYPE_MYTXT,	null,	true);
		} elseif (isset($this->_aQCM['reponse_id'][$nQuestion])) {
			$aListeReponses						= array();
			foreach ($aListeReponseChecked as $nReponse => $bChecked) {
				// Récupération de l'identifiant de la réponse à la question
				$aListeReponses[]				= DataHelper::get($this->_aQCM['reponse_id'][$nQuestion],			$nReponse,	DataHelper::DATA_TYPE_STR,		self::AUCUNE_REPONSE);
			}
		}

		// Fonctionnalité réalisée si les identifiants sont valides
		if (!empty($nIdQuestion) && !empty($nIdControle)) {
			// Construction du tableau associatif des champs à enregistrer
			$aSet	= array(
				"ordre_question = :ordre_question,",
				"id_controle = :id_controle,",
				"id_question = :id_question,",
				"libre_reponse_candidat = :libre_reponse_candidat,",
				"liste_reponses_candidat = :liste_reponses_candidat"
			);

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind	= array(
				":ordre_question"				=> $nQuestion,
				":id_controle"					=> $nIdControle,
				":id_question"					=> $nIdQuestion,
				":libre_reponse_candidat"		=> $sLibreReponse,
				":liste_reponses_candidat"		=> implode(DataHelper::ARRAY_SEPARATOR, $aListeReponses)
			);

			// Contrôle de l'existence d'une relation entre les tables `controle`, `question` et `controle_reponse_candidat`.
			$nIdControleReponseCandidat			= $this->getIdControleReponseCandidat($nIdControle, $nIdQuestion);
			if (!empty($nIdControleReponseCandidat)) {
				// Requête UPDATE
				$aInitQuery						= array("UPDATE controle_reponse_candidat SET");
				$aSet[]							= "WHERE id_controle_reponse_candidat = :id_controle_reponse_candidat";
				$aBind[':id_controle_reponse_candidat']	= $nIdControleReponseCandidat;

				// Exécution de la requête UPDATE
				$this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			} else {
				// Requête INSERT
				$aInitQuery						= array("INSERT INTO controle_reponse_candidat SET");

				// Exécution de la requête INSERT
				$nIdControleReponseCandidat		= $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			}
		}

		// Renvoi de l'identifiant
		return $nIdControleReponseCandidat;
	}

	/**
	 * @brief	Modification du résultat de la réponse d'un candidat.
	 *
	 * @li	La relation entre les tables `controle`, `question` et `controle_reponse_candidat` existe déjà.
	 *
	 * @li	Commit final si toutes les phases d'enregistrement se déroulent correctement, lors de l'enregistrement du LOG.
	 *
	 * @param	integer	$nIdQuestion		: Identifiant de la question en base de données.
	 * @param	integer	$nIdControle		: Identifiant du contrôle en base de données.
	 * @param	float	$fResultatReponse	: Résultat de la réponse à la question.
	 * @return	array, tableau contenant l'ensemble des données du formulaire QCM.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrerResultatReponseControle($nIdQuestion, $nIdControle, $fResultatReponse = null) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $nIdQuestion, $nIdControle, $fResultatReponse);

		// Force le mode transactionnel
		$this->beginTransaction();

		// Requête UPDATE
		$aUpdate	= array(
			"UPDATE controle_reponse_candidat",
			"SET resultat_reponse_candidat = :resultat_reponse_candidat",
			"WHERE id_controle = :id_controle AND id_question = :id_question"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind		= array(
			":id_controle"					=> $nIdControle,
			":id_question"					=> $nIdQuestion,
			":resultat_reponse_candidat"	=> is_null($fResultatReponse) ? "NULL" : $fResultatReponse
		);

		// Exécution de la requête UPDATE
		$this->executeSQL($aUpdate, $aBind);
	}

	/******************************************************************************************************
	 * @todo ENREGISTRER
	 ******************************************************************************************************/

	/**
	 * @brief	Parcours du formulaire afin d'enregistrer chaque partie.
	 *
	 * @li	Si l'identifiant existe déjà en base, l'enregistrement sera redirigé vers une méthode UPDATE,
	 * sinon, vers une méthode INSERT.
	 *
	 * @li	Commit final si toutes les phases d'enregistrement se déroulent correctement.
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
	 * @param	string	$nIdControle		: Identifiant du contrôle.
	 * @return	array, tableau contenant l'ensemble des données du formulaire QCM.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrerControle(array $aQCM, $nIdControle) {
		// Ajout d'un suivit pour le debuggage
		$this->debug(__METHOD__, $aQCM);

		// Initialisation du formulaire
		$this->_aQCM = $aQCM;

		// Force le mode transactionnel
		$this->beginTransaction();

		try {
			// Parcours de l'ensemble des questions
			foreach ($this->_aQCM['question_id'] as $nQuestion => $nIdQuestion) {
				// Enregistrement dans la table `controle_reponse_candidat`
				$this->enregistrerReponseControle($nQuestion, $nIdControle);
			}

			// Validation des modifications
			$this->oSQLConnector->commit();

			// Affichage d'un message de confirmation
			ViewRender::setMessageSuccess("Enregistrement réalisé avec succès !");
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();

			// Affichage d'un message d'erreur
			ViewRender::setMessageError("Erreur rencontrée lors de l'enregistrement...");

			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aQCM;
	}

}
