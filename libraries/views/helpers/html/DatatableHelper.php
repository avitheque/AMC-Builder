<?php
/**
 * Classe de création d'un tableau dynamique dans l'application.
 *
 * @li	Exploite les fonctionnalités du plugin DataTables de jQuery.
 * 
 * @code
 * 	// Tableau BIDIMENTIONNEL représentant le résultat d'une requête
 * 	$aListeItem = array(
 * 		array(
 * 			// Key		=> VALEUR DE LA COLONNE
 * 			'column_1'	=> "A1",
 * 			'column_2'	=> "B1",
 * 			'column_3'	=> "C1",
 * 			'column_4'	=> "D1",
 * 			'column_5'	=> "E1",
 * 			'date_debut'=> "1970-01-01",
 * 			'date_fin'	=> "1979-09-22"
 * 		),
 * 		array(
 * 			// Key		=> VALEUR DE LA COLONNE
 * 			'column_1'	=> "A2",
 * 			'column_2'	=> "B2",
 * 			'column_3'	=> "C2",
 * 			'column_4'	=> "D2",
 * 			'column_5'	=> "E2",
 * 			'date_debut'=> "1979-09-22",
 * 			'date_fin'	=> date("Y-m-d")
 * 		),
 * 		array(
 * 			// Key		=> VALEUR DE LA COLONNE
 * 			'column_1'	=> "A3",
 * 			'column_2'	=> "B3",
 * 			'column_3'	=> "C3",
 * 			'column_4'	=> "D3",
 * 			'column_5'	=> "E3",
 * 			'date_debut'=> date("Y-m-d"),
 * 			'date_fin'	=> "9999-12-31",
 * 		)
 * 	);
 * 
 * 	// Initialisation de l'objet DatatableHelper à partir de la liste
 * 	$oDataTable = new DatatableHelper("DatatableHelper",	$aListeItem);
 * 	
 * 	// Attribution d'une classe CSS à la 1ère colonne par son nom
 * 	$oDataTable->setClassColumn("align-left strong",		"column_1");
 * 	
 * 	// Formatage des colonnes en type DATE
 * 	$oDataTable->setFormatOnColumn('date_debut',			DataHelper::DATA_TYPE_DATE);
 * 	$oDataTable->setFormatOnColumn('date_fin',				DataHelper::DATA_TYPE_DATE);
 * 	
 * 	// Tri par défaut sur la colonne de `date_debut`
 * 	$oDataTable->setOrderColumn('date_debut',				DatatableHelper::ORDER_ASC);
 * 	
 * 	// Rendu final sous forme de code HTML
 * 	print $oDataTable->renderHTML();
 * @endcode
 *
 * @name		DatatableHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 67 $
 * @since		$LastChangedDate: 2017-07-19 00:09:56 +0200 (Wed, 19 Jul 2017) $
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
	public function setOrderColumn($xRefData = 0, $sOrder = self::ORDER_BOTH) {
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
