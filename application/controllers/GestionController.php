<?php
/**
 * @brief	Classe contrôleur de la gestion des stages et des candidats.
 *
 * @li	Possibilité de modifier l'identifiant du candidat (pour correction).
 *
 * Étend la classe abstraite AbstractFormulaireController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireController.php
 *
 * @name		GestionController
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
class GestionController extends AbstractFormulaireController {

	const		ID_CANDIDAT					= 'ID_CANDIDAT';
	const		ID_STAGE					= 'ID_STAGE';
	const		ID_UTILISATEUR				= 'ID_UTILISATEUR';

	const		TYPE_CANDIDAT				= "candidat";
	const		TYPE_STAGE					= "stage";
	const		TYPE_UTILISATEUR			= "utilisateur";

	private		$_idCandidat				= null;
	private		$_idStage					= null;
	private		$_idUtilisateur				= null;

	/**
	 * @brief	Instance du modèle de gestion du référentiel de l'application.
	 * @var		ReferentielManager
	 */
	protected	$_oReferentielManager		= null;

	/**
	 * @brief	Instance du modèle de gestion des stages et des candidats.
	 * @var		AdministrationManager
	 */
	protected	$_oAdministrationManager	= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li	Le contrôleur différencie l'action du bouton composé du type [ACTION_TYPE_COMPLEMENT].
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'GESTION', GestionInterface::$LIST_CHAMPS_FORM);

		// Initialisation de l'instance du référentiel
		$this->_oReferentielManager			= new ReferentielManager();

		// Initialisation de l'instance de l'administration
		$this->_oAdministrationManager		= new AdministrationManager();

		// Initialisation des variables de traitement
		$sAction							= null;
		$sType								= null;
		$sComplement						= null;

		// Récupération du bouton sélectionné du type [ACTION_TYPE_COMPLEMENT]
		if ($sButton = $this->getParam('button')) {
			// Extraction des variables de traitement à partir du nom de bouton
			$aElement						= explode("_", $sButton);

			// Renseignement des variables de traitement
			$sAction						= DataHelper::get($aElement, 0, DataHelper::DATA_TYPE_STR);
			$sType							= DataHelper::get($aElement, 1, DataHelper::DATA_TYPE_STR);
			$sComplement					= DataHelper::get($aElement, 2, DataHelper::DATA_TYPE_STR);
		}

		// Récupération des éléments en session
		$this->_idCandidat					= $this->getDataFromSession(AdministrationManager::ID_CANDIDAT);
		$this->_idStage						= $this->getDataFromSession(AdministrationManager::ID_STAGE);
		$this->_idUtilisateur				= $this->getDataFromSession(AdministrationManager::ID_UTILISATEUR);

		// Fonctionnalité réalisée selon l'action du bouton
		switch ($sAction) {

			case "fermer":
				// Message de débuggage
				$this->debug("FERMER");
				// Exécution de l'action
				$this->resetAction(null);
				break;

			case "enregistrer":
				// Message de débuggage
				$this->debug("ENREGISTRER");
				// Exécution de l'action
				$this->enregistrerAction($sType);
				break;

			case "supprimer":
				// Message de débuggage
				$this->debug("SUPPRIMER");
				// Exécution de l'action
				$this->supprimerAction($sType);
				break;

			case "ajouter":
				// Message de débuggage
				$this->debug("AJOUTER");
				// Exécution de l'action
				$this->ajouterAction($sType, $sComplement);
				break;

			case "retirer":
				// Message de débuggage
				$this->debug("RETIRER");
				// Exécution de l'action
				$this->retirerAction($sType);
				break;

			case "importer":
				// Message de débuggage
				$this->debug("IMPORTER");
				// Exécution de l'action
				$this->importerAction($sType);
				break;

			case "exporter":
				// Message de débuggage
				$this->debug("EXPORTER");
				// Exécution de l'action
				$this->exporterAction($sType);
				break;

			case "generer":
				// Message de débuggage
				$this->debug("GÉNÉRER");
				// Exécution de l'action
				$this->genererAction($sType);
				break;

			case "renouveler":
				// Message de débuggage
				$this->debug("RENOUVELER");
				// Exécution de l'action
				$this->renouvelerAction($sType);
				break;

			default:
				// Message de débuggage
				$this->debug("DÉFAUT");
				break;

		}
	}

	/******************************************************************************************************
	 * @todo	DEFAULT
	 ******************************************************************************************************/

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".indexAction()");

		// Effacement des paramètres en session
		$this->resetDataIntoSession(AdministrationManager::ID_CANDIDAT);
		$this->resetDataIntoSession(AdministrationManager::ID_STAGE);
		$this->resetDataIntoSession(AdministrationManager::ID_UTILISATEUR);

		// Effacement des données du formulaire
		$this->resetFormulaire();

		// Construction de la liste des référentiels
		$this->addToData('liste_referentiels',			$this->_oAdministrationManager->findAllReferentiels());

		// Construction de la liste des candidats
		$this->addToData('liste_candidats',				$this->_oAdministrationManager->findAllCandidats());

		// Construction de la liste des stages
		$this->addToData('liste_stages',				$this->_oAdministrationManager->findAllStages());

		// Construction de la liste des utilisateurs
		$this->addToData('liste_utilisateurs',			$this->_oAdministrationManager->findAllUtilisateurs());
	}

	/******************************************************************************************************
	 * @todo	CHARGER
	 ******************************************************************************************************/

	/**
	 * @brief	Chargement du formulaire du CANDIDAT, STAGE ou UTILISATEUR.
	 *
	 * @param	string		$sType		: type de l'entrée, parmis [candidat], [stage] ou [utilisateur].
	 * @param	integer		$nId		: identifiant de l'entrée.
	 */
	public function chargerAction($sType, $nId) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".chargerAction($sType, $nId)");

		// Fonctionnalité réalisée selon le type
		switch ($sType) {

			// Chargement d'un CANDIDAT
			case AdministrationManager::TYPE_CANDIDAT:
				// Fonctionnalité réalisée si les données du formulaire ne sont pas encore chargées
				if (!empty($nId) && $this->isEmptyFormulaire('candidat_id')) {
					// Actualisation des données du formulaire CANDIDAT
					$this->resetFormulaire(
						// Initialisation du formulaire avec les données en base
						$this->_oAdministrationManager->chargerCandidat($nId)
					);

					// Enregistrement de l'identifiant du candidat en session
					if ($this->getFormulaire('candidat_id')) {
						$this->sendDataToSession($nId, AdministrationManager::ID_CANDIDAT);
					}
				} elseif (!$this->isEmptyFormulaire('candidat_id')) {
					// Rendu de la vue de modification
					$this->render($this->_controller);
				}
			break;

			// Chargement d'un STAGE
			case AdministrationManager::TYPE_STAGE:
				// Fonctionnalité réalisée si les données du formulaire ne sont pas encore chargées
				if (!empty($nId) && $this->isEmptyFormulaire('stage_id')) {
					// Actualisation des données du formulaire STAGE
					$this->resetFormulaire(
						// Initialisation du formulaire avec les données en base
						$this->_oAdministrationManager->chargerStage($nId)
					);

					// Enregistrement de l'identifiant du stage en session
					if ($this->getFormulaire('stage_id')) {
						$this->sendDataToSession($nId,	AdministrationManager::ID_STAGE);
					}
				} elseif (!$this->isEmptyFormulaire('stage_id')) {
					// Rendu de la vue de modification
					$this->render($this->_controller);
				}
			break;

			// Chargement d'un UTILISATEUR
			case AdministrationManager::TYPE_UTILISATEUR:
				// Fonctionnalité réalisée si les données du formulaire ne sont pas encore chargées
				if (!empty($nId) && $this->isEmptyFormulaire('utilisateur_id')) {
					// Actualisation des données du formulaire UTILISATEUR
					$this->resetFormulaire(
						// Initialisation du formulaire avec les données en base
						$this->_oAdministrationManager->chargerUtilisateur($nId)
					);

					// Enregistrement de l'identifiant du stage en session
					if ($this->getFormulaire('utilisateur_id')) {
						$this->sendDataToSession($nId,	AdministrationManager::ID_UTILISATEUR);
					}
				} elseif (!$this->isEmptyFormulaire('utilisateur_id')) {
					// Rendu de la vue de modification
					$this->render($this->_controller);
				}
			break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
			break;
		}

		// Redirection afin d'effacer les éléments présents en GET
		$this->redirect("gestion/$sType");
	}

	/**
	 * @brief	Action du contrôleur pour la gestion d'un candidat.
	 */
	public function candidatAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".candidatAction()");

		// Construction de la liste des grades
		$this->addToData('liste_grades',				$this->_oReferentielManager->findListeGrades());

		// Récupération de l'identifiant du candidat en session
		$this->_idCandidat						= $this->getDataFromSession(AdministrationManager::ID_CANDIDAT);
		$this->resetDataIntoSession(AdministrationManager::ID_STAGE);
		$this->resetDataIntoSession(AdministrationManager::ID_UTILISATEUR);

		// Récupération de l'identifiant du candidat passé en GET
		$nIdCandidat							= $this->getParam('id');

		// Chargement du formulaire si l'identifiant est présent en session
		if ($nIdCandidat && empty($this->_idCandidat)) {
			// Chargement du candidat sélectionné
			$this->chargerAction(AdministrationManager::TYPE_CANDIDAT, $nIdCandidat);
		} else {
			// Message de débuggage
			$this->debug("ID_CANDIDAT = $this->_idCandidat");

			// Liste des stages du candidat
			$this->addToData('liste_stages_candidat',	$this->_oAdministrationManager->findStagesByCandidat($this->_idCandidat));
		}

		// Changement de vue
		$this->render('editer');
	}

	/**
	 * @brief	Action du contrôleur pour la gestion d'un stage.
	 */
	public function stageAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".stageAction()");

		// Récupération de l'identifiant du stage en session
		$this->_idStage							= $this->getDataFromSession(AdministrationManager::ID_STAGE);
		$this->resetDataIntoSession(AdministrationManager::ID_CANDIDAT);
		$this->resetDataIntoSession(AdministrationManager::ID_UTILISATEUR);

		// Récupération de l'identifiant du stage passé en GET
		$nIdStage								= $this->getParam('id');

		// Construction de la liste des domaines
		$this->addToData('liste_domaines',				$this->_oReferentielManager->findListeDomaines());

		// Construction de la liste des sous-domaines
		$this->addToData('liste_sous_domaines',			$this->_oReferentielManager->findListeSousDomaines());

		// Construction de la liste des catégories
		$this->addToData('liste_categories',			$this->_oReferentielManager->findListeCategories());

		// Construction de la liste des sous-categories
		$this->addToData('liste_sous_categories',		$this->_oReferentielManager->findListeSousCategories());

		// Chargement du formulaire si l'identifiant est présent en session
		if ($nIdStage && empty($this->_idStage)) {
			// Chargement du stage sélectionné
			$this->chargerAction(AdministrationManager::TYPE_STAGE, $nIdStage);
		} else {
			// Message de débuggage
			$this->debug("ID_STAGE = $this->_idStage");

			// Liste des candidats du stage
			$this->addToData('liste_candidats_stage',	$this->_oAdministrationManager->findCandidatsByStage($this->_idStage));
		}

		// Changement de vue
		$this->render('editer');
	}

	/**
	 * @brief	Action du contrôleur pour la gestion d'un utilisateur.
	 */
	public function utilisateurAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".utilisateurAction()");

		// Construction de la liste des grades
		$this->addToData('liste_grades',				$this->_oReferentielManager->findListeGrades());

		// Construction de la liste des profiles
		$this->addToData('liste_profils',				$this->_oReferentielManager->findListeProfiles());

		// Récupération de l'identifiant du stage en session
		$this->_idUtilisateur					= $this->getDataFromSession(AdministrationManager::ID_UTILISATEUR);
		$this->resetDataIntoSession(AdministrationManager::ID_CANDIDAT);
		$this->resetDataIntoSession(AdministrationManager::ID_STAGE);

		// Récupération de l'identifiant de l'utilisateur passé en GET
		$nIdUtilisateur							= $this->getParam('id');

		// Chargement du formulaire si l'identifiant est présent en session
		if ($nIdUtilisateur && empty($this->_idUtilisateur)) {
			// Chargement du stage sélectionné
			$this->chargerAction(AdministrationManager::TYPE_UTILISATEUR, $nIdUtilisateur);
		} else {
			// Message de débuggage
			$this->debug("ID_UTILISATEUR = $this->_idUtilisateur");
		}

		// Changement de vue
		$this->render('editer');
	}

	/******************************************************************************************************
	 * @todo	ENREGISTRER
	 ******************************************************************************************************/

	/**
	 * @brief	Enregistrement du formulaire CANDIDAT, STAGE ou UTILISATEUR.
	 *
	 * @param	string		$sType		: type de l'entrée, parmis [candidat], [stage] ou [utilisateur].
	 */
	public function enregistrerAction($sType) {
		// Message de débuggage
		$this->debug("BUTTON = " . $this->getParam('button'));

		// Récupération des paramètres du formulaire
		$aParams								= $this->getParamsLike($sType);

		switch ($sType) {
			case AdministrationManager::TYPE_CANDIDAT:
				// Fonctionnalité réalisée si l'identifiant du candidat n'est pas valide
				if (!empty($aParams['candidat_id']) && !empty($aParams['candidat_grade'])) {
					// Actualisation des données du formulaire au cours de l'enregistrement
					$this->resetFormulaire(
					// Enregistrement du candidat
						$this->_oAdministrationManager->enregistrerCandidat($aParams, $this->_idCandidat)
					);

					// Enregistrement de l'identifiant du candidat en session
					if ($nId = $this->getFormulaire('candidat_id')) {
						$this->sendDataToSession($nId,	AdministrationManager::ID_CANDIDAT);
					}
				} else {
					$aMessage					= array();

					// Le grade est mal saisi
					if (empty($aParams['candidat_grade'])) {
						$aMessage[]				= "Veuillez renseigner un grade valide";
					}

					// L'identifiant est mal saisi
					if (empty($aParams['candidat_id'])) {
						$aMessage[]				= "Veuillez renseigner un identifiant de candidat valide";
					}

					// Affichage d'un message d'erreur
					ViewRender::setMessageWarning("Erreur de saisie !", $aMessage);
				}
				break;

			case AdministrationManager::TYPE_STAGE:
				// Fonctionnalité réalisée si le libellé du stage n'est pas valide
				if (!empty($aParams['stage_libelle'])) {
					// Actualisation des données du formulaire au cours de l'enregistrement
					$this->resetFormulaire(
						// Enregistrement du stage
						$this->_oAdministrationManager->enregistrerStage($aParams)
					);

					// Enregistrement de l'identifiant du candidat en session
					if ($nId = $this->getFormulaire('stage_id')) {
						$this->sendDataToSession($nId,	AdministrationManager::ID_STAGE);
					}
				} else {
					// Affichage d'un message d'erreur
					ViewRender::setMessageWarning("Erreur de saisie !", "Veuillez renseigner un libellé de stage valide");
				}
				break;

			case AdministrationManager::TYPE_UTILISATEUR:
				// Fonctionnalité réalisée si l'identifiant de l'utilisateur n'est pas valide
				if (!empty($aParams['utilisateur_id'])) {
					// Actualisation des données du formulaire au cours de l'enregistrement
					$this->resetFormulaire(
						// Enregistrement du stage
						$this->_oAdministrationManager->enregistrerUtilisateur($aParams, $this->_idUtilisateur)
					);

					// Enregistrement de l'identifiant du candidat en session
					if ($nId = $this->getFormulaire('utilisateur_id')) {
						$this->sendDataToSession($nId,	AdministrationManager::ID_UTILISATEUR);
					}
				} else {
					// Affichage d'un message d'erreur
					ViewRender::setMessageWarning("Erreur de saisie !", "Veuillez renseigner un identifiant d'utilisateur valide");
				}
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}

		// Changement de vue
		$this->render('editer');
	}

	/******************************************************************************************************
	 * @todo	SUPPRIMER
	 ******************************************************************************************************/

	/**
	 * @brief	Suppression du formulaire CANDIDAT, STAGE ou UTILISATEUR.
	 *
	 * @param	string		$sType		: type de l'entrée, parmis [candidat], [stage] ou [utilisateur].
	 * @return	boolean, résultat de la suppression.
	 */
	public function supprimerAction($sType) {
		// Message de débuggage
		$this->debug("BUTTON = " . $this->getParam('button'));

		switch ($sType) {
			case AdministrationManager::TYPE_CANDIDAT:
				// Suppression du candidat
				$this->_oAdministrationManager->deleteCandidatById($this->_idCandidat);
				break;

			case AdministrationManager::TYPE_STAGE:
				// Suppression du stage
				$this->_oAdministrationManager->deleteStageById($this->_idStage);
				break;

			case AdministrationManager::TYPE_UTILISATEUR:
				// Suppression de l'utilisateur
				$this->_oAdministrationManager->deleteUtilisateurById($this->_idUtilisateur);
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}

		// Réinitialisation des paramètres en session
		$this->resetAction();
	}

	/******************************************************************************************************
	 * @todo	AJOUTER
	 ******************************************************************************************************/

	/**
	 * @brief	Ajout d'éléments au formulaire CANDIDAT, STAGE ou UTILISATEUR.
	 *
	 * @param	string		$sType		: type de l'entrée, parmis [candidat], [stage] ou [utilisateur].
	 * @param	string		$sOption	: (optionnel) complément du type d'entrée.
	 * @return	void
	 */
	public function ajouterAction($sType, $sOption = null) {
		// Message de débuggage
		$this->debug("BUTTON = " . $this->getParam('button'));

		switch ($sType) {
			case AdministrationManager::TYPE_CANDIDAT:
				/** @todo	RAS */
				break;

			case AdministrationManager::TYPE_STAGE:
				// Récupération de la liste des candidats sélectionnés
				$aListeCandidats				= $this->getParam('candidat_id');

				// Fonctionnalité réalisée si la liste des candidats est valide
				if (DataHelper::isValidArray($aListeCandidats)) {
					// Ajout de la liste de candidats au stage
					$this->_oAdministrationManager->addStageCandidats($aListeCandidats, $this->_idStage);
				}
				break;

			case AdministrationManager::TYPE_UTILISATEUR:
				/** @todo	RAS */
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}

		// Changement de vue
		$this->render('editer');
	}

	/******************************************************************************************************
	 * @todo	RETIRER
	 ******************************************************************************************************/

	/**
	 * @brief	Retrait d'une liste d'éléments sélectionnés.
	 *
	 * @return	void
	 */
	public function retirerAction($sType) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".retirerAction($sType)");

		// Récupération des paramètres du formulaire
		$aSelection								= $this->getParam('selection');

		// Fonctionnalité réalisée selon le type
		switch ($sType) {
			case AdministrationManager::TYPE_CANDIDAT:
				/** @todo	RAS */
				break;

			case AdministrationManager::TYPE_STAGE:
				if (DataHelper::isValidArray($aSelection)) {
					foreach ($aSelection as $nIdCandidat) {
						// Suppression du candidat selon l'identifiant du stage
						$this->_oAdministrationManager->deleteCandidatByIdStage($nIdCandidat, $this->_idStage);
					}
				}
				break;

			case AdministrationManager::TYPE_UTILISATEUR:
				/** @todo	RAS */
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}
	}

	/******************************************************************************************************
	 * @todo	IMPORTER
	 ******************************************************************************************************/

	/**
	 * @brief	Importation d'une liste au format CSV.
	 *
	 * @li	PHASE 1 : lecture du contenu du fichier sous forme de tableau.
	 * @li	PHASE 2 : traitement des données sélectionnées du tableau.
	 *
	 * @return	void
	 */
	public function importerAction($sType) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".importerAction($sType)");

		// Récupération des informations du fichier passé en FILE
		$aFileName								= $this->getParam('file_name');

		// Fonctionnalité réalisée si le formulaire est encore vide
		$aDatas = array();
		if (isset($aFileName['tmp_name'])) {
			#######################################################################################
			#	PHASE 1
			#######################################################################################

			// Récupération du fichier temporaire
			$sFileName = DataHelper::get($aFileName, 'tmp_name', DataHelper::DATA_TYPE_STR);
			$this->resetDataIntoSession("liste_import");
			$this->resetDataIntoSession("filtre_columns");

			// Fonctionnalité réalisée si l'importation du fichier est erroné
			if (DataHelper::get($aFileName, 'error', DataHelper::DATA_TYPE_INT)) {
				ViewRender::setMessageWarning("Veuillez sélectionner un fichier valide !");
			} else {
				// Contrôle du type de fichier à importer
				if ($aFileName['type'] == ImportManager::TYPE_CSV || $aFileName['type'] == ImportManager::TYPE_XLS) {
					// Liste des filtres de colonnes
					$aFiltre					= $this->_oAdministrationManager->findFiltreColumnns(AdministrationManager::TYPE_CANDIDAT);
					$this->sendDataToSession($aFiltre, "filtre_columns");

					// Exécution de l'import
					$oImportation				= new ImportManager();
					$aDatas						= DataHelper::removeLinesFromRequest($oImportation->importer($sFileName), $aFiltre);

					// Récupération de la liste construite par l'importation
					$this->sendDataToSession($aDatas,	"liste_import");

					if (!DataHelper::isValidArray($aDatas)) {
						// Affichage d'un message d'erreur
						ViewRender::setMessageAlert("Le fichier sélectionné n'est pas pris en charge par l'application...");
					}
				} else {
					ViewRender::setMessageWarning("Le fichier sélectionné n'est pas valide !");
				}
			}
		} else {
			#######################################################################################
			#	PHASE 2
			#######################################################################################

			// Récupération des données de la PHASE 1
			$aDatas								= $this->getDataFromSession("liste_import");

			// Traitement de l'importation du contenu du fichier CSV
			$aFiltre							= $this->getParam('filtre');

			$aImport							= array();
			foreach ($aDatas as $nOccurence => $aLine) {
				foreach ($aFiltre as $nColumn => $sLibelle) {
					if (in_array($sLibelle, $this->getDataFromSession("filtre_columns"))) {
						unset($aDatas[$nOccurence]);
						exit;
					} elseif (!empty($sLibelle)) {
						$aImport[$nOccurence][$sLibelle] = $aLine[$nColumn];
					}
				}
			}

			// Fonctionnalité réalisée selon le type
			switch ($sType) {
				case AdministrationManager::TYPE_CANDIDAT:
					/** @todo	RAS */
					break;

				case AdministrationManager::TYPE_STAGE:
					$this->_oAdministrationManager->importerCandidats($aImport, $this->_idStage);
					break;

				case AdministrationManager::TYPE_CANDIDAT:
					/** @todo	RAS */
					break;

				default:
					// Message de débuggage
					$this->debug("Type inconnu");
					break;
			}
		}

		// Changement de vue
		$this->render('editer');
	}

	/******************************************************************************************************
	 * @todo	EXPORTER
	 ******************************************************************************************************/

	/**
	 * @brief	Exportation du formulaire au format LaTeX.
	 *
	 * @return	void
	 */
	public function exporterAction($sType) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".exporterAction($sType)");

		// Récupération des paramètres du formulaire
		$aParams								= $this->getParamsLike($sType);

		// Fonctionnalité réalisée selon le type
		switch ($sType) {
			case AdministrationManager::TYPE_CANDIDAT:
				/** @todo	RAS */
				break;

			case AdministrationManager::TYPE_STAGE:
				// Récupération de la liste des stagiaires
				$aListeCandidats				= $this->_oAdministrationManager->findCandidatsByStage($this->_idStage);

				// Génération du formulaire
				$oDocument						= new ExportCandidatsManager($aListeCandidats);
				// Fonctionnalité réalisée si le libellé n'est pas vide
				if ($sLibelle					= $this->getFormulaire('stage_libelle')) {
					// Le fichier portera le libellé du stage
					$oDocument->setFilename($sLibelle);
				}
				$oDocument->render();
				break;

			case AdministrationManager::TYPE_UTILISATEUR:
				/** @todo	RAS */
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}

		// Désactivation de la vue
		$this->render(FW_VIEW_VOID);
	}

	/******************************************************************************************************
	 * @todo	GÉNÉRER
	 ******************************************************************************************************/

	/**
	 * @brief	Générer le document PDF.
	 *
	 * @return	void
	 */
	public function genererAction($sType) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".exporterAction($sType)");

		// Récupération des paramètres du formulaire
		$aParams = $this->getParamsLike($sType);

		// Fonctionnalité réalisée selon le type
		switch ($sType) {
			// Document de candidat
			case AdministrationManager::TYPE_CANDIDAT:
				/** @todo	RAS */
				break;

			// Document de stage
			case AdministrationManager::TYPE_STAGE:
				// Récupération de la liste des stagiaires
				$aListeCandidatsStage			= $this->_oAdministrationManager->findCandidatsByStage($this->_idStage);

				// Filtre sur les champs à récupérer
				$aFiltreExtraction				= array(
					'id_candidat'				=> "ID",
					'libelle_court_grade'		=> "GRADE",
					'nom_candidat'				=> "NOM",
					'prenom_candidat'			=> "PRÉNOM",
					'unite_candidat'			=> "UNITÉ",
					'code_candidat'				=> "CODE"
				);

				// Formatage des champs à réaliser
				$aDimensions					= array(
					'id_candidat'				=> 15,
					'libelle_court_grade'		=> 15,
					'unite_candidat'			=> 65
				);

				$aAligns						= array(
					'id_candidat'				=> PDFManager::ALIGN_CENTER,
					'libelle_court_grade'		=> PDFManager::ALIGN_CENTER,
					'nom_candidat'				=> PDFManager::ALIGN_LEFT,
					'prenom_candidat'			=> PDFManager::ALIGN_LEFT,
					'unite_candidat'			=> PDFManager::ALIGN_LEFT,
					'code_candidat'				=> PDFManager::ALIGN_CENTER
				);

				// Extraction des données de la requête afin de construire un nouveau tableau
				$aListeCandidats				= DataHelper::extractArrayFromRequestByLabel($aListeCandidatsStage, $aFiltreExtraction, null, true);

				// Génération du document PDF du stage
				$oDocument						= new PDFManager();
				$oDocument->setFilename($this->getParam('stage_libelle'));
				$oDocument->title($this->getParam('stage_libelle'), 30);
				$oDocument->setXY(10, 30);
				$oDocument->table($aListeCandidats, $aDimensions, $aAligns);
				$oDocument->render();
				break;

			// Document d'utilisateur
			case AdministrationManager::TYPE_UTILISATEUR:
				/** @todo	RAS */
				break;

			default:
				// Message de débuggage
				$this->debug("Type inconnu");
				break;
		}

		// Désactivation de la vue
		$this->render(FW_VIEW_VOID);
	}

	/******************************************************************************************************
	 * @todo	RENOUVELER
	 ******************************************************************************************************/

	/**
	 * @brief	Renouvellement du code candidat.
	 *
	 * @return	void
	 */
	public function renouvelerAction($sType) {
		// Message de débuggage
		$this->debug(__CLASS__ . ".renouvelerAction($sType)");

		// Récupération des paramètres du formulaire
		$aCandidats								= $this->getParam('selection');

		// Récupération des paramètres du formulaire
		$nFormat								= $this->getParam('candidat_code');

		// Renouvellement de la sélection
		if (!empty($aCandidats)) {
			$this->_oAdministrationManager->renouvelerCandidats($this->_idStage, $aCandidats, $nFormat);
		}
	}

}
