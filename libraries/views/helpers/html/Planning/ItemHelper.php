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
 *		'task_matterIdId'		=> 1,											// Identifiant de la matière
 *
 *		'task_locationId'		=> "Localisation de la tâche...",				// Libellé de la localisation affectée à la tâche
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
 *		'task_update'		=> 0											// Indicateur de modification de la tâche
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
 * @version		$LastChangedRevision: 133 $
 * @since		$LastChangedDate: 2018-05-30 20:17:07 +0200 (Wed, 30 May 2018) $
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

	const		ID_PLANNING_FORMAT		= 'planning-%d-%d-%d-%d';
	const		ID_ITEM_FORMAT			= 'item-%s';

	const		PATTERN_PRINCIPAL		= "@\>(.*)\<@";
	const		FORMAT_PRINCIPAL		= "<B>%s</B>";
	const		FORMAT_SECONDAIRE		= "%s";

	const		TYPE_PRINCIPAL			= 'principal';
	const		TYPE_SECONDAIRE			= 'secondaire';

	const		PANEL_CONFLICT_CSS		= 'panel-conflict';
	const		ITEM_CONFLICT_CSS		= 'item-conflict';

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
	private		$_oInstanceStorage			= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	protected	$_id					= null;

	public		$_matter				= "Libellé de la matière";
	public		$_matterId				= 0;

	public		$_content				= "";

	public		$_location				= "-";
	public		$_locationId			= 0;
	public		$_conflict_location		= false;

	public		$_team					= array();
	public		$_teamId				= 0;
	public		$_conflict_team			= false;

	protected	$_year					= 0;
	protected	$_month					= 0;
	protected	$_day					= 0;
	protected	$_hour					= 0;
	protected	$_minute				= 0;
	protected	$_duration				= 1;
	protected	$_count					= null;
	protected	$_update				= 0;

	protected	$_timer					= 60;

	protected	$_hrefZoomIn			= "#";
	protected	$_hrefTrash				= "#";
	protected	$_class					= "";

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
		return $this->_year;
	}

	/**
	 * @brief	Récupération de la valeur du mois de l'élément
	 * @return	integer
	 */
	public function getMonth() {
		return $this->_month;
	}

	/**
	 * @brief	Récupération de la valeur du jour de l'élément
	 * @return	integer
	 */
	public function getDay() {
		return $this->_day;
	}

	/**
	 * @brief	Récupération de la valeur de l'heure de l'élément
	 * @return	integer
	 */
	public function getHour() {
		return $this->_hour;
	}

	/**
	 * @brief	Récupération de la valeur de la minute de l'élément
	 * @return	integer
	 */
	public function getMinute() {
		return $this->_minute;
	}

	/**
	 * @brief	Récupération de la valeur de la durée de l'élément
	 * @return	integer
	 */
	public function getDuration() {
		return $this->_duration;
	}

	/**
	 * @brief	Récupération du compteur de fin de séance de l'élément
	 * @return	integer
	 */
	public function getCompteur() {
		return $this->_count;
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
	public function getLocation() {
		return $this->_location;
	}

	/**
	 * @brief	Récupération de l'identifiant de la localisation de l'élément
	 * @return	integer
	 */
	public function getLocationId() {
		return $this->_locationId;
	}

	/**
	 * @brief	Récupération du libellé de la matière de l'élément
	 * @return	string
	 */
	public function getMatter() {
		return $this->_matter;
	}

	/**
	 * @brief	Récupération de l'identifiant de la matière de l'élément
	 * @return	integer
	 */
	public function getMatterId() {
		return $this->_matterId;
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
				$aPrincipal[]			= DataHelper::convertToString($aMatched[1], $iFormat);
			} else {
				$aSecondaire[]			= DataHelper::convertToString($sLabel, $iFormat);
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
		$this->_year					= date("Y", $dTimeStamp);
		$this->_month					= date("m", $dTimeStamp);
		$this->_day						= date("d", $dTimeStamp);
		$this->_hour					= date("H", $dTimeStamp);
		$this->_minute					= date("i", $dTimeStamp);
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
		$this->_year					= $aDateParams['Y'];
		$this->_month					= $aDateParams['m'];
		$this->_day						= $aDateParams['d'];

		// Extraction des paramètres du TIME
		$this->_hour					= $aDateParams['H'];
		$this->_minute					= $aDateParams['i'];
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
	public function setCompteur($nCount = null) {
		$this->_count					= $nCount;
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
	 * @return	void
	 */
	public function setMatter($nId, $sLabel = null, $nCount = null) {
		$this->_matterId				= intval($nId);
		$this->_matter					= $sLabel;
		// Fonctionnalité réalisée si un compteur est passé en paramètre
		if (!is_null($nCount)) {
			$this->_count				= $nCount;
		}
	}

	/**
	 * @brief	Attribution d'une localisation à l'élément
	 *
	 * @param	integer	$nId				: identidiants du groupe du local.
	 * @param	string	$sLabel				: description du local.
	 * @return	void
	 */
	public function setLocation($nId, $sLabel = null) {
		$this->_locationId				= intval($nId);
		$this->_location				= $sLabel;
	}

	/**
	 * @brief	Ajout d'un status d'état à l'élément
	 *
	 * @param	boolean	$bUpdate			: valeur du status de modification.
	 * @return	void
	 */
	public function setUpdateStatus($bStatus = false) {
		$this->_update					= (int) $bStatus;
	}

	/**
	 * @brief	Ajout d'un groupe de participants à l'élément
	 *
	 * @param	mixed	$nId				: identidiants du groupe de participants.
	 * @param	array	$aListPerson		: liste des participants.
	 * @param	string	$iType				: (optionnel) type de(s) participant(s) parmi les constantes `TYPE_PRINCIPAL` ou `TYPE_SECONDAIRE`.
	 * @return	void
	 */
	public function setTeam($nId, $aListPerson = array(), $iType = null) {
		$this->_teamId					= $nId;

		// Fonctionnalité réalisée si le type de participant est TYPE_PRINCIPAL
		if ($iType == self::TYPE_PRINCIPAL) {
			// Initialisation de la liste de(s) participant(s)
			$this->_team				= array();

			// Parcours de l'ensemble des participants
			foreach ((array) $aListPerson as $sPerson) {
				$this->_team[]			= sprintf(self::FORMAT_PRINCIPAL, trim($sPerson));
			}
		} else {
			// Initialisation de la liste de(s) participant(s)
			$this->_team				= (array) $aListPerson;
		}
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
				foreach ($this->_team as $sNomHTML) {
					$this->_matterHTML .= "\n\t└─ " . DataHelper::extractContentFromHTML($sNomHTML);
				}
			}

			// Indicateur de conflit sur la cellule
			$sPanelClass				= $this->hasConflict()		? self::PANEL_CONFLICT_CSS	: null;

			// Indicateur de conflit sur les descriptions (SALLES)
			$sConflictLocation			= $this->_conflict_location	? self::ITEM_CONFLICT_CSS	: null;

			// Indicateur de conflit sur les participants
			$sConflictTeam				= $this->_conflict_team		? self::ITEM_CONFLICT_CSS	: null;

			// Ajout d'un élément
			$this->_html = "<li class='item $sPanelClass " . $this->_class . " ui-widget-content ui-corner-tr ui-draggable' align='center'>
								<article title=\"" . $this->_matterHTML . "\" class='job padding-0' align='center'>
									<h3 class='strong left max-width'>" . $this->_matter . "</h3>
									<div class='content center'>
										<p class='planning-item-describe center $sConflictLocation'>" . $this->_location . "</p>
										<section class='planning-item-content'>" . $this->_content . "</section>
										<ul class='planning-item-participant $sConflictTeam'>
											<li class='principal'>"		. implode(" - ", $this->getTeam(self::TYPE_PRINCIPAL))	. "</li>
											<li class='secondaire'>"	. implode(" - ", $this->getTeam(self::TYPE_SECONDAIRE))	. "</li>
										</ul>
										<input type=\"hidden\" value="	. $this->_id			. " name=\"task_id[]\">
										<input type=\"hidden\" value="	. $this->_year			. " name=\"task_year[]\">
										<input type=\"hidden\" value="	. $this->_month			. " name=\"task_month[]\">
										<input type=\"hidden\" value="	. $this->_day			. " name=\"task_day[]\">
										<input type=\"hidden\" value="	. $this->_hour			. " name=\"task_hour[]\">
										<input type=\"hidden\" value="	. $this->_minute		. " name=\"task_minute[]\">
										<input type=\"hidden\" value="	. $this->_duration		. " name=\"task_duration[]\">
										<input type=\"hidden\" value="	. $this->_matterId		. " name=\"task_matterId[]\">
										<input type=\"hidden\" value="	. $this->_locationId	. " name=\"task_locationId[]\">
										<input type=\"hidden\" value="	. $this->_teamId		. " name=\"task_teamId[]\">
										<input type=\"hidden\" value="	. $this->_update		. " name=\"task_update[]\">
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
