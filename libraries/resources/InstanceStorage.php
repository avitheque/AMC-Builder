<?php
/** @brief	Système de stockage inter-classe.
 *
 * Cette classe SINGLETON permet aux instances de communiquer entre elles.
 * Les instances des différentes classes de l'application peuvent transmettre ou modifier les mêmes variables.
 *
 * @li	InstanceStorage::getInstance() pour récupérer l'instance du singleton.
 * @code
 *  // Récupération de deux instances
 *  $oInstance_A	= InstanceStorage::getInstance();
 * 	$oInstance_B	= InstanceStorage::getInstance();
 *
 *  // Enregistrement d'une variable dans l'instance B
 * 	$oInstance_B->setParam("calcul", 6*6^6);
 *
 *  // Récupération de la valeur de la variable depuis l'instance A
 * 	print($oInstance_A->getParam("calcul"));			// Affiche 279936 !
 * @endcode
 *
 * @name		InstanceStorage
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class InstanceStorage {

	/**
	 * @brief	Instance du SINGLETON de la classe.
	 * @var		InstanceStorage
	 */
	private static $_oInstance	= null;

	/**
	 * @brief	Tableau des données stockées dans l'instance en cours.
	 * @var		array
	 */
	private $_aParam			= array();
	
	/**
	 * @brief	Tableau des messages stockés dans l'instance en cours.
	 * @var		array
	 */
	private $_aMessages			= array();

	/** @brief	Instanciation du SINGLETON.
	 *
	 * La méthode instancie le SINGLETON s'il n'était pas déjà instancié.
	 * @return	InstanceStorage
	 */
	public static function getInstance() {
		// Fonctionnalité réalisée si l'instance du SINGLETON n'existe pas encore
		if (is_null(self::$_oInstance)) {
			// Initialisation du SINGLETON
			self::$_oInstance = new InstanceStorage();
		}
		// Renvoi de l'instance du SINGLETON
		return self::$_oInstance;
	}

	/** @brief	Sauvegarde une variable.
	 *
	 * La méthode sauvegarde la valeur transmise en l'associant à un nom.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @param	mixed	$xValue		: valeur à sauvegarder.
	 * @return	void
	 */
	public function setParam($sIndex, $xValue) {
		// Sauvegarde la variable dans l'instance
		$this->_aParam[$sIndex] = $xValue;
	}

	/** @brief	Teste une variable.
	 *
	 * La méthode vérifie si la valeur d'une variable est précédemment sauvegardée.
	 * La valeur renvoyée sera FALSE si elle n'a pas été déclarée avec set().
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	boolean
	 */
	public function issetParam($sIndex) {
		// Test de la valeur
		return isset($this->_aParam[$sIndex]);
	}

	/** @brief	Récupère une variable.
	 *
	 * La méthode récupère la valeur d'une variable précédemment sauvegardée.
	 * La valeur renvoyée sera nulle si elle n'a pas été déclarée avec set().
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public function getParam($sIndex) {
		// Initialisation de la valeur par défaut
		$xValue = null;
		// Fonctionnalité réalisée si l'étiquette existe
		if ($this->issetParam($sIndex)) {
			$xValue = $this->_aParam[$sIndex];
		}
		// Renvoi de la valeur
		return $xValue;
	}

	/** @brief	Lecture des données du contrôleur.
	 *
	 * La méthode récupère l'ensemble de la valeur de la variable 'data' sauvegardée par le contrôleur.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public function getDatas() {
		// Récupère l'ensemble des données stockées
		return $this->getParam('data');
	}

	/** @brief	Teste une variable d'entrée du contrôleur.
	 *
	 * La méthode vérifie si la valeur d'une variable est dans l'entrée du contrôleur.
	 * La valeur renvoyée sera FALSE si elle n'est pas présente dans $this->_aParam['data'].
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	boolean
	 */
	public function issetData($sIndex) {
		// Récupère les données
		$aDatas = $this->getDatas();
		// Test de la valeur
		return isset($aDatas[$sIndex]);
	}

	/** @brief	Lecture d'une entrée du contrôleur.
	 *
	 * La méthode récupère la valeur d'une entrée de la variable 'data' par son étiquette.
	 * @param	string	$sIndex		: nom de l'étiquette.
	 * @return	mixed	: object|array|string|integer|boolean
	 */
	public function getData($sIndex = null) {
		// Initialisation de la valeur par défaut
		$xValue = null;
		// Récupère les données
		$aDatas = $this->getDatas();
		// Fonctionnalité réalisée si l'étiquette existe
		if (isset($aDatas[$sIndex])) {
			// Récupération de la valeur
			$xValue = $aDatas[$sIndex];
		}
		// Renvoi de la valeur
		return $xValue;
	}
	
}
