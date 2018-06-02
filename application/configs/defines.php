<?php
/**
 * @brief       Fichier d'initialisation de l'application.
 *
 * Ce fichier permet de déclarer toutes les constantes exploitées par le Framework afin d'initialiser le pattern MVC.
 *
 * @li Appel du fichier de démarrage de l'application.
 * @see /application/Bootstrap.php
 *
 * @name		Defines
 * @category	Init
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 134 $
 * @since		$LastChangedDate: 2018-06-02 08:51:35 +0200 (Sat, 02 Jun 2018) $
 */

/**
 * Définition des variables d'environnement de l'application
 */
define('ROOT_PATH',					realpath($_SERVER['DOCUMENT_ROOT'].'/..'));

define('PUBLIC_PATH',				ROOT_PATH . '/public');
define('LIBRARY',					ROOT_PATH . '/libraries');
define('APPLICATION',				ROOT_PATH . '/application');
define('CONFIGS',					APPLICATION . '/configs');
define('INDEX',						'/index');

define('DOWNLOAD_PATH',				PUBLIC_PATH . '/download');
define('IMAGES_PATH',				'/images');
define('JQUERY_PATH',				'/jquery');
define('SCRIPTS_PATH',				'/scripts');
define('STYLES_PATH',				'/styles');

/**
 * Paramètres du framework
 */
define('FW_MODELS',					LIBRARY.'/models');
define('FW_CONTROLLERS',			LIBRARY.'/controllers');
define('FW_RESOURCES',				LIBRARY.'/resources');
define('FW_VIEWS',					LIBRARY.'/views');
define('FW_HELPERS',				FW_VIEWS.'/helpers');
define('FW_HTML',					FW_HELPERS.'/html');

define('FW_FORM_SCRIPTS_PATH',		PUBLIC_PATH . SCRIPTS_PATH.'/formulaires');
define('FW_FORM_STYLES_PATH',		PUBLIC_PATH . STYLES_PATH.'/formulaires');

define('FW_VIEW_SCRIPTS',			FW_VIEWS . SCRIPTS_PATH);
define('FW_VIEW_STYLES',			FW_VIEWS . STYLES_PATH);
define('FW_VIEW_VOID',				FW_VIEWS.'/VwVoid');
define('FW_VIEW_ERROR',				FW_VIEWS.'/VwError');


/**
 * Paramètres du MVC
 */
define('MODULES',					APPLICATION.'/modules');
define('MODELS',					APPLICATION.'/models');
define('CONTROLLERS',				APPLICATION.'/controllers');
define('INTERFACES',				APPLICATION.'/interfaces');
define('VIEWS',						APPLICATION.'/views');
define('VIEWS_SCRIPTS',				VIEWS.'/scripts');
define('VIEWS_HELPERS',				VIEWS.'/helpers');


/**
 * Constantes des noms de session protégées de l'application
 */
define('AUTHENTICATE',				"AuthenticateManage");
define('SESSION_MESSENGER',			"SessionMessenger");
define('SESSION_NOTIFICATION',		"SessionNotification");
define('VIEW_BODY',					"body");
define('VIEW_CACHE',				"cache");
define('VIEW_DEBUG',				"debug");
define('VIEW_DIALOG',				"dialog");
define('VIEW_EXCEPTION',			"exception");
define('VIEW_FOOTER',				"footer");
define('VIEW_FORM_START',			"form");
define('VIEW_FORM_END',				"/form");
define('VIEW_HEAD',					"head");
define('VIEW_HEADER',				"header");
define('VIEW_JQUERY',				"jQuery");
define('VIEW_MAIN',					"main");
define('VIEW_MENU',					"menu");
define('VIEW_SCRIPTS',				"scripts");
define('VIEW_STYLES',				"styles");
define('VIEW_STYLESHEET',			"stylesheet");
define('VIEW_MD5',					"MD5");

/**
 * Liste des noms de session à protéger dans l'application
 */
$aApplicationSessions = array(
	AUTHENTICATE,
    SESSION_MESSENGER,
	SESSION_NOTIFICATION,
	VIEW_BODY,
	VIEW_CACHE,
	VIEW_DEBUG,
	VIEW_DIALOG,
	VIEW_EXCEPTION,
	VIEW_FOOTER,
	VIEW_FORM_START,
	VIEW_FORM_END,
	VIEW_HEAD,
	VIEW_HEADER,
	VIEW_JQUERY,
	VIEW_MAIN,
	VIEW_MENU,
	VIEW_SCRIPTS,
	VIEW_STYLES,
	VIEW_STYLESHEET
);
define('APPLICATION_SESSIONS',		implode("|", $aApplicationSessions));


/**
 * @brief	Initialisation des chemins de l'application à inclure dans le PATH
 */
set_include_path(
	'.'.PATH_SEPARATOR.
	// Librairie de l'application =======================================
	LIBRARY.PATH_SEPARATOR.

	// Répertoires du Framework =========================================
	FW_MODELS.PATH_SEPARATOR.			// MODEL
	FW_CONTROLLERS.PATH_SEPARATOR.		// CONTRÔLEUR
    FW_RESOURCES.PATH_SEPARATOR.		// RESSOURCES
	FW_VIEWS.PATH_SEPARATOR.			// VUES
	FW_HELPERS.PATH_SEPARATOR.			// HELPERS
	FW_HTML.PATH_SEPARATOR.				// HTML

	// Répertoires de l'application =====================================
	MODELS.PATH_SEPARATOR.
	CONTROLLERS.PATH_SEPARATOR.
	INTERFACES.PATH_SEPARATOR.
	VIEWS.PATH_SEPARATOR.
	VIEWS_HELPERS.PATH_SEPARATOR.
	get_include_path()
);


/**
 * @brief	Chargement du fichier de classe à partir de son nom.
 *
 * @li  Le caractère [_] dans le nom de la classe indique la présence d'un sous-répertoire.
 *
 * @param	string	$sClass				: Nom de la classe.
 */
function __autoload($sClass) {
	// Chaque caractère de séparation correspond à un noeud dans arborescence de l'application
	$sFilenameClass = str_ireplace("_", "/", $sClass) . '.php';

	// Fonctionnalité réalisée si la classe fait partie des HELPERS de la librairie
	if (file_exists(FW_HELPERS . $sFilenameClass)) {
		// La classe est présente dans l'arborescence de la librairie
		require_once(FW_HELPERS . $sFilenameClass);
	} else {
		// La classe est présente dans l'arborescence directe de l'application
		require_once($sFilenameClass);
	}
}

/**
 * @brief	Récupération du fichier de chargement de l'application.
 *
 * @li  La classe Bootstrap permet de charger les ressources de l'application selon l'environnement passé en paramètre.
 */
require_once APPLICATION . '/Bootstrap.php';
