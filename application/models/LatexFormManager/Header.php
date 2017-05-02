<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Entête du document avec déclaration des bibliothèques LaTeX.
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Header
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
class LatexFormManager_Header extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/header.tex';

	/**
	 * @brief	Constantes des paramètres de création du document.
	 *
	 * @var		string
	 */
	const PACKAGE_INPUTENC						= "utf8x";			// Format du document LaTeX
	const PACKAGE_FONTENC						= "T1";
	const PACKAGE_AMC							= "bloc,completemulti";

	/**
	 * @brief	Constante de génération des copies aléatoires.
	 *
	 * @code
	 * 	\AMCrandomseed{1237893}
	 * @endcode
	 *
	 * @var		string
	 */
	const DOCUMENT_RANDOMSEED					= "\\AMCrandomseed{%d}\n\n";

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $sPaperSize							= FormulaireManager::GENERATION_FORMAT_DEFAUT;
	private $sLanguage							= FormulaireManager::GENERATION_LANGUE_DEFAUT;
	private $nRandomSeed						= null;

	/**
	 * @brief	Initialisation de la taille du papier.
	 *
	 * @param	string	$sSize				: taille du papier.
	 * @return	void
	 */
	public function setPaperSize($sSize) {
		$this->sPaperSize = $sSize;
	}

	/**
	 * @brief	Initialisation de la langue du document.
	 *
	 * @param	string	$sSize				: taille du papier.
	 * @return	void
	 */
	public function setLanguage($sLanguage) {
		$this->sLanguage = $sLanguage;
	}

	/**
	 * @brief	Initialisation du modèle de mélange.
	 *
	 * @param	integer	$nSeed				: graine de mélange.
	 * @return	void
	 */
	public function setRandomSeed($nSeed) {
		$this->nRandomSeed = $nSeed;
	}

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 *		\documentclass[%generation_format]{article}
	 *
	 *		\usepackage[utf8x]{inputenc}
	 *		\usepackage[T1]{fonctenc}
	 *		\usepackage[francais,bloc,completemulti]{automultiplechoice}
	 *
	 *		\begin{document}
	 *		\AMCrandomseed{1237893}
	 *
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex = sprintf($sFileContents, $this->sPaperSize, self::PACKAGE_INPUTENC, self::PACKAGE_FONTENC, $this->sLanguage, self::PACKAGE_AMC);

		// Customisation mélange aléatoire des questions / réponses.
		if (!empty($this->nRandomSeed)) {
			$this->_latex .= sprintf(self::DOCUMENT_RANDOMSEED, $this->nRandomSeed);
		}

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
