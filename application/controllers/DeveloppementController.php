<?php
/**
 * @brief	Classe contrôleur de documentation pour les développeurs.
 *
 * Étend la classe abstraite AbstractAuthenticateController.
 * @see			{ROOT_PATH}/application/controllers/AbstractAuthenticateController.php
 *
 * @name		DeveloppementController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 15 $
 * @since		$LastChangedDate: 2017-04-29 21:33:00 +0200 (Sat, 29 Apr 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class DeveloppementController extends AbstractAuthenticateController {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__);

		// Transmission de paramètres à la vue
		$aConnexion = array(
			'login'		=> "sic",
			'password'	=> "master",
			'webmaster'	=> (int) $this->_oAuth->isProfil(AclManager::ID_PROFIL_WEBMASTER)
		);

		// Stockage de la variable avec un nom de déclaration
		$this->addToData('connexion', $aConnexion);

		// Envoi d'un message de debuggage uniquement en MODE_DEBUG
		$this->debug("Message de débuggage généré depuis le contrôleur, visible uniquement en <span class=\"bold italic\">MODE_DEBUG</span>...");
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {
		/**
		 * Rendu de la vue [developpement/index.phtml] par défaut
		 *
		 * @code
		 * 	// Équivalent à la ligne suivante :
		 * 	$this->render(FW_DEFAULTVIEW);
		 *
		 * 	// Ou la ligne suivante si la constante FW_DEFAULTVIEW correspond bien à [index]
		 * 	$this->render('index');
		 * @endcode
		 */
		// Création d'un premier message d'information destiné à avertir l'utilisateur.
		// Dans l'ordre de création, celui-ci sera affiché en ARRIÈRE plan
		ViewRender::setMessageInfo("Tout dépend dans quel ordre le message arrive...<span class=\"right margin-right-5\">(3/3)</span>");

		// Création d'un deuxième message d'information destiné à avertir l'utilisateur.
		// Dans l'ordre de création, celui-ci sera affiché entre deux plans
		ViewRender::setMessageWarning("Un message peut en cacher un autre !<span class=\"right margin-right-5\">(2/3)</span>");

		// Création d'un dernier message d'information destiné à confirmer la réalisation d'un traitement.
		// Dans l'ordre de création, celui-ce sera affiché en PREMIER plan
		ViewRender::setMessageSuccess("Ce message est généré depuis le contrôleur pour confirmer la réalisation d'un traitement à l'utilsateur...<span class=\"right margin-right-5\">(1/3)</span>");
	}

	/**
	 * @brief	Action permettant d'afficher les informations du serveur.
	 *
	 * Fonctionnalité autorisée si l'utilisateur possède le profil Webmaster
	 */
	public function phpinfoAction() {
		// Désactivation du rendu de la page HTML
		ViewRender::setNoRenderer(true);

		/**
		 * Rendu de la vue [developpement/phpinfo.phtml] par défaut
		 */
		if (!$this->_oAuth->isProfil(AclManager::ID_PROFIL_WEBMASTER)) {
			// Fonctionnalité réalisée si l'utilisateur n'est pas le Webmaster !
			$this->_view = FW_VIEW_VOID;
			$this->redirect("developpement");
		}
	}

	/**
	 * @brief	Méthode réalisée lors de la redirection de l'action.
	 */
	public function redirectionAction() {
		/**
		 * Rendu de la vue [developpement/redirection.phtml] par défaut
		 */
		// Rendu d'une vue différente
		$this->render('autre-vue');
	}

	/**
	 * @brief	Méthode réalisée lors de la redirection de l'action.
	 */
	public function javascriptAction() {
		/**
		 * Rendu de la vue [developpement/javascript.phtml] par défaut
		 */
		// Création d'un message d'information destiné à avertir l'utilisateur.
		ViewRender::setMessageWarning("Ne vous trompez pas dans l'alternance des simples et doubles quotes, lorsque vous passez du code HTML à celui du JavaScript !");
	}

	/**
	 * @brief	Méthode réalisée lors de l'exploitation de l'aide sur l'objet GalleryHelper.
	 */
	public function galleryAction() {
		// Désactivation du rendu de la page HTML
		ViewRender::setNoRenderer(true);

		// Transfert de l'identifiant récupéré dans la vue
		$this->addToData('id_gallery', $this->getParam('id'));

		/**
		 * Rendu de la vue [developpement/gallery.phtml] par défaut
		 */
	}

}
