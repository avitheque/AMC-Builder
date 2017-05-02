<?php
/**
 * Classe de création d'un formulaire modal.
 *
 * @name		ModalHelper
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
class ModalHelper {

	const	ENCTYPE_NONE				= null;
	const	ENCTYPE_MULTIPART_FORMDATA	= "multipart/form-data";

	const	METHOD_POST					= "post";
	const	METHOD_GET					= "get";

	const	DEFAULT_CLASSNAME			= "modal";

	const	CLASS_VISIBLE				= "visible";
	const	CLASS_HIDDEN				= "hidden";

	/**
	 * @brief	Paramètres du formulaire HTML
	 * @var		string
	 */
	private $_name			= "modal";
	private $_action		= "#";
	private $_method		= self::METHOD_POST;
	private $_aClassName	= array(self::DEFAULT_CLASSNAME);
	private $_visible		= self::CLASS_VISIBLE;
	private $_enctype		= "";
	private $_id			= "";

	/**
	 * @brief	Paramètres de construction du MODAL
	 * @var		array
	 */
	private $_form			= true;
	private $_html			= array();
	private $_script		= array();

	/**
	 * @brief	Paramètres de création du MODAL
	 * @var		string
	 */
	protected $title		= null;
	protected $position		= null;
	protected $width		= null;
	protected $maxWidth		= "document.body.clientWidth - 20";
	protected $height		= null;
	protected $maxHeight	= "document.body.clientHeight - 20";
	protected $autoOpen		= "true";
	protected $draggable	= "true";
	protected $resizable	= "false";
	protected $modal		= "true";
	protected $buttons		= "false";
	protected $closeText	= "Fermer";

	/**
	 * @brief	Liste des paramètres et leur format
	 * @var		array
	 */
	private	 $PARAMS		= array(
		'autoOpen'	=> '%s',
		'draggable'	=> '%s',
		'position'	=> '%s',
		'resizable'	=> '%s',
		'width'		=> '%s',
		'maxWidth'	=> '%s',
		'height'	=> '%s',
		'maxHeight'	=> '%s',
		'modal'		=> '%s',
		'title'		=> '"%s"',
		'closeText'	=> '"%s"',
		'buttons'	=> '%s'
	);

	/**
	 * Classe de contruction du formulaire.
	 *
	 * @li	Le modal n'est pas visible par défaut, un évènement JavaScript doit d'activer.
	 *
	 * @param	string		$sName		: nom du formulaire.
	 * @param	string		$sAction	: nom de l'action.
	 * @param	string		$sMethod	: méthode de transfert des données.
	 * @param	boolean		$bVisible	: visibilité du MODAL.
	 * @return	void
	 */
	public function __construct($sName = null, $sAction = "#", $sMethod = self::METHOD_POST, $bVisible = false) {
		$this->_name		= empty($sName) ? mktime(0, 0, 0) : $sName;
		$this->_action		= $sAction;
		$this->_method		= $sMethod;
		$this->_id			= DataHelper::convertStringToId($this->_name);
		$this->setVisible($bVisible);
	}

	/**
	 * @brief	Modification de l'encodage du transfert de données au formulaire.
	 * Méthode permettant d'ajouter des fichier pour upload vers le serveur.
	 *
	 * @param	string		$sEnctype	: type d'encodage du formulaire.
	 * @return	void
	 */
	public function setEnctype($sEnctype = self::ENCTYPE_MULTIPART_FORMDATA) {
		$this->_enctype		= $sEnctype;
	}

	/**
	 * @brief	Ajout d'un contenu HTML.
	 * Méthode permettant d'ajouter du code HTML dans le conteneur.
	 *
	 * @param	string		$sHTML		: contenu HTML à injecter dans le conteneur.
	 * @return	void
	 */
	public function linkContent($sHTML) {
		$this->_html[]		= implode(chr(13), (array) $sHTML);
	}

	/**
	 * @brief	Ajout d'un élément dans le JavaScript du MODAL.
	 * Méthode permettant d'ajouter du code dans le script JavaScript du MODAL.
	 *
	 * @param	string		$sString	: script à ajouter à la collection.
	 * @param	boolean		$bPrepend	: (optionnel) si le script doit être inséré en premier, sinon à la suite de la collection.
	 * @return	void
	 */
	public function addScript($sString, $bPrepend = false) {
		// Fonctionnalité réalisée si le script n'est pas terminé par le caractère [;]
		if (!preg_match("@.*;$@", $sString)) {
			$sString = ";";
		}
		// Ajout du script à la suite du caractère [Tabulation]
		$sString = chr(9) . $sString;
		if ($bPrepend) {
			$this->_script	= array_merge((array) $sString, $this->_script);
		} else {
			$this->_script[] = $sString;
		}
	}

	/**
	 * @brief	Titre du MODAL
	 * Méthode permettant de modifier le titre du MODAL.
	 *
	 * @param	string		$sString	: titre du MODAL.
	 * @return	void
	 */
	public function setTitle($sString) {
		$this->title 		= $sString;
	}

	/**
	 * @brief	Initialisation du MODAL
	 * Méthode permettant de d'activer l'apparence sous forme MODAL ou fenêtre simple.
	 *
	 * @param	bool		$bModal		: apparence en MODAL ou fenêtre.
	 * @return	void
	 */
	public function setModal($bModal = false) {
		$this->modal		= $bModal		? "true"	: "false";
	}

	/**
	 * @brief	Initialisation du redimentionnement
	 * Méthode permettant de d'activer le redimentionnement de la fenêtre.
	 *
	 * @param	bool		$bResizable	: redimentionnement de la fenêtre.
	 * @return	void
	 */
	public function setResizable($bResizable = false) {
		$this->resizable	= $bResizable	? "true"	: "false";
	}

	/**
	 * @brief	Visibilité du MODAL
	 * Méthode permettant de modifier la visibilité du MODAL.
	 *
	 * @param	bool		$bVisible	: visibilité du MODAL au chargement.
	 * @return	void
	 */
	public function setVisible($bVisible = false) {
		$this->autoOpen		= $bVisible		? "true"	: "false";
		$this->_visible		= $bVisible		? "visible"	: "hidden";
	}

	/**
	 * @brief	Position du MODAL
	 * Méthode permettant de changer la position du MODAL.
	 *
	 * @li	Position par défaut
	 * @code
	 * 		position:	{ my: "center", at: "center", of: window }
	 * @endcode
	 *
	 * @param	string		$my			: largeur du MODAL, NULL par défaut.
	 * @param	string		$at			: hauteur du MODAL, NULL par défaut.
	 * @param	string		$of			: hauteur du MODAL, NULL par défaut.
	 * @return	void
	 */
	public function setPosition($my = "center", $at = "center", $of = "window") {
		$this->position		= sprintf('{my: "%s", at: "%s", of: %s}', $my, $at, $of);
	}

	/**
	 * @brief	Largeur du MODAL
	 * Méthode permettant de changer la largeur du MODAL.
	 *
	 * @param	integer		$nWidth		: valeur numérique de la largeur (nombre de pixels).
	 * @return	void
	 */
	public function setWidth($nWhidth = null) {
		// Renseignement de la LARGEUR
		if (DataHelper::isValidNumeric($nWhidth)) {
			$this->width	= $nWhidth;
		}
	}

	/**
	 * @brief	Hauteur du MODAL
	 * Méthode permettant de changer la hauteur du MODAL.
	 *
	 * @param	integer		$nHeight	: valeur numérique de la hauteur (nombre de pixels).
	 * @return	void
	 */
	public function setHeight($nHeight = null) {
		// Renseignement de la HAUTEUR
		if (DataHelper::isValidNumeric($nHeight)) {
			$this->height	= $nHeight;
		}
	}

	/**
	 * @brief	Dimensions du MODAL
	 * Méthode permettant de changer les dimensions du MODAL.
	 *
	 * @param	integer		$nWidth		: valeur numérique de la largeur (nombre de pixels).
	 * @param	integer		$nHeight	: valeur numérique de la hauteur (nombre de pixels).
	 * @return	void
	 */
	public function setDimensions($nWhidth = null, $nHeight = null) {
		// Renseignement de la LARGEUR
		$this->setWidth($nWhidth);

		// Renseignement de la HAUTEUR
		$this->setHeight($nHeight);
	}

	/**
	 * @brief	Ajout d'un nom de classe au MODAL
	 * Méthode permettant d'ajouter un nom de classe passé en paramètre.
	 *
	 * @li	Seuls les noms n'existant pas dans la collection seront ajoutés.
	 *
	 * @return	void
	 */
	public function addClassName($sClassName = null) {
		// Suppression des caractères [espaces] superflus
		$sClassName = trim($sClassName);

		// Fonctionnalité réalisée si la classe n'est pas déjà présente
		if (!empty($sClassName) && !in_array($sClassName, $this->_aClassName)) {
			$this->_aClassName[$sClassName] = $sClassName;
		}
	}

	/**
	 * @brief	Supression d'un nom de classe du MODAL
	 * Méthode permettant de supprimer le nom de la classe passée en paramètre.
	 *
	 * @li	Seule la classe par défaut ne peut pas être supprimée.
	 *
	 * @return	void
	 */
	public function removeClassName($sClassName = null) {
		// Suppression des caractères [espaces] superflus
		$sClassName = trim($sClassName);

		// Fonctionnalité réalisée si la classe n'est pas déjà présente
		if (!empty($sClassName)) {
			unset($this->_aClassName[$sClassName]);
		}
	}

	/**
	 * @brief	Réinitialisation de la classe par défaut du MODAL
	 * Méthode permettant de réaffecter le nom de la classe par défaut du MODAL.
	 *
	 * @return	void
	 */
	public function resetClassName($sClassName = self::CLASS_DEFAULT) {
		// Réinitialisation de la classe par défaut
		$this->_aClassName = array($sClassName);
	}

	/**
	 * @brief	Désactivation de la balise FORM
	 * Méthode permettant de supprimer le contenu de la balise FORM du modal.
	 *
	 * @return	void
	 */
	public function setForm($bEnable = true) {
		$this->_form		= (bool) $bEnable;
	}

	/**
	 * @brief	Initialisation des paramètres du MODAL
	 *
	 * @li	Exploitation de variable dynamiques lors de la construction des paramètres.
	 *
	 * @return	void
	 */
	private function _init() {
		// Initialisation des paramètres
		$aListeParams = array();
		// Parcours l'ensemble des paramètres et n'ajoute que les éléments renseignés
		foreach ($this->PARAMS as $sAttribute => $sFormat) {
			// Exploitation de variables dynamiques
			if (!empty($this->$sAttribute)) {
				// Construction du format du paramètre
				$sParams = sprintf('%s: %s', $sAttribute, $sFormat);
				// Renseignement du paramètre
				$aListeParams[] = sprintf($sParams, $this->$sAttribute);
			}
		}

		// Initialisation du script de la boîte de dialogue et ses paramètres
		$sScript = sprintf(
			'$("#' . $this->_id . '").dialog({
				%s
			});', implode(',' . chr(10), $aListeParams)
		);

		// Insertion du script
		$this->addScript($sScript, true);
	}

	/**
	 * @brief	Rendu final HTML dans le SKEL
	 * Méthode permettant de générer le MODAL dans la structure de la page HTML.
	 *
	 * @return	void
	 */
	public function renderHTML() {
		// Initialisation des paramètres du MODAL
		$this->_init();

		// Construction de la balise FORM
		$sFormStart	= "";
		$sFormEnd	= "";
		if ($this->_form) {
			// Début HTML du formulaire
			$sFormStart	= sprintf('<form name="%s" action="%s" method="%s" enctype="%s">', $this->_name, $this->_action, $this->_method, $this->_enctype);
			// Fin HTML du formulaire
			$sFormEnd	= '</form>';
		}

		// Ajout du contenu
		ViewRender::addToBody(sprintf('<article id="%s" class="%s %s">%s %s %s</article>', $this->_id, implode(' ', $this->_aClassName), $this->_visible, $sFormStart, implode(" ", $this->_html), $sFormEnd));

		// Ajout de la feuille de style
		ViewRender::addToStylesheet(FW_VIEW_STYLES . "/ModalHelper.css");

		// Ajout du JavaScript à la page
		ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/ModalHelper.js");

		// Ajout d'un JavaScript compémentaire
		ViewRender::addToJQuery($this->_script);
	}

}
