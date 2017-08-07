<?php
/**
 * @brief	Classe contrôleur de passage d'une épreuve QCM.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		EpreuveController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 77 $
 * @since		$LastChangedDate: 2017-08-07 21:40:32 +0200 (Mon, 07 Aug 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EpreuveController extends AbstractFormulaireQCMController {

	/**
	 * @brief	Constante de programmation de l'épreuve.
	 *
	 * @var		string
	 */
	const		STATUT_PROGRAMMATION			= EpreuveManager::STATUT_PROGRAMMATION;

	/**
	 * @brief	Constantes des actions dans le formulaire.
	 *
	 * @var		string
	 */
	const		ACTION_TEMPORAIRE				= "temporaire";
	const		ACTION_FINALIZE					= "finalize";

	/**
	 * @brief	Constantes du formulaire.
	 *
	 * @var		string
	 */
	const		ID_CONTROLE						= 'ID_CONTROLE';
	const		ID_EPREUVE						= 'ID_EPREUVE';

	/**
	 * @var		integer
	 */
	protected	$_idEpreuve						= null;
	protected	$_idControle					= null;

	/**
	 * @brief	Instance du gestionnaire des contrôles QCM.
	 * @var		EpreuveManager
	 */
	protected	$_oEpreuveManager				= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_EPREUVE');

		// Instance du modèle de gestion des formulaires
		$this->_oEpreuveManager					= new EpreuveManager();

		// Récupération de l'identifiant de l'épreuve en session
		$this->_idEpreuve						= $this->getDataFromSession(self::ID_EPREUVE);
		$this->_idControle						= $this->getDataFromSession(self::ID_CONTROLE);

		// Récupération de l'identifiant de l'épreuve passé en GET
		$nIdEpreuve								= $this->getParam('id_epreuve');
		if (empty($this->_idEpreuve) && !empty($nIdEpreuve)) {
			// Chargement de l'épreuve sélectionnée
			$this->_idEpreuve					= $nIdEpreuve;
		}

		// Fonctionnalité réalisée selon l'action du bouton
		$sButton = strtolower($this->getParam('button'));
		switch ($sButton) {

			case self::ACTION_TEMPORAIRE:
				// Message de débuggage
				$this->debug("TEMPORAIRE");
				// Exécution de l'action
				$this->temporaireAction();
				break;

			case self::ACTION_FINALIZE:
				// Message de débuggage
				$this->debug("FINALIZE");
				// Exécution de l'action
				$this->finalizeAction();
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
		// Chargement du formulaire si l'identifiant est présent en session
		if ($this->_idEpreuve && empty($this->_idFormulaire)) {
			// Récupération de l'état de la programmation
			$nStatutEpreuve	= $this->_oEpreuveManager->getProgrammationSatementByIdEpreuve($this->_idEpreuve);

			// Récupération de l'identifiant du formulaire rattaché à l'épreuve
			$nIdFormulaire	= $this->_oFormulaireManager->getIdFormulaireFromIdEpreuve($this->_idEpreuve);

			// Chargement du formulaire
			$this->chargerAction($nIdFormulaire);

			// Enregistrement de l'identifiant de l'épreuve en session
			$this->sendDataToSession($this->_idEpreuve, self::ID_EPREUVE);

			// Enregistrement de l'identifiant de l'épreuve en session
			$this->sendDataToSession($this->_idFormulaire, self::ID_FORMULAIRE);

			// Enregistrement de l'état de programmation en session
			$this->sendDataToSession($nStatutEpreuve, self::STATUT_PROGRAMMATION);

			// Redirection avec l'action
			$this->redirect($this->_controller . '/controle');
		} elseif (!empty($this->_aForm['formulaire_id'])) {
			// Rendu de la vue de modification de l'épreuve
			$this->render("controle");
		} else {
			// Recherche de la liste des épreuves selon l'identifiant de l'utilisateur connecté
			$aListeEpreuve	= $this->_oEpreuveManager->findAllEpreuvesModifiablesByIdCandidat($this->_oAuth->getIdUtilisateur());

			// Envoi de la liste à la vue
			$this->addToData('liste_epreuve', $aListeEpreuve);
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée lors d'un contrôle.
	 *
	 * @li	Contrôle si le candidat est autorisé à passer l'épreuve.
	 * @li	Contrôle si la programmation de l'épreuve est valide selon `datetime_epreuve` et `duree_epreuve`.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function controleAction() {
		// Fonctionnalité réalisée si l'identifiant du contrôle n'existe pas encore
		if (!empty($this->_idEpreuve) && empty($this->_idControle) || empty($this->_aForm['id_controle'])) {
			// Récupération de l'identifiant du contrôle en cours
			$this->_idControle = $this->_oEpreuveManager->initControleByCandidatEpreuve($this->_oAuth->getIdUtilisateur(), $this->_idEpreuve);

			// Chargement du formulaire avec la récupération des données du candidat en base de données
			$this->_aForm = $this->_oEpreuveManager->chargerControle($this->_aForm, $this->_idControle);
		}

		// Fonctionnalité réalisée si l'identifiant du contrôle n'est pas valide
		if (empty($this->_idControle)) {
			// Redirection à la page d'erreur
			$this->redirect($this->_controller . "/error");
		} else {
			// Initialisation de l'identifiant du contrôle dans le formulaire
			$this->_aForm['controle_id']	= $this->_idControle;

			// Enregistrement de l'identifiant du contrôle en session
			$this->sendDataToSession($this->_idControle, self::ID_CONTROLE);
		}
	}

	/**
	 * @brief	Enregistrement temporaire du formulaire.
	 *
	 * @return	void
	 */
	public function temporaireAction() {
		// Enregistrement temporaire du formulaire
		$this->_aForm = $this->_oEpreuveManager->enregistrerControle($this->_aForm, $this->_idControle);

		// Retour au formulaire
		$this->render('controle');
	}

	/**
	 * @brief	Enregistrement définitif du formulaire.
	 *
	 * @return	void
	 */
	public function finalizeAction() {
		// Mise à jour définitive des informations du contrôle
		$this->_oEpreuveManager->finalizeControleById($this->_idControle);

		// Effacement du formulaire
		$this->resetAction(null);
	}

	/**
	 * @brief	Affichage d'une erreur.
	 *
	 * @return	void
	 */
	public function errorAction() {
		$this->render("error");
	}
}
