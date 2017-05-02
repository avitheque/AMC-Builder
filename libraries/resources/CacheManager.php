<?php
/**
 * @brief	Classe de gestion du cache de l'application.
 *
 *
 * @name		CacheManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 19 $
 * @since		$LastChangedDate: 2017-04-30 15:27:06 +0200 (dim., 30 avr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class CacheManager {

	const			CHARSET_DEFAULT	= "utf-8";
	const			CACHE_DEFAULT	= 86400;
	const			CACHE_FILENAME	= "/cache_%s_%s";

	private 		$_nameSpace		= null;
	private 		$_aCollection	= array();

	/**
	 * @brief	Instance du SINGLETON de la classe.
	 * @var		SessionManager
	 */
	private static $oInstance		= null;

	/**
	 * SessionManager constructor.
	 * @param	string	$sNameSpace		: espace de nom pour le stockage.
	 * @return	void
	 */
	protected function __construct($sNameSpace) {
		$this->_nameSpace = $sNameSpace;
	}

	/**
	 * @brief	Instanciation du SINGLETON.
	 *
	 * La méthode instancie le SINGLETON s'il n'était pas déjà instancié.
	 * @param	string	$sNameSpace		: espace de nom pour le stockage.
	 * @return	SessionManager
	 */
	public static function getInstance($sNameSpace) {
		// Fonctionnalité réalisée si l'instance du SINGLETON n'existe pas encore
		if (!is_null(self::$oInstance)) {
			// Initialisation du SINGLETON
			self::$oInstance = new CacheManager($sNameSpace);
		}

		// Renvoi de l'instance du SINGLETON
		return self::$oInstance;
	}

	/**
	 * @brief	Récupération du chemin de fichier du CACHE par son INDEX.
	 *
	 * @param	string	$sIndex			: index d'enregistrement.
	 * @return	string
	 */
	private function _getCachedFilename($sIndex) {
		// Initialisation du nom de fichier
		$sFilename = null;

		// Fonctionnalité réalisée si le cache est déclaré
		if (isset($this->_aCollection[$this->_nameSpace]) && array_key_exists($sIndex, (array) $this->_aCollection[$this->_nameSpace])) {
			// Récupération du nom du fichier
			$sFilename = $this->_aCollection[$this->_nameSpace][$sIndex];
		} else {
			// Fichier non mis en cache
			$sFilename = false;
		}

		// Renvoi du résultat
		return $sFilename;
	}

	/**
	 * @brief	Vérifie la présence du fichier en CACHE par son INDEX.
	 *
	 * @param	string	$sIndex			: index d'enregistrement.
	 * @return	boolean
	 */
	public function isCached($sIndex) {
		// Initialisation de la vérification
		$bExists = false;

		// Fonctionnalité réalisée si le cache est autorisé
		if (defined('CACHE_ACTIVE') && (bool) CACHE_ACTIVE) {
			// Récupération du nom de fichier
			$sFileName = $this->_getCachedFilename($sIndex);

			// Fonctionnalité réalisée si le fichier existe
			if (file_exists($sFileName)) {
				// Fichier stocké en cache
				$bExists = file_exists($sFileName);
			} else {
				// Fichier non mis en cache
				$bExists = false;
			}
		} else {
			// Cache désactivé
			$bExists = false;
		}

		// Renvoi de la vérification
		return $bExists;
	}

	/**
	 * @brief	Récupération du contenu du cache.
	 *
	 * @param	string	$sIndex			: index d'enregistrement.
	 * @return	string
	 */
	public function getContentFromCache($sIndex) {
		// Récupération du nom du fichier
		$sFilename = $this->_getCachedFilename($sIndex);

		// Fonctionnalité réalisée si le fichier est enregistré
		if (!empty($sFilename)) {
			// Récupération du contenu du fichier
			return file_get_contents($sFilename);
		} else {
			return null;
		}
	}

	/**
	 * @brief	Récupération du contenu du CACHE.
	 *
	 * @param	string	$sIndex			: index d'enregistrement.
	 * @param	string	$sContent		: contenu à mettre en CACHE.
	 */
	public function setContentToCache($sIndex, $sContent) {
		// Fonctionnalité réalisée si le cache est autorisé
		if (defined('CACHE_ACTIVE') && (bool) CACHE_ACTIVE) {
			// Initialisation du chemin du fichier pour le CACHE
			$sFilename = sprintf(CACHE_PATH . self::CACHE_FILENAME, $this->_nameSpace, $sIndex);

			// Ajout du nom du fichier à la collection
			file_put_contents($sFilename, $sContent);

			if (isset($this->_aCollection[$this->_nameSpace])) {
				$this->_aCollection[$this->_nameSpace][$sIndex]	= $sFilename;
			} else {
				$this->_aCollection[$this->_nameSpace]			= array($sIndex => $sFilename);
			}

			// Enregistrement du contenu dans le fichier
			return $sFilename;
		} else {
			// Cache désactivé
			return false;
		}
	}

}
