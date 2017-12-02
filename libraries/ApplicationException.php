<?php
/** @brief	Classe des exceptions
 *
 * Cette classe hérite de la classe Exception.
 * C'est une Exception enrichie qui possède en plus :
 * @li un lien vers le Controleur qui suivra l'affichage du message de l'exception.
 * @li des messages informatifs supplémentaire
 * le message de l'exception est codifié. Voir le contenu de VwError.php pour la signification des codes.
 *
 * @name		ApplicationException
 * @package		Exception
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ApplicationException extends Exception {
	private $_oInstanceStorage		= null;
	private $aParams				= array();
	private $aExtra					= array();

	/** @brief	Constructeur.
	 *
	 * Le constructeur mémorise les paramètres transmis, et effectue des vérifications mineures de type.
	 * Il vérifie entre autre si l'on passe des tableaux ou des chaînes en paramètre.
	 * @param	string		$sMessage	: Code littèral de l'exception.
	 * @param	array		$aExtra		: Tableau de chaines de caractères informatives décrivant l'exception.
	 */
	public function __construct($sMessage, $aExtra = array()) {
		if ($sMessage) {
			$aParams				= (array) $sMessage;
		} else {
			$aParams				= array(Constantes::ERROR_UNDEFINED);
		}

		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage	= InstanceStorage::getInstance();

		$sMsg = array_shift($aParams);
		parent::__construct($sMsg);
		$this->aParams				= $aParams;
		$this->aExtra				= (array) $aExtra;
	}

	/** @brief	Paramètres associés aux messages.
	 *
	 * La fonction renvoie les paramètres associés au message.
	 * Les paramètre se présentent sous forme d'un tableau.
	 * le paramètre 0 équivaut au label de l'exception.
	 * Les paramètres suivants correspondant à autant de ligne d'information destinées à être affichées
	 * à la suite dans le message d'erreur.
	 * @return	Tableau de chaines
	 */
	public function getParams() {
		return $this->aParams;
	}

	/** @brief	Contrôleur.
	 *
	 * Récupération du contrôlleur.
	 * @return	Chaine de caractères.
	 */
	public function getController() {
		return DataHelper::get($this->_oInstanceStorage->getParam("execute"), "controller", DataHelper::DATA_TYPE_CLASSID, FW_DEFAULTCONTROLLER);
	}

	/** @brief	Action.
	 *
	 * Récupération de l'action.
	 * @return	Chaine de caractères.
	 */
	public function getAction() {
		return DataHelper::get($this->_oInstanceStorage->getParam("execute"), "action", DataHelper::DATA_TYPE_CLASSID, FW_DEFAULTACTION);
	}

	/** @brief	Tableau de lignes d'info supplémentaire.
	 *
	 * Renvoie le tableau des lignes d'information supplémentaires.
	 * @return	Tableau de chaines.
	 */
	public function getExtra() {
		return $this->aExtra;
	}

}
