<?php
/**
 * Classe de création d'un lien HREF dans l'application.
 *
 * @name		AnchorHelper
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
class AnchorHelper extends HtmlHelper {

	/**
	 *
	 * @param	string	$sLabel
	 * @param	string	$sRoot
	 * @param	string	$sClass
	 * @param	string	$sRootOption
	 * @param	string	$sTitle
	 */
	public function __construct($sLabel = "Cliquez ici", $sRoot = "#", $sClass = null, $sRootOption = null, $sTitle = null) {
		parent::__construct("a");
		// Fonctionnalité réalisée si une option est à inclure dans le lien
		if (preg_match("@\%[a-z]@", $sRoot)) {
			$sRoot = sprintf($sRoot, $sRootOption);
		} else {
			$sRoot = $sRoot . $sRootOption;
		}
		$this->setAttribute("href", $sRoot);
		$this->setAttribute("title", $sTitle);
		$this->setClass("button " . $sClass);
		$this->setData($sLabel);
	}

}
