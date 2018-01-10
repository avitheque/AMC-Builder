<?php
/**
 * @brief	Classe contrôleur de l'importation d'un fichier AMC-TXT.
 *
 * @li	Une fois l'importation enregistrée, la redirection est effectuée sur l'édition.
 * C'est pourquoi la session de l'importation est [QCM_EDITION] et l'information [ID_FORMULAIRE] doit être purgée.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		ImportationController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 100 $
 * @since		$LastChangedDate: 2018-01-10 19:53:46 +0100 (Wed, 10 Jan 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ImportationController extends AbstractFormulaireQCMController {

	/**
	 * @var		ImportQuestionnaireManager
	 */
	protected	$_oImportQuestionnaireManager;

	/**
	 * @var		string
	 */
	private		$_fileName;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_EDITION');

		// Effacement de l'identifiant du formulaire en session (exploité dans les contrôleurs [Edition])
		$this->resetDataIntoSession('ID_FORMULAIRE');

		// Instance du modèle de gestion des importations de questionnaires
		$this->_oImportQuestionnaireManager = new ImportQuestionnaireManager();

		// Instance du modèle de gestion des formulaires
		$this->_oFormulaireManager = new FormulaireManager();

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {

			case self::ACTION_IMPORTER:
				// Message de débuggage
				$this->debug("IMPORTER");
				// Exécution de l'action
				$this->importerAction();
				break;

			// Implémente la redirection lors de l'enregistrement de l'importation
			case self::ACTION_ENREGISTRER:
				// Redirection sur le contrôleur [Edition]
				$this->redirect('edition?id_formulaire=' . $this->getFormulaire('formulaire_id'));
				break;

			default:
				break;

		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {
		// Chargement du formulaire si l'identifiant est présent en session
		if ($this->getDataFromSession('FILE_NAME')) {
			// Rendu de la vue de l'importation
			$this->render('importation');
		}
	}

	/**
	 * @brief	Importation d'un formulaire.
	 */
	public function importerAction() {
		// Récupération des informations du fichier passé en FILE
		$aFileName = $this->getParam('file_name');

		// Fonctionnalité réalisée si le formulaire est encore vide
		if (DataHelper::isValidArray($aFileName) && isset($aFileName['tmp_name'])) {
			// Récupération du fichier temporaire
			$sFileName = DataHelper::get($aFileName, 'tmp_name', DataHelper::DATA_TYPE_STR);

			// Fonctionnalité réalisée si l'importation du fichier est erroné
			if (DataHelper::get($aFileName, 'error', DataHelper::DATA_TYPE_INT)) {
				// Affichage d'un message d'avertissement
				ViewRender::setMessageWarning("Veuillez sélectionner un fichier valide !");
			} else {
				// Contrôle du type de fichier à importer
				if ($aFileName['type'] == ImportManager::TYPE_TEXT) {
					// Extraction du nom du fichier
					$sName = trim(preg_replace("@(\.[a-zA-Z0-9]+)*$@", "", $aFileName['name']));
					if (empty($sName)) {
						// Nom du formulaire par défaut s'il n'est pas renseigné
						$sName = FormulaireManager::TITRE_DEFAUT;
					}

					// Récupération de la sélection des questions strictes (tout ou rien)
					$bStrict	= DataHelper::get($this->getParams(),	'formulaire_strict',	DataHelper::DATA_TYPE_BOOL,		FormulaireManager::QUESTION_STRICTE_IMPORT);
					// Récupération de la valeur de la pénalité par défaut des questions à choix multiples (non strictes)
					$pPenalite	= DataHelper::get($this->getParams(),	'formulaire_penalite',	DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::PENALITE_DEFAUT);
					// Initialisation des données du formulaire à partir de l'importation
					$this->resetFormulaire(
						// Importation du formulaire
						$this->_oImportQuestionnaireManager->importer($sFileName, $sName, $bStrict, $pPenalite)
					);
					
					// Fonctionnalité réalisée si le référentiel a été trouvé pour la catégorie du formulaire
					if ($nIdDomaine = $this->_oImportQuestionnaireManager->getDomaine()) {
						// Chargement du domaine
						$this->_aForm['formulaire_domaine']				= $nIdDomaine;
						
						// Chargement de la liste des sous-domaines
						$this->addToData('liste_sous_domaines',			$this->_oReferentielManager->findListeSousDomaines($nIdDomaine));
						// Chargement de la liste des catégories
						$this->addToData('liste_categories',			$this->_oReferentielManager->findListeCategories($nIdDomaine));
					
						// Fonctionnalité réalisée si la catégorie a été trouvé
						if ($nIdCategorie = $this->_oImportQuestionnaireManager->getCategorie()) {
							// Chargement de la catégorie
							$this->_aForm['formulaire_categorie']		= $nIdCategorie;

							// Chargement de la liste des sous-catégories
							$this->addToData('liste_sous_categories',	$this->_oReferentielManager->findListeSousCategories($nIdCategorie));
						}
					}

					// Fonctionnalité réalisée si l'importation du formulaire est valide
					if ($this->issetFormulaire()) {
						// Enregistrement du fichier en session
						$this->sendDataToSession($sFileName, 'FILE_NAME');

						// Affichage d'un message de confirmation
						ViewRender::setMessageInfo("Importation réalisée avec succès !", "Les modifications du formulaire ne sont pas encore enregistrées...");

						// Activation d'un message de confirmation JavaScript
						$this->sendDataToSession(true, 'FORMULAIRE_UPDATED');

						// Rendu de la vue de modification
						$this->render('importation');
					} else {
						// Suppression du fichier en session
						$this->resetDataIntoSession('FILE_NAME');

						// Affichage d'un message d'erreur
						ViewRender::setMessageError("Le fichier sélectionné n'est pas pris en charge par l'application...");

						// Rendu de la vue par défaut
						$this->render('index');
					}
				} else {
					// Affichage d'un message d'erreur
					ViewRender::setMessageError("Le type de fichier sélectionné n'est pas compatible !");
				}
			}
		}
	}

}
