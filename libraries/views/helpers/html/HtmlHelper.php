<?php
/**
 * Classe de création d'une balise HTML.
 *
 * @name		HtmlHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 141 $
 * @since		$LastChangedDate: 2018-08-12 18:05:58 +0200 (Sun, 12 Aug 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class HtmlHelper {

	const		APPEND			= 'append';
	const		PREPEND			= 'prepend';

	const		ASC				= 'sort';
	const		DESC			= 'rsort';
	const		KEY_ASC			= 'ksort';
	const		KEY_DESC		= 'krsort';
	const		NONE			= 'none';

	private		$_tag			= null;
	private		$_attribute		= array();
	private		$_closing		= false;
	private		$_dataPosition	= self::APPEND;

	protected	$_before		= "";
	protected	$_data			= "";
	protected	$_after			= "";

	/**********************************************************************************************
	 * @todo	METHODES STATIQUES
	 **********************************************************************************************/

	/**
	 * @brief	Méthode de création de listes d'options pour les champs SELECT.
	 *
	 * @param	array		$aListe			: liste d'éléments de la forme array('key' => "value").
	 * @param	array		$xValues		: liste des champs sélectionnés.
	 * @param	string		$sFirst			: première valeur du champ.
	 * @param	const		$sTri			: (optionnel) préférence de tri des valeurs, NULL par défaut.
	 * @param	boolean		$bDisabled		: (optionnel) champ en lecture seule, seule la valeur est renseignée.
	 * @param	string		$sFormat		: (optionnel) format HTML de chaque entrée de balise OPTION.
	 * @return	string
	 * @author durandcedric
	 */
	public static function buildListOptions($aListe, $xValues = array(), $sFirst = null, $sTri = self::NONE, $bDisabled = false, $sFormat = "<option %s value=\"%s\" >%s</option>") {
		// Tri de la liste
		switch ($sTri) {
			case self::ASC:
				asort($aListe);
				break;

			case self::DESC:
				arsort($aListe);
				break;

			case self::KEY_ASC:
				ksort($aListe);
				break;

			case self::KEY_DESC:
				krsort($aListe);
				break;

			case self::NONE:
			default:
				break;
		}

		// Création de la première entrée
		$sPrefix = null;
		if (isset($sFirst) && !$bDisabled) {
			$sPrefix = self::buildListOptions(array(0 => $sFirst), array(), null, self::NONE, $bDisabled, $sFormat);
		}

		// Initialisation de la liste
		$sOptions = "";
		if (DataHelper::isValidArray($aListe)) {
			foreach ($aListe as $nId => $sLibelle) {
				// Fonctionnalité réalisée si l'élément est sélectionné
				$sSelected = in_array($nId, (array) $xValues) ? "selected=\"selected\"" : null;
				if ($bDisabled && empty($sSelected)) {
					continue;
				}
				// Ajout de l'option
				$sOptions .= sprintf($sFormat, $sSelected, $nId, $sLibelle);
			}
		}

		// Finalisation si aucune valeur n'est valide
		if (empty($sOptions) && $bDisabled) {
			$sPrefix = self::buildListOptions(array(0 => '-'), array(), null, self::NONE, false, $sFormat);
		}

		return $sPrefix . $sOptions;
	}

	/**
	 * @brief	Méthode de création de listes d'options sous forme d'arborescence pour les champs SELECT.
	 *
	 * @param	array		$aArborescence	: liste d'éléments de la forme d'une arborescence.
	 * @param	array		$xValues		: liste des identifiants sélectionnés.
	 * @param	string		$sFirst			: première valeur du champ.
	 * @param	const		$sTri			: (optionnel) préférence de tri des valeurs, NULL par défaut.
	 * @param	boolean		$bDisabled		: (optionnel) champ en lecture seule, seule la valeur est renseignée.
	 * @param	string		$sFormat		: (optionnel) format HTML de chaque entrée de balise OPTION.
	 * @return	string
	 * @author durandcedric
	 */
	public static function buildListArborescenceOptions($aArborescence, $xValues = array(), $sFormatList = array('id' => 'libelle'), $bDisabled = false, $sFormat = "<option %s value=\"%s\" >%s</option>") {
		// Manipulation du premier paramètre sous forme de liste
		$aListe	= DataHelper::requestToList($aArborescence, $sFormatList, null, 'none');

		// Initialisation de la liste
		$sOptions = "";
		if (DataHelper::isValidArray($aListe)) {
			foreach ($aListe as $nId => $sLibelle) {
				// Fonctionnalité réalisée si l'élément est sélectionné
				$sSelected = in_array($nId, (array) $xValues) ? "selected=\"selected\"" : null;
				if ($bDisabled && empty($sSelected)) {
					continue;
				}
				// Ajout de l'option
				$sOptions .= sprintf($sFormat, $sSelected, $nId, $sLibelle);
			}
		}

		// Renvoi de la liste des options
		return $sOptions;
	}

	/**********************************************************************************************
	 * @todo	METHODES D'INSTANCE
	 **********************************************************************************************/

	/**
	 * @brief	Classe contructeur de l'élément HTML.
	 *
	 * @param	string	$sTagName
	 * @param	boolean	$bClosing
	 * @return	void
	 */
	public function __construct($sTagName = "", $bClosing = false) {
		$this->_tag = strtolower($sTagName);
		$this->setClosing($bClosing);
	}

	/**
	 * @brief	Configuration de la position des données.
	 * @param	string	$sPosition
	 * @return	void
	 */
	public function setDataPosition($sPosition = self::APPEND) {
		switch (strtolower($sPosition)) {
			case self::PREPEND:
				$this->_dataPosition = self::PREPEND;
				break;

			default:
				$this->_dataPosition = self::APPEND;
				break;
		}
	}

	/**
	 * @brief	Fermeture de la balise
	 * @param	boolean	$bClosing			: fermeture de la balise
	 * @return	void
	 */
	public function setClosing($bClosing = false) {
		$this->_closing = $bClosing;
	}

	/**
	 * @brief	Initialisation de l'attribut de la balise
	 * @param	string	$sName				: nom de l'attribut de la balise
	 * @param	mixed	$xValue				: valeur de l'attribut de la balise
	 * @return	void
	 */
	public function setAttribute($sName, $xValue = null) {
		// Fonctionnalité réalisée si la valeur n'est pas vide
		if (!empty($xValue)) {
			// Initialisation de l'attribut
			$this->_attribute[$sName] = $xValue;
		} else {
			// Suppression de l'attribut
			unset($this->_attribute[$sName]);
		}
	}

	/**
	 * @brief	Récupération de l'attribut de la balise
	 * @param	string	$sName				: nom de l'attribut de la balise
	 * @return	mixed
	 */
	public function getAttribute($sName) {
		$xValue			= null;
		if (isset($this->_attribute[$sName])) {
			$xValue		= $this->_attribute[$sName];
		}
		return $xValue;
	}

	/**
	 * @brief	Ajout de l'attribut de la balise
	 * @param	string	$sName				: nom de l'attribut de la balise
	 * @param	mixed	$xValue				: valeur a ajouter à l'attribut de la balise
	 * @return	void
	 */
	public function addAttribute($sName, $xValue = null) {
		// Fonctionnalité réalisée si l'attribut n'existe pas
		if (isset($this->_attribute[$sName]) && !preg_match("@$xValue@", $this->_attribute[$sName]) && !empty($xValue)) {
			// Ajout de la valeur à l'attribut actuel
			$this->_attribute[$sName] .= " " . $xValue;
		} elseif (!empty($xValue)) {
			// Initialisation de l'attribut actuel
			$this->setAttribute($sName, $xValue);
		}
	}

	/**
	 * @brief	Initialisation de l'attribut name de la balise
	 * @param	string	$sName				: attribut name de la balise
	 * @return	void
	 */
	public function setName($sName) {
		$this->setAttribute("name", trim(strtolower($sName)));
	}

	/**
	 * @brief	Initialisation de l'identifiant du champ.
	 *
	 * @param	string	$sId
	 * @return	void
	 */
	public function setId($sId, $sValue = null) {
		$this->setAttribute('id', DataHelper::convertStringToId($sId, $sValue));
	}

	/**
	 * @brief	Contraint l'utilisateur à renseigner le champ du formulaire.
	 *
	 * @param	string	$bRequired
	 * @return	void
	 */
	public function setRequired($bRequired = true) {
		if ($bRequired) {
			$this->setAttribute('required', "required");
		} else {
			$this->setAttribute('required', null);
		}
	}

	/**
	 * @brief	Initialisation de la classe du champ.
	 *
	 * @param	string	$sClass
	 * @return	void
	 */
	public function setClass($sClass) {
		$this->setAttribute('class', $sClass);
	}

	/**
	 * @brief	Ajout d'une nouvelle classe au champ.
	 *
	 * @param	string	$sClass
	 * @return	void
	 */
	public function addClass($sClass) {
		$this->addAttribute('class', $sClass);
	}

	/**
	 * @brief	Initialisation des données.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function setData($xContent) {
		// Traitement des données
		$this->_data	= ($xContent instanceof HtmlHelper)		? $xContent->renderHTML()	: $xContent;
	}

	/**
	 * @brief	Ajout des données.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function addData($xContent) {
		// Traitement des données
		$this->_data	.= ($xContent instanceof HtmlHelper)	? $xContent->renderHTML()	: $xContent;
	}

	/**
	 * @brief	Suppression des données.
	 * @return	void
	 */
	public function clearData() {
		$this->_data	= "";
	}

	/**
	 * @brief	Initialisation du contenu après la balise.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function setAfter($xContent) {
		// Traitement des données
		$this->_after	= ($xContent instanceof HtmlHelper)		? $xContent->renderHTML()	: $xContent;
	}

	/**
	 * @brief	Ajout du contenu après la balise.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function addAfter($xContent) {
		// Traitement des données
		$this->_after	.= ($xContent instanceof HtmlHelper)	? $xContent->renderHTML()	: $xContent;
	}

	/**
	 * @brief	Suppression du contenu après la balise.
	 * @return	void
	 */
	public function clearAfter() {
		$this->_after	= "";
	}

	/**
	 * @brief	Initialisation du contenu avant la balise.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function setBefore($xContent) {
		// Traitement des données
		$this->_before	= ($xContent instanceof HtmlHelper)		? $xContent->renderHTML()	: $xContent;
	}
	
	/**
	 * @brief	Ajout du contenu avant la balise.
	 *
	 * @param	mixed	$xContent
	 * @return	void
	 */
	public function addBefore($xContent) {
		// Traitement des données
		$this->_before	.= ($xContent instanceof HtmlHelper)	? $xContent->renderHTML()	: $xContent;
	}

	/**
	 * @brief	Suppression du contenu avant la balise.
	 * @return	void
	 */
	public function clearBefore() {
		$this->_before	= "";
	}

	/**
	 * @brief	Création d'un élément LABEL.
	 *
	 * @param	string	$sLabel
	 * @param	string	$sClass
	 * @return	void
	 */
	public function setLabel($sLabel = null, $sClass = null) {
		// Construction de l'élément HTML
		$oLabel	= new HtmlHelper("label");
		$oLabel->setData($sLabel);
		$oLabel->setClass($sClass);
		$oLabel->setAttribute("for", $this->getAttribute('id'));

		// Ajout de l'élément aux données
		$this->addData($oLabel);
	}

	/**********************************************************************************************
	 * @todo	RENDU FINAL DE L'ÉLÉMENT
	 **********************************************************************************************/

	/**
	 * @brief	Rendu de la balise HTML.
	 *
	 * Le code HTML est renvoyé sous forme de chaîne de caractères.
	 * @return	string
	 */
	public function renderHTML() {
		// Construction des attributs
		$sAttributes	= "";
		if (DataHelper::isValidArray($this->_attribute)) {
			foreach ($this->_attribute as $sAttibut => $xValue) {
				if (!is_null($xValue)) {
					$sAttributes .= sprintf('%s="%s"', $sAttibut, implode(" ", (array) $xValue)) . chr(32);
				}
			}
		}

		// Initialisation du contenu HTML
		$sHTML = $this->_before;
		if ($this->_closing && $this->_dataPosition == self::APPEND) {
			// Balise auto-fermante avec DATA après la balise
			$sHTML .= sprintf('<%s %s/>%s', $this->_tag, $sAttributes, $this->_data);
		} elseif ($this->_closing && $this->_dataPosition == self::PREPEND) {
			// Balise auto-fermante avec DATA avant la balise
			$sHTML .= sprintf('%s<%s %s/>', $this->_data, $this->_tag, $sAttributes);
		} else {
			// Balise avec données
			$sHTML .= sprintf('<%s %s>%s</%s>', $this->_tag, $sAttributes, $this->_data, $this->_tag);
		}
		$sHTML .= $this->_after;

		// Renvoi du code HTML
		return $sHTML;
	}
}
