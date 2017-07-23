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
 * @version		$LastChangedRevision: 67 $
 * @since		$LastChangedDate: 2017-07-19 00:09:56 +0200 (Wed, 19 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class MenuHelper extends ViewRender {

	/**
	 * @brief	Liste des menus
	 *
	 * Liste des ressources ACL constituant le menu de l'application.
	 *
	 * @li	Possibilité d'ajouter des ACTIONS comme ressource du type [ressource_action]
	 *
	 * ATTENTION : Si une ressource n'est pas déclarée, le menu correspondant ne sera pas affiché.
	 * @li	Le titre de chaque menu est déclaré dans les ressources ACL
	 * @see		application/configs/acl.ini
	 *
	 * @var		array
	 */
	private $_menu				= array(
		'index',									// Accueil
		'login',									// Connexion à un compte
		'creation',									// Créer un QCM
		'importation',								// Importer un QCM au format AMC-TEXT
		'edition',									// Éditer un QCM
		'validation',								// Valider un QCM
		'generation',								// Générer le fichier LaTeX
		'gestion',									// Gérer l'application
		'supervision',								// Administrer le serveur
		'epreuve',									// Participer à une épreuve QCM
		'compte',									// Accéder aux informations du compte

		// Sous-menus de la forme array('ressource_enfant' => 'ressource_parent')
		'developpement'			=> 'index',			// Page pour les développeurs

		'compte_logout'			=> 'compte',		// Déconnexion de l'utilisateur connecté

		'referentiel'			=> 'gestion',		// Gestion du référentiel
		'gestion_groupe'		=> 'gestion',		// Gestion des groupes
		'gestion_utilisateur'	=> 'gestion',		// Gestion des utilisateur de l'application
		'gestion_stage'			=> 'gestion',		// Gestion d'un stage
		'gestion_candidat'		=> 'gestion'		// Gestion des candidats
	);
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
		// Récupération du singleton AclManager
		$oAcl = AclManager::getInstance();

		// Initialisation des variables d'instance
		$this->_acl				= $aAcl;
		$this->_controller		= $sController;
		$this->_action			= $sAction;

		// Construction du menu selon les ACLs
		foreach ((array) $this->_menu as $sSousMenu => $sRessource) {
			// Fonctionnalité réalisée si le sous-menu est valide
			if (is_string($sSousMenu) && array_key_exists($sRessource, $this->_html)) {
				// Menu secondaire
				$this->addItemToMenuController($sRessource, $oAcl->getRessourceLabel($sSousMenu), $sSousMenu);
			} else {
				// Menu principal
				$this->setMenuController($oAcl->getRessourceLabel($sRessource), $sRessource);
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
