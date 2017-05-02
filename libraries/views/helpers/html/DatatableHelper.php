<?php
/**
 * Classe de création d'un tableau dynamique dans l'application.
 *
 * @li	Exploite les fonctionnalités du plugin DataTables de jQuery.
 *
 * @name		DatatableHelper
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
class DatatableHelper extends TableHelper {

	/**
	 * @brief	Constantes de tri des colonnes.
	 * @var		string
	 */
	const	ORDER_NONE			= '';
	const	ORDER_ASC			= 'asc';
	const	ORDER_DESC			= 'desc';
	const	ORDER_BOTH			= 'both';
	static	$PARAMS_ORDER		= array(self::ORDER_BOTH, self::ORDER_ASC, self::ORDER_DESC);

	/**
	 * Options des colonnes du plugin DataTable
	 * @var		array
	 */
	private $_option			= array('language' => 'DATATABLE_LANGUAGE');

	/**
	 * 	@brief	Ajout d'une option du plugin DataTable à une colonne
	 *
	 * @param	string	$sAttribute		: attribut de l'option.
	 * @param	string	$xValue			: valeur de l'option.
	 * @return	void
	 */
	public function setOption($sAttribute = null, $xValue = null) {
		// Fonctionnalité réalisée si le paramaètre n'existe pas (encore)
		if (! isset($this->_option[$sAttribute])) {
			// Initialisation de la collection
			$this->_option[$sAttribute] = array();
		}

		// Ajout du paramètre à la collection
		$this->_option[$sAttribute] += $xValue;
	}

	/**
	 * @brief	Affectation de l'ordre de tri du tableau sur une colonne par défaut.
	 *
	 * @param	mixed	$xRefData		: référence de la colonne de tri.
	 * @param	string	$sOrder			: ordre de tri à affecter.
	 * @return	void
	 */
	public function setOrderColumn($xRefData = 0, $sOrder = "both") {
		// Récupération de l'occurrence de la colonne de référence
		$nOccurrence = $this->getColumnOccurrence($xRefData);

		// Contrôle du paramètre
		$sAttribute = in_array($sOrder, self::$PARAMS_ORDER) ? strtolower($sOrder) : self::ORDER_NONE;

		// Ajout du paramètre à la collection
		$this->setOption('order', array(array($nOccurrence, $sAttribute)));
	}


	/**
	 * @brief	Désactivation de la fonctionnalité de tri sur une colonne.
	 *
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @return	void
	 */
	public function disableOrderingOnColumn($xRefData = 0) {
		// Récupération de l'occurrence de la colonne de référence
		$nOccurrence = $this->getColumnOccurrence($xRefData);

		// Ajout du paramètre à la collection
		$this->setOption('columnDefs', array(array('orderable' => false, 'targets' => $nOccurrence)));
	}

	/**
	 * 	@brief	Initialisation du plugin jQuery DataTables
	 *
	 * @see		/public/library/scripts/TableHelper.js
	 * @return	void
	 */
	private function _initPlugin() {
		// Récupération de l'identifiant
		$sId = $this->getAttribute('id');

		// Ajout du fichier JavaScript à la collection
		ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/DatatableHelper.js");

		// Activation du plugin DataTables
		$sJQuery = '$("#' . $sId . '").dataTable(' . DataHelper::convertToJSON($this->_option) . ');';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);
	}

	/**
	 * 	@brief	Création du code HTML
	 *
	 * @see		/public/library/scripts/TableHelper.js
	 *
	 * @param	string	$sEmpty		: texte à afficher s'il n'y a aucun résultat.
	 * @param	string	$sClass		: classe CSS du tableau.
	 * @return	string, code HTML du tableau
	 */
	public function renderHTML($sEmpty = self::TEXT_EMPTY, $sClass = "dataTable max-width center") {
		//#################################################################################################
		// CODE JAVASCRIPT
		//#################################################################################################
		$this->_initPlugin();

		//#################################################################################################
		// FEUILLE DE STYLE
		//#################################################################################################

		// Ajout de la feuille de style
		ViewRender::addToStylesheet(FW_VIEW_STYLES . "/DatatableHelper.css");

		// Renvoi du code HTML
		return parent::renderHTML($sEmpty, $sClass);
	}

}
