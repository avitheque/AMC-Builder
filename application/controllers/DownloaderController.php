<?php
/**
 * @brief	Classe contrôleur de téléchargement de fichiers.
 *
 * Contrôleur exploité pour mettre en CACHE les fichiers statiques de l'application
 * ou pour télécharger des fichier présents dans le répertoire {DOWNLOAD_PATH}
 *
 * @li	Il n'est pas nécessaire d'être authentifié pour exploiter cette ressource.
 * @remark	Pas besoin de renseigner cette ressource dans les ACLs !
 *
 * Étend la classe abstraite AbstractApplicationController.
 * @see			{ROOT_PATH}/application/controllers/AbstractApplicationController.php
 *
 * @li	Possibilité de télécharger un fichier présent dans le répertoire `{ROOT_PATH}/public/download/`
 * @example		<a class='button small green right' href='/downloader?file=format-GIFT.txt' target='_blank'>Télécharger le fichier</a>
 *
 * @li	Possibilité de récupérer une feuille de style présent dans le répertoire `{ROOT_PATH}/public/styles`
 * @example		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/main.css" />
 *
 * @li	Possibilité de récupérer un script JavaScript présent dans le répertoire `{ROOT_PATH}/public/scritps`
 * @example		<script type="text/javascript" src="/downloader/scripts/main.js"></script>
 *
 * @name		IndexController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class DownloaderController extends AbstractApplicationController {

	public function resetAction()	{}
	public function finalAction()	{}

	const		CHARSET_DEFAULT		= "utf-8";
	const		CACHE_FILENAME		= "/cache_%s.%s";

	const		TIME_CACHE_CSS		= 604800;
	const		TIME_CACHE_JS		= 216000;
	const		TIME_CACHE_IMAGE	= 2592000;

	const		EXTENSION_CSS		= "css";
	const		EXTENSION_JPG		= "jpg";
	const		EXTENSION_JPEG		= "jpeg";
	const		EXTENSION_JS		= "js";
	const		EXTENSION_PNG		= "png";
	const		EXTENSION_TXT		= "txt";

	const		CONTENT_TYPE_CSS	= "text/css";
	const		CONTENT_TYPE_JPG	= "image/jpg";
	const		CONTENT_TYPE_JPEG	= "image/jpeg";
	const		CONTENT_TYPE_JS		= "application/javascript";
	const		CONTENT_TYPE_PNG	= "image/png";
	const		CONTENT_TYPE_TXT	= "text/plain";

	private		$_sFile				= null;
	private		$_sName				= null;
	private		$_sExtension		= null;

	/**
	 * @brief	Extensions valides
	 *
	 * @var array
	 */
	static public $VALID_EXTENSION	= array(
		self::EXTENSION_CSS,
		self::EXTENSION_JPG,
		self::EXTENSION_JPEG,
		self::EXTENSION_JS,
		self::EXTENSION_PNG,
		self::EXTENSION_TXT
	);

	/**
	 * @brief	Constructeur de la classe.
	 *
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__);

		// Désactivation de la vue par défaut
		ViewRender::setNoRenderer(true);

		// Récupération du nom du fichier passé en paramètre
		$this->_sFile		= $this->issetParam("file") ? $this->getParam("file") : $this->_option;

		// Séparation du nom du fichier de son extension
		$aExplode			= explode(".", $this->_sFile);
		$this->_sName		= DataHelper::get($aExplode, 0);
		$this->_sExtension	= strtolower(DataHelper::get($aExplode, 1));
	}

	/**
	 * @brief	Activation du cache dans les entêtes.
	 *
	 * @param	string		$sContentType	: durée de la mise en CACHE (en secondes).
	 * @param	string		$sCharset		: (optionnel) mode d'encodage du contenu du fichier.
	 */
	private function _setContentTypeHeader($sContentType, $sCharset = null) {
		// Fonctionnalité réalisée si le CHARSET est présent
		if (!empty($sCharset)) {
			// Renseignement du CHARSET avec le CONTENT-TYPE
			header("Content-Type: $sContentType; charset=$sCharset");
		} else {
			// Renseignement uniquement du CONTENT-TYPE
			header("Content-Type: $sContentType");
		}
		header("Vary: Accept-Encoding");
	}

	/**
	 * @brief	Désactivation du cache.
	 */
	private function _setNoCacheHeader() {
		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
	}

	/**
	 * @brief	Activation du cache dans les entêtes.
	 *
	 * @param	integer		$nOffset	: durée de la mise en CACHE (en secondes).
	 * @param	string		$sAccess	: mode d'accès au stockage du CACHE (public, private).
	 */
	private function _setCacheControl($nOffset, $sAccess = "public") {
		header(sprintf("Cache-Control: max-age=%d, %s", $nOffset, $sAccess));
		header(sprintf("Expires: %s", gmdate("D, d M Y H:i:s", intval(time() + $nOffset)) . " GMT"));
	}

	/**
	 * @brief	Méthode appelée avant d'exécuter l'action principale.
	 *
	 */
	public function initAction() {
		// Fonctionnalité réalisée si le fichier existe
		if (preg_match("@/@", $this->_sFile) || empty($this->_sFile) || empty($this->_sExtension) || !in_array($this->_sExtension, self::$VALID_EXTENSION)) {
			// Le fichier n'existe pas !
			header('Location: /index', true, 404);
			throw new ApplicationException(Constantes::ERROR_DOWNLOAD_FILE, $this->_sFile);
		}

		// Initialisation du HEADER
		switch (strtolower($this->_sExtension)) {

			// Fonctionnalité réalisée pour les feuilles de style
			case self::EXTENSION_CSS:
				// Initialisation du HEADER
				$this->_setContentTypeHeader(self::CONTENT_TYPE_CSS, self::CHARSET_DEFAULT);
				// Ajout d'un délai d'expiration
				$this->_setCacheControl(self::TIME_CACHE_CSS, "public");
				break;

			// Fonctionnalité réalisée pour les fichiers JavaScripts
			case self::EXTENSION_JS:
				// Initialisation du HEADER
				$this->_setContentTypeHeader(self::CONTENT_TYPE_JS, self::CHARSET_DEFAULT);
				// Ajout d'un délai d'expiration
				$this->_setCacheControl(self::TIME_CACHE_JS, "private");
				break;

			// Fonctionnalité réalisée pour les images
			case self::EXTENSION_JPG:
				// Initialisation du HEADER
				$this->_setContentTypeHeader(self::CONTENT_TYPE_JPG);
				// Ajout d'un délai d'expiration
				$this->_setCacheControl(self::TIME_CACHE_IMAGE, "public");
				break;
			case self::EXTENSION_JPEG:
				// Initialisation du HEADER
				$this->_setContentTypeHeader(self::CONTENT_TYPE_JPEG);
				// Ajout d'un délai d'expiration
				$this->_setCacheControl(self::TIME_CACHE_IMAGE, "public");
				break;
			case self::EXTENSION_PNG:
				// Initialisation du HEADER
				$this->_setContentTypeHeader(self::CONTENT_TYPE_PNG);
				// Ajout d'un délai d'expiration
				$this->_setCacheControl(self::TIME_CACHE_IMAGE, "public");
				break;

			// Fonctionnalité réalisée dans les autres cas
			default:
				break;
		}

		// Suppression d'éléments du HEADER
		header_remove("Pragma");
		header_remove("Set-Cookie");
		header_remove("X-Powered-By");
	}

	/**
	 * @brief	Méthode permettant de récupérer les meta-data du fichier.
	 *
	 * @param	string		$sFileName	: chemin du fichier.
	 * @return	array
	 */
	private function _getFileInfos($sFileName) {
		// Initialisation du résultat
		$aFileInfos = array();
		// Fonctionnalité réalisée si le fichier existe
		if (file_exists($sFileName)) {
			// Taille du fichier
			$aFileInfos['size']		= filesize($sFileName);
			// Dernière modification du fichier
			$aFileInfos['mdate']	= filemtime($sFileName);
		} else {
			// Le fichier n'existe pas !
			header("HTTP/1.1 404 Not Found");
			throw new ApplicationException(Constantes::ERROR_DOWNLOAD_FILE, $sFileName);
		}
		// Renvoi du résultat sous forme de tableau
		return $aFileInfos;
	}

	/**
	 * @brief	Récupération du chemin du fichier en CACHE.
	 *
	 * Cette méthode n'est appelée que si le cache est autorisé.
	 */
	private function _getCacheFilename() {
		// Renvoi du chemin du fichier en CACHE
		return sprintf(CACHE_PATH . self::CACHE_FILENAME, $this->_sName, $this->_sExtension);
	}

	/**
	 * @brief	Vérifie la présence du fichier en CACHE.
	 */
	private function _isCached() {
		// Fonctionnalité réalisée si le cache est autorisé
		if (defined('CACHE_ACTIVE') && (bool) CACHE_ACTIVE) {
			// Récupération du chemin du fichier en CACHE
			$sFilename	= $this->_getCacheFilename();

			// Fonctionnalité réalisée si le fichier est présent dans le CACHE
			if ($bExists = file_exists($sFilename)) {
				// Chargement de l'entête du fichier
				header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($sFilename)) . " GMT");
			}

			// Fonctionnalité réalisée si le fichier existe
			return $bExists;
		} else {
			// Cache désactivé
			return false;
		}
	}

	/**
	 * @brief	Récupération du contenu du CACHE.
	 *
	 * Cette méthode n'est appelée que si le cache est autorisé.
	 */
	private function _getCacheContent() {
		// Récupération du chemin du fichier en CACHE
		$sFilename = $this->_getCacheFilename();

		// Affichage du contenu du fichier en CACHE
		print file_get_contents($sFilename);
	}

	/**
	 * @brief	Récupération du contenu du CACHE.
	 *
	 * @param	string		$sContent	: contenu à mettre en CACHE.
	 */
	private function _setContentToCache($sContent) {
		// Fonctionnalité réalisée si le cache est autorisé
		if (defined('CACHE_ACTIVE') && (bool) CACHE_ACTIVE) {
			// Récupération du chemin du fichier en CACHE
			$sFilename = $this->_getCacheFilename();

			// Affichage du contenu du fichier en CACHE
			return file_put_contents($sFilename, $sContent);
		} else {
			// Cache désactivé
			return false;
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 *
	 * @li	Téléchargement du fichier par le client.
	 */
	public function indexAction() {
		// Initialisation du chemin vers le fichier
		$sFilename	= DOWNLOAD_PATH . '/' . $this->_sFile;

		// Récupération des informations du fichier
		$aFileInfos = $this->_getFileInfos($sFilename);

		// Fonctionnalité réalisée si le fichier existe
		if (DataHelper::isValidArray($aFileInfos)) {
			// Début de la mise en tampon du fichier avec compression automatique
			ob_start('ob_gzhandler');

			// Initialisation de l'entête du fichier pour le téléchargement
			header("Content-disposition: attachment; filename=\"$this->_sFile\"");
			header("Content-Type: application/octet-stream; charset=" . self::CHARSET_DEFAULT);
			header("Content-Transfer-Encoding: binary");

			// Désactivation du cache
			$this->_setNoCacheHeader();

			// Récupération du contenu SANS passer par le CACHE
			print file_get_contents($sFilename);

			// Ajout de la taille du contenu
			header("Content-Length: " . ob_get_length());

			// Fin de la mise en tampon du fichier
			ob_end_flush();
		}
	}

	/**
	 * @brief	Mise en cache d'un fichier.
	 *
	 * @li	Compression du fichier hors MODE_DEBUG, hors fichier
	 */
	public function setFileToCache($sFilename) {
		// Récupération des informations du fichier à exploiter
		$aFileInfos = $this->_getFileInfos($sFilename);

		// Fonctionnalité réalisée si le fichier existe
		if (DataHelper::isValidArray($aFileInfos)) {
			// Début de la mise en tampon du fichier avec compression automatique
			ob_start('ob_gzhandler');

			// Chargement de l'entête du fichier
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $aFileInfos['mdate']) . " GMT");

			// Récupération du contenu
			if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
				// Désactivation du cache
				$this->_setNoCacheHeader();

				// Récupération du contenu tel quel
				print file_get_contents($sFilename);
			} else {
				// Compression du contenu à la volée
				switch (strtolower($this->_sExtension)) {

					// Fonctionnalité réalisée pour les feuilles de style
					case self::EXTENSION_CSS:
						// Compression de la feuille de style
						print MiniCSS::minify($sFilename);
						break;

					// Fonctionnalité réalisée pour les fichiers JavaScripts
					case self::EXTENSION_JS:
						// Compression du JavaScript
						print MiniJS::minify(file_get_contents($sFilename));
						break;

					// Fonctionnalité réalisée dans les autres cas
					default:
						// Récupération du contenu tel quel
						print file_get_contents($sFilename);
						break;
				}
			}

			// Ajout de la taille du contenu
			header("Content-Length: " . ob_get_length());

			// Enregistrement du contenu du tampon dans le fichier de cache
			$this->_setContentToCache(ob_get_contents());

			// Fin de la mise en tampon du fichier
			ob_end_flush();
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée pour les images.
	 *
	 * @li	Compression du fichier hors MODE_DEBUG
	 */
	public function imagesAction() {
		// Initialisation du chemin vers le fichier
		$sFilename	= PUBLIC_PATH . IMAGES_PATH . '/' . $this->_sFile;

		// Fonctionnalité réalisée si le fichier existe en CACHE
		if ($this->_isCached()) {
			// Récupération du fichier depuis le CACHE
			$this->_getCacheContent();
		} elseif ($this->_sExtension == self::EXTENSION_PNG) {
			// Ajout du fichier dans le CACHE
			$this->setFileToCache($sFilename);
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée pour les JavaScripts.
	 *
	 * Récupération du fichier JavaScript
	 *
	 * @li	Compression du fichier hors MODE_DEBUG
	 */
	public function scriptsAction() {
		// Initialisation du chemin vers le fichier
		$sFilename	= PUBLIC_PATH . SCRIPTS_PATH . '/' . $this->_sFile;

		// Fonctionnalité réalisée si le fichier existe en CACHE
		if ($this->_isCached()) {
			// Récupération du fichier depuis le CACHE
			$this->_getCacheContent();
		} elseif ($this->_sExtension == self::EXTENSION_JS) {
			// Ajout du fichier dans le CACHE
			$this->setFileToCache($sFilename);
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée pour les feuilles de style.
	 *
	 * @li	Compression du fichier hors MODE_DEBUG
	 */
	public function stylesAction() {
		// Initialisation du chemin vers le fichier
		$sFilename	= PUBLIC_PATH . STYLES_PATH . '/' . $this->_sFile;

		// Fonctionnalité réalisée si le fichier existe en CACHE
		if ($this->_isCached()) {
			// Récupération du fichier depuis le CACHE
			$this->_getCacheContent();
		} elseif ($this->_sExtension == self::EXTENSION_CSS) {
			// Ajout du fichier dans le CACHE
			$this->setFileToCache($sFilename);
		}
	}

}
