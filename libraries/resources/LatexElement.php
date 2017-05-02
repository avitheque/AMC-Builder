<?php
/**
 * @brief	Classe abstraite de construction de documents LaTeX.
 *
 * @li	La variable d'instance @a $this->_latex correspond au code LaTeX du document à exporter.
 *
 * @name		LatexElement
 * @category	Model
 * @package		Document
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class LatexElement {

	protected $_latex		= "";

	/**
	 * @brief	Rendu final du document sous forme d'un fichier texte à télécharger par le client.
	 *
	 * @return	file
	 */
	public function render() {
		return $this->_latex;
	}

}
