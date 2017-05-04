<?php
/**
 * @brief	Classe abstraite de génération de documents.
 *
 * @li	La variable d'instance @a $this->_document correspond au corps du document à exporter.
 * @li	La variable d'instance @a $this->_filename correspond au nom du fichier exporté.
 * @li	La méthode DocumentManager::render() permet de générer le document selon les paramètres [Content-Type] et [Extension] du fichier.
 *
 * @name		DocumentManager
 * @category	Model
 * @package		Document
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (Sun, 30 Apr 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class DocumentManager {

	protected $_document		= null;
	protected $_filename		= "export";
	protected $_contentType		= "text/plain";
	protected $_charset			= "utf-8";
	protected $_extension		= "txt";

	/**
	 * @brief	Initialisation du nom du fichier.
	 *
	 * @param	string	$sFilename			: nom du fichier
	 * @return	void
	 */
	public function setFilename($sFilename) {
		$this->_filename		= strtr(trim($sFilename), array(chr(32) => "_"));
	}

	/**
	 * @brief	Initialisation du contentType du fichier.
	 *
	 * @param	string	$sContentType		: type du fichier
	 * @return	void
	 */
	public function setContentType($sContentType) {
		$this->_contentType		= trim($sContentType);
	}

	/**
	 * @brief	Initialisation du contentType du fichier.
	 *
	 * @param	string	$sContentType		: type du fichier
	 * @return	void
	 */
	public function setExtension($sExtension) {
		$this->_extension		= trim($sExtension);
	}

	/**
	 * @brief	Rendu final du document sous forme d'un fichier texte à télécharger par le client.
	 *
	 * @param	string	$sContentType		: (optionnel) format de sortie, par défaut [text/plain]
	 * @param	string	$sExt				: (optionnel) extension du fichier de sortie, par défaut [txt]
	 * @param	string	$sCharset				: (optionnel) extension du fichier de sortie, par défaut [txt]
	 * @return	file
	 */
	public function render($sContentType = null, $sExt = null) {
		// Récupération des paramètres d'export s'ils sont spécifiés
		$this->_contentType		= !empty($sContentType)	? $sContentType	: $this->_contentType;
		$this->_extension		= !empty($sExt)			? $sExt			: $this->_extension;
		$sFileName				= trim($this->_filename) . "." . trim($this->_extension);

		// Désactivation de la compression afin d'éviter des erreurs sur certains navigateurs
		if (ini_get("zlib.output_compression")) {
			ini_set("zlib.output_compression", "Off");
		}

		// Fonctionnalité réalisée si le document est un FPDF
		if (is_object($this->_document) && method_exists($this->_document, 'output')) {
			// Génération du document PDF au format UTF-8
			$this->_document->output($sFileName, 'D', true);
		} else {
			// Modification de l'entête afin de désactiver le CACHE
			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0");
			header("Cache-Control: max-age=0");
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Content-Type:\"" . trim($this->_contentType) . "\"; charset=" . trim($this->_charset));
			header("Content-Disposition: attachment; filename=\"" . $sFileName . "\"");

			// Affichage du document
			print $this->_document;
		}

		// Fin du rendu
		exit;
	}

}
