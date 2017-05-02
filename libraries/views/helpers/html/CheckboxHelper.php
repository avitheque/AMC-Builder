<?php
/**
 * Classe de création d'un champ INPUT de type CHECKBOX dans l'application.
 *
 * @li	Un champ caché permet de récupérer la valeur "TRUE" ou "FALSE" selon si l'élément est coché.
 *
 * @name		CheckboxHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class CheckboxHelper extends InputHelper {

	protected	$_name			= null;
	protected	$_hidden		= null;
	protected	$_boolean		= true;
	protected	$_value			= "true";

	/**
	 * @brief	Classe contructeur de l'élément HTML.
	 *
	 * @param	string	$sName
	 * @param	string	$xValue
	 * @param	string	$sLabel
	 * @param	string	$sClass
	 */
	public function __construct($sName = "", $sLabel = null, $sClass = null) {
		// Nom du champ caché directement celui du champ souhaité
		$this->_hidden = $sName;

		// Fonctionnalité si le nom fait partie d'une liste de tableau
		if (preg_match("@(.+)\[(.*)\]$@", $sName, $aMatched)) {
			$this->_boolean	= false;

			// Extraction de la valeur du champ
			$this->_value	= DataHelper::get($aMatched, 2, DataHelper::DATA_TYPE_ANY, "true");

			// Renommage du champ visible
			$sName			= DataHelper::get($aMatched, 1) . "_check[]";
		} else {
			// Renommage du champ visible
			$sName	.= "_check";
		}

		// Construction du champ visible
		parent::__construct($sName, $this->_value, "checkbox", $sLabel, $sClass);
	}

	/**
	 * @brief	Rendu de la balise HTML
	 *
	 * @overload	HtmlHelper::renderHTML()
	 * @return	string
	 */
	public function renderHTML() {
		// Rendu de l'élément principal
		$sHTML = parent::renderHTML();

		// Initialisation de la sélection par défaut
		$sChecked		= "false";
		if ($this->getAttribute("checked")) {
			// Fonctionnalité réalsisée si le champ est coché
			$sChecked	= $this->_value;
		}

		// Ajout du champ caché pour la sélection
		$oHidden = new InputHelper($this->_hidden, $sChecked, "hidden");
		if (!$this->_boolean) {
			// Renommage de l'identifiant du champ avec sa valeur
			$this->_hidden = DataHelper::convertStringToId($this->_hidden, $this->_value);
		}
		// Initialisation de l'identifiant du champ caché
		$oHidden->setId($this->_hidden);
		$sHTML .= $oHidden->renderHTML();

		// Récupération de l'identifiant de la case à cocher éventuellement modifié lors de sa création
		$this->_id		= $this->getAttribute('id');

		// Récupération de l'identifiant du champ caché éventuellement modifié lors de sa création
		$this->_hidden	= $oHidden->getAttribute('id');

		// Script de mise à jour de la sélection des cases à cocher avec le champ caché
		$sJQuery = '// Activation du changement de valeur au clic
					$("#' . $this->_id . '").click(function() {
						if ($(this).is(":checked")) {
							$("#' . $this->_hidden . '").val("' . $this->_value . '");
						} else {
							$("#' . $this->_hidden . '").val("false");
						}
					});';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);

		// Renvoi du code HTML
		return $sHTML;
	}

}
