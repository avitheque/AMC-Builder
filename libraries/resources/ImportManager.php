<?php
/**
 * @brief	Classe de gestion des imports de fichiers.
 *
 * L'ensemble du fichier est parcouru afin de générer un tableau correspondant.
 *
 * @name		ImportManager
 * @category	Model
 * @package		Document
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (Sun, 30 Apr 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ImportManager {

	/**
	 * @brief	Type de fichier pour l'importation.
	 * @var		string
	 */
	const		TYPE_TEXT		= "text/plain";
	const		TYPE_CSV		= "text/csv";
	const		TYPE_XLS		= "application/vnd.ms-excel";

	/**
	 * @brief	Ensemble des caractères délimitant une cellule par défaut.
	 * @var		string
	 */
	const		CHAR_SEPARATOR	= ";,";

	/**
	 * @brief	Importation d'un fichier.
	 * Transforme le contenu d'un fichier sous forme de tableau.
	 *
	 * @li	Chaque ligne du tableau correspond à une ligne du fichier.
	 * @li	Possibilité d'utiliser un tableau de caractères permettant d'identifier chaque cellule.
	 * @code
	 * 	// Format chaîne de caractères pour un fichier CSV
	 * 	$xCharSeparator = ";,";
	 *
	 * 	// Équivalent de la chaîne ci-dessus sous forme d'un tableau
	 * 	$xCharSeparator = array(";", ",");
	 * @endcode
	 *
	 * @param	filename	$sFileName			: chemin du fichier à traiter.
	 * @param	mixed		$xCharSeparator		: ensemble des caractères de séparation de cellule.
	 * @return	array
	 */
	public function importer($sFileName, $xCharSeparator = self::CHAR_SEPARATOR) {
		// Initialisation du TEMP
		$aTemp				= array();

		// Ouverture du fichier
		$oFile = fopen($sFileName, 'r');

		// Parcours du fichier ligne par ligne
		while (!feof($oFile)) {
			// Récupération de la ligne
			$sLine		= fgets($oFile);

			// Fonctionnalité réalisée si la ligne est vide
			if (strlen($sLine) < 2) {
				continue;
			}

			// Suppression du saut de ligne
			$sString	= strtr($sLine, array("\r\n" => "", "\n" => ""));

			// Extraction du contenu
			if (!empty($xCharSeparator)) {
				$aTemp[]	= preg_split("/[" . implode("", (array) $xCharSeparator) . "]+/", $sString);
			}
		}

		// Fermeture du fichier
		fclose($oFile);

		// Renvoi du formulaire
		return $aTemp;
	}

}
