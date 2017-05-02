<?php
/**
 * @brief	Classe abstraite d'exportation de documents.
 *
 * @li	La variable d'instance @a $this->_document correspond au corps du document à exporter.
 * @li	La variable d'instance @a $this->_filename correspond au nom du fichier exporté.
 * @li	La méthode ExportManager::render() permet de générer le document selon les paramètres [Content-Type] et [Extension] du fichier.
 *
 * @name		ExportManager
 * @category	Model
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
abstract class ExportManager {

	protected $_document		= null;
	protected $_filename		= "export";
	protected $_contentType		= "text/plain";
	protected $_extension		= "tex";

	/**
	 * @brief	Initialisation du nom du fichier.
	 *
	 * @param	string	$sFilename			: nom du fichier
	 * @return	void
	 */
	public function setFilename($sFilename) {
		$this->_filename = trim($sFilename);
	}

	/**
	 * @brief	Initialisation du contentType du fichier.
	 *
	 * @param	string	$sContentType		: type du fichier
	 * @return	void
	 */
	public function setContentType($sContentType) {
		$this->_contentType = trim($sContentType);
	}

	/**
	 * @brief	Initialisation du contentType du fichier.
	 *
	 * @param	string	$sContentType		: type du fichier
	 * @return	void
	 */
	public function setExtension($sExtension) {
		$this->_extension = trim($sExtension);
	}

	/**
	 * @brief	Rendu final du document sous forme d'un fichier texte à télécharger par le client.
	 *
	 * @param	string	$sContentType		: (optionnel) format de sortie, par défaut [text/plain]
	 * @param	string	$sExt				: (optionnel) extension du fichier de sortie, par défaut [txt]
	 * @return	file
	 */
	public function render($sContentType = null, $sExt = null) {
		// Récupération des paramètres d'export s'ils sont spécifiés
		$this->_contentType	= !empty($sContentType)	? $sContentType	: $this->_contentType;
		$this->_extension	= !empty($sExt)			? $sExt			: $this->_extension;

		// Modification de l'entête
		header('Content-Type:"' . trim($this->_contentType) . '"');
		header('Content-Disposition: attachment; filename="' . trim($this->_filename) . '.' . trim($this->_extension) . '"');
		// Affichage du document
		print $this->_document;
		// Fin du rendu
		exit;
	}

}
