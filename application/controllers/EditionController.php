<?php
/**
 * @brief	Classe contrôleur de l'édition d'un questionnaire QCM.
 *
 * @li	Une fois l'importation enregistrée, la redirection est effectuée sur l'édition.
 * C'est pourquoi la session de l'importation est [QCM_EDITION] et l'information [FILE_NAME] doit être purgée.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		EditionController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 67 $
 * @since		$LastChangedDate: 2017-07-19 00:09:56 +0200 (Wed, 19 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EditionController extends AbstractFormulaireQCMController {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_EDITION');

		// Effacement de l'éventuel fichier d'importation en session	(exploité dans le contrôleur [Importation])
		$this->resetDataIntoSession('FILE_NAME');
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function indexAction() {
		// Chargement du formulaire si l'identifiant est présent en session
		if ($this->_idFormulaire) {
			$this->chargerAction($this->_idFormulaire);
		} else {
			// Recherche de la liste des formulaires
			$aListeEdition = $this->_oFormulaireManager->findAllFormulaires();

			// Envoi de la liste à la vue
			$this->addToData('liste_edition', $aListeEdition);
		}
	}

}
