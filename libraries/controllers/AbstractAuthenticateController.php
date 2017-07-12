<?php
/**
 * @brief	Classe contrôleur permettant d'exploité les paramètres d'authentification de l'utilisateur connecté.
 *
 * Classe contrôleur qui étend la classe contrôleur par défaut de l'application.
 * Les contrôleurs qui étendent directement cette classe DOIVENT être renseignées dans les ressources ACL.
 * @see			{ROOT_PATH}/application/configs/acl.ini
 *
 * @li	Contrôle de l'authentification de l'utilisateur connecté à l'application.
 * Permet de contrôler l'accès à l'application :
 * 	- l'utilisateur connecté doit être reconnu et autorisé par les ACLs ;
 * 	- le profil ne peut accéder qu'aux menus qui lui sont attribués.
 *
 * @li	Construction du menu principal de l'application selon le rôle de l'authentification.
 * Permet d'injecter dans la vue le menu de l'application construit selon les droits ACLs.
 *
 * Étend la classe abstraite AbstractApplicationController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractApplicationController.php
 *
 * @name		AbstractAuthenticateController
 * @category	Controllers
 * @package		Classes
 * @subpackage	Libraries
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 66 $
 * @since		$LastChangedDate: 2017-07-12 19:33:31 +0200 (Wed, 12 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class AbstractAuthenticateController extends AbstractApplicationController {

	/**
	 * @brief	Nom de session de stockage du contrôleur.
	 * @var		array
	 */
	protected	$_sessionNameSpace	= __CLASS__;

	/**
	 *
	 * @var		AuthenticateManager
	 */
	protected	$_oAuth				= null;

	/**
	 *
	 * @var		AclManager
	 */
	protected	$_oAcl				= null;

	/**
		 * @var		MenuHelper
		 */
	private		$_oMenu				= null;

	/**
		 * @var		TaskbarHelper
		 */
	private		$_oBar				= null;

	/**
	 * Constructeur de la classe de l'application.
	 *
		 * @overload	AbstractApplicationController::construct($sNameSpace = __CLASS__)
	 *
	 * @param	string $sNameSpace		: Nom du contrôleur à appeler, par défaut le nom de la classe.
	 */
	public function __construct($sNameSpace = __CLASS__) {
		// Initialisation du contôleur
		parent::__construct($sNameSpace);

		// Récupération du SINGLETON de l'instance AuthenticateManager
		$this->_oAuth	= AuthenticateManager::getInstance();

		// Récupération du rôle de l'utilisateur
		$sRole			= $this->_oAuth->getRole();

		// Récupération de l'instance AclManager
		$this->_oAcl	= AclManager::getInstance();

		// Teste si l'utilisateur a le droit d'accès à la ressource
		if (! $this->_oAcl->isAllowed($sRole, $this->_controller)) {
			// Nom de la ressource du type CONTROLLER / ACTION
			$sRessource = $this->_controller . "/" . $this->_action;
			// Génération d'une exception
			throw new ApplicationException('EAclNotAllowed', $sRessource, $this->_controller, $this->_action);
		}

		// Initialisation du menu
		$this->_oMenu	= new MenuHelper($this->_oAcl->getAcl($sRole), $this->_controller, $this->_action);

		// Initialisation de la barre des tâches
		$this->_oBar	= new TaskbarHelper($this->_oAuth->getParams());

		// Enregistrement des paramètres d'accès à l'application
		$this->addToData('CONTROLLER',	$this->_controller);
		$this->addToData('ACTION',		$this->_action);
		$this->addToData('RENDER',		$this->getParam('render'));
	}

	/**
	 * Méthode de récupération du nom de session du contrôleur
	 *
	 * @return	string
	 */
	public function getSessionNameSpace() {
		return $this->_sessionNameSpace;
	}

	/**
		 * Méthode d'exécution de la classe
		 *
		 * @overload	AbstractApplicationController::execute()
		 */
	public function execute() {
		// Action de déconnexion
		if (in_array('logout', array($this->_controller, $this->_action))) {
			// Effacement des variables de la session
			$this->_oAuth->logout();
			$this->mergeParams();
		}

		// Exécution de la classe parent
		return parent::execute();
	}

	/**
		 * Méthode de redirection vers une ressource de l'application.
		 */
	public function redirect($sRessource, $bLocal = true) {
		// Message de debuggage
		$this->debug("REDIRECTION");

		if ($bLocal) {
			// La ressource est sur le serveur
			$sLocation = $_SERVER['HTTP_HOST'] . "/" . $sRessource;
		} else {
			// La ressource est ailleurs
			$sLocation = $sRessource;
		}

		// Redirection de la page
		header('Location: //' . $sLocation);
	}

	/**
		 * @brief	Méthode de création d'un tableau de valeurs provenant des données d'un formulaire.
		 *
		 * @param	array	$aForm			: ensemble des valeurs du formulaire
		 */
	public function addFormToData(array $aForm) {
		if (is_array($aForm)) {
			foreach ($aForm as $sIndex => $xData) {
				$this->addToData($sIndex, $xData);
			}
		}
	}

	/**
		 * @brief	Méthode de création d'un message de débuggage.
		 *
		 * @param	mixed	$sMessage		: message à afficher, peut être un tableau.
		 */
	public function debug($sMessage, $sTitle = null) {
		if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
			// Fonctionnalité réalisée si le contenu est sous forme de tableau
			if (is_array($sMessage)) {
				$sTemp = $sMessage;

				// Initialisation du message de debuggage
				$sMessage = !empty($sTitle) ? "$sTitle = " : "";

				$sMessage .= 'array(';
				foreach ($sTemp as $sKey => $sValue) {
					$sMessage .= '<br /><span>' . $sKey . '</span> => ' . $sValue;
				}
				$sMessage .= '<br />)';
			}

			// Ajout du message dans VIEW_DEBUG
			ViewRender::addToDebug(implode('<br />', (array) $sMessage));
		}
	}

	/**
	 * @brief	Méthode de stockage de données en session.
	 *
	 * @li	Stockage intermédiaire réalisé dans la variable du contôleur.
	 *
	 * @param	mixed	$xData			: ensemble des données.
	 * @param	string	$sIndex			: nom de stockage de la variable.
	 * @return	void
	 */
	public function sendDataToSession($xData, $sIndex) {
		// Stockage des données dans la variable du contrôleur
		$this->addToData($sIndex, $xData);

		if (is_null($xData)) {
			// Suppression de l'entrée en session
			unset($_SESSION[$this->_sessionNameSpace][$sIndex]);
		} else {
			// Stockage des données en SESSION
			$_SESSION[$this->_sessionNameSpace][$sIndex]	= $xData;
		}
	}

	/**
	 * @brief	Méthode de suppression de données en session.
	 *
	 * @li	Suppression intermédiaire réalisée dans la variable du contôleur.
	 *
	 * @param	string	$sIndex			: nom de stockage de la variable.
	 * @return	void
	 */
	public function resetDataIntoSession($sIndex) {
		// Stockage des données dans la variable du contrôleur
		unset($this->aData[$sIndex]);
		// Stockage des données en SESSION
		unset($_SESSION[$this->_sessionNameSpace][$sIndex]);
	}

	/**
	 * @brief	Méthode de contrôle de données en session.
	 *
	 * @param	string	$sIndex			: nom de stockage de la variable.
	 * @return	mixed
	 */
	public function issetSessionData($sIndex) {
		if (isset($_SESSION[$this->_sessionNameSpace])) {
			return array_key_exists($sIndex, $_SESSION[$this->_sessionNameSpace]);
		} else {
			return null;
		}
	}

	/**
	 * @brief	Méthode de récupération de données en session.
	 *
	 * @param	string	$sIndex			: nom de stockage de la variable.
	 * @return	mixed
	 */
	public function getDataFromSession($sIndex) {
		if (isset($_SESSION[$this->_sessionNameSpace])) {
			return DataHelper::get($_SESSION[$this->_sessionNameSpace], $sIndex);
		} else {
			return null;
		}
	}

	/**
		 * @brief	Méthode de stockage de données du formulaire en session.
		 *
		 * @li	Parcours les paramètres du formulaire dans la variable de classe du contrôleur @a $this->aParams.
		 *
	 * @param	array	$aForm			: ensemble des noms de champ du formulaire.
	 * @param	string	$sIndex			: nom de stockage de la variable.
	 * @return	array
	 */
	public function addFormToSession(array $aForm, $sIndex) {
		// Initialisation du tableau par le contenu en session
		$aDataSession = $this->getDataFromSession($sIndex);

		$aData = array();
		// Parcours de l'ensemble des champs
		foreach ($aForm as $sParam => $iType) {
			// Récupère le champ en session en premier
			$xValue = DataHelper::get($aDataSession, $sParam, DataHelper::DATA_TYPE_ANY);

			// Récupération des paramètre du formulaire s'ils sont présents
			$xParam = $this->getParam($sParam);
			if (!is_null($xParam)) {
				$xValue = $xParam;
			}

			// Ajout du paramètre aux données
			$aData[$sParam] = $xValue;
		}

		// Stockage des données en session
		$this->sendDataToSession($aData, $this->_sessionNameSpace);

		// Renvoi des données à jour
		return $aData;
	}

	/**
	 * @brief	Purge de la session courante.
	 *
	 * Redirection afin d'effacer les éléments présents en GET
	 *
	 * @li	Préserve les sessions nécessaires à l'application.
	 *
	 * @param	string		$sSessionName	: (optionnel) non de la session à purger, sinon toutes les sessions.
	 * @return	void
	 */
	protected function _clearSession($sSessionName = null) {
		$aApplicationSessions = explode("|", APPLICATION_SESSIONS);

		$this->resetDatas();
		$this->resetParams();

		// Fonctionnalité réalisée si le nom de session
		if (empty($sSessionName)) {
			foreach ($_SESSION as $sSessionName => $xData) {
				if (in_array($sSessionName, $aApplicationSessions)) {
					continue;
				} else {
					// Message de debuggage
					$this->debug("Purge de la session \"<i>$sSessionName</i>\"");
					unset($_SESSION[$sSessionName]);
				}
			}
		} elseif (!in_array($sSessionName, $aApplicationSessions)) {
			// Message de debuggage
			$this->debug("Purge de la session \"<i>$sSessionName</i>\"");
			unset($_SESSION[$sSessionName]);
		}
	}

	/**
	 * @brief	Initialisation de l'action du contrôleur.
	 *
	 * @li	Cette methode est exécutée avant chaque action du contrôleur.
	 *
	 * @return	void
	 */
	public function initAction() {
		/** @todo Initialisation de l'action */
	}

	/**
	 * @brief	Finalisation de l'action du contrôleur.
	 *
	 * @li	Cette methode est exécutée à la fin de chaque action du contrôleur.
	 *
	 * @return	void
	 */
	public function finalAction() {
		/** @todo Finalisation de l'action */
	}

	/**
	 * @brief	Action de réinitialisation du contrôleur.
	 *
	 * Redirection afin d'effacer les éléments présents en GET
	 * @param	string		$sRessource		: (optionnel) ressource pour la redirection, sinon le contrôleur est appelé par défaut.
	 * @param	array		$aDataSession	: (optionnel) paramètres à transférer en session.
	 * @return	void
	 */
	public function resetAction($sRessource = null, $aDataSession = array()) {
		$this->_clearSession($this->_sessionNameSpace);
		foreach ($aDataSession as $sIndex => $xData) {
			$this->sendDataToSession($xData, $sIndex);
		}
		$this->redirect(!empty($sRessource) ? $sRessource : $this->_controller);
	}

	/**
		 * @brief	Méthode d'affichage de l'exception sous forme de message d'erreur.
		 *
	 * @param	Exception $oException exception qui est rencontrée.
	 * @param	string $sMessage message supplémentaire à afficher.
		 */
	public function showException($oException, $sMessage = null) {
		try {
			// Affichage d'un message
			if (!is_null($sMessage)) {
				$this->storeError($sMessage);
			}

			// Vérification de la présence de l'exception
			if (!is_null($oException)) {
				// Récupération de l'objet de l'exception parent
				if (method_exists($oException, "getCause") && is_object($oException->getCause())) {
					$this->storeError($oException->getCause()->getMessage());
				}
				// Récupération de l'objet de l'exception enfant
				if (!is_null($oException->getMessage())) {
					$this->storeError($oException->getMessage());
				}

				// Récupération de la trace de l'exception enfant en mode débug
				if (defined('MODE_DEBUG') && (bool) MODE_DEBUG && !is_null($oException->getTraceAsString())) {
					$this->storeError($oException->getTraceAsString());
				}
			}
		} catch (Exception $e) {
			// Affichage de l'exception sous forme de message d'erreur
			$this->showException($e, "Une erreur s'est produite lors de la récupération de l'Exception.");
		}
	}

}
