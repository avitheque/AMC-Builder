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
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ApplicationException extends Exception {
	private $sController	= FW_DEFAULTCONTROLLER;
	private $sAction		= FW_DEFAULTACTION;
	private $aParams		= array();
	private $aExtra			= array();

	/** @brief	Constructeur.
	 *
	 * Le constructeur mémorise les paramètres transmis, et effectue des vérifications mineures de type.
	 * Il vérifie entre autre si l'on passe des tableaux ou des chaînes en paramètre.
	 * @param	string		$sMessage	: Code littèral de l'exception.
	 * @param	array		$aExtra		: Tableau de chaines de caractères informatives décrivant l'exception.
	 * @param	string		$sAction	: URL générée sous forme de lien.
	 */
	public function __construct($sMessage, $aExtra = array(), $sController = FW_DEFAULTCONTROLLER, $sAction = FW_DEFAULTACTION) {
		if ($sMessage) {
			$aParams = (array) $sMessage;
		} else {
			$aParams = array(Constantes::ERROR_UNDEFINED);
		}
		$sMsg = array_shift($aParams);
		parent::__construct($sMsg);
		$this->aParams		= $aParams;
		$this->sController	= $sController;
		$this->sAction		= $sAction;
		$this->aExtra		= (array) $aExtra;
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
	 * URL de destination générée sous forme de lien dans la boite d'affichage.
	 * @return	Chaine de caractères.
	 */
	public function getController() {
		return $this->sController;
	}

	/** @brief	Action.
	 *
	 * URL de destination générée sous forme de lien dans la boite d'affichage.
	 * @return	Chaine de caractères.
	 */
	public function getAction() {
		return $this->sAction;
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
