<?php
/**
 * @brief	Helper de création d'un planning.
 *
 * Vue étendue du formulaire permettant d'ajouter des éléments par cliqué/glissé.
 *
 * @name		PlanningHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 15 $
 * @since		$LastChangedDate: 2017-04-29 21:33:00 +0200 (sam., 29 avr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PlanningHelper {

	/**
	 * Constante de construction de la liste des identifiants à exclure du résultat.
	 * @var		char
	 */
	const		EXCLUDE_SEPARATOR			= ",";

	/**
	 * Constante de construction de la liste des jours et heures non travaillées.
	 * @var		char
	 */
	const		DEPRECATED_LIST_SEPARATOR	= ",";
	const		DEPRECATED_ITEM_SEPARATOR	= "-";

	/**
	 * Construction de l'interface graphique
	 * var		PLANNING_DEFAULT_FORMAT		: type visuel de rendu parmis [calendrier, progression]
	 */
	const		FORMAT_CALENDAR				= "calendar";
	const		FORMAT_PROGRESSION			= "progression";
	const		PLANNING_DEFAULT_CLASSNAME	= "diary";
	const		PLANNING_HOLIDAY_CLASSNAME	= "diary holiday";
	const		PLANNING_HEADER_CLASSNAME	= "header";

	const 		PLANNING_VALID_CLASS		= "ui-widget-content ui-state-default";
	const 		PLANNING_DEPRECATED_CLASS	= "ui-widget-content ui-state-disabled";
	const		PLANNING_WIDTH_RATIO		= 99;
	const		PLANNING_DEFAULT_FORMAT		= self::FORMAT_CALENDAR;

	/**
	 * Constante de construction de la liste des éléments du formulaire de recherche.
	 * @var		string
	 */
	const		TYPE_SELECT					= "select";

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage			= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	private		$_title						= "Jour de la semaine";

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	private		$_aForm						= array();

	/**
	 * @brief	Message de résultat non trouvé.
	 * @var		string
	 */
	private		$_empty						= "Aucun résultat n'a été trouvé...";

	/**
	 * @brief	Conteneur HTML de l'élément SOURCE.
	 * @var		string
	 */
	protected	$item						= "";

	/**
	 * @brief	Conteneur HTML du panneau CIBLE.
	 * @var		string
	 */
	protected	$planning					= "";

	/**
	 * @brief	Indicateur de construction.
	 * @var		bool
	 */
	private		$_build						= false;

	/**
	 * @brief	Liste des éléments sous forme de gallerie.
	 * @var		array
	 */
	private		$_aItems					= array();

	/**
	 * @brief	Liste des identifiants à exclure de la collection.
	 * @var		array
	 */
	private		$_exclude					= array();

	const		PLANNING_DAYS				= 7;
	const		PLANNING_HOUR_START			= 8;
	const		PLANNING_HOUR_END			= 18;
	const		PLANNING_DEPRECATED_DAYS	= "6-7";			// Jour(s) de la semaine non travaillé(s) [1-7] : 1 pour Lundi à 7 pour Dimanche
	const		PLANNING_DEPRECATED_HOURS	= "0-8,13,18-23";	// Heure(s) non travaillée(s)
	const		PLANNING_TIMER_SIZE			= 60;				// Taille d'un bloc en minutes
	const		PLANNING_MAX_WIDTH			= 90;				// Ratio de l'affichage en %
	private		$_planning_format			= self::PLANNING_DEFAULT_FORMAT;
	private		$_planning_annee			= 1970;
	private		$_planning_mois				= 1;
	private		$_planning_jour				= 1;
	private		$_planning_jour_id			= 1;
	private		$_planning_jour_width		= 100;
	private		$_planning_duree			= self::PLANNING_DAYS;
	private		$_planning_debut			= self::PLANNING_HOUR_START;
	private		$_planning_fin				= self::PLANNING_HOUR_END;
	private		$_planning_timer_size		= self::PLANNING_TIMER_SIZE;
	private		$_planning_deprecated_days	= self::PLANNING_DEPRECATED_DAYS;
	private		$_planning_deprecated_hours	= self::PLANNING_DEPRECATED_HOURS;
	private		$_planning_deprecated_dates	= array();

	/**
	 * @brief	Liste des noms de jours dans la semaine.
	 * @var		array
	 */
	private		$_planning_semaine			= array(
		1 => "Lundi",
		2 => "Mardi",
		3 => "Mercredi",
		4 => "Jeudi",
		5 => "Vendredi",
		6 => "Samedi",
		7 => "Dimanche"
	);

	/**
	 * @brief	Liste des noms de jours dans la semaine au format court.
	 * @var		array
	 */
	private		$_planning_semaine_court	= array(
		1 => "L",
		2 => "M",
		3 => "M",
		4 => "J",
		5 => "V",
		6 => "S",
		7 => "D"
	);

	const		DEFAULT_CELL_WIDTH			= 50;
	private		$_nCellWidth				= self::DEFAULT_CELL_WIDTH;

	const		ITEM_FORMAT					= 'item-%s';
	const		PLANNING_FORMAT				= 'planning-%d40-%d20-%d20';
	private		$_md5						= '1234567890';
	private		$_id_aItems					= 'item-1234567890';
	private		$_id_planning				= 'planning-1234567890';

	const		MODAL_ACTION_DEFAULT		= '/planning/tache';
	private		$_modal						= null;
	private		$_modal_action				= self::MODAL_ACTION_DEFAULT;

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	boolean	$bReadonly			: Verrouillage de la modification des champs.
	 * @param	date	$dDateStart			: Date de début du planning [Y-m-d], possibilité de donner une date au format [jj/mm/aaaa].
	 * @param	integer	$nNbDays			: Nombre de jours à afficher [1-7].
	 * @param	integer	$nStartHour			: Heure de début pour chaque jour.
	 * @param	integer	$nEndHour			: Heure de fin pour chaque jour.
	 * @param	string	$sDeprecatedHours	: Liste d'heures non travaillées, séparées par le caractère [,].
	 * @param	integer	$nTimerSize			: Nombre de minutes par bloc.
	 * @return	string
	 */
	public function __construct($dDateStart = null, $nNbDays = self::PLANNING_DAYS, $nStartHour = self::PLANNING_HOUR_START, $nEndHour = self::PLANNING_HOUR_END, $sDeprecatedHours = self::PLANNING_DEPRECATED_HOURS, $sDeprecatedDays = self::PLANNING_DEPRECATED_DAYS, $nTimerSize = self::PLANNING_TIMER_SIZE) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage			= InstanceStorage::getInstance();

		// Construction du MD5 à partir des paramètres d'entrée
		$this->_md5							= md5($dDateStart . $nNbDays . $nStartHour . $nEndHour . $sDeprecatedHours . $sDeprecatedDays);

		// Récupération de la date de début
		$sDateStart							= DataHelper::dateFrToMy($dDateStart);
		list($annee, $mois, $jour)			= explode('-', $sDateStart);

		// Initialisation des paramètres de progression
		$this->_planning_annee				= $annee;
		$this->_planning_mois				= $mois;
		$this->_planning_jour				= $jour;

		// Initialisation des paramètres de progression
		$this->_planning_duree				= $nNbDays;
		$this->_planning_debut				= $nStartHour;
		$this->_planning_fin				= $nEndHour;
		$this->_planning_deprecated_hours	= DataHelper::getArrayFromList($sDeprecatedHours,	self::DEPRECATED_LIST_SEPARATOR,	self::DEPRECATED_ITEM_SEPARATOR);
		$this->_planning_deprecated_days	= DataHelper::getArrayFromList($sDeprecatedDays,	self::DEPRECATED_LIST_SEPARATOR,	self::DEPRECATED_ITEM_SEPARATOR);
		$this->_planning_timer_size			= $nTimerSize;

		//#################################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#################################################################################################

		// Nom de session des données
		$sSessionNameSpace					= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Données du formulaire
		$this->_aForm						= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Initialisation du conteneur
		$this->item							= "<ul id=\"planning-item-" . $this->_md5 . "\" class=\"planning-item ui-helper-reset ui-helper-clearfix max-width\">";
	}

	/**
	 * @brief	Initialisation de la largeur d'une cellule de plannification
	 *
	 * @param	integer	$nWidth				: Largeur en pixels.
	 * @return	string
	 */
	public function setCellWidth($nWidth = self::DEFAULT_CELL_WIDTH) {
		$this->_nCellWidth = $nWidth;
	}

	/**
	 * @brief	Initialisation du titre du panneau.
	 *
	 * @param	string	$sTitle				: titre du panneau.
	 * @return	void
	 */
	public function setTitre($sTitle = null) {
		$this->_title	= $sTitle;
	}

	/**
	 * @brief	Initialisation du message de résultat vide.
	 *
	 * @param	string	$sEmptyMessage		: texte à afficher si aucun résultat n'est trouvé.
	 * @return	void
	 */
	public function setEmpty($sEmptyMessage = null) {
		$this->_empty	= $sEmptyMessage;
	}

	/**
	 * @brief	Initialisation de la liste des identifiants à exclure.
	 *
	 * @param	array	$aListExcludeId		: Tableau contenant l'ensemble des identifiants à ne pas prendre en compte.
	 * @return	void
	 */
	public function setExcludeByListId($aListExcludeId = array()) {
		$this->_exclude = $aListExcludeId;
	}

	/**
	 * @brief	Ajoute une date non travaillée à la liste.
	 *
	 * @param	date	$dDate				: Date à ajouter à la collection.
	 * @return	void
	 */
	public function addDateToDeprecated($dDate) {
		// Fonctionnalité réalisée si la DATE est un TIMESTAMP
		if (DataHelper::isValidNumeric($dDate)) {
			// La DATE correspond au TIMESTAMP
			$nTimeStamp = $dDate;
			// Récupération de la DATE au format [Y-m-d]
			$dDateMySQL	= date("Y-m-d", $dDate);
		} else {
			// Formatage de la DATE au format [Y-m-d]
			$dDateMySQL = DataHelper::dateFrToMy($dDate);
			// Extraction des éléments de la DATE
			list($y, $m, $d) = explode($dDateMySQL);
			// Convertion au format TIMESTAMP
			$nTimeStamp = mktime(0, 0, 0, $m, $d, $y);
		}
		$this->_planning_deprecated_dates[$nTimeStamp] = $dDateMySQL;
	}

	/**
	 * @brief	Ajout d'un élément.
	 *
	 * @li	Contrôle que l'identifiant de l'élément n'est pas à exclure.
	 *
	 * @example	Exemple d'utilisation avec l'ajout d'un texte et d'une image
	 * @code
	 * 		// Création d'une nouvelle bibliothèque
	 * 		$oPanning = new PanningHelper();
	 *
	 * 		// Lors du clic sur le [ZOOM] le contenu du modal sera chargé avec le contenu de l'URL "/search/question?id=15"
	 * 		$oPanning->addItem("<span class=\"strong\">Contenu de l'élément</span><img src=\"/images/logo.png\" alt=\"Logo\" />", 15, "/search/question?id=%d");
	 *
	 * 		// Récupération du panneau dans le VIEW_MAIN
	 * 		ViewRender::addToMain($oPanning->renderHTML());
	 * @endcode
	 *
	 * @param	string	$sHtml				: Contenu HTML à ajouter.
	 * @param	mixed	$xId				: Identifiant de l'élément.
	 * @param	string	$sHrefZoomIn		: Format du chemin à réaliser lors du clic sur le Zoom.
	 * @return	void
	 */
	public function addItem($sHtml, $xId = null, $sHrefZoomIn = "/index?id=%") {
		// Fonctionnalité réalisée si l'identifiant n'est pas déjà présent dans le questionnaire
		if (! in_array($xId, $this->_exclude)) {
			// Création d'un nouvel élément
			$oItem = new Planning_ItemHelper($sHtml, $xId, $sHrefZoomIn);
			// Ajout de l'élément à la collection
			$this->_aItems[] = $oItem->renderHTML();
		}
	}

	public function setModalAction($url) {
		$this->_modal_action = $url;
	}

	public function setPlanningFormat($sFormat = self::PLANNING_DEFAULT_FORMAT) {
		$this->_planning_format = $sFormat;
	}

	/**
	 * @brief	Construction du formulaire de recherche
	 *
	 * @li	La(Les) liste(s) des champs SELECT transite(nt) dans les paramètres de l'instance InstanceStorage.
	 * @li	Un champ caché [exclude] permet de lister les identifiants à ne pas récupérer.
	 *
	 * @param	string	$sAction			: action du formulaire.
	 * @param	array	$aSearchItems		: tableau BIDIMENTIONNEL contenant les éléments du moteur de recherche.
	 * 	Chaque entrée possède les éléments suivants :
	 * 		- string	'default'			: valeur par défaut du champ HTML ;
	 * 		- string	'index'				: occurence de l'élément (exploité dans le cas d'un champ SELECT) ;
	 * 		- string	'label'				: libellé du champ HTML ;
	 * 		- string	'type'				: type de champ HTML.
	 * 		- string	'value'				: valeur du champ HTML.
	 * @return	void
	 */
	private function _buildSearchForm($sAction, $aSearchItems = array()) {
		// Initialisation du formulaire de recherche
		$sSearch	= "<form action=\"" . $sAction . "\" method=\"post\" name=\"planning-" . $this->_md5 . "\" id=\"search-planning-" . $this->_md5 . "\" class=\"no-wrap blue max-width\">
						<br />
						<fieldset>
							<legend>Propriétés de l'élément</legend>
							<ul class=\"margin-H-10p\">";

		// Initialisation des paramètres exploités par AJAX
		$aDataAJAX	= array();

		// Parcours de l'ensemble des listes du formulaire
		foreach ((array) $aSearchItems as $sName => $aItem) {
			// Initialisation des paramètres des champs
			$sId			= "id_" . $sName;
			$sLabel			= isset($aItem['label'])	? $aItem['label']				: "";
			$sIndex			= isset($aItem['index'])	? $aItem['index']				: 0;
			$sType			= isset($aItem['type'])		? strtolower($aItem['type'])	: InputHelper::TYPE_TEXT;
			$sValue			= isset($aItem['value'])	? strtolower($aItem['value'])	: InputHelper::TYPE_TEXT;

			if (isset($aItem['default'])) {
				// Récupération du champ de référence du formulaire pour la valeur par défaut
				$sDefault		= isset($aItem['default']) ? $aItem['default'] : null;

				// Détermination de la valeur par défaut d'après le champ du formulaire de référence
				$sValue			= DataHelper::get($this->_aForm, $sDefault, DataHelper::DATA_TYPE_TXT, $sValue);
			}

			// Construction de l'ensemble HTML
			$sSearch		.= "<li>";

			// Traitement selon le type de l'élément HTML
			switch ($sType) {

				case self::TYPE_SELECT:
					// Construction de la liste des options du champ SELECT
					$sOptions	= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData($sIndex), $sValue, '-');

					// Ajout du champ SELECT
					$sSearch	.= "<label for=\"" . $sId . "\" class=\"strong width-150-min width-30p\">" . $sLabel . "</label>";
					$sSearch	.= "<select id=\"" . $sId . "\" name=\"" . $sName . "\" class=\"width-50p\">" . $sOptions . "</select>";
					break;

				default:
					// Construction du champ INPUT
					$oInput		= new InputHelper($sName, $sValue, $sType, $sLabel, "strong center max-width");
					$oInput->setId($sId);

					// Ajout du champ INPUT
					$sSearch	.= $oInput->renderHTML();
					break;
			}

			// Ajout de l'entrée pour les options AJAX
			if ($sType == InputHelper::TYPE_CHECKBOX) {
				// Renvoi un bouléen si le champ est coché
				$aDataAJAX[$sId]	= $sName . ': $("#' . $sId . '", "#search-planning-' . $this->_md5 . '").is(":checked")';
			} else {
				// Renvoi la valeur du champ
				$aDataAJAX[$sId]	= $sName . ': $("#' . $sId . '", "#search-planning-' . $this->_md5 . '").val()';
			}

			// Finalisation
			$sSearch		.= "</li>";
		}

		// Ajout de la liste des identifiants exclus dans une entrée cachée qui sera exploitée par AJAX
		$sSearch	.= "<input type=\"hidden\" name=\"exclude-" . $this->_md5 . "\" value=\"" . implode(self::EXCLUDE_SEPARATOR, $this->_exclude) . "\" />";

		// Ajout de l'entrée cachée aux options AJAX
		$aDataAJAX[]= 'exclude: $("input[name=exclude-' . $this->_md5 . ']").val()';

		// Finalisation du formulaire
		$sSearch			.= "</ul>
							<div class=\"margin-20\">
								<button type=\"reset\" id=\"reset-item-" . $this->_md5 . "\" class=\"left no-margin red\">Annuler</button>
								<button type=\"button\" id=\"search-item-" . $this->_md5 . "\" class=\"right no-margin blue\">Rechercher</button>
							</div>
						</fieldset>
					</form>";

		// Ajout du script d'ouverture
		$sJQuery = '// Action sur le bouton [Rechercher] du MODAL
					$("button#search-item-' . $this->_md5 . '").on("click", function() {
						// Affichage de la bibliothèque
						$.ajax({
							async:		false,
							type:		"POST",
							dataType:	"HTML",
							url:		"' . $this->_modal_action . '",
							data:		{' . implode(",", $aDataAJAX) . '},
							success:	function(html) {
								// Chargement du contenu trouvé
								$("ul#planning-item-' . $this->_md5 . '").html(html);
							},
							complete:	function() {
								// Initialisation de la fonctionnalité de planification
								initPlanning("' . $this->_md5 . '");
							}
						});
					});
					
					// Action sur le bouton [Annuler] de la Gallerie
					$("button#reset-item-' . $this->_md5 . '").on("click", function() {
						// Suppression du contenu
						$("ul#planning-item-' . $this->_md5 . '").html("");
					});';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);

		// Renvoi du contenu HTML
		return $sSearch;
	}

	/**
	 * @brief	Construction de la progression du jour.
	 *
	 * @param	string	$IdProgression		: identifiant de la CELLULE.
	 * @param	string	$sTitreJour			: titre du jour.
	 * @param	string	$sLibelleJour		: libellé du jour.
	 * @param	string	$sLibelleDate		: libellé de la date.
	 * @param	string	$sDiaryStyle		: style CSS attribué au jour.
	 * @param	string	$sClassDefault		: classe CSS global par défault.
	 * @param	string	$sClassItem			: classe CSS de l'élément.
	 * @param	string	$sClassName			: nom de la classe CSS de la progression.
	 */
	private function _buildProgression($sClassName = self::PLANNING_HEADER_CLASSNAME, $IdProgression = 0, $dDatePlanning = null, $aProgression = array()) {
		// Extraction des informations de la progression à partir de la DATE
		$this->_planning_jour_id	= date("N", $dDatePlanning);
		$sLibelleJour				= $this->_planning_semaine[$this->_planning_jour_id];
		$sLibelleDate				= date('d/m/Y', $dDatePlanning);

		// Libellé du jour
		$sTitreJour			= strtoupper($sLibelleJour) . " " . $sLibelleDate;
		if ($this->_planning_format == self::FORMAT_CALENDAR) {
			$sTitreJour		=  $this->_planning_semaine_court[$this->_planning_jour_id];
		}

		// Découpage du volume horaire
		$nTranche			= $this->_planning_timer_size/60;
		$nWidth				= intval($this->_planning_jour_width * $nTranche);

		// Initialisation de la classe CSS pour chaque tranche horaire
		$sDiaryStyle		= "";
		$sClassItem			= "width-" . $nWidth . "p";
		if ($this->_planning_format == self::FORMAT_CALENDAR && $sClassName != self::PLANNING_HEADER_CLASSNAME) {
			// Remplacement du titre
			$sTitreJour		=  $sLibelleJour[0];
			$nWidth			= 100;
			$sClassItem		= "";

			// Calcul de la largeur de chaque volume horaire
			$fDayWidth		= number_format(self::PLANNING_WIDTH_RATIO / $this->_planning_duree, 2);
			$sDiaryStyle	= "style=\"width: " . $fDayWidth . "%\"";
		}

		// Affectation de la CLASSE selon si le jour est férié
		$sClassDefault		= in_array($IdProgression, $this->_planning_deprecated_dates)			? self::PLANNING_DEPRECATED_CLASS	: self::PLANNING_VALID_CLASS;

		// Modification de la classe CSS du jour selon s'il n'est pas travaillé
		$sClassDefault		= in_array($this->_planning_jour_id, $this->_planning_deprecated_days)	? self::PLANNING_DEPRECATED_CLASS	: $sClassDefault;

		// Libellé du jour
		$sTitreJour			= strtoupper($sLibelleJour) . " " . $sLibelleDate;
		if ($this->_planning_format == self::FORMAT_CALENDAR) {
			$sTitreJour		= $this->_planning_semaine_court[$this->_planning_jour_id];
		}

		// Construction du planning du jour
		$sPlanningHTML		= "<dl class=\"" . $sClassName . "\" title=\"" . strtoupper($sLibelleJour) . " " . $sLibelleDate . "\" $sDiaryStyle>
									<dt><h3>" . $sTitreJour . "</h3></dt>";

		// Finalisation de la zone de progression
		for ($heure = $this->_planning_debut ; $heure <= $this->_planning_fin ; $heure += $nTranche) {
			// Détermination du crénau horaire
			$h	= $heure%60;
			$m	= ($heure - $heure%60) * 60;

			// Fonctionnalité réalisée par défaut
			$sClassPlanning		= $sClassDefault;
			if ($sClassName == self::PLANNING_DEFAULT_CLASSNAME) {
				// Coloration des zones non travaillées ou normales
				$sClassPlanning	= in_array($h, $this->_planning_deprecated_hours)		? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;
				$sClassPlanning	= in_array($m, $this->_planning_deprecated_hours[$h])	? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;

				// Fonctionnalité réalisée si l'heure spécifique de la journée est non travaillée
				$sClassPlanning	= in_array($h, $this->_planning_deprecated_days[$this->_planning_jour_id]) ? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;

				// Fonctionnalité réalisée en cas de rendu sous forme de CALENDAR
				if ($this->_planning_format == self::FORMAT_CALENDAR) {
					$sClassPlanning .= " static";
				}
			}

			// Construction de la cellule horaire
			$sTimeIndex		= sprintf('%02d:%02d', $h, $m);
			/**
			 * @todo	DECOUPAGE HORAIRE - MINUTE
			 * $sPlanningHTML	.= "<dd id=\"planning-" . $IdProgression . "-" . $h . "-" . $m . "\" class=\"planning " . $sClassPlanning . " " . $sClassItem . "\">
			 */
			$sPlanningHTML	.= "<dd id=\"planning-" . $IdProgression . "-" . $h . "\" class=\"planning " . $sClassPlanning . " " . $sClassItem . "\">
									<h4 class=\"ui-widget-header\">" . $sTimeIndex . "</h4>
									<!-- @todo PROGRESSION -->
									" . DataHelper::get($aProgression[$IdProgression], $sTimeIndex) . "
								</dd>";
		}

		// Finalitation du planning du jour
		$sPlanningHTML		.= "</dl>";

		// Renvoi du code HTML
		return $sPlanningHTML;
	}

	/**
	 * @brief	Construction de la progression
	 *
	 * @param	string	$IdProgression		: identifiant de la CELLULE.
	 * @param	array	$aProgression		: contenu de la progression du jour.
	 * @return	string
	 */
	private function _getProgression($IdProgression, $aProgression = array()) {
		// Récupération de l'dentifiant du jour
		list($annee, $mois, $jour) = explode('-', $IdProgression);

		// Extraction de la date du jour à partir de l'identifiant
		$dDatePlanning		= mktime(0, 0, 0, $mois, $jour, $annee);

		// Calcul de la largeur de chaque volume horaire
		$this->_planning_jour_width	= intval(self::PLANNING_MAX_WIDTH / ($this->_planning_fin - $this->_planning_debut));

		/**
		 * @todo	IDENTIFICATION DE LA SEMAINE
		 *
		// Détermination du nombre de jours dans le même identifiant de semaine
		if ($nIdSemaine == date('W', $dDatePlanning)) {
			$nColspan++;
		} else {
			$nColspan = 1;
			$nIdSemaine		= date('W', $dDatePlanning);
		}
		 */

		// Affectation de la CLASSE selon si le jour est férié
		$sClassName			= in_array($IdProgression, $this->_planning_deprecated_dates) ? self::PLANNING_HOLIDAY_CLASSNAME : self::PLANNING_DEFAULT_CLASSNAME;

		// Construction du planning du jour
		$this->planning		.= $this->_buildProgression($sClassName, $IdProgression, $dDatePlanning, $aProgression);
	}

	/**
	 * @brief	Construction des éléments HTML
	 *
	 * @return	string
	 */
	private function _buildHTML() {
		// Fonctionnalité réalisée si la construction n'a pas été réalisée
		if (! $this->_build) {
			// Enregistrement du rendu
			$this->_build		= true;

			// Fonctionnalité si plusieurs éléments sont récupérés
			if (count($this->_aItems)) {
				// Chargement des éléments
				$this->item		.= implode(chr(10), $this->_aItems);
			} elseif (!empty($this->_empty)) {
				// Aucun élément n'a été trouvé
				$this->item		.= sprintf("<h3 class=\"strong center margin-top-25 padding-bottom-25\">%s</h3>", $this->_empty);
			}

			// Finalisation du panneau
			$this->item			.= "</ul>";

			// Ajout du JavaScript dans le code HTML afin d'être compatible avec AJAX
			$this->item			.= "<script type=\"text/javascript\">"
								// Fonctionnalité de déclaration si les éléments n'existent pas
								. "if (typeof(PLANNING_CURRENT) == 'undefined') { var PLANNING_CURRENT = \"\"; }"
								. "if (typeof(PLANNING_MD5) == 'undefined') { var PLANNING_MD5 = []; }"
								. "if (typeof(PLANNING_CELL_WIDTH) == 'undefined') { var PLANNING_CELL_WIDTH = []; }"
								// Chargement des valeurs des éléments
								. "PLANNING_CURRENT = \"" . $this->_md5 . "\";"
								. "PLANNING_MD5[\"" . $this->_md5 . "\"] = \"" . $this->_md5 . "\";"
								. "PLANNING_CELL_WIDTH[\"" . $this->_md5 . "\"] = " . $this->_nCellWidth . ";"
								. "</script>";

			// Ajout du JavaScript à la page
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/PlanningHelper.js");

			// Ajout de la feuille de style
			ViewRender::addToStylesheet(FW_VIEW_STYLES . "/PlanningHelper.css");

			// Initialisation de la liste des jours de la semaine
			$aJours				= array();
			for ($i = 0 ; $i < $this->_planning_duree; $i++) {
				// Identifiant du jour de la semaine sous la forme [Y-m-d]
				$aJours[]		= date('Y-m-d', mktime(0, 0, 0, $this->_planning_mois, ($this->_planning_jour + $i), $this->_planning_annee));
			}

			// Construction de chaque zone de progression selon l'identifiant du jour
			$this->planning		= "<section id=\"" . $this->_md5 . "\" class=\"$this->_planning_format week left center max-width no-wrap\">";
			$nIdSemaine			= 0;
			$nColspan			= 1;
			foreach ($aJours as $IdProgression) {
				/**
				 * @todo	CONTENU DE LA PROGRESSION
				 */
				$aProgression	= array();

				// Récupération de la progression selon l'identifiant du jour
				$this->_getProgression($IdProgression, $aProgression);
			}

			// Fonctionnalité réalisée si le format à afficher est au format CALENDAR
			if ($this->_planning_format == self::FORMAT_CALENDAR) {
				// Création d'une entête à la PROGRESSION
				$this->planning	.= $this->_buildProgression();
				$bInit			= false;
			}

			// Finalisation de la zone de progression
			$this->planning		.= "</section>";

			// Activation du planning par jQuery
			ViewRender::addToJQuery("initPlanning(\"" . $this->_md5 . "\");");
		}
	}

	/**
	 * @brief	Rendu du MODAL
	 *
	 * Ajout du MODAL directement dans VIEW_BODY
	 * @li	Possibilité d'ajouter un moteur de recherche par un tableau de paramètres.
	 * @see PlanningHelper::_buildSearchForm($sAction, $aSearchItems)
	 *
	 * @param	string	$sAction			: URL du moteur de recherche.
	 * @param	array	$aSearchItems		: tableau contenant les éléments du moteur de recherche.
	 * @return	void
	 */
	private function _buildModal($sAction = self::MODAL_ACTION_DEFAULT, $aSearchItems = array()) {
		$oModal = new ModalHelper("modal-item-" . $this->_md5);
		$oModal->addClassName("overflow-hidden");
		$oModal->setTitle("Édition d'un élément");
		$oModal->setResizable(true);
		$oModal->setModal(false);
		$oModal->setDimensions(495);
		$oModal->setForm(false);
		$oModal->setPosition("center", "left top", "window");
		$oModal->linkContent("<section id=\"search-content-" . $this->_md5 . "\" class=\"$this->_planning_format\">" . $this->getItem() . "</section>");

		// Ajout d'un champ caché relatif à l'identifiant
		$aSearchItems['item_id'] = array(
			'type'	=> 'hidden'
		);

		// Ajout d'un champ relatif à la durée
		$aSearchItems['item_duree'] = array(
			'type'	=> 'number',
			'label'	=> 'Durée',
			'value'	=> 1
		);

		// Fonctionnalité réalisée si l'action du formulaire est précisé
		if (!empty($sAction)) {
			$oModal->linkContent($this->_buildSearchForm($sAction, $aSearchItems));
		}
		ViewRender::addToBody($oModal->renderHTML());
	}

	/**
	 * @brief	Rendu final de l'élément SOURCE
	 * @return	string
	 */
	public function getItem() {
		// Construction des éléments si ce n'est pas déjà fait
		$this->_buildHTML();
		// Renvoi de l'élément
		return $this->item;
	}

	/**
	 * @brief	Rendu final du conteneur CIBLE
	 * @return	string
	 */
	public function getPlanning() {
		// Construction des éléments si ce n'est pas déjà fait
		$this->_buildHTML();
		// Renvoi du conteneur
		return $this->planning;
	}

	/**
	 * @brief	Rendu final de l'élément sous forme de MODAL
	 *
	 * @li	Possibilité d'ajouter un moteur de recherche.
	 * @param	string	$sAction			: URL du moteur de recherche.
	 * @param	array	$aSearchItems		: tableau contenant les éléments du moteur de recherche.
	 * @return	string
	 */
	public function renderHTML($sAction = self::MODAL_ACTION_DEFAULT, $aSearchItems = array()) {
		// Ajout des ressources au Dialog
		$this->_buildModal($sAction, $aSearchItems);

		// Renvoi du conteneur
		return $this->getPlanning();
	}

}
