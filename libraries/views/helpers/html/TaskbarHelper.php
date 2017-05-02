<?php
/**
 * Classe de gestion de la barre des tâches de l'application.
 *
 * @name		TaskbarHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 5 $
 * @since		$LastChangedDate: 2017-03-02 22:16:57 +0100 (jeu., 02 mars 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class TaskbarHelper extends ViewRender {

	/**
	 * Classe de contruction de la barre.
	 */
	public function __construct($aAuth = array()) {
		// Récupération de l'identifiant de l'utilisateur
		$nIdUtilisateur	= DataHelper::get($aAuth, 'id_utilisateur',		DataHelper::DATA_TYPE_STR,	AuthenticateManager::DEFAULT_ID_UTILISATEUR);
		// Ajout du bouton de déconnexion si l'utilisateur est authentifié
		$this->setExitButton($nIdUtilisateur);

		// Récupération du login de l'utilisateur
		$sLogin			= DataHelper::get($aAuth, 'login_utilisateur',	DataHelper::DATA_TYPE_ANY,	AuthenticateManager::DEFAULT_LOGIN);
		// Récupération du libellé de l'utilisateur
		$sDisplayName	= DataHelper::get($aAuth, 'display_name',		DataHelper::DATA_TYPE_ANY,	AuthenticateManager::DEFAULT_DISPLAY_NAME);
		// Récupération du rôle du compte utilisateur
		$sRole			= DataHelper::get($aAuth, 'role_profil',		DataHelper::DATA_TYPE_ANY,	AuthenticateManager::DEFAULT_ROLE);
		// Récupération du profil de l'utilisateur
		$sProfil		= DataHelper::get($aAuth, 'libelle_profil',		DataHelper::DATA_TYPE_STR,	AuthenticateManager::DEFAULT_LIBELLE_PROFIL);

		// Ajout de l'utilisateur à la barre
		$this->setIdentity($sLogin, $sDisplayName, $sRole);
		// Ajout du profil à la barre
		$this->setProfil($sProfil);
	}

	/**
	 * @brief	Nom de l'utilisateur
	 * Méthode permettant de créer la barre des tâches.
	 *
	 * @param	string	$sLogin			: login de l'utilisateur.
	 * @param	string	$sDisplayName	: nom de l'utilisateur.
	 * @return	string
	 */
	public function setIdentity($sLogin, $sDisplayName = "") {
		// Accès à la ressource `login` si non authentifié
		$sRessource		= "login";

		// Fonctionnalité réalisée si l'utilisateur est authentifié
		if ($sLogin != AuthenticateManager::DEFAULT_LOGIN) {
			// Accès à la ressource `compte`
			$sRessource	= "compte";
		} else {
			// L'utilisateur est non identifié
			$sLogin		= AuthenticateManager::DEFAULT_DISPLAY_NAME;
		}

		// Construction de l'affichage de l'utilisateur
		return ViewRender::addToFooter(sprintf('<span class="user right"><a id="idCompte" href="%s" title="%s">%s</a></span>', $sRessource, $sDisplayName, $sLogin));
	}

	/**
	 * @brief	Profil de l'utilisateur
	 * Méthode permettant de créer la barre des tâches.
	 *
	 * @param	string	$sProfil		: libellé du profil.
	 * @return	string
	 */
	public function setProfil($sProfil) {
		// Fonctionnalité réalisée si le MODE_DEBUG est actif
		$sMode			= "";
		if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
			$sMode		= "<span class=\"red margin-right-5\">[MODE_DEBUG]</span>";
		}

		// Construction de l'affichage du profil
		return ViewRender::addToFooter(sprintf('<span class="profil left">%s%s</span>', $sMode, $sProfil));
	}

	/**
	 * @brief	Bouton de déconnexion
	 *
	 * Méthode permettant de créer un bouton de déconnexion dans la barre des tâches.
	 *
	 * @param	integer	$nIdUtilisateur	: identifiant de l'utilisateur.
	 * @return	string
	 */
	public function setExitButton($nIdUtilisateur = 0) {
		// Fonctionnalité réalisée si l'identifiant de l'utilisateur est valide
		if (empty($nIdUtilisateur)) {
			return false;
		}

		// Construction du menu
		return ViewRender::addToFooter('<a href="/compte/logout" class="confirm exit right" id="idExit" title="Quitter">...</a>');
	}

}
