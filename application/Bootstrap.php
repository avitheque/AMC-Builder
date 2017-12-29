<?php
	/**
	 * @brief		Initialisation de l'application.
	 *
	 * Classe de lancement et d'initialisation de l'application.
	 *
	 * @li	Permet de charger les ressources de l'application selon l'environnement passé en paramètre.
	 * @li	Appelé par le frontal {ROOT_PATH}/public/index.php
	 *
	 * @name		Bootstrap
	 * @category	Init
	 * @package		Main
	 * @subpackage	Application
	 * @author		durandcedric@avitheque.net
	 * @update		$LastChangedBy: durandcedric $
	 * @version		$LastChangedRevision: 92 $
	 * @since		$LastChangedDate: 2017-12-29 05:09:06 +0100 (Fri, 29 Dec 2017) $
	 *
	 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
	 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
	 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
	 */
	class Bootstrap {

	/**
	 * Instance de la classe SessionMessenger.
	 * @var		SessionMessenger
	 */
	protected  $_oSessionMessenger	= null;

	/**
	 * Instance de la classe InstanceStorage.
	 * @var InstanceStorage
	 */
	protected  $_oInstanceStorage	= null;

	/**
	 * @brief	Construction de la classe.
	 *
	 * Charge la configuration de l'application selon l'environnement passé en paramètre.
	 * @li    Le nom de l'environnement correspond au [nom_de_la_section] présent dans le fichier `application.ini`.
	 *
	 * @param	string	$sEnv		: nom de l'environnement à charger.
	 * @return	void
	 */
	public function __construct($sEnv = 'default') {
		// Récupération du singleton de la zone de stockage entre contrôleurs
		$this->_oSessionMessenger	= SessionMessenger::getInstance();
		$this->_oSessionMessenger->setFirstRender($this->_oSessionMessenger->getLastRender());

		// Récupération du singleton de la zone de stockage entre contrôleurs
		$this->_oInstanceStorage	= InstanceStorage::getInstance();

		// Lecture de la configuration
		$aConfig		=						ParseIniFile::parse($sEnv);

		// Extraction de chaque paramètres
		$aPHP			=						DataHelper::get($aConfig,	'php');
		$aApplication	=						DataHelper::get($aConfig,	'application');
		$aPDO			=						DataHelper::get($aConfig,	'pdo_mysql');
		$aCache			=						DataHelper::get($aConfig,	'cache');
		$aLDAP			=						DataHelper::get($aConfig,	'ldap');

		/**
		 * PHP
		 */
		error_reporting(E_ALL | E_STRICT);
		ini_set('default_charset',				DataHelper::get($aPHP,		'default_charset'));
		ini_set('memory_limit',					DataHelper::get($aPHP,		'memory_limit'));
		ini_set('magic_quotes_gpc',				DataHelper::get($aPHP,		'magic_quotes_gpc',	DataHelper::DATA_TYPE_BOOL));

		set_time_limit(DataHelper::get($aPHP, 'set_time_limit'));
		date_default_timezone_set(DataHelper::get($aPHP, 'date_default_timezone_set',			DataHelper::DATA_TYPE_NONE,		DataHelper::DEFAULT_TIMEZONE));

		/**
		 * APPLICATION
		 */
		defined('APP_NAME')						||	define('APP_NAME',							DataHelper::get($aApplication,	'name'));
		defined('APP_VERSION')					||	define('APP_VERSION',						DataHelper::get($aApplication,	'version'));
		defined('MODE_DEBUG')					||	define('MODE_DEBUG',						DataHelper::get($aApplication,	'mode_debug',				DataHelper::DATA_TYPE_BOOL));
		defined('MODE_DEMO')					||	define('MODE_DEMO',							DataHelper::get($aApplication,	'mode_demo',				DataHelper::DATA_TYPE_BOOL));
		defined('MODE_SUBSTITUTE_USER')			||	define('MODE_SUBSTITUTE_USER',				DataHelper::get($aApplication,	'mode_substitute_user',		DataHelper::DATA_TYPE_BOOL));
		defined('FW_DEFAULTCONTROLLER')			||	define('FW_DEFAULTCONTROLLER',				DataHelper::get($aApplication,	'default_controller'));
		defined('FW_DEFAULTACTION')				||	define('FW_DEFAULTACTION',					DataHelper::get($aApplication,	'default_action'));
		defined('FW_DEFAULTVIEW')				||	define('FW_DEFAULTVIEW',					FW_DEFAULTACTION);
		defined('FW_AUTO_SUBCONTROLLER')		||	define('FW_AUTO_SUBCONTROLLER',				DataHelper::get($aApplication,	'auto_subcontroller',		DataHelper::DATA_TYPE_BOOL));
		defined('FW_AUTO_SUBCONTROLLER_PREFIX')	||	define('FW_AUTO_SUBCONTROLLER_PREFIX',		DataHelper::get($aApplication,	'auto_subcontroller_prefix'));
		defined('FW_UTF8_ENCODE')				||	define('FW_UTF8_ENCODE',					DataHelper::get($aApplication,	'utf8_encode',				DataHelper::DATA_TYPE_BOOL));
		defined('DATE_FORMAT')					||	define('DATE_FORMAT',						DataHelper::get($aApplication,	'date_format',				DataHelper::DATA_TYPE_STR));

		/**
		 * SESSION
		 */
		defined('SESSION_NAME')					||	define('SESSION_NAME',						DataHelper::get($aApplication,	'session_name',				DataHelper::DATA_TYPE_STR,	DataHelper::getTime()));
		$this->_oSessionManager = SessionManager::getInstance(SESSION_NAME);

		/**
		 * BIBLIOTHÈQUE JQUERY
		 */
		defined('JQUERY_VERSION')				||	define('JQUERY_VERSION',					DataHelper::get($aApplication,	'jquery_version'));
		defined('JQUERY_UI_VERSION')			||	define('JQUERY_UI_VERSION',					DataHelper::get($aApplication,	'jquery_UI_version'));
		defined('JQUERY_UI_THEME')				||	define('JQUERY_UI_THEME',					DataHelper::get($aApplication,	'jquery_UI_theme'));
		defined('JQUERY_UI_PATH')				||	define('JQUERY_UI_PATH',					JQUERY_PATH . "/" . JQUERY_UI_VERSION);
		defined('JQUERY_UI_THEME_PATH')			||	define('JQUERY_UI_THEME_PATH',				JQUERY_UI_PATH . "/themes/" . JQUERY_UI_THEME);

		/**
		 * ADDON TIMEPICKER JQUERY
		 */
		defined('SERIAL_JQUERY_UI_ADDON')		||	define('SERIAL_JQUERY_UI_ADDON',			serialize(DataHelper::get($aApplication,	'jquery_UI_addon')));

		/**
		 * BIBLIOTHÈQUE DATATABLES
		 */
		defined('DATATABLES_VERSION')			||	define('DATATABLES_VERSION',				DataHelper::get($aApplication,	'dataTables_version'));
		defined('DATATABLES_PATH')				||	define('DATATABLES_PATH',					JQUERY_PATH . "/" . DATATABLES_VERSION . "/media");
		defined('DATATABLES_JS_PATH')			||	define('DATATABLES_JS_PATH',				DATATABLES_PATH . "/js");
		defined('DATATABLES_CSS_PATH')			||	define('DATATABLES_CSS_PATH',				DATATABLES_PATH . "/css");

		/**
		 * PDO
		 */
		defined('PDO_ACTIVE')					||	define('PDO_ACTIVE', 						DataHelper::get($aPDO,			'active',					DataHelper::DATA_TYPE_BOOL));
		defined('PDO_CHARSET')					||	define('PDO_CHARSET',						DataHelper::get($aPDO,			'charset'));
		defined('PDO_DBNAME')					||	define('PDO_DBNAME',						DataHelper::get($aPDO,			'dbname'));
		defined('PDO_HOST')						||	define('PDO_HOST',							DataHelper::get($aPDO,			'host'));
		defined('PDO_PORT')						||	define('PDO_PORT',							DataHelper::get($aPDO,			'port',						DataHelper::DATA_TYPE_INT));
		defined('PDO_USER')						||	define('PDO_USER',							DataHelper::get($aPDO,			'user'));
		defined('PDO_PASSWD')					||	define('PDO_PASSWD',						DataHelper::get($aPDO,			'passwd'));
		defined('PDO_FINAL_COMMIT')				||	define('PDO_FINAL_COMMIT',					DataHelper::get($aPDO,			'final_commit',				DataHelper::DATA_TYPE_BOOL));
		defined('PDO_AUTO_COMMIT')				||	define('PDO_AUTO_COMMIT',					DataHelper::get($aPDO,			'auto_commit',				DataHelper::DATA_TYPE_BOOL));
		defined('PDO_DISABLE_FULL_GROUP_BY')	||	define('PDO_DISABLE_FULL_GROUP_BY',			DataHelper::get($aPDO,			'disable_full_group_by',	DataHelper::DATA_TYPE_BOOL));

		/**
		 * CACHE
		 */
		defined('CACHE_ACTIVE')					||	define('CACHE_ACTIVE', 						DataHelper::get($aCache,		'active',					DataHelper::DATA_TYPE_BOOL));
		defined('CACHE_PATH')					||	define('CACHE_PATH',						DataHelper::get($aCache,		'path'));

		/**
		 * LDAP
		 */
		defined('LDAP_ACTIVE')					||	define('LDAP_ACTIVE', 						DataHelper::get($aLDAP,			'active',					DataHelper::DATA_TYPE_BOOL));
		defined('LDAP_CHARSET')					||	define('LDAP_CHARSET',						DataHelper::get($aLDAP,			'charset'));
		defined('LDAP_HOST')					||	define('LDAP_HOST',							DataHelper::get($aLDAP,			'host'));
		defined('LDAP_PORT')					||	define('LDAP_PORT',							DataHelper::get($aLDAP,			'port',						DataHelper::DATA_TYPE_INT));
		defined('LDAP_DN')						||	define('LDAP_DN',							DataHelper::get($aLDAP,			'dn'));

		/**
		 * VUE
		 */
		ViewRender::start();

		/**
		 * DATABASE
		 */
		$this->checkDataBase();

		/**
		 * LAST_RENDER
		 */
		$this->_oSessionMessenger->setLastRender(DataHelper::getTime());
	}

	/**
	 * @brief	Vérification de l'accès à la base de données.
	 *
	 * Affiche un message d'avertissement si l'accès à la base de données
	 * 	- n'est pas correctement configurée ;
	 * 	- n'est pas accessible (hors service).
	 * @return	void
	 */
	static private function checkDataBase() {
		// Fonctionnalité réalisée si l'accès à la base n'a pas été encore réalisée
		if (empty($_SESSION["PDO_ACCESS"])) {
			// Initialisation du connecteur
			$oManager = new MySQLManager();
			// Récupération du statut de connexion à la base
			$_SESSION["PDO_ACCESS"] = $oManager->isConnectedPDO();
		}

		// Message d'alerte pour l'accès à la base de données
		if (empty($_SESSION["PDO_ACCESS"])) {
			ViewRender::setMessageError("Erreur de configuration !", "Impossible de se connecter à la base de données...");
		}
	}

	/**
	 * @brief	Vérification de la validité de la classe CONTROLLER.
	 *
	 * @li Le nom du fichier est de la forme `NomController.php`
	 *
	 * @param	string	$sClass		: nom de la classe à vérifier.
	 * @return	bool
	 */
	static private function checkControlClass($sClass) {
		// Nom de la classe à vérifier
		$sClassName = ucfirst(strtolower($sClass)) . "Controller";
		// Fonctionnalité permettant de vérifier que le fichier de la classe est présent
		return file_exists(CONTROLLERS.'/'.$sClassName.'.php');
	}

	/**
	 * @brief	Chargement de la VIEW selon son CONTROLLER et son ACTION.
	 *
	 * Méthode statique permettant de charger les fichiers PHP de chaque action selon l'arborescence de l'application
	 * @code
	 * 	[application]
	 * 		|__.
	 * 		|__..
	 * 		|__[configs]
	 * 		|__[controllers]							# Répertoire des CONTROLLERs
	 * 		|	|__.
	 * 		|	|__..
	 * 		|	|__IndexController.php
	 * 		|	|__NomducontrôleurController.php		# Classe PHP portant le nom du contrôleur auquel est ajouté `Controller`
	 * 		|__[interfaces]
	 * 		|__[models]
	 * 		|__[views]									# Répertoire des VIEWs
	 * 			|__.
	 * 			|__..
	 * 			|__[helpers]
	 * 			|__[scripts]
	 * 				|__.
	 * 				|__..
	 * 				|__[index]							# Nom du répertoire du CONTROLLER par défaut
	 * 				|	|__.
	 * 				|	|__..
	 * 				|	|__index.phtml
	 * 				|
	 * 				|__[nom_du_contrôleur]				# Répertoire du contrôleur appelé
	 * 					|__.
	 * 					|__..
	 * 					|__index.phtml
	 * 					|__nom_de_l'action.phtml		# Vue portant le nom de l'action appelée
	 * @endcode
	 *
	 * @param	string	$sController	: nom du contrôleur.
	 * @param	string	$sAction		: nom de l'action du contrôleur.
	 * @return	void
	 */
	static private function includeView($sController = FW_DEFAULTCONTROLLER, $sAction = null) {
		if (empty($sAction) || $sAction == FW_VIEW_VOID) {
			// Vue sans structure HTML
			$sView			= FW_VIEW_VOID;
		} else {
			// Vue selon l'arborescence CONTROLLER/ACTION.phtml
			$sView			= $sController . "/" . $sAction;
		}

		// Liste des chemin possibles
		$aIncludePath		= array(
			'/' . $sView . '.phtml',
			'/' . $sView . '.php'
		);

		// Parcours de l'arborescente de l'application à la recherche du fichier PHP à inclure
		if ($sView != FW_VIEW_VOID) {
			foreach ($aIncludePath as $sIncludePath) {
				if (file_exists(VIEWS_SCRIPTS . $sIncludePath)) {
					include(VIEWS_SCRIPTS . $sIncludePath);
				} elseif (file_exists(VIEWS_HELPERS . $sIncludePath)) {
					include(VIEWS_SCRIPTS . $sIncludePath);
				} elseif (file_exists($sIncludePath)) {
					include($sIncludePath);
				} else {
					throw new ApplicationException('EViewNotFound', $sController, $sView);
				}
			}
		}
	}

	/**
	 * @brief	Lancement de l'application.
	 *
	 * @li	Exploitation du SINGLETON InstanceStorage afin de permettre de transiter des données entre différentes classes CONTROLLER.
	 * @return	void
	 */
	public function run() {
		try {
			// Initialisation du nom de la vue
			$sView						= '';

			// Récupération des données envoyées par le formulaire $_GET[] / $_POST[]
			$aGetPost					= $_REQUEST;

			// Fonctionnalité réalisée en MODE REWRITE sous serveur Nginx
			if (isset($aGetPost['base_url']) && (!isset($_SERVER['REDIRECT_URL']) || empty($_SERVER['REDIRECT_URL']))) {
				$_SERVER['REDIRECT_URL']= $aGetPost['base_url'];
			}

			// Récupération des paramètres depuis l'URL sous la forme {CONTROLLER}.{SUBCONTROLLER}/{ACTION}.{SUBACTION}/{OPTION}
			$sSubController				= null;
			$sSubAction					= null;
			$sOption					= null;
			if (isset($_SERVER['REDIRECT_URL']) && ($_SERVER['REDIRECT_URL'] != INDEX || $_SERVER['REDIRECT_URL'] != INDEX . ".php")) {
				$aURL					= @explode("/", $_SERVER['REDIRECT_URL']);
				$sController			= DataHelper::get($aURL,		1,					DataHelper::DATA_TYPE_CLASSID,	FW_DEFAULTCONTROLLER);
				$sAction				= DataHelper::get($aURL,		2,					DataHelper::DATA_TYPE_CLASSID,	FW_DEFAULTACTION);
				$sOption				= DataHelper::get($aURL,		3,					DataHelper::DATA_TYPE_CLASSID,	null);
			} else {
				$sController			= DataHelper::get($aGetPost,	'controller',		DataHelper::DATA_TYPE_CLASSID,	FW_DEFAULTCONTROLLER);
				$sSubController			= DataHelper::get($aGetPost,	'subController',	DataHelper::DATA_TYPE_CLASSID,	null);
				$sAction				= DataHelper::get($aGetPost,	'action',			DataHelper::DATA_TYPE_CLASSID,	FW_DEFAULTACTION);
				$sSubAction				= DataHelper::get($aGetPost,	'subAction',		DataHelper::DATA_TYPE_CLASSID,	null);
				$sOption				= DataHelper::get($aGetPost,	'option',			DataHelper::DATA_TYPE_CLASSID,	null);
			}

			// Extraction des paramètres du contrôleur (Le caractère [.] permet d'atteindre un sous-contrôleur)
			$aController				= explode('.', $sController);
			$sController				= $aController[0];
			$sSubController				= isset($aController[1])	? $aController[1]	: $sSubController;

			// Initialisation du nom du contrôleur
			if (empty($sController)) {
				throw new ApplicationException('ENoController', "null", "null");
			} elseif (empty($sAction)) {
				$sAction = FW_DEFAULTACTION;
			}
			// Extraction des paramètres de l'action (Le caractère [.] permet d'atteindre un sous-contrôleur)
			$aAction					= explode('.', $sAction);
			$sAction					= $aAction[0];
			$sSubAction					= isset($aAction[1])		? $aAction[1]		: $sSubAction;

			// Enregistrement des informations d'exécution dans le SINGLETON de l'instance
			$this->_oInstanceStorage->setParam('execute', array(
				'controller'			=> $sController,
				'subController'			=> $sSubController,
				'action'				=> $sAction,
				'subAction'				=> $sSubAction,
				'option'				=> $sOption
			));

			// Initialisation du contrôleur
			$oController = null;
			if (self::checkControlClass($sController)) {
				// Renommage du contrôleur avec le suffixe "Controller"
				$sControllerClassName	= ucfirst(strtolower($sController)) . "Controller";

				// Renommage de l'action avec le suffixe "Action"
				$sActionMethod			= strtolower($sAction)."Action";

				// Instanciation du contrôleur
				$oController			= new $sControllerClassName();

				// Fonctionnalité réalisée si l'action est celle par défaut
				if ($sAction == FW_DEFAULTACTION) {
					// Exécution de la méthode initiale
					$oController->initAction();
					// Exécution de la méthode par défaut `indexAction`
					$oController->indexAction();
					// Exécution de la méthode finale
					$oController->finalAction();
				} else {
					// Sinon, recherche si l'action correspond bien à une méthode du contrôleur
					if (method_exists($oController, $sActionMethod)) {
						// Exécution de la méthode initiale
						$oController->initAction();
						// Exécution de la méthode par son nom
						$oController->$sActionMethod();
						// Exécution de la méthode finale
						$oController->finalAction();
					} else {
						// L'action n'existe pas !
						throw new ApplicationException('EActionNotFound', "$sController/$sAction", $sController, $sAction);
					}
				}
				// Récupération du nom de la vue
				$sView = $oController->getViewRender();
			} else {
				// Le contrôleur n'existe pas !
				throw new ApplicationException('EControllerNotFound', "$sController/$sAction", $sController);
			}

			// Initialisation du TIMESTAMP de début du rendu
			$this->_oInstanceStorage->setParam('VIEW_START',	DataHelper::getTime());

			// Initialisation du nom du contrôleur
			$this->_oInstanceStorage->setParam('controller',	$sController);
			// Initialisation du nom du sous-contrôleur
			$this->_oInstanceStorage->setParam('subController',	$sSubController);
			// Initialisation du nom de l'action
			$this->_oInstanceStorage->setParam('action',		$sAction);
			// Initialisation du nom de la sous-action
			$this->_oInstanceStorage->setParam('subAction',		$sSubAction);
			// Initialisation du nom de l'option
			$this->_oInstanceStorage->setParam('option',		$sOption);

			// Chargement des variables de l'application
			$this->_oInstanceStorage->setParam('data',			$oController->getData());
			$this->_oInstanceStorage->setParam('messages',		$oController->getMessages());
			$this->_oInstanceStorage->setParam('errors',		$oController->getErrors());
			$this->_oInstanceStorage->setParam('successes',		$oController->getSuccesses());

			// Chargement de la vue
			self::includeView($sController, $sView);
		} catch (ApplicationException $e) {
			include(FW_VIEW_ERROR.'.php');
		}
	}
}
