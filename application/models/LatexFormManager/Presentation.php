<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Présentation de l'objectif de l'épreuve.
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Presentation
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
class LatexFormManager_Presentation extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/presentation.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $sText								= LatexFormManager::DOCUMENT_INSTRUCTIONS_TXT;

	/**
	 * @brief	Initialisation de la taille du papier.
	 *
	 * @param	string	$sText				: texte des instructions.
	 * @return	void
	 */
	public function setText($sText) {
		$this->sText = $sText;
	}

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 *		\noindent\hrulefill
	 *		\begin{center}
	 * 			Veuillez répondre aux questions du mieux que vous pouvez.
	 * 		\end{center}
	 *
	 * 		\vspace*{.5cm}
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex = sprintf($sFileContents, $this->sText);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
