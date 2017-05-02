<?php
/**
 * Classe de création d'un champ INPUT dans l'application.
 *
 * @name		InputHelper
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
class InputHelper extends HtmlHelper {

	const	TYPE_CHECKBOX	= "checkbox";
	const	TYPE_DATE		= "date";
	const	TYPE_HIDDEN		= "hidden";
	const	TYPE_RADIO		= "radio";
	const	TYPE_TEXT		= "text";

	protected	$_type		= null;
	protected	$_label		= null;
	protected	$_class		= null;

	/**
	 * @brief	Classe contructeur de l'élément HTML.
	 *
	 * @param	string	$sName
	 * @param	string	$xValue
	 * @param	string	$sType
	 * @param	string	$sClass
	 */
	public function __construct($sName = "", $xValue = null, $sType = self::TYPE_TEXT, $sLabel = null, $sClass = null) {
		parent::__construct("input", true);
		$this->setName($sName);
		$this->setId($sName, $xValue);
		$this->setAttribute('value', $xValue);
		$this->setAttribute('type', $sType);

		$this->_type	= $sType;
		$this->_label	= $sLabel;
		$this->_class	= $sClass;
	}

	/**
	 * @brief	Rendu de la balise HTML
	 *
	 * @overload	HtmlHelper::renderHTML()
	 * @return	string
	 */
	public function renderHTML() {
		// Fonctionnalité réalisée si le type de l'élément n'est pas TYPE_HIDDEN
		if (!empty($this->_type) && $this->_type != self::TYPE_HIDDEN) {
			$this->setLabel($this->_label, $this->_class);
		}
		// Rendu de l'élément
		return parent::renderHTML();
	}

}
