<?php
/**
 * @brief	MVC
 *
 * L'application est construite sur le modèle MVC de ZendFramework 1.
 * Toutes les requêtes passent toujours en premier par le fichier [index.php].
 *
 * La classe Bootstrap permet de charger l'application selon l'environnement passé en paramètre.
 * Si le serveur a déjà la variable d'environnement APP_ENV déclarée dans un vhosts, ce paramètre sera exploité.
 * Sinon la déclaration ici présente, définie par défaut, sera prise en charge.
 *
 * @li	Le fichier [index.php] permet d'initialiser le modèle MVC.
 * @li	Le fichier [defines.php] permet d'initialiser les constances nécessaires au démarrage de l'application.
 *
 * @name		index.php
 * @package		Init
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 */

/**
 * @brief	Chargement des paramètres de l'application
 */
require_once '../application/configs/defines.php';

// Récupération de la variable d'environnement du serveur
defined('APP_ENV')	|| define('APP_ENV',	(getenv('APP_ENV')	? getenv('APP_ENV')	: "default"));

try {
	// Initialisation de la configuration de l'application
	$oApplication = new Bootstrap(APP_ENV);
	// Lancement de l'application
	$oApplication->run();
} catch (Exception $e) {
	print $e->getMessage();
}
?>
