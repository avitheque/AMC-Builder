<?php
/**
 * Classe de création d'un tableau dans l'application.
 *
 * @li	Possibilité d'activer le plugin DataTables de jQuery.
 *
 * @name		TableHelper
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
class TableHelper extends HtmlHelper {

	static	$DATE_FORMAT		= array(
		DataHelper::DATA_TYPE_DATE,
		DataHelper::DATA_TYPE_DATETIME
	);

	const	TEXT_EMPTY			= "Aucun résultat à afficher...";
	const	CONDITIONAL_ANCHOR	= "Anchor";
	const	CONDITIONAL_TEXT	= "Text";

	protected $_columns			= array();
	protected $_hidden			= array();
	protected $_visible			= array();
	protected $_data			= array();

	private $_head				= array();
	private $_body				= array();
	private $_foot				= array();

	private $_table				= array();

	private $_style				= array();
	private $_class				= array();
	private $_condition			= array();
	private $_format			= array();
	private $_concat			= array();

	/**
	 * Classe de contruction du tableau.
	 */
	public function __construct($sId = null, $aData = array(), $aHiddenColumns = array()) {
		// Initialisation de l'identifiant du tableau
		$this->setAttribute('id', $sId);

		// Récupération des données
		$this->_data	= $aData;

		// Initialisation des listes du tableau
		$this->_columns	= isset($this->_data[0]) ? array_keys($this->_data[0]) : array();
		$this->_visible	= $this->_columns;
		$this->_hidden	= $aHiddenColumns;

		// Initialisation des champs visibles
		$this->_initVisible();
	}

	/**
	 * @brief	Initialisation des colonnes visibles.
	 */
	private function _initVisible() {
		// Extraction des colonnes à masquer
		$this->_visible = array();
		if (!empty($this->_hidden)) {
			$aDiff = array_diff($this->_columns, (array) $this->_hidden);
		} else {
			$aDiff = $this->_columns;
		}

		// Parcours des colonnes à afficher
		foreach ($aDiff as $sKey) {
			$this->_visible[$sKey] = $sKey;
		}
	}

	/**
	 * @brief	Récupération des colonnes.
	 */
	public function getColumns($bVisible = false) {
		if ($bVisible) {
			return $this->_visible;
		} else {
			return $this->_columns;
		}
	}

	/**
	 *	@brief	Récupération de la clé de la colonne
	 *
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @return	string|integer
	 */
	public function getColumnKey($xRefData, $bNumeric = false) {
		// Initialisation de la référence
		$sKey = null;

		if (isset($this->_columns[$xRefData])) {
			// Récupération dans les occurrences
			$sKey = $this->_columns[$xRefData];
		} elseif (in_array($xRefData, $this->_columns)) {
			// Récupération dans les titres de colonnes
			$sKey = $xRefData;
		} elseif (in_array($xRefData, $this->_visible)) {
			// Récupération dans les titres personnalisés
			$aColumns = array_flip($this->_visible);
			$sKey = $aColumns[$xRefData];
		} else {
			$sKey = $xRefData;
		}

		// Renvoi de la colonne de référence
		return $sKey;
	}

	/**
	 *	@brief	Récupération d'occurrence de la colonne visible
	 *
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @return	integer
	 */
	public function getColumnOccurrence($xRefData) {
		// Initialisation de l'occurence de la colonne
		$nOccurrence = 0;

		// Fonctionnalité réalisée uniquement si des donnée sont présentes
		if (DataHelper::isValidArray($this->_data)) {
			if (DataHelper::isValidNumeric($xRefData, true)) {
				// Récupération de l'occurrence directe
				$nOccurrence = $xRefData;
			} elseif (in_array($xRefData, $this->_visible)) {
				// Récupération de l'occurrence selon le libellé affiché de la colonne
				$aKey = array_keys(array_keys(array_flip($this->_visible)), $xRefData);
				$nOccurrence = $aKey[0];
			} else {
				// Récupération de l'occurrence selon le nom de la colonne
				$aKey = array_flip(array_keys($this->_visible));
				$nOccurrence = $aKey[$xRefData];
			}
		} else {
			// La donée n'existe pas
			$nOccurrence = null;
		}

		// Renvoi de l'occurence de la colonne
		return $nOccurrence;
	}

	/**
	 * @brief	Ajout d'une colonne visible
	 *
	 * @li	Vérifie si le champ n'existe pas déjà.
	 *
	 * @param	sting	$sTitre			: libellé de la colonne à ajouter.
	 * @return	mixed, référence de la colonne.
	 */
	private function _addVisibleColumn($sTitre) {
		// Recherche de la clé de la colonne de référence
		$sKey = $this->getColumnKey($sTitre);

		// Fonctionnalités réalisées si le champ n'existe pas
		if (empty($sKey) || !in_array($sKey, $this->_columns)) {
			// Ajout du titre de la colonne à la liste visible
			$this->_visible[$sTitre] = $sTitre;
			$sKey = $sTitre;
		}

		// Renvoi de la clé de la colonne de référence
		return $sKey;
	}

	/**
	 * @brief	Suppression d'une colonne visible
	 *
	 * @li	Vérifie si le champ existe déjà.
	 *
	 * @param	sting	$sTitre			: libellé de la colonne à masquer.
	 * @return	mixed, référence de la colonne.
	 */
	public function removeColumn($sTitre) {
		// Recherche de la clé de la colonne de référence
		$sKey = $this->getColumnKey($sTitre);

		// Fonctionnalités réalisées si le champ n'existe pas
		if (in_array($sTitre, $this->_visible)) {
			// Suppression de la colonne à la liste visible
			unset($this->_visible[$sTitre]);
		} elseif (in_array($sKey, $this->_visible)) {
			// Suppression de la colonne à la liste visible
			unset($this->_visible[$sKey]);
		}

		// Renvoi de la clé de la colonne de référence
		return $sKey;
	}

	/**
	 * @brief	Suppression d'un ensemple de colonnes visibles
	 *
	 * @li	Vérifie si les champs existent déjà.
	 *
	 * @param	sting	$aListeTitres	: liste des libellés de colonnes à masquer.
	 * @return	array, liste des références de colonnes.
	 */
	public function removeColumns($aListeTitres = array()) {
		// Initialisation de la liste des références
		$aKey = array();

		// Fonctionnalité réalisée pour chaque champ
		foreach ((array) $aListeTitres as $sTitre) {
			// Suppression de la colonne visible
			$aKey[] = $this->removeColumn($sTitre);
		}

		// Renvoi de la liste des colonnes de référence
		return $aKey;
	}

	/**
	 * @brief	Renommage des colonnes du tableau
	 *
	 * @li	Possibilité de changer l'ordre d'affichage des colonnes.
	 *
	 * @param	array	$aNewNames		: liste associative des noms de colonnes à renommer.
	 * @code
	 *  // Soit le tableau de données suivant
	 *  //  _______________________________________________________________________________
	 *  // | nom_colonne_1 | nom_colonne_2 | nom_colonne_3 | nom_colonne_4 | nom_colonne_5 |
	 *  // |_______________|_______________|_______________|_______________|_______________|
	 *  // | valeur_1      | valeur_2      | valeur_3      | valeur_4      | valeur_5      |
	 *  // | valeur_6      | valeur_7      | valeur_8      | valeur_9      | valeur_10     |
	 *  // | valeur_11     | valeur_12     | valeur_13     | valeur_14     | valeur_15     |
	 *  // | valeur_16     | valeur_17     | valeur_18     | valeur_19     | valeur_20     |
	 *  // |_______________|_______________|_______________|_______________|_______________|
	 *  $aData = array(
	 *  	array(
	 *  		'nom_colonne_1'	=> "valeur_1",
	 *  		'nom_colonne_2'	=> "valeur_2",
	 *  		'nom_colonne_2'	=> "valeur_3",
	 *  		'nom_colonne_4'	=> "valeur_4",
	 *  		'nom_colonne_5'	=> "valeur_5",
	 *  	),
	 *  	array(
	 *  		'nom_colonne_1'	=> "valeur_6",
	 *  		'nom_colonne_2'	=> "valeur_7",
	 *  		'nom_colonne_2'	=> "valeur_8",
	 *  		'nom_colonne_4'	=> "valeur_9",
	 *  		'nom_colonne_5'	=> "valeur_10",
	 *  	),
	 *  	array(
	 *  		'nom_colonne_1'	=> "valeur_11",
	 *  		'nom_colonne_2'	=> "valeur_12",
	 *  		'nom_colonne_2'	=> "valeur_13",
	 *  		'nom_colonne_4'	=> "valeur_14",
	 *  		'nom_colonne_5'	=> "valeur_15",
	 *  	),
	 *  	array(
	 *  		'nom_colonne_1'	=> "valeur_16",
	 *  		'nom_colonne_2'	=> "valeur_17",
	 *  		'nom_colonne_2'	=> "valeur_18",
	 *  		'nom_colonne_4'	=> "valeur_19",
	 *  		'nom_colonne_5'	=> "valeur_20",
	 *  	),
	 *  );
	 *
	 *  // Affichage des colonnes dans l'ordre suivant
	 *  //  ___________________________________________________________
	 *  // | Colonne 5 | Colonne 1 | Colonne 2 | Colonne 3 | Colonne 4 |
	 *  // |___________|___________|___________|___________|___________|
	 *  // | valeur_5  | valeur_1  | valeur_2  | valeur_3  | valeur_4  |
	 *  // | valeur_10 | valeur_6  | valeur_7  | valeur_8  | valeur_9  |
	 *  // | valeur_15 | valeur_11 | valeur_12 | valeur_13 | valeur_14 |
	 *  // | valeur_20 | valeur_16 | valeur_17 | valeur_18 | valeur_19 |
	 *  // |___________________________________________________________|
	 * 	$aNewNames = array(
	 * 		'nom_colonne_5' => "Colonne 5",
	 * 		'nom_colonne_1' => "Colonne 1",
	 * 		'nom_colonne_2' => "Colonne 2",
	 * 		'nom_colonne_3' => "Colonne 3",
	 * 		'nom_colonne_4' => "Colonne 4",
	 * 	);
	 * @endcode
	 * @param	bool	$bHideOther		: (optionnel) masque les autres colonnes.
	 * @return	void
	 */
	public function renameColumns($aNewNames = array(), $bHideOther = false) {
		$aColumns = array();
		// Parcours l'ensemble des colonnes à renommer
		foreach ($aNewNames as $sKey => $sRename) {
			// Fonctionnalité réalisée si le champ est à ajouter
			if (is_numeric($sKey)) {
				$aColumns[$sRename] = $sRename;
			}

			// Fonctionnalité réalisée si le champ n'est pas vide
			if (!empty($sRename) && isset($this->_visible[$sKey]) && !in_array($sRename, $this->_visible)) {
				// La colonne correspond
				$aColumns[$sKey] = $sRename;
				// Suppression de la référence d'origine
				unset($this->_visible[$sKey]);
			}
		}

		// Réinitialisation de la liste des colonnes visibles avec celles renommées en premières
		if ($bHideOther) {
			$this->_visible = $aColumns;
		} else {
			$this->_visible = array_merge($aColumns, $this->_visible);
		}
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant une concaténation de champs
	 *
	 * @li	Exploitation de la méthode PHP @a vsprintf($sFormat, $aRefColumns).
	 *
	 * @param	string	$sTitre			: titre de la colonne ajoutée.
	 * @param	string	$sFormat		: format exploité.
	 * @param	array	$aRefColumns	: liste des références de colonnes à concaténer.
	 * @return	void
	 */
	public function addConcatenationOnColumn($sTitre, $sFormat, $aRefColumns) {
		// Ajout du titre de la colonne à la liste visible
		$sKey = $this->_addVisibleColumn($sTitre);

		// Ajout du formatage de la concaténation à la collection
		$this->_concat[$sKey] = array($sFormat => $aRefColumns);
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant un lien HREF de type bouton
	 *
	 * @param	string	$sTitre			: titre de la colonne ajoutée.
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @param	string	$sLabel			: libellé du bouton.
	 * @param	string	$sRoot			: référence du chemin HREF.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @param	string	$sTarget		: (optionnel) ajout de l'attribut TARGET.
	 * @param	string	$sTitle			: (optionnel) ajout de l'attribut TITLE.
	 * @return	void
	 */
	public function addAnchorOnColumn($sTitre, $xRefData = 0, $sLabel, $sRoot = "#", $sClass = "green", $sTarget = null, $sTitle = null) {
		// Ajout du titre de la colonne à la liste visible
		$this->_addVisibleColumn($sTitre);

		// Parcours l'ensemble des données
		foreach ($this->_data as $nOccurrence => $aEntity) {
			// Récupération de la clé de la colonne de référence
			$sKey = $this->getColumnKey($xRefData);

			// Initialisation du chemin HREF
			$sHref = "#";
			$sRootOption = null;
			if (!empty($sHref) && isset($aEntity[$sKey])) {
				// Récupération de la valeur de la colonne
				$sRootOption = $aEntity[$sKey];
			}

			// Création de l'élément HTML
			$oAnchor = new AnchorHelper($sLabel, $sRoot, $sClass, $sRootOption, $sTitle);
			if (!empty($sTarget)) {
				// Ajout de l'attribut TARGET
				$oAnchor->setAttribute('target', $sTarget);
			}

			// Ajout de l'élément
			if (array_key_exists($sTitre, $this->_data[$nOccurrence])) {
				$this->_data[$nOccurrence][$sTitre] .= $oAnchor->renderHTML();
			} else {
				$this->_data[$nOccurrence][$sTitre] = $oAnchor->renderHTML();
			}
		}
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant un champ INPUT
	 *
	 * @param	string	$sTitre			: titre de la colonne ajoutée.
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @param	string	$sName			: nom du champ INPUT.
	 * @param	string	$sType			: type de champ, [text] par défaut.
	 * @param	string	$sLabel			: label associé par défaut.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @return	void
	 */
	public function addInputOnColumn($sTitre, $xRefData = 0, $sName, $sType = "text", $sLabel = "", $sClass = null) {
		// Ajout du titre de la colonne à la liste visible
		$this->_addVisibleColumn($sTitre);

		// Parcours l'ensemble des données
		foreach ($this->_data as $nOccurrence => $aEntity) {
			// Récupération de la clé de la colonne de référence
			$sKey = $this->getColumnKey($xRefData);

			// Récupération de la valeur de la colonne
			$sValue = $aEntity[$sKey];

			// Création de l'élément HTML
			$oInput = new InputHelper($sName, $sValue, $sType, $sLabel);
			$this->_data[$nOccurrence][$sTitre] = $oInput->renderHTML();
		}
	}

	/**
	 * 	@brief	Ajout d'un formatage à une colonne
	 *
	 * @param	mixed	$xRefData		: référence de la colonne cible.
	 * @param	string	$sFormat		: classe CSS de l'élément.
	 * @return	void
	 */
	public function setFormatOnColumn($xRefData = 0, $sFormat) {
		// Récupération de la clé de la colonne de référence
		$sKey = $this->getColumnKey($xRefData);

		// Ajout du formatage à la collection
		$this->_format[$sKey] = $sFormat;
	}

	/**
	 * 	@brief	Ajout d'une option du plugin DataTable à une colonne
	 *
	 * @param	mixed	$xRefData		: référence de la colonne cible.
	 * @param	string	$sAttribute		: attribut de l'option.
	 * @param	string	$sValue			: valeur de l'option.
	 * @return	void
	 */
	public function setOptionOnColumn($xRefData = 0, $sAttribute = null, $sValue = null) {
		// Récupération de la clé de la colonne de référence
		$sKey = $this->getColumnKey($xRefData);

		// Ajout du formatage à la collection
		$this->_option[$sKey][$sAttribute] = $sValue;
	}

	/**
	 * 	@brief	Injection d'une valeur dans la colonne
	 *
	 * @param	mixed	$xRefTitle		: référence de la colonne cible.
	 * @param	mixed	$xRefData		: référence de la colonne de donnée.
	 * @param	string	$sFormat		: format de la chaîne.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @return	void
	 */
	public function prependValueIntoColumn($xRefTitle = 0, $xRefData = 0, $sFormat = "%s", $sClass = null) {
		// Parcours l'ensemble des données
		foreach ($this->_data as $nOccurrence => $aEntity) {
			// Récupération de la clé de la colonne de référence
			$sRefColumn = $this->getColumnKey($xRefTitle);

			// Récupération de la clé de la colonne de référence
			$sKey	= $this->getColumnKey($xRefData);
			// Récupération de la valeur de la colonne
			$sValue	= $aEntity[$sKey];

			// Formatage de la valeur
			$sData	= DataHelper::convertToString($sValue, $sFormat);

			// Création de l'élément HTML
			$oSpan	= new SpanHelper($sData, $sClass);

			// Récupération de la valeur d'origine
			$sCell	= $this->_data[$nOccurrence][$sRefColumn];
			// Injection de la valeur à la fin de la cellule
			$this->_data[$nOccurrence][$sRefColumn] = $oSpan->renderHTML() . $sCell;
		}
	}

	/**
	 * 	@brief	Ajout d'une valeur à la colonne
	 *
	 * @param	mixed	$xRefTitle		: référence de la colonne cible.
	 * @param	mixed	$xRefData		: référence de la colonne de donnée.
	 * @param	string	$sFormat		: format de la chaîne.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @return	void
	 */
	public function appendValueIntoColumn($xRefTitle = 0, $xRefData = 0, $sFormat = "%s", $sClass = null) {
		// Parcours l'ensemble des données
		foreach ($this->_data as $nOccurrence => $aEntity) {
			// Récupération de la clé de la colonne de référence
			$sRefColumn = $this->getColumnKey($xRefTitle);

			// Récupération de la clé de la colonne de référence
			$sKey	= $this->getColumnKey($xRefData);
			// Récupération de la valeur de la colonne
			$sValue	= $aEntity[$sKey];

			// Formatage de la valeur
			$sData	= DataHelper::convertToString($sValue, $sFormat);

			// Création de l'élément HTML
			$oSpan	= new SpanHelper($sData, $sClass);

			// Ajout de la valeur d'origine à la fin de la cellule
			$this->_data[$nOccurrence][$sRefColumn] .= $oSpan->renderHTML();
		}
	}

	public function setConditionalClassOnColumn($xRefLEFT, $sOperator, $xRefRIGHT, $sClass, $xRefColumn = null) {
		// Parcours l'ensemble des données
		foreach ($this->_data as $nOccurrence => $aEntity) {

			if (is_numeric($xRefLEFT)) {
				$sValueLEFT		= $xRefLEFT;
			} else {
				// Récupération de la clé de la colonne de référence GAUCHE
				$sColumnLEFT	= $this->getColumnKey($xRefLEFT);
				// Récupération de la valeur de la colonne
				$sValueLEFT		= $aEntity[$sColumnLEFT];
			}

			if (is_numeric($xRefRIGHT)) {
				$sValueRIGHT	= $xRefRIGHT;
			} else {
				// Récupération de la clé de la colonne de référence DROITE
				$sColumnRIGHT	= $this->getColumnKey($xRefRIGHT);
				// Récupération de la valeur de la colonne
				$sValueRIGHT	= $aEntity[$sColumnRIGHT];
			}

			switch ($sOperator) {
				case ">":
					$bTest	= $sValueLEFT > $sValueRIGHT;
					break;

				case ">=":
					$bTest	= $sValueLEFT >= $sValueRIGHT;
					break;

				case "=":
				case "==":
					$bTest	= $sValueLEFT == $sValueRIGHT;
					break;

				case "===":
					$bTest	= $sValueLEFT === $sValueRIGHT;
					break;

				case "<=":
					$bTest	= $sValueLEFT <= $sValueRIGHT;
					break;

				case "<":
					$bTest	= $sValueLEFT < $sValueRIGHT;
					break;

				case "<>":
				case "!=":
					$bTest	= $sValueLEFT != $sValueRIGHT;
					break;

				default:
					$bTest	= false;
					break;
			}

			// Ajout de la valeur d'origine à la fin de la cellule
			if ($bTest && is_null($xRefColumn)) {
				// Attribution de la classe sur toute la ligne
				if (isset($this->_class[$nOccurrence]['tr'])) {
					$this->_class[$nOccurrence]['tr'] += " " . $sClass;
				} else {
					$this->_class[$nOccurrence]['tr'] = $sClass;
				}
			} elseif ($bTest && !is_null($xRefColumn)) {
				foreach ((array) $xRefColumn as $sColumn) {
					// Récupération de la clé de la colonne de référence GAUCHE
					$sKeyColumn = $this->getColumnKey($sColumn);
					// Attribution de la classe sur la cellule
					if (isset($this->_class[$nOccurrence]['td'][$sKeyColumn])) {
						$this->_class[$nOccurrence]['td'][$sKeyColumn] += " " . $sClass;
					} else {
						$this->_class[$nOccurrence]['td'][$sKeyColumn] = $sClass;
					}
				}
			}
		}
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant un texte selon une condition
	 *
	 * @param	string	$sTitre			: titre de la colonne ajoutée.
	 * @param	mixed	$xRefCondition	: référence de la colonne pour la condition.
	 * @param	string	$sCondition		: valeur de la colonne pour la condition.
	 * @param	string	$sText			: contenu du texte à ajouter si la condition est vérifiée.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @return	void
	 */
	public function setConditionalTextOnColumn($sTitre, $xRefCondition = 0, $sCondition, $sText = "", $sClass = null) {
		// Ajout du titre de la colonne à la liste visible
		$sKeyTitre = $this->_addVisibleColumn($sTitre);

		// Récupération de la clé de la colonne de référence
		$sKeyCond	= $this->getColumnKey($xRefCondition);

		// Création de l'élément HTML
		$this->_condition[$sKeyTitre][$sKeyCond][$sCondition] = array(
			'type'	=> self::CONDITIONAL_TEXT,
			'text'	=> $sText,
			'class'	=> $sClass
		);
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant un lien HREF selon une condition
	 *
	 * @param	string	$sTitre			: titre de la colonne ajoutée.
	 * @param	mixed	$xRefCondition	: référence de la colonne pour la condition.
	 * @param	string	$sCondition		: valeur de la colonne pour la condition.
	 * @param	mixed	$xRefData		: référence de la colonne cible du lien HREF.
	 * @param	string	$sLabel			: libellé du lien HREF à ajouter si la condition est vérifiée.
	 * @param	string	$sRoot			: référence du chemin HREF.
	 * @param	string	$sClass			: classe CSS de l'élément.
	 * @param	string	$sTitle			: (optionnel) attribut TITLE.
	 * @return	void
	 */
	public function setConditionalAnchorOnColumn($sTitre, $xRefCondition = 0, $sCondition, $xRefData = 0, $sLabel, $sRoot = "/index?action=", $sClass = "green", $sTitle = null) {
		// Ajout du titre de la colonne à la liste visible
		$sKeyTitre	= $this->_addVisibleColumn($sTitre);

		// Récupération de la clé de la colonne de référence
		$sKeyCond	= $this->getColumnKey($xRefCondition);

		// Création de l'élément HTML
		$this->_condition[$sKeyTitre][$sKeyCond][$sCondition] = array(
			'type'	=> self::CONDITIONAL_ANCHOR,
			'ref'	=> $xRefData,
			'label'	=> $sLabel,
			'root'	=> $sRoot,
			'class'	=> $sClass,
			'title'	=> $sTitle
		);
	}

	/**
	 * 	@brief	Initialise l'attribution d'une classe à une colonne
	 *
	 * @param	string	$sClass			: classe CSS.
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @return	void
	 */
	public function setClassColumn($sClass, $xRefData = null) {
		if (is_null($xRefData)) {
			foreach ($this->_columns as $sKey) {
				$this->_class['td'][$sKey] = $sClass;
			}
		} elseif (is_array($xRefData)) {
			// Parcours de la liste des colonnes
			foreach ($xRefData as $sColumn) {
				$this->setClassColumn($sClass, $sColumn);
			}
		} else {
			// Récupération de la clé de la colonne de référence
			$sKey = $this->getColumnKey($xRefData);

			// Ajout de la classe à la collection
			$this->_class['td'][$sKey] = $sClass;
		}
	}

	/**
	 * 	@brief	Ajoute une classe à une colonne
	 *
	 * @param	string	$sClass			: classe CSS.
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @return	void
	 */
	public function addClassColumn($sClass, $xRefData = null) {
		// Fonctionnalité réalisée si la colonne n'est pas déclarée
		if (is_null($xRefData)) {
			// Attribution de la classe à toutes les colonnes
			foreach ($this->_columns as $sKey) {
				if (!isset($this->_class['td'][$sKey])) {
					$this->_class['td'][$sKey] = $sClass;
				} else {
					$this->_class['td'][$sKey] .= " " . $sClass;
				}
			}
		} else {
			// Récupération de la clé de la colonne de référence
			$sKey = $this->getColumnKey($xRefData);

			// Ajout de la classe à la collection
			if (!isset($this->_class['td'][$sKey])) {
				$this->_class['td'][$sKey] = $sClass;
			} else {
				$this->_class['td'][$sKey] .= " " . $sClass;
			}
		}
	}

	/**
	 * 	@brief	Ajout d'un champ SELECT dans une colonne du THEAD
	 *
	 * @param	array	$sName			: nom du champ SELECT.
	 * @param	array	$aListeOptions	: liste des options du champ SELECT.
	 * @param	mixed	$sValue			: valeur du champ SELECT.
	 * @param	mixed	$xRefData		: référence de la colonne.
	 * @param	string	$sLabel			: (optionnel) libellé du champ.
	 * @param	boolean	$bReplace		: (optionnel) remplace la valeur de la cellule.
	 */
	public function addSelectOnHeadColumn($sName, $aListeOptions, $sValue = null, $xRefData = 0, $sLabel = null, $bReplace = false) {
		// Récupération de l'occurrence de la colonne
		$nOccurrence = $this->getColumnKey($xRefData);

		// Ajout de l'élément dans la colonne
		if (isset($this->_visible[$nOccurrence])) {
			// Construction du champ SELECT
			$oSelect = new SelectHelper($sName, $aListeOptions, $sValue, $sLabel);
			$oSelect->setId($sName, $nOccurrence);
			if ($bReplace) {
				// Remplacement du contenu
				$this->_visible[$nOccurrence] = $oSelect->renderHTML();
			} else {
				// Ajout à la colonne
				$this->_visible[$nOccurrence] += $oSelect->renderHTML();
			}
		}
	}

	/**
	 * 	@brief	Suppression des lignes contenant selon un fitre
	 *
	 * @li	Supprime les lignes qui contienne un élément du filtre.
	 *
	 * @param	array	$aFiltre		: liste de données à filtrer.
	 */
	public function removeLines($aFiltre) {
		// Parcours des lignes du tableau
		foreach ($this->_data as $nOccurrence => $aLine) {
			// Parcours des colonnes de la ligne
			foreach ($aLine as $sColumn) {
				// Fonctionnalité réalisée si la colonne est à filtrer
				if (in_array($sColumn, $aFiltre)) {
					// Suppression de la ligne
					unset($this->_data[$nOccurrence]);
					continue;
				}
			}
		}
	}

	/**
	 * 	@brief	Construction de l'entête de tableau
	 *
	 * @return	void
	 */
	private function _buildTHead($bForce = false) {
		if ($this->_data || $bForce) {
			foreach ($this->_visible as $nOccurrence => $sColumn) {
				if (isset($this->_head[$nOccurrence])) {
					$this->_head[$nOccurrence] += $sColumn;
				} else {
					$this->_head[$nOccurrence] = $sColumn;
				}
			}
		}
	}

	/**
	 * 	@brief	Ajout d'une colonne contenant un lien HREF selon une condition
	 *
	 * @param	string	$sType			: titre de la colonne ajoutée.
	 * @param	array	$aConfig		: tableau de configuration de l'élément HTML.
	 * @param	array	$aEntity		: tableau des données.
	 * @return	void
	 */
	private function _getConditionalHTML($sType, $aConfig, $aEntity = array()) {
		switch ($sType) {
			case self::CONDITIONAL_ANCHOR:
				// Récupération de la configuration
				$sLabel 		= isset($aConfig['label'])	? $aConfig['label']	: "";
				$sRoot			= isset($aConfig['root'])	? $aConfig['root']	: "";
				$xRefData		= isset($aConfig['ref'])	? $aConfig['ref']	: 0;
				$sClass 		= isset($aConfig['class'])	? $aConfig['class']	: "";
				$sTitle 		= isset($aConfig['title'])	? $aConfig['title']	: "";

				// Récupération de la clé de la colonne de référence
				$sRefKey		= $this->getColumnKey($xRefData);
				$sRootOption	= $aEntity[$sRefKey];

				// Construction de l'élément HTML
				$oAnchor		= new AnchorHelper($sLabel, $sRoot, $sClass, $sRootOption, $sTitle);
				$xData			= $oAnchor->renderHTML();
				break;

			case self::CONDITIONAL_TEXT:
				// Récupération de la configuration
				$sText	 		= isset($aConfig['text'])	? $aConfig['text']	: "";
				$sClass 		= isset($aConfig['class'])	? $aConfig['class']	: "";

				// Construction de l'élément HTML
				$oSpan			= new SpanHelper($sText, $sClass);
				$xData			= $oSpan->renderHTML();
				break;

			default:
				$xData = "";
				break;
		}

		return $xData;
	}

	/**
	 * 	@brief	Construction du corps de tableau
	 *
	 * @li	Exploitation de la méthode PHP @a vsprintf($sFormat, $aRefColumns).
	 *
	 * @return	void
	 */
	private function _buildTBody() {
		if (DataHelper::isValidArray($this->_data)) {
			foreach ($this->_data as $xOccurrence => $aEntity) {
				foreach ($this->_visible as $xRefData => $sColumn) {
					// Récupération de la donnée
					$xData = isset($aEntity[$xRefData])	 ? $aEntity[$xRefData] : "";

					// Ajout d'une concaténation entre éléments
					if (array_key_exists($xRefData, $this->_concat)) {
						// Récupération de la clé de la colonne de référence
						$sKey = $this->getColumnKey($xRefData);

						// Parcours de la configuration de la concaténation
						foreach ($this->_concat[$xRefData] as $sFormat => $aRefColumns) {
							$aArgs = array();
							foreach ($aRefColumns as $sColumn) {
								$aArgs[] = $aEntity[$sColumn];
							}
							// Formatage de la concaténation
							$xData = vsprintf($sFormat, $aArgs);
						}
					}

					// Ajout d'un élément conditionnel
					if (array_key_exists($xRefData, $this->_condition)) {
						// Récupération de la clé de la colonne de référence
						$sKey = $this->getColumnKey($sColumn);

						foreach ($this->_condition[$xRefData] as $sReference => $aCondition) {
							foreach ($aCondition as $sTestValue => $aConfig) {
								// Récupération du nom de la colonne à tester
								$sColumn = $this->getColumnKey($sReference);
								if (array_key_exists($sReference, $aEntity)) {
									$sValue = $aEntity[$sReference];
								} elseif (array_key_exists($sColumn, $aEntity)) {
									$sValue = $aEntity[$sColumn];
								} else {
									continue;
								}

								// Fonctionnalité réalisée si la condition est réalisée
								if ($sValue == $sTestValue) {
									// Récupération du contenu HTML
									$xData = $this->_getConditionalHTML($aConfig['type'], $aConfig, $aEntity);
								}
							}
						}
					}

					// Ajout de la donnée au tableau
					$this->_body[$xOccurrence][$xRefData] = "$xData";
				}
			}
		}
	}

	/**
	 * 	@brief	Construction du pied de tableau
	 *
	 * @return	void
	 */
	private function _buildTFoot($bForce = false) {
		if ($this->_data || $bForce) {
			/** @todo	RAS */
		}
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
	public function renderHTML($sEmpty = self::TEXT_EMPTY, $sClass = "max-width center") {
		// Récupération de l'identifiant
		$sId = $this->getAttribute('id');

		//#################################################################################################
		// FEUILLE DE STYLE CSS
		//#################################################################################################

		// Ajout de la feuille de style
		ViewRender::addToStylesheet(FW_VIEW_STYLES . "/TableHelper.css");

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		if (empty($this->_head)) {
			$this->_buildTHead();
		}
		if (empty($this->_body)) {
			$this->_buildTBody();
		}
		if (empty($this->_foot)) {
			$this->_buildTFoot();
		}

		// Initialisation des variables de cellule
		$nLine		= 0;
		$nColumn	= 0;

		// Fonctionnalité réalisée si des données sont présentes
		if (DataHelper::isValidArray($this->_body)) {

			/** THEAD ****************************************************************************/

			// Création du code HTML
			$sHTML =	"<table " . sprintf('class="table %s"', $sClass) . " " . sprintf('id="%s"', $sId) . ">
							<thead>
								<tr>";
			foreach ($this->_head as $sHead) {
				$sHTML .= "			<th class=\"line-$nLine column-$nColumn cell-$nLine-$nColumn\">" . $sHead . "</th>";
				$nColumn++;
			}
			$sHTML .=	"		</tr>
							</thead>
							<tbody>";

			/** TBODY ****************************************************************************/

			// Affichage des données
			foreach ($this->_body as $nOccurrence => $aColumn) {
				$nLine++;
				$nColumn	= 0;

				// Classe de la ligne
				$sClassLine	= ($nLine%2) ? "odd" : "even";

				// Fonctionnalité réalisée si une classe est appliquée à la ligne
				if (isset($this->_class[$nOccurrence]['tr'])) {
					$sClassLine .= " " . $this->_class[$nOccurrence]['tr'];
				}

				$sHTML .=	"	<tr class=\"" . $sClassLine . "\">";
				foreach ($aColumn as $sKey => $sData) {
					$sClassColumn = null;
					// Fonctionnalité réalisée si une classe est appliquée à la colonne
					if (isset($this->_class['td'][$sKey])) {
						$sClassColumn = $this->_class['td'][$sKey];
					}
					// Fonctionnalité réalisée si une classe est appliquée à la cellule
					if (isset($this->_class[$nOccurrence]['td'][$sKey]) && array_key_exists($sKey, $this->_class[$nOccurrence]['td'])) {
						$sClassColumn .= " " . $this->_class[$nOccurrence]['td'][$sKey];
					}
					// Fonctionnalité réalisée si un formatage de colonne est à réaliser
					if (isset($this->_format[$sKey])) {
						$sPrefix = "";

						// Fonctionnalité réalisée si le formatage est une date
						if (in_array($this->_format[$sKey], self::$DATE_FORMAT)) {
							// Ajout d'un champ caché avec la valeur d'origine au format [Y-m-d]
							$sPrefix = sprintf('<span class="hidden">%s</span>', $sData);
						}

						$sData = $sPrefix . DataHelper::convertToString($sData, $this->_format[$sKey]);
					}

					// Classe de la cellule
					$sClass	= "class=\"line-$nLine column-$nColumn cell-$nLine-$nColumn $sClassColumn\"";

					// Construction de la cellule
					$sHTML .= sprintf("<td %s>%s</td>", $sClass, $sData);
					$nColumn++;
				}
				$sHTML .=	"	</tr>";
			}

			/** TFOOT ****************************************************************************/

			$sHTML .=	"	</tbody>
							<tfoot>";
			foreach ($this->_foot as $aColumn) {
				$sHTML .=	"	<tr>";
				foreach ($aColumn as $sData) {
					$sHTML .= "		<td>" . $sData . "</td>";
				}
				$sHTML .=	"	</tr>";
			}
			$sHTML .=	"	</tfoot>";
			$sHTML .=	"</table>";
		} else {
			// Aucun résultat
			$sHTML =	"<h4 class=\"strong center\">" . $sEmpty . "</h4>";
		}

		// Renvoi du code HTML
		return $sHTML;
	}

}
