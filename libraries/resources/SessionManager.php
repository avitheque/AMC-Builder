<?php
/**
 * @name		SessionManager
 * @package		Helpers
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class SessionManager {

	/**
	 * @brief	Instance du SINGLETON de la classe.
	 * @var		SessionManager
	 */
	private static $oInstance = null;

	/**
	 * SessionManager constructor.
	 * @param	string	$sNameSpace		: nom de la session.
	 * @return	void
	 */
	protected function __construct($sNameSpace = '') {
		session_start();
		session_name($sNameSpace);
	}

	/**
	 * @brief	Instanciation du SINGLETON.
	 *
	 * La méthode instancie le SINGLETON s'il n'était pas déjà instancié.
	 * @param	string	$sNameSpace		: nom de la session.
	 * @return	SessionManager
	 */
	public static function getInstance($sNameSpace = '') {
		// Fonctionnalité réalisée si l'instance du SINGLETON n'existe pas encore
		if (is_null(self::$oInstance)) {
			// Initialisation du SINGLETON
			self::$oInstance = new SessionManager($sNameSpace);
		}
		// Renvoi de l'instance du SINGLETON
		return self::$oInstance;
	}

	/** @brief	Sauvegarde une variable.
	 *
	 * La méthode sauvegarde la valeur transmise en l'associant à un nom.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @param	mixed	$xValue		: valeur à sauvegarder.
	 * @return	void
	 */
	public static function set($sIndex, $xValue) {
		$_SESSION[$sIndex] = $xValue;
	}

	/** @brief	Récupère une variable.
	 *
	 * La méthode récupère la valeur d'une variable précédemment sauvegardée.
	 * La valeur renvoyée sera nulle si elle n'a pas été déclarée avec set().
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public static function get($sIndex) {
		// Initialisation de la valeur par défaut
		$xValue = null;
		// Fonctionnalité réalisée si l'étiquette existe
		if (isset($_SESSION[$sIndex])) {
			// Récupération de la valeur
			$xValue = $_SESSION[$sIndex];
		}
		// Renvoi de la valeur
		return $xValue;
	}

}
