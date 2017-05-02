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
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
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

	private		$_tag			= null;
	private		$_attribute		= array();
	private		$_closing		= false;
	private		$_dataPosition	= self::APPEND;

	protected	$_data			= null;

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
	public static function buildListOptions($aListe, $xValues = array(), $sFirst = null, $sTri = null, $bDisabled = false, $sFormat = "<option %s value=\"%s\" >%s</option>") {
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

			default:
				break;
		}

		// Création de la première entrée
		$sPrefix = null;
		if (isset($sFirst) && !$bDisabled) {
			$sPrefix = self::buildListOptions(array(0 => $sFirst), array(), null, null, $bDisabled, $sFormat);
		}

		// Initialisation de la liste
		$sOptions = "";
		if (DataHelper::isValidArray($aListe)) {
			foreach ($aListe as $nId => $sLibelle) {
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
			$sPrefix = self::buildListOptions(array(0 => '-'), array(), null, null, false, $sFormat);
		}

		return $sPrefix . $sOptions;
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
		$this->_attribute[$sName] = $xValue;
	}

	/**
	 * @brief	Récupération de l'attribut de la balise
	 * @param	string	$sName				: nom de l'attribut de la balise
	 * @return	mixed
	 */
	public function getAttribute($sName) {
		$xValue = null;
		if (isset($this->_attribute[$sName])) {
			$xValue = $this->_attribute[$sName];
		}
		return $xValue;
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
	 * @brief	Initialisation des données.
	 *
	 * @param	mixed	$xData
	 * @return	void
	 */
	public function setData($xData) {
		// Traitement des données
		$this->_data .= ($xData instanceof HtmlHelper)	? $xData->renderHTML()	: $xData;
	}

	/**
	 * @brief	Suppression des données.
	 * @return	void
	 */
	public function clearData() {
		$this->_data = null;
	}

	/**
	 * @brief	Initialisation des données.
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
		$this->setData($oLabel);
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

		$sHTML = "";
		if ($this->_closing && $this->_dataPosition == self::APPEND) {
			// Balise auto-fermante avec DATA après la balise
			$sHTML = sprintf('<%s %s/>%s', $this->_tag, $sAttributes, $this->_data);
		} elseif ($this->_closing && $this->_dataPosition == self::PREPEND) {
			// Balise auto-fermante avec DATA avant la balise
			$sHTML = sprintf('%s<%s %s/>', $this->_data, $this->_tag, $sAttributes);
		} else {
			// Balise avec données
			$sHTML = sprintf('<%s %s>%s</%s>', $this->_tag, $sAttributes, $this->_data, $this->_tag);
		}

		// Renvoi du code HTML
		return $sHTML;
	}
}
