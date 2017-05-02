<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Finalisation du document et instruction de mélange des copies.
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Closure
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
class LatexFormManager_Closure extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/closure.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string
	 */
	private $sMixedGroup						= "";

	/**
	 * @brief	Initialisation du mélange des groupes de question.
	 *
	 * @param	string	$sGroup				: ensemble des groupes à mélanger.
	 * @return	void
	 */
	public function addMixedGroup($sGroup) {
		$this->sMixedGroup .= $sGroup;
	}

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 * 			\melangegroupe{amc}
	 * 			\restituegroupe{amc}
	 *			%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 * 			\clearpage
	 * 		}
	 * 		\end{document}
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex = sprintf($sFileContents, $this->sMixedGroup);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
