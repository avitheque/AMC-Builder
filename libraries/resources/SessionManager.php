<?php
/**
 * @name		SessionManager
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 72 $
 * @since		$LastChangedDate: 2017-07-29 16:54:10 +0200 (Sat, 29 Jul 2017) $
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
	private static $oInstance	= null;

	private $_nameSpace			= null;

	/**
	 * SessionManager constructor.
	 * @param	string	$sNameSpace		: nom de la session.
	 * @return	void
	 */
	protected function __construct($sNameSpace = '') {
		session_start();
		session_name($sNameSpace);
		$this->setNameSpace($sNameSpace);
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

	public function setNameSpace($sNameSpace) {
		$this->_nameSpace = $sNameSpace;
	}

	public function getNameSpace() {
		return $this->_nameSpace;
	}

	public function destroy() {
		// Suppression de la SESSION
		unset($_SESSION[$this->getNameSpace()]);
	}

	/** @brief	Purge d'une entrée.
	 *
	 * La méthode supprime l'entrée correspondant à l'index.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	void
	 */
	public function unsetIndex($sIndex) {
		// Suppression de l'entrée de la SESSION
		unset($_SESSION[$this->getNameSpace()][$sIndex]);
	}

	/** @brief	Sauvegarde une variable.
	 *
	 * La méthode sauvegarde la valeur transmise en l'associant à un nom.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @param	mixed	$xValue		: valeur à sauvegarder.
	 * @return	void
	 */
	public function setIndex($sIndex, $xValue = null) {
		if (is_null($xValue)) {
			// Purge de l'entrée en SESSION
			$this->unsetIndex($sIndex);
		} else {
			// Stockage des données en SESSION
			$_SESSION[$this->getNameSpace()][$sIndex] = $xValue;
		}
	}

	/** @brief	Vérifie la présence d'une entrée.
	 *
	 * La méthode vérifie si une entrée correspond à l'index.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public function issetIndex($sIndex) {
		// Renvoi du résultat
		return isset($_SESSION[$this->getNameSpace()][$sIndex]);
	}

	/** @brief	Récupère une variable.
	 *
	 * La méthode récupère la valeur d'une variable précédemment sauvegardée.
	 * La valeur renvoyée sera nulle si elle n'a pas été déclarée avec set().
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public function getIndex($sIndex) {
		// Initialisation de la valeur par défaut
		$xValue = null;
		// Fonctionnalité réalisée si l'étiquette existe
		if ($this->issetIndex($sIndex)) {
			// Récupération de la valeur
			$xValue = $_SESSION[$this->getNameSpace()][$sIndex];
		}
		// Renvoi de la valeur
		return $xValue;
	}

}
