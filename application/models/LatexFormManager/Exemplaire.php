<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Détermination du nombre d'exemplaires avec la zone date et durée de l'épreuve.
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
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class LatexFormManager_Exemplaire extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/exemplaire.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $nNumber							= FormulaireManager::GENERATION_EXEMPLAIRES_DEFAUT;
	private $sTitle								= FormulaireManager::EPREUVE_TYPE_DEFAUT;
	private $sLabel								= FormulaireManager::GENERATION_NOM_DEFAUT;
	private $sDate								= FormulaireManager::EPREUVE_DATE_FORMAT;
	private $sTime								= FormulaireManager::EPREUVE_DUREE_DEFAUT;

	/**
	 * @brief	Initialisation du nombre de copies.
	 *
	 * @param	integer	$nNumber			: nombre de chiffres.
	 * @return	void
	 */
	public function setNumber($nNumber) {
		$this->nNumber = $nNumber;
	}

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
	 * 		%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 *
	 * 		\exemplaire{20}{
	 * 		\noindent{\bf Libellé du stage \hfill Titre de l'épreuve \\ Examen du 04/09/2015 \hfill Durée : 50 minutes}
	 *
	 * 		\vspace{2ex}
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex = sprintf($sFileContents, $this->nNumber, $this->sLabel, $this->sTitle, $this->sDate, $this->sTime);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
