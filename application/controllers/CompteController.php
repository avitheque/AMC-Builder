<?php
/**
 * @brief	Classe contrôleur du compte d'un utilisateur.
 *
 * Étend la classe métier AbstractFormulaireController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireController.php
 *
 * @name		CompteController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 58 $
 * @since		$LastChangedDate: 2017-07-06 19:25:04 +0200 (Thu, 06 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class CompteController extends AbstractFormulaireController {

	/**
	 * @brief	Paramètres de l'utilisateur connecté.
	 * @var		integer|string
	 */
	private		$_idGrade					= null;
	private		$_idGroupe					= null;
	private		$_idProfil					= null;
	private		$_idUtilisateur				= null;
	private		$_loginUtilisateur			= null;

	/**
	 * @brief	Instance du modèle de gestion du référentiel de l'application.
	 * @var		ReferentielManager
	 */
	protected $_oReferentielManager		= null;

	/**
	 * @brief	Instance du modèle de gestion des stages et des candidats.
	 * @var		AdministrationManager
	 */
	protected $_oAdministrationManager		= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
   	 * @overload	AbstractFormulaireController::construct()
	 *
	 * @li	Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'COMPTE', CompteInterface::$LIST_CHAMPS_FORM);

		// Initialisation de l'instance du référentiel
		$this->_oReferentielManager			= new ReferentielManager();

		// Initialisation de l'instance de l'administration
		$this->_oAdministrationManager		= new AdministrationManager();

		// Récupération de l'identifiant de grade de l'utilisateur connecté
		$this->_idGrade						= $this->_oAuth->getIdGrade();
		// Récupération de l'identifiant de l'utilisateur connecté
		$this->_idUtilisateur				= $this->_oAuth->getIdUtilisateur();
		// Récupération de l'identifiant du profil de l'utilisateur connecté
		$this->_idProfil					= $this->_oAuth->getIdProfil();
		// Récupération de l'identifiant du groupe de l'utilisateur connecté
		$this->_idGroupe					= $this->_oAuth->getIdProfil();
		// Récupération de l'identifiant du login de l'utilisateur connecté
		$this->_loginUtilisateur			= $this->_oAuth->getLogin();

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {

			case "enregistrer":
				// Message de débuggage
				$this->debug("ENREGISTRER");
				// Exécution de l'action
				$this->enregistrerAction();
			break;

			default:
				// Message de débuggage
				$this->debug("DÉFAUT");
			break;

		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".indexAction()");

		// Construction de la liste des grades
		$this->addToData('liste_grades',	$this->_oReferentielManager->findListeGrades());

		// Construction de la liste des groupes
		$this->addToData('liste_groupes',	$this->_oAdministrationManager->findAllGroupes());

		// Construction de la liste des profiles
		$this->addToData('liste_profils',	$this->_oReferentielManager->findListeProfiles());

		// Actualisation des données du formulaire UTILISATEUR
		$this->resetFormulaire(
			// Initialisation du formulaire avec les données en base
			$this->_oAdministrationManager->chargerUtilisateur($this->_idUtilisateur)
		);
	}

	/**
	 * @brief	Enregistrement du formulaire CANDIDAT, STAGE ou UTILISATEUR.
	 */
	public function enregistrerAction() {
		// Message de débuggage
		$this->debug("BUTTON = " . $this->getParam('button'));

		// Récupération des paramètres du formulaire
		$aParams						= $this->getParamsLike('utilisateur_');

		// Protection contre le changement de grade
		$aParams['utilisateur_grade']	= $this->_idGrade;
		// Protection contre le changement de groupe
		$aParams['utilisateur_groupe']	= $this->_idGroupe;
		// Protection contre le changement d'identifiant
		$aParams['utilisateur_id']		= $this->_idUtilisateur;
		// Protection contre le changement de profil
		$aParams['utilisateur_profil']	= $this->_idProfil;
		// Protection contre le changement de login
		$aParams['utilisateur_login']	= $this->_loginUtilisateur;

		// Actualisation des données du formulaire au cours de l'enregistrement
		$this->resetFormulaire(
			// Enregistrement de l'utilisateur
			$this->_oAdministrationManager->enregistrerUtilisateur($aParams, $this->_idUtilisateur)
		);
	}

	/**
	 * @brief	Déconnexion de l'utilisateur.
	 */
	public function logoutAction() {
		// Purge de l'authentification
		$this->_oAuth->destroy();

		// Redirection vers l'accueil de l'application
		$this->redirect('index');
	}
}
