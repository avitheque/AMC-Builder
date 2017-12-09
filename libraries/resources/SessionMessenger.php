<?php
/**
 * @brief	Gestionnaire des messages en SESSION.
 *
 * @li	L'espace de nom `SessionMessages` est réservé pour la gestion des messages entre SESSIONS.
 *
 * @name		SessionMessenger
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 86 $
 * @since		$LastChangedDate: 2017-12-09 19:52:40 +0100 (Sat, 09 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class SessionMessenger extends SessionManager {

	/**
	 * @brief	Instanciation du SINGLETON.
	 *
	 * La méthode instancie le SINGLETON s'il n'était pas déjà instancié.
     *
	 * @param	string	$sNameSpace		: nom de la session.
	 * @return	SessionMessenger
	 */
	public static function getInstance($sNameSpace = SESSION_MESSENGER) {
		// Fonctionnalité réalisée si l'instance du SINGLETON n'existe pas encore
		if (is_null(self::$oInstance)) {
			// Initialisation du SINGLETON
			self::$oInstance = new SessionMessenger($sNameSpace);
		}

		// Renvoi de l'instance du SINGLETON
		return self::$oInstance;
	}

	/**
	 * @brief	Initialise la variable de session `FIRST_RENDER`.
	 *
	 * @li	Indicateur d'accès au Bootstrap [IN].
	 *
	 * @param	float	$fTime			: moment à enregistrer.
	 * @return	void
	 */
	public function setFirstRender($fTime = null) {
		$this->setIndex('FIRST_RENDER', !empty($fTime) ? (float) $fTime : DataHelper::getTime());
	}

	/**
	 * @brief	Récupère la variable de session `FIRST_RENDER`.
	 *
	 * @li	Indicateur d'accès au Bootstrap [IN].
	 *
	 * @return	float
	 */
	public function getFirstRender() {
		return $this->getIndex('FIRST_RENDER');
	}

	/**
	 * @brief	Initialise la variable de session `VIEW_RENDER`.
	 *
	 * @li	Indicateur d'accès à ViewRender::start().
	 *
	 * @param	float	$fTime			: moment à enregistrer.
	 * @return	void
	 */
	public function setViewRender($fTime = null) {
		$this->setIndex('VIEW_RENDER', !empty($fTime) ? (float) $fTime : DataHelper::getTime());
	}

	/**
	 * @brief	Récupère la variable de session `VIEW_RENDER`.
	 *
	 * @li	Indicateur d'accès à ViewRender::start().
	 *
	 * @return	float
	 */
	public function getViewRender() {
		return $this->getIndex('VIEW_RENDER');
	}

	/**
	 * @brief	Initialise la variable de session `LAST_RENDER`.
	 *
	 * @li	Indicateur d'accès au Bootstrap [OUT].
	 *
	 * @param	float	$fTime			: moment à enregistrer.
	 * @return	void
	 */
	public function setLastRender($fTime = null) {
		$this->setIndex('LAST_RENDER', !empty($fTime) ? (float) $fTime : DataHelper::getTime());
	}

	/**
	 * @brief	Récupère la variable de session `LAST_RENDER`.
	 *
	 * @li	Indicateur d'accès au Bootstrap [OUT].
	 *
	 * @return	float
	 */
	public function getLastRender() {
		return $this->getIndex('LAST_RENDER');
	}

	/**
	 * @brief	Suppression d'un message.
	 *
	 * @li	Possibilité de supprimer tous les messages du même type en ne renseignant pas l'identifiant.
	 *
	 * @param	string	$sType		: type de message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
     * @return	void.
	 */
	public function unsetMessage($sType = ViewRender::MESSAGE_INFO, $xTag = null) {
		// Purge du contenu s'il existe
		if (!is_null($xTag) && $this->issetMessage($sType, $xTag)) {
			unset($_SESSION[SESSION_MESSENGER][$sType][$xTag]);
		} elseif ($this->issetMessage($sType)) {
			unset($_SESSION[SESSION_MESSENGER][$sType]);
		}
	}

	/**
	 * @brief	Enregistrement d'un message.
	 *
	 * Ajout d'un message à la collection.
	 *
	 * @param	string	$sType		: type de message.
	 * @param	string	$sMessage	: corps du message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
     * @return	void.
	 */
	public function setMessage($sType = ViewRender::MESSAGE_INFO, $sMessage, $xTag = null) {
		// Ajout d'un message à la collection
		$_SESSION[SESSION_MESSENGER][$sType][!empty($xTag) ? $xTag : DataHelper::getTime()]	= $sMessage;
	}

	/**
	 * @brief	Enregistrement d'un message d'erreur.
	 *
	 * Ajout d'un message à la collection `error`.
	 *
	 * @param	string	$sMessage	: corps du message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
     * @return	void.
	 */
	public function setMessageError($sMessage, $xTag = null) {
		// Ajout d'un message à la collection
		$this->setMessage(ViewRender::MESSAGE_ERROR, $sMessage, $xTag);
	}

	/**
	 * @brief	Enregistrement d'un message de succès.
	 *
	 * Ajout d'un message à la collection `success`.
	 *
	 * @param	string	$sMessage	: corps du message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
     * @return	void.
	 */
	public function setMessageSuccess($sMessage, $xTag = null) {
		// Ajout d'un message à la collection
		$this->setMessage(ViewRender::MESSAGE_SUCCESS, $sMessage, $xTag);
	}

	/**
	 * @brief	Enregistrement d'un message d'avertissement.
	 *
	 * Ajout d'un message à la collection `warning`.
	 *
	 * @param	string	$sMessage	: corps du message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
     * @return	void.
	 */
	public function setMessageWarning($sMessage, $xTag = null) {
		// Ajout d'un message à la collection
		$this->setMessage(ViewRender::MESSAGE_WARNING, $sMessage, $xTag);
	}

	/**
	 * @brief	Vérification d'un message.
	 *
	 * Vérifie la présence d'un message.
	 *
	 * @param	string	$sType		: type de message.
	 * @param	string	$xTag		: (optionnel) identifiant du message.
	 * @return	boolean.
	 */
	public function issetMessage($sType = ViewRender::MESSAGE_INFO, $xTag = null) {
        // Initialisation de la liste de(s) message(s)
        $bExists = false;
		// Récupération du message
		if (!is_null($xTag)) {
			// Recherche si le message existe par son titre
            $bExists = isset($_SESSION[SESSION_MESSENGER][$sType][$xTag]);
		} else {
			// Recherche si le message existe par sa présence
            $bExists = isset($_SESSION[SESSION_MESSENGER][$sType]);
		}

        // Renvoi de la vérification
        return $bExists;
	}

	/**
	 * @brief	Récupération d'un message.
	 *
	 * @li	Chaque message périmé est supprimé !
	 *
	 * @param	string	$sType		: type de message.
	 * @return	mixed.
	 */
	public function getMessage($sType = ViewRender::MESSAGE_INFO) {
        // Initialisation de la liste de(s) message(s)
        $aMessages = array();

		// Fonctionnalité réalisée si le type de message existe dans la collection
		if (isset($_SESSION[SESSION_MESSENGER][$sType])) {
			// Parcours de l'ensemble des messages selon le type en paramètre
			foreach ($_SESSION[SESSION_MESSENGER][$sType] as $fTime => $sMessage) {
				// Fonctionnalité réalisée si le message est périmé
				if ($fTime <= self::getFirstRender()) {
					// Suppression du message périmé
					unset($_SESSION[SESSION_MESSENGER][$sType][$fTime]);
				} else {
					// Récupération du message
					$aMessages[$fTime] = $sMessage;
				}
			}
		}

        // Renvoi de la liste de(s) message(s)
        return $aMessages;
	}

}
