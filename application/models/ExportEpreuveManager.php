<?php
/**
 * @brief	Classe de gestion d'exportation de l'épreuve avec la liste des candidats au format PDF.
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
 * @version		$LastChangedRevision: 94 $
 * @since		$LastChangedDate: 2017-12-29 17:27:29 +0100 (Fri, 29 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ExportEpreuveManager extends DocumentManager {

	const		DEFAULT_TABLE_SALLE			= 20;
	const		DEFAULT_SIGNATURE			= 'signature';

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @param 	integer		$nIdEpreuve		: identifiant de l'épreuve.
	 * @param 	boolean		$bPageSalle		: (optionnel) génération de la page destinée pour l'affichage en salle.
	 * @param 	boolean		$bOrderCandidat	: (optionnel) TRI par les NOM et PRÉNOM de chaque candidat, sinon par le NUMÉRO DE TABLE dans chaque salle.
	 * @return 	void
	 */
	public function __construct($nIdEpreuve, $bPageSalle = true, $bOrderCandidat = true) {
		// Initialisation des paramètres de l'export
		$this->setContentType("application/pdf");
		$this->setExtension("pdf");

		// Instance du modèle de gestion des formulaires
		$this->_oFormulaireManager			= new FormulaireManager();

		// Recherche de l'épreuve par son identifiant
		$aEpreuve							= $this->_oFormulaireManager->getEpreuveById($nIdEpreuve);

		// Recherche de tous les candidats de l'épreuve par son identifiant
		$aCandidats							= $this->_oFormulaireManager->findAllCandidatsByEpreuveId($nIdEpreuve);

		// Récupération du nom du stage
		$sStage								= DataHelper::get($aEpreuve,				"libelle_stage",				DataHelper::DATA_TYPE_PDF);

		// Récupération du libellé de l'épreuve
		$sLibelleEpreuve					= DataHelper::get($aEpreuve,				"libelle_epreuve",				DataHelper::DATA_TYPE_PDF);

		// Récupération du type de l'épreuve
		$sType								= DataHelper::get($aEpreuve,				"type_epreuve",					DataHelper::DATA_TYPE_PDF);

		// Récupération de la date de l'épreuve
		$dDate								= DataHelper::get($aEpreuve,				"date_epreuve",					DataHelper::DATA_TYPE_DATE);

		// Récupération de l'heure de l'épreuve
		$tHeure								= DataHelper::get($aEpreuve,				"heure_epreuve",				DataHelper::DATA_TYPE_TIME);

		// Récupération de la durée de l'épreuve
		$nDuree								= DataHelper::get($aEpreuve,				"duree_epreuve",				DataHelper::DATA_TYPE_INT);

		// Récupération de la(es) salle(s) de l'épreuve, sinon juste une valeur dans le tableau à [0]
		$aListeSalles						= DataHelper::get($aEpreuve,				"liste_salles_epreuve",			DataHelper::DATA_TYPE_ARRAY,	array(0));

		// Récupération du format du code candidat
		$nCodeFormat						= DataHelper::get($aEpreuve,				"code_candidat_generation",		DataHelper::DATA_TYPE_PDF);

		// Récupération de l'attribution d'une table à chaque candidat
		$bAffectationTable					= DataHelper::get($aEpreuve,				"table_affectation_epreuve",	DataHelper::DATA_TYPE_BOOL,		false);

		// Fonctionnalité réalisée si l'affectation d'une table est demandée
		$bRandomTable						= false;
		if ($bAffectationTable) {
			// Récupération de l'affectation des place de façon aléatoire
			$bRandomTable					= DataHelper::get($aEpreuve,				"table_aleatoire_epreuve",		DataHelper::DATA_TYPE_BOOL,		$bRandomTable);
		}

		// Initialisation des données des salles
		$aTables							= array();		// Ensemble des tables dans chaque salle
		$aSalles							= array();		// Ensemble des salles pour l'épreuve
		$aCapacite							= array();		// Capacité de chaque salle
		$aListeSallesCandidats				= array();		// Répartition des candidats dans chaque salle

		// ========================================================================================
		//	@todo LISTE DES CANDIDATS
		// ========================================================================================

		// Construction du tableau associatif entre les libellés de champs et le titre de colonne
		$aFiltre		= array(
			'libelle_court_grade'			=> "GRADE",
			'nom_candidat'					=> "NOM",
			'prenom_candidat'				=> "PRÉNOM",
			'code_candidat'					=> "CODE",
			'table_candidat'				=> "TABLE"
		);

		// Suppression de l'information de table s'il n'est pas souhaité
		if (!$bAffectationTable) {
			// Suppression de l'attribution de la table
			unset($aFiltre['table_candidat']);
		}

		// Formatage des colonnes
		$aChangeContent	= array(
			// Formatage du nombre de chiffres du candidat et comble avec [0] par la gauche
			'code_candidat'					=> "%0".$nCodeFormat."d",
		);

		// Extraction des données des candidats selon le filtre
		$aCandidats							= DataHelper::extractArrayFromRequestByLabel($aCandidats, array_keys($aFiltre), null, false);

		// ========================================================================================
		//	@todo PDF
		// ========================================================================================
		// Initialisation du nom du fichier à partir des noms du STAGE et du FORMULAIRE
		if (strlen($sLibelleEpreuve)) {
			$this->setFilename($sStage . ' - ' . $sLibelleEpreuve);
		} else {
			$this->setFilename($sStage);
		}

		// Initialisation de la capacité totale à mettre en place pour l'examen
		$nTotalCapaciteExamen				= 0;

		// Initialisation du contenu
		$this->_document = new EpreuvePDFManager();
		$this->_document->setFont('Arial', '', 15);
		$this->_document->setStage($sStage);
		$this->_document->setTitre($sLibelleEpreuve);
		$this->_document->setEpreuve($sType . " du " . $dDate . " à " . $tHeure);
		$this->_document->setDuree("Durée : " . $nDuree . "mn");

		// Parcours de la liste des salles afin de déterminer la capacité des salles
		foreach ((array) $aListeSalles as $nOccurrence => $nIdSalle) {
			// Initialisation du tableau des salles
			$aTables[$nOccurrence]			= array();
			$aSalles[$nOccurrence]			= array();

			// Initialisation de la capacité de la salle par défaut
			$nCapacite						= self::DEFAULT_TABLE_SALLE;

			// Fonctionnalité réalisée si la salle est renseignée
			if (DataHelper::isValidNumeric($nIdSalle, false)) {
				// Récupération des informations de la salle
				$aSalles[$nOccurrence]		= $this->_oFormulaireManager->getSalleById($nIdSalle);

				// Récupération des informations de la capacité de la salle
				$nCapacite					= DataHelper::get($aSalles[$nOccurrence],	"capacite_statut_salle",		DataHelper::DATA_TYPE_INT,		self::DEFAULT_TABLE_SALLE);
			}

			// Stockage de la capacité de la salle
			$aCapacite[$nOccurrence]		= $nCapacite;
			$nTotalCapaciteExamen			+= $nCapacite;

			// Initialisation des places disponibles pour la salle
			for ($i = 0 ; $i < $nCapacite ; $i++) {
				// Affectation du numéro de la table qui commence à [1]
				$aTables[$nOccurrence][$i]	= $i + 1;

				// Initialisation de chaque entrée vide selon le filtre
				foreach ($aFiltre as $sField => $sValue) {
					$aListeSallesCandidats[$nOccurrence][$i][$sField] = null;
				}
			}
		}

		// Contrôle si la capacité est suffisante
		if ($nTotalCapaciteExamen < count($aCandidats)) {
			throw new ApplicationException("La sélection des salles ne permet pas d'accueillir la totalité des " . count($aCandidats) . " candidats !");
		}

		// Initialisation de la première sélection à [-1]
		$nChoixCandidat						= -1;
		$nChoixTable						= -1;

		// Répartition des candidats entre chaque salle d'examen
		while (count($aCandidats)) {
			// Parcours des salles
			for ($nOccurrence = 0 ; $nOccurrence < count($aListeSalles) ; $nOccurrence++) {
				// Récupération de la capacité courante
				$nCapacite = $aCapacite[$nOccurrence];

				// Traitement de la salle tant que sa capacité d'accueil est encore possible
				if ($nCapacite && isset($aTables[$nOccurrence]) && count($aTables[$nOccurrence]) && DataHelper::isValidArray($aCandidats)) {
					// Affectation du numéro de table
					if ($bRandomTable) {
						// Tri des candidats sans préserver les clés
						sort($aCandidats);

						// Sélection d'un candidat au hazard dans la liste
						$nChoixCandidat		= rand(0, count($aCandidats) - 1);

						// Réinitialisation de la sélection
						$nChoixTable		= -1;
						// Recherche d'une entrée valide dans la liste des tables
						while (!in_array($nChoixTable, array_keys($aTables[$nOccurrence]))) {
							// Sélection d'une table au hazard dans la liste
							$nChoixTable	= rand(0, $nCapacite);
						}
					} else {
						// Sélection du candidat suivant dans la liste
						$nChoixCandidat++;

						// Sélection de la table suivante dans la liste
						$nChoixTable		= $aCapacite[$nOccurrence] - count($aTables[$nOccurrence]);
					}

					// Injection du numéro de table au candidat
					$aCandidats[$nChoixCandidat]['table_candidat'] = $aTables[$nOccurrence][$nChoixTable];

					if ($bOrderCandidat) {
						// Clé de l'entrée constituée du NOM, PRÉNOM du candidat
						$sNomCandidat		= $aCandidats[$nChoixCandidat]['nom_candidat'];
						$sPrenomCandidat	= $aCandidats[$nChoixCandidat]['prenom_candidat'];
						$sLibelleCandidat	= $sNomCandidat . "|" . $sPrenomCandidat;

						// Attribution du candidat à la salle selon son NOM et son PRÉNOM
						$aListeSallesCandidats[$nOccurrence][$sLibelleCandidat] = $aCandidats[$nChoixCandidat];
					} else {
						// Attribution du candidat à la salle selon le NUMÉRO DE TABLE
						$aListeSallesCandidats[$nOccurrence][$nChoixTable] = $aCandidats[$nChoixCandidat];
					}

					// Suppression du candidat de la liste d'origine
					unset($aCandidats[$nChoixCandidat]);

					// Suppression de la table de la liste d'origine
					unset($aTables[$nOccurrence][$nChoixTable]);
				}
			}
		}


		// Fonctionnalité réalisée si le tableau final doit être trié selon les NOM et PRÉNOM du candidat
		if ($bOrderCandidat) {
			// Parcours des salles
			foreach($aListeSallesCandidats as $nSalle => $aListeCandidat) {
				// Parcours l'ensemble des clés devant correspondre à une chaîne de caractères (NOM, PRÉNOM)
				foreach ($aListeCandidat as $xOccurrence => $aCandidat) {
					// Fonctionnalité réalisée si la clé est numérique
					if (is_numeric($xOccurrence)) {
						// Suppression de l'entrée si elle est numérique
						unset($aListeSallesCandidats[$nSalle][$xOccurrence]);
					}
				}
				ksort($aListeSallesCandidats[$nSalle]);
			}
		}

		// Parcours de la liste des salles afin de répartir les stagiaires
		foreach ($aListeSalles as $nOccurrence => $nIdSalle) {
			// Récupération du libellé de la salle
			$sLibelleSalle					= DataHelper::get($aSalles[$nOccurrence],	"libelle_salle",				DataHelper::DATA_TYPE_PDF);
			$this->_document->setSalle($sLibelleSalle);

			// Définition des alignements du tableau
			$aAligns = array(
				'libelle_court_grade'		=> PDFManager::ALIGN_CENTER,
				'nom_candidat'				=> PDFManager::ALIGN_LEFT,
				'prenom_candidat'			=> PDFManager::ALIGN_LEFT,
				'code_candidat'				=> PDFManager::ALIGN_CENTER
			);

			// Fonctionnalité réalisée si la génération de la feuille de la salle doit être réalisé
			if ($bPageSalle) {
				// Suppression de la colonne d'émargement
				unset($aFiltre[self::DEFAULT_SIGNATURE]);

				// Définition des dimensions du tableau
				$aDimensions = array(
					'libelle_court_grade'	=> 20,
					'nom_candidat'			=> 70,
					'prenom_candidat'		=> 70,
					'code_candidat'			=> 20
				);

				// Extraction du tableau des candidats à partir du filtre
				$aListeCandidats			= DataHelper::extractArrayFromRequestByLabel($aListeSallesCandidats[$nOccurrence], $aFiltre, $aChangeContent, true);
				// Définition de la police courante
				$this->_document->setFont(PDFManager::FONT_ARIAL, PDFManager::STYLE_DEFAULT, 14);
				// Ajout d'une page par salle d'épreuve
				$this->_document->buildPage($aListeCandidats, $aDimensions, $aAligns, 12);
			}

			// Définition des dimensions du tableau
			$aDimensions = array(
				'libelle_court_grade'		=> 17,
				'nom_candidat'				=> 55,
				'prenom_candidat'			=> 45,
				'code_candidat'				=> 20,
				'table_candidat'			=> 17
			);

			// Ajout de la colonne d'émargement
			$aFiltre[self::DEFAULT_SIGNATURE]	= "ÉMARGEMENT";

			// Extraction du tableau des candidats à partir du filtre
			$aListeCandidats				= DataHelper::extractArrayFromRequestByLabel($aListeSallesCandidats[$nOccurrence], $aFiltre, $aChangeContent, true);
			// Définition de la police courante
			$this->_document->setFont(PDFManager::FONT_ARIAL, PDFManager::STYLE_DEFAULT, 14);
			// Ajout d'une page par salle d'épreuve
			$this->_document->buildPage($aListeCandidats, $aDimensions, $aAligns, 12);
			// Ajout de l'émargement des surveillants
			$this->_document->addSignature("Émargement des surveillants :", true);
		}
	}

}
