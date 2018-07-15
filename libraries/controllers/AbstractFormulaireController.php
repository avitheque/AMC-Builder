<?php
/**
 * @brief	Classe contrôleur abstraite des formulaires HTML.
 *
 * Cette classe abstraite permet de gérer les données transitées via les formulaires et de stocker leurs données en session.
 *
 * @li La variable de classe $LIST_CHAMPS_FORM de l'interface permet de lister l'ensemble des noms de champs du formulaire à exploiter.
 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
 *
 * @li Lors de la construction de la classe, les champs sont récupérés depuis la session s'ils sont présents, sinon ceux du formulaire HTML.
 *
 * @li La méthode @a finaleAction de la classe permet de stocker à nouveau le formulaire en session à la fin du traitement.
 *
 * Étend la classe abstraite AbstractAuthenticateController.
 * @see			{ROOT_PATH}/application/controllers/AbstractAuthenticateController.php
 *
 * @name		AbstractFormulaireController
 * @category	Controllers
 * @package		Classes
 * @subpackage	Libraries
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 136 $
 * @since		$LastChangedDate: 2018-07-14 17:20:16 +0200 (Sat, 14 Jul 2018) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class AbstractFormulaireController extends AbstractAuthenticateController {

	const		ACTION_ANNULER				= "annuler";
	const		ACTION_ENREGISTRER			= "enregistrer";
	const		ACTION_MODIFIER 			= "modifier";
	const		ACTION_SUPPRIMER 			= "supprimer";

	/**
	 * @brief	Données du formulaire sous forme de tableau.
	 * @var		array
	 */
	protected	$_aForm						= array();

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * Initialisation du tableau des données du formulaire HTML.
	 *
	 * @li Protection de l'application contre l'injection de données via les méthodes POST et GET.
	 *
	 * @param	string	$sNameSpace			: Nom du contrôleur appelé.
	 * @param	string	$sSessionNameSpace	: Nom de session permettant de stocker le formulaire, par défaut le nom de la classe.
	 * @param	array	$aListeChamps		: (optionnel) Liste des champs du formulaire HTML à traiter, les autres champs éventuels ne sont pas exploités.
	 */
	public function __construct($sNameSpace, $sSessionNameSpace = __CLASS__, $aListeChamps = array()) {
		parent::__construct($sNameSpace);
		// Initialisation du nom de session du formulaire
		$this->_sessionNameSpace = $sSessionNameSpace;

		// Initialisation de paramètres du formulaire
		$this->_aForm = $this->initFormulaire($aListeChamps, $this->_sessionNameSpace);

		// Enregistrement du NameSpace de la SESSION
		$this->addToData('SESSION_NAMESPACE', $sSessionNameSpace);

		// Fonctionnalité réalisée si le paramètre d'action fait appel à la réinitialisation
		if ($this->_action == 'reset' || $this->_option == 'reset') {
			$this->resetAction($this->_controller . "/" . $this->_action);
		}
	}

	/**
	 * @brief	Réinitialisation des variables du formulaire.
	 *
	 * @param	array	$aData				: tableau des données de réinitialisation du formulaire.
	 * @return	void
	 */
	protected function resetFormulaire($aData = array()) {
		// Purge du formulaire
		$this->_aForm = $aData;
		// Mise à jour des données en session
		$this->sendDataToSession($aData, $this->_sessionNameSpace);
	}

	/**
	 * @brief	Suppression des variables du formulaire.
	 *
	 * @return	void
	 */
	protected function unsetFormulaire() {
		// Purge du formulaire
		$this->resetFormulaire(null);
	}

	/**
	 * @brief	Chargement des variables du formulaire à partir du formulaire.
	 *
	 * @li Le formulaire est stocké dans la variable $_SESSION[$this->_sessionNameSpace].
	 *
	 * @param	array	$aListeChamps		: Liste des champs du formulaire.
	 * @param	string	$sSessionNameSpace	: Nom de session permettant de stocker le formulaire.
	 * @return	array tableau de configuration du formulaire.
	 */
	protected function initFormulaire($aListeChamps = array()) {
		// Initialisation du formulaire avec les données en session
		return $this->addFormToSession($aListeChamps, $this->_sessionNameSpace);
	}

	/**
	 * @brief	Récupération des variables du formulaire.
	 *
	 * @li	Possibilité de récupérer la valeur d'un champ par son index.
	 *
	 * @param	string	$sIndex				: Champs du formulaire à récupérer.
	 * @param	integer	$iType				: (optionnel) Constante de typage de variable
	 * @param	mixed	$xDefault			: (optionnel) Valeur de l'élément par défaut
	 * @param	bool	$bForceEmpty		: (optionnel) Force la valeur par défaut si le contenu est vide : "", '', 0, NULL
	 * @return	mixed|array, élément du formulaire ou tableau de configuration du formulaire.
	 */
	public function getFormulaire($sIndex = null, $iType = DataHelper::DATA_TYPE_ANY, $xDefault = null, $bForceEmpty = false) {
		if (!is_null($sIndex)) {
			return trim(DataHelper::get($this->_aForm, $sIndex, $iType, $xDefault, $bForceEmpty));
		} else {
			return $this->_aForm;
		}
	}

	/**
	 * @brief	Initialisation des variables du formulaire.
	 *
	 * @li	Possibilité de récupérer la valeur d'un champ par son index.
	 *
	 * @param	string	$sIndex				: Champ du formulaire à initialiser.
	 * @param	mixed	$xValue				: Valeur du champ à initialiser.
	 * @return	void.
	 */
	public function setFormulaire($sIndex, $xValue = null) {
		if (is_null($xValue)) {
			// Suppression de l'entrée
			unset($this->_aForm[$sIndex]);
		} else {
			// Stockage des données
			$this->_aForm[$sIndex]	= $xValue;
		}

		// Mise à jour des données en session
		$this->sendDataToSession($this->_aForm, $this->_sessionNameSpace);
	}

	/**
	 * @brief	Vérifie si le formulaire est renseigné.
	 *
	 * @return	boolean
	 */
	public function issetFormulaire() {
		// Renvoi du résultat
		return DataHelper::isValidArray($this->_aForm);
	}

	/**
	 * @brief	Vérifie si une entrée du formulaire est renseignée.
	 *
	 * @return	boolean
	 */
	public function isEmptyFormulaire($sIndex) {
		// Récupération de la valeur de l'index
		$xData = $this->getFormulaire($sIndex);
		// Renvoi du résultat
		return empty($xData);
	}

	/**
	 * @brief	Réinitialisation les données du formulaire.
	 * 
	 * Purge toutes les données présentes dans le(s) formulaire(s).
	 * @see		AbstractAuthenticateController::resetAction()
	 * @return	void
	 */
	public function resetAction() {
		// Purge des données du formulaire
		$this->resetFormulaire();
		// Action du contrôleur parent
		parent::resetAction($this->_controller);
	}

	/**
	 * @brief	Action finale du contrôleur.
	 *
	 * @return	void
	 */
	public function finalAction() {
		// Mise à jour des données en session
		$this->sendDataToSession($this->_aForm, $this->_sessionNameSpace);
	}

}
