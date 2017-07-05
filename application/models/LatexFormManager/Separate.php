<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Détermination de la feuille des réponses séparées avec la zone date et durée de l'épreuve.
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Exemplaire
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
class LatexFormManager_Separate extends LatexElement {

	/**
	 * @brief	Constante de construction du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/separate.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $sTitle								= FormulaireManager::EPREUVE_TYPE_DEFAUT;
	private $sLabel								= FormulaireManager::GENERATION_NOM_DEFAUT;
	private $sDate								= FormulaireManager::EPREUVE_DATE_FORMAT;
	private $sTime								= FormulaireManager::EPREUVE_DUREE_DEFAUT;

	/**
	 * @brief	Initialisation du titre de l'épreuve.
	 *
	 * @param	string	$sTitle				: titre de l'épreuve.
	 * @return	void
	 */
	public function setTitle($sTitle) {
		$this->sTitle = $sTitle;
	}

	/**
	 * @brief	Initialisation du libellé du stage.
	 *
	 * @param	string	$sLabel				: libellé du stage.
	 * @return	void
	 */
	public function setLabel($sLabel) {
		$this->sLabel = $sLabel;
	}

	/**
	 * @brief	Initialisation de la date de l'épreuve.
	 *
	 * @param	string	$sDate				: date de l'épreuve.
	 * @return	void
	 */
	public function setDate($sDate) {
		$this->sDate = $sDate;
	}

	/**
	 * @brief	Initialisation de la durée de l'épreuve.
	 *
	 * @param	string	$sTime				: durée de l'épreuve.
	 * @return	void
	 */
	public function setTime($sTime) {
		$this->sTime = $sTime;
	}

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 * 		\AMCcleardoublepage
	 * 		\AMCdebutFormulaire
	 * 		\noindent{\bf %s \hfill %s \\ %s \hfill Durée : %d minutes}
	 *
	 * 		\noindent\hrulefill
	 * 		\vspace*{.5cm}
	 *
	 * 		\begin{center}
	 * 			{\large\bf Feuille des réponses :}
	 * 		\end{center}
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex .= sprintf($sFileContents, $this->sLabel, $this->sTitle, $this->sDate, $this->sTime);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
