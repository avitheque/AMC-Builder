<?php
/** @brief	Contrôleur abstrait de l'application.
 *
 * Classe contrôleur par défaut de l'application.
 * Les contrôleurs qui étendent directement cette classe n'ont pas besoin d'être déclarées dans les ressources ACL.
 *
 * @li	Exploitation du SINGLETON de la classe InstanceStorage afin de charger et de stocker les données accessibles.
 *
 * Le contrôleur abstrait ne peut pas être instancié directement mais permet d'écrire des classes de contrôle.
 * @code
 *	class LoginController extends AbstractApplicationController {
 *
 *		// Action par défaut du contrôleur
 *		public function indexAction() {
 *			// Méthode à implémenter si la vue est différente du nom de l'action `index.phtml`
 *			// afin de charger la vue `Nom-de-Vue-a-retourner.phtml`
 *			$this->render("Nom-de-Vue-a-retourner");	# NE PAS METTRE L'EXTENSION DU FICHIER ! #
 *		}
 *
 * 	}
 * @endcode
 *
 * Il possède des méthodes permettant d'accéder aux paramètres POST ou GET, ainsi qu'aux variables de sessions,
 * en les validant par une vérification de type ; ce qui empêche l'utilisation de valeurs dont le type est erroné.
 *
 * @name		AbstractApplicationController
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
abstract class AbstractApplicationController implements Interfaces_iAbstractApplicationController {

	protected	$oInstanceStorage			= null;
	protected	$oSessionMessenger			= null;						// Gestionnaire des messages transmis en session
	protected	$aMessages					= array();					// Messages transmis par le contrôleur
	protected	$aParamTypes				= array();					// Métadonnées des paramètres
	protected	$aParams					= array();					// Données transmises par l'URL et les formulaires
	protected	$aData						= array();					// Données transmises du contrôleur à la vue

	protected	$_controller				= FW_DEFAULTCONTROLLER;
	protected	$_subController				= null;
	protected	$_action					= FW_DEFAULTACTION;
	protected	$_subAction					= null;
	protected	$_option					= null;
	protected	$_view						= FW_DEFAULTVIEW;

	/** @brief	Constructeur.
	 *
	 * Constructeur généraliste instancié par un controleur spécialisé de l'application.
	 *
	 * @li		Les informations {CONTROLLER}[.{SUBCONTROLLER}]/{ACTION}.{SUBACTION}/{OPTION}
	 *
	 * @param	string	$sNameSpace			: Nom de la classe contrôleur appelée.
	 */
	public function __construct($sNameSpace = __CLASS__) {
		// Mise à jour de l'instance de `SessionMessenger`
		$this->oSessionMessenger			= SessionMessenger::getInstance();

		// Récupération des variables de l'instance en cours
		$this->mergeParams();

		// Récupération de l'instance InstanceStorage
		$this->oInstanceStorage				= InstanceStorage::getInstance();

		// Récupère les paramètres d'exécution en cours
		$aExecute = $this->oInstanceStorage->getParam('execute');

		// Initialisation de la variable du contrôleur
		$this->defineParam('controller',	DataHelper::DATA_TYPE_CLASSID);
		$this->_controller					= strtolower(str_replace("Controller", "", $sNameSpace));
		$this->aParams['controller']		= $this->_controller;

		// Initialisation de la variable du sous-contrôleur
		$this->defineParam('subController',	DataHelper::DATA_TYPE_CLASSID);
		$this->_subController				= DataHelper::get($aExecute, 'subController');
		$this->aParams['subController']		= $this->_subController;

		// Initialisation de la variable de l'action
		$this->defineParam('action',		DataHelper::DATA_TYPE_CLASSID);
		$this->_action						= DataHelper::get($aExecute, 'action');
		$this->aParams['action']			= $this->_action;

		// Initialisation de la variable de l'sous-action
		$this->defineParam('subAction',		DataHelper::DATA_TYPE_CLASSID);
		$this->_subAction					= DataHelper::get($aExecute, 'subAction');
		$this->aParams['subAction']			= $this->_subAction;

		// Initialisation de la variable de l'option
		$this->defineParam('option',		DataHelper::DATA_TYPE_CLASSID);
		$this->_option						= DataHelper::get($aExecute, 'option');
		$this->aParams['option']			= $this->_option;

		// Initialisation de la vue par défaut avec le nom de l'action
		$this->render(!empty($this->_action) ? $this->_action : FW_DEFAULTVIEW);

		// Initialisation des variables JAVASCRIPT injectées dans la vue
		$aScriptsList						= array(
			'var CONTROLLER="'	. $this->_controller	. '";',
			'var ACTION="'		. $this->_action		. '";',
			'var OPTION="'		. $this->_option		. '";',
			'var VIEW="'		. $this->_view			. '";'
		);

		// Compression des scripts avec JavaScriptPacker
		ViewRender::addToScripts($aScriptsList);
	}

	/** @brief	Initialisation du typage des paramètres.
	 *
	 * Cette procédure permet de stocker la liste des clés de variable et leur type associé.
	 *
	 * @param	string	$sParam			: Nom de la clé.
	 * @param	integer	$nType			: Code du type du paramètre (voir la classe DataHelper::DATA_TYPE_*).
	 * @return	void
	 */
	protected function defineParam($sParam, $nType) {
		$this->aParamTypes[$sParam] = $nType;
	}

	/** @brief	Initialisation de la vue.
	 *
	 * Cette procédure est invoquée afin de modifier la vue chargée par l'action du contrôleur.
	 *
	 * @param	string	$sView			: Nom de la vue (sans extension *.phtml)
	 * @return	void
	 */
	public function render($sView = FW_DEFAULTVIEW) {
		$this->_view = $sView;
	}

	/** @brief	Récupère le nom de la vue.
	 *
	 * Cette procédure est invoquée afin de récupérer la vue à charger par l'action du contrôleur.
	 *
	 * @return	string
	 */
	public function getViewRender() {
		return $this->_view;
	}

	/** @brief	Initialisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée avant chaque action.
	 *
	 * @return	void.
	 */
	public abstract function initAction();

	/** @brief	Initialisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur.
	 *
	 * @return	string view : valeur de la vue que l'on souhaite afficher.
	 */
	public abstract function indexAction();

	/** @brief	Action de réinitialisation du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur.
	 *
	 * @return	void.
	 */
	public abstract function resetAction();

	/** @brief	Finalisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée à la fin de chaque action.
	 *
	 * @return	void.
	 */
	public abstract function finalAction();

	/** @brief	Initialise le tableau de données d'instance.
	 *
	 * Cette procédure réinitialise la variable de session $this->aData.
	 * @return	void
	 */
	protected function resetDatas() {
		$this->aData = array();
	}

	/** @brief	Initialise le tableau de paramètres d'instance.
	 *
	 * Cette procédure réinitialise la variable de session $this->aParams.
	 * @return	void
	 */
	protected function resetParams() {
		$this->aParams = array();
	}

	/** @brief	Ajout de donnée a destination de la Vue.
	 *
	 * Cette procédure permet de stocker un objet à destination de la vue.
	 * Une clé (identifiant est associée à l'objet).
	 *
	 * @param	string	$sIndex			: Nom de l'étiquette.
	 * @param	mixed	$xValue			: Élément a insérer (n'importe quel type).
	 */
	protected function addToData($sIndex, $xValue = null) {
		// Fonctionnalit réalisée si l'élément est NULL
		if (is_null($xValue)) {
			// Suppression de l'entrée
			unset($this->aData[$sIndex]);
		} else {
			// Stockage des données
			$this->aData[$sIndex] = $xValue;
		}
	}

	/** @brief	Poste les variables POST et GET à destination de la vue.
	 *
	 * Cette procédure poste l'intégralité des variables POST, GET et SESSION à destination de la vue.
	 * Cela permet a un formulaire de retrouver facilement ce qui a été transmis en paramètre, ou en session.
	 */
	protected function transferToData() {
		// Fonctionnalité réalisée pour chaque paramètre
		foreach($this->aParams as $key => $value) {
			$this->addToData($key, $value);
		}
	}

	/** @brief	Tableau de stockage des données à destination de la vue.
	 *
	 * Renvoie le tableau contenant les objets de stockage a destination de la vue.
	 *
	 * @return	array : Tableau associatif.
	 */
	public function getData() {
		return $this->aData;
	}

	/** @brief	Stocke un message à destination de la vue.
	 *
	 * Stocke un message informatif.
	 *
	 * @param	string	$sType			: Type du message.
	 * @param	string	$sMessage		: Contenu du message.
	 */
	protected function storeMessage($sType = ViewRender::MESSAGE_INFO, $sMessage) {
		$this->aMessages[$sType][] = $sMessage;
	}

	/** @brief	Stocke un message d'erreur à destination de la vue.
	 *
	 * Stocke un message d'erreur.
	 *
	 * @param	string	$sError			: Contenu du message d'erreur.
	 */
	protected function storeError($sError) {
		$this->storeMessage(ViewRender::MESSAGE_ERROR, $sError);
	}

	/** @brief	Stocke un message de succès.
	 *
	 * Stocke un message de succès à destination de la vue.
	 *
	 * @param	string	$sSuccess		: Contenu du message de succès.
	 */
	protected function storeSuccess($sSuccess) {
		$this->storeMessage(ViewRender::MESSAGE_SUCCESS, $sSuccess);
	}

	/** @brief	Stocke un message d'avertissement.
	 *
	 * Stocke un message d'avertissement à destination de la vue.
	 *
	 * @param	string	$sWarning		: Contenu du message d'avertissement.
	 */
	protected function storeWarning($sWarning) {
		$this->storeMessage(ViewRender::MESSAGE_WARNING, $sWarning);
	}

	/** @brief	Tableau de stockage des messages.
	 *
	 * Cette fonction renvoie le tableau contenant les messages stockés par le contrôleur via la méthode storeMessage().
	 *
	 * @return	Tableau de chaînes.
	 */
	public function getMessages($sType = ViewRender::MESSAGE_INFO) {
		return array_key_exists($sType, $this->aMessages) ? $this->aMessages[$sType] : null;
	}

	/** @brief	Tableau de stockage des messages d'erreur.
	 *
	 * Cette fonction renvoie le tableau contenant les messages d'erreur, stockés par le contrôleur via la méthode storeError().
	 *
	 * @return	Tableau de chaînes.
	 */
	public function getErrors() {
		return $this->getMessages(ViewRender::MESSAGE_ERROR);
	}

	/** @brief	Tableau de stockage des messages de succès.
	 *
	 * Cette fonction renvoie le tableau contenant les messages de succès, stockés par le contrôleur via la méthode storeSuccess().
	 *
	 * @return	Tableau de chaînes.
	 */
	public function getSuccesses() {
		return $this->getMessages(ViewRender::MESSAGE_SUCCESS);
	}

	/** @brief	Tableau de stockage des messages d'avertissement.
	 *
	 * Cette fonction renvoie le tableau contenant les messages d'avertissement, stockés par le contrôleur via la méthode storeAlert().
	 *
	 * @return	Tableau de chaînes.
	 */
	public function getWarnings() {
		return $this->getMessages(ViewRender::MESSAGE_WARNING);
	}

	/** @brief	Nom du contrôleur.
	 *
	 * Cette fonction renvoie le nom du contrôleur actif.
	 *
	 * @return	string : Nom du contrôleur qui a été invoquée.
	 */
	public function getController() {
		return $this->_controller;
	}

	/** @brief	Nom de l'action.
	 *
	 * Cette fonction renvoie le nom de l'action du contrôleur.
	 *
	 * @return	string : Nom de l'action du contrôleur qui a été invoquée.
	 */
	public function getAction() {
		return $this->_action;
	}

	/** @brief	Fusion des paramètres GET, POST, et SESSION.
	 *
	 * Cette procédure effectue une copie des paramètre GET, POST et SESSION, et les fusionne dans un tableau pour les mettre à disposition du contrôleur.
	 *
	 * @li	Si des noms de paramètre entrent en conflit, ce sont les paramètres SESSION qui priment, sur POST,
	 * et POST prime sur GET.
	 */
	protected function mergeParams() {
		$this->aParams = array_merge(array_merge($_GET, $_POST, $_FILES), $_SESSION);
	}

	/** @brief	Test un paramètre.
	 *
	 * Cette fonction renvoie un bouléen selon la présence de la valeur d'un paramètre GET, POST ou SESSION.
	 * @code
	 * 	$this->issetParam('numero', DataHelper::DATA_TYPE_INT);
	 * @endcode
	 *
	 * @param	string	$sParam			: Nom du paramètre.
	 * @return	boolean
	 */
	protected function issetParam($sParam) {
		return isset($this->aParams[$sParam]);
	}

	/** @brief	Récupère tous les paramètres.
	 *
	 * Cette fonction renvoie toutes les valeurs GET, POST ou SESSION.
	 * @code
	 * 	$this->getParams();
	 * @endcode
	 *
	 * @return	array
	 */
	protected function getParams() {
		return $this->aParams;
	}

	/** @brief	Récupère un paramètre.
	 *
	 * Cette fonction renvoie la valeur d'un paramètre GET, POST ou SESSION.
	 * @code
	 * 	$this->getParam('numero', DataHelper::DATA_TYPE_INT);
	 * @endcode
	 *
	 * @param	string	$sParam			: Nom du paramètre.
	 * @param	integer	$iType			: Code du type du paramètre (voir la classe DataHelper::DATA_TYPE_*).
	 * @return	mixed
	 */
	protected function getParam($sParam, $iType = DataHelper::DATA_TYPE_NONE) {
		if ($iType != DataHelper::DATA_TYPE_NONE) {
			$this->defineParam($sParam, $iType);
		}

		// Typage de la valeur
		if ($this->issetParam($sParam)) {
			$xValue	= DataHelper::get($this->aParams,		$sParam,	$iType,		null);
			$iType	= DataHelper::get($this->aParamTypes,	$sParam,	null,		DataHelper::DATA_TYPE_NONE);
			if (DataHelper::checkValue($xValue, $iType)) {
				// Élimination des caractères ["] en trop
				return DataHelper::stripApostrophes($xValue);
			} else {
				throw new ApplicationException('EParamTypeMismatch', $sParam, INDEX);
			}
		}

		return null;
	}

	/** @brief	Récupère un groupe de paramètres similaires.
	 *
	 * Cette fonction renvoie la valeur des paramètre GET, POST ou SESSION resemblant à l'expression passée en argument.
	 * @code
	 * 	$this->getParamLike('candidat_*', DataHelper::DATA_TYPE_STR);
	 * @endcode
	 *
	 * @param	string	$sExp			: Expression du paramètre.
	 * @param	integer	$nType			: Code du type du paramètre (voir la classe DataHelper::DATA_TYPE_*).
	 * @return	array
	 */
	protected function getParamsLike($sRegExp = ".*", $nType = DataHelper::DATA_TYPE_NONE) {
		$aRes		= array();
		$sPattern	= sprintf("@%s@", $sRegExp);
		foreach ($this->aParams as $key => $value) {
			if (preg_match($sPattern, $key)) {
				$aRes[$key] = $this->getParam($key, $nType);
			}
		}
		return $aRes;
	}

	/** @brief	Récupère un tableau de paramètres commençant par la même chaîne.
	 *
	 * Cette fonction renvoie la valeur des paramètre GET, POST ou SESSION dont les clés débutent de la même manière.
	 * @code
	 * 	$this->getParamArray('candidat_', DataHelper::DATA_TYPE_STR);
	 * @endcode
	 *
	 * @param	string	$sParamStart	: Chaîne de caractères de début.
	 * @param	integer	$nType			: Code du type du paramètre (voir la classe DataHelper::DATA_TYPE_*).
	 * @return	array
	 */
	protected function getParamArray($sParamStart, $nType = DataHelper::DATA_TYPE_NONE) {
		$aRes = array();
		foreach ($this->aParams as $key => $value) {
			if (substr($key, 0, strlen($sParamStart)) == $sParamStart) {
				$aRes[$key] = $this->getParam($key, $nType);
			}
		}
		return $aRes;
	}
}
