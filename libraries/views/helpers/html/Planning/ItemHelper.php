<?php
/**
 * @brief	Helper de création d'un élément du planning.
 *
 * Vue permettant de créer des éléments constituant le planning, pouvant être cliqué/glissé.
 *
 *
 *
 *
 * @li	Chaque élément HTML de la progression embarque des champs cachés renseignés via JavaScript :
 * @code
 * <article class="job">
 * 		<div class="content">
 * 			(...)
 * 			<input type="hidden" name="tache_id[]" />
 * 			<input type="hidden" name="tache_annee[]" />
 * 			<input type="hidden" name="tache_mois[]" />
 * 			<input type="hidden" name="tache_jour[]" />
 * 			<input type="hidden" name="tache_heure[]" />
 * 			<input type="hidden" name="tache_minute[]" />
 * 			<input type="hidden" name="tache_duree[]" />
 * 			<input type="hidden" name="tache_groupe[]" />
 * 			<input type="hidden" name="tache_update[]" />
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
 * @version		$LastChangedRevision: 107 $
 * @since		$LastChangedDate: 2018-03-24 13:49:48 +0100 (Sat, 24 Mar 2018) $
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
	const		TYPE_SELECT					= "select";

	const		ITEM_FORMAT					= 'item-%s';
	const		PLANNING_FORMAT				= 'planning-%d40-%d20-%d20-%d20';

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage			= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	protected	$_id						= null;
	protected	$_title						= "Jour de la semaine";
	protected	$_describe					= "";
	protected	$_content					= "";
	protected	$_groupe					= 0;
	protected	$_participant				= array();
	protected	$_hrefZoomIn				= "#";
	protected	$_hrefTrash					= "#";
	protected	$_class						= "";
	protected	$_year						= 0;
	protected	$_month						= 0;
	protected	$_day						= 0;
	protected	$_hour						= 0;
	protected	$_minute					= 0;
	protected	$_duration					= 1;
	protected	$_timer						= 60;
	protected	$_update					= 0;


	/**
	 * @brief	Indicateur de construction.
	 * @var		bool
	 */
	private		$_build						= false;

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
	 * @param	string	$sTitle				: Titre de l'élément.
	 * @param	string	$sDescribe			: (optionnel) Texte d'information relatif à la tâche.
	 * @param	string	$sContentHTML		: (optionnel) Contenu HTML à ajouter en plus de la descrition.
	 * @param	string	$sClass				: (optionnel) Classe CSS affecté à l'élément.
	 * @return	void
	 */
	public function __construct($xId = null, $sTitle, $sDescribe = "", $sContentHTML = "", $sClass = "") {
		$this->_id			= $xId;
		$this->_title		= $sTitle;
		$this->_describe	= $sDescribe;
		$this->_content		= $sContentHTML;
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
	 * @brief	Initialisation de la classe CSS de l'élément
	 *
	 * @param	string	$sClass				: nom de la classe CSS.
	 * @return	void
	 */
	public function setClass($sClass) {
		$this->_class		= trim($sClass);
	}

	/**
	 * @brief	Ajout d'une classe CSS à l'élément
	 *
	 * @param	string	$sClass				: nom de la classe CSS à ajouter.
	 * @return	void
	 */
	public function addClass($sClass) {
		$this->_class		= empty($this->_class) ? $sClass : $this->_class . " " . trim($sClass);
	}

	/**
	 * @brief	Initialisation de l'action d'interaction avec l'élément
	 *
	 * @param	string	$sAction			: nom de l'URL.
	 * @return	void
	 */
	public function setHrefAction($sAction = "#", $sFormat = "%s?id=%s") {
		$this->_hrefAction	= $sAction;
		$this->_hrefFormat	= $sFormat;
	}

	/**
	 * @brief	Initialisation de l'action de [ZoomIn]
	 *
	 * @param	string	$sHrefZoomIn		: format de l'URL avec intégration de l'identifiant par la méthode PHP `sprintf`.
	 * @return	void
	 */
	public function setHrefZoomIn($sHrefZoomIn = "/planning/zoom?id=%s") {
		$this->_hrefZoomIn	= $sHrefZoomIn;
	}

	/**
	 * @brief	Initialisation de l'action de [Trash]
	 *
	 * @param	string	$sHrefTrash			: format de l'URL avec intégration de l'identifiant par la méthode PHP `sprintf`.
	 * @return	void
	 */
	public function setHrefTrash($sHrefTrash = "/planning/trash?id=%s") {
		$this->_hrefTrash	= $sHrefTrash;
	}

	/**
	 * @brief	Initialisation de la valeur du TimeStamp de l'élément
	 *
	 * @param	date	$dTimeStamp			: date au format TIMESTAMP.
	 * @return	void
	 */
	public function setTimeStamp($dTimeStamp) {
		$this->_year		= date("Y", $dTimeStamp);
		$this->_month		= date("m", $dTimeStamp);
		$this->_day			= date("d", $dTimeStamp);
		$this->_hour		= date("H", $dTimeStamp);
		$this->_minute		= date("i", $dTimeStamp);
	}

	/**
	 * @brief	Initialisation de la valeur de la date et l'heure de l'élément
	 *
	 * @param	date	$dDateTime				: valeur de la date avec l'heure de l'élément.
	 * @return	void
	 */
	public function setDateTime($dDateTime) {
		// Forçage du format de la dans au format MySQL [Y-m-d H:i:s]
		$dDateTimeMySQL					= DataHelper::dateTimeFrToMy($dDateTime);

		// Séparation des parties DATE / TIME
		list($dDateMySQL, $dTimeMySQL)	= explode(" ", $dDateTimeMySQL);

		// Extraction des paramètres de la DATE
		$aDateItems						= explode("-", $dDateMySQL);
		$this->_year					= DataHelper::get($aDateItems, 0, DataHelper::DATA_TYPE_INT);
		$this->_month					= DataHelper::get($aDateItems, 1, DataHelper::DATA_TYPE_INT);
		$this->_day						= DataHelper::get($aDateItems, 2, DataHelper::DATA_TYPE_INT);

		// Extraction des paramètres du TIME
		$aTimeItems						= explode(":", $dTimeMySQL);
		$this->_hour					= DataHelper::get($aTimeItems, 0, DataHelper::DATA_TYPE_INT);
		$this->_minute					= DataHelper::get($aTimeItems, 1, DataHelper::DATA_TYPE_INT);
	}

	/**
	 * @brief	Initialisation de la valeur de la date de l'élément
	 *
	 * @param	integer	$dDate				: valeur de la date.
	 * @return	void
	 */
	public function setDate($dDate) {
		// Forçage du format de la dans au format MySQL [Y-m-d]
		$dDateMySQL			= DataHelper::dateFrToMy($dDate);

		// Extraction des paramètres de la DATE
		list($this->_year, $this->_month, $this->_day)	= explode("-", $dDateMySQL);
	}

	/**
	 * @brief	Initialisation de la valeur de l'année de l'élément
	 *
	 * @param	integer	$nYear				: valeur de l'année sur 4 chiffres.
	 * @return	void
	 */
	public function setYear($nYear) {
		$this->_year		= $nYear;
	}

	/**
	 * @brief	Initialisation de la valeur du mois de l'élément
	 *
	 * @param	integer	$nMonth				: valeur du mois sur 2 chiffres.
	 * @return	void
	 */
	public function setMonth($nMonth) {
		$this->_month		= $nMonth;
	}

	/**
	 * @brief	Initialisation de la valeur du jour de l'élément
	 *
	 * @param	integer	$nDay				: valeur du mois sur 2 chiffres.
	 * @return	void
	 */
	public function setDay($nDay) {
		$this->_day			= $nDay;
	}

	/**
	 * @brief	Initialisation de la valeur de l'heure de l'élément
	 *
	 * @param	integer	$nHour				: valeur de l'heure sur 2 chiffres.
	 * @return	void
	 */
	public function setHour($nHour) {
		$this->_hour		= $nHour;
	}

	/**
	 * @brief	Initialisation de la valeur de la minute de l'élément
	 *
	 * @param	integer	$nMinute			: valeur des minutes sur 2 chiffres.
	 * @return	void
	 */
	public function setMinute($nMinute) {
		$this->_minute		= $nMinute;
	}

	/**
	 * @brief	Initialisation de la valeur de la durée de l'élément
	 *
	 * @param	integer	$nTime				: valeur de la durée en minutes.
	 * @return	void
	 */
	public function setDuration($nTime) {
		$this->_duration	= $nTime;
	}

	/**
	 * @brief	Ajout d'un groupe de participants à la tâche
	 *
	 * @param	mixed	$nIdGroupe			: identidiants du groupe de participants.
	 * @return	void
	 */
	public function setParticipant($nIdGroupe, $aListeParticipant = array()) {
		$this->_groupe			= $nIdGroupe;
		$this->_participant		= (array) $aListeParticipant;
	}

	public function _buildHTML($bDeletable = true) {
		// Fonctionnalité réalisée si la construction n'a pas été réalisée
		if (! $this->_build) {
			// Enregistrement du rendu
			$this->_build		= true;
			$this->_titleHTML	= $this->_title;

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
			if (!empty($this->_participant)) {
				foreach ($this->_participant as $sNomHTML) {
					$this->_titleHTML .= "\n\t└─ " . DataHelper::extractContentFromHTML($sNomHTML);
				}
			}

			// Ajout d'un élément
			$this->_html = "<li class='item " . $this->_class . " ui-widget-content ui-corner-tr ui-draggable' align='center'>
								<article title=\"" . $this->_titleHTML . "\" class='job padding-0' align='center'>
									<h3 class='strong left max-width'>" . $this->_title . "</h3>
									<div class='content center'>
										<p class='center'>" . $this->_describe . "</p>
										
										<section class='planning-item-content'>" . $this->_content . "</section>
										
										<section class='planning-item-participant'>" . implode(" - ", $this->_participant) . "</section>
										
										<input type=\"hidden\" value=" . $this->_id			. " name=\"tache_id[]\">
										<input type=\"hidden\" value=" . $this->_year		. " name=\"tache_annee[]\">
										<input type=\"hidden\" value=" . $this->_month		. " name=\"tache_mois[]\">
										<input type=\"hidden\" value=" . $this->_day		. " name=\"tache_jour[]\">
										<input type=\"hidden\" value=" . $this->_hour		. " name=\"tache_heure[]\">
										<input type=\"hidden\" value=" . $this->_minute		. " name=\"tache_minute[]\">
										<input type=\"hidden\" value=" . $this->_duration	. " name=\"tache_duree[]\">
										<input type=\"hidden\" value=" . $this->_groupe		. " name=\"tache_groupe[]\">
										<input type=\"hidden\" value=" . $this->_update		. " name=\"tache_update[]\">
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
