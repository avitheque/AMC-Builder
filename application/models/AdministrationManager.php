<?php
/**
 * Classe de gestion administrative des candidats, des stages et des utilisateurs.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * Étend la classe d'accès à la base de données MySQLManager.
 * @see			{ROOT_PATH}/libraries/models/MySQLManager.php
 *
 * @name		CandidatManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 * @see			{ROOT_PATH}/libraries/models/MySQLManager.php
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class AdministrationManager extends MySQLManager {

	const	ID_CANDIDAT						= 'ID_CANDIDAT';
	const	ID_STAGE						= 'ID_STAGE';
	const	ID_UTILISATEUR					= 'ID_UTILISATEUR';

	const	TYPE_CANDIDAT					= "candidat";
	const	TYPE_STAGE						= "stage";
	const	TYPE_UTILISATEUR				= "utilisateur";

	/**
	 * @brief	Constantes des champs des formulaires de gestion.
	 * @var		string
	 */
	const	CANDIDAT_ID_MAXLENGTH			= 32;
	const	CANDIDAT_NOM_MAXLENGTH			= 50;
	const	CANDIDAT_PRENOM_MAXLENGTH		= 50;
	const	CANDIDAT_UNITE_MAXLENGTH		= 50;

	const	DATE_MAX_LENGTH					= 10;
	const	REFERENTIEL_LIBELLE_MAXLENGTH	= 50;

	const	STAGE_LIBELLE_MAXLENGTH			= 50;
	const	STAGE_DATE_MAXLENGTH			= 10;

	const	UTILISATEUR_ID_MAXLENGTH		= 32;
	const	UTILISATEUR_LOGIN_MAXLENGTH		= 25;
	const	UTILISATEUR_PASSWORD_MAXLENGTH	= 32;
	const	UTILISATEUR_NOM_MAXLENGTH		= 50;
	const	UTILISATEUR_PRENOM_MAXLENGTH	= 50;

	const	CAPACITE_MAX_LENGTH				= 3;
	const	REFERENTIEL_CAPACITE_DEFAUT		= 20;

	const	LIBELLE_CANDIDAT				= "CONCAT(libelle_court_grade, ' ', nom_candidat, ' ', prenom_candidat, ' (', id_candidat, ')')";
	const	LIBELLE_UTILISATEUR				= "CONCAT(libelle_court_grade, ' ', nom_utilisateur, ' ', prenom_utilisateur, ' (', id_utilisateur, ')')";

	/**
	 * @brief	Tableau de champs du formulaire HTML.
	 * @var		array
	 */
	private $_aFormulaire					= array();

	/**********************************************************************************************
	 * @todo	LISTE FILTRES
	 **********************************************************************************************/

	/**
	 *
	 * @param	string		$sType			: type du référentiel à récupérer.
	 */
	public function findFiltreColumnns($sType = null) {
		switch ($sType) {
			case self::TYPE_CANDIDAT:
				return array(
					'libelle_grade'		=> "Grade",
					'candidat_nom'		=> "Nom",
					'candidat_prenom'	=> "Prénom",
					'candidat_unite'	=> "Affectation",
					'candidat_id'		=> "Identifiant"
				);
				break;

			default:
				return array();
				break;
		}
	}

	/**********************************************************************************************
	 * @todo	LISTE DES RÉFÉRENTIELS
	 **********************************************************************************************/

	/**
	 * @brief	Récupèration de la liste des référentiels de l'application.
	 *
	 * Renvoi la liste des référentiels
	 * @code
	 * return array(
	 *		'nom_de_la_table'	=> array('id', 'libelle_table', 'date_debut_table', 'date_fin_table'),
	 * );
	 * @endcode
	 *
	 * @return	array
	 */
	public function findAllReferentiels() {
		// Initialisation du résultat
		$aListeReferentiels = array();

		// Parcours de la liste du référentiel administrable
		foreach (ReferentielManager::$REF_TABLE_LIBELLE as $sTable => $sLibelle) {
			try {
				// Recherche du nombre d'entrées du référentiel
				$sQuery = "SELECT COUNT(*) FROM $sTable WHERE id_$sTable > 0";

				// Fonctionnalité réalisée si l'utilisateur n'est pas [Administrateur]
				if (!$this->_oAuth->isProfil(AclManager::ID_PROFIL_ADMINISTRATOR, false)) {
					// Restriction sur les dates
					$sQuery .= " AND (date_fin_" . $sTable . " >= CURRENT_DATE() OR date_fin_" . $sTable . " IS NULL)";
				}

				// Exécution de la requête
				$aData	= $this->selectSQL($sQuery, 0);
				$nCount	= $aData['COUNT(*)'];

				// Ajout du référentiel au tableau
				$aListeReferentiels[] = array(
					'table_referentiel'		=> $sTable,
					'libelle_referentiel'	=> $sLibelle,
					'count_referentiel'		=> $nCount
				);
			} catch (Exception $e) {
				throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
			}
		}

		// Renvoi du résultat
		return $aListeReferentiels;
	}

	/**
	 * @brief	Récupération de l'identifiant du grade pas son libellé.
	 *
	 * @param	string		$sLabel			: libellé du grade.
	 * @return	integer, identifiant du grade.
	 */
	public function getIdGradeByLabel($sLabel) {
		// Initialisation de résultat
		$nIdGrade = 0;

		// Requête SELECT
		$aQuery = array(
			"SELECT	id_grade",
			"FROM grade",
			"WHERE UPPER(libelle_grade) = :libelle_grade",
			"OR UPPER(libelle_grade) LIKE :libelle_grade",
			"OR UPPER(libelle_court_grade) = :libelle_grade",
			"OR UPPER(libelle_court_grade) LIKE :libelle_grade",
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":libelle_grade" => strtoupper($sLabel)
		);
		
		// Exécution de la requête et récupération du premier résultat
		$aResultat = $this->executeSQL($aQuery, $aBind, 0);
		
		// Renvoi de l'identifiant
		return DataHelper::get($aResultat, 'id_grade', DataHelper::DATA_TYPE_INT, 0);
	}

	/**********************************************************************************************
	 * @todo	CANDIDATS
	 **********************************************************************************************/

	/**
	 * @brief	Méthode de recherche de tous les candidats.
	 *
	 * @li	Concaténation des champs du grade, nom, prénom et identifiant pour donner le `libelle_candidat`.
	 * @li	Tri des candidats par ordre de grade puis par nom et prénom.
	 *
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllCandidats() {
		// Requête SELECT
		$aQuery = array(
			"SELECT	*,",
			self::LIBELLE_CANDIDAT . " AS libelle_candidat",
			"FROM candidat",
			"JOIN grade USING(id_grade)",
			"ORDER BY nom_candidat ASC, prenom_candidat ASC, ordre_grade DESC"
		);

		try {
			return $this->executeSQL($aQuery);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
		}
	}

	/**
	 * @brief	Méthode de recherche de tous les candidats d'un stage.
	 *
	 * @li	Concaténation des champs du grade, nom, prénom et identifiant pour donner le `libelle_candidat`.
	 * @li	Tri des candidats par ordre de grade puis par nom et prénom.
	 *
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findCandidatsByStage($nId) {
		// Requête SELECT
		$aQuery = array(
			"SELECT	*,",
			self::LIBELLE_CANDIDAT . " AS libelle_candidat",
			"FROM candidat",
			"JOIN grade USING(id_grade)",
			"LEFT JOIN stage_candidat USING(id_candidat)",
			"LEFT JOIN stage USING(id_stage)",
			"WHERE id_stage = :id_stage",
			"ORDER BY nom_candidat ASC, prenom_candidat ASC, ordre_grade DESC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_stage" => $nId
		);

		try {
			// Exécution de la requête et renvoi du résultat sous forme de tableau
			return $this->executeSQL($aQuery, $aBind);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
		}
	}

	/**
	 * @brief	Recherche d'une liste de candidats.
	 *
	 * @todo	PROBLÈME D'EXÉCUTION DE LA REQUÊTE PRÉPARÉE $this->executeSQL($sQuery, $aBind)
	 *
	 * @param	date		$dDebut			: date de début
	 * @param	date		$dFin			: date de fin
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 */
	public function findCandidatByDate($dDebut, $dFin) {
		// Requête SELECT
		$sQuery = "SELECT candidat.*, grade.* FROM candidat
					JOIN grade USING(id_grade)
					WHERE id_candidat NOT IN(
						SELECT id_candidat FROM stage_candidat
						WHERE id_stage IN(
							SELECT id_stage FROM stage
							WHERE (date_debut_stage	BETWEEN DATE_FORMAT(:date_debut, '%Y-%m-%d') AND DATE_FORMAT(:date_fin, '%Y-%m-%d'))
							   OR (date_fin_stage	BETWEEN DATE_FORMAT(:date_debut, '%Y-%m-%d') AND DATE_FORMAT(:date_fin, '%Y-%m-%d'))
							   OR (date_debut_stage	<= DATE_FORMAT(:date_debut, '%Y-%m-%d') AND date_fin_stage >= DATE_FORMAT(:date_fin, '%Y-%m-%d'))
						)
					)
					ORDER BY nom_candidat ASC, prenom_candidat ASC, ordre_grade DESC";

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":date_debut"	=> DataHelper::dateFrToMy($dDebut),
			":date_fin"		=> DataHelper::dateFrToMy($dFin)
		);

		try {
			// Exécution de la requête et renvoi du résultat sous forme de tableau
			return $this->executeSQL($sQuery, $aBind);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, DataHelper::queryToString($sQuery, $aBind));
		}
	}

	/**
	 * @brief	Recherche d'un candidat.
	 *
	 * @param	integer		$nId			: identifiant du candidat
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 */
	public function getCandidatById($nId) {
		// Requête SELECT
		$aQuery = array(
			"SELECT * FROM candidat",
			"WHERE id_candidat = :id_candidat"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_candidat"	=> $nId
		);

		// Exécution de la requête et renvoi du premier résultat
		return $this->executeSQL($aQuery, $aBind, 0);
	}

	/**********************************************************************************************
	 * @todo	STAGES
	 **********************************************************************************************/

	/**
	 * @brief	Méthode de recherche de tous les stages.
	 *
	 * @li	Récupère le nombre de candidats enregistrés dans chaque stage.
	 * @li	Tri des stages par date de début puis par le libellé du stage.
	 *
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllStages() {
		// Requête SELECT
		$sQuery = "SELECT *,
						COUNT(id_stage_candidat)
					FROM stage
					LEFT JOIN domaine USING(id_domaine)
					LEFT JOIN sous_domaine USING(id_sous_domaine)
					LEFT JOIN categorie USING(id_categorie)
					LEFT JOIN sous_categorie USING(id_sous_categorie)
					LEFT JOIN stage_candidat USING(id_stage)
					GROUP BY id_stage
					ORDER BY date_debut_stage DESC, libelle_stage ASC";
		try {
			// Exécution de la requête et renvoi du résultat sous forme de tableau
			return $this->selectSQL($sQuery);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, DataHelper::queryToString($sQuery));
		}
	}

	/**
	 * @brief	Méthode de recherche de tous les stages d'un candidat.
	 *
	 * @li	Concaténation des champs du grade, nom, prénom et identifiant pour donner le `libelle_candidat`.
	 * @li	Tri des stages par date de début puis par le libellé du stage.
	 *
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findStagesByCandidat($nId) {
		// Fonctionnalité réalisée si l'identifiant est valide
		if (empty($nId)) {
			return array();
		}

		// Requête SELECT
		$aQuery = array(
			"SELECT	*,",
			self::LIBELLE_CANDIDAT . " AS libelle_candidat",
			"FROM stage",
			"LEFT JOIN domaine USING(id_domaine)",
			"LEFT JOIN stage_candidat USING(id_stage)",
			"LEFT JOIN candidat USING(id_candidat)",
			"JOIN grade USING(id_grade)",
			"WHERE id_candidat = :id_candidat",
			"ORDER BY date_debut_stage DESC, libelle_stage ASC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_candidat"		=> $nId
		);

		try {
			// Exécution de la requête et renvoi du résultat sous forme de tableau
			return $this->executeSQL($aQuery, $aBind);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
		}
	}

	/**
	 * @brief	Recherche d'un stage.
	 *
	 * @param	integer		$nId			: identifiant du stage
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 */
	public function getStageById($nId) {
		// Requête SELECT
		$aQuery = array(
			"SELECT * FROM stage",
			"WHERE id_stage = :id_stage"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_stage"			=> $nId
		);

		// Exécution de la requête et renvoi du premier résultat
		return $this->executeSQL($aQuery, $aBind, 0);
	}

	/**
	 * @brief	Recherche des codes candidats d'un stage.
	 *
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 */
	public function findCodeCandidatByIdStage($nIdStage) {
		// Requête SELECT
		$aQuery = array(
				"SELECT code_candidat FROM stage_candidat",
				"WHERE id_stage = :id_stage"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
				":id_stage"			=> $nIdStage
		);

		// Exécution de la requête
		$aSearch = $this->executeSQL($aQuery, $aBind);

		$aResultat = array();
		foreach ($aSearch as $aItem) {
			// Ajout du code candidat à la collection
			$aResultat[] = $aItem['code_candidat'];
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**********************************************************************************************
	 * @todo	UTILISATEURS
	 **********************************************************************************************/

	/**
	 * @brief	Méthode de recherche de tous les utilisateurs.
	 *
	 * @li	Concaténation des champs du grade, nom, prénom et identifiant pour donner le `libelle_utilisateur`.
	 * @li	Tri des stages par date de début puis par le libellé du stage.
	 *
	 * @param	bool		$bEditable		: filtre sur les comptes éditables par l'interface.
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllUtilisateurs($bEditable = true) {
		// Requête SELECT
		$aQuery = array(
			"SELECT	*,",
			self::LIBELLE_UTILISATEUR . " AS libelle_utilisateur",
			"FROM utilisateur",
			"JOIN grade USING(id_grade)",
			"JOIN profil USING(id_profil)",
			"WHERE editable_utilisateur >= :editable_utilisateur",
			"ORDER BY nom_utilisateur ASC, prenom_utilisateur ASC, ordre_grade DESC"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":editable_utilisateur"	=> $bEditable ? 1 : 0
		);

		try {
			// Exécution de la requête et renvoi du résultat sous forme de tableau
			return $this->executeSQL($aQuery, $aBind);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
		}
	}

	/**
	 * @brief	Recherche d'un candidat.
	 *
	 * @param	integer		$nId			: identifiant du candidat
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 */
	public function getUtilisateurById($nId) {
		// Requête SELECT
		$aQuery = array(
			"SELECT * FROM utilisateur",
			"JOIN grade USING(id_grade)",
			"WHERE id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_utilisateur"	=> $nId
		);

		// Exécution de la requête et renvoi du premier résultat
		return $this->executeSQL($aQuery, $aBind, 0);
	}

	/**********************************************************************************************
	 * @todo	CHARGER
	 **********************************************************************************************/

	/**
	 * @brief	Chargement d'un candidat enregistré en base de données.
	 *
	 * @param	integer		$nId			: identifiant du candidat à charger.
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aCandidat = array(
	 * 		// CANDIDAT ***************************************************************************
	 * 		'candidat_id'			=> "Identifiant du candidat",
	 * 		'candidat_grade'		=> "Identifiant du grade du candidat",
	 * 		'candidat_nom'			=> "Nom du candidat",
	 * 		'candidat_prenom'		=> "Prénom du candidat",
	 * 		'candidat_unite'		=> "Unité d'affectation du candidat"
	 * );
	 * @endcode
	 */
	public function chargerCandidat($nId) {
		// Initialisation du référentiel
		$this->_aFormulaire = array();

		try {
			// Initialisation du formulaire
			$this->_aFormulaire['candidat_id']		= $nId;

			// Récupération des données du formulaire
			$aResultat	= $this->getCandidatById($nId);
			// Fonctionnalité réalisée si le candidat n'est pas valide
			if (!DataHelper::isValidArray($aResultat)) {
				// Initialisation de l'identifiant
				$this->_aFormulaire['candidat_id']	= null;
			}

			// Chargement des données du formulaire
			$this->_aFormulaire['candidat_grade']	= DataHelper::get($aResultat, 'id_grade',							DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['candidat_nom']		= DataHelper::get($aResultat, 'nom_candidat',						DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['candidat_prenom']	= DataHelper::get($aResultat, 'prenom_candidat',					DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['candidat_unite']	= DataHelper::get($aResultat, 'unite_candidat',						DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['candidat_datetime']= DataHelper::get($aResultat, 'date_modification_candidat',			DataHelper::DATA_TYPE_DATETIME);

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aFormulaire;
	}

	/**
	 * @brief	Chargement d'un stage enregistré en base de données.
	 *
	 * @param	integer		$nId			: identifiant du stage à charger.
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aStage = array(
	 * 		// STAGE ******************************************************************************
	 * 		'stage_id'				=> "Identifiant du stage",
	 * 		'stage_libelle'			=> "Libellé du stage",
	 * 		'stage_date_debut'		=> "Date de début du stage",
	 * 		'stage_date_fin'		=> "Date de fin du stage"
	 * );
	 * @endcode
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function chargerStage($nId) {
		// Initialisation du référentiel
		$this->_aFormulaire = array();

		try {
			// Initialisation du formulaire
			$this->_aFormulaire['stage_id']				= $nId;

			// Récupération des données du formulaire
			$aResultat	= $this->getStageById($nId);
			// Fonctionnalité réalisée si le stage n'est pas valide
			if (!DataHelper::isValidArray($aResultat)) {
				// Initialisation de l'identifiant
				$this->_aFormulaire['stage_id']			= null;
			}

			// Chargement des données du formulaire
			$this->_aFormulaire['stage_libelle']		= DataHelper::get($aResultat, 'libelle_stage',					DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['stage_domaine']		= DataHelper::get($aResultat, 'id_domaine',						DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['stage_sous_domaine']	= DataHelper::get($aResultat, 'id_sous_domaine',				DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['stage_categorie']		= DataHelper::get($aResultat, 'id_categorie',					DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['stage_sous_categorie']	= DataHelper::get($aResultat, 'id_sous_categorie',				DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['stage_date_debut']		= DataHelper::get($aResultat, 'date_debut_stage',				DataHelper::DATA_TYPE_DATE);
			$this->_aFormulaire['stage_date_fin']		= DataHelper::get($aResultat, 'date_fin_stage',					DataHelper::DATA_TYPE_DATE);
			$this->_aFormulaire['stage_datetime']		= DataHelper::get($aResultat, 'date_modification_stage',		DataHelper::DATA_TYPE_DATETIME);

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aFormulaire;
	}

	/**
	 * @brief	Chargement d'un utilisateur enregistré en base de données.
	 *
	 * @param	integer		$nId			: identifiant de l'utilisateur à charger.
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aUtilisateur = array(
	 * 		// UTILISATEUR ************************************************************************
	 * 		'utilisateur_id'		=> "Identifiant du stage",
	 * 		'utilisateur_grade'		=> "Identifiant du grade de l'utilisateur",
	 * 		'utilisateur_nom'		=> "Nom de l'utilisateur",
	 * 		'utilisateur_prenom'	=> "Prénom de l'utilisateur",
	 * 		'utilisateur_profil'	=> "Identifiant du profil de l'utilisateur",
	 * 		'utilisateur_password'	=> "Login de connexion",
	 * 		'utilisateur_login'		=> "Login de connexion"
	 * );
	 * @endcode
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function chargerUtilisateur($nId) {
		// Initialisation du référentiel
		$this->_aFormulaire = array();

		try {
			// Initialisation du formulaire
			$this->_aFormulaire['utilisateur_id']		= $nId;

			// Récupération des données du formulaire
			$aResultat	= $this->getUtilisateurById($nId);
			// Fonctionnalité réalisée si le stage n'est pas valide
			if (!DataHelper::isValidArray($aResultat)) {
				// Initialisation de l'identifiant
				$this->_aFormulaire['utilisateur_id']	= null;
			}

			// Chargement des données du formulaire
			$this->_aFormulaire['utilisateur_grade']	= DataHelper::get($aResultat, 'id_grade',						DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['utilisateur_nom']		= DataHelper::get($aResultat, 'nom_utilisateur',				DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['utilisateur_prenom']	= DataHelper::get($aResultat, 'prenom_utilisateur',				DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['utilisateur_profil']	= DataHelper::get($aResultat, 'id_profil',						DataHelper::DATA_TYPE_INT);
			$this->_aFormulaire['utilisateur_login']	= DataHelper::get($aResultat, 'login_utilisateur',				DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['utilisateur_password']	= DataHelper::get($aResultat, 'utilisateur_password',			DataHelper::DATA_TYPE_STR);
			$this->_aFormulaire['utilisateur_datetime']	= DataHelper::get($aResultat, 'date_modification_utilisateur',	DataHelper::DATA_TYPE_DATETIME);

		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $this->_aFormulaire;
	}

	/**********************************************************************************************
	 * @todo	ENREGISTRER
	 **********************************************************************************************/

	/**
	 * @brief	Enregistrement d'un candidat en base de données.
	 *
	 * @param	array		$aCandidat		: tableau des paramètres du candidat à enregistrer.
	 * @code
	 * 	$aCandidat = array(
	 * 		// CANDIDAT ***************************************************************************
	 * 		'candidat_id'			=> "Identifiant du candidat",
	 * 		'candidat_grade'		=> "Identifiant du grade du candidat",
	 * 		'candidat_nom'			=> "Nom du candidat",
	 * 		'candidat_prenom'		=> "Prénom du candidat",
	 * 		'candidat_unite'		=> "Unité d'affectation du candidat"
	 * );
	 * @endcode
	 * @param	array		$idOrigine		: identifiant du candidat à l'origine, en cas de modification.
	 *
	 * @li	Possibilité de modifier l'identifiant tant qu'aucun rattachement à un stage n'est réalisé,
	 * Sinon, création d'une nouvelle entrée.
	 *
	 * @return	array
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrerCandidat($aCandidat, $idOrigine = null) {
		// Recherche si le candidat est déjà présent
		$aSearch = $this->getCandidatById($idOrigine);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_candidat'				=> DataHelper::get($aCandidat, "candidat_id",		DataHelper::DATA_TYPE_STR),
			':id_grade'					=> DataHelper::get($aCandidat, "candidat_grade",	DataHelper::DATA_TYPE_INT),
			':nom_candidat'				=> DataHelper::get($aCandidat, "candidat_nom",		DataHelper::DATA_TYPE_STR),
			':prenom_candidat'			=> DataHelper::get($aCandidat, "candidat_prenom",	DataHelper::DATA_TYPE_STR),
			':unite_candidat'			=> DataHelper::get($aCandidat, "candidat_unite",	DataHelper::DATA_TYPE_STR)
		);

		// Construction du tableau associatif les champs à enregistrer dans l'ordre
		$aSet	= array(
			0 => null,	/** @todo	Modification de l'idendifiant du candidat ********************/
			1 => "id_grade = :id_grade,",
			2 => "nom_candidat = :nom_candidat,",
			3 => "prenom_candidat = :prenom_candidat,",
			4 => "unite_candidat = :unite_candidat"
		);

		// Fonctionnalité de mise à jour du candidat
		if (DataHelper::isValidArray($aSearch)) {
			// Requête UPDATE
			$aInitQuery					= array("UPDATE candidat SET");
			// Finalisation de la collection $aSet[]
			if (!empty($idOrigine) && $aCandidat['candidat_id'] == $idOrigine) {
				// L'identifiant du candidat n'a pas changé
				$aSet[count($aSet)]		= "WHERE id_candidat = :id_candidat";
			} else {
				// Modification de l'identifiant du candidat
				$aSet[0]				= "id_candidat = :id_candidat,";

				// Finalisation de la collection $aSet[]
				$aSet[count($aSet)]		= "WHERE id_candidat = :id_origine";
				$aBind[':id_origine']	= $idOrigine;
			}
		} else {
			// Requête INSERT
			$aInitQuery					= array("INSERT INTO candidat SET");
			// Début de la collection $aSet[]
			$aSet[0]					= "id_candidat = :id_candidat,";
		}

		// Enregistrement du candidat
		$nIdCandidat = $this->_save($aInitQuery, $aSet, $aBind);

		// Actualisation de l'identifiant
		if (!empty($nIdCandidat) && !is_bool($nIdCandidat)) {
			$aCandidat['candidat_id'] = $nIdCandidat;
		}

		// Renvoi des données du candidat
		return $aCandidat;
	}

	/**
	 * @brief	Enregistrement d'un stage en base de données.
	 *
	 * @param	array		$aStage			: tableau des paramètres du stage à enregistrer.
	 * @code
	 * 	$aStage = array(
	 * 		// STAGE ******************************************************************************
	 * 		'stage_id'				=> "Identifiant du stage",
	 * 		'stage_libelle'			=> "Libellé du stage",
	 * 		'stage_date_debut'		=> "Date de début du stage",
	 * 		'stage_date_fin'		=> "Date de fin du stage"
	 * );
	 * @endcode
	 * @return	array
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrerStage($aStage) {
		// Récupération de l'identifiant du stage
		$nIdStage = DataHelper::get($aStage, "stage_id", DataHelper::DATA_TYPE_INT);

		// Recherche si le stage est déjà présent
		$aSearch = $this->getStageById($nIdStage);

		// Construction du tableau associatif les champs à enregistrer dans l'ordre
		$aSet	= array(
			0 => null,	/** @todo	Enregistrement de l'idendifiant du stage *********************/
			1 => "libelle_stage = :libelle_stage,",
			2 => "id_domaine = :id_domaine,",
			3 => "id_sous_domaine = :id_sous_domaine,",
			4 => "id_categorie = :id_categorie,",
			5 => "id_sous_categorie = :id_sous_categorie,",
			6 => "date_debut_stage = DATE_FORMAT(:date_debut_stage, '%Y-%m-%d'),",
			7 => "date_fin_stage = DATE_FORMAT(:date_fin_stage, '%Y-%m-%d')"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':libelle_stage'			=> DataHelper::get($aStage, 	"stage_libelle",		DataHelper::DATA_TYPE_STR),
			':id_domaine'				=> DataHelper::get($aStage, 	"stage_domaine",		DataHelper::DATA_TYPE_INT),
			':id_sous_domaine'			=> DataHelper::get($aStage, 	"stage_sous_domaine",	DataHelper::DATA_TYPE_INT),
			':id_categorie'				=> DataHelper::get($aStage, 	"stage_categorie",		DataHelper::DATA_TYPE_INT),
			':id_sous_categorie'		=> DataHelper::get($aStage, 	"stage_sous_categorie",	DataHelper::DATA_TYPE_INT),
			':date_debut_stage'			=> DataHelper::get($aStage, 	"stage_date_debut",		DataHelper::DATA_TYPE_MYDATE),
			':date_fin_stage'			=> DataHelper::get($aStage, 	"stage_date_fin",		DataHelper::DATA_TYPE_MYDATE)
		);

		// Fonctionnalité de mise à jour du candidat
		if (DataHelper::isValidArray($aSearch)) {
			// Requête UPDATE
			$aInitQuery					= array("UPDATE stage SET");

			// Finalisation de la collection $aSet[]
			$aSet[count($aSet)]			= "WHERE id_stage = :id_stage";
			$aBind[':id_stage'] 		= $nIdStage;
		} else {
			// Requête INSERT
			$aInitQuery					= array("INSERT INTO stage SET");
		}

		// Enregistrement du stage
		$nIdStage = $this->_save($aInitQuery, $aSet, $aBind);

		// Actualisation de l'identifiant
		if (!empty($nIdStage) && !is_bool($nIdStage)) {
			$aStage['stage_id'] = $nIdStage;
		}

		// Renvoi des données du stage
		return $aStage;
	}

	/**
	 * @brief	Enregistrement d'un utilisateur en base de données.
	 *
	 * @param	array		$aUtilisateur	: tableau des paramètres de l'utilisateur à enregistrer.
	 * @code
	 * 	$aUtilisateur = array(
	 * 		// UTILISATEUR ************************************************************************
	 * 		'utilisateur_id'		=> "Identifiant du stage",
	 * 		'utilisateur_grade'		=> "Identifiant du grade de l'utilisateur",
	 * 		'utilisateur_nom'		=> "Nom de l'utilisateur",
	 * 		'utilisateur_prenom'	=> "Prénom de l'utilisateur",
	 * 		'utilisateur_profil'	=> "Identifiant du profil de l'utilisateur",
	 * 		'utilisateur_password'	=> "Login de connexion",
	 * 		'utilisateur_login'		=> "Login de connexion"
	 * );
	 * @endcode
	 * @param	array		$idOrigine		: identifiant de l'utilisateur à l'origine, en cas de modification.
	 * @return	array
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrerUtilisateur($aUtilisateur, $idOrigine = null) {
		// Récupération de l'identifiant du stage
		$nIdUtilisateur = DataHelper::get($aUtilisateur, "utilisateur_id", DataHelper::DATA_TYPE_STR);

		// Recherche si l'utilisateur est déjà présent
		$aSearch = $this->getUtilisateurById(empty($idOrigine) ? $nIdUtilisateur : $idOrigine);

		// Construction du tableau associatif les champs à enregistrer dans l'ordre
		$aSet	= array(
			0 => null,	/** @todo	Modification de l'idendifiant de l'utilisateur ***************/
			// Paramètres de connexion
			1 => "id_profil = :id_profil,",
			2 => "login_utilisateur = :login_utilisateur,",
			3 => null,	/** @todo	Modification du mot de passe *********************************/
			// Paramètres de l'utilisateur
			4 => "id_grade = :id_grade,",
			5 => "nom_utilisateur = :nom_utilisateur,",
			6 => "prenom_utilisateur = :prenom_utilisateur",
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_utilisateur'			=> DataHelper::get($aUtilisateur, "utilisateur_id",			DataHelper::DATA_TYPE_STR),
			':id_grade'					=> DataHelper::get($aUtilisateur, "utilisateur_grade",		DataHelper::DATA_TYPE_INT),
			':nom_utilisateur'			=> DataHelper::get($aUtilisateur, "utilisateur_nom",		DataHelper::DATA_TYPE_STR),
			':prenom_utilisateur'		=> DataHelper::get($aUtilisateur, "utilisateur_prenom",		DataHelper::DATA_TYPE_STR),
			':id_profil'				=> DataHelper::get($aUtilisateur, "utilisateur_profil",		DataHelper::DATA_TYPE_INT),
			':login_utilisateur'		=> DataHelper::get($aUtilisateur, "utilisateur_login",		DataHelper::DATA_TYPE_STR)
		);

		// Fonctionnalité réalisée si le mot de passe n'est pas vide
		$sPassword		= DataHelper::get($aUtilisateur, "utilisateur_password",		DataHelper::DATA_TYPE_STR);
		$sConfirmation	= DataHelper::get($aUtilisateur, "utilisateur_confirmation",	DataHelper::DATA_TYPE_STR);

		// Fonctionnalité arrêtée si le mot de passe est invalide
		if (empty($nIdUtilisateur) && empty($sPassword) || $sPassword != $sConfirmation) {
			ViewRender::setMessageAlert("Mot de passe invalide");
			return $aUtilisateur;
		} elseif (!empty($sPassword) && $sPassword == $sConfirmation) {
			// Ajout de l'enregistrement du mot de passe s'il n'est pas vide
			$aSet[3]						= "password_utilisateur = :password_utilisateur,";
			$aBind[':password_utilisateur']	= md5($sPassword);
		}

		// Fonctionnalité de mise à jour du candidat
		if (DataHelper::isValidArray($aSearch)) {
			// Requête UPDATE
			$aInitQuery					= array("UPDATE utilisateur SET");

			// Finalisation de la collection $aSet[]
			if (!empty($idOrigine) && $aUtilisateur['utilisateur_id'] == $idOrigine) {
				// L'identifiant de l'utilisateur n'a pas changé
				$aSet[count($aSet)]		= "WHERE id_utilisateur = :id_utilisateur";
			} else {
				// Modification de l'identifiant de l'utilisateur
				$aSet[0]				= "id_utilisateur = :id_utilisateur,";

				// Finalisation de la collection $aSet[]
				$aSet[count($aSet)]		= "WHERE id_utilisateur = :id_origine";
				$aBind[':id_origine']	= $idOrigine;
			}
		} else {
			// Requête INSERT
			$aInitQuery					= array("INSERT INTO utilisateur SET");
			// Début de la collection $aSet[]
			$aSet[0]					= "id_utilisateur = :id_utilisateur,";
		}

		// Enregistrement de l'utilisateur
		$nIdUtilisateur = $this->_save($aInitQuery, $aSet, $aBind);

		// Actualisation de l'identifiant
		if (!empty($nIdUtilisateur) && !is_bool($nIdUtilisateur)) {
			$aUtilisateur['utilisateur_id'] = $nIdUtilisateur;
		}

		// Renvoi des données de l'utilisateur
		return $aUtilisateur;
	}

	/**********************************************************************************************
	 * @todo	AJOUTER
	 **********************************************************************************************/

	/**
	 * @brief	Ajout de candidats à un stage.
	 *
	 * @li	Exploitation d'une transaction.
	 * @li	Recherche si le couple `id_stage` et `id_candidat` existe déjà en base.
	 * @li	Lors de l'enregistrement, le code candidat par défaut correspond à l'identifiant.
	 *
	 * @param	array		$aCandidats		: liste des identifiants de candidats à ajouter
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @return	array, tableau contenant l'ensemble des résultats d'enregistrement.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function addStageCandidats($aCandidats, $nIdStage) {
		// Initialisation du résultat
		$aValide = array();

		// Force le mode transactionnel
		$this->beginTransaction();

		// Fonctionnalité réalisée pour chaque candidat
		foreach ($aCandidats as $nIdCandidat) {
			// Requête SELECT
			$sSearch = "SELECT id_stage_candidat FROM stage_candidat WHERE id_stage = :id_stage AND id_candidat = :id_candidat";

			// Requête INSERT
			$aQuery = array(
				"INSERT INTO stage_candidat SET",
				"id_stage = :id_stage,",
				"id_candidat = :id_candidat,",
				"code_candidat = :id_candidat",
			);

			// Construction du tableau associatif des étiquettes et leurs valeurs
			$aBind	= array(
				":id_stage"		=> $nIdStage,
				":id_candidat"	=> $nIdCandidat
			);

			// Recherche si l'enregistrement existe déjà
			$aSearch = $this->executeSQL($sSearch, $aBind, 0);
			if (DataHelper::isValidArray($aSearch)) {
				continue;
			}

			try {
				// Exécution de la requête
				$aValide[$nIdCandidat] = $this->executeSQL($aQuery, $aBind);
			} catch (ApplicationException $e) {
				// Annulation des modifications
				$this->oSQLConnector->rollBack();
				// Affichage d'un message d'erreur
				ViewRender::setMessageAlert("Erreur rencontrée lors de l'enregistrement...");
				// Personnalisation de l'exception
				throw new ApplicationException('EQueryInsert', DataHelper::queryToString($aQuery, $aBind));
			}
		}

		// Validation des modifications
		$this->oSQLConnector->commit();

		// Réinitialisation de VIEW_DIALOG
		ViewRender::clearDialog();

		// Fonctionnalité réalisée si au moins un enregistrement a été réalisé
		if (DataHelper::isValidArray($aValide)) {
			// Affichage d'un message de confirmation
			ViewRender::setMessageSuccess("Enregistrement réalisé avec succès !");
		} else {
			// Affichage d'un message d'avertissement
			ViewRender::setMessageAlert("Aucun enregistrement n'a été réalisé...");
		}
		// Renvoi du résultat
		return $aValide;
	}

	/**********************************************************************************************
	 * @todo	IMPORTER
	 **********************************************************************************************/

	/**
	 * @brief	Importation d'une liste de candidats à un stage.
	 *
	 * @li	Si le candidat n'existe pas, il est enregistré.
	 *
	 * @li	Commit final par la méthode AdministrationManager::addStageCandidats().
	 *
	 * @param	array		$aListeCandidats: liste des données d'enregistrement des candidats
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @return	array, tableau contenant l'ensemble des résultats d'enregistrement.
	 * @return	array
	 */
	public function importerCandidats($aListeCandidats, $nIdStage) {
		// Initialisation du résultat
		$aCandidats = array();

		// Force le mode transactionnel
		$this->beginTransaction();

		// Fonctionnalité réalisée pour chaque candidat
		foreach ($aListeCandidats as $aParams) {
			// Recherche de l'dentifiant du grade
			if (!isset($aParams['candidat_grade'])) {
				// Récupération du libellé
				$sLabel = DataHelper::get($aParams, "libelle_grade", DataHelper::DATA_TYPE_STR, null);
				// Recherche de l'identifiant du grade
				$aParams['candidat_grade'] = $this->getIdGradeByLabel($sLabel);
			}

			// Fonctionnalité réalisée si l'enregistrement du candidat est valide
			if ($this->enregistrerCandidat($aParams, $aParams['candidat_id'])) {
				// Ajout de l'identifiant à la collection
				$aCandidats[] = $aParams['candidat_id'];
			}
		}

		// Enregistrement de la liste des candidats au STAGE
		$this->addStageCandidats($aCandidats, $nIdStage);
		// Renvoi du résultat
		return $aCandidats;
	}

	/**********************************************************************************************
	 * @todo	RENOUVELER
	 **********************************************************************************************/

	/**
	 * @brief	Modification du code candidat
	 *
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @param	string		$sCodeCandidat	: code du candidat à enregistrer
	 * @param	string		$sIdCandidat	: identifiant du candidat
	 * @return	void
	 */
	public function modifierCodeCandidatByStage($nIdStage, $sCodeCandidat, $sIdCandidat) {
		// Requête UPDATE
		$aQuery = array(
			"UPDATE stage_candidat SET",
			"code_candidat = :code_candidat",
			"WHERE id_stage = :id_stage",
			"AND id_candidat = :id_candidat"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
				':code_candidat'			=> (string) $sCodeCandidat,
				':id_stage'					=> $nIdStage,
				':id_candidat'				=> $sIdCandidat
		);

		// Mise à jour du code candidat
		$this->_save($aQuery, array(), $aBind);
	}

	/**
	 * @brief	Renouvellement de la liste des codes candidats
	 *
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @param	array		$aCandidats		: liste des identifiants de candidat
	 * @param	integer		$nFormat		: format du code candidat
	 * @return	void
	 */
	public function renouvelerCandidats($nIdStage, $aCandidats, $nFormat) {
		// Récupération de la liste des code selon l'identifiant du stage
		$aListeOriginal = $this->findCodeCandidatByIdStage($nIdStage);

		// Parcours de la liste des candidats à renouveler
		foreach ($aCandidats as $nIdCandidat) {
			// Initialisation du code candidat
			$sCodeCandidat = "";

			// Initialisation du code jusqu'à ce que le code soit unique
			while (empty($sCodeCandidat)) {
				// Caractères constituant le code candidat
				$sCharacts = "1234567890";

				// Création du code aléatoirement
				for($i = 0 ; $i < $nFormat ; $i++) {
					$sCodeCandidat .= substr($sCharacts, rand()%(strlen($sCharacts)), 1);
				}

				// Fonctionnalité réalisée si le code existe déjà
				if (in_array($sCodeCandidat, $aListeOriginal)) {
					$sCodeCandidat = "";
				}
			}

			// Formatage du code candidat du style '%01d', '%02d', '%03d', '%04d', '%05d', '%06d', '%07d' ou '%08d'
			$sCodeCandidat = sprintf('%0' . $nFormat . 'd', $sCodeCandidat);

			// Ajout du code à la collection
			$aListeOriginal[] = $sCodeCandidat;

			// Enregistrement du code candidat
			$this->modifierCodeCandidatByStage($nIdStage, $sCodeCandidat, $nIdCandidat);
		}
	}

	/**********************************************************************************************
	 * @todo	SUPPRIMER
	 **********************************************************************************************/

	/**
	 * @brief	Suppression d'un candidat.
	 *
	 * @param	integer		$nId			: identifiant du stage
	 * @return	boolean
	 */
	public function deleteCandidatById($nId) {
		// Requête SELECT
		$aQuery = array(
			"DELETE FROM candidat",
			"WHERE id_candidat = :id_candidat"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_candidat"		=> $nId
		);

		// Exécution de la requête et renvoi résultat
		return $this->_delete($aQuery, $aBind);
	}

	/**
	 * @brief	Suppression d'un candidat enregistré sur un stage.
	 *
	 * @param	integer		$nIdCandidat	: identifiant du candidat
	 * @param	integer		$nIdStage		: identifiant du stage
	 * @return	boolean
	 */
	public function deleteCandidatByIdStage($nIdCandidat, $nIdStage) {
		// Requête SELECT
		$aQuery = array(
			"DELETE FROM stage_candidat",
			"WHERE id_candidat = :id_candidat",
			"AND id_stage = :id_stage"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_candidat"		=> $nIdCandidat,
			":id_stage"			=> $nIdStage
		);

		// Exécution de la requête et renvoi résultat
		return $this->_delete($aQuery, $aBind);
	}

	/**
	 * @brief	Suppression d'un stage.
	 *
	 * @param	integer		$nId			: identifiant du stage
	 * @return	boolean
	 */
	public function deleteStageById($nId) {
		// Requête SELECT
		$aQuery = array(
			"DELETE FROM stage",
			"WHERE id_stage = :id_stage"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_stage"			=> $nId
		);

		// Exécution de la requête et renvoi résultat
		return $this->_delete($aQuery, $aBind);
	}

	/**
	 * @brief	Suppression d'un utilisateur.
	 *
	 * @param	integer		$nId			: identifiant du stage
	 * @return	boolean
	 */
	public function deleteUtilisateurById($nId) {
		// Requête SELECT
		$aQuery = array(
			"DELETE FROM utilisateur",
			"WHERE id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_utilisateur"	=> $nId
		);

		// Exécution de la requête et renvoi résultat
		return $this->_delete($aQuery, $aBind);
	}

}
