<?php
/**
 * @brief	Helper de création d'un planning au format HTML.
 *
 * Vue étendue du formulaire permettant d'ajouter des éléments par cliqué/glissé.
 *
 * @name		PlanningHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 111 $
 * @since		$LastChangedDate: 2018-03-25 14:37:49 +0200 (Sun, 25 Mar 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PlanningHTMLHelper extends PlanningHelper {

	/**
	 * Construction de l'interface graphique HTML
	 *
	 * @li		PLANNING_DEFAULT_FORMAT				: type visuel de rendu parmis [calendrier, progression]
	 * var		string
	 */
	const		FORMAT_CALENDAR						= "calendar";
	const		FORMAT_PROGRESSION					= "progression";
	const		PLANNING_DEFAULT_FORMAT				= self::FORMAT_CALENDAR;
	
	const		PLANNING_DEFAULT_CLASSNAME			= "diary";
	const		PLANNING_HOLIDAY_CLASSNAME			= "diary holiday";
	const		PLANNING_HEADER_CLASSNAME			= "header";
	
	const 		PLANNING_VALID_CLASS				= "ui-widget-content ui-state-default";
	const 		PLANNING_DEPRECATED_CLASS			= "ui-widget-content ui-state-disabled";
	const		PLANNING_WIDTH_RATIO				= 99;

	const		MODAL_ACTION_DEFAULT				= '/planning/tache';
	private		$_modal								= null;
	private		$_modal_action						= self::MODAL_ACTION_DEFAULT;
	
	protected	$_planning_format					= self::PLANNING_DEFAULT_FORMAT;

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	date	$dDateStart				: Date de début du planning [Y-m-d], possibilité de donner une date au format [jj/mm/aaaa].
	 * @param	integer	$nNbDays				: Nombre de jours à afficher [1-7].
	 * @param	integer	$nStartHour				: Heure de début pour chaque jour.
	 * @param	integer	$nEndHour				: Heure de fin pour chaque jour.
	 * @param	string	$sDeprecatedHours		: Liste d'heures non travaillées, séparées par le caractère [,].
	 * @param	integer	$nTimerSize				: Nombre de minutes par bloc.
	 * @return	string
	 */
	public function __construct($dDateStart = null, $nNbDays = PlanningHelper::PLANNING_DAYS, $nStartHour = PlanningHelper::PLANNING_HOUR_START, $nEndHour = PlanningHelper::PLANNING_HOUR_END, $sDeprecatedHours = PlanningHelper::PLANNING_DEPRECATED_HOURS, $sDeprecatedDays = PlanningHelper::PLANNING_DEPRECATED_DAYS, $nTimerSize = PlanningHelper::PLANNING_TIMER_SIZE) {
		// Construction du PARENT
		parent::__construct($dDateStart, $nNbDays, $nStartHour, $nEndHour, $sDeprecatedHours, $sDeprecatedDays, $nTimerSize);
		
		// Nom de session des données
		$sSessionNameSpace						= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');
		// Données du formulaire
		$this->_aForm							= $this->_oInstanceStorage->getData($sSessionNameSpace);
		// Initialisation du conteneur
		$this->item								= "<ul id=\"planning-item-" . $this->_md5 . "\" class=\"planning-item ui-helper-reset ui-helper-clearfix max-width\">";
	}

	/**
	 * @brief	Initialisation du titre du panneau.
	 *
	 * @param	string	$sTitle					: titre du panneau.
	 * @return	void
	 */
	public function setTitre($sTitle = null) {
		$this->_title							= $sTitle;
	}

	/**
	 * @brief	Initialisation de l'action du formulaire MODALE.
	 *
	 * @param	string	$url					: URL de l'action.
	 * @return	void
	 */
	public function setModalAction($url) {
		$this->_modal_action					= $url;
	}

	/**
	 * @brief	Initialisation du format du rendu.
	 *
	 * @param	string	$sFormat				: nom du format à générer.
	 * @return	void
	 */
	public function setPlanningRender($sFormat = self::PLANNING_DEFAULT_FORMAT) {
		$this->_planning_format					= $sFormat;
	}

	/**
	 * @brief	Construction du formulaire de recherche
	 *
	 * @li	La(Les) liste(s) des champs SELECT transite(nt) dans les paramètres de l'instance InstanceStorage.
	 * @li	Un champ caché [exclude] permet de lister les identifiants à ne pas récupérer.
	 *
	 * @param	string	$sAction				: action du formulaire.
	 * @param	array	$aSearchItems			: tableau BIDIMENTIONNEL contenant les éléments du moteur de recherche.
	 * 	Chaque entrée possède les éléments suivants :
	 * 		- string	'default'				: valeur par défaut du champ HTML ;
	 * 		- string	'index'					: occurence de l'élément (exploité dans le cas d'un champ SELECT) ;
	 * 		- string	'label'					: libellé du champ HTML ;
	 * 		- string	'type'					: type de champ HTML.
	 * 		- string	'value'					: valeur du champ HTML.
	 * @return	void
	 */
	private function _buildSearchForm($sAction, $aSearchItems = array()) {
		// Initialisation du formulaire de recherche
		$sSearch								= "<form action=\"" . $sAction . "\" method=\"post\" name=\"planning-" . $this->_md5 . "\" id=\"search-planning-" . $this->_md5 . "\" class=\"no-wrap blue max-width\">
													<br />
													<fieldset>
														<legend>Propriétés de l'élément</legend>
														<ul class=\"margin-H-10p\">";

		// Initialisation des paramètres exploités par AJAX
		$aDataAJAX	= array();

		// Parcours de l'ensemble des listes du formulaire
		foreach ((array) $aSearchItems as $sName => $aItem) {
			// Initialisation des paramètres des champs
			$sId								= "id_" . $sName;
			$sLabel								= isset($aItem['label'])	? $aItem['label']				: "";
			$sIndex								= isset($aItem['index'])	? $aItem['index']				: 0;
			$sType								= isset($aItem['type'])		? strtolower($aItem['type'])	: InputHelper::TYPE_TEXT;
			$sValue								= isset($aItem['value'])	? strtolower($aItem['value'])	: InputHelper::TYPE_TEXT;

			if (isset($aItem['default'])) {
				// Récupération du champ de référence du formulaire pour la valeur par défaut
				$sDefault						= isset($aItem['default']) ? $aItem['default'] : null;

				// Détermination de la valeur par défaut d'après le champ du formulaire de référence
				$sValue							= DataHelper::get($this->_aForm, $sDefault, DataHelper::DATA_TYPE_TXT, $sValue);
			}

			// Construction de l'ensemble HTML
			$sSearch							.= "			<li>";

			// Traitement selon le type de l'élément HTML
			switch ($sType) {

				case self::TYPE_SELECT:
					// Construction de la liste des options du champ SELECT
					$sOptions					= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData($sIndex), $sValue, '-');

					// Ajout du champ SELECT
					$sSearch					.= "				<label for=\"" . $sId . "\" class=\"strong width-150-min width-30p\">" . $sLabel . "</label>";
					$sSearch					.= "				<select id=\"" . $sId . "\" name=\"" . $sName . "\" class=\"width-50p\">" . $sOptions . "</select>";
					break;

				default:
					// Construction du champ INPUT
					$oInput						= new InputHelper($sName, $sValue, $sType, "width-50p right");
					$oInput->addLabel($sLabel, "strong left width-150-min width-30p");
					$oInput->setId($sId);

					// Ajout du champ INPUT
					$sSearch					.= $oInput->renderHTML();
					break;
			}

			// Ajout de l'entrée pour les options AJAX
			if ($sType == InputHelper::TYPE_CHECKBOX) {
				// Renvoi un bouléen si le champ est coché
				$aDataAJAX[$sId]				= $sName . ': $("#' . $sId . '", "#search-planning-' . $this->_md5 . '").is(":checked")';
			} else {
				// Renvoi la valeur du champ
				$aDataAJAX[$sId]				= $sName . ': $("#' . $sId . '", "#search-planning-' . $this->_md5 . '").val()';
			}

			// Finalisation
			$sSearch							.= "			</li>";
		}

		// Ajout de la liste des identifiants exclus dans une entrée cachée qui sera exploitée par AJAX
		$sSearch								.= "<input type=\"hidden\" name=\"exclude-" . $this->_md5 . "\" value=\"" . implode(self::EXCLUDE_SEPARATOR, $this->_exclude) . "\" />";

		// Ajout de l'entrée cachée aux options AJAX
		$aDataAJAX[]							= 'exclude: $("input[name=exclude-' . $this->_md5 . ']").val()';

		// Finalisation du formulaire
		$sSearch								.= "		</ul>
															<br />
															<hr class=\"blue half-width\"/>
															<div class=\"margin-20\">
																<button type=\"reset\" id=\"reset-item-" . $this->_md5 . "\" class=\"left no-margin red\">Annuler</button>
																<button type=\"button\" id=\"search-item-" . $this->_md5 . "\" class=\"right no-margin blue\">Rechercher</button>
															</div>
														</fieldset>
													</form>";

		// Ajout du script d'ouverture
		$sJQuery								= '// Action sur le bouton [Rechercher] du MODAL
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
														
																// Adaptation de la zone de recherche selon le résultat
																updateModalHeight("' . $this->_md5 . '");
															},
															complete:	function() {
																// Initialisation de la fonctionnalité de planification
																initPlanning("' . $this->_md5 . '");
														
																// Adaptation de la zone de recherche selon le résultat
																updateModalHeight("' . $this->_md5 . '");
															}
														});
													});
													
													// Action sur le bouton [Annuler] de la Gallerie
													$("button#reset-item-' . $this->_md5 . '").on("click", function() {
														// Suppression du contenu
														$("ul#planning-item-' . $this->_md5 . '").html("");
														
														// Adaptation de la zone de recherche selon le résultat
														updateModalHeight("' . $this->_md5 . '");
													});';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);

		// Renvoi du contenu HTML
		return $sSearch;
	}

	/**
	 * @brief	Construction de la progression du jour.
	 *
	 * @param	string	$sClassName				: nom de la classe de la progression.
	 * @param	string	$IdProgression			: identifiant unique de la progression.
	 * @param	date	$dDatePlanning			: date de la progression.
	 */
	private function _buildProgressionHTML($sClassName = self::PLANNING_HEADER_CLASSNAME, $IdProgression = 0, $dDatePlanning = null) {
		$sLibelleJour							= "";
		$sLibelleDate							= "";
		$sTitreJour								= "";
		if (!is_null($dDatePlanning)) {
			// Extraction des informations de la progression à partir de la DATE
			$this->_planning_jour_id			= date("N", $dDatePlanning);
			$sLibelleJour						= $this->_liste_planning_semaine[$this->_planning_jour_id];
			$sLibelleDate						= date('d/m/Y', $dDatePlanning);
		}

		// Découpage du volume horaire
		$nWidth									= intval($this->_planning_jour_width * $this->_tranche_horaire);
		$fWidthItem								= $nWidth - 1.5;
		$sClassItem								= "width-" . $nWidth . "p";

		// Initialisation de la classe CSS pour chaque tranche horaire
		$sDiaryStyle							= "";

		// Fonctionnalité réalisée pour un affichage sous forme de CALENDRIER
		if ($this->_planning_format == self::FORMAT_CALENDAR && $sClassName != self::PLANNING_HEADER_CLASSNAME) {
			// Réinitialisation de la classe de la tâche
			$sClassItem							= "";

			// Calcul de la largeur de chaque volume horaire
			$fDayWidth							= number_format(self::PLANNING_WIDTH_RATIO / $this->_planning_duree, 2);
			$fWidthItem							= ($fDayWidth <= 1) ? 0.5 : intval($fDayWidth);
		}

		// Mise en place d'une classe CSS relative à la dimention des éléments du PLANNING
		$sClassWidthItem						= "width-" . str_replace(".", "-", $fWidthItem) . "p";

		// Affectation de la CLASSE selon si le jour est férié
		$sClassDefault							= in_array($IdProgression, $this->_planning_deprecated_dates)			? self::PLANNING_DEPRECATED_CLASS	: self::PLANNING_VALID_CLASS;

		// Modification de la classe CSS du jour selon s'il n'est pas travaillé
		$sClassDefault							= in_array($this->_planning_jour_id, $this->_planning_deprecated_days)	? self::PLANNING_DEPRECATED_CLASS	: $sClassDefault;

		// Libellé du jour
		$sTitreJour								= "<h3>" . strtoupper($sLibelleJour) . " " . $sLibelleDate . "</h3>";
		if ($this->_planning_format == self::FORMAT_CALENDAR) {
			$sTitreJour							= "<h5>" . date('d', $dDatePlanning) . "</h5>";
			$sTitreJour							.= "<h5>" . $this->_liste_planning_semaine_court[$this->_planning_jour_id] . "</h5>";
		}

		// Construction du planning du jour
		$sPlanningHTML							= "<dl class=\"" . $sClassName . "\" title=\"" . strtoupper($sLibelleJour) . " " . $sLibelleDate . "\" $sDiaryStyle>
													<dt>
														" . $sTitreJour . "
													</dt>";

		// Finalisation de la zone de progression
		for ($heure = $this->_planning_debut ; $heure <= $this->_planning_fin + 1 ; $heure += $this->_tranche_horaire) {
			// Construction de la dernière ligne du Calendrier afin de définir la dernière plage horaire
			if ($heure > $this->_planning_fin && !is_null($dDatePlanning)) {
				continue;
			}

			// Détermination du crénau horaire
			$h									= $heure%60;
			$m									= ($heure - $heure%60) * 60;

			// Fonctionnalité réalisée par défaut
			$sClassPlanning						= $sClassDefault;
			if ($sClassName == self::PLANNING_DEFAULT_CLASSNAME) {
				// Coloration des zones non travaillées ou normales
				$sClassPlanning					= in_array($h, $this->_planning_deprecated_hours)		? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;
				$sClassPlanning					= in_array($m, $this->_planning_deprecated_hours[$h])	? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;

				// Fonctionnalité réalisée si l'heure spécifique de la journée est non travaillée
				$sClassPlanning					= in_array($h, $this->_planning_deprecated_days[$this->_planning_jour_id]) ? self::PLANNING_DEPRECATED_CLASS : $sClassPlanning;

				// Fonctionnalité réalisée en cas de rendu sous forme de CALENDAR
				if ($this->_planning_format == self::FORMAT_CALENDAR) {
					$sClassPlanning				.= " static";
				}
			}

			// Construction de la cellule horaire
			$sTimeIndex							= sprintf('%02d:%02d', $h, $m);
			/**
			 * @todo	DECOUPAGE HORAIRE - MINUTE
			 * $sPlanningHTML	.= "<dd id=\"planning-" . $IdProgression . "-" . $h . "-" . $m . "\" class=\"planning " . $sClassPlanning . " " . $sClassItem . "\">
			 */
			$oItemElement						= DataHelper::get($this->_aItems[$IdProgression], $sTimeIndex, DataHelper::DATA_TYPE_ANY, null);
			$sClassSet							= null;
			$sItemHTML							= null;

			// Extraction des éléments `Y`, `m` et `d`
			preg_match("@^([0-9]+)\-([0-9]+)\-([0-9]+)$@", $IdProgression, $aMatched);
			// Initialisation de l'identifiant de la CELLULE à partir de l'identifiant de la PROGRESSION sous forme `planning-Y-m-d-H` sans la caractère [0] de début
			$sIdItemElement						= sprintf(Planning_ItemHelper::ID_PLANNING_FORMAT, intval($aMatched[1]), intval($aMatched[2]), intval($aMatched[3]), intval($h));

			// Fonctionnalité réalisée si un élément du PLANNING est présent
			if ($oItemElement instanceof Planning_ItemHelper) {
				// La classe porte l'identifiant de la tâche
				$sClassSet						= $sIdItemElement . " set";
				$oItemElement->addClass($sClassWidthItem);
				$sItemHTML						= $oItemElement->renderHTML();
			}

			// Construction de la CELLULE
			$sPlanningHTML						.= "<dd id=\"" . $sIdItemElement . "\" class=\"planning " . $sClassPlanning . " " . $sClassSet . " " . $sClassItem . "\">
														<h4 class=\"ui-widget-header\">" . $sTimeIndex . "</h4>
														<ul class=\"planning-item ui-helper-reset ui-helper-clearfix\">
															" . $sItemHTML . "
														</ul>
													</dd>";
		}

		// Finalitation du planning du jour
		$sPlanningHTML							.= "</dl>";

		// Renvoi du code HTML
		return $sPlanningHTML;
	}

	/**
	 * @brief	Construction de la progression
	 *
	 * @li	Ajout de la progression à la semaine.
	 *
	 * @param	string	$IdProgression			: identifiant de la CELLULE.
	 * @return	void
	 */
	private function _buildProgressionHTMLByWeek($IdProgression) {
		// Récupération de l'dentifiant du jour
		list($annee, $mois, $jour)				= explode('-', $IdProgression);

		// Extraction de la date du jour à partir de l'identifiant
		$dDatePlanning							= mktime(0, 0, 0, $mois, $jour, $annee);

		// Calcul de la largeur de chaque volume horaire
		$this->_planning_jour_width				= intval(self::PLANNING_MAX_WIDTH / ($this->_planning_fin - $this->_planning_debut));

		// Détermination du nombre de jours dans le même identifiant de semaine
		$nIdSemaine								= date('W', $dDatePlanning);
		$nIdJour								= date("N", $dDatePlanning);

		// Affectation de la CLASSE selon si le jour est férié
		$sClassName								= in_array($IdProgression, $this->_planning_deprecated_dates) ? self::PLANNING_HOLIDAY_CLASSNAME : self::PLANNING_DEFAULT_CLASSNAME;

		// Construction du planning du jour
		$this->_semaine[$nIdSemaine][$nIdJour]	= $this->_buildProgressionHTML($sClassName, $IdProgression, $dDatePlanning);
	}

	/**
	 * @brief	Construction du planning sous forme de PROGRESSION horizontale.
	 *
	 * @param	string		$sFormat			: format de construction.
	 * @return	void
	 */
	private function _getProgressionStandardHTML($sFormat = "%s") {
		foreach ($this->_semaine as $nIdSemaine => $aProgressionHTML) {
			// Construction de la progression avec la SEMAINE
			foreach ($aProgressionHTML as $sHTML) {
				$this->planning	.= sprintf($sFormat, $sHTML);
			}
		}
	}

	/**
	 * @brief	Construction du planning sous forme de TABLEAU.
	 *
	 * @return	void
	 */
	private function _getProgressionTableHTML() {
		// Calcul de la largeur de chaque volume horaire
		$fDayWidth								= number_format(self::PLANNING_WIDTH_RATIO / $this->_planning_duree, 2);
		$sDiaryStyle							= "style=\"width: " . $fDayWidth . "%\"";

		$sHead									= "";
		$sBody									= "";
		$sFoot									= "";

		// Fonctionnalité réalisée uniquement pour le Calendrier
		if ($this->_planning_format == self::FORMAT_CALENDAR) {
			$dDebut								= mktime(0, 0, 0, $this->_planning_mois, $this->_planning_jour, $this->_planning_annee);
			$dFin								= $dDebut + (($this->_planning_duree) * 3600 * 24);
			$nIdMois							= (int) $this->_planning_mois;
			$nCount								= 0;
			while ($dDebut <= $dFin) {
				// Fonctionnalité réalisée à chaque changement de MOIS
				if ($nIdMois != (int) date('m', $dDebut) || $dDebut >= $dFin) {
					// Initialisation des éléments du MOIS
					$nDiff						= $dDebut < $dFin	? 1 : 0;
					$iType						= $nCount <= 3	? DataHelper::SHORT	: DataHelper::UPPER;
					$sTitre						= DataHelper::getLibelleMois((int) date('m', $dDebut) - $nDiff, $iType);
					$sTitre						= $nCount < 6	? $sTitre	: $sTitre . " " . date('Y', $dDebut);
					$sTitre						= $nCount > 1	? $sTitre	: "";

					// Construction du nom du MOIS
					$sHead						.= "	<th id=\"month-$nIdMois\" class=\"center horizontal no-wrap ui-widget-content\" colspan=\"" . $nCount . "\">
															<h5 class=\"left absolute margin-left-5\">$sTitre</h5>&nbsp;
														</th>";

					// Récupération du mois
					$nIdMois					= (int) date('m', $dDebut);
					$nCount						= 0;
				}
				$nCount							+= 1;
				$dDebut							+= self::PLANNING_HEPHEMERIDE;
			}
			// Fin du HEAD
			$sHead		 						.= "	</tr>";
			$sHead								.= "	<tr>";

			// Ajout d'un pied de page au CALENDRIER
			$sFoot								.= "		<td class=\"left no-wrap\">" . $this->_buildProgressionHTML() . "</td>";
		}

		// Parcours de chaque semaine
		foreach ($this->_semaine as $nIdSemaine => $aProgressionSemaine) {
			// Initialisation des éléments de la SEMAINE
			$sTitre								= $this->_planning_format == self::FORMAT_PROGRESSION	? "Semaine $nIdSemaine"	: $nIdSemaine;
			$sClassTitre						= $this->_planning_format == self::FORMAT_PROGRESSION	? "vertical"			: "horizontal";

			// Construction du numéro de SEMAINE
			$sHead								.= "	<th class=\"week-$nIdSemaine center $sClassTitre no-wrap ui-widget-content\" colspan=\"" . count($aProgressionSemaine) . "\">$sTitre</th>";

			// Construction de la progression avec la SEMAINE
			foreach ($aProgressionSemaine as $nIdJour => $sProgressionHTML) {
				$sBody							.= "		<td role=\"week-$nIdSemaine\" class=\"day-$nIdJour center\" $sDiaryStyle>" . $sProgressionHTML . "</td>";
			}
		}

		// Construction du HEAD
		$this->planning							.= "<table class=\"max-width\" cellspacing=0 cellpadding=0>
														<tbody>
															<tr>
																" . $sHead . "
															</tr>";

		// Construction du BODY
		$this->planning							.= "		<tr>
																" . $sBody ."
																" . $sFoot ."
															</tr>
														</tbody>
													</table>";
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
			$this->_build						= true;

			// Fonctionnalité si plusieurs éléments sont récupérés
			if (!empty($this->_empty)) {
				// Aucun élément n'a été trouvé
				$this->item						.= sprintf("<h3 class=\"strong center margin-top-25 padding-bottom-25\">%s</h3>", $this->_empty);
			}

			// Finalisation du panneau
			$this->item							.= "</ul>";

			// Ajout de la CLASSE JavaScript à la page
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/Planning/Item.class.js");
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/Planning/Helper.class.js");

			// Ajout du JavaScript à la page
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/PlanningHelper.js");

			// Ajout de la feuille de style
			ViewRender::addToStylesheet(FW_VIEW_STYLES . "/PlanningHelper.css");
			
			// Initialisation de la liste des jours de la semaine
			for ($i = 0 ; $i < $this->_planning_duree; $i++) {
				// Identifiant du jour de la semaine sous la forme [Y-m-d]
				$IdProgression					= date('Y-m-d', mktime(0, 0, 0, $this->_planning_mois, ($this->_planning_jour + $i), $this->_planning_annee));

				// Récupération de la progression selon l'identifiant du jour
				$this->_buildProgressionHTMLByWeek($IdProgression);
			}

			// Construction de chaque zone de progression selon l'identifiant du jour
			$this->planning						= "<section id=\"" . $this->_md5 . "\" class=\"planningHelper $this->_planning_format week center max-width no-wrap\">";

			// Fonctionnalité réalisée si le format à afficher est au format CALENDAR
			if ($this->_planning_format == self::FORMAT_CALENDAR) {
				// Construction de la progression sous forme de TABLEAU
				$this->_getProgressionTableHTML();
			} else {
				// Construction de la progression
				$this->_getProgressionStandardHTML();
			}

			// Finalisation de la zone de progression
			$this->planning						.= "</section>
													<script type='text/javascript'>
														// Fonctionnalité de déclaration si les éléments n'existent pas
														if (typeof(PLANNING_DEBUG) == 'undefined')			{ var PLANNING_DEBUG = " . ((bool) MODE_DEBUG ? "true" : "false") . "; }
														if (typeof(PLANNING_MD5) == 'undefined')			{ var PLANNING_MD5 = []; }
														if (typeof(PLANNING_CELL_WIDTH) == 'undefined')		{ var PLANNING_CELL_WIDTH = []; }
									
														// Chargement des valeurs des éléments
														PLANNING_MD5['" . $this->_md5 . "'] = '" . $this->_md5 . "';
														PLANNING_CELL_WIDTH['" . $this->_md5 . "'] = " . $this->_nCellWidth . ";
													</script>";
			
			// Fonctionnalité réalisée en MODE_DEBUG
			if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
				$sClassStyle					= $this->_planning_format == self::FORMAT_CALENDAR ? "margin-top-10-important" : "";
				$this->planning					.= "<button class=\"red right $sClassStyle\" id=\"button-" . $this->_md5 . "\" onclick=\"$('section#" . $this->_md5 . "').getProgression();\">Test</button>";
			}

			// Activation du planning par jQuery
			ViewRender::addToJQuery("initPlanning('" . $this->_md5 . "');");
		}
	}

	/**
	 * @brief	Rendu du MODAL
	 *
	 * Ajout du MODAL directement dans VIEW_BODY
	 * @li	Possibilité d'ajouter un moteur de recherche par un tableau de paramètres.
	 * @see PlanningHelper::_buildSearchForm($sAction, $aSearchItems)
	 *
	 * @param	string	$sAction				: URL du moteur de recherche.
	 * @param	array	$aSearchItems			: tableau contenant les éléments du moteur de recherche.
	 * @return	void
	 */
	private function _buildModal($sAction = PlanningHelper::MODAL_ACTION_DEFAULT, $aSearchItems = array()) {
		$oModal = new ModalHelper("modal-item-" . $this->_md5);
		$oModal->addClassName("overflow-hidden");
		$oModal->setTitle("Édition d'un élément");
		$oModal->setResizable(true);
		$oModal->setModal(false);
		$oModal->setDimensions(495);
		$oModal->setForm(false);
		$oModal->setPosition("center", "left top", "window");
		$oModal->linkContent("<section id=\"search-content-" . $this->_md5 . "\" class=\"modal-search $this->_planning_format\">" . $this->getItem() . "</section>");

		// Ajout d'un champ caché relatif à l'identifiant
		$aSearchItems['item_id']				= array(
			'type'	=> 'hidden'
		);

		// Ajout d'un champ relatif à la durée
		$aSearchItems['item_duree']				= array(
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
	 * @brief	Rendu final de l'élément sous forme de MODAL.
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
