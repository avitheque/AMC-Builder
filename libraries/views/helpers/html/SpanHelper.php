<?php
/**
 * Classe de création d'un texte dans l'application.
 *
 * @name		SpanHelper
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
class SpanHelper extends HtmlHelper {

	/**
	 *
	 * @param	string	$sText
	 * @param	string	$sClass
	 */
	public function __construct($sText = "", $sClass = null) {
		parent::__construct("span");
		$this->setData($sText);
		$this->setClass($sClass);
	}

}
