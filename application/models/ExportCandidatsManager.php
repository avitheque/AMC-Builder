<?php
/**
 * @brief	Classe de gestion d'exportation de la liste des candidats au format CSV.
 *
 * L'ensemble du formulaire est parcouru afin de générer un tableau associatif entre
 * les champs du formulaire et ceux de la base de données.
 *
 * Étend la classe abstraite DocumentManager.
 * @see			{ROOT_PATH}/libraries/models/DocumentManager.php
 *
 * @name		ExportCandidatsManager
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
class ExportCandidatsManager extends DocumentManager {

	const	COLUMN_SEPARATOR		= ':';

	static	public $COLUMN_EXPORT	= array(
		'libelle_court_grade'		=>	"grade",
		'nom_candidat'				=>	"nom",
		'prenom_candidat'			=>	"prenom",
		'code_candidat'				=>	"code"
	);

	/**
	 *
	 * @param	array		$aCandidats		: liste des candidats.
	 */
	public function __construct($aCandidats = array()) {
		// Initialisation des paramètres de l'export
		$this->setContentType("text/csv");
		$this->setExtension("csv");

		// Construction de la première ligne
		$this->_document = implode(self::COLUMN_SEPARATOR, self::$COLUMN_EXPORT);

		// Parcours de la liste des candidats
		foreach ($aCandidats as $aData) {
			// Récupération des champs du candidat
			$aEntity	= array();
			foreach (self::$COLUMN_EXPORT as $sKey => $sColumn) {
				$aEntity[$sColumn] = $aData[$sKey];
			}

			// Ajout d'une nouvelle ligne de candidat
			$this->_document .= chr(13) . implode(self::COLUMN_SEPARATOR, $aEntity);
		}
	}

}
