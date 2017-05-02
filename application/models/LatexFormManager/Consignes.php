<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Renseignement sur la façon de remplire le QCM par les candidats
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Consignes
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
class LatexFormManager_Consignes extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/consignes.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $sText								= FormulaireManager::GENERATION_CONSIGNES_DEFAUT;

	/**
	 * @brief	Initialisation de la taille du papier.
	 *
	 * @param	string	$sSize				: taille du papier.
	 * @return	void
	 */
	public function setText($sText) {
		$this->sText = $sText;
	}

	/**
	 * @brief	Renvoi le contenu du document
	 *
	 * @code
	 * 		\begin{center}
	 * 			Veuillez remplir complètement chaque case au stylo à encre noir ou bleu-noir afin de reporter vos choix de réponse. Les encres de couleur claires, fluorescentes ou effaçables sont interdites.
	 * 			Pour toute correction, veuillez utiliser du blanc correcteur exclusivement.
	 * 			DANS CE DERNIER CAS, NE REDESSINEZ PAS LA CASE !
	 * 		\end{center}
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
