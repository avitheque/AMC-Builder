<?php
/**
 * @brief	Classe contrôleur de la gestion des référentiels de l'application.
 *
 * Étend la classe abstraite AbstractFormulaireController.
 * @see			{ROOT_PATH}/libraries/controller/AbstractFormulaireController.php
 *
 * @name		ReferentielController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ReferentielController extends AbstractFormulaireController {

	/**
	 * @brief	Table du référentiel sélectionnée
	 * @var		string
	 */
	private		$_table						= null;
	private		$_idTable					= null;

	/**
	 * @brief	Instance du modèle de gestion du référentiel de l'application.
	 * @var		ReferentielManager
	 */
	protected $_oReferentielManager		= null;

	/**
	 * @brief	Instance du modèle de gestion des stages et des candidats.
	 * @var		AdministrationManager
	 */
	protected $_oAdministrationManager		= null;

	/**
	 * @brief	Constructeur de la classe.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'REFERENTIEL', ReferentielInterface::$LIST_CHAMPS_FORM);

		// Initialisation de l'instance du référentiel
		$this->_oReferentielManager			= new ReferentielManager();

		// Initialisation de l'instance de l'administration
		$this->_oAdministrationManager		= new AdministrationManager();

		// Récupération de la table sélectionnée en session
		$this->_table						= $this->getDataFromSession('TABLE_NAME');
		$this->_idTable						= $this->getDataFromSession('ID_TABLE');

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {

			case "annuler":
				// Message de débuggage
				$this->debug("ANNULER");
				// Exécution de l'action
				$this->annulerAction();
				break;

			case "fermer":
				// Message de débuggage
				$this->debug("FERMER");
				// Exécution de l'action
				$this->fermerAction();
				break;

			case "enregistrer":
				// Message de débuggage
				$this->debug("ENREGISTRER");
				// Exécution de l'action
				$this->enregistrerAction();
				break;

			case "supprimer":
				// Message de débuggage
				$this->debug("SUPPRIMER");
				// Exécution de l'action
				$this->supprimerAction();
				break;

			default:
				// Message de débuggage
				$this->debug("DÉFAUT");
				break;

		}

	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {
		// Construction de la liste des référentiels
		$this->addToData('liste_referentiels',	$this->_oAdministrationManager->findAllReferentiels());
	}

	/**
	 * @brief	Fermeture du référentiel.
	 *
	 * @li	Retour à la liste du référentiel.
	 *
	 * @return	void
	 */
	public function fermerAction() {
		// Effacement de l'éventuel identifiant de référentiel en session	(exploité dans la consultation)
		$this->resetDataIntoSession('ID_TABLE');

		// Redirection afin d'effacer les éléments présents en GET
		$this->redirect("referentiel");
	}

	/**
	 * @brief	Chargement du référentiel.
	 *
	 * @li	Méthode exploitée à la fois pour l'ajout ou l'édition d'un référentiel.
	 *
	 * @param	string		$sTable		: nom du référentiel.
	 * @param	integer		$nIdTable	: identifiant du référentiel.
	 * @return	void
	 */
	public function chargerAction($sTable = null, $nIdTable = null) {
		// Message de débuggage
		if (empty($sTable)) {
			// Chargement à partir des données stockées à la suite d'une redirection
			$this->debug(__CLASS__ . ".chargerAction($this->_table, $this->_idTable)");

			// Chargement de la totalité du référentiel sélectionné
			$this->addToData('liste_referentiel',	$this->_oReferentielManager->findListeReferentiel($this->_table));
		} else {
			// Chargement à partir des données passées en paramètre
			$this->debug(__CLASS__ . ".chargerAction($sTable, $nIdTable)");
		}

		// Fonctionnalité réalisée si la table est liée à un parent
		if (array_key_exists($this->_table, ReferentielManager::$REF_TABLE_PARENT)) {
			// Message de débuggage
			$this->debug("ReferentielManager->findListeParents($this->_table)");

			// Récupération de la liste du référentiel parent
			$aLiteParent = $this->_oReferentielManager->findListeParent($this->_table);
			$this->addToData('liste_parent', $aLiteParent);
		}

		// Fonctionnalité réalisée si les données du formulaire ne sont pas encore chargées
		if (!empty($sTable) && empty($nIdTable)) {
			// Chargement de la totalité du référentiel sélectionné
			$this->addToData('liste_referentiel',	$this->_oReferentielManager->findListeReferentiel($sTable));

			// Enregistrement de la table du référentiel en session
			$this->sendDataToSession($sTable,	'TABLE_NAME');
			$this->resetDataIntoSession('ID_TABLE');
			$this->_table	= $sTable;
			$this->_idTable	= null;

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect('referentiel/editer');
		} elseif (!empty($sTable) && !empty($nIdTable)) {
			// Actualisation des données du formulaire
			$this->resetFormulaire(
				// Initialisation du formulaire avec les données en base du référentiel selon son identifiant
				$this->_oReferentielManager->charger($sTable, $nIdTable)
			);

			// Enregistrement de l'identifiant du référentiel en session
			$this->sendDataToSession($sTable,	'TABLE_NAME');
			$this->sendDataToSession($nIdTable,	'ID_TABLE');
			$this->_table	= $sTable;
			$this->_idTable	= $nIdTable;

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect('referentiel/editer');
		}

		// Transfert des données à la vue
		$this->addToData('TABLE_NAME',	$this->_table);
		$this->addToData('TABLE_ID',	$this->_idTable);
	}

	/**
	 * @brief	Réinitialisation du formulaire afin de retourner en mode AJOUT
	 *
	 * @li	Surchage de la méthode du contrôleur parent
	 * @see		AbstractFormulaireController::unsetFormulaire()
	 * @return	void
	 */
	public function unsetFormulaire() {
		// Suppression du formulaire courant
		parent::unsetFormulaire();

		// Suppression de l'éventuel identifiant de référentiel en session	(exploité dans la consultation)
		$this->resetDataIntoSession('TABLE_ID');
		$this->_idTable	= null;
	}

	/**
	 * @brief	Ajout d'un référentiel.
	 *
	 * @li	Enregistrement d'un nouveau référentiel.
	 * @li	Si la variable d'instance de la table n'est pas initialisée, alors réinitialisation du contrôleur.
	 *
	 * @return	void
	 */
	public function ajouterAction() {
		// Fonctionnalité réalisée si la table n'est pas définie
		if (empty($this->_table)) {
			// Réinitialisation du contrôleur
			$this->resetAction(null);
		} elseif (!empty($this->_idTable) || $this->issetFormulaire('referentiel_id')) {
			// Suppression du formulaire courant
			$this->unsetFormulaire();
		}

		// Initialisation du formulaire
		$this->chargerAction();

		// Changement de vue
		$this->render('editer');
	}

	/**
	 * @brief	Annuler la modification du référentiel.
	 *
	 * @li	Retour à l'ajout d'un référentiel.
	 *
	 * @return	void
	 */
	public function annulerAction() {
		// Suppression du formulaire courant afin de retourner en AJOUT
		$this->unsetFormulaire();

		// Enregistrement de la table du référentiel en session
		$this->sendDataToSession($this->_table,	'TABLE_NAME');
		$this->resetDataIntoSession('ID_TABLE');
		$this->_table	= $this->_table;
		$this->_idTable	= null;

		// Redirection afin d'effacer les éléments présents en GET
		$this->redirect("referentiel/ajouter");
	}

	/**
	 * @brief	Consultation du référentiel.
	 *
	 * @li	Affichage de la liste complète du référentiel sélectionné.
	 *
	 * @return	void
	 */
	public function consulterAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".consulterAction()");

		// Récupération de l'identifiant du référentiel passé en GET
		$sTable		= $this->getParam('table');

		// Chargement des informations du référentiel sélectionné
		if (!empty($sTable)) {
			// Chargement de la totalité du référentiel sélectionné
			$this->addToData('liste_referentiel',	$this->_oReferentielManager->findListeReferentiel($sTable));

			// Enregistrement de la table du référentiel en session
			$this->sendDataToSession($sTable,	'TABLE_NAME');
			$this->resetDataIntoSession('ID_TABLE');
			$this->_table	= $sTable;
			$this->_idTable	= null;

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect("referentiel/consulter");
		}

		// Transfert des données à la vue
		$this->addToData('TABLE_NAME',	$this->_table);
		$this->addToData('TABLE_ID',	null);

		// Chargement de la totalité du référentiel sélectionné
		$this->addToData('liste_referentiel',	$this->_oReferentielManager->findListeReferentiel($this->_table));
	}

	/**
	 * @brief	Édition du référentiel.
	 *
	 * @li	Affichage du référentiel sélectionné par son identifiant.
	 *
	 * @return	void
	 */
	public function editerAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".editerAction()");
		$this->debug("TABLE_NAME = " . $this->_table);
		$this->debug("ID_TABLE = " .  $this->_idTable);

		// Récupération de l'identifiant du référentiel passé en GET
		$sTable		= $this->getParam('table');
		$nIdTable	= $this->getParam('id');
		$this->chargerAction($sTable, $nIdTable);
	}

	/**
	 * @brief	Enregistrement du référentiel.
	 * @return	void
	 */
	public function enregistrerAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".enregistrerAction()");
		$this->debug("TABLE_NAME = " . $this->_table);
		$this->debug("ID_TABLE = " .  $this->_idTable);

		// Initialisation de la variable d'enregistrement
		$aEnregistrement	= array();

		try {
			// Enregistrement du formulaire
			$aEnregistrement = $this->_oReferentielManager->enregistrer($this->_table, $this->getFormulaire());
		} catch (ApplicationException $e) {
			ViewRender::setMessageError($e->getMessage());
		}

		// Fonctionnalité réalisée si l'enregistrement s'est réalisé correctement
		if (DataHelper::isValidArray($aEnregistrement)) {
			// Suppression du formulaire courant afin de retourner en AJOUT
			$this->unsetFormulaire();
		}

		// Changement de vue
		$this->render('editer');
	}

	/**
	 * @brief	Suppression du référentiel.
	 * @return	void
	 */
	public function supprimerAction() {
		// Message de débuggage
		$this->debug(__CLASS__ . ".supprimerAction()");
		$this->debug("TABLE_NAME = " . $this->_table);
		$this->debug("ID_TABLE = " .  $this->_idTable);

		try {
			// Suppression du référentiel
			$this->_oReferentielManager->deleteReferentiel($this->_table, $this->_idTable);

			// Suppression du formulaire courant afin de retourner en AJOUT
			$this->unsetFormulaire();
		} catch (ApplicationException $e) {
			// Impossible de supprimer un référentiel lié à une autre table
			ViewRender::setMessageError("Un référentiel lié ne peut être supprimé...");
		}

		// Changement de vue
		$this->render('editer');
	}
}
