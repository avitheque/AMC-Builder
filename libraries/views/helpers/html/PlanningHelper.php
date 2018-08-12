<?php
/**
 * @brief	Helper de création d'un planning.
 *
 * @name		PlanningHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 143 $
 * @since		$LastChangedDate: 2018-08-12 20:20:22 +0200 (Sun, 12 Aug 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class PlanningHelper {

	/**
	 * Constante de construction de la liste des identifiants à exclure du résultat.
	 * @var		char
	 */
	const		EXCLUDE_SEPARATOR					= ",";

	/**
	 * Constante de construction de la liste des jours et heures non travaillées.
	 * @var		char
	 */
	const		DEPRECATED_LIST_SEPARATOR			= ",";
	const		DEPRECATED_ITEM_SEPARATOR			= "-";

	/**
	 * Constante de construction des jours du planning.
	 * @var		integer
	 */
	const		PLANNING_HEPHEMERIDE				= 86400;
	const		PLANNING_REPAS_HEURE				= 13;
	const		PLANNING_REPAS_DUREE				= 1;

	/**
	 * Constante de construction de la liste des éléments du formulaire de recherche.
	 * @var		string
	 */
	const		TYPE_SELECT							= "select";
	const		TYPE_NUMBER							= "number";
	const		TYPE_TEXT							= "text";

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	protected	$_oInstanceStorage					= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	protected	$_title								= "Planning de la semaine";

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	protected	$_aForm								= array();

	/**
	 * @brief	Message de résultat non trouvé.
	 * @var		string
	 */
	protected	$_empty								= "Aucun résultat n'a été trouvé...";

	/**
	 * @brief	Conteneur HTML de l'élément SOURCE.
	 * @var		string
	 */
	protected	$item								= "";

	/**
	 * @brief	Conteneur HTML du panneau CIBLE.
	 * @var		string
	 */
	protected	$_semaine							= array();
	protected	$planning							= "";

	/**
	 * @brief	Indicateur de construction.
	 * @var		bool
	 */
	protected	$_build								= false;

	/**
	 * @brief	Liste des éléments sous forme de collection.
	 * @var		array
	 */
	protected	$_aItems							= array();

	/**
	 * @brief	Liste des identifiants à exclure de la collection.
	 * @var		array
	 */
	protected	$_exclude							= array();

	/**
	 * @brief	Constantes des paramètres de construction de PlanningHelper.
	 * @var		integer|string
	 */
	const		PLANNING_DAYS						= 7;
	const		PLANNING_HOUR_START					= 8;
	const		PLANNING_HOUR_END					= 18;
	const		PLANNING_TIMER_SIZE					= 60;				// Taille d'un bloc en minutes
	const		PLANNING_MAX_WIDTH					= 90;				// Ratio de l'affichage en %
	const		PLANNING_DATE_FORMAT				= "d/m/o";
	const		PLANNING_WEEK_FORMAT				= "W";
	const		PLANNING_TIME_FORMAT				= "%02d:%02d";

	protected	$_planning_annee					= 1970;
	protected	$_planning_mois						= 1;
	protected	$_timestamp_debut					= 0;
	protected	$_timestamp_fin						= 604800;
	protected	$_planning_jour						= 1;
	protected	$_planning_jour_id					= 1;
	protected	$_planning_jour_width				= 100;
	protected	$_planning_duree					= self::PLANNING_DAYS;
	protected	$_planning_duree_cours				= 50;
	protected	$_planning_debut					= self::PLANNING_HOUR_START;
	protected	$_planning_repas_heure				= self::PLANNING_REPAS_HEURE;
	protected	$_planning_repas_duree				= self::PLANNING_REPAS_DUREE;
	protected	$_planning_fin						= self::PLANNING_HOUR_END;
	protected	$_planning_timer_size				= self::PLANNING_TIMER_SIZE;
	protected	$_planning_deprecated_dates			= array();

	/**
	 * @brief	Liste des noms de jours dans la semaine.
	 * @var		array
	 */
	protected	$_liste_planning_semaine			= array(
		1 => "Lundi",
		2 => "Mardi",
		3 => "Mercredi",
		4 => "Jeudi",
		5 => "Vendredi",
		6 => "Samedi",
		7 => "Dimanche"
	);

	const		DEFAULT_CELL_WIDTH					= 50;
	protected	$_nCellWidth						= self::DEFAULT_CELL_WIDTH;
	protected	$_md5								= '1234567890';

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	date	$dDateStart					: Date de début du planning [Y-m-d], possibilité de donner une date au format [jj/mm/aaaa].
	 * @param	integer	$nNbDays					: Nombre de jours à afficher [1-7].
	 * @param	integer	$nStartHour					: Heure de début pour chaque jour.
	 * @param	integer	$nEndHour					: Heure de fin pour chaque jour.
	 * @return	string
	 */
	public function __construct($dDateStart = null, $nNbDays = self::PLANNING_DAYS, $nStartHour = self::PLANNING_HOUR_START, $nEndHour = self::PLANNING_HOUR_END) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage					= InstanceStorage::getInstance();

		// Construction du MD5 à partir des paramètres d'entrée
		$this->_md5									= md5($dDateStart . $nNbDays . $nStartHour . $nEndHour . time());

		// Récupération de la date de début au format MySQL [Y-m-d]
        $sDateStart                                 = DataHelper::dateFrToMy($dDateStart);
        list($annee, $mois, $jour)					= explode('-', $sDateStart);

        // Initialisation de l'objet DateTime à partir de la date au format MySQL [Y-m-d]
		$oDateTime									= new DateTime($sDateStart);
		$this->_timestamp_debut						= $oDateTime->getTimestamp();

        // Récupération de la date de fin par addition du nombre de jours
		$oDateTime->modify("+ " . ($nNbDays - 1) . " days");
		$this->_timestamp_fin						= $oDateTime->getTimestamp();

		// Initialisation des paramètres de progression
		$this->_planning_annee						= $annee;
		$this->_planning_mois						= $mois;
		$this->_planning_jour						= $jour;

		// Initialisation des paramètres de progression
		$this->_planning_duree						= $nNbDays;
		$this->_planning_debut						= $nStartHour;
		$this->_planning_fin						= $nEndHour;

		// Découpage du volume horaire
		$this->_volume_horaire						= $this->_planning_fin - $this->_planning_debut;
		$this->_tranche_horaire						= $this->_planning_timer_size / 60;
	}

	/**
	 * @brief	Ajoute une date non travaillée à la liste.
	 *
	 * @param	date	$dDate						: Date à ajouter à la collection.
	 * @return	void
	 */
	public function addDateToDeprecated($dDate) {
		// Fonctionnalité réalisée si la DATE est un TIMESTAMP
		if (DataHelper::isValidNumeric($dDate)) {
			// La DATE correspond au TIMESTAMP
			$nTimeStamp								= $dDate;
			// Récupération de la DATE au format [Y-m-d]
			$dDateMySQL								= date("Y-m-d", $dDate);
		} else {
			// Formatage de la DATE au format [Y-m-d]
			$dDateMySQL								= DataHelper::dateFrToMy($dDate);
			// Extraction des éléments de la DATE
			list($y, $m, $d)						= explode("-", $dDateMySQL);
			// Convertion au format TIMESTAMP
			$nTimeStamp								= mktime(0, 0, 0, $m, $d, $y);
		}
		$this->_planning_deprecated_dates[$nTimeStamp] = $dDateMySQL;
	}

	/**
	 * @brief	Test deux éléments du PLANNING.
	 *
	 * @li	Exploitation de Planning_ItemHelper.
	 *
	 * @param	object	$oItemA						: Instance de Planning_ItemHelper.
	 * @param	object	$oItemB						: Instance de Planning_ItemHelper.
	 * @return	boolean
	 */
	private function _isItemIdentiqual(Planning_ItemHelper $oItemA, Planning_ItemHelper $oItemB) {
		// Initialisation du résultat
		$bValide									= true;
		// Parcours de la listes des champs identifiants deux éléments
		foreach (Planning_ItemHelper::$LIST_ITEM_LABEL as $sLabel) {
			// Fonctionnalité réalisée si les éléments diffèrent
			if ($oItemA->$sLabel != $oItemB->$sLabel) {
				$bValide							= false;
			}
		}
		// Renvoi du résultat
		return $bValide;
	}

	/**
	 * @brief	Ajout d'un élément.
	 *
	 * @li	Contrôle que l'identifiant de l'élément n'est pas à exclure.
	 *
	 * @example	Exemple d'utilisation avec l'ajout d'un texte et d'une image
	 * @code
	 * 		// Création d'un nouveau PLANNING
	 * 		$oPanning->new PlanningHelper("2017-01-02", 7, 8, 18, "8,13,18", "5:16-23,6,7", 60);
	 *
	 * 		// Création d'une nouvelle entrée
	 * 		$oItem = new Panning_ItemHelper();
	 * 		// Lors du clic sur le [ZOOM] le contenu du MODAL sera chargé avec le contenu de l'URL "/search/question?id=15"
	 * 		$oItem->setContent("<span class=\"strong\">Contenu de l'élément</span><img src=\"/images/logo.png\" alt=\"Logo\" />", 15, "/search/question?id=%d");
	 *
	 *		// Ajout de l'entrée au PLANNING
	 * 		$oPanning->addItem($oItem);
	 *
	 * 		// Récupération du panneau dans le VIEW_MAIN
	 * 		ViewRender::addToMain($oPanning->renderHTML());
	 * @endcode
	 *
	 * @param	object	$oItem						: Entrée du planning sous forme d'instance de Planning_ItemHelper.
	 * @return	void
	 */
	public function addItem(Planning_ItemHelper $oItem) {
		// Récupération du titre
		$sTitre										= $oItem->getTitle();
		$nDuration									= $oItem->getDuration();

		// Initalisation de l'identifiant de l'entrée à partir des paramètres de la DATE
		$sDateMySQL									= $oItem->getDate("Y-m-d");
		$sTimeMySQL									= $oItem->getTime("H:i");

		// Fonctionnalité réalisée si l'entrée de la collection n'existe pas encore
		if (!array_key_exists($sDateMySQL, $this->_aItems)) {
			// Initialisation de la collection
			$this->_aItems[$sDateMySQL]				= array();
		} else {
			$nHour									= $oItem->getTime("H");
			$nDuration								= $oItem->getDuration();
			while ($nHour > $this->_planning_debut) {
				$nHour--;

				//array_key_exists($sTimeBeforeMySQL, $this->_aItems[$sDateMySQL])) {
				$sTimeBeforeMySQL					= sprintf("%02d:%02d", $nHour, $oItem->getTime("i"));

				// Fonctionnalité réalisée si la nouvelle cellule est la continuité du cours précédent
				if (array_key_exists($sTimeBeforeMySQL, $this->_aItems[$sDateMySQL])) {
					$oItemBefore					= $this->_aItems[$sDateMySQL][$sTimeBeforeMySQL];

					$sTitreBefore					= $oItemBefore->getTitle();
					$sDurationBefore				= $oItemBefore->getDuration();

					// La tâche est identique
					if ($this->_isItemIdentiqual($oItem, $oItemBefore)) {
						// Suppression de l'élément précédent
						unset($this->_aItems[$sDateMySQL][$sTimeMySQL]);

						// Mise à jour de la durée
						$nDuration					+= $oItemBefore->getDuration();
						$oItem->setDuration($nDuration);

						// Mise à jour de l'heure de début
						$sTimeMySQL					= $sTimeBeforeMySQL;
						$oItem->setFullTime($sTimeMySQL);
					}
				} elseif ($nHour == $this->_planning_repas_heure) {
					// Pause méridienne
					break;
				}
			}
		}

		// Ajout de l'élément à la collection
		$this->_aItems[$sDateMySQL][$sTimeMySQL]	= $oItem;
	}

	/**
	 * @brief	Initialisation du message de résultat vide.
	 *
	 * @param	string	$sEmptyMessage				: texte à afficher si aucun résultat n'est trouvé.
	 * @return	void
	 */
	public function setEmpty($sEmptyMessage = null) {
		$this->_empty								= $sEmptyMessage;
	}

	/**
	 * @brief	Initialisation de la liste des identifiants à exclure.
	 *
	 * @param	array	$aListExcludeId				: Tableau contenant l'ensemble des identifiants à ne pas prendre en compte.
	 * @return	void
	 */
	public function setExcludeByListId($aListExcludeId = array()) {
		$this->_exclude								= $aListExcludeId;
	}

}
