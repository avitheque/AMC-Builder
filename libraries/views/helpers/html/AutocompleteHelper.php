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
 * @version		$LastChangedRevision: 89 $
 * @since		$LastChangedDate: 2017-12-27 00:05:27 +0100 (Wed, 27 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class AutocompleteHelper extends InputHelper {

	/**
	 * @brief	Nom des champs de construction de la liste JSON.
	 * @var		string
	 */
	const		KEY				= "code";
	const		LABEL			= "label";

	protected	$_id			= null;
	protected	$_hidden		= null;
	protected	$_liste			= array();
	protected	$_json			= array();

	/**
	 *
	 * @param	string	$sName		: nom du champ dans le formulaire.
	 * @param	array	$aListe		: liste des valeurs possibles.
	 * @param	string	$sValue		: valeur de la saisie
	 * @param	string	$bStrict	: (optionnel) la valeur du champ doit être présent dans la liste.
	 */
	public function __construct($sName = "", $aListe = array(), $sValue = null, $bStrict = true) {
		// Fonctionnalité réalisée si la saisie est stricte
		if ($bStrict && DataHelper::isValidArray($aListe) && !empty($sValue)) {
			// Recherche si le libellé existe
			$aKeys	= array_keys($aListe, $sValue, $bStrict);
			$nKey	= DataHelper::get($aKeys, 0);
			if (!empty($nKey) && array_key_exists($nKey, $aListe)) {
				// La valeur saisie correspond à un libellé du tableau
				$sValue = $aListe[$nKey];
			} elseif (array_key_exists($sValue, $aListe)) {
				// La valeur saisie correspond à une clé du tableau
				$sValue = $aListe[$sValue];
			} else {
				// Sinon NULL
				$sValue = null;
			}
		}
		// Construction du LABEL permettant d'ouvrir la liste disponible
		parent::__construct($sName, $sValue, "text");
		$this->addLabel("&#9013;", "drop-down");

		// Fonctionnalité réalisée si la liste AUTOCOMPLETE est valide
		$this->_list = $aListe;
		if (DataHelper::isValidArray($aListe)) {
			foreach ($aListe as $sValue => $sLabel) {
				$this->_json[] = sprintf('{' . self::KEY . ': "%s", ' . self::LABEL . ': "%s"}', $sValue, $sLabel);
			}
		}
	}

	/**
	 * @brief	Nom du champ caché
	 *
	 * @param	string	$sName
	 * @return	void
	 */
	public function setHiddenInputName($sName) {
		$this->_hidden	= $sName;
	}

	/**
	 * @brief	Récupère la valeur du champ caché
	 * 
	 * @return	mixed
	 */
	public function getHiddenKey() {
		$sHiddenKey = null;
		if (DataHelper::isValidArray($this->_list)) {
			// Récupération de la liste des clés correspondantes à la saisie
			$aExists	= array_keys($this->_list, $this->getAttribute('value'));
			// Récupération de la clé
			$sHiddenKey = isset($aExists[0]) ? $aExists[0] : null;
		}
		
		return $sHiddenKey;
	}

	/**
	 * @brief	Rendu de l'élément.
	 *
	 * @see InputHelper::renderHTML()
	 */
	public function renderHTML() {
		// Récupération de l'identifiant
		$this->_id = $this->getAttribute('id');

		// Ajout de la feuille de style
		ViewRender::addToStylesheet(FW_VIEW_STYLES . "/AutocompleteHelper.css");

		// Script d'activation du plugin jQuery AutoComplete
		$sJQuery = '// Déclaration de la liste des éléments au format JSON
					var ' . $this->_id . '_liste = [' . implode(",", $this->_json) . '];

					// Activation du plugin jQuery.autoComplete()
					$("#' . $this->_id . '").autocomplete({
						source: 	' . $this->_id . '_liste,
						select:		function(event, ui) {
							$("#' . $this->_hidden . '").val(ui.item.' . self::KEY . ');
							$("#' . $this->_id . '").val(ui.item.' . self::LABEL . ');
						}
					});

					// Affichage de la liste complète
					$("label[for=' . $this->_id . ']").click(function() {
						$("#' . $this->_id . '").autocomplete("search", " ");
					});

					// Modification du champ sans passer par AutoComplete
					$("#' . $this->_id . '").change(function() {
						var hiddenKey = "";
						for (i in ' . $this->_id . '_liste) {
							// Recherche
							if ($(this).val() == ' . $this->_id . '_liste[i]["' . self::LABEL . '"]) {
								hiddenKey = ' . $this->_id . '_liste[i]["' . self::KEY . '"];
							}
						}
						$("#' . $this->_hidden . '").val(hiddenKey);
					});';

		// Compression du script avec JavaScriptPacker
		ViewRender::addToJQuery($sJQuery);

		// Construction du champ de saisie avec le label indissociables
		$sHTML = "<span class=\"no-wrap\">" . parent::renderHTML() . "</span>";

		// Récupération de la valeur sélectionnée
		$sHiddenKey = $this->getHiddenKey();

		// Ajout du champ caché pour la sélection
		$oHidden = new InputHelper($this->_hidden, $sHiddenKey, "hidden");
		$oHidden->setId($this->_hidden);
		$sHTML .= $oHidden->renderHTML();

		// Renvoi du code HTML
		return $sHTML;
	}
}
