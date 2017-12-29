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
 * @version		$LastChangedRevision: 93 $
 * @since		$LastChangedDate: 2017-12-29 15:37:13 +0100 (Fri, 29 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class GenerationController extends AbstractFormulaireQCMController {

	/**
	 * @brief	Constantes de l'épreuve.
	 *
	 * @var		string
	 */
	const		ID_EPREUVE							= 'ID_EPREUVE';

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
		$this->_idEpreuve 			= $this->getDataFromSession(self::ID_EPREUVE);

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
		// Suppression de l'identifiant de l'épreuve en session
		$this->resetDataIntoSession(self::ID_EPREUVE);

		// Recherche de la liste des formulaires en attente de génération
		$aListeGeneration			= $this->_oFormulaireManager->findAllFormulairesForGeneration();

		// Recherche de la liste des épreuves générées
		$aListeEpreuves				= $this->_oFormulaireManager->findAllEpreuves();

		// Recherche de la liste des épreuves générées
		$aListeProgrammations		= $this->_oFormulaireManager->findAllProgrammations();

		// Recherche de la capacité d'accueil de chaque épreuve
		foreach ($aListeProgrammations as $nOccurrence => $aEpreuve) {
			// Ajout de l'information de la capacité à l'épreuve courante
			$aListeProgrammations[$nOccurrence]['SUM(capacite_statut_salle)']	= $this->_oFormulaireManager->getCapacitesByEpreuveId($aEpreuve['id_epreuve']);
		}

		// Envoi de la liste à la vue
		$this->addToData('liste_generation',		$aListeGeneration);
		$this->addToData('liste_epreuves',			$aListeProgrammations);
		$this->addToData('liste_programmations',	$aListeProgrammations);
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
		$oFormulaire				= new LatexFormManager($this->getFormulaire());
		$oFormulaire->render();

		// Désactivation de la vue
		$this->render(FW_VIEW_VOID);
	}

	/**
	 * @brief	Programmer une nouvelle épreuve.
	 *
	 * Programmation d'une nouvelle épreuve.
	 * @li	Si l'identifiant d'une épreuve est passé en paramètre, l'identifiant du formulaire est recherché
	 * @return	void
	 */
	public function epreuveAction() {
		// Récupération de l'identifiant de l'épreuve
		$nIdEpreuve					= $this->getParam('id_epreuve');

		// Récupération de l'identifiant de l'épreuve
		$nIdFormulaire				= $this->getParam('id_formulaire');

		// Initialisation des données de l'épreuve
		if (!empty($nIdEpreuve)) {
			// Stockage de l'identifiant de l'épreuve en session
			$this->sendDataToSession($nIdEpreuve, self::ID_EPREUVE);

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect($this->_controller . '/epreuve');
		} elseif ($this->_idEpreuve) {
			// Actualisation des données du formulaire
			$this->resetFormulaire(
				// Initialisation du formulaire avec les données en base
				$this->_oFormulaireManager->generer($this->_idEpreuve)
			);

			// Protection du formulaire contre la modification si un contrôle est en cours
			$this->sendDataToSession($this->_oFormulaireManager->isControleExistsByIdEpreuve($this->_aForm['epreuve_id']), 'CONTROLE_EPREUVE_EXISTS');
			$this->addToData('CONTROLE_EPREUVE_EXISTS', $this->_oFormulaireManager->isControleExistsByIdEpreuve($this->_aForm['epreuve_id']));

		} elseif (empty($this->_idFormulaire)) {
			// Suppression de l'identifiant de l'épreuve en session
			$this->resetDataIntoSession(self::ID_EPREUVE);
		}

		// Construction du référentiel des différents formats exploités
		$this->addToData('liste_formats',	$this->_oReferentielManager->findListeFormatPapier());

		// Construction du référentiel des différents types d'épreuve
		$this->addToData('liste_types',		$this->_oReferentielManager->findListeTypesEpreuve());

		// Construction de la liste des stages
		$nIdDomaine					= null;
		if ($nIdFormulaire) {
			$nIdDomaine				= $this->getFormulaire('formulaire_domaine',	DataHelper::DATA_TYPE_INT);
		}
		// Chargement de la liste des stages en cours
		$this->addToData('liste_stages',	$this->_oReferentielManager->findListeStages($nIdDomaine));

		// Construction de la liste des salles
		$dDateEpreuve				= $this->getFormulaire('epreuve_date', 			DataHelper::DATA_TYPE_MYDATE);
		$tHeureEpreuve				= $this->getFormulaire('epreuve_heure',			DataHelper::DATA_TYPE_TIME);
		$nDureeEpreuve				= $this->getFormulaire('epreuve_duree',			DataHelper::DATA_TYPE_INT);
		$this->addToData('liste_salles',	$this->_oReferentielManager->findListeSalles($dDateEpreuve, $tHeureEpreuve, $nDureeEpreuve));

		// Message de débuggage
		$this->debug(":id_domaine = $nIdDomaine");
	}

	/**
	 * @brief	Impression d'une programmation d'épreuve.
	 *
	 * @return	void
	 */
	public function imprimerAction() {
		// Récupération de l'identifiant de l'épreuve
		$nIdEpreuve					= $this->getParam('id_epreuve');

		if (!empty($nIdEpreuve)) {
			// Stockage de l'identifiant de l'épreuve en session
			$this->sendDataToSession($nIdEpreuve, self::ID_EPREUVE);

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect($this->_controller . '/imprimer');
		} elseif ($this->_idEpreuve) {
			// Suppression de l'identifiant de l'épreuve en session
			$this->resetDataIntoSession(self::ID_EPREUVE);

			try {
				// Édition de l'épreuve à partir de l'identifiant stocké en session
				$oDocument = new ExportEpreuveManager($this->_idEpreuve, true, true);
			} catch (ApplicationException $e) {
				$oDocument = new PDFManager();
				$oDocument->setFilename("Erreur d'impression");
				$oDocument->addLine();
				$oDocument->setTextColor(255, 0, 0);
				$oDocument->title("Erreur rencontrée lors de l'impression", 20);
				$oDocument->addLine();
				$oDocument->setFontSizePt(15);
				$oDocument->setTextColor(0);
				$oDocument->textMiddle($e->getMessage());
			}
			$oDocument->render();

			// Désactivation de la vue
			$this->render(FW_VIEW_VOID);
		}
	}

	/**
	 * @brief	Suppression d'une épreuve.
	 *
	 * @return	void
	 */
	public function supprimerAction() {
		// Récupération de l'identifiant de l'épreuve
		$nIdEpreuve					= $this->getParam('id_epreuve');

		if (!empty($nIdEpreuve)) {
			// Recherche si l'épreuve existe
			$aEpreuve				= $this->_oFormulaireManager->getEpreuveById($nIdEpreuve);

			// Fonctionnalité réalisée si l'épreuve est valide
			if (DataHelper::isValidArray($aEpreuve)) {
				// Suppression de l'épreuve
				$this->_oFormulaireManager->supprimerEpreuveById($nIdEpreuve);
			}
		}

		// Suppression de l'identifiant de l'épreuve en session
		$this->resetDataIntoSession(self::ID_EPREUVE);

		// Redirection afin d'effacer les éléments présents en GET
		$this->redirect($this->_controller);
	}

}
