<?php
/**
 * Classe de gestion du menu de l'application.
 *
 * @name		MenuHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 75 $
 * @since		$LastChangedDate: 2017-08-02 23:54:49 +0200 (Wed, 02 Aug 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class MenuHelper extends ViewRender {

	/**
	 * @brief	Constante de configuration de la navigation.
	 * @var		string	`navigation.ini` par défaut.
	 */
	const	FILENAME_INI		= 'navigation.ini';
	const	MAIN_MENU			= 'main';
	private $_init				= array();

	private $_controller		= FW_DEFAULTCONTROLLER;
	private $_action			= FW_DEFAULTACTION;
	private $_acl				= array();
	private $_html				= array();

	/**
	 * Classe de contruction du menu.
	 * @param	array	$aAcl			: tableau ACL de l'utilisateur en cours.
	 * @param	string	$sController	: nom du contrôleur.
	 * @param	string	$sAction		: nom de l'action.
	 * @return	void
	 */
	public function __construct(array $aAcl, $sController = null, $sAction = null) {
		// Récupération des ACL sous forme de tableau
		$this->_init					= ParseIniFile::parse(null, self::FILENAME_INI);

		// Initialisation des variables d'instance
		$this->_acl						= $aAcl;
		$this->_controller				= $sController;
		$this->_action					= $sAction;

		// Construction du menu principal
		foreach ($this->_init as $sSection => $aMenu) {
			foreach ($aMenu as $sRessource => $sLabel) {
				// Construction du menu principal
				if ($sSection == self::MAIN_MENU) {
					// Menu principal
					$this->setMenuController($sLabel, $sRessource);
				} elseif (is_string($sLabel) && array_key_exists($sSection, $this->_html)) {
					// Menu secondaire
					$this->addItemToMenuController($sRessource, $sLabel, $sLabel);
				}
			}
		}

		// Rendu final du menu
		ViewRender::addToMenu($this->renderHTML());
	}

	/**
	 * Méthode permettant de créer un menu relatif à un contrôleur.
	 *
	 * @li Si l'action courante correspond au menu, le menu sera déclaré comme sélectionné.
	 * La classe CSS [.selected] sera affecté au menu.
	 *
	 * @param	string	$sLabel			: libellé du menu.
	 * @param	string	$sRessource		: nom de la ressource.
	 * @return	void
	 */
	public function setMenuController($sLabel, $sRessource = "") {
		// Teste si l'utilisateur courant a le droit d'accès sur la ressource
		if (!in_array($sRessource, $this->_acl)) {
			return false;
		}

		// Initialisation de la classe CSS
		$sClass					= "";
		// Fonctionnalité réalisée si le contrôleur courant correspond au menu
		if ($sRessource == $this->_controller) {
			// Sélection de l'élément
			$sClass				= "selected";
		}

		// Construction du menu
		$this->_html[$sRessource][] = array(
			'label'				=> $sLabel,
			'ressource'			=> $sRessource,
			'class'				=> $sClass
		);
	}

	/**
	 * Méthode permettant d'ajouter un élément au menu d'un contrôleur.
	 *
	 * @li Si l'action courante correspond au menu, le menu sera déclaré comme sélectionné.
	 * La classe CSS [.selected] sera affecté au menu.
	 *
	 * @param	string	$sMenuController	: ressource du menu.
	 * @param	string	$sLabel				: libellé du menu.
	 * @param	string	$sRessource			: nom de la ressource.
	 * @return	void
	 */
	public function addItemToMenuController($sMenuController, $sLabel, $sRessource = "") {
		// Teste si l'utilisateur courant a le droit d'accès sur la ressource
		if (!in_array($sMenuController, $this->_acl) || !in_array($sRessource, $this->_acl)) {
			return false;
		}

		// Initialisation de la classe CSS
		$sClass					= "";
		// Fonctionnalité réalisée si le contrôleur courant correspond au menu
		if ($sRessource == $this->_controller || preg_match("@".$this->_controller."_".$this->_action."@", $sRessource)) {
			// Sélection de l'élément
			$sClass				= "selected";
			// Sélection du menu principal
			if (isset($this->_html[$sMenuController][0])) {
				$this->_html[$sMenuController][0]['class'] = $sClass;
			}
		}

		// Construction du menu
		$this->_html[$sMenuController][] = array(
			'label'				=> $sLabel,
			'ressource'			=> $sRessource,
			'class'				=> $sClass
		);
	}

	/**
	 * Méthode permettant de générer le menu.
	 *
	 * @return	string
	 */
	public function renderHTML() {
		// Initialisation du résultat
		$sHTML					= "";

		foreach ($this->_html as $aRessource) {
			// Fonctionnalité réalisée s'il n'y a qu'un seul élément dans le menu
			if (count($aRessource) == 0) {
				// Récupération de la première occurrence
				$aItem			= $aRessource[0];

				// Récupération du libellé
				$sLabel			= $aItem['label'];
				$sRessource		= $aItem['ressource'];
				$sClass			= $aItem['class'];

				// Menu unique
				$sHTML .= sprintf('<li id="%s" class="%s"><a href="/%s">%s</a></li>', $sRessource, $sClass, $sRessource, $sLabel);
			} else {
				// Parcours de l'ensemble des menu et sous-menus
				foreach ($aRessource as $nOccurrence => $aItem) {
					// Récupération du libellé
					$sLabel		= $aItem['label'];
					$sRessource	= $aItem['ressource'];
					$sLink		= str_ireplace("_", "/", $sRessource);
					$sClass		= $aItem['class'];

					// Le menu principal est à l'occurrence [0]
					if (empty($nOccurrence) && $sLink == FW_DEFAULTCONTROLLER) {
						// Menu d'accueil de l'application
						$sHTML	.= sprintf('<li id="%s" class="%s"><a href="/%s">%s</a><ul class="sub-menu">', $sRessource, $sClass, $sLink, $sLabel);
					} elseif (empty($nOccurrence)) {
						// Menu principal
						$sHTML	.= sprintf('<li id="%s" class="%s"><a href="/%s/reset">%s</a><ul class="sub-menu">', $sRessource, $sClass, $sLink, $sLabel);
					} else {
						// Menu secondaire
						$sHTML	.= sprintf('<li id="%s" class="%s"><a href="/%s/reset">%s</a></li>', $sRessource, $sClass, $sLink, $sLabel);
					}

					$nOccurrence++;
				}
				// Finalisation du menu
				$sHTML			.= "</ul><li>";
			}
		}

		// Renvoi du code HTML
		return $sHTML;
	}
}
