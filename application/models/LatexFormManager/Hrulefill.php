<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Séparateur vertical avec ligne continue sur toute la largeur de la page.
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Hrulefill
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 56 $
 * @since		$LastChangedDate: 2017-07-05 02:05:10 +0200 (Wed, 05 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class LatexFormManager_Hrulefill extends LatexElement {

	/**
	 * @brief	Constante de construction du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/hrulefill.tex';

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 *			\noindent\hrulefill
	 *			\vspace*{%s}
	 * @endcode
	 *
	 * @param	integer	$nSpacing			: taille de l'espacement.
	 * @param	string	$sUnite				: unité de l'espacement.
	 * @return	string LaTeX
	 */
	public function render($nSpacing = 5, $sUnite = "mm") {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex .= sprintf($sFileContents, $nSpacing, $sUnite);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
