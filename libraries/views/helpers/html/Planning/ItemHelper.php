<?php
/**
 * @brief	Helper de création d'un élément du planning.
 *
 * Vue permettant de créer des éléments constituant le planning, pouvant être cliqué/glissé.
 *
 * @name		ItemHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 7 $
 * @since		$LastChangedDate: 2017-04-12 22:49:54 +0200 (mer., 12 avr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class Planning_ItemHelper {

	/**
	 * Constante de construction de la liste des identifiants à exclure du résultat.
	 * @var		char
	 */
	const		EXCLUDE_SEPARATOR			= ",";

	/**
	 * Constante de construction de la liste des heures non travaillées.
	 * @var		char
	 */
	const		DEPRECATED_SEPARATOR		= ",";

	/**
	 * Construction de l'interface graphique
	 * var		PLANNING_DEFAULT_FORMAT		: type visuel de rendu parmis [calendrier, progression]
	 */
	const		FORMAT_CALENDAR				= "calendar";
	const		FORMAT_PROGRESSION			= "progression";
	const 		PLANNING_VALID_CLASS		= "ui-widget-content ui-state-default";
	const 		PLANNING_DEPRECATED_CLASS	= "ui-widget-content ui-state-disabled";
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
	const		PLANNING_DEPRECATED_DAYS	= "6,7";			// Jours de la semaine [1-7] : 1 pour Lundi, 7 pour Dimanche
	const		PLANNING_DEPRECATED_HOURS	= "8,13,18";		// Horaires
	private		$_planning_format			= self::PLANNING_DEFAULT_FORMAT;
	private		$_planning_annee			= 1970;
	private		$_planning_mois				= 1;
	private		$_planning_jour				= 1;
	private		$_planning_duree			= self::PLANNING_DAYS;
	private		$_planning_debut			= self::PLANNING_HOUR_START;
	private		$_planning_fin				= self::PLANNING_HOUR_END;
	private		$_planning_deprecated_days	= self::PLANNING_DEPRECATED_DAYS;
	private		$_planning_deprecated_hours	= self::PLANNING_DEPRECATED_HOURS;

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
	 * @param	string	$sTitle				: Titre de l'élément.
	 * @param	string	$sContent			: Contenu HTML à ajouter.
	 * @param	mixed	$xId				: (optionnel) Identifiant de l'élément.
	 * @param	string	$sHrefZoomIn		: (optionnel) Format du chemin à réaliser lors du clic sur le Zoom.
	 * @param	string	$sClass				: (optionnel) Classe CSS affecté à l'élément.
	 * @return	void
	 */
	public function __construct($sTitle, $sContent, $xId = null, $sHrefZoomIn = "/index?id=%", $sClass = "") {
		// Fonctionnalité réalisée si le bouton [zoom] peut être affiché
		$sZoomIn = "";
		if (!is_null($xId) && !empty($sHrefZoomIn)) {
			$sZoomIn = "<a href=\"" . sprintf($sHrefZoomIn, $xId) . "\" title=\"Voir le contenu\" class=\"ui-icon ui-icon-zoomin\">Détails</a>";
		}
		
		// Ajout d'un élément
		$this->_html = "<li class=\"item $sClass ui-widget-content ui-corner-tr\" align=\"center\">
							<article title=\"$sTitle\" class=\"job padding-0\" align=\"center\">
								<h3 class=\"strong left\">$sTitle</h3>
								<div class=\"content\">
									" . $sContent . "
								</div>
							</article>
							<section class=\"item-bottom tooltip-track\">
								<a class=\"ui-icon ui-icon-pin-s draggable-item\" title=\"Déplacer cet élément\" href=\"#\">&nbsp;</a>
							</section>
							" . $sZoomIn . "
						</li>";
	}

	/**
	 * @brief	Rendu final HTML
	 * @return	string
	 */
	public function renderHTML() {
		// Renvoi du code HTML
		return $this->_html;
	}

}
