<?php
/**
 * @brief	Gestion des ACLs.
 *
 * Cette classe SINGLETON permet de gérer l'accès des utilisateurs aux ressources de l'application.
 *
 * @li	La classe étant un singleton, utiliser getInstance() pour récupérer l'instance de ce singleton.
 * @code
 * 	$oAcl = AclManager::getInstance();
 * @endcode
 *
 * @code
 * 	$this->_acl	=	array(
 * 		role	=>	array(
 * 			0	=>	ressouce1,
 * 			1	=>	ressouce2,
 * 			...
 * 			N-¹	=>	ressouceN,
 * 		)
 * 	);
 * @endcode
 *
 * @name		AclManager
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 75 $
 * @since		$LastChangedDate: 2017-08-02 23:54:49 +0200 (Wed, 02 Aug 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class AclManager {

	/**
	 * @brief	Constante de configuration du fichier ACL.
	 * @var		string	`acl.ini` par défaut.
	 */
	const		FILENAME_INI			= 'acl.ini';

	/**
	 * @brief	Constantes des profils.
	 * @var		integer
	 */
	const		ID_PROFIL_UNDEFINED		= 0;
	const		ID_PROFIL_GUEST			= 1;
	const		ID_PROFIL_USER			= 2;
	const		ID_PROFIL_EDITOR		= 3;
	const		ID_PROFIL_VALIDATOR		= 4;
	const		ID_PROFIL_ADMINISTRATOR	= 5;
	const		ID_PROFIL_WEBMASTER		= 6;

	/**
	 * @brief	const	antes des rôles.
	 * @var		string
	 */
	const		ROLE_DEFAULT			= '*';						# id_profil = 0
	const		ROLE_GUEST				= 'guest';					# id_profil = 1
	const		ROLE_USER				= 'user';					# id_profil = 2
	const		ROLE_EDITOR				= 'editor';					# id_profil = 3
	const		ROLE_VALIDATOR			= 'validator';				# id_profil = 4
	const		ROLE_ADMIN				= 'administrator';			# id_profil = 5
	const		ROLE_GOD				= 'webmaster';				# id_profil = 6

	/**
	 * @brief	const	antes des droits.
	 * @var		string
	 */
	const		ROLES_SECTION			= 'roles';
	const		RESSOURCES_SECTION		= 'ressources';
	const		ALLOW					= 'allow';
	const		DENY					= 'deny';

	private 	$_init					= array();
	protected	$_acl					= array();
	protected	$_roles					= array();
	protected	$_ressources			= array();

	/**
	 * Instance du SINGLETON des ACLs.
	 * @var		AclManager
	 */
	private static $oInstance	= null;

	/**
	 * @brief	Chargement des droits ACLs.
	 *
	 * Constructeur de la classe qui permet la récupération des droits
	 * depuis le fichier de configuration généralement trouvé dans "/application/configs/acl.ini".
	 *
	 * @param	string		$file		: chemin du fichier, `acl.ini` par défaut.
	 * @return	void
	 */
	protected function __construct($filename = self::FILENAME_INI) {
		// Récupération des ACL sous forme de tableau
		$this->_init = ParseIniFile::parse(null, $filename);

		// Initialisation de la liste des roles
		$this->_initRessources();

		// Initialisation de la liste des ACL avec les ressources
		$this->_initRoles();

		// Chargement des ACLs
		$this->_setRoleRessources();
	}

	/**
	 * @brief	Récupération de l'instance du singleton des ACLs.
	 *
	 * Méthode static permettant de récupérer l'instance AclManager en cours, sinon l'initialise.
	 *
	 * @param	string		$file		: chemin du fichier, `acl.ini` par défaut.
	 * @return	AclManager
	 */
	static public function getInstance() {
		// Fonctionnalité réalisée si aucune instance n'est active
		if (is_null(self::$oInstance)) {
			// Initialisation de l'instance
			self::$oInstance = new AclManager(self::FILENAME_INI);
		}
		// Renvoi de l'instance
		return self::$oInstance;
	}

	/**
	 * @brief	Initialisation de la liste des ressources.
	 *
	 * @return	void
	 */
	private function _initRessources() {
		// Parcours de l'ensemble des ressources
		foreach ($this->_init[self::RESSOURCES_SECTION] as $sRessource => $sLabel) {
			$this->_ressources[$sRessource] = $sLabel;
		}
	}

	/**
	 * @brief	Initialisation de la liste des rôles.
	 *
	 * @return	void
	 */
	private function _initRoles() {
		// Parcours de l'ensemble des rôles
		foreach ($this->_init[self::ROLES_SECTION] as $sRole => $sNull) {
			$this->_roles[] = $sRole;
		}
	}

	/**
	 * @brief	Affectation des ressources aux rôles.
	 *
	 * Méthode permettant d'établir les privilèges dont bénéficient les utilisateurs en fonction de leur profil (rôle).
	 * On récupère par la même occasion les actions dont les droits sont hérités.
	 *
	 * @return	void
	 * @throws ApplicationException
	 */
	private function _setRoleRessources() {
		// Parcours de l'ensemble des rôles
		foreach ($this->_roles as $sRole) {
			$aACL = array();

			// Fonctionnalité réalisée si le rôle est valide
			if (in_array($sRole, $this->_roles)) {
				// Récupération de la liste des ressources d'accès à un rôle
				$aRessourcesAcl = $this->_init[$sRole];

				// Parcours de l'ensemble des ressources
				foreach ($aRessourcesAcl as $sRessource => $sAccess) {
					// Fonctionnalité réalisée si la ressource est déclarée
					if (in_array($sRessource, array_keys($this->_ressources))) {
						switch ($sAccess) {
							case self::ALLOW:
								$aACL[] = $sRessource;
								break;

							case self::DENY:
								unset($aACL[$sRessource]);
								break;

							default:
								// Fonctionnalité réalisée si l'accès correspond à l'environnement de l'application
								if (APP_ENV == $sAccess) {
									$aACL[] = $sRessource;
								} else {
									continue;
								}
								break;
						}

					} else {
						throw new ApplicationException('ERessourceAclNotFound', $sRessource);
					}
				}
			} else {
				throw new ApplicationException('ERoleAclNotFound', $sRole);
			}

			// Affectation des ACL au rôle
			$this->_acl[$sRole] = $aACL;
		}
	}

	/**
	 * @brief	Récupération du libellé de la ressource.
	 *
	 * Méthode permettant de récupérer la chaîne de caractères affecté à une ressource.
	 *
	 * @param	string $sRole
	 * @return	array
	 */
	public function getRessourceLabel($sRessource) {
		return DataHelper::get($this->_ressources,	$sRessource,	DataHelper::DATA_TYPE_CLASSID);
	}

	/**
	 * @brief	Récupération des privilèges d'un rôle.
	 *
	 * Méthode permettant de récupérer la liste des ACL selon le rôle passé en paramètre.
	 *
	 * @param	string $sRole
	 * @return	array
	 */
	public function getAcl($sRole) {
		return DataHelper::get($this->_acl,			$sRole,			DataHelper::DATA_TYPE_CLASSID);
	}

	/**
	 * @brief	Vérification des privilèges.
	 *
	 * Méthode permettant de vérifier les privilèges dont bénéficient l'utilisateur en fonction du profil (rôle).
	 *
	 * @param	string		$sRole
	 * @param	string		$sRessource
	 * @return	boolean
	 */
	public function isAllowed($sRole, $sRessource) {
		// Fonctionnalité réalisée si le rôle existe
		if (isset($this->_acl[$sRole])) {
			// Renvoi le résultat de vérification d'affectation du rôle sur la ressource
			return in_array($sRessource, $this->_acl[$sRole]);
		} else {
			// Génération d'une exception
			throw new ApplicationException('ERoleAclNotFound', $sRole);
		}
	}

}
