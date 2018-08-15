<?php
/**
 * @brief	Helper de création d'un élément du planning.
 *
 * Vue permettant de créer des éléments constituant le planning, pouvant être cliqué/glissé.
 *
 *
 *		'task_id'			=> 1111,										// Identifiant de la tâche en base de données
 *
 *		'task_matterId'		=> "Élément A",									// Titre de la matière affectée à la tâche
 *		'task_matterIdId'	=> 1,											// Identifiant de la matière
 *
 *		'task_information'	=> null,										// Informations complémentaires non transmises lors du déplacement
 *
 *		'task_locationId'	=> "Localisation de la tâche...",				// Libellé de la localisation affectée à la tâche
 *		'task_locationIdId'	=> 1,											// Identifiant de la localisation
 *
 *		'task_team'			=> array("Personne 1, Personne 2, Personne 3"),	// Liste des participants, les participants PRINCIPAUX sont entourés de la balse <B></B>
 *		'task_teamId'		=> 1,											// Identifiant de la liste des participants
 *
 *		'task_year'			=> null,
 *		'task_month'		=> null,
 *		'task_day'			=> null,
 *		'task_hour'			=> null,
 *		'task_minute'		=> null,
 *		'task_duration'		=> null,
 *
 *		'task_content'		=> null,										// Contenu HTML complémentaire
 *
 *		'task_update'		=> 0,											// Indicateur de modification de la tâche
 *
 *		'task_background'	=> "#RGB"										// Couleur de fond
 *
 *
 * @li	Chaque élément HTML de la progression embarque des champs cachés renseignés via JavaScript :
 * @code
 * <article class="job">
 * 		<div class="content">
 * 			(...)
 * 			<input type="hidden" name="task_id[]" />
 * 			<input type="hidden" name="task_year[]" />
 * 			<input type="hidden" name="task_month[]" />
 * 			<input type="hidden" name="task_day[]" />
 * 			<input type="hidden" name="task_hour[]" />
 * 			<input type="hidden" name="task_minute[]" />
 * 			<input type="hidden" name="task_duration[]" />
 * 			<input type="hidden" name="task_matterId[]" />
 * 			<input type="hidden" name="task_locationId[]" />
 * 			<input type="hidden" name="task_teamId[]" />
 * 			<input type="hidden" name="task_update[]" />
 * 		</div>
 * </article
 * @endcode
 *
 *
 *
 *
 *
 * @name		ItemHelper
 * @category	Helper
 * @package		PlanningHelper
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 149 $
 * @since		$LastChangedDate: 2018-08-15 14:23:09 +0200 (Wed, 15 Aug 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class Planning_ItemHelper {

	/**
	 * Constante de construction de la liste des éléments du formulaire de recherche.
	 * @var		string
	 */
	const		TYPE_SELECT				= "select";
	const		TYPE_TEXT				= "text";

	const		ID_PLANNING_FORMAT		= 'planning-%d-%d-%d-%d';
	const		ID_ITEM_FORMAT			= 'item-%s';

	const		PATTERN_PRINCIPAL		= "@\>(.*)\<@";
	const		FORMAT_PRINCIPAL		= "<B>%s</B>";
	const		FORMAT_SECONDAIRE		= "%s";

	const		TYPE_PRINCIPAL			= 'principal';
	const		TYPE_SECONDAIRE			= 'secondaire';

	const		TIME_CONFLICT_BG_COLOR	= 'rgba(255, 100, 0, 0.3)';
	const		PANEL_CONFLICT_CSS		= 'panel-conflict';
	const		ITEM_CONFLICT_CSS		= 'item-conflict';

	const		FORM_LOCATION			= "form-location";
	const		FORM_TEAM				= "form-team";

	const		AJAX_ACTION_SUFFIX		= "Ajax";
	const		AJAX_FORMAT_ERROR		= "Le format attendu n'est pas correct...";
	const		AJAX_MESSAGE_ERROR		= "Une erreur a été rencontrée au cours du traitement...";

	/**
	 * @brief	Liste des noms de variables d'instance pour tester les différences.
	 * @var		array
	 */
	public static $LIST_ITEM_LABEL		= array(
		1		=> "_content",
		2		=> "_locationId",
		3		=> "_matterId",
		4		=> "_teamId"
	);

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage		= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	protected	$_id					= null;

	public		$_information			= "";
	public		$_content				= "";

	public		$_matter				= "Libellé de la matière";
	public		$_matterId				= 0;
	public		$_matterInfo			= "";

	public		$_location				= "-";
	public		$_locationId			= 0;
	public		$_locationInfo			= "";
	public		$_locationHref			= false;
	public		$_conflict_location		= false;

	public		$_team					= array();
	public		$_teamId				= 0;
	public		$_teamInfo				= "";
	public		$_teamHref				= false;
	public		$_conflict_team			= false;

	protected	$_year					= 0;
	protected	$_month					= 0;
	protected	$_day					= 0;
	protected	$_hour					= 0;
	protected	$_minute				= 0;
	protected	$_duration				= 1;
	protected	$_count					= 0;
	protected	$_update				= 0;

	protected	$_timer					= 60;

	protected	$_hrefZoomIn			= "#";
	protected	$_hrefTrash				= "#";
	protected	$_class					= "";
	protected	$_background			= null;

	/**
	 * @brief	Indicateur de construction.
	 * @var		bool
	 */
	private		$_build					= false;

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @li	Contrôle que l'identifiant de l'élément n'est pas à exclure.
	 *
	 * @example	Exemple d'utilisation avec l'ajout d'un texte et d'une image
	 * @code
	 * 		// Création d'un nouvel élément
	 * 		// Lors du clic sur le [ZOOM] le contenu du modal sera chargé avec le contenu de l'URL "/search/question?id=15"
	 * 		$oItem = new Panning_ItemHelper("<span class=\"strong\">Contenu de l'élément</span><img src=\"/images/logo.png\" alt=\"Logo\" />", 15, "/search/question?id=%d");
	 *
	 * 		// Intégration du contenu HTML au menu
	 * 		ViewRender::addToMenu($oItem->renderHTML());
	 * @endcode
	 *
	 * @param	mixed	$xId				: (optionnel) Identifiant de l'élément, NULL si aucun.
	 * @param	string	$sTitle				: (optionnel) Titre de l'élément.
	 * @param	string	$sDescription		: (optionnel) Texte d'information relatif à l'élément.
	 * @param	string	$sContentHTML		: (optionnel) Contenu HTML à ajouter en plus de la descrition.
	 * @param	string	$sClass				: (optionnel) Classe CSS affecté à l'élément.
	 * @return	void
	 */
	public function __construct($xId = null, $sTitle = "", $sDescription = "", $sContentHTML = "", $sClass = "") {
		// Initialisation des paramètres
		$this->_id						= $xId;
		$this->_matter					= trim($sTitle);
		$this->_location				= trim($sDescription);
		$this->_content					= trim($sContentHTML);

		// Initialisation de la classe CSS
		$this->setClass($sClass);
	}

	/**
	 * @brief	Récupération de la valeur du TimeStamp de l'élément
	 *
	 * @return	date
	 */
	public function getTimeStamp() {
		return mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
	}

	/**
	 * @brief	Récupération de la valeur du DateTime de l'élément
	 *
	 * @return	date
	 */
	public function getDateTime($sFormat = "Y-m-d H:i") {
		return date($sFormat, $this->getTimeStamp());
	}

	/**
	 * @brief	Récupération de la valeur de la DATE de l'élément
	 *
	 * @return	date
	 */
	public function getDate($sFormat = "Y-m-d") {
		return date($sFormat, $this->getTimeStamp());
	}

	/**
	 * @brief	Récupération de la valeur du TIME de l'élément
	 *
	 * @return	date
	 */
	public function getTime($sFormat = "H:i") {
		return date($sFormat, $this->getTimeStamp());
	}

	/**
	 * @brief	Récupération de la valeur de l'année de l'élément
	 * @return	integer
	 */
	public function getYear() {
		return (int) $this->_year;
	}

	/**
	 * @brief	Récupération de la valeur du mois de l'élément
	 * @return	integer
	 */
	public function getMonth() {
		return (int) $this->_month;
	}

	/**
	 * @brief	Récupération de la valeur du jour de l'élément
	 * @return	integer
	 */
	public function getDay() {
		return (int) $this->_day;
	}

	/**
	 * @brief	Récupération de la valeur de l'heure de l'élément
	 * @return	integer
	 */
	public function getHour() {
		return (int) $this->_hour;
	}

	/**
	 * @brief	Récupération de la valeur de la minute de l'élément
	 * @return	integer
	 */
	public function getMinute() {
		return (int) $this->_minute;
	}

	/**
	 * @brief	Récupération de la valeur de la durée de l'élément
	 * @return	integer
	 */
	public function getDuration() {
		return (int) $this->_duration;
	}

	/**
	 * @brief	Récupération du compteur de fin de séance de l'élément
	 * @return	integer
	 */
	public function getCompteur() {
		return (int) $this->_count;
	}

	/**
	 * @brief	Récupération du titre de l'élément
	 * @return	string
	 */
	public function getTitle() {
		$sTitre							= $this->_matter;
		// Fonctionnalité réalisée si un compteur de fin de séance est présent
		if (!is_null($this->_count)) {
			// Initialisation du compteur de début de séance
			$nDebut						= $this->_count - $this->getDuration() + 1;

			// Initialisation du compteur de fin de séance
			$nFin						= $this->_count;

			// Construction du titre avec le compteur de séance
			$sTitre						= sprintf("%s [%d-%d]", $sTitre, $nDebut, $nFin);
		}
		return $sTitre;
	}

	/**
	 * @brief	Récupération du libellé de la localisation de l'élément
	 * @return	string
	 */
	public function getLocation($bShowInfo = false, $sConcatFormat = "%s %s") {
		// Fonctionnalité réalisée si l'information complémentaire doit être affichée
		if ($bShowInfo && !empty($this->_locationInfo)) {
			// Ajout de l'info
			$sLocation					= sprintf($sConcatFormat, $this->_location, $this->_locationInfo);
		} else {
			$sLocation					= $this->_location;
		}

		// Renvoi de la location
		return $sLocation;
	}

	/**
	 * @brief	Récupération de l'identifiant de la localisation de l'élément
	 * @return	integer
	 */
	public function getLocationId() {
		return $this->_locationId;
	}

	/**
	 * @brief	Récupération de l'information sur la localisation de l'élément
	 * @return	string
	 */
	public function getLocationInfo() {
		return $this->_locationInfo;
	}

	/**
	 * @brief	Récupération de l'action du bouton de la localisation
	 * @return	string URL
	 */
	public function getLocationHrefAction() {
		return $this->_locationHref;
	}

	/**
	 * @brief	Récupération du libellé de la matière de l'élément
	 * @return	string
	 */
	public function getMatter($bShowInfo = false, $sConcatFormat = "%s %s") {
		// Fonctionnalité réalisée si l'information complémentaire doit être affichée
		if ($bShowInfo && !empty($this->_matterInfo)) {
			// Ajout de l'info
			$sMatter					= sprintf($sConcatFormat, $this->_matter, $this->_matterInfo);
		} else {
			$sMatter					= $this->_matter;
		}

		// Renvoi de la matière
		return $sMatter;
	}

	/**
	 * @brief	Récupération de l'identifiant de la matière de l'élément
	 * @return	integer
	 */
	public function getMatterId() {
		return $this->_matterId;
	}

	/**
	 * @brief	Récupération de l'information sur la matière de l'élément
	 * @return	string
	 */
	public function getMatterInfo() {
		return $this->_matterInfo;
	}

	/**
	 * @brief	Récupération de la liste des participants à l'élément
	 *
	 * @param	string	$iType				: (optionnel) type de(s) participant(s) parmi les constantes `TYPE_PRINCIPAL` ou `TYPE_SECONDAIRE`.
	 * @param	string	$iFormat			: (optionnel) type de format définit dans la classe DataHelper.
	 * @return	array
	 */
	public function getTeam($iType = null, $iFormat = DataHelper::DATA_TYPE_STR) {
		// Initialisation des éléments de liste
		$aPrincipal						= array();
		$aSecondaire					= array();

		// Parcours de la liste des participants
		foreach ($this->_team as $sLabel) {
			if (preg_match(self::PATTERN_PRINCIPAL, $sLabel, $aMatched)) {
				// Récupération du libellé en supprimant les espaces superflus
				$sTeamLabel				= DataHelper::convertToString($aMatched[1], $iFormat);
				// Fonctionnalité réalisée si le libellé n'est pas vide
				if (!empty($sTeamLabel)) {
					$aPrincipal[]		= $sTeamLabel;
				}
			} else {
				// Récupération du libellé en supprimant les espaces superflus
				$sTeamLabel				= DataHelper::convertToString($sLabel, $iFormat);
				// Fonctionnalité réalisée si le libellé n'est pas vide
				if (!empty($sTeamLabel)) {
					$aSecondaire[]		= $sTeamLabel;
				}
			}
		}

		// Traitement selon le type de liste
		switch ($iType) {
			case self::TYPE_PRINCIPAL:
				$aListe					= $aPrincipal;
				break;

			case self::TYPE_SECONDAIRE:
				$aListe					= $aSecondaire;
				break;

			default:
				$aListe					= array_merge($aPrincipal, $aSecondaire);
				break;
		}

		// Renvoi de la liste
		return $aListe;
	}

	/**
	 * @brief	Récupération de l'identifiant du groupe de participants à l'élément
	 * @return	integer
	 */
	public function getTeamId() {
		return $this->_teamId;
	}

	/**
	 * @brief	Récupération de l'information sur le groupe de participants à l'élément
	 * @return	string
	 */
	public function getTeamInfo() {
		return $this->_teamInfo;
	}

	/**
	 * @brief	Récupération de l'action du bouton du groupe de participants à l'élément
	 * @return	string URL
	 */
	public function getTeamHrefAction() {
		return $this->_teamHref;
	}

	/**
	 * @brief	Initialisation de l'information de l'élément
	 * 
	 * @param	string	$sInformation		: code HTML complémentaire.
	 * @return	void
	 */
	public function setInformation($sInformation) {
		$this->_information				= trim($sInformation);
	}

	/**
	 * @brief	Initialisation du contenu HTML de l'élément
	 * 
	 * @param	string	$sContent			: code HTML complémentaire.
	 * @return	void
	 */
	public function setContent($sContent) {
		$this->_content					= trim($sContent);
	}

	/**
	 * @brief	Initialisation du BACKGROUND de l'élément
	 * 
	 * @param	string	$sBackground		: valeur de l'attribut BACKGROUND.
	 * @return	void
	 */
	public function setBackground($sBackground) {
		$this->_background				= trim($sBackground);
	}

	/**
	 * @brief	Initialisation de la classe CSS de l'élément
	 *
	 * @param	string	$sClass				: nom de la classe CSS.
	 * @return	void
	 */
	public function setClass($sClass) {
		$this->_class					= trim($sClass);
	}

	/**
	 * @brief	Ajout d'une classe CSS à l'élément
	 *
	 * @param	string	$sClass				: nom de la classe CSS à ajouter.
	 * @return	void
	 */
	public function addClass($sClass) {
		$this->_class					= empty($this->_class) ? $sClass : $this->_class . " " . trim($sClass);
	}

	/**
	 * @brief	Initialisation de l'action d'interaction avec l'élément
	 *
	 * @param	string	$sAction			: nom de l'URL.
	 * @return	void
	 */
	public function setHrefAction($sAction = "#", $sFormat = "%s?id=%s") {
		$this->_hrefAction				= $sAction;
		$this->_hrefFormat				= $sFormat;
	}

	/**
	 * @brief	Initialisation de l'action de [ZoomIn]
	 *
	 * @param	string	$sHrefZoomIn		: format de l'URL avec intégration de l'identifiant par la méthode PHP `sprintf`.
	 * @return	void
	 */
	public function setHrefZoomIn($sHrefZoomIn = "/planning/zoom?id=%s") {
		$this->_hrefZoomIn				= $sHrefZoomIn;
	}

	/**
	 * @brief	Initialisation de l'action de [Trash]
	 *
	 * @param	string	$sHrefTrash			: format de l'URL avec intégration de l'identifiant par la méthode PHP `sprintf`.
	 * @return	void
	 */
	public function setHrefTrash($sHrefTrash = "/planning/trash?id=%s") {
		$this->_hrefTrash				= $sHrefTrash;
	}

	/**
	 * @brief	Initialisation de la valeur du TimeStamp de l'élément
	 *
	 * @param	date	$dTimeStamp			: date au format TIMESTAMP.
	 * @return	void
	 */
	public function setTimeStamp($dTimeStamp) {
		$this->_year					= (int) date("Y", $dTimeStamp);
		$this->_month					= (int) date("m", $dTimeStamp);
		$this->_day						= (int) date("d", $dTimeStamp);
		$this->_hour					= (int) date("H", $dTimeStamp);
		$this->_minute					= (int) date("i", $dTimeStamp);
	}

	/**
	 * @brief	Initialisation de la valeur de la date et l'heure de l'élément
	 *
	 * @param	date	$dDateTime				: valeur de la date avec l'heure de l'élément.
	 * @return	void
	 */
	public function setDateTime($dDateTime) {
		// Extraction des arguments de la dans au format MySQL [Y-m-d H:i:s]
		$aDateParams					= DataHelper::extractParamsFromDateTime($dDateTime);

		// Extraction des paramètres de la DATE
		$this->_year					= (int) $aDateParams['Y'];
		$this->_month					= (int) $aDateParams['m'];
		$this->_day						= (int) $aDateParams['d'];

		// Extraction des paramètres du TIME
		$this->_hour					= (int) $aDateParams['H'];
		$this->_minute					= (int) $aDateParams['i'];
	}

	/**
	 * @brief	Initialisation de la valeur de la date de l'élément
	 *
	 * @param	integer	$dDate				: valeur de la date.
	 * @return	void
	 */
	public function setDate($dDate) {
		// Forçage du format de la dans au format MySQL [Y-m-d]
		$dDateMySQL						= DataHelper::dateFrToMy($dDate);

		// Extraction des paramètres de la DATE
		list($nYear, $nMonth, $nDay)	= explode("-", $dDateMySQL);

		// Initialisation des valeurs
		$this->setYear($nYear);
		$this->setMonth($nMonth);
		$this->setDay($nDay);
	}

	/**
	 * @brief	Initialisation de la valeur de l'année de l'élément
	 *
	 * @param	integer	$nYear				: valeur de l'année sur 4 chiffres.
	 * @return	void
	 */
	public function setYear($nYear) {
		$this->_year					= intval($nYear);
	}

	/**
	 * @brief	Initialisation de la valeur du mois de l'élément
	 *
	 * @param	integer	$nMonth				: valeur du mois sur 2 chiffres.
	 * @return	void
	 */
	public function setMonth($nMonth) {
		$this->_month					= intval($nMonth);
	}

	/**
	 * @brief	Initialisation de la valeur du jour de l'élément
	 *
	 * @param	integer	$nDay				: valeur du mois sur 2 chiffres.
	 * @return	void
	 */
	public function setDay($nDay) {
		$this->_day						= intval($nDay);
	}

	/**
	 * @brief	Initialisation de la valeur de l'heure de l'élément
	 *
	 * @param	integer	$nHour				: valeur de l'heure sur 2 chiffres.
	 * @return	void
	 */
	public function setHour($nHour) {
		$this->_hour					= intval($nHour);
	}

	/**
	 * @brief	Initialisation de la valeur de la minute de l'élément
	 *
	 * @param	integer	$nMinute			: valeur des minutes sur 2 chiffres.
	 * @return	void
	 */
	public function setMinute($nMinute) {
		$this->_minute					= intval($nMinute);
	}

	/**
	 * @brief	Initialisation de la valeur de la durée de l'élément
	 *
	 * @param	integer	$nDuration			: valeur de la durée en minutes.
	 * @return	void
	 */
	public function setDuration($nDuration) {
		$this->_duration				= intval($nDuration);
	}

	/**
	 * @brief	Initialisation du compteur horaire de l'élément
	 *
	 * @param	integer	$nCount			: numéro du volume horaire.
	 * @return	void
	 */
	public function setCompteur($nCount = 1) {
		$this->_count					= intval($nCount);
	}

	/**
	 * @brief	Initialisation de la valeur de l'heure de l'élément
	 *
	 * @param	string	$sFullTime			: chaîne de caractères représentant l'heure avec les minutes.
	 * @param	char	$cSeparator			: caractère de séparation entre les heures et les minutes.
	 * @return	void
	 */
	public function setFullTime($sFullTime, $cSeparator = ":") {
		// Extraction des paramètres de l'HEURE
		list($nHour, $nMinute)			= explode($cSeparator, $sFullTime);

		// Initialisation des valeurs
		$this->setHour($nHour);
		$this->setMinute($nMinute);
	}

	/**
	 * @brief	Attribution d'une matière à l'élément
	 *
	 * @param	integer	$nId				: identidiants de la matière.
	 * @param	string	$sLabel				: description de la matière.
	 * @param	integer	$nCount				: (optionnel) compteur de consommation de la matière.
	 * @param	string	$sInfo				: (optionnel) informations complémentaires du groupe de participants.
	 * @return	void
	 */
	public function setMatter($nId, $sLabel = null, $nCount = null, $sInfo = null) {
		$this->_matter					= $sLabel;
		$this->_matterId				= intval($nId);
		$this->_matterInfo				= trim($sInfo);
		// Fonctionnalité réalisée si un compteur est passé en paramètre
		if (!is_null($nCount)) {
			$this->_count				= intval($nCount);
		}
	}

	/**
	 * @brief	Attribution d'une localisation à l'élément
	 *
	 * @param	integer	$nId				: identidiants du groupe du local.
	 * @param	string	$sLabel				: description du local.
	 * @param	string	$sInfo				: (optionnel) informations complémentaires du local.
	 * @return	void
	 */
	public function setLocation($nId, $sLabel = null, $sInfo = null) {
		$this->_location				= $sLabel;
		$this->_locationId				= intval($nId);
		$this->_locationInfo			= trim($sInfo);
	}

	/**
	 * @brief	Attribution de l'action du bouton de la localisation
	 * @param	string	$sUrl				: URL de l'action du bouton.
	 * @param	array	$aParams			: (optionnel) tableau de paramètres $_GET a ajouter à l'URL.
	 * @return	void
	 */
	public function setLocationHrefAction($sUrl, $aParams = array()) {
		// Injection d'un JOCKER
		$sUrl							.= "%s";

		// Fonctionnalité réalisée si au moins un paramètre est présent
		if (DataHelper::isValidArray($aParams)) {
			$sUrl						.= "?";
			$aGet						= array();
			foreach ($aParams as $sKey => $xValue) {
				// Fonctionnalité réalisée si la valeur est un tableau
				if (DataHelper::isValidArray($xValue)) {
					$xValue				= serialize($xValue);
				}
				// Ajout de l'entrée à la collection $_GET
				$aGet[]					= "$sKey=$xValue";
			}
			$sUrl						.= implode("&", $aGet);
		}

		// Affectation de l'URL
		$this->_locationHref			= $sUrl;
	}

	/**
	 * @brief	Ajout d'un status d'état à l'élément
	 *
	 * @param	boolean	$bUpdate			: valeur du status de modification.
	 * @return	void
	 */
	public function setUpdateStatus($bStatus = false) {
	    // Le type de la variable est enregistré en NUMÉRIQUE bien qu'il s'agisse d'un bouléen
		$this->_update					= $bStatus ? 1 : 0;
	}

	/**
	 * @brief	Ajout d'un groupe de participants à l'élément
	 *
	 * @param	mixed	$nId				: identidiants du groupe de participants.
	 * @param	array	$aListPerson		: liste des participants.
	 * @param	string	$iType				: (optionnel) type de(s) participant(s) parmi les constantes `TYPE_PRINCIPAL` ou `TYPE_SECONDAIRE`.
	 * @param	string	$sInfo				: (optionnel) informations complémentaires du groupe de participants.
	 * @return	void
	 */
	public function setTeam($nId, $aListPerson = array(), $iType = null, $sInfo = null) {
		$this->_teamId					= $nId;

		// Fonctionnalité réalisée si le type de participant est TYPE_PRINCIPAL
		if ($iType == self::TYPE_PRINCIPAL) {
			// Initialisation de la liste de(s) participant(s)
			$this->_team				= array();

			// Parcours de l'ensemble des participants
			foreach ((array) $aListPerson as $sLabel) {
				// Récupération du libellé en supprimant les espaces superflus
				$sTeamLabel				= DataHelper::convertToString($sLabel);

				// Fonctionnalité réalisée si la chaîne n'est pas vide
				if (!empty($sTeamLabel)) {
					$this->_team[]		= sprintf(self::FORMAT_PRINCIPAL, $sTeamLabel);
				}
			}
		} else {
			// Initialisation de la liste de(s) participant(s)
			if (DataHelper::isValidArray($aListPerson, null, true)) {
				$this->_team			= $aListPerson;
			} else {
				// Récupération du libellé en supprimant les espaces superflus
				$sTeamLabel				= DataHelper::convertToString($aListPerson);
				// Fonctionnalité réalisée si la chaîne n'est pas vide
				if (!empty($sTeamLabel)) {
					$this->_team		= array($sTeamLabel);
				}
			}
		}

		// Ajout de l'info en dernière position
		if (!empty($sInfo)) {
			$this->_teamInfo			= trim($sInfo);
			$this->_team[]				= $this->_teamInfo;
		}
	}

	/**
	 * @brief	Attribution de l'action du bouton du groupe de participants à l'élément
	 * @param	string	$sUrl				: URL de l'action du bouton.
	 * @param	array	$aParams			: (optionnel) tableau de paramètres $_GET a ajouter à l'URL.
	 * @return	void
	 */
	public function setTeamHrefAction($sUrl, $aParams = array()) {
		// Injection d'un JOCKER
		$sUrl							.= "%s";

		// Fonctionnalité réalisée si au moins un paramètre est présent
		if (DataHelper::isValidArray($aParams)) {
			$sUrl						.= "?";
			$aGet						= array();
			foreach ($aParams as $sKey => $xValue) {
				// Fonctionnalité réalisée si la valeur est un tableau
				if (DataHelper::isValidArray($xValue)) {
					$xValue				= serialize($xValue);
				}
				// Ajout de l'entrée à la collection $_GET
				$aGet[]					= "$sKey=$xValue";
			}
			$sUrl						.= implode("&", $aGet);
		}

		// Affectation de l'URL
		$this->_teamHref				= $sUrl;
	}

	/**
	 * @brief	Récupère l'indicateur de conflit sur la localisation.
	 * @void	boolean
	 */
	public function hasConflictLocation() {
		return $this->_conflict_location;
	}

	/**
	 * @brief	Active l'indicateur de conflit sur la localisation.
	 *
	 * @param	boolean	$bBoolean			: active l'indicateur de conflit.
	 * @return	void
	 */
	public function setConflictLocation($bBoolean) {
		$this->_conflict_location		= $bBoolean;
	}

	/**
	 * @brief	Récupère l'indicateur de conflit sur les participants.
	 * @void	boolean
	 */
	public function hasConflictTeam() {
		return $this->_conflict_team;
	}

	/**
	 * @brief	Active l'indicateur de conflit sur les participants.
	 *
	 * @param	boolean	$bBoolean			: active l'indicateur de conflit.
	 * @return	void
	 */
	public function setConflictTeam($bBoolean) {
		$this->_conflict_team			= $bBoolean;
	}

	/**
	 * @brief	Récupère l'indicateur de conflit sur la localisation ou les participants.
	 * @void	boolean
	 */
	public function hasConflict() {
		return $this->_conflict_location || $this->_conflict_team;
	}

	public function _buildHTML($bDeletable = true) {
		// Fonctionnalité réalisée si la construction n'a pas été réalisée
		if (! $this->_build) {
			// Enregistrement du rendu
			$this->_build				= true;
			$this->_matterHTML			= $this->_matter;

			// Fonctionnalité réalisée si le bouton [zoom] peut être affiché
			$sZoomIn = "";
			if (!empty($this->_id) && !empty($this->_hrefZoomIn)) {
				$sZoomIn = "<a class='ui-icon ui-icon-zoomin' title='Voir le contenu' href='" . $this->_hrefZoomIn . "' >&nbsp;</a>";
			}

			// Fonctionnalité réalisée si le bouton [poubelle] peut être affiché
			$sTrash = "";
			if (!empty($this->_id) && !empty($this->_hrefTrash)) {
				$sTrash = "<a class='ui-icon ui-icon-trash' title='Retirer cet élément' href='" . $this->_hrefTrash . "'>&nbsp;</a>";
			}

			// Fonctionnalité réalisée si des participants sont présents
			if (DataHelper::isValidArray($this->_team, null, true)) {
				foreach ($this->_team as $sLabelHTML) {
					// Ajout du participant s'il est valide
					if (!empty($sLabelHTML) && $sLabelHTML != "&nbsp;" && $sLabelHTML != "<B>&nbsp;</B>") {
						$this->_matterHTML .= "\n\t└─ " . DataHelper::extractContentFromHTML($sLabelHTML);
					}
				}
			}

			// Indicateur de conflit sur la cellule
			$sPanelClass				= $this->hasConflict()		? self::PANEL_CONFLICT_CSS	: null;

			// Indicateur de conflit sur les descriptions (SALLES)
			$sConflictLocation			= $this->_conflict_location	? self::ITEM_CONFLICT_CSS	: null;

			// Indicateur de conflit sur les participants
			$sConflictTeam				= $this->_conflict_team		? self::ITEM_CONFLICT_CSS	: null;

			$sBackground				= null;
			if ($this->_background)	{
				$sBackground			= "style='background: $this->_background;'";
			}

			// Création d'un bouton d'action pour la localisation
			$sLocationButtonSection		= null;
			if ($this->_locationHref) {
				// Identification unique de l'URL
				$sMD5					= md5($this->_locationHref);

				// Intégration de la section dans la vue
				$sLocationButtonSection	= "<section class='hidden " . self::FORM_LOCATION . "'>
												<button id=\"search-location-" . $sMD5 . "\">Modifier cette section</button>
												<hr />
											</section>";

				// Appel du MODAL au clic sur le bouton
				ViewRender::addToJQuery('$(document).on("click", "button#search-location-' . $sMD5 . '", function() {
											// Ajout du formulaire MODAL
											$("dialog").append("<div id=\"location-' . $sMD5 . '\"></div>");

											// Création du formulaire MODAL
											$("div#location-' . $sMD5 . '").dialog({
												autoOpen:		false,
												height:			"90%",
												width:			"90%",
												dialogClass:	"no-close",
												buttons:		[{
													text:		"Modifier",
													click:		function() {
														$(this).dialog("close");
													}
												}]
											});

											// Chargement du contenu du MODAL
											$.ajax({
												async:		false,
												type:		"POST",
												dataType:	"HTML",
												url:		"' . sprintf($this->_locationHref, '') . '",
												data:		{
													FORM_MD5:	"' . $sMD5 . '",
													task_matterId:		$("article#planning-viewer").find("input[name^=task_matterId]").val(),
													task_locationId:	$("article#planning-viewer").find("input[name^=task_locationId]").val(),
													task_locationInfo:	$("article#planning-viewer").find("input[name^=task_locationInfo]").val(),
													task_year:			$("article#planning-viewer").find("input[name^=task_year]").val(),
													task_month:			$("article#planning-viewer").find("input[name^=task_month]").val(),
													task_day:			$("article#planning-viewer").find("input[name^=task_day]").val(),
													task_hour:			$("article#planning-viewer").find("input[name^=task_hour]").val(),
													task_duration:		$("input#id_modal_duree").val()
												},
												success:	function(response) {
													// Ajout du formulaire MODAL
													$("dialog").append("<div id=\"location-' . $sMD5 . '\">" + response + "</div>");

													// Création du formulaire MODAL de sélection des SALLES
													$("div#location-' . $sMD5 . '").dialog({
														title:			"Sélectionnez la(les) salle(s) et le volume horaire de réservation",
														maxHeight:		$("article#main-article").height() - 100 + " px",
														width:			"90%",
														dialogClass:	"no-close",
														buttons:		[{
															text:		"Modifier",
															click:		function() {
																// Récupération de la liste des salles sélectionnées
																var liste_salles = [];
																$("input[type=checkbox]:checked", this).each(function() {
																	liste_salles.push($(this).val());
																});

																// Récupération des informations complémentaires
																var salle_info = $("input#salle_info").val();

																// Récupération du `id_grp_salle` correspondant à la liste 
																$.ajax({
																	async:		false,
																	type:		"POST",
																	dataType:	"JSON",
																	url:		"' . sprintf($this->_locationHref, self::AJAX_ACTION_SUFFIX) . '",
																	data:		{
																		liste_salles:	liste_salles.join(","),
																		salle_info:		salle_info
																	},
																	success:	function(object) {
																		// fonctionnalité réalisée si la réponse est valide
																		if (typeof(object) == "object") {
																			// Chargement des paramètres dans le MODAL
																			$("p.planning-item-location", "article#planning-viewer").text(object.label);
																			$("input[name^=task_locationId]", "article#planning-viewer").val(object.id);
																			$("input[name^=task_locationInfo]", "article#planning-viewer").val(object.info);
																		} else {
																			// Affichage du problème au CLIENT
																			alert("' . self::AJAX_FORMAT_ERROR . '");
																		}
																	},
																	complete:	function() {
																		// Fermeture du MODAL
																		$("div#location-' . $sMD5 . '").dialog("close");
																	},
																	error:		function() {
																		// Affichage du problème au CLIENT
																		alert("' . self::AJAX_MESSAGE_ERROR . '");
																	}
																});
															}
														}],
														close:			function() {
															// Purge du MODAL
															$(this).remove();
														}
													});
												}
											});
										});');
			}

			// Création d'un bouton d'action pour la localisation
			$sTeamButtonSection			= null;
			if ($this->_teamHref) {
				// Identification unique de l'URL
				$sMD5					= md5($this->_teamHref);

				// Intégration de la section dans la vue
				$sTeamButtonSection		= "<section class='hidden " . self::FORM_TEAM . "'>
												<button id=\"search-team-" . $sMD5 . "\">Modifier cette section</button>
											</section>";

				// Appel du MODAL au clic sur le bouton
				ViewRender::addToJQuery('$(document).on("click", "button#search-team-' . $sMD5 . '", function() {
											// Ajout du formulaire MODAL
											$("dialog").append("<div id=\"team-' . $sMD5 . '\"></div>");

											// Création du formulaire MODAL
											$("div#team-' . $sMD5 . '").dialog({
												autoOpen:		false,
												height:			"90%",
												width:			"90%",
												dialogClass:	"no-close",
												buttons:		[{
													text:		"Modifier",
													click:		function() {
														$(this).dialog("close");
													}
												}]
											});

											// Chargement du contenu du MODAL
											$.ajax({
												async:		false,
												type:		"POST",
												dataType:	"HTML",
												url:		"' . sprintf($this->_teamHref, '') . '",
												data:		{
													FORM_MD5:	"' . $sMD5 . '",
													task_matterId:		$("article#planning-viewer").find("input[name^=task_matterId]").val(),
													task_teamId:		$("article#planning-viewer").find("input[name^=task_teamId]").val(),
													task_teamInfo:		$("article#planning-viewer").find("input[name^=task_teamInfo]").val(),
													task_year:			$("article#planning-viewer").find("input[name^=task_year]").val(),
													task_month:			$("article#planning-viewer").find("input[name^=task_month]").val(),
													task_day:			$("article#planning-viewer").find("input[name^=task_day]").val(),
													task_hour:			$("article#planning-viewer").find("input[name^=task_hour]").val(),
													task_duration:		$("input#id_modal_duree").val()
												},
												success:	function(response) {
													// Ajout du formulaire MODAL
													$("dialog").append("<div id=\"team-' . $sMD5 . '\">" + response + "</div>");

													// Création du formulaire MODAL de sélection des FORMATEURS
													$("div#team-' . $sMD5 . '").dialog({
														title:			"Sélectionnez le(les) formateurs(s) et le volume horaire de réservation",
														maxHeight:		$("article#main-article").height() - 100 + " px",
														width:			"90%",
														dialogClass:	"no-close",
														buttons:		[{
															text:		"Modifier",
															click:		function() {
																// Récupération de la liste des formateurs sélectionnés
																var		liste_principal = [];
																$("input[name^=formateur_principal]:checked", this).each(function() {
																	// Ajout du personnel à la collection des PRINCIPAUX
																	liste_principal.push($(this).val());
																});

																// Récupération de la liste des formateurs secondaires
																var		liste_secondaire = [];
																$("input[name^=groupe_formateur]:checked", this).each(function() {
																	// Filtre les personnels déjà présents en formateur principal
																	if (liste_principal.indexOf($(this).val()) == -1 && $(this).val()) {
																		// Ajout du personnel à la collection des SECONDAIRES
																		liste_secondaire.push($(this).val());
																	}
																});

																// Récupération des informations complémentaires
																var formateur_info = $("input#formateur_info").val();

																// Récupération du `ID` associé à la liste de formateurs
																$.ajax({
																	async:		false,
																	type:		"POST",
																	dataType:	"JSON",
																	url:		"' . sprintf($this->_teamHref, self::AJAX_ACTION_SUFFIX) . '",
																	data:		{
																		liste_principal:	liste_principal.join(","),
																		liste_secondaire:	liste_secondaire.join(","),
																		formateur_info:		formateur_info,
																		task_matterId:		$("article#planning-viewer").find("input[name^=task_matterId]").val()
																	},
																	success:	function(object) {
																		// fonctionnalité réalisée si la réponse est valide
																		if (typeof(object) == "object") {
																			// Chargement des paramètres dans le MODAL
																			$("li.principal", "article#planning-viewer").html(object.principal);
																			$("li.secondaire", "article#planning-viewer").html(object.secondaire);
																			$("input[name^=task_teamId]", "article#planning-viewer").val(object.id);
																			$("input[name^=task_teamInfo]", "article#planning-viewer").val(object.info);
																		} else {
																			// Affichage du problème au CLIENT
																			alert("' . self::AJAX_FORMAT_ERROR . '");
																		}
																	},
																	complete:	function() {
																		// Fermeture du MODAL
																		$("div#team-' . $sMD5 . '").dialog("close");
																	},
																	error:		function() {
																		// Affichage du problème au CLIENT
																		alert("' . self::AJAX_MESSAGE_ERROR . '");
																	}
																});
															}
														}],
														close:			function() {
															// Purge du MODAL
															$(this).remove();
														}
													});
												}
											});
										});');
			}

			// Ajout d'un élément
			$this->_html = "<li class='item $sPanelClass " . $this->_class . " ui-widget-content ui-corner-tr ui-draggable' align='center' $sBackground>
								<article title=\"" . $this->_matterHTML . "\" class='job padding-0' align='center'>
									<h3 class='strong left max-width'>" . $this->_matter . " " . $this->_matterInfo . "</h3>
									<div class='planning-item-information'>" . $this->_information . "</div>
									<div class='content center'>
										<p class='planning-item-location center $sConflictLocation'>
											" . $this->_location . " " . $this->_locationInfo . "
										</p>
										$sLocationButtonSection
										<section class='item-item-content'>" . $this->_content . "</section>
										<ul class='planning-item-team $sConflictTeam'>
											<li class='principal'>"		. implode(" - ", $this->getTeam(self::TYPE_PRINCIPAL))	. "</li>
											<li class='secondaire'>"	. implode(" - ", $this->getTeam(self::TYPE_SECONDAIRE))	. "</li>
										</ul>
										$sTeamButtonSection
										<input type=\"hidden\" value=\""	. $this->_id			. "\" name=\"task_id[]\">
										<input type=\"hidden\" value=\""	. $this->_year			. "\" name=\"task_year[]\">
										<input type=\"hidden\" value=\""	. $this->_month			. "\" name=\"task_month[]\">
										<input type=\"hidden\" value=\""	. $this->_day			. "\" name=\"task_day[]\">
										<input type=\"hidden\" value=\""	. $this->_hour			. "\" name=\"task_hour[]\">
										<input type=\"hidden\" value=\""	. $this->_minute		. "\" name=\"task_minute[]\">
										<input type=\"hidden\" value=\""	. $this->_duration		. "\" name=\"task_duration[]\">
										<input type=\"hidden\" value=\""	. $this->_matterId		. "\" name=\"task_matterId[]\">
										<input type=\"hidden\" value=\""	. $this->_matterInfo	. "\" name=\"task_matterInfo[]\">
										<input type=\"hidden\" value=\""	. $this->_locationId	. "\" name=\"task_locationId[]\">
										<input type=\"hidden\" value=\""	. $this->_locationInfo	. "\" name=\"task_locationInfo[]\">
										<input type=\"hidden\" value=\""	. $this->_teamId		. "\" name=\"task_teamId[]\">
										<input type=\"hidden\" value=\""	. $this->_teamInfo		. "\" name=\"task_teamInfo[]\">
										<input type=\"hidden\" value=\""	. $this->_update		. "\" name=\"task_update[]\">
									</div>
								</article>
								<section class='item-bottom'>
									<a class='ui-icon ui-icon-pin-s draggable-item' title='Déplacer cet élément' href='#'>&nbsp;</a>
									$sZoomIn
									$sTrash
								</section>
							</li>";
		}
	}

	/**
	 * @brief	Rendu final HTML
	 * @return	string
	 */
	public function renderHTML() {
		if (! $this->_build) {
			$this->_buildHTML();
		}

		// Renvoi du code HTML
		return $this->_html;
	}

}
