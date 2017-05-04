<?php
/** @brief	Connecteur générique SQL / LDAP.
 *
 * @li	Exploite les classes suivantes
 * 	- PDOConnector  pour SQL
 *  - LDAPConnector pour LDAP
 *
 * @name		ConnectorFactory
 * @category	Model
 * @package		Connector
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class Connectors_ConnectorFactory {

	const	LDAP_CONNECTOR		= "Connectors_LDAPConnector";
	const	SQL_CONNECTOR		= "Connectors_SQLConnector";

	/**
	 * @brief	Instance du SINGLETON de la classe.
	 * @var		SQLConnector|LDAPConnector
	 */
	private static $_oInstance	= null;
	private $aConfigs			= null;

	/** @brief	Constructeur de la classe.
	 */
	private function __construct() {
		// Initialisation du tableau des configurations
		$this->aConfigs = array();
	}

	/** @brief	Instanciation du SINGLETON.
	 *
	 * La méthode instancie le SINGLETON s'il n'était pas déjà instancié.
	 * @return	InstanceStorage
	 */
	public static function getInstance() {
		// Fonctionnalité réalisée si l'instance du SINGLETON n'existe pas encore
		if (is_null(self::$_oInstance)) {
			// Initialisation du SINGLETON
			self::$_oInstance = new Connectors_ConnectorFactory();
		}
		// Renvoi de l'instance du SINGLETON
		return self::$_oInstance;
	}

	/**
	 * @brief	Checksum des paramètres.
	 *
	 * Méthode permettant de générer une chaîne MD5 correspondant au tableau de paramètres.
	 * @param	array	$aConfig			: tableau de paramètres.
	 * @return	string MD5
	 */
	private function checkSumParams(array $aConfig) {
		// Encode les paramètres sous forme de chaîne MD5
		return md5(implode('|', $aConfig));
	}

	/**
	 * @brief	Vérification des paramètres.
	 *
	 * Méthode permettant de vérifier si les paramètres correspondent à la connexion en cours.
	 * @param	string	$sConfig			: chaîne MD5 correspondant aux paramètres à vérifier.
	 * @return	boolean
	 */
	private function checkConfig($sConfig) {
		// Vérifie la configuration
		return array_key_exists($sConfig, $this->aConfigs);
	}

	/**
	 * @brief	Initialisation de la connexion.
	 *
	 * Méthode permettant de se connecter à un connecteur selon la classe du modèle passé en paramètre.
	 * @param	string	$sConnectorClass	: nom du modèle connecteur.
	 * @param	array	$aConfig			: tableau de paramètres au connecteur.
	 * @return	boolean
	 */
	public function getConnector($sConnectorClass, $aConfig) {
		// Génération d'une chaîne MD5 correspondant à la configuration du connecteur
		$sConfig = $this->checkSumParams($aConfig);
		// Fonctionnalité réalisée si la configuration n'existe pas encore
		if (!isset($this->aConfigs[$sConfig])) {
			// Enregistrement de l'instance du connecteur
			$this->aConfigs[$sConfig] = new $sConnectorClass();
			$this->aConfigs[$sConfig]->open($aConfig);
		}
		// Renvoi l'instance du connecteur
		return $this->aConfigs[$sConfig];
	}
}
