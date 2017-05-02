<?php
/**
 * @brief	Classe de transcription du formulaire en fichier LaTeX.
 *
 * Zone d'identification des codes candidats
 * @li	La méthode render() permet de générer le document au format LaTeX.
 *
 * Étend la classe abstraite LatexElement.
 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
 *
 * @name		LatexFormManager_Candidat
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
class LatexFormManager_Candidat extends LatexElement {

	/**
	 * @brief	Constante de construction du début du document.
	 *
	 * @var		file
	 */
	const DOCUMENT_SOURCE						= '/latex/candidat.tex';

	/**
	 * @brief	Variables de construction du document.
	 *
	 * @var		string|integer|array
	 */
	private $sLabel								= FormulaireManager::CANDIDATS_LABEL_DEFAULT;
	private $nCountCode							= FormulaireManager::CANDIDATS_CODE_DEFAUT;
	private $sText								= FormulaireManager::CANDIDATS_CARTOUCHE_DEFAUT;

	/**
	 * @brief	Initialisation du libellé dans le cartouche du candidat.
	 *
	 * @param	string	$sLabel				: libellé affiché dans le cartouche.
	 * @return	void
	 */
	public function setLabel($sLabel) {
		$this->sLabel = $sLabel;
	}

	/**
	 * @brief	Initialisation du nombre de chiffres.
	 *
	 * @param	integer	$nCode				: nombre de chiffres.
	 * @return	void
	 */
	public function setCountCode($nCode) {
		$this->nCountCode = $nCode;
	}

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
	 * 			\begin{minipage}{.4\linewidth}
	 * 				\champnom{
	 * 					\fbox{
	 * 						\begin{minipage}{.9\linewidth}
	 * 							Code candidat :
	 *
	 * 							\vspace*{.5cm}\dotfill
	 * 							\vspace*{1mm}
	 * 						\end{minipage}
	 * 					}
	 * 				}
	 * 				\vspace{3ex}
	 * 				Codez votre code candidat à l’aide
	 * 				des cases ci-contre en reportant chaque
	 * 				numéro de gauche à droite
	 * 			\end{minipage}
	 * 			\begin{minipage}{.1\linewidth}
	 * 				\vspace{2cm}
	 * 				$\longrightarrow{}$
	 * 			\end{minipage}
	 * 			\begin{minipage}{.5\linewidth}
	 * 				\noindent\AMCcode{code}{%d}\hspace*{\fill}
	 * 			\end{minipage}
	 *
	 * 			\vspace{.5cm}
	 * 			\noindent\hrulefill
	 * @endcode
	 *
	 * @return	string LaTeX
	 */
	public function render() {
		// Récupération du contenu du fichier
		$sFileContents = file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE);

		// Initialisation du document
		$this->_latex = sprintf($sFileContents, $this->sLabel, $this->sText, $this->nCountCode);

		// Renvoi du code LaTeX
		return $this->_latex;
	}
}
