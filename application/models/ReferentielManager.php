<?php
/**
 * Classe de consultation du référentiel.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * @name		ReferentielManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 100 $
 * @since		$LastChangedDate: 2018-01-10 19:53:46 +0100 (Wed, 10 Jan 2018) $
 * @see			{ROOT_PATH}/libraries/models/MySQLManager.php
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ReferentielManager extends MySQLManager {

	const	TABLE_SALLE			= "salle";

	/**
	 * @brief	Tableau associatif des tables du référentiel et leur dénomination.
	 *
	 * @li	Liste des référentiels administrables, dans l'ordre d'affichage souhaité.
	 *
	 * @var		array
	 * @code
	 * 	array(
	 * 		'nom_de_la_table'	=> "Libellé du référentiel"
	 *  );
	 * @endcode
	 */
	static $REF_TABLE_LIBELLE	= array(
		// NOM DE LA TABLE		=> LIBELLÉ DU RÉFÉRENTIEL
		'domaine'				=> "Domaine",
		'sous_domaine'			=> "Sous-Domaine",
		'categorie'				=> "Catégorie",
		'sous_categorie'		=> "Sous-Catégorie",
		self::TABLE_SALLE		=> "Gestion des salles"
	);

	/**
	 * @brief	Tableau associatif des tables et leur parent.
	 *
	 * @li	Liste des relations entre la table du référentiel et celle du parent.
	 *
	 * @var		array
	 * @code
	 * 	array(
	 * 		'nom_de_la_table'	=> 'nom_de_la_table_parent'
	 *  );
	 * @endcode
	 */
	static $REF_TABLE_PARENT	= array(
		// NOM DE LA TABLE		=> NOM DE LA TABLE PARENT
		'categorie'				=> 'domaine',
		'sous_domaine'			=> 'domaine',
		'sous_categorie'		=> 'categorie'
	);

	/**
	 * @brief	Tableau associatif des champs du référentiel et leur composition.
	 *
	 * @li	Liste des champs de chaque référentiel.
	 * @li	Les champs seront filtrés selon la table par la classe SelectManager lors de la construction de la requête.
	 *
	 * @var		array
	 * @code
	 * 	array(
	 * 		'nom_du_champ'		=> DataHelper::DATA_TYPE_*
	 *  );
	 * @endcode
	 */
	static $REF_TABLE_FORMAT	= array(
		// NOM DU CHAMP			=> FORMAT DES CHAMPS DE LA TABLE
		'id_%s'					=> DataHelper::DATA_TYPE_INT,
		'libelle_%s'			=> DataHelper::DATA_TYPE_STR,
		'description_%s'		=> DataHelper::DATA_TYPE_TXT,
		'date_debut_%s'			=> DataHelper::DATA_TYPE_DATE,
		'date_fin_%s'			=> DataHelper::DATA_TYPE_DATE,
		'date_modification_%s'	=> DataHelper::DATA_TYPE_DATETIME
	);

	/**
	 * @brief	Tableau associatif des référentiel auxquels doient être ajouter l'information des DATES.
	 *
	 * @var		array
	 * @code
	 * 	array(
	 * 		'nom_de_la_table'	=> boolean
	 *  );
	 * @endcode
	 */
	static $REF_TABLE_DATE		= array(
		// NOM DE LA TABLE		=> AFFICHAGE DES DATES DANS LE LIBELLE
		'stage'					=> true
	);

	/**
	 * @brief	Tableau associatif des référentiel auxquels doient être jointes des tables annexes.
	 *
	 * @var		array
	 * @code
	 * 	array(
	 *		// NOM DE LA TABLE	=> LISTE DE JOINTURES ANNEXES
	 * 		'nom_de_la_table'	=> array(
	 * 									// PARAMÈTRES DE JOINTURE AVEC LA TABLE_1
	 * 									array(
	 * 										'name'	=> NAME_1,		// Nom de la table
	 * 										'type'	=> TYPE_1,		// Type de jointure
	 * 										'using'	=> USING_1,		// Nom de champ utilisé par la commande USING()
	 * 										'field'	=> FIELD_1		// Ensemble des champs à récupérer
	 * 									),
	 *
	 * 									// PARAMÈTRES DE JOINTURE AVEC LA TABLE_2
	 * 									array(
	 * 										'name'	=> NAME_2,		// Nom de la table
	 * 										'type'	=> TYPE_2,		// Type de jointure
	 * 										'using'	=> USING_2,		// Nom de champ utilisé par la commande USING()
	 * 										'field'	=> FIELD_2		// Ensemble des champs à récupérer
	 * 									),
	 *
	 * 									// PARAMÈTRES DE JOINTURE AVEC LA TABLE_3
	 * 									array(
	 * 										'name'	=> NAME_3,			// Nom de la table
	 * 										'type'	=> TYPE_3,			// Type de jointure
	 * 										'using'	=> USING_3,			// Clef étrangère utilisée par la commande USING()
	 * 										'field'	=> FIELD_3			// Ensemble des champs à récupérer
	 * 									)
	 *  	);
	 * @endcode
	 */
	static $REF_TABLE_JOINTURE	= array(
		// NOM DE LA TABLE		=> LISTE DE JOINTURE ANNEXES
		self::TABLE_SALLE		=> array(
										// PARAMÈTRES DE JOINTURE SUR LA TABLE `statut_salle`
										array(
											'name'	=> "statut_salle",	// Nom de la table
											'type'	=> "LEFT",			// Type de jointure
											'using'	=> "id_salle",		// Clef étrangère utilisée par la commande USING()
											'field'	=> "*"				// Ensemble des champs à récupérer
										)
		)
	);

	/**
	 * @brief	Tableau des types d'épreuve.
	 * @var		array
	 */
	static $REF_TYPE_EPREUVE	= array(
		"Concours",
		"Contrôle",
		"Épreuve",
		"Examen",
		"Questionnaire"
	);

	/**
	 * @brief	Constante du libellé de l'épreuve.
	 * Le libellé de l'épreuve est construit à partir du libellé du stage et de ses dates de début et de fin.
	 * @var		string
	 */
	const CONCAT_LIBELLE_DATE	= "CONCAT(libelle_%s, ' (', DATE_FORMAT(date_debut_%s, '%d/%m/%Y'), ' - ', DATE_FORMAT(date_fin_%s, '%d/%m/%Y'), ')')";

	/***************************************************************************************************
	 * @todo	GÉNÉRATION D'UN FORMULAIRE QCM
	 ***************************************************************************************************/

	/**
	 * @brief	Récupèration de la liste des formats papier gérés.
	 *
	 * @li	Renvoi la liste des formats supportés par l'exportation.
	 * @code
	 * return array(
	 *		'a4paper'			=> "A4 - Portrait",
	 *		'a4paper,landscape'	=> "A4 - Paysage",
	 *		'a3paper'			=> "A3 - Portrait",
	 *		'a3paper,landscape'	=> "A3 - Paysage"
	 * );
	 * @endcode
	 *
	 * @return	array
	 */
	public function findListeFormatPapier() {
		return array(
			'a4paper'			=> "A4 - Portrait"
		);
	}

	/***************************************************************************************************
	 * @todo	CONSULTER
	 ***************************************************************************************************/

	/**
	 * @brief	Récupèration d'un référentiel.
	 *
	 * @li	Méthode générique de consultation du référentiel.
	 * @li	Le profil [AclManager::ID_PROFIL_ADMINISTRATOR] n'est pas limité sur les dates de validité du référentiel.
	 *
	 * @param	string	$sTable			: Nom de la table du référentiel en base de données.
	 * @param	array	$xWhere			: Clause WHERE de la requête.
	 * @code
	 * 	$xWhere = array('nom_du_champ' => "valeur du champ");
	 * @endcode
	 * @param	array	$xFinal  	  	: Clause finale de la requête.
	 * @return	array
	 */
	public function findListeReferentiel($sTable, $xWhere = array(), $xOrder = array(), $xFinal = array()) {
		// Construction de la liste des ALIAS
		$aAlias = array();
		foreach (self::$REF_TABLE_FORMAT as $sAlias => $sFormat) {
			// Récupération du nom du champ
			$sLabel = sprintf($sAlias, $sTable);

			// Fonctionnalité réalisée si le libellé doit contenir les DATES
			if ($sAlias == "libelle_%s" && array_key_exists($sTable, self::$REF_TABLE_DATE) && self::$REF_TABLE_DATE[$sTable]) {
				// Concatenation du LIBELLE avec DATE_DEBUT et DATE_FIN
				$sLabel = str_replace("%s", $sTable, self::CONCAT_LIBELLE_DATE);
			}

			// Ajout de l'alias à la collection
			$aAlias[$sLabel] = sprintf($sAlias, "referentiel");
		}

		// Initialisation du gestionnaire SELECT
		$oReferentiel = new SelectManager($sTable, $aAlias);

		// Fonctionnalité réalisée si la table liée à une table parent
		if (array_key_exists($sTable, self::$REF_TABLE_PARENT)) {
			// Récupération du nom de la table parent
			$sParentTable	= self::$REF_TABLE_PARENT[$sTable];
			// Jointure avec la table parent
			$aAliasParent = array(
				"id_" . $sParentTable		=> "id_parent",
				"libelle_" . $sParentTable	=> "libelle_parent"
			);
			$oReferentiel->joinUsing(SelectManager::JOIN_LEFT, $sParentTable, "id_%s", $aAliasParent);
		}

		// Parcours de la liste des jointures annexes au référentiel
		foreach (self::$REF_TABLE_JOINTURE as $sName => $aJointure) {
			// Fonctionnalité réalisée si la table possède une jointure
			if ($sName == $sTable) {
				// Parcours de l'ensemble des jointures de la table
				foreach ($aJointure as $aParams) {
					$sType	= $aParams['type'];
					$sName	= $aParams['name'];
					$sUsing	= $aParams['using'];
					$sField	= $aParams['field'];

					// Ajout de la jointure à la requête
					$oReferentiel->joinUsing($sType, $sName, $sUsing, $sField);
				}
			}
		}

		// Ajout de la clause WHERE
		$oReferentiel->where($xWhere);
		$oReferentiel->andWhere(sprintf("id_%s > 0", $sTable));

		// Fonctionnalité réalisée si l'utilisateur n'est pas [Administrateur]
		if (!$this->_oAuth->isProfil(AclManager::ID_PROFIL_ADMINISTRATOR)) {
			// Restriction sur les dates
			$sWhere	= str_ireplace("%s", $sTable, "date_fin_%s >= CURRENT_DATE() OR date_fin_%s IS NULL");
			// Ajout de la restriction à la clause WHERE
			$oReferentiel->andWhere($sWhere);
		}

		// Ajout du tri
		$oReferentiel->order($xOrder);

		// Ajout à la fin de la requête
		$oReferentiel->append($xFinal);

		$aReferentiel = array();
		try {
			// Exécution de la requête
			$aReferentiel = $oReferentiel->fetchAll();
		} catch (ApplicationException $e) {
			throw new ApplicationException('EBadQuery', $oReferentiel->toString());
		}

		// Renvoi du tableau
		return $aReferentiel;
	}

	/**
	 * @brief	Récupèration d'un référentiel selon les dates de validité.
	 *
	 * @li	Seules les entrées récupérées ont les champs :
	 * 	- DATE_DEBUT	inférieur à CURRENT_DATE() ;
	 * 	- DATE_FIN		supérieur à CURRENT_DATE().
	 *
	 * @param	string	$sTable			: Nom de la table du référentiel en base de données.
	 * @param	array	$xWhere			: Clause WHERE de la requête.
	 * @code
	 * 	$xWhere = array('nom_du_champ' => "valeur du champ");
	 * @endcode
	 * @param	array	$xFinal   	 	: Clause finale de la requête.
	 * @return	array
	 */
	public function findListeReferentielValid($sTable, $xWhere = array(), $xOrder = array(), $xFinal = array()) {
		// Forçage du typage en ARRAY de la clause WHERE
		$aWhere		= (array) $xWhere;

		// Ajout de la restriction à la clause WHERE
		$aWhere[]	= str_ireplace("%s", $sTable, "CURRENT_DATE() BETWEEN date_debut_%s AND date_fin_%s OR date_fin_%s IS NULL");

		// Exécution de la requête et renvoi du tableau
		return $this->findListeReferentiel($sTable, $aWhere, $xOrder, $xFinal);
	}

	/**
	 * @brief	Récupèration d'un référentiel par son identifiant.
	 *
	 * @param	string	$sTable			: Nom de la table du référentiel en base de données.
	 * @param	integer	$nId			: Identifiant à récupérer.
	 * @return	array
	 */
	public function getReferentielById($sTable, $nId) {
		// Initialisation du résultat
		$aResultat = array();
		// Récupération du référentiel;
		$aReferentiel = $this->findListeReferentiel($sTable, array("id_" . $sTable => $nId));
		// Récupération du résultat
		if (DataHelper::isValidArray($aReferentiel)) {
			// Récupération de la premère occurrence
			$aResultat = $aReferentiel[0];
		} else {
			$aResultat = array();
		}
		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Récupèration de la liste du référentiel parent.
	 *
	 * @li	Recherche le nom de la table parent passée en paramètre.
	 *
	 * @param	string	$sTable			: Nom de la table du référentiel en base de données.
	 * @return	array
	 */
	public function findListeParent($sTable) {
		// Fonctionnalité réalisée si la table est liée à un parent
		if (array_key_exists($sTable, self::$REF_TABLE_PARENT)) {
			// Récupération de la table parent
			$sTable = self::$REF_TABLE_PARENT[$sTable];
		}
		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel($sTable, null, "libelle_referentiel ASC");
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"));
	}

	/**
	 * @brief	Récupèration de la liste des domaines selon un libellé.
	 * @param	string	$sLabel			: Nom du domaine à rechercher.
	 * @return	array
	 */
	public function findDomainesByLabel($sLabel = null) {
		// Découpage du contenu en mots clés
		$aItems		= explode(" ", $sLabel);
		
		$aSearch	= array();
		$nCount		= 0;
		
		// Parcours du référentiel avec chaque mot-clé à la recherche d'un seul résultat
		while (!DataHelper::isValidArray($aSearch, 1) && $nCount < count($aItems)) {
			// Initialisation de la clause WHERE
			$aWhere	= array(
				sprintf("libelle_domaine LIKE LOWER('%%%s%%')", strtolower(DataHelper::get($aItems, $nCount, DataHelper::DATA_TYPE_STR)))
			);

			// Récupération du référentiel selon le fragment du nom de catégorie
			$aSearch = $this->findListeReferentiel('domaine', $aWhere, "libelle_referentiel ASC");
			
			// Comptage de boucle
			$nCount++;
		}
		
		// Renvoi du résultat sous forme de tableau
		return $aSearch;
	}

	/**
	 * @brief	Récupèration de la liste des domaines.
	 * @return	array
	 */
	public function findListeDomaines() {
		// Initialisation de la clause WHERE
		$aWhere = array();

		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('domaine', $aWhere, "libelle_referentiel ASC");
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"));
	}

	/**
	 * @brief	Récupèration de la liste des sous-domaines.
	 * @param	integer	$nIdDomaine		: Identifiant du domaine de référence.
	 * @return	array
	 */
	public function findListeSousDomaines($nIdDomaine = null) {
		// Initialisation de la clause WHERE
		$aWhere = array();

		// Fonctionnalité ralisée si l'identifiant du domaine est renseigné
		if (!is_null($nIdDomaine)) {
			$aWhere['id_domaine'] = $nIdDomaine;
		}

		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('sous_domaine', $aWhere, "libelle_referentiel ASC");
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"));
	}

	/**
	 * @brief	Récupèration de la liste des catégories selon un libellé.
	 * @param	string	$sLabel			: Nom de la catégorie à rechercher.
	 * @return	array
	 */
	public function findCategoriesByLabel($sLabel = null) {
		// Découpage du contenu en mots clés
		$aItems		= explode(" ", $sLabel);
		
		$aSearch	= array();
		$nCount		= 0;
		
		// Parcours du référentiel avec chaque mot-clé à la recherche d'un seul résultat
		while (!DataHelper::isValidArray($aSearch, 1) && $nCount < count($aItems)) {
			// Initialisation de la clause WHERE
			$aWhere	= array(
				sprintf("libelle_categorie LIKE LOWER('%%%s%%')", strtolower(DataHelper::get($aItems, $nCount, DataHelper::DATA_TYPE_STR)))
			);

			// Récupération du référentiel selon le fragment du nom de catégorie
			$aSearch = $this->findListeReferentiel('categorie', $aWhere, "libelle_referentiel ASC");
			
			// Comptage de boucle
			$nCount++;
		}
		
		// Renvoi du résultat sous forme de tableau
		return $aSearch;
	}

	/**
	 * @brief	Récupèration de la liste des catégories.
	 * @param	integer	$nIdDomaine		: Identifiant du domaine associé.
	 * @return	array
	 */
	public function findListeCategories($nIdDomaine = null) {
		// Initialisation de la clause WHERE
		$aWhere = array();

		// Fonctionnalité ralisée si l'identifiant du domaine est renseigné
		if (!is_null($nIdDomaine)) {
			$aWhere['id_domaine'] = $nIdDomaine;
		}

		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('categorie', $aWhere, "libelle_referentiel ASC");
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"));
	}

	/**
	 * @brief	Récupèration de la liste des sous-catégories.
	 * @param	integer	$nIdCategorie	: identifiant de la catégorie de référence.
	 * @return	array
	 */
	public function findListeSousCategories($nIdCategorie = null) {
		// Initialisation de la clause WHERE
		$aWhere = array();

		// Fonctionnalité ralisée si l'identifiant du domaine est renseigné
		if (!is_null($nIdCategorie)) {
			$aWhere['id_categorie'] = $nIdCategorie;
		}

		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('sous_categorie', $aWhere, "libelle_referentiel ASC");
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"));
	}

	/**
	 * @brief	Récupèration de la liste des grades.
	 * @return	array
	 */
	public function findListeGrades() {
		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('grade', null, array('ordre_grade DESC'));
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"), "-", false, "");
	}

	/**
	 * @brief	Récupèration de la liste des profiles.
	 * @return	array
	 */
	public function findListeProfiles() {
		// Construction de la clause WHERE
		$aWhere = array("id_profil >= %d" => AclManager::ID_PROFIL_USER);
		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('profil', $aWhere, array('id_profil ASC'));
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"), null, false, "");
	}

	/**
	 * @brief	Récupèration de la liste des stages.
	 *
	 * @li	Possibilité de filtrer la liste des stages selon le domaine.
	 * @li	Possibilité de filtrer uniquement les stages qui se termineront ultérieurement.
	 *
	 * @param	integer	$nIdDomaine		: (optionnel) Identifiant du domaine associé.
	 * @param	boolean	$bActif			: (optionnel) Ne récupère que les stages non terminés ou à venir.
	 * @return	array
	 */
	public function findListeStages($nIdDomaine = null, $bActif = false) {
		// Initialisation de la clause WHERE
		$aWhere = array();
		
		// Construction de la clause WHERE
		$aWhere = array();
		
		// Fonctionnalité réalisée si un domaine est passé en paramètre
		if (!is_null($nIdDomaine)) {
			$aWhere["id_domaine = %d"]	= $nIdDomaine;
		}
		
		// Fonctionnalité réalisée si le stage ne doit pas être terminé
		if ($bActif) {
			// Filtre selon la date de fin
			$aWhere["date_fin_stage >= %s"]	= date("Y-m-d");
		}
		
		// Récupération du référentiel
		$aReferentiel = $this->findListeReferentiel('stage', $aWhere, array('libelle_stage ASC'));
		// Transformation du tableau en array('id' => 'libelle')
		return DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"), null, false, "");
	}

	/**
	 * @brief	Récupèration de la liste des types d'épreuve.
	 *
	 * @return	array
	 */
	public function findListeTypesEpreuve() {
		// Initialisation du résultat
		$aListe = array();
		// Parcours du référentiel statique
		foreach (self::$REF_TYPE_EPREUVE as $sLibelle) {
			$aListe[$sLibelle] = $sLibelle;
		}
		// Envoi du tableau du type array('libelle' => 'libelle')
		return $aListe;
	}

	/**
	 * @brief	Récupèration de la liste des salles.
	 *
	 * @li	Possibilité de filtrer la liste des salles selon leur disponibilité.
	 *
	 * @param	date	$dDate				: (optionnel) Date de disponibilité à rechercher au format [Y-m-d].
	 * @param	time	$tHeure				: (optionnel) Heure de disponibilité à rechercher au format [H:i].
	 * @param	integer	$nDuree				: (optionnel) Durée nécessaire à l'utilisation, en minutes.
	 * @param	boolean	$bExamen			: (optionnel) Si la salle est destinée à une épreuve.
	 * @param	boolean	$bReservable		: (optionnel) Si la salle est destinée à une épreuve.
	 * @return	array
	 */
	public function findListeSalles($dDate = null, $tHeure = null, $nDuree = 0, $bExamen = true, $bReservable = null) {
		try {
			// Initialisation de la clause WHERE
			$aWhere = array();

			// Filtre sur les salles d'examen
			if (isset($bExamen)) {
				$aWhere[] = sprintf("examen_statut_salle = %d", (bool) $bExamen);
			}

			// Filtre sur les salles réservable
			if (isset($bReservable)) {
				$aWhere[] = sprintf("reservable_statut_salle = %d", (bool) $bReservable);
			}

			// Fonctionnalité réalisée si un filtre est renseigné
			if (isset($dDate) || isset($tHeure)) {
				// Requête SELECT
				$aQuery	= array(
					"SELECT * FROM salle",
					"INNER JOIN statut_salle USING(id_salle)",
					"LEFT JOIN reservation USING(id_salle)",
					"WHERE " . implode (" AND ", $aWhere),
					"AND (date_debut_reservation NOT BETWEEN \":date_debut_libre\" AND \":date_fin_libre\" OR id_reservation IS NULL)",
					"GROUP BY id_salle"
				);

				// Définition de la date si non renseignée
				if (empty($dDate)) {
					// Date courante par défaut
					$dDate					= date("Y-m-d");
				}

				// Définition de l'heure si non renseignée
				if (empty($tHeure)) {
					// Heure courante par défaut
					$tHeure					= date("H:i");
				}

				// Récupération des informations de la date
				list($nAn, $nMois, $nJour)	= explode("-", $dDate);

				// Récupération des informations de l'heure et des minutes
				list($nHeure, $nMinutes)	= explode(":", $tHeure);

				// Définition de la date de début
				$dDateDebut					= mktime($nHeure, $nMinutes, 0, $nMois, $nJour, $nAn);

				// Ajustement des paramètres de recherche selon la durée
				if (empty($nDuree)) {
					// Passage au jour suivant
					$nJour++;
				} else {
					// Ajout de la durée en minutes
					$nMinutes += $nDuree;
				}

				// Définition de la date de fin
				$dDateFin					= mktime($nHeure, $nMinutes, 0, $nMois, $nJour, $nAn);

				// Construction du tableau associatif des étiquettes et leurs valeurs
				$aBind = array(
					':date_debut_libre'	=> date("Y-m-d H:i", $dDateDebut),
					':date_fin_libre'	=> date("Y-m-d H:i", $dDateFin)
				);

				// Exécution de la requête et récupération sous forme de tableau
				$aReferentiel = $this->executeSQL($aQuery, $aBind);

				// Renvoi de la liste
				$aResultat = DataHelper::requestToList($aReferentiel, array('id_salle' => "libelle_salle"));
			} else {
				// Récupération du référentiel
				$aReferentiel = $this->findListeReferentiel('salle', $aWhere, array('libelle_salle ASC'));
				// Transformation du tableau en array('id' => 'libelle')
				$aResultat = DataHelper::requestToList($aReferentiel, array('id_referentiel' => "libelle_referentiel"), null, false, "");
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Transformation du tableau en array('id' => 'libelle')
		return $aResultat;
	}

	/******************************************************************************************************
	 * @todo	VÉRIFICATION
	 ******************************************************************************************************/

	/**
	 * @brief	Vérifie si le libellé du réfrentiel existe déjà.
	 *
	 * @li	Recherche du libellé selon les dates de validité du référentiel.
	 *
	 * @param	string	$sTableReferentiel	: Nom de la table du référentiel à charger.
	 * @param	array	$aReferentiel		: tableau des paramètres du référentiel à enregistrer.
	 * @return	string, libellé du référentiel en doublon
	 * @see		ReferentielManager::enregistrer()
	 */
	private function _isLibelleExists($sTableReferentiel, $aReferentiel) {
		// Récupération de l'identifiant du référentiel
		$nIdReferentiel			= DataHelper::get($aReferentiel,	"referentiel_id",					DataHelper::DATA_TYPE_STR);

		// Récupération du libellé du référentiel
		$sLibelleReferentiel	= DataHelper::get($aReferentiel,	"referentiel_libelle",				DataHelper::DATA_TYPE_STR);

		// Construction de la requête
		$aWhere					= array("id_" . $sTableReferentiel . " != %d" => $nIdReferentiel);
		$aSearch				= $this->findListeReferentielValid($sTableReferentiel, $aWhere);

		// Transformation du résultat de la requête sous forme de liste
		$aListeLibelle			= DataHelper::requestToList($aSearch, array('id_referentiel' => "libelle_referentiel"));

		// Fonctionnalité réalisée si le libellé existe déjà
		if (in_array($sLibelleReferentiel, $aListeLibelle)) {
			return $sLibelleReferentiel;
		} else {
			return false;
		}
	}

	/******************************************************************************************************
	 * @todo	CHARGER
	 ******************************************************************************************************/

	/**
	 * @brief	Chargement d'un référentiel enregistré en base de données.
	 *
	 * @param	string	$sTableReferentiel	: Nom de la table du référentiel à charger.
	 * @param	integer	$nIdReferentiel		: Identifiant du référentiel à charger.
	 * @return	array, tableau au format attendu par le formulaire HTML
	 * @code
	 * 	$aReferentiel = array(
	 * 		// GÉNÉRALITÉS ************************************************************************
	 * 		'referentiel_id'				=> "Identifiant du référentiel (en BDD)",
	 * 		'referentiel_libelle'			=> "Libellé du référentiel (en BDD)",
	 * 		'referentiel_description'		=> "Saisie libre pour la description du référentiel (en BDD)",
	 * 		'referentiel_parent'			=> "Identifiant du référentiel parent (en BDD)",
	 * 		'referentiel_date_debut'		=> "Date de début de prise en compte de l'entrée (en BDD)",
	 * 		'referentiel_date_fin'			=> "Date de fin de prise en compte de l'entrée (en BDD)",
	 * 		'referentiel_datetime'			=> "Date de modification de l'entrée (en BDD)"
	 * );
	 * @endcode
	 */
	public function charger($sTableReferentiel, $nIdReferentiel) {
		// Initialisation du référentiel
		$aData = array();

		try {
			// Initialisation du formulaire
			$aData['referentiel_table']				= $sTableReferentiel;
			$aData['referentiel_id']				= $nIdReferentiel;
			$aData['referentiel_parent']			= null;

			// Récupération des données du formulaire
			$aReferentiel							= $this->getReferentielById($sTableReferentiel, $nIdReferentiel);

			// Fonctionnalité réalisée si le référentiel n'est pas valide
			if (!DataHelper::isValidArray($aReferentiel)) {
				// Initialisation de l'identifiant
				$aData['referentiel_id']			= null;
			}

			// Chargement des données du formulaire
			$aData['referentiel_libelle']			= DataHelper::get($aReferentiel, 'libelle_referentiel',				DataHelper::DATA_TYPE_STR);
			$aData['referentiel_description']		= DataHelper::get($aReferentiel, 'description_referentiel',			DataHelper::DATA_TYPE_TXT);
			$aData['referentiel_date_debut']		= DataHelper::get($aReferentiel, 'date_debut_referentiel',			DataHelper::DATA_TYPE_DATE);
			$aData['referentiel_date_fin']			= DataHelper::get($aReferentiel, 'date_fin_referentiel',			DataHelper::DATA_TYPE_DATE);
			$aData['referentiel_datetime']			= DataHelper::get($aReferentiel, 'date_modification_referentiel',	DataHelper::DATA_TYPE_DATETIME);

			// Fonctionnalité réalisée si le référentiel courant est `salle`
			if ($sTableReferentiel == "salle") {
				$aData['statut_salle_id']			= DataHelper::get($aReferentiel, 'id_statut_salle',					DataHelper::DATA_TYPE_INT);
				$aData['statut_salle_capacite']		= DataHelper::get($aReferentiel, 'capacite_statut_salle',			DataHelper::DATA_TYPE_INT_ABS);
				$aData['statut_salle_examen']		= DataHelper::get($aReferentiel, 'examen_statut_salle',				DataHelper::DATA_TYPE_BOOL);
				$aData['statut_salle_informatique']	= DataHelper::get($aReferentiel, 'informatique_statut_salle',		DataHelper::DATA_TYPE_BOOL);
				$aData['statut_salle_reseau']		= DataHelper::get($aReferentiel, 'reseau_statut_salle',				DataHelper::DATA_TYPE_BOOL);
				$aData['statut_salle_reservable']	= DataHelper::get($aReferentiel, 'reservable_statut_salle',			DataHelper::DATA_TYPE_BOOL);
			}

			// Fonctionnalité réalisée si la table est liée à un parent
			if (array_key_exists($sTableReferentiel, self::$REF_TABLE_PARENT)) {
				// Récupération du nom de la table parent
				$aData['referentiel_parent']		= DataHelper::get($aReferentiel, 'id_parent',						DataHelper::DATA_TYPE_INT);
			}
		} catch (ApplicationException $e) {
			throw new ApplicationException($e->getMessage(), $e->getExtra());
		}

		// Renvoi du formulaire
		return $aData;
	}

	/***************************************************************************************************
	 * @todo	ENREGISTRER
	 ***************************************************************************************************/

	/**
	 * @brief	Enregistrement du LOG sur la table `log_referentiel`.
	 *
	 * @param	string	$sTable				: Nom de la table du referentiel en base de données.
	 * @param	integer	$nIdReponse			: Identifiant du referentiel en base de données.
	 * @param	array	$aQuery				: Tableau représentant la requête d'enregistrement en base de données.
	 * @param	boolean	$bFinalCommit		: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	protected function logReferentiel($sTable, $nId, $aQuery, $bFinalCommit = false) {
		// Récupération du type de l'action de la requête
		$sTypeAction = DataHelper::getTypeSQL($aQuery, true);

		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"type_action = :type_action,",
			"table_referentiel = :table_referentiel,",
			"id_referentiel = :id_referentiel,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':type_action'			=> $sTypeAction,
			':table_referentiel'	=> $sTable,
			':id_referentiel'		=> $nId,
			':id_utilisateur'		=> $this->_idUtilisateur
		);

		// Enregistrement du LOG
		return $this->logAction('log_referentiel', $aSet, $aBind, $bFinalCommit);
	}

	/**
	 * @brief	Récupère le statut de la salle.
	 *
	 * @li	Méthode privée employée uniquement par le référentiel relatif à la table `statut_salle`.
	 *
	 * @param	array	$aParams			: tableau des paramètres du référentiel `salle`.
	 * @return	integer, identifiant du statut
	 * @see		ReferentielManager::_updateStatutSalle()
	 */
	private function _getStatutSalleExists($nIdStatutSalle, $nIdSalle) {
		// Construction de la requête SELECT
		$sQuery	= "SELECT * FROM statut_salle
					WHERE id_statut_salle = :id_statut
					   OR id_salle = :id_salle
					ORDER BY id_salle, id_statut_salle
					LIMIT 1";

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_statut'	=> $nIdStatutSalle,
			':id_salle'		=> $nIdSalle
		);

		// Exécution de la requête et envoi du premier résultat
		return $this->executeSQL($sQuery, $aBind, 0);
	}

	/**
	 * @brief	Enregistrement du statut de la salle.
	 *
	 * @li	Méthode privée employée uniquement par le référentiel relatif à la table `salle`.
	 *
	 * @param	array	$aParams			: tableau des paramètres du référentiel `salle`.
	 * @return	integer, identifiant du statut.
	 * @see		ReferentielManager::enregistrer()
	 */
	private function _updateStatutSalle($aParams) {
		// Récupération de l'identifiant du statut de la salle
		$nIdStatutSalle	= DataHelper::get($aParams, 	"statut_salle_id",		DataHelper::DATA_TYPE_INT);

		// Récupération de l'identifiant de la salle
		$nIdSalle		= DataHelper::get($aParams, 	"referentiel_id",		DataHelper::DATA_TYPE_INT);

		// Recherche si l'enregistrement existe déjà
		$aSearch		= $this->_getStatutSalleExists($nIdStatutSalle, $nIdSalle);
		$nIdSearch		= DataHelper::get($aSearch,		"statut_salle_id",		DataHelper::DATA_TYPE_INT);

		// Récupération de l'identifiant du statut de la salle s'il est différent
		if (!empty($nIdSearch) && $nIdStatutSalle != $nIdSearch) {
			// Écrasement de la valeur de l'identifiant du statut
			$nIdStatutSalle = $nIdSearch;
		}

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_salle'			=> DataHelper::get($aParams,			"referentiel_id",				DataHelper::DATA_TYPE_INT),
			':capacite'			=> DataHelper::get($aParams,			"statut_salle_capacite",		DataHelper::DATA_TYPE_INT_ABS),
			':informatique'		=> DataHelper::get($aParams,			"statut_salle_informatique",	DataHelper::DATA_TYPE_BOOL),
			':reseau'			=> DataHelper::get($aParams,			"statut_salle_reseau",			DataHelper::DATA_TYPE_BOOL),
			':reservable'		=> DataHelper::get($aParams,			"statut_salle_reservable",		DataHelper::DATA_TYPE_BOOL),
			':examen'			=> DataHelper::get($aParams,			"statut_salle_examen",			DataHelper::DATA_TYPE_BOOL)
		);

		// Construction du tableau associatif les champs à enregistrer
		$aSet	= array(
			"id_salle = :id_salle,",
			"capacite_statut_salle = :capacite,",
			"informatique_statut_salle = :informatique,",
			"reseau_statut_salle = :reseau,",
			"reservable_statut_salle = :reservable,",
			"examen_statut_salle = :examen"
		);

		// Fonctionnalité de mise à jour du candidat
		if (DataHelper::isValidArray($aSearch)) {
			// Requête UPDATE
			$aInitQuery				= array("UPDATE statut_salle SET");

			// Finalisation de la collection $aSet[]
			$aSet[count($aSet)]		= "WHERE id_statut_salle = :id";
			$aBind[':id']			= $nIdStatutSalle;
		} else {
			// Requête INSERT
			$aInitQuery				= array("INSERT INTO statut_salle SET");
		}

		// Enregistrement du statut
		$nIdStatutSalle = $this->_save($aInitQuery, $aSet, $aBind);

		// Actualisation de l'identifiant
		if (!empty($nIdStatutSalle) && !is_bool($nIdStatutSalle)) {
			$aParams['statut_salle_id'] = $nIdStatutSalle;
		}

		// Renvoi de l'identifiant du statut
		return $aParams['statut_salle_id'];
	}

	/**
	 * @brief	Enregistrement d'un référentiel en base de données.
	 *
	 * @param	string	$sTableReferentiel	: nom de la table du référentiel à enregistrer.
	 * @param	array	$aReferentiel		: tableau des paramètres du référentiel à enregistrer.
	 * @code
	 * 	$aReferentiel = array(
	 * 		// GÉNÉRALITÉS ************************************************************************
	 * 		'referentiel_table'				=> "Nom de la table du référentiel (en BDD)",
	 * 		'referentiel_id'				=> "Identifiant du référentiel (en BDD)",
	 * 		'referentiel_libelle'			=> "Libellé du référentiel (en BDD)",
	 * 		'referentiel_description'		=> "Saisie libre pour la description du référentiel (en BDD)",
	 * 		'referentiel_parent'			=> "Identifiant du référentiel parent (en BDD)",
	 * 		'referentiel_date_debut'		=> "Date de début de prise en compte de l'entrée (en BDD)",
	 * 		'referentiel_date_fin'			=> "Date de fin de prise en compte de l'entrée (en BDD)",
	 * );
	 * @endcode
	 * @return	array
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function enregistrer($sTableReferentiel, $aReferentiel) {
		if (empty($aReferentiel['referentiel_table'])) {
			// Chargement du nom du référentiel
			$aReferentiel['referentiel_table'] = $sTableReferentiel;
		}

		// Récupération de l'identifiant du référentiel
		$nIdReferentiel			= DataHelper::get($aReferentiel, "referentiel_id",		DataHelper::DATA_TYPE_INT);

		// Recherche si l'enregistrement existe déjà
		$aSearch				= $this->getReferentielById($sTableReferentiel, $nIdReferentiel);

		// Recherche si un enregistrement en cours de validité porte déjà le libellé à enregistrer
		if ($sLibelle = $this->_isLibelleExists($sTableReferentiel, $aReferentiel)) {
			// Fonctionnalité réalisée si le libellé existe déjà
			throw new ApplicationException("Un référentiel porte déjà le libellé <span class=\"strong italic\">" . $sLibelle . "</span> !");
		}

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':libelle'			=> DataHelper::get($aReferentiel,		"referentiel_libelle",			DataHelper::DATA_TYPE_MYTXT),
			':description'		=> DataHelper::get($aReferentiel,		"referentiel_description",		DataHelper::DATA_TYPE_MYTXT),
			':date_debut'		=> DataHelper::get($aReferentiel,		"referentiel_date_debut",		DataHelper::DATA_TYPE_MYDATE),
			':date_fin'			=> DataHelper::get($aReferentiel,		"referentiel_date_fin",			DataHelper::DATA_TYPE_MYDATE)
		);

		// Fonctionnalité réalisée si le libellé n'est pas renseigné
		if (empty($aBind[':libelle'])) {
			throw new ApplicationException("Veuillez renseigner le libellé !");
		}

		// Construction du tableau associatif les champs à enregistrer dans l'ordre
		$aSet	= array(
			0 => null,	/** @todo	Renseignement du référentiel parent **************************/
			1 => "libelle_" . $sTableReferentiel . " = :libelle,",
			2 => "description_" . $sTableReferentiel . " = :description,",
			3 => null, /** @todo	Renseignement d'un champ supplémentaire **********************/
			4 => "date_debut_" . $sTableReferentiel . " = DATE_FORMAT(:date_debut, '%Y-%m-%d'),",
			5 => "date_fin_" . $sTableReferentiel . " = DATE_FORMAT(:date_fin, '%Y-%m-%d')"
		);

		// Fonctionnalité réalisée si la table est liée à une table parent
		if (array_key_exists($sTableReferentiel, self::$REF_TABLE_PARENT)) {
			// Récupération de la valeur du référentiel parent
			$nIdParent			= DataHelper::get($aReferentiel,		"referentiel_parent",		DataHelper::DATA_TYPE_INT);

			// Fonctionnalité réalisée si le référentiel parent n'est pas renseigné
			if (empty($nIdParent)) {
				throw new ApplicationException("Veuillez sélectionner un référentiel parent !");
			}

			// Récupération de la table parent
			$sTableParent		= self::$REF_TABLE_PARENT[$sTableReferentiel];

			// Ajout de la référence du parent dans les étiquettes
			$aSet[0] = sprintf("id_%s = :id_parent,", $sTableParent);
			$aBind[':id_parent']	= $nIdParent;
		}

		// Fonctionnalité de mise à jour du candidat
		if (DataHelper::isValidArray($aSearch)) {
			// Requête UPDATE
			$aInitQuery				= array("UPDATE $sTableReferentiel SET");

			// Finalisation de la collection $aSet[]
			$aSet[count($aSet)]		= sprintf("WHERE id_%s = :id", $sTableReferentiel);
			$aBind[':id']			= $nIdReferentiel;
		} else {
			// Requête INSERT
			$aInitQuery				= array("INSERT INTO $sTableReferentiel SET");
		}

		// Enregistrement du référentiel
		$nIdReferentiel = $this->_save($aInitQuery, $aSet, $aBind);

		// Actualisation de l'identifiant
		if (!empty($nIdReferentiel) && !is_bool($nIdReferentiel)) {
			$aReferentiel['referentiel_id'] = $nIdReferentiel;
		}

		// Fonctionnalité réalisée si la table correspond à `salle`
		if ($sTableReferentiel == self::TABLE_SALLE) {
			// Mise à jour des paramètres de la salle
			$aReferentiel['statut_salle_id'] = $this->_updateStatutSalle($aReferentiel);
		}

		// Enregistrement du LOG en base de données
		$this->logReferentiel($sTableReferentiel, $aReferentiel['referentiel_id'], $aInitQuery, true);

		// Renvoi des données du référentiel
		return $aReferentiel;
	}

	/**
	 * @brief	Suppression des relations avec le référentiel.
	 *
	 * @li	Méthode privée de suppression des relations entre les tables.
	 *
	 * @param	string	$sTableReferentiel	: Nom du référentiel à purger.
	 * @param	integer	$nIdReferentiel		: Identifiant du référentiel à purger.
	 * @return	boolean, résultat de la suppression.
	 */
	public function _purgeTableLink($sTableReferentiel, $nIdReferentiel) {
		// Fonctionnalité réalisée pour la suppression en cascade
		switch ($sTableReferentiel) {

			case self::TABLE_SALLE:
				// Suppression de la liaison avec la table `statut_salle`
				$sTable = "statut_salle";
				// Selon l'identifiant de la salle
				$sField	= "id_salle";
				break;

			default:
				return false;
				break;
		}

		// Construction de la requête DELETE
		$sQuery			= sprintf('DELETE FROM %s WHERE %s = :id', $sTable, $sField);
		$aBind[':id']	= $nIdReferentiel;

		// Exécution de la relation
		return $this->_delete($sQuery, $aBind);
	}

	/**
	 * @brief	Suppression du référentiel.
	 *
	 * @param	string	$sTableReferentiel	: Nom du référentiel à supprimer.
	 * @param	integer	$nIdReferentiel		: Identifiant du référentiel à supprimer.
	 * @return	boolean, résultat de la suppression.
	 */
	public function deleteReferentiel($sTableReferentiel, $nIdReferentiel) {
		// Fonctionnalité réalisée pour la suppression en cascade
		switch ($sTableReferentiel) {

			// Suppression de la liaison avec la table `statut_salle`
			case self::TABLE_SALLE:
				$this->_purgeTableLink($sTableReferentiel, $nIdReferentiel);
				break;

			default:
				break;
		}

		// Requête DELETE
		$aQuery = array(
			"DELETE FROM $sTableReferentiel",
			"WHERE id_$sTableReferentiel = :id_referentiel"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			":id_referentiel"	=> $nIdReferentiel
		);

		// Exécution de la requête
		$bExec	= $this->_delete($aQuery, $aBind);

		// Enregistrement du LOG en base de données
		$this->logReferentiel($sTableReferentiel, $nIdReferentiel, $aQuery, true);

		// Renvoi des données de suppression
		return $bExec;
	}
}
