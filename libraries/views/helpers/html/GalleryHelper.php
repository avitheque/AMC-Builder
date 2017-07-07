<?php
/**
 * @brief	Helper de création d'une gallerie.
 *
 * Vue étendue du formulaire permettant d'ajouter des éléments par cliqué/glissé.
 *
 * @li	Exploitation d'un bouton <button id="show-gallery"> pour l'affichage de la bibliothèque.
 *
 * @name		GalleryHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 59 $
 * @since		$LastChangedDate: 2017-07-07 21:01:53 +0200 (Fri, 07 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class GalleryHelper {

	/**
	 * Constante de construction de la liste des identifiants à exclure du résultat.
	 * @var		char
	 */
	const		EXCLUDE_SEPARATOR	= ",";

	/**
	 * Constante de construction de la liste des éléments du formulaire de recherche.
	 * @var		string
	 */
	const		TYPE_SELECT			= "select";

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage	= null;

	/**
	 * @brief	Nom du panel.
	 * @var		string
	 */
	private		$_title				= "Zone d'importation de la bibliothèque";

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	private		$_aForm				= array();

	/**
	 * @brief	Message de résultat non trouvé.
	 * @var		string
	 */
	private		$_empty				= "Aucun résultat n'a été trouvé...";

	/**
	 * @brief	Conteneur HTML de la gallerie SOURCE.
	 * @var		string
	 */
	protected	$gallery			= "";

	/**
	 * @brief	Conteneur HTML du panneau CIBLE.
	 * @var		string
	 */
	protected	$panel				= "";

	/**
	 * @brief	Indicateur de construction.
	 * @var		bool
	 */
	private		$_build				= false;

	/**
	 * @brief	Liste des éléments de la gallerie.
	 * @var		array
	 */
	private		$_aItems			= array();

	/**
	 * @brief	Liste des identifiants à exclure de la gallerie.
	 * @var		array
	 */
	private		$_exclude			= array();

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @return	string
	 */
	public function __construct($bReadonly = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage = InstanceStorage::getInstance();

		//#################################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#################################################################################################

		// Nom de session des données
		$sSessionNameSpace		= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Données du formulaire
		$this->_aForm			= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Initialisation du conteneur
		$this->gallery = "<ul id=\"gallery\" class=\"gallery ui-helper-reset ui-helper-clearfix max-width\">";
	}

	/**
	 * @brief	Initialisation du titre du panneau.
	 *
	 * @param	string	$sTitle			: titre du panneau.
	 * @return	void
	 */
	public function setTitre($sTitle = null) {
		$this->_title	= $sTitle;
	}

	/**
	 * @brief	Initialisation du message de résultat vide.
	 *
	 * @param	string	$sEmptyMessage	: texte à afficher si aucun résultat n'est trouvé.
	 * @return	void
	 */
	public function setEmpty($sEmptyMessage = null) {
		$this->_empty	= $sEmptyMessage;
	}

	/**
	 * @brief	Initialisation de la liste des identifiants à exclure.
	 *
	 * @param	array	$aListExcludeId	: Tableau contenant l'ensemble des identifiants à ne pas prendre en compte.
	 * @return	void
	 */
	public function setExcludeByListId($aListExcludeId = array()) {
		$this->_exclude = array_merge($this->_exclude, (array) $aListExcludeId);
	}

	/**
	 * @brief	Ajout d'un élément.
	 *
	 * @li	Contrôle que l'identifiant de l'élément n'est pas à exclure.
	 *
	 * @example	Exemple d'utilisation avec l'ajout d'un texte et d'une image
	 * @code
	 * 		// Création d'une nouvelle bibliothèque
	 * 		$oGallery = new GalleryHelper();
	 *
	 * 		// Lors du clic sur le [ZOOM] le contenu du modal sera chargé avec l'adresse "/search/question?id=15"
	 * 		$oGallery->addItem("<span class=\"strong\">Contenu de l'élément</span><img src=\"/images/logo.png\" alt=\"Logo\" />", 15, "/search/question?id=%d");
	 *
	 * 		// Récupération du panneau dans le VIEW_MAIN
	 * 		ViewRender::addToMain($oGallery->renderHTML());
	 * @endcode
	 *
	 * @param	string	$sHtml			: Contenu HTML à ajouter.
	 * @param	mixed	$xId			: Identifiant de l'élément.
	 * @param	string	$sHrefZoomIn	: Format du chemin à réaliser lors du clic sur le Zoom.
	 * @return	void
	 */
	public function addItem($sHtml, $xId = null, $sHrefZoomIn = "/index?id=%") {
		// Fonctionnalité réalisée si l'identifiant n'est pas déjà présent dans le questionnaire
		if (! in_array($xId, $this->_exclude)) {
			// Initialisation du bouton [zoom]
			$sZoomIn = "";

			// Fonctionnalité réalisée si le bouton [zoom] peut être affiché
			if (!is_null($xId) && !empty($sHrefZoomIn)) {
				// Ajout de l'icône [zoom]
				$sZoomIn = "<a href=\"" . sprintf($sHrefZoomIn, $xId) . "\" title=\"Voir le contenu\" class=\"ui-icon ui-icon-zoomin\">Détails</a>";
			}

			// Ajout d'un élément
			$this->_aItems[] = "<li class=\"ui-widget-content ui-corner-tr\">" . $sHtml . "
								" . $sZoomIn . "
								<a href=\"#\" title=\"Ajouter cet élément\" class=\"ui-icon ui-icon-plus\">Ajouter une question</a>
							</li>";

			// Ajout de l'identifiant à la collection
			$this->setExcludeByListId($xId);
		}
	}

	/**
	 * @brief	Construction du formulaire de recherche
	 *
	 * @li	La(Les) liste(s) des champs SELECT transite(nt) dans les paramètres de l'instance InstanceStorage.
	 * @li	Un champ caché [exclude] permet de lister les identifiants à ne pas récupérer.
	 *
	 * @param	string	$sAction		: URL du moteur de recherche.
	 * @param	array	$aSearchItems	: tableau BIDIMENTIONNEL contenant les éléments du moteur de recherche.
	 * 	Chaque entrée possède les éléments suivants :
	 * 		- string	'default'		: valeur par défaut du champ HTML ;
	 * 		- string	'index'			: occurence de l'élément (exploité dans le cas d'un champ SELECT) ;
	 * 		- string	'label'			: libellé du champ HTML ;
	 * 		- string	'type'			: type de champ HTML.
	 * @return	void
	 */
	private function _buildSearchForm($sAction, $aSearchItems = array()) {
		// Initialisation du formulaire de recherche
		$sSearch	= "<form action=\"" . $sAction . "\" method=\"post\" name=\"bibliotheque\" id=\"search-bibliotheque\" class=\"no-wrap blue left max-width\">
							<fieldset>
								<legend>Filtre de recherche des questions</legend>
								<ul class=\"margin-H-10p\">";

		// Initialisation des paramètres exploités par AJAX
		$aDataAJAX	= array();

		// Parcours de l'ensemble des listes du formulaire
		foreach ($aSearchItems as $sName => $aItem) {
			// Initialisation des paramètres des champs
			$sId			= "id_" . $sName;
			$sLabel			= $aItem['label'];
			$sIndex			= $aItem['index'];
			$sType			= isset($aItem['type']) ? strtolower($aItem['type']) : InputHelper::TYPE_TEXT;

			$nValue			= 0;
			if (isset($aItem['default'])) {
				// Récupération du champ de référence du formulaire pour la valeur par défaut
				$sDefault		= isset($aItem['default']) ? $aItem['default'] : null;

				// Détermination de la valeur par défaut d'après le champ du formulaire de référence
				$nValue			= DataHelper::get($this->_aForm, $sDefault, DataHelper::DATA_TYPE_INT, $nValue);
			}

			// Construction de l'ensemble HTML
			$sSearch		.= "<li>";

			switch ($sType) {

				case self::TYPE_SELECT:
					// Construction de la liste des options du champ SELECT
					$sOptions	= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData($sIndex), $nValue, '-');

					// Ajout du champ SELECT
					$sSearch	.= "<label for=\"" . $sId . "\" class=\"strong width-150-min width-30p\">" . $sLabel . "</label>
									<select id=\"" . $sId . "\" name=\"" . $sName . "\" class=\"width-50p\">" . $sOptions . "</select>";
					break;

				default:
					// Construction du champ INPUT
					$oInput		= new InputHelper($sName, $nValue, $sType, $sLabel, "strong center max-width");
					$oInput->setId($sId);

					// Ajout du champ INPUT
					$sSearch	.= $oInput->renderHTML();
					break;
			}

			// Ajout de l'entrée pour les options AJAX
			if ($sType == InputHelper::TYPE_CHECKBOX) {
				// Renvoi un bouléen si le champ est coché
				$aDataAJAX[$sId]	= $sName . ': $("#' . $sId . '").is(":checked")';
			} else {
				// Renvoi la valeur du champ
				$aDataAJAX[$sId]	= $sName . ': $("#' . $sId . '").val()';
			}

			// Finalisation
			$sSearch		.= "</li>";
		}

		// Ajout de la liste des identifiants exclus dans une entrée cachée qui sera exploitée par AJAX
		sort($this->_exclude);
		$sSearch	.= "<input type=\"hidden\" name=\"exclude\" value=\"" . implode(self::EXCLUDE_SEPARATOR, $this->_exclude) . "\" />";

		// Ajout de l'entrée cachée aux options AJAX
		$aDataAJAX[]= 'exclude: $("input[name=exclude]").val()';

		// Finalisation du formulaire
		$sSearch			.= "</ul>
								<div class=\"margin-20\">
									<button type=\"reset\" id=\"reset-gallery\" class=\"left no-margin red\">Annuler</button>
									<button type=\"button\" id=\"search-gallery\" class=\"right no-margin blue\">Rechercher</button>
								</div>
							</fieldset>
						</form>";

		// Ajout du script d'ouverture
		$sJQuery = '// Action sur le bouton [Rechercher] de la Gallerie
					$("button#search-gallery").click(function() {
						// Affichage de la bibliothèque
						$.ajax({
							async:		false,
							type:		"POST",
							dataType:	"HTML",
							url:		"' . $sAction . '",
							data:		{' . implode(",", $aDataAJAX) . '},
							success:	function(html) {
								// Chargement de la zone de résultat
								$("section#search-content").html(html);
							},
							complete:	function() {
								// Initialisation de la fonctionnalité de la Gallery
								initGallery();
							}
						});
					});
					
					// Action sur le bouton [Annuler] de la Gallerie
					$("button#reset-gallery").click(function() {
						// Suppression du contenu
						$("section#search-content").html("");		
					});';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);

		// Renvoi du contenu HTML
		return $sSearch;
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
			$this->_build	= true;

			if (count($this->_aItems)) {
				$this->gallery .= implode(chr(10), $this->_aItems);
			} elseif (!empty($this->_empty)) {
				$this->gallery .= sprintf("<h3 class=\"strong center margin-top-50\">%s</h3>", $this->_empty);
			}

			// Finalisation du panneau
			$this->gallery .= "</ul>";

			// Ajout du JavaScript dans la page
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/GalleryHelper.js");

			// Ajout de la feuille de style
			ViewRender::addToStylesheet(FW_VIEW_STYLES . "/GalleryHelper.css");

			// Finalisation du panneau
			$this->panel = '<div id="panel" class="panel ui-widget-content ui-state-default">
								<h4 class="ui-widget-header"><span class="ui-icon ui-icon-plus">' . $this->_title . '</span>' . $this->_title . '</h4>
							</div>
							<button id="show-gallery" type="button" class="green no-margin right">Afficher la bibliothèque</button>
							<br />';
		}
	}

	/**
	 * @brief	Rendu final de la gallerie SOURCE
	 * @return	string
	 */
	public function getGallery() {
		// Construction des éléments si ce n'est pas déjà fait
		$this->_buildHTML();
		// Renvoi de la gallerie
		return $this->gallery;
	}

	/**
	 * @brief	Rendu final du conteneur CIBLE
	 * @return	string
	 */
	public function getPanel() {
		// Construction des éléments si ce n'est pas déjà fait
		$this->_buildHTML();
		// Renvoi du conteneur
		return $this->panel;
	}

	/**
	 * @brief	Rendu final de la gallerie sous forme de MODAL
	 *
	 * @li	Possibilité d'ajouter un moteur de recherche.
	 * @param	string	$sAction		: URL du moteur de recherche.
	 * @param	array	$aSearchItems	: tableau contenant les éléments du moteur de recherche.
	 * @return	string
	 */
	public function renderHTML($sAction = null, $aSearchItems = array()) {
		// Ajout des ressources au Dialog
		$oModal = new ModalHelper("modal-gallery");
		$oModal->setTitle("Bibliothèque");
		$oModal->setResizable(true);
		$oModal->setModal(false);
		$oModal->setDimensions(495);
		$oModal->setForm(false);
		$oModal->setPosition("center", "left top", "window");
		$oModal->linkContent("<section id=\"search-content\">" . $this->getGallery() . "</section>");
		if (!empty($sAction)) {
			$oModal->linkContent($this->_buildSearchForm($sAction, $aSearchItems));
		}
		ViewRender::addToBody($oModal->renderHTML());

		// Ajout du script d'ouverture
		ViewRender::addToJQuery('// Action sur le bouton [Afficher la bibliothèque]
								$("button#show-gallery").click(function() {
									// Affichage de la bibliothèque
									$("#modal-gallery").dialog("open");
									// Désactivation des scrollBars sur la bibliothèque
									$("#modal-gallery").css({overflow: "hidden", overflowY: "auto"});
								});');

		// Renvoi du conteneur
		return $this->getPanel();
	}

}
