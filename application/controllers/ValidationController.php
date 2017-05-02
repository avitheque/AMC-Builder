<?php
/**
 * @brief	Classe contrôleur de la validation d'un questionnalire QCM.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controller/AbstractFormulaireQCMController.php
 *
 * @name		ValidationController
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
class ValidationController extends AbstractFormulaireQCMController {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_VALIDATION');

		// Effacement de l'éventuel fichier d'importation en session	(exploité dans le contrôleur [Importation])
		$this->resetDataIntoSession('FILE_NAME');

		// Fonctionnalité réalisée selon l'action du bouton
		switch (strtolower($this->getParam('button'))) {

			case "supprimer":
				// Message de débuggage
				$this->debug("SUPPRIMER");
				// Exécution de l'action
				$this->supprimerAction();
				break;

			case "valider":
				// Message de débuggage
				$this->debug("VALIDER");
				// Exécution de l'action
				$this->validerAction();
				break;

			case "generer":
				// Message de débuggage
				$this->debug("ENREGISTRER");
				// Exécution de l'action
				$this->genererAction();
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
		if ($this->_idFormulaire) {
			$this->chargerAction($this->_idFormulaire);
		} else {
			// Recherche de la liste des formulaires en attente de validation
			$aListeValidation = $this->_oFormulaireManager->findAllFormulairesForValidation();

			// Envoi de la liste à la vue
			$this->addToData('liste_validation', $aListeValidation);
		}
	}

	/**
	 * @brief	Suppression d'un formulaire.
	 */
	public function supprimerAction() {
		// Validation du formulaire
		$bValide = false;
		if (!empty($this->_idFormulaire)) {
			$bValide	= $this->_oFormulaireManager->supprimerFormulaireById($this->_idFormulaire);
		}

		// Effacement de l'identifiant courant du formulaire
		parent::resetAction(null);
	}

	/**
	 * @brief	Validation d'un formulaire.
	 */
	public function validerAction() {
		// Validation du formulaire
		$bValide = false;
		if (!empty($this->_idFormulaire)) {
			$bValide	= $this->_oFormulaireManager->valider($this->_idFormulaire);
		}

		// Effacement de l'identifiant courant du formulaire
		parent::resetAction(null);
	}

	/**
	 * @brief	Générer le formulaire au format LaTeX.
	 *
	 * @return	void
	 */
	public function genererAction() {
		// Génération du formulaire
		$oFormulaire = new LatexFormManager($this->getFormulaire());
		$oFormulaire->render();

		// Désactivation de la vue
		$this->render(FW_VIEW_VOID);
	}

}
