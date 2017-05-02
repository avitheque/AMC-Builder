<?php
/**
 * @brief	Classe contrôleur d'authentification d'un utilisateur.
 *
 * Étend la classe abstraite AbstractFormulaireController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireController.php
 *
 * @name		LoginController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class LoginController extends AbstractFormulaireController {

	/**
	 *
	 * @var		UserManager
	 */
	protected $_oUserManager				= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'LOGIN', LoginInterface::$LIST_CHAMPS_FORM);

		// Instance du gestionnaire des utilisateurs
		$this->_oUserManager = new UserManager();

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {
			case "connexion":
				// Message de débuggage
				$this->debug("CONNEXION");
				// Exécution de l'action
				$this->connexionAction();
			break;

			default:
				// Message de débuggage
				$this->debug("DÉFAUT");
				// Réinitialisation du formulaire
				$this->resetFormulaire();
			break;

		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {}

	/**
	 * @brief	Action finale du contrôleur.
	 */
	public function finalAction() {}

	/**
	 * @brief	Action de connexion à l'application.
	 */
	public function connexionAction() {
		// Récupération des champs du formulaire
		$sLogin		= $this->getParam('login');
		$sPassword	= $this->getParam('password');

		// Récupération de l'utilisateur par son login et son mot de passe
		$aUtilisateur = $this->_oUserManager->getUtilisateurByLoginPassword($sLogin, $sPassword);

		if (DataHelper::isValidArray($aUtilisateur)) {
			// Authentification de l'utilisateur dans l'application
			$this->_oAuth->authenticate($aUtilisateur);

			// Enregistrement du log de la connexion
			$this->_oUserManager->logConnexion($aUtilisateur['id_utilisateur']);

			// Redirection vers l'accueil
			$this->redirect('index');
		} else {
			// Suppression de la valeur du mot de passe en session
			$this->setFormulaire('password');

			// Redirection vers la page d'erreur
			$this->render('failure');
		}
	}

}
