<?php
/**
 * @brief	Classe contrôleur abstraite des formulaires QCM.
 *
 * Cette classe abstraite permet de gérer les formulaires QCM.
 *
 * @li Chargement du référentiel à la construction de la classe.
 *
 * Étend la classe abstraite AbstractFormulaireController.
 * @see			{ROOT_PATH}/application/controllers/AbstractFormulaireController.php
 *
 * @name		AbstractFormulaireQCMController
 * @category	Controllers
 * @package		Classes
 * @subpackage	Libraries
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 72 $
 * @since		$LastChangedDate: 2017-07-29 16:54:10 +0200 (Sat, 29 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class AbstractFormulaireQCMController extends AbstractFormulaireController {

	/**
	 * @brief	Constantes des actions dans le formulaire.
	 *
	 * @var		string
	 */
	const		ACTION_ACTUALISER					= "actualiser";
	const		ACTION_AJOUTER						= "ajouter";
	const		ACTION_EDITER						= "editer";
	const		ACTION_EFFACER						= "effacer";
	const		ACTION_ENREGISTRER					= "enregistrer";
	const		ACTION_EXPORTER						= "exporter";
	const		ACTION_FERMER						= "fermer";
	const		ACTION_FORCER						= "forcer";
	const		ACTION_IMPORTER						= "importer";
	const		ACTION_RETIRER						= "retirer";
	const		ACTION_SUPPRIMER					= "supprimer";
	const		ACTION_TERMINER						= "terminer";

	/**
	 * @brief	Constantes du formulaire.
	 *
	 * @var		string
	 */
	const		ID_FORMULAIRE						= 'ID_FORMULAIRE';

	/**
	 * @brief	Liste des action invisibles dans l'URL.
	 *
	 * @li	Exploité lors d'une redirection.
	 * @see		AbstractFormulaireQCMController::chargerAction()
	 * @var		array
	 */
	static protected $HIDDEN_ACTION					= array(
		'index',
		'reset'
	);

	/**
	 * @brief	Instance du référentiel.
	 * @var		ReferentielManager
	 */
	protected	$_oReferentielManager				= null;

	/**
	 * @brief	Instance du gestionnaire des formulaires QCM.
	 * @var		FormulaireManager
	 */
	protected	$_oFormulaireManager				= null;

	/**
	 * @var		integer
	 */
	protected	$_idFormulaire						= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 * @li Si le paramètre $_GET['id'] est présent, le formulaire est chargé puis le contrôleur redirige la page.
	 * @li La redirection de la page se fait sur la vue portant le même nom que le contrôleur par la variable d'instance @a $this->_controller.
	 *
	 * @param	string	$sNameSpace			: Nom du contrôleur appelé.
	 * @param	string	$sSessionNameSpace	: Nom de session permettant de stocker le formulaire, par défaut le nom de la classe.
	 */
	public function __construct($sNameSpace, $sSessionNameSpace = __CLASS__) {
		// Initialisation du contrôleur
		parent::__construct($sNameSpace, $sSessionNameSpace, FormulaireQCMInterface::$LIST_CHAMPS_FORM);

		// Initialisation des tableaux du référentiel
		$aListeDomaines								= array();
		$aListeSousDomaines							= array();
		$aListeCategories							= array();
		$aListeSousCategories						= array();

		// Instance du modèle de gestion des formulaires
		$this->_oFormulaireManager					= new FormulaireManager();

		// Initialisation de l'instance du référentiel
		$this->_oReferentielManager					= new ReferentielManager();

		// Récupération des éléments du référentiel sélectionnés
		$nIdDomaine									= $this->getFormulaire('formulaire_domaine');
		$nIdSousDomaine								= $this->getFormulaire('formulaire_sous_domaine');
		$nIdCategorie								= $this->getFormulaire('formulaire_categorie');
		$nIdSousCategorie							= $this->getFormulaire('formulaire_sous_categorie');

		// Construction du référentiel
		$aListeDomaines								= $this->_oReferentielManager->findListeDomaines();

		// Récupération des référentiels relatifs au DOMAINE
		if (!empty($nIdDomaine)) {
			$aListeSousDomaines						= $this->_oReferentielManager->findListeSousDomaines($nIdDomaine);
			$aListeCategories						= $this->_oReferentielManager->findListeCategories($nIdDomaine);
			// Récupération des référentiels relatifs à la CATÉGORIE
			if (!empty($nIdSousCategorie)) {
				$aListeSousCategories				= $this->_oReferentielManager->findListeSousCategories($nIdCategorie);
			}
		}

		// Chargement du référentiel dans la vue
		$this->addToData('liste_domaines',			$aListeDomaines);
		$this->addToData('liste_sous_domaines',		$aListeSousDomaines);
		$this->addToData('liste_categories',		$aListeCategories);
		$this->addToData('liste_sous_categories',	$aListeSousCategories);

		// Récupération de l'identifiant du formulaire en session
		$this->_idFormulaire						= $this->getDataFromSession(self::ID_FORMULAIRE);

		// Récupération de l'identifiant du formulaire passé en GET
		$nIdFormulaire = $this->getParam('id_formulaire');
		if (!empty($nIdFormulaire)) {
			// Chargement du formulaire sélectionné
			$this->chargerAction($nIdFormulaire);
		}

		/**
		 * @todo Possibilité de précharger le contenu de la bibliothèque lors du chargement
		 *
		// ========================================================================================
		// INITIALISATION DU CONTENU DE LA BIBLIOTHÈQUE - DÉSACTIVÉE AU CHARGEMENT POUR L'INSTANT...
		// ========================================================================================
		// Initialisation des critères de recherche des question à ajouter dans la bibliothèque
		$aCriteres = array();
		$aCriteres['id_domaine']					= $nIdDomaine;
		$aCriteres['id_sous_domaine']				= $nIdSousDomaine;
		$aCriteres['id_categorie']					= $nIdCategorie;
		$aCriteres['id_sous_categorie']				= $nIdSousCategorie;

		// Récupération du contenu de la bibliothèque selon les critères du formulaire
		$this->addToData('liste_bibliotheque',		$this->_oFormulaireManager->findAllQuestionsByCriteres($aCriteres));
		// ========================================================================================
		 *
		 */

		// Fonctionnalité réalisée selon l'action du bouton
		$sButton = strtolower($this->getParam('button'));
		switch ($sButton) {

			case self::ACTION_ACTUALISER:
				// Activation d'un message de confirmation JavaScript
				$this->sendDataToSession(true, 'FORMULAIRE_UPDATED');
				break;

			case self::ACTION_AJOUTER:
				// Activation d'un message de confirmation JavaScript
				$this->sendDataToSession(true, 'FORMULAIRE_UPDATED');
				// Message de débuggage
				$this->debug("AJOUTER");
				// Exécution de l'action
				$this->ajouterAction();
				break;

			case self::ACTION_FORCER:
				// Activation d'un message de confirmation JavaScript
				$this->sendDataToSession(true, 'FORMULAIRE_UPDATED');
				// Message de débuggage
				$this->debug("FORCER");
				// Exécution de l'action
				$this->forcerAction();
				break;

			case self::ACTION_ENREGISTRER:
				// Message de débuggage
				$this->debug("ENREGISTRER");
				// Exécution de l'action
				$this->enregistrerAction();
				break;

			case self::ACTION_EFFACER:
			case self::ACTION_FERMER:
				// Message de débuggage
				$this->debug("RESET");
				// Exécution de l'action
				$this->resetAction(null);
				break;

			case self::ACTION_TERMINER:
				// Message de débuggage
				$this->debug("TERMINER");
				// Exécution de l'action
				$this->terminerAction();
				break;

			default:
				// Retrait d'une question au formulaire : $sButton = "retirer_{occurrence}"
				if (preg_match("@^" . self::ACTION_RETIRER . "_([0-9]+)@", $sButton, $aMatched)) {
					// Récupération de l'occurence de la question
					$nQuestion = $aMatched[1];

					// Message de débuggage
					$this->debug("RETIRER");
					// Exécution de l'action
					$this->retirerAction($nQuestion);
				}
				break;
		}

		// Transfert de l'info du bouton
		$this->_aForm['action_button']				= $sButton;
	}

	/**
	 * @brief	Ajout d'une question dans le formulaire.
	 *
	 * @li	Initialisation des entrées de chaque réponses à la nouvelle question.
	 * @li	Stockage de l'occurrence de l'onglet du Questionnaire dans le champ caché `formulaire_active_tab`.
	 * @li	Stockage de l'occurrence de la question dans le champ caché `formulaire_active_question`.
	 *
	 * @return	void
	 */
	public function ajouterAction() {
		// Passage par défaut à l'onglet du Questionnaire
		$this->_aForm['formulaire_active_tab']				= 1;
		// Activation de occurrence de la nouvelle question dans le Questionnaire
		$this->_aForm['formulaire_active_question']			= $this->_aForm['formulaire_nb_total_questions'];

		// Incrémentation du nombre de questions dans le formulaire
		if (isset($this->_aForm['formulaire_nb_total_questions'])) {
			$this->_aForm['formulaire_nb_total_questions']	+= 1;
		} else {
			$this->_aForm['formulaire_nb_total_questions']	= 1;
		}
	}

	/**
	 * @brief	Ajout d'une question dans le formulaire.
	 *
	 * @return	void
	 */
	public function forcerAction() {
		// Récupération de la pénalité par défaut via la méthode POST
		$pPenalite = DataHelper::get($this->getParams(),				'formulaire_penalite',	DataHelper::DATA_TYPE_INT_ABS,		FormulaireManager::PENALITE_DEFAUT);

		// Fonctionnalité réalisée si au moins une question est présente
		if (count($this->_aForm['question_penalite'])) {
			// Parcours du questionnaire
			foreach ($this->_aForm['question_penalite'] as $nQuestion => $aQuestion) {
				// Ecrasement de la valeur par défaut
				$this->_aForm['question_penalite'][$nQuestion] = $pPenalite;
			}
		}
	}

	/**
	 * @brief	Chargement du formulaire.
	 *
	 * @param	integer		$nId	: identifiant du formulaire.
	 */
	public function chargerAction($nId) {
		// Fonctionnalité réalisée si les données du formulaire ne sont pas encore chargées
		if (!empty($nId) && empty($this->_idFormulaire)) {
			// Actualisation des données du formulaire
			$this->resetFormulaire(
				// Initialisation du formulaire avec les données en base
				$this->_oFormulaireManager->charger($nId)
			);

			// Protection du formulaire contre la modification si un contrôle est en cours
			$this->sendDataToSession($this->_oFormulaireManager->isControleExistsByIdEpreuve($this->_aForm['epreuve_id']), 'CONTROLE_EPREUVE_EXISTS');
			$this->addToData('CONTROLE_EPREUVE_EXISTS', $this->_oFormulaireManager->isControleExistsByIdEpreuve($this->_aForm['epreuve_id']));

			// Enregistrement de l'identifiant du formulaire en session
			$this->sendDataToSession($nId, self::ID_FORMULAIRE);

			// Redirection afin d'effacer les éléments présents en GET
			if (in_array($this->_action, self::$HIDDEN_ACTION)) {
				// Redirection dans l'action par défaut
				$this->redirect($this->_controller);
			} else {
				// Redirection avec l'action
				$this->redirect($this->_controller . '/' . $this->_action);
			}
		} elseif (!empty($this->_aForm['formulaire_id'])) {
			// Rendu de la vue de modification
			$this->render($this->_controller);
		}
	}

	/**
	 * @brief	Rapatriement des éléments importés de la bibliothèque.
	 *
	 * Méthode exploité lors d'une erreur d'enregistrement afin de rapatrier dans la bibliothèque les élément sélectionnés par l'utilisateur.
	 *
	 * @li	Ne récupère que les éléments nons associés au formulaire (associaton en base dans la table `formulaire_question`).
	 * @li	Réinitialisation de l'onglet actif par défaut.
	 *
	 * @return	void
	 */
	protected function _getUndoBibliothequeItems() {
		// Récupération des entrées de la bibliothèque non enregisrées dans le formulaire
		$aListePanelItems											= explode(GalleryHelper::EXCLUDE_SEPARATOR, $this->_aForm["bibliotheque_exclude"]);

		// Récupération de l'ensemble des champs de la bibliothèque
		$aBibliothequeFields										= DataHelper::getLinesFromArrayLike($this->_aForm, "bibliotheque_");
		// Parcours des éléments de la bibliothèque
		foreach ($aBibliothequeFields as $sField => $aItems) {
			// Réinitialisation du champ du formulaire
			$this->_aForm[$sField]									= array();
		}

		// Parcours l'ensemble des éléments trouvés
		if (DataHelper::isValidArray($aListePanelItems)) {
			$nCount			= 0;
			foreach ($aListePanelItems as $nOccurrence => $nIdQuestion) {
				// Fonctionnalité réalisée si l'élément n'existe pas déjà dans la liste des questions
				if (!in_array($nIdQuestion, $this->_aForm['question_id'])) {
					// Recherche de la question en base de données
					$aQuestion										= $this->_oFormulaireManager->getQuestionById($nIdQuestion);

					// Chargement des informations de la bibliothèque sélectionnée dans le formulaire
					$this->_aForm['bibliotheque_id'][$nCount]		= $nIdQuestion;
					$this->_aForm['bibliotheque_titre'][$nCount]	= DataHelper::get($aQuestion, "titre_question",	DataHelper::DATA_TYPE_TXT);
					$this->_aForm['bibliotheque_enonce'][$nCount]	= DataHelper::get($aQuestion, "enonce_question",	DataHelper::DATA_TYPE_TXT);
					$this->_aForm['bibliotheque_libre'][$nCount]	= DataHelper::get($aQuestion, "libre_question",	DataHelper::DATA_TYPE_BOOL);
					$this->_aForm['bibliotheque_stricte'][$nCount]	= DataHelper::get($aQuestion, "stricte_question",	DataHelper::DATA_TYPE_BOOL);

					// Fonctionnalité réalisée si la réponse n'est pas LIBRE
					if (!$this->_aForm['bibliotheque_libre'][$nCount]) {
						// Recherche des réponses associées à la question en base de données
						$aReponses									= $this->_oFormulaireManager->findReponsesByIdQuestion($nIdQuestion);

						// Récupération des réponses associées à la question
						$this->_aForm['bibliotheque_nombre_reponses'][$nCount] = count($aReponses);
					}

					$nCount++;
				}
			}
		}

		// Retour sur le premier onglet
		$this->_aForm['formulaire_active_tab']	= 0;
	}

	/**
	 * @brief	Enregistrement du formulaire.
	 *
	 * @return	void
	 */
	public function enregistrerAction() {
		// Récupération du titre du formulaire
		$sFormulaireTitre	= trim($this->_aForm['formulaire_titre']);

		// Fonctionnalité réalisé si le titre du formulaire est vide
		if (empty($sFormulaireTitre)) {
			// Avertissement sur le champ manquant
			ViewRender::setMessageAlert("ERREUR RENCONTRÉ AU COURS DE L'ENREGISTREMENT !", "Veuillez renseigner le nom du formulaire...");

			// Récupération des entrées de la bibliothèque non enregistrées dans le formulaire
			$this->_getUndoBibliothequeItems();
		} else {
			// Enregistrement du formulaire
			$this->_aForm = $this->_oFormulaireManager->enregistrer($this->_aForm, false);
		}
	}

	/**
	 * @brief	Fermeture d'un formulaire.
	 * @param	string		$sRessource		: (optionnel) ressource pour la redirection, sinon le contrôleur est appelé par défaut.
	 */
	public function resetAction($sRessource = null) {
		// Réinitialisation des variables par défaut du formulaire
		$this->resetFormulaire(
			array(
				'formulaire_id'						=> 0,
				'formulaire_active_tab'				=> 0,
				'formulaire_active_question'		=> 0,
				'formulaire_nb_total_questions'		=> FormulaireManager::NB_TOTAL_QUESTIONS_DEFAUT
			)
		);

		// Redirection afin d'effacer les éléments présents en GET
		parent::resetAction(null);
	}

	/**
	 * @brief	Finalisation du formulaire.
	 *
	 * Le formulaire est enregistré et transmis pour validation.
	 *
	 * @return	void
	 */
	public function terminerAction() {
		// Récupération du titre du formulaire
		$sFormulaireTitre	= trim($this->_aForm['formulaire_titre']);

		// Fonctionnalité réalisé si le titre du formulaire est vide
		if (empty($sFormulaireTitre)) {
			// Avertissement sur le champ manquant
			ViewRender::setMessageAlert("ERREUR RENCONTRÉ AU COURS DE L'ENREGISTREMENT !", "Veuillez renseigner le nom du formulaire...");

			// Récupération des entrées de la bibliothèque non enregistrées dans le formulaire
			$this->_getUndoBibliothequeItems();
		} else {
			// Enregistrement du formulaire
			$this->enregistrerAction();

			// Finalisation du formulaire
			$this->_oFormulaireManager->terminer($this->_aForm['formulaire_id']);

			// Effacement du formulaire
			$this->resetAction(null);
		}
	}

	/**
	 * @brief	Retrait d'une question au formulaire.
	 *
	 * @li	Seule la relation dans la table `formulaire_question` est supprimée.
	 *
	 * @param	integer		$nQuestion	: occurence de la question dans le formulaire.
	 * @return	void
	 */
	public function retirerAction($nQuestion) {
		// Récupération de l'identifiant du formulaire
		$nIdFormulaire	= DataHelper::get($this->_aForm,				'formulaire_id',		DataHelper::DATA_TYPE_INT,		null);

		// Fonctionnalité réalisée si l'identifiant ne correspond pas à celui de l'instance
		if ($this->_idFormulaire != $nIdFormulaire) {
			// STOP !
			return false;
		}

		// Récupération de l'identifiant de la question
		$nIdQuestion	= DataHelper::get($this->_aForm['question_id'],	$nQuestion,				DataHelper::DATA_TYPE_INT,		null);

		// Suppression de la relation entre le formulaire et la question
		$bValide		= $this->_oFormulaireManager->retirerQuestion($nIdFormulaire, $nIdQuestion);

		// Fonctionnalité réalisée si la suppression s'est correctement déroulée
		if ($bValide) {
			// Récupération de la liste des champs à purger relatifs aux QUESTION et REPONSES
			$aReset		= DataHelper::getLinesFromArrayLike(FormulaireQCMInterface::$LIST_CHAMPS_FORM, array("question_", "reponse_"));

			// Fonctionnalité réalisée pour tous les champs à purger
			foreach ($aReset as $sChamp => $iType) {
				// Suppression du champ de la question
				unset($this->_aForm[$sChamp][$nQuestion]);
				// Réaffectation de occurrence des questions dans l'ordre
				$this->_aForm[$sChamp] = array_values($this->_aForm[$sChamp]);
			}

			// Soustraction du nombre total de questions dans le formulaire
			$this->_aForm['formulaire_nb_total_questions']--;

			// Fonctionnalité réalisée si la question active n'existe plus
			if ($this->_aForm['formulaire_active_question'] >= $this->_aForm['formulaire_nb_total_questions']) {
				// Adaptation de l'occurrence de la question active
				$this->_aForm['formulaire_active_question'] = $this->_aForm['formulaire_nb_total_questions'] - 1;
			}
		}
	}
}
