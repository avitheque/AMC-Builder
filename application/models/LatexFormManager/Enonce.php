<?php
	/**
	 * @brief	Classe de transcription du formulaire en fichier LaTeX.
	 *
	 * Présentation de la feuille des énoncés de l'épreuve.
	 * @li	La méthode render() permet de générer le document au format LaTeX.
	 *
	 * Étend la classe abstraite LatexElement.
	 * @see			{ROOT_PATH}/libraries/models/LatexElement.php
	 *
	 * @name		LatexFormManager_Enonce
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
	class LatexFormManager_Enonce extends LatexElement {

		/**
		 * @brief	Constante de construction du document.
		 *
		 * @var		file
		 */
		const DOCUMENT_SOURCE						= '/latex/enonce.tex';

		/**
		 * @brief	Renvoi le contenu du document
		 *
		 * @code
		 *		\noindent\hrulefill
		 *		\vspace*{5mm}
		 *		\begin{center}
		 * 			{\large\bf Feuille des énoncés :}
		 * 		\end{center}
		 * @endcode
		 *
		 * @return	string LaTeX
		 */
		public function render() {
			// Récupération du contenu du fichier
			$this->addContent(file_get_contents(FW_HELPERS . self::DOCUMENT_SOURCE));

			// Renvoi du code LaTeX
			return $this->_latex;
		}
	}
