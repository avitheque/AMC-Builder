<?php
/**
 * @brief	Classe contrôleur de la génération d'un questionnalire QCM au format AMC.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		GenerationController
 * @category	Controller
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
class GenerationController extends AbstractFormulaireQCMController {

	/**
	 * @var		integer
	 */
	protected	$_idEpreuve						= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_GENERATION');

		// Effacement de l'éventuel fichier d'importation en session	(exploité dans le contrôleur [Importation])
		$this->resetDataIntoSession('FILE_NAME');

		// Récupération de l'identifiant du formulaire en session
		$this->_idEpreuve 		= $this->getDataFromSession('ID_EDITION');

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {

			case self::ACTION_EXPORTER:
				// Message de débuggage
				$this->debug("EXPORTER");
				// Exécution de l'action
				$this->exporterAction();
				break;

			default:
				break;

		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function indexAction() {
		// Fonctionnalité réalisée si l'identifiant du formulaire est renseigné
		if ($this->_idFormulaire) {
			// Chargement du formulaire si l'identifiant est présent en session
			$this->chargerAction($this->_idFormulaire);

			// Construction du référentiel des différents formats exploités
			$this->addToData('liste_formats',	$this->_oReferentielManager->findListeFormatPapier());

			// Construction du référentiel des différents types d'épreuve
			$this->addToData('liste_types',		$this->_oReferentielManager->findListeTypesEpreuve());

			// Construction de la liste des stages
			$nIdDomaine			= DataHelper::get($this->getFormulaire(), 'formulaire_domaine',	DataHelper::DATA_TYPE_INT, 0);
			$this->addToData('liste_stages',	$this->_oReferentielManager->findListeStages($nIdDomaine));

			// Construction de la liste des salles
			$dDateEpreuve		= DataHelper::get($this->getFormulaire(), 'epreuve_date', 		DataHelper::DATA_TYPE_MYDATE);
			$tHeureEpreuve		= DataHelper::get($this->getFormulaire(), 'epreuve_heure',		DataHelper::DATA_TYPE_TIME);
			$nDureeEpreuve		= DataHelper::get($this->getFormulaire(), 'epreuve_duree',		DataHelper::DATA_TYPE_INT);
			$this->addToData('liste_salles',	$this->_oReferentielManager->findListeSalles($dDateEpreuve, $tHeureEpreuve, $nDureeEpreuve));

			// Message de débuggage
			$this->debug(":id_domaine = $nIdDomaine");
		} else {
			// Recherche de la liste des formulaires en attente de génération
			$aListeGeneration	= $this->_oFormulaireManager->findAllFormulairesForGeneration();

			// Recherche de la liste des épreuves générées
			$aListeEpreuves		= $this->_oFormulaireManager->findAllEpreuves();

			// Recherche de la capacité d'accueil de chaque épreuve
			foreach ($aListeEpreuves as $nOccurrence => $aEpreuve) {
				// Ajout de l'information de la capacité à l'épreuve courante
				$aListeEpreuves[$nOccurrence]['SUM(capacite_statut_salle)']	= $this->_oFormulaireManager->getCapacitesByEpreuveId($aEpreuve['id_epreuve']);
			}

			// Envoi de la liste à la vue
			$this->addToData('liste_generation',	$aListeGeneration);
			$this->addToData('liste_epreuves',		$aListeEpreuves);
		}
	}

	/**
	 * @brief	Enregistrement du formulaire.
	 *
	 * Ré-implémente la méthode d'enregistrement pour la génération du questionnaire.
	 * @override	AbstractFormulaireQCMController.enregistrerAction()
	 *
	 * @return	void
	 */
	public function enregistrerAction() {
		// Actualisation des données du formulaire au cours de l'enregistrement
		$this->resetFormulaire(
			// Enregistrement du formulaire
			$this->_oFormulaireManager->enregistrer($this->getFormulaire(), true)
		);
	}

	/**
	 * @brief	Exportation du formulaire au format LaTeX.
	 *
	 * @return	void
	 */
	public function exporterAction() {
		// Génération du formulaire
		$oFormulaire			= new LatexFormManager($this->getFormulaire());
		$oFormulaire->render();

		// Désactivation de la vue
		$this->render(FW_VIEW_VOID);
	}

	/**
	 * @brief	Édition d'une épreuve.
	 *
	 * @return	void
	 */
	public function epreuveAction() {
		// Récupération de l'identifiant de l'épreuve
		$nIdEpreuve = $this->getParam('id_epreuve');

		if (!empty($nIdEpreuve)) {
			// Stockage de l'identifiant de l'édition en session
			$this->sendDataToSession($nIdEpreuve, "ID_EDITION");

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect('generation/epreuve');
		} elseif ($this->_idEpreuve) {
			// Édition de l'épreuve à partir de l'identifiant stocké en session
			$oEpreuve			= new ExportEpreuveManager($this->_idEpreuve, true, true);
			$oEpreuve->render();

			// Désactivation de la vue
			$this->render(FW_VIEW_VOID);
		}
	}

	/**
	 * @brief	Édition d'une épreuve.
	 *
	 * @return	void
	 */
	public function supprimerAction() {
		// Récupération de l'identifiant de l'épreuve
		$nIdEpreuve = $this->getParam('id_epreuve');

		if (!empty($nIdEpreuve)) {
			// Recherche si l'épreuve existe
			$aEpreuve = $this->_oFormulaireManager->getEpreuveById($nIdEpreuve);

			// Fonctionnalité réalisée si l'épreuve est valide
			if (DataHelper::isValidArray($aEpreuve)) {
				// Suppression de l'épreuve
				$this->_oFormulaireManager->supprimerEpreuveById($nIdEpreuve);
			}
		}

		// Redirection afin d'effacer les éléments présents en GET
		$this->redirect('generation');
	}

}
