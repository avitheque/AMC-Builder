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
 * @version		$LastChangedRevision: 33 $
 * @since		$LastChangedDate: 2017-06-11 21:24:20 +0200 (Sun, 11 Jun 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class AuthenticateManager {

	const		DEFAULT_ID_UTILISATEUR			= 0;
	const		DEFAULT_ID_GROUPE				= 0;
	const		DEFAULT_ID_GRADE				= 0;
	const		DEFAULT_MODIFIABLE_UTILISATEUR	= false;
	const		DEFAULT_ID_PROFIL				= AclManager::ID_PROFIL_UNDEFINED;
	const		DEFAULT_LIBELLE_PROFIL			= Constantes::PROFIL_GUEST;
	const		DEFAULT_LOGIN					= Constantes::LOGIN_GUEST;
	const		DEFAULT_ROLE					= AclManager::ROLE_GUEST;
	const		DEFAULT_DISPLAY_NAME			= "Utilisateur non authentifié";

	/**
	 * @brief	Paramètres d'authentification par défaut.
	 *
	 * @var		array
	 */
	static public $DEFAULT_PARAMS 				= array(
		'display_name'				=> self::DEFAULT_DISPLAY_NAME,								// STRING	Libellé de l'utilisateur
		'id_groupe'					=> self::DEFAULT_ID_GROUPE,									// INTEGER	Identifiant du groupe de l'utilisateur
		'id_grade'					=> self::DEFAULT_ID_GRADE,									// INTEGER	Identifiant du grade de l'utilisateur
		'id_profil'					=> self::DEFAULT_ID_PROFIL,									// INTEGER	Identifiant du profil de l'utilisateur
		'id_utilisateur'			=> self::DEFAULT_ID_UTILISATEUR,							// STRING	Identifiant de l'utilisateur
		'libelle_profil'			=> self::DEFAULT_LIBELLE_PROFIL,							// STRING	Libelle du profil de l'utilisateur
		'login_utilisateur'			=> self::DEFAULT_LOGIN,										// STRING	Login de l'utilisateur
		'modifiable_utilisateur'	=> self::DEFAULT_MODIFIABLE_UTILISATEUR,					// BOOLEAN	Droit de modification pour l'utilisateur
		'role_profil'				=> self::DEFAULT_ROLE										// STRING	Rôle de l'utilisateur
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
	 * 		integer	$aUtilisateur['id_utilisateur']			: identifiant de l'utilisateur
	 * 		string	$aUtilisateur['login_utilisateur']		: libellé du compte utilisateur
	 * 		integer	$aUtilisateur['id_profil']				: identifiant du profil
	 * 		string	$aUtilisateur['libelle_profil']			: libellé du profil
	 * 		string	$aUtilisateur['role_profil']			: rôle de l'utilisateur
	 * 		integer	$aUtilisateur['id_groupe']				: identifiant du groupe
	 * 		integer	$aUtilisateur['id_grade']				: identifiant du grade
	 * 		string	$aUtilisateur['libelle_grade']			: libellé du grade
	 * 		string	$aUtilisateur['libelle_court_grade']	: libellé court du grade
	 * 		string	$aUtilisateur['nom_utilisateur']		: nom de l'utilisateur
	 * 		string	$aUtilisateur['prenom_utilisateur']		: prénom de l'utilisateur
	 * 		string	$aUtilisateur['display_name']			: texte complet d'identification de l'utilisateur [GRD NOM Prénom]
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
	 * @brief	Initialisation d'un paramètre d'authentification.
	 *
	 * @param	string	$sParam		: nom du paramètre.
	 * @param	mixed	$xValue		: valeur du paramètre.
	 * @return	void
	 */
	private function setParam($sParam, $xValue) {
		$this->_{$sParam} = $xValue;
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
		$_SESSION = array();
		$this->_params = $aParams;
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
		return (bool) $this->getParam('id_utilisateur');
	}

	/**
	 * @brief	Vérification du profil de l'utilisateur.
	 *
	 * @li	Selon le principe de hiérarchie, en recherche étendue, le profil supérieur inclu forcément le profil inférieur.
	 * @code
	 * 	AclManager::ID_PROFIL_WEBMASTER		= 6; #		      .
	 * 	AclManager::ID_PROFIL_ADMINISTRATOR	= 5; #	         / \
	 * 	AclManager::ID_PROFIL_VALIDATOR		= 4; #		    /   \
	 * 	AclManager::ID_PROFIL_EDITOR		= 3; #		   /     \
	 * 	AclManager::ID_PROFIL_USER			= 2; #		  /       \
	 * 	AclManager::ID_PROFIL_GUEST			= 1; #		 /         \
	 * 	AclManager::ID_PROFIL_UNDEFINED		= 0; #		/___________\
	 * @endcode
	 *
	 * @param	integer	$nSearch			: identifiant du profil à tester.
	 * @param	boolean	$bStrict			: (optionnel) TRUE recherche stricte ou FALSE recherche étendue.
	 * @return boolean
	 */
	public function isProfil($nSearch, $bStrict = false) {
		//						COMPARAISON STRICTE							|	COMPARAISON ÉTENDUE (Principe de hiérarchie)
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
	 * 		integer	$aUtilisateur['id_utilisateur']			: identifiant de l'utilisateur
	 * 		string	$aUtilisateur['login_utilisateur']		: libellé du compte utilisateur
	 * 		integer	$aUtilisateur['id_profil']				: identifiant du profil
	 * 		string	$aUtilisateur['libelle_profil']			: libellé du profil
	 * 		string	$aUtilisateur['role_profil']			: rôle de l'utilisateur
	 * 		integer	$aUtilisateur['id_groupe']				: identifiant du groupe
	 * 		integer	$aUtilisateur['id_grade']				: identifiant du grade
	 * 		string	$aUtilisateur['libelle_grade']			: libellé du grade
	 * 		string	$aUtilisateur['libelle_court_grade']	: libellé court du grade
	 * 		string	$aUtilisateur['nom_utilisateur']		: nom de l'utilisateur
	 * 		string	$aUtilisateur['prenom_utilisateur']		: prénom de l'utilisateur
	 * 		string	$aUtilisateur['display_name']			: (facultatif) texte complet sur l'identification de l'utilisateur [GRD NOM Prénom]
	 * @endcode
	 */
	public function authenticate($aUtilisateur = array()) {
		if (!$this->isAuthenticated() && DataHelper::isValidArray($aUtilisateur)) {
			// Nom de l'utilisateur du type NOM Prénom (GRADE)
			if (!isset($aUtilisateur['display_name']) || empty($aUtilisateur['display_name'])) {
				// Construction du DISPLAYNAME
				$aUtilisateur['display_name']	= strtoupper($aUtilisateur['nom_utilisateur']) . " " . ucfirst($aUtilisateur['prenom_utilisateur']) . " (" . strtoupper($aUtilisateur['libelle_court_grade']) . ")";
			}

			// Initialisation des informations de connexion
			$aUtilisateur['id_session']			= session_id();
			$aUtilisateur['ip_adresse']			= $_SERVER['REMOTE_ADDR'];

			// Ajout des données de l'utilisateur connecté
			foreach (self::$DEFAULT_PARAMS as $sKey => $xValue) {
				$this->_params[$sKey]			= $aUtilisateur[$sKey];
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
		return $this->_params['id_utilisateur'];
	}

	/**
	 * @brief	Récupère le profil de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getLibelleProfil() {
		return $this->_params['libelle_profil'];
	}

	/**
	 * @brief	Récupère l'identifiant du profil de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdProfil() {
		return $this->_params['id_profil'];
	}

	/**
	 * @brief	Récupère l'identifiant du groupe de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdGroupe() {
		return $this->_params['id_groupe'];
	}

	/**
	 * @brief	Récupère l'identifiant du grade de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getIdGrade() {
		return $this->_params['id_grade'];
	}

	/**
	 * @brief	Récupère le rôle de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getRole() {
		return $this->_params['role_profil'];
	}

	/**
	 * @brief	Récupère du login de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getLogin() {
		return $this->_params['login_utilisateur'];
	}

	/**
	 * @brief	Récupère le nom de l'utilisateur.
	 *
	 * @return	string
	 */
	public function getDisplayName() {
		return $this->_params['display_name'];
	}

	/**
	 * @brief	Vérifie si l'utilisateur a le droit de modification.
	 *
	 * @return	bool
	 */
	public function isModifiable() {
		return (bool) $this->_params['modifiable_utilisateur'];
	}

}
