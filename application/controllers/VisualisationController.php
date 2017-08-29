<?php
/**
 * @brief	Classe contrôleur de visualisation de la correction d'un questionnalire QCM.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		VisualisationController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 78 $
 * @since		$LastChangedDate: 2017-08-29 18:14:10 +0200 (Tue, 29 Aug 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class VisualisationController extends AbstractFormulaireQCMController {

	/**
	 * @var		integer
	 */
	protected	$_idCorrection		= null;

	/**
	 * @var		FormulaireManager
	 */
	protected	$_oFormulaireManager;

	/**
	 * @brief	Constructeur de la classe.
	 *
   	 * @overload	AbstractFormulaireQCMController::construct()
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'CORRECTION');

		// Récupération de l'identifiant du formulaire en session
		$this->_idCorrection 		= $this->getDataFromSession('ID_CORRECTION');
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function indexAction() {
		// Récupération de l'identifiant du formulaire
		$nIdFormulaire = $this->getParam("id_formulaire");

		// Fonctionnalité réalisée si le formulaire est valide
		if ($nIdFormulaire) {
			// Stockage de l'identifiant de l'édition en session
			$this->sendDataToSession($nIdFormulaire, "ID_CORRECTION");

			// Redirection afin d'effacer les éléments présents en GET
			$this->redirect('visualisation');
		} elseif ($this->_idCorrection) {
			// Génération du formulaire sous forme PDF
			$oFormulaire = new ExportCorrectionManager($this->_oFormulaireManager->charger($this->_idCorrection));
			$oFormulaire->render();

			// Désactivation de la vue
			$this->render(FW_VIEW_VOID);
		} else {
			// Retour à la page d'accueil
			$this->redirect("index");
		}
	}

}
