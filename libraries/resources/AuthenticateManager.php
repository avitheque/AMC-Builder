<?php
/**
 * @brief	Authentification d'un utilisateur dans l'application.
 * Classe de création des ACLs via un fichier de configuration INI.
 *
 * Cette classe SINGLETON permet de gérer l'authentification des utilisateurs dans l'application.
 *
 * @name		AuthenticateManager
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 140 $
 * @since		$LastChangedDate: 2018-07-14 19:29:36 +0200 (Sat, 14 Jul 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class AuthenticateManager {

	const		FIELD_DISPLAY_NAME				= 'display_name';								// Champ du libellé de l'utilisateur
	const		FIELD_GRADE_ID					= 'id_grade';									// Champ d'identification du grade
	const		FIELD_GROUPE_DROITE				= 'borne_droite';								// Champ de la BORNE DROITE du groupe
	const		FIELD_GROUPE_GAUCHE				= 'borne_gauche';								// Champ de la BORNE GAUCHE du groupe
	const		FIELD_GROUPE_ID					= 'id_groupe';									// Champ d'identification du groupe
	const		FIELD_GROUPE_LABEL				= 'libelle_groupe';								// Champ du libellé du groupe
	const		FIELD_PROFIL_ID					= 'id_profil';									// Champ de l'identifiant du profil
	const		FIELD_PROFIL_LABEL				= 'libelle_profil';								// Champ du libellé du profil
	const		FIELD_PROFIL_ROLE				= 'role_profil';								// Champ du rôle du profil
	const		FIELD_USER_ID					= 'id_utilisateur';								// Champ d'identification de l'utilisateur
	const		FIELD_USER_LOGIN				= 'login_utilisateur';							// Champ du login de l'utilisateur
	const		FIELD_USER_STATUS				= 'modifiable_utilisateur';						// Champ d'autorisation de modification

	const		DEFAULT_BORNE_DROITE			= 2;
	const		DEFAULT_BORNE_GAUCHE			= 1;
	const		DEFAULT_DISPLAY_NAME			= Constantes::LOGIN_GUEST;
	const		DEFAULT_ID_GRADE				= 0;
	const		DEFAULT_ID_GROUPE				= 0;
	const		DEFAULT_ID_PROFIL				= AclManager::ID_PROFIL_GUEST;
	const		DEFAULT_ID_UTILISATEUR			= 0;
	const		DEFAULT_LIBELLE_GROUPE			= null;
	const		DEFAULT_LIBELLE_PROFIL			= AclManager::LABEL_PROFIL_GUEST;
	const		DEFAULT_LOGIN					= Constantes::LOGIN_GUEST;
	const		DEFAULT_ROLE					= AclManager::ROLE_GUEST;
	const		DEFAULT_USER_STATUS				= false;

	/**
	 * @brief	Paramètres d'authentification par défaut.
	 *
	 * @var		array
	 */
	static public $DEFAULT_PARAMS 				= array(
		// INFORMATIONS DE L'UTILISATEUR
		self::FIELD_USER_STATUS		=> self::DEFAULT_USER_STATUS,								// BOOLEAN	Statut d'accès de l'utilisateur
		self::FIELD_USER_ID			=> self::DEFAULT_ID_UTILISATEUR,							// INTEGER	Identifiant du compte utilisateur
		self::FIELD_USER_LOGIN		=> self::DEFAULT_LOGIN,										// STRING	Login de l'utilisateur
		self::FIELD_DISPLAY_NAME	=> self::DEFAULT_DISPLAY_NAME,								// STRING	Libellé de présentation de l'utilisateur
		self::FIELD_GRADE_ID		=> self::DEFAULT_ID_GRADE,									// INTEGER	Identifiant du grade de l'utilisateur
		// GESTION DU GROUPE
		self::FIELD_GROUPE_DROITE	=> self::DEFAULT_BORNE_DROITE,								// INTEGER	Borne DROITE du groupe de l'utilisateur
		self::FIELD_GROUPE_GAUCHE	=> self::DEFAULT_BORNE_GAUCHE,								// INTEGER	Borne GAUCHE du groupe de l'utilisateur
		self::FIELD_GROUPE_ID		=> self::DEFAULT_ID_GROUPE,									// INTEGER	Identifiant du groupe de l'utilisateur
		self::FIELD_GROUPE_LABEL	=> self::DEFAULT_LIBELLE_GROUPE,							// STRING	Libellé du groupe de l'utilisateur
		// GESTION DU PROFIL
		self::FIELD_PROFIL_ID		=> self::DEFAULT_ID_PROFIL,									// INTEGER	Identifiant du profil de l'utilisateur
		self::FIELD_PROFIL_LABEL	=> self::DEFAULT_LIBELLE_PROFIL,							// STRING	Libelle du profil de l'utilisateur
		self::FIELD_PROFIL_ROLE		=> self::DEFAULT_ROLE										// STRING	Rôle de l'utilisateur
	);

	/**
	 * @brief	Paramètres de l'instance d'authentification.
	 * @var		array
	 */
	protected	$_params						= array();

	/**
	 * @brief	Instance du SINGLETON de l'authentification.
	 * @var		AuthenticateManager
	 */
	private static $oInstance					= null;

	/**
	 * @brief	Chargement des droits ACLs.
	 *
	 * Constructeur de la classe qui permet la récupération des droits
	 * depuis le fichier de configuration généralement trouvé dans "/application/configs/acl.ini".
	 *
	 * @param	string		$file		: chemin du fichier acl.ini
	 * @return	void
	 */
	protected function __construct() {
		$this->initUser();
	}

	/**
	 * @brief	Initialisation des paramètres d'authentification.
	 *
	 * Méthode permettant de stocker l'authentification de l'utilisateur dans l'application.
	 *
	 * @li Exploite le tableau des informations de l'utilisateur en base de données.
	 * @code
	 * 		// TABLE `profil`
	 * 		integer	$aUtilisateur['id_profil']				: identifiant du profil
	 * 		string	$aUtilisateur['libelle_profil']			: libellé du profil
	 * 		string	$aUtilisateur['role_profil']			: rôle de l'utilisateur
	 * 		// TABLE `groupe`
	 * 		integer	$aUtilisateur['id_groupe']				: identifiant du groupe
	 * 		integer	$aUtilisateur['libelle_groupe']			: libellé du groupe
	 * 		integer	$aUtilisateur['borne_droite']			: borne droite du groupe
	 * 		integer	$aUtilisateur['borne_gauche']			: borne gauche du groupe
	 * 		// TABLE `grade`
	 * 		integer	$aUtilisateur['id_grade']				: identifiant du grade
	 * 		string	$aUtilisateur['libelle_court_grade']	: libellé court du grade
	 * 		string	$aUtilisateur['libelle_grade']			: libellé du grade
	 * 		// TABLE `utilisateur`
	 * 		string	$aUtilisateur['display_name']			: libellé de présentation de l'utilisateur [GRD NOM Prénom]
	 * 		integer	$aUtilisateur['id_utilisateur']			: identifiant de l'utilisateur
	 * 		string	$aUtilisateur['login_utilisateur']		: libellé du compte utilisateur
	 * 		string	$aUtilisateur['nom_utilisateur']		: nom de l'utilisateur
	 * 		string	$aUtilisateur['prenom_utilisateur']		: prénom de l'utilisateur
	 * @endcode
	 *
	 * @li	Possibilité de s'authentifier par défaut à un autre compte en MODE_DEBUG et MODE_SUBSTITUTE_USER actif.
	 */
	protected function initUser() {
		// Fonctionnalité réalisée en MODE_DEBUG avec la possibilité de se subtiliser à un utilisateur prédéfini
		if (defined('MODE_DEBUG') && (bool) MODE_DEBUG && defined('MODE_SUBSTITUTE_USER') && (bool) MODE_SUBSTITUTE_USER) {
			// Lecture de la configuration
			$aConfig	= ParseIniFile::parse(APP_ENV);

			// Récupération des paramètres d'authentification
			$aAUTH		= DataHelper::get($aConfig, 'auth', null, array());

			// Récupération de la valeur de substitution
			$aUtilistateur = array();
			foreach (self::$DEFAULT_PARAMS as $sParam => $sDefault) {
				if (array_key_exists($sParam, $aAUTH)) {
					$aUtilistateur[$sParam]	= DataHelper::get($aAUTH, $sParam);

					// Ajout d'un indicateur de MODE_SUBSTITUTE_USER dans le LOGIN
					if ($sParam == 'login_utilisateur') {
						$aUtilistateur[$sParam]	.= '<span class="strong blue margin-left-5">[MODE_SUBSTITUTE_USER]</span>';
					}
				}
			}

			// Authentification de l'utilisateur
			$this->authenticate(array_merge(self::$DEFAULT_PARAMS, $aUtilistateur));
		} else {
			$this->reset(self::$DEFAULT_PARAMS);
		}
	}

	/**
	 * @brief	Récupération de l'instance du singleton d'authentification.
	 *
	 * Méthode static permettant de récupérer l'instance AuthenticateManager en cours, sinon l'initialise.
	 *
	 * @param	string		$file		: chemin du fichier acl.ini
	 * @return	AuthenticateManager
	 */
	static public function getInstance() {
		if (! isset($_SESSION[__CLASS__])) {
			self::$oInstance = new AuthenticateManager();
			$_SESSION[__CLASS__] = self::$oInstance;
		}
		return $_SESSION[__CLASS__];
	}

	/**
	 * @brief	Recherche si un paramètre d'authentification est défini.
	 *
	 * @param	string	$sParam		: nom du paramètre.
	 * @return	string|integer
	 */
	public function issetParam($sParam) {
		return isset($this->_params[$sParam]) && !empty($this->_params[$sParam]);
	}

	/**
	 * @brief	Initialisation d'un paramètre d'authentification.
	 *
	 * @param	string	$sParam		: nom du paramètre.
	 * @param	mixed	$xValue		: valeur du paramètre.
	 * @return	void
	 */
	public function setParam($sParam, $xValue) {
		$this->_param[$sParam] = $xValue;
	}

	/**
	 * @brief	Récupère l'ensemble des paramètres d'authentification.
	 *
	 * @return	array
	 */
	public function getParams() {
		return $this->_params;
	}

	/**
	 * @brief	Récupère un paramètre d'authentification.
	 *
	 * @param	string	$sParam		: nom du paramètre.
	 * @param	integer	$iType		: Constante de typage de variable
	 * @return	string|integer
	 */
	public function getParam($sParam, $iType = DataHelper::DATA_TYPE_ANY) {
		return DataHelper::get($this->_params, $sParam, $iType);
	}

	/**
	 * @brief	Réinitialisation les données d'authentification de l'utilisateur.
	 *
	 * @return	string
	 */
	public function reset($aParams = array()) {
		$_SESSION		= array();
		$this->_params	= $aParams;
	}

	/**
	 * @brief	Purge les données d'authentification de l'utilisateur.
	 *
	 * @return	string
	 */
	public function destroy() {
		session_regenerate_id(true);
		$this->reset(self::$DEFAULT_PARAMS);
	}

	/**
	 * @brief	Vérification de l'authentification.
	 *
	 * Méthode permettant de contrôler l'authentification de l'utilisateur dans l'application.
	 *
	 * @return	boolean
	 */
	public function isAuthenticated() {
		return (bool) $this->getParam(self::FIELD_USER_ID);
	}

	/**
	 * @brief	Vérification du profil de l'utilisateur.
	 *
	 * @li	Selon le principe de hiérarchie, en recherche étendue, le profil supérieur inclu forcément le profil inférieur.
	 * @code
	 * 	AclManager::ID_PROFIL_WEBMASTER		= 6; #            .
	 * 	AclManager::ID_PROFIL_ADMINISTRATOR	= 5; #           / \
	 * 	AclManager::ID_PROFIL_VALIDATOR		= 4; #          /   \
	 * 	AclManager::ID_PROFIL_EDITOR		= 3; #         /     \
	 * 	AclManager::ID_PROFIL_USER			= 2; #        /       \
	 * 	AclManager::ID_PROFIL_GUEST			= 1; #       /         \
	 * 	AclManager::ID_PROFIL_UNDEFINED		= 0; #      /___________\
	 * @endcode
	 *
	 * @param	integer	$nSearch			: identifiant du profil à tester.
	 * @param	boolean	$bStrict			: (optionnel) TRUE recherche stricte ou FALSE recherche étendue.
	 * @return boolean
	 */
	public function isProfil($nSearch, $bStrict = false) {
		//						COMPARAISON STRICTE									|	COMPARAISON ÉTENDUE (Principe de hiérarchie)
		return ($bStrict) ?		$this->getParam('id_profil') == $nSearch	: $this->getParam('id_profil') >= $nSearch;
	}

	/**
	 * @brief	Enregistrement de l'authentification.
	 *
	 * Méthode permettant de stocker l'authentification de l'utilisateur dans l'application.
	 *
	 * @li Exploite le tableau des informations de l'utilisateur en base de données
	 *
	 * @param	array	$aUtilisateur		: tableau des informations d'authentification
	 * @code
	 * 		// TABLE `profil`
	 * 		integer	$aUtilisateur['id_profil']				: identifiant du profil
	 * 		string	$aUtilisateur['libelle_profil']			: libellé du profil
	 * 		string	$aUtilisateur['role_profil']			: rôle de l'utilisateur
	 * 		// TABLE `groupe`
	 * 		integer	$aUtilisateur['id_groupe']				: identifiant du groupe
	 * 		integer	$aUtilisateur['libelle_groupe']			: libellé du groupe
	 * 		integer	$aUtilisateur['borne_droite']			: borne droite du groupe
	 * 		integer	$aUtilisateur['borne_gauche']			: borne gauche du groupe
	 * 		// TABLE `grade`
	 * 		integer	$aUtilisateur['id_grade']				: identifiant du grade
	 * 		string	$aUtilisateur['libelle_court_grade']	: libellé court du grade
	 * 		string	$aUtilisateur['libelle_grade']			: libellé du grade
	 * 		// TABLE `utilisateur`
	 * 		string	$aUtilisateur['display_name']			: libellé de présentation de l'utilisateur [GRD NOM Prénom]
	 * 		integer	$aUtilisateur['id_utilisateur']			: identifiant de l'utilisateur
	 * 		string	$aUtilisateur['login_utilisateur']		: libellé du compte utilisateur
	 * 		string	$aUtilisateur['nom_utilisateur']		: nom de l'utilisateur
	 * 		string	$aUtilisateur['prenom_utilisateur']		: prénom de l'utilisateur
	 * @endcode
     * @throws	ApplicationException
	 */
	public function authenticate($aUtilisateur = array()) {
		if (!$this->isAuthenticated() && DataHelper::isValidArray($aUtilisateur, null, true)) {
			// Nom de l'utilisateur du type NOM Prénom (GRADE)
			if (!isset($aUtilisateur['display_name']) || empty($aUtilisateur['display_name'])) {
				// Construction du DISPLAYNAME
				$aUtilisateur['display_name']			= strtoupper($aUtilisateur['nom']) . " " . ucfirst($aUtilisateur['prenom']) . " (" . strtoupper($aUtilisateur['grade_court']) . ")";
			}

			// Initialisation des informations de connexion
			$aUtilisateur['id_session']					= session_id();
			$aUtilisateur['ip_adresse']					= $_SERVER['REMOTE_ADDR'];

			// Ajout des données de l'utilisateur connecté
			foreach (self::$DEFAULT_PARAMS as $sKey => $xValue) {
				$this->_params[$sKey]					= $aUtilisateur[$sKey];
			}
		} else {
			throw new ApplicationException('EAuthNotFind', $aUtilisateur);
		}
	}

	/**
	 * @brief	Récupère l'adresse IP de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIpAdresse() {
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * @brief	Récupère l'identifiant de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdUtilisateur() {
		return DataHelper::get($this->_params,self::FIELD_USER_ID,		DataHelper::DATA_TYPE_INT_ABS);
	}

	/**
	 * @brief	Récupère l'identifiant du profil de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdProfil() {
		return DataHelper::get($this->_params,self::FIELD_PROFIL_ID,		DataHelper::DATA_TYPE_INT_ABS);
	}

	/**
	 * @brief	Récupère le profil de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getLibelleProfil() {
		return DataHelper::get($this->_params,self::FIELD_PROFIL_LABEL);
	}

	/**
	 * @brief	Récupère le rôle de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getRole() {
		return DataHelper::get($this->_params,self::FIELD_PROFIL_ROLE);
	}

	/**
	 * @brief	Récupère l'identifiant du groupe de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdGroupe() {
		return DataHelper::get($this->_params,self::FIELD_GROUPE_ID,			DataHelper::DATA_TYPE_INT_ABS);
	}

	/**
	 * @brief	Récupère le libellé du groupe de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getLibelleGroupe() {
		return DataHelper::get($this->_params,self::FIELD_GROUPE_LABEL);
	}

	/**
	 * @brief	Récupère la borne droite du groupe de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getBorneDroite() {
		return DataHelper::get($this->_params, self::FIELD_GROUPE_DROITE,	DataHelper::DATA_TYPE_INT_ABS);
	}

	/**
	 * @brief	Récupère la borne gauche du groupe de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getBorneGauche() {
		return DataHelper::get($this->_params, self::FIELD_GROUPE_GAUCHE,	DataHelper::DATA_TYPE_INT_ABS);
	}

	/**
	 * @brief	Récupère l'identifiant du grade de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdGrade() {
		return DataHelper::get($this->_params, self::FIELD_GRADE_ID);
	}

	/**
	 * @brief	Récupère du login de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getLogin() {
		return DataHelper::get($this->_params, self::FIELD_USER_LOGIN);
	}

	/**
	 * @brief	Récupère le nom de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getDisplayName() {
		return DataHelper::get($this->_params, self::FIELD_DISPLAY_NAME);
	}

	/**
	 * @brief	Vérifie si l'utilisateur a le droit de modification.
	 *
	 * @return	bool
	 */
	public function isModifiable() {
		return DataHelper::get($this->_params, self::FIELD_USER_STATUS, DataHelper::DATA_TYPE_BOOL);
	}

}
