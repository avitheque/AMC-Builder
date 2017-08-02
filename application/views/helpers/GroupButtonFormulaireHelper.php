<?php
/**
 * @brief	Helper de création des boutons du formulaire QCM
 *
 * Vue de contenu permettant de créer les boutons du formulaire QCM.
 *
 * @name		GroupButtonFormulaireHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 74 $
 * @since		$LastChangedDate: 2017-07-30 01:56:01 +0200 (Sun, 30 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class GroupButtonFormulaireHelper {

	/**
	 * Verrouillage des boutons de modification.
	 * @var		boolean
	 */
	private		$_readonly			= false;

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage	= null;

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_html				= "";

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @param	boolean	$bTerminer		: (optionnel) mise à valisation du QCM.
	 * @param	boolean	$bExporter		: (optionnel) exportation du QCM.
	 *
	 * @return	void
	 */
	public function __construct($bTerminer = false, $bExporter = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage	= InstanceStorage::getInstance();

		// Nom de session du QCM
		$sSessionNameSpace				= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Récupération de l'instance du singleton SessionManager
		$oSessionManager				= SessionManager::getInstance($sSessionNameSpace);

		//#########################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#########################################################################################

		// Données du QCM
		$aFormulaireQCM				= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Identifiant du questionnaire
		$nIdFormulaire				= DataHelper::get($aFormulaireQCM, 'formulaire_id', DataHelper::DATA_TYPE_INT,	null);

		//#########################################################################################
		// CONSTRUCTION DES BOUTONS DU FORMULAIRE QCM
		//#########################################################################################

		// Protection du formulaire contre la modification si une épreuve est en cours
		$this->setReadonly($oSessionManager->getIndex('CONTROLE_EPREUVE_EXISTS'));

		// Zone de boutons du formulaire QCM
		$this->_buildGroupButton($nIdFormulaire, $bTerminer, $bExporter);
	}

	/**
	 * @brief	Verrouillage des boutons du formulaire.
	 *
	 * @param	boolean	$bBoolean		: TRUE active la protection contre la modification du formulaire.
	 * @return	void
	 */
	public function setReadonly($bBoolean = false) {
		$this->_readonly			= $bBoolean;
	}

	/**
	 * @brief	Zone de boutons du formulaire QCM.
	 *
	 * @param	integer	$nIdFormulaire	: Identifiant du formulaire QCM.
	 * @param	boolean	$bTerminer		: Fait apparaître le bouton [Terminer].
	 * @param	boolean	$bExporter		: Fait apparaître le bouton [Exporter].
	 * @return	void
	 */
	private function _buildGroupButton($nIdFormulaire = null, $bTerminer = false, $bExporter = false) {
		//#########################################################################################
		// CONSTRUCTION DU GROUPE DE BOUTONS RELATIF AU QUESTIONNAIRE QCM
		//#########################################################################################

		// Boutons par défaut d'un QCM non enregistré
		$sGauche = "<button type=\"submit\" class=\"red confirm left tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_EFFACER . "\" title=\"Recommencer un nouveau QCM\" role=\"touche_A\">Annuler</button>";
		$sMilieu = "";
		if ($this->_readonly) {
			// Ajout d'un message d'avertissement
			ViewRender::setMessageWarning("Droits limités !", "Aucune modification du formulaire n'est autorisée...");

			// Formulaire non modifiable
			$sDroite				= "<button type=\"submit\" class=\"disabled right tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_FERMER . "\" title=\"Enregistrer le QMC\" role=\"touche_S\" disabled>Sauvegarder</button>";
		} else {
			// Enregistrement possible
			$sDroite				= "<button type=\"submit\" class=\"green right tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_ENREGISTRER . "\" title=\"Enregistrer le QMC\" role=\"touche_S\">Sauvegarder</button>";
		}

		// Fonctionnalité réalisée si le QCM est déjà enregistré
		if ($nIdFormulaire) {
			$sGauche				= "<button type=\"submit\" class=\"red confirm left tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_FERMER . "\" title=\"Retour à la page précédente\" role=\"touche_F\">Fermer</button>";
		}

		// Fonctionnalité réalisée si le bouton [Exporter] est actif
		if ($bExporter) {
			$sMilieu				= "<button type=\"submit\" class=\"blue tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_EXPORTER . "\" title=\"Générer le code LaTeX du QCM\" role=\"touche_E\">Exporter</button>";
		} elseif (!$this->_readonly && ($nIdFormulaire || $bTerminer)) {
			// Fonctionnalité réalisée afin de proposer le formulaire à la validation
			$sMilieu				= "<button type=\"submit\" class=\"blue tooltip\" name=\"button\" value=\"" . AbstractFormulaireQCMController::ACTION_TERMINER . "\" title=\"Soumettre le QCM à validation\" role=\"touche_T\">Terminer</button>";
		}

		// Boucle de création de la liste des questions
		$this->_html				.= "<div class=\"group-button\">
											" . $sGauche . "
											" . $sMilieu . "
											" . $sDroite . "
										</div>";
	}

	/**
	 * @brief	Rendu final du contenu.
	 *
	 * @return	string
	 */
	public function render() {
		// Renvoi du code HTML
		return $this->_html;
	}
}
