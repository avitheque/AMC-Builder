<?php
/**
 * Classe de création d'un champ SELECT dans l'application.
 *
 * @name		SelectHelper
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
class SelectHelper extends HtmlHelper {

	/**
	 * @brief	Classe contructeur de l'élément HTML.
	 *
	 * @param	string	$sName
	 * @param	array	$aListeOptions
	 * @param	string	$sValue
	 * @param	string	$sClass
	 */
	public function __construct($sName = "", $aListeOptions = array(), $sValue = null) {
		parent::__construct("select");
		$this->setName($sName);
		$this->setId($sName);
		$this->setOptions($aListeOptions, $sValue);
	}

	/**
	 *
	 * @param	array	$aListe
	 * @param	mixed	$sValue
	 * @return	void
	 */
	public function setOptions($aListe = array(), $sValue = null) {
		$this->setData(HtmlHelper::buildListOptions($aListe, $sValue, "-"));
	}

}
