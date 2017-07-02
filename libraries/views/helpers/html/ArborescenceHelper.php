<?php
/**
 * Classe de création d'une arborescence dans l'application.
 *
 * @name		ArborescenceHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 53 $
 * @since		$LastChangedDate: 2017-07-02 02:29:58 +0200 (Sun, 02 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ArborescenceHelper {

	/**
	 * @brief	Constantes de position des champs dans la structure de $aListeItem.
	 * @var		string
	 */
	const		POSITION_ID				= 0;
	const		POSITION_PARENT			= 1;
	const		POSITION_LEFT			= 2;
	const		POSITION_RIGHT			= 3;

	const		POSITION_LABEL_INTERVAL	= 1;	// Position du champ 'label' dans la liste exploitant un PARENT
	const		POSITION_LABEL_PARENT	= 2;	// Position du champ 'label' dans la liste exploitant un INTERVAL

	/**
	 * @brief	Variables d'instance de la structure de la liste.
	 * @var		string
	 */
	private		$_idPosition			= self::POSITION_ID;
	private		$_idParentPosition		= self::POSITION_PARENT;
	private		$_leftPosition			= self::POSITION_LEFT;
	private		$_rightPosition			= self::POSITION_RIGHT;

	/**
	 * @brief	Paramètres de construction de l'arborescence.
	 * @var		string
	 */
	private		$_nNiveau				= 0;
	private		$_nCompteur				= 0;
	private		$_nBorneGauche			= 0;
	private		$_nBorneDroite			= 0;

	private		$_labelIntervalPosition	= self::POSITION_LABEL_INTERVAL;
	private		$_labelParentPosition	= self::POSITION_LABEL_PARENT;

	/**
	 * @brief	Options de construction de l'arborescence.
	 * @var		string
	 */
	private		$_readonly				= false;

	/**
	 * @brief	Liste des éléments sous forme de collection.
	 * @var		array
	 */
	private		$_aItems				= array();
	private		$_idActive				= null;

	/**
	 * @brief	Constructeur de la classe ArborescenceHelper.
	 *
	 * @param	string	$sName				: nom du champ dans le formulaire.
	 * @param	boolean	$bReadOnly			: nom du champ dans le formulaire.
	 */
	public function __construct($sName = "", $bReadOnly = true) {
		$this->_name					= $sName;
		$this->_readonly				= $bReadOnly;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setIdPosition($xIndex) {
		$this->_idPosition				= $xIndex;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setIdParentPosition($xIndex) {
		$this->_idParentPosition		= $xIndex;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setLeftPosition($xIndex) {
		$this->_leftPosition			= $xIndex;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setRightPosition($xIndex) {
		$this->_rightPosition			= $xIndex;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setLabelPositionInterval($xIndex) {
		$this->_labelIntervalPosition	= $xIndex;
	}

	/**
	 * @param	mixed	$xIndex				: Nom du champ ou occurrence dans la liste.
	 */
	public function setLabelPositionParent($xIndex) {
		$this->_labelParentPosition		= $xIndex;
	}

	/**
	 * @param	mixed	$nInteger			: Identifiant du groupe actif.
	 */
	public function setActiveById($nInteger) {
		$this->_idActive				= $nInteger;
	}

	/**
	 * @param	array	$aListeItems		: Liste sous forme d'arborescence.
	 */
	public function setListeItems($aListeItems = array()) {
		$this->_aItems					= $aListeItems;
	}

	/**
	 * @brief	Construction d'une arborescence à partir d'une liste exploitant les intervalles.
	 *
	 * @li Format de $aListeItems classé selon la borne gauche intervallaire.
	 * @code
	 *		// Liste à transformer en arborescence
	 *		$aListeItems = array(
	 *			0	=> array(0,		'public',	1,		2),
	 *			1	=> array(1,		'1',		3,		8),
	 *			2	=> array(2,		'1.1',		4,		5),
	 *			3	=> array(3,		'1.2',		6,		7),
	 *			4	=> array(4,		'2',		9,		28),
	 *			5	=> array(5,		'2.1',		10,		15),
	 *			6	=> array(8,		'2.2',		16,		27),
	 *			7	=> array(6,		'2.1.1',	11,		12),
	 *			8	=> array(7,		'2.1.2',	13,		14),
	 *			9	=> array(9,		'2.2.1',	17,		24),
	 *			10	=> array(13,	'2.2.2',	25,		26),
	 *			11	=> array(10,	'2.2.1.1',	18,		19),
	 *			12	=> array(11,	'2.2.1.2',	20,		21),
	 *			13	=> array(12,	'2.2.1.3',	22,		23)
	 *		);
	 *
	 *		// Format de l'arborescence en sortie
	 *		$aArborescence = array(
	 *			0	=> array('id' => 0,					'label'	=> 'public',	'items' => array()),
	 *			1	=> array('id' => 1,					'label'	=> '1',			'items' => array(
	 *				0	=> array('id' => 2,				'label'	=> '1.1',		'items' => array()),
	 *				1	=> array('id' => 3,				'label'	=> '1.2',		'items' => array())
	 *			)),
	 *			2	=> array('id' => 4,					'label'	=> '2',			'items' => array(
	 *				0	=> array('id' => 5,				'label'	=> '2.1',		'items' => array(
	 *					0	=> array('id' => 6,			'label'	=> '2.1.1',		'items' => array()),
	 *					1	=> array('id' => 7,			'label'	=> '2.1.1',		'items' => array())
	 * 				)),
	 *				1	=> array('id' => 8,				'label'	=> '2.2',		'items' => array(
	 *					0	=> array('id' => 9,			'label'	=> '2.2.1',		'items' => array(
	 *						0	=> array('id' => 10,	'label'	=> '2.2.1.1',	'items' => array()),
	 *						1	=> array('id' => 11,	'label'	=> '2.2.1.2',	'items' => array()),
	 *						2	=> array('id' => 12,	'label'	=> '2.2.1.3',	'items' => array())
	 * 					)),
	 *					1	=> array('id' => 13,		'label'	=> '2.2.2',		'items' => array())
	 * 				))
	 * 			))
	 *		);
	 * @endcode
	 * @param	array	$aListeItems		: Tableau BIDIMENSIONNEL exploitant des INTERVALLES.
	 */
	protected function _extractArborescenceFromIntervalles(array $aListeItems = array()) {
		// Initialisation de l'arborescence
		$aArborescence				= array();

		// Fonctionnalité réalisée pour chaque première entrée
		foreach ($aListeItems as $aItem) {
			// Initialisation des paramètres de l'entrée
			$id						= $aItem[$this->_idPosition];
			$parent					= null;
			$label					= $aItem[$this->_labelIntervalPosition];

			// Initialisation de l'entrée
			$aEntity				= array(
				'id'	=> $id,
				'label'	=> $label,
				'items'	=> array()
			);

			// Récupération des bornes de l'intervalle
			$left					= $aItem[$this->_leftPosition];
			$right					= $aItem[$this->_rightPosition];

			// Recherche d'un parent direct
			$bStop					= false;
			$nOccurrence			= count($aListeItems);
			// Parcours de la liste depuis la fin
			while (!$bStop && $nOccurrence > 0) {
				$nOccurrence--;
				// Fonctionnalité réalisée si l'élément PARENT a été trouvé
				if ($left > $aListeItems[$nOccurrence][$this->_leftPosition] && $right < $aListeItems[$nOccurrence][$this->_rightPosition]) {
					// Récupération de l'identifiant PARENT
					$parent	= $aListeItems[$nOccurrence][$this->_idPosition];
					$bStop	= true;
				}
			}

			// Fonctionnalité réalisée si un identifiant parent est trouvé
			if (!empty($parent)) {
				// Ajout de l'entrée au parent
				$aArborescence = DataHelper::arrayMerge($aArborescence, array('id' => $parent), array('items' => $aEntity));
			} else {
				// Initialisation de l'entrée
				$aArborescence[] = $aEntity;
			}
		}

		// Renvoi du résultat
		return $aArborescence;
	}

	/**
	 * @brief	Construction d'une arborescence à partir d'une liste exploitant des identifiants parents.
	 *
	 * @li Format de $aListeItems avec exploitation d'un identifiant parent.
	 * @code
	 *		// Liste à transformer en arborescence
	 *		$aListeItems = array(
	 *			0	=> array(0,		NULL,		'public'),
	 *			1	=> array(1,		NULL,		'1'),
	 *			2	=> array(4,		NULL,		'2'),
	 *			3	=> array(2,		1,			'1.1'),
	 *			4	=> array(3,		1,			'1.2'),
	 *			5	=> array(5,		4,			'2.1'),
	 *			6	=> array(8,		4,			'2.2'),
	 *			7	=> array(6,		5,			'2.1.1'),
	 *			8	=> array(7,		5,			'2.1.2'),
	 *			9	=> array(9,		8,			'2.2.1'),
	 *			10	=> array(13,	8,			'2.2.2'),
	 *			11	=> array(10,	9,			'2.2.1.1'),
	 *			12	=> array(11,	9,			'2.2.1.2'),
	 *			13	=> array(12,	9,			'2.2.1.3')
	 *		);
	 *
	 *		// Format de l'arborescence en sortie
	 *		$aArborescence = array(
	 *			0	=> array('id' => 0,					'label'	=> 'public',	'items' => array()),
	 *			1	=> array('id' => 1,					'label'	=> '1',			'items' => array(
	 *				0	=> array('id' => 2,				'label'	=> '1.1',		'items' => array()),
	 *				1	=> array('id' => 3,				'label'	=> '1.2',		'items' => array())
	 *			)),
	 *			2	=> array('id' => 4,					'label'	=> '2',			'items' => array(
	 *				0	=> array('id' => 5,				'label'	=> '2.1',		'items' => array(
	 *					0	=> array('id' => 6,			'label'	=> '2.1.1',		'items' => array()),
	 *					1	=> array('id' => 7,			'label'	=> '2.1.1',		'items' => array())
	 * 				)),
	 *				1	=> array('id' => 8,				'label'	=> '2.2',		'items' => array(
	 *					0	=> array('id' => 9,			'label'	=> '2.2.1',		'items' => array(
	 *						0	=> array('id' => 10,	'label'	=> '2.2.1.1',	'items' => array()),
	 *						1	=> array('id' => 11,	'label'	=> '2.2.1.2',	'items' => array()),
	 *						2	=> array('id' => 12,	'label'	=> '2.2.1.3',	'items' => array())
	 * 					)),
	 *					1	=> array('id' => 13,		'label'	=> '2.2.2',		'items' => array())
	 * 				))
	 * 			))
	 *		);
	 * @endcode
	 *
	 * @param	array	$aListeItems		: Tableau BIDIMENSIONNEL exploitant un IDENTIFIANT PARENT.
	 */
	protected function _extractArborescenceFromParents(array $aListeItems = array()) {
		// Initialisation de l'arborescence
		$aArborescence				= array();

		// Fonctionnalité réalisée pour chaque première entrée
		foreach ($aListeItems as $aItem) {
			// Initialisation des paramètres de l'entrée
			$id						= $aItem[$this->_idPosition];
			$parent					= $aItem[$this->_idParentPosition];
			$label					= $aItem[$this->_labelParentPosition];

			// Initialisation de l'entrée
			$aEntity				= array(
				'id'	=> $id,
				'label'	=> $label,
				'items'	=> array()
			);

			// Fonctionnalité réalisée si un identifiant parent est trouvé
			if (!empty($parent)) {
				// Ajout de l'entrée au parent
				$aArborescence = DataHelper::arrayMerge($aArborescence, array('id' => $parent), array('items' => $aEntity));
			} else {
				// Initialisation de l'entrée
				$aArborescence[] = $aEntity;
			}
		}

		// Renvoi du résultat
		return $aArborescence;
	}

	/**
	 * @brief	Construction d'une arborescence à partir d'une liste exploitant des sous-éléments.
	 *
	 * @li Format de $aListeItems avec exploitation d'une liste imbriquée.
	 * @code
	 *		// Liste à transformer en arborescence
	 *		$aListeItems = array(
	 *			0	=> array('public'				=> null),
	 *			1	=> array('1'					=> array(
	 *				2	=> array('1.1'				=> null),
	 *				3	=> array('1.2'				=> null)
	 *			)),
	 *			4	=> array('2'					=> array(
	 *				5	=> array('2.1'				=> array(
	 *					6	=> array('2.1.1'		=> null),
	 *					7	=> array('2.1.1'		=> null)
	 * 				)),
	 *				8	=> array('2.2'				=> array(
	 *					9	=> array('2.2.1'		=> array(
	 *						10	=> array('2.2.1.1'	=> null),
	 *						11	=> array('2.2.1.2'	=> null),
	 *						12	=> array('2.2.1.3'	=> null)
	 * 					)),
	 *					13	=> array('2.2.2'		=> null)
	 * 				))
	 * 			))
	 *		);
	 *
	 *		// Format de l'arborescence en sortie
	 *		$aArborescence = array(
	 *			0	=> array('id' => 0,					'label'	=> 'public',	'items' => array()),
	 *			1	=> array('id' => 1,					'label'	=> '1',			'items' => array(
	 *				0	=> array('id' => 2,				'label'	=> '1.1',		'items' => array()),
	 *				1	=> array('id' => 3,				'label'	=> '1.2',		'items' => array())
	 *			)),
	 *			2	=> array('id' => 4,					'label'	=> '2',			'items' => array(
	 *				0	=> array('id' => 5,				'label'	=> '2.1',		'items' => array(
	 *					0	=> array('id' => 6,			'label'	=> '2.1.1',		'items' => array()),
	 *					1	=> array('id' => 7,			'label'	=> '2.1.1',		'items' => array())
	 * 				)),
	 *				1	=> array('id' => 8,				'label'	=> '2.2',		'items' => array(
	 *					0	=> array('id' => 9,			'label'	=> '2.2.1',		'items' => array(
	 *						0	=> array('id' => 10,	'label'	=> '2.2.1.1',	'items' => array()),
	 *						1	=> array('id' => 11,	'label'	=> '2.2.1.2',	'items' => array()),
	 *						2	=> array('id' => 12,	'label'	=> '2.2.1.3',	'items' => array())
	 * 					)),
	 *					1	=> array('id' => 13,		'label'	=> '2.2.2',		'items' => array())
	 * 				))
	 * 			))
	 *		);
	 * @endcode
	 *
	 * @param	array	$aListeItems		: Tableau MULTIDIMENSIONNEL exploitant une IMBRICATION.
	 */
	protected function _extractArborescenceFromArrays($aListeItems = array()) {
		// Initialisation de l'arborescence
		$aArborescence			= array();
		$nOccurrence			= 0;

		// Fonctionnalité réalisée pour chaque première entrée
		foreach ($aListeItems as $nId => $aItems) {
			// Initialisation de la première entrée
			$aArborescence[$nOccurrence] = array('id' => $nId);

			// Fonctionnalité réalisée si une sous-arborescence est présente
			if (DataHelper::isValidArray($aItems)) {
				// Fonctionnalité réalisée pour chaque sous-entrée
				foreach ((array) $aItems as $sLabel => $aSubItems) {
					$aArborescence[$nOccurrence]['label'] = $sLabel;
					$aArborescence[$nOccurrence]['items'] = self::_extractArborescenceFromArrays($aSubItems);
				}
			}

			// Passage à l'occurrence suivante
			$nOccurrence++;
		}

		// Renvoi du résultat
		return $aArborescence;
	}

	/**
	 * @brief	Chargement de la liste à partir d'une référence intervallaire.
	 *
	 * @li Format de $aListeItems classé selon la borne gauche intervallaire.
	 * @code
	 *		// occurence	| id	| libellé	| left	| right
	 *		$aListeItems = array(
	 *			0	=> array(0,		'public',	1,		2),
	 *			1	=> array(1,		'1',		3,		8),
	 *			2	=> array(2,		'1.1',		4,		5),
	 *			3	=> array(3,		'1.2',		6,		7),
	 *			4	=> array(4,		'2',		9,		28),
	 *			5	=> array(5,		'2.1',		10,		15),
	 *			6	=> array(8,		'2.2',		16,		27),
	 *			7	=> array(6,		'2.1.1',	11,		12),
	 *			8	=> array(7,		'2.1.2',	13,		14),
	 *			9	=> array(9,		'2.2.1',	17,		24),
	 *			10	=> array(13,	'2.2.2',	25,		26),
	 *			11	=> array(10,	'2.2.1.1',	18,		19),
	 *			12	=> array(11,	'2.2.1.2',	20,		21),
	 *			13	=> array(12,	'2.2.1.3',	22,		23)
	 *		);
	 * @endcode
	 *
	 * @param	array	$aListeItems		: Tableau BIDIMENSIONNEL exploitant un IDENTIFIANT PARENT.
	 */
	public function setListeItemsFromIntervalles(array $aListeItems = array()) {
		// Transformation de la liste en arborescence
		$this->setListeItems($this->_extractArborescenceFromIntervalles($aListeItems));
	}

	/**
	 * @brief	Chargement de la liste à partir d'une référence parent.
	 *
	 * @li Format de $aListeItems classé selon l'identifiant parent.
	 * @code
	 *		// occurence	| id	| parent	| libellé
	 *		$aListeItems = array(
	 *			0	=> array(0,		NULL,		'public'),
	 *			1	=> array(1,		NULL,		'1'),
	 *			2	=> array(4,		NULL,		'2'),
	 *			3	=> array(2,		1,			'1.1'),
	 *			4	=> array(3,		1,			'1.2'),
	 *			5	=> array(5,		4,			'2.1'),
	 *			6	=> array(8,		4,			'2.2'),
	 *			7	=> array(6,		5,			'2.1.1'),
	 *			8	=> array(7,		5,			'2.1.2'),
	 *			9	=> array(9,		8,			'2.2.1'),
	 *			10	=> array(13,	8,			'2.2.2'),
	 *			11	=> array(10,	9,			'2.2.1.1'),
	 *			12	=> array(11,	9,			'2.2.1.2'),
	 *			13	=> array(12,	9,			'2.2.1.3')
	 *		);
	 * @endcode
	 *
	 * @param	array	$aListeItems		: Tableau BIDIMENSIONNEL exploitant un IDENTIFIANT PARENT.
	 */
	public function setListeItemsFromParents(array $aListeItems = array()) {
		// Transformation de la liste en arborescence
		$this->setListeItems($this->_extractArborescenceFromParents($aListeItems));
	}

	/**
	 * @brief	Chargement de la liste à partir d'un tableau imbriqué.
	 *
	 * @li Format de $aListeItems avec exploitation d'une imbrication de sous-éléments.
	 * @code
	 *		// id	=> libellé
	 *		$aListeItems = array(
	 *			0	=> array('public'				=> null),
	 *			1	=> array('1'					=> array(
	 *				2	=> array('1.1'				=> null),
	 *				3	=> array('1.2'				=> null)
	 *			)),
	 *			4	=> array('2'					=> array(
	 *				5	=> array('2.1'				=> array(
	 *					6	=> array('2.1.1'		=> null),
	 *					7	=> array('2.1.1'		=> null)
	 * 				)),
	 *				8	=> array('2.2'				=> array(
	 *					9	=> array('2.2.1'		=> array(
	 *						10	=> array('2.2.1.1'	=> null),
	 *						11	=> array('2.2.1.2'	=> null),
	 *						12	=> array('2.2.1.3'	=> null)
	 * 					)),
	 *					13	=> array('2.2.2'		=> null)
	 * 				))
	 * 			))
	 *		);
	 * @endcode
	 *
	 * @param	array	$aListeItems		: Tableau BIDIMENSIONNEL exploitant un IDENTIFIANT PARENT.
	 */
	public function setListeItemsFromArrays(array $aListeItems = array()) {
		// Transformation de la liste en arborescence
		$this->setListeItems($this->_extractArborescenceFromArrays($aListeItems));
	}

	/**
	 * @brief	Construction de l'arborescence au format HTML.
	 *
	 * @li	Calcul des bornes GAUCHE / DROITE de l'interval.
	 *
	 * @param	array	$aListeItems		: liste des champs.
	 * @param	array	$aButtons			: affichage d'un bouton d'ajout, d'édition ou de suppression.
	 * @return	string
	 */
	private function _buildArborescence($aListeItems = array(), $aButtons = array()) {
		// Construction de l'arborescence
		$sHtml			= "<ul class=\"arborescence\" id=\"niveau-" . $this->_nNiveau . "\">";

		// Parcours de l'arborescence
		foreach ($aListeItems as $aEntity) {
			// Compteur suivant
			$this->_nCompteur++;

			// Initialisation de la borne GAUCHE
			$nCurrentBorneGauche		= ++$this->_nBorneGauche;
			$sHtml		.= "	<li class=\"branche\" id=\"item-" . $this->_nCompteur . "\">";

			// Ajout du libellé
			$sLabel		= $aEntity['label'];
			if (!$this->_readonly && isset($aButtons['edit'])) {
				$sLabel	= "<a href=\"" . sprintf($aButtons['edit'], $aEntity['id']) . "\" class=\"link strong blue\">" . $sLabel . "</a>";
			}

			// Construction du libellé
			$sHtml		.= "		<span class=\"titre strong\">" . $sLabel . "</span>";

			// Passage au niveau suivant
			$this->_nNiveau++;
			$sUnderHtml	= "";
			// Fonctionnalité réalisée si une sous-arborescence est présente
			if (DataHelper::isValidArray($aEntity['items'])) {
				// Construction récursive de la sous-arborescence
				$sUnderHtml	.= $this->_buildArborescence($aEntity['items'], $aButtons);
			} else {
				// Construction d'une sous-arborescence vide
				$sUnderHtml	.= "<ul class=\"arborescence\" id=\"niveau-" . $this->_nNiveau . "\">
								<li class=\"branche empty\" id=\"item-" . $this->_nCompteur . "\">
									<span>&nbsp;</span>
								</li>
							</ul>";
			}
			// Retour au niveau précédent
			$this->_nNiveau--;

			// Calcul des bornes suivantes
			$nCurrentBorneDroite	= ++$this->_nBorneGauche;
			$sHtml		.= "		<input type=\"hidden\" name=\"borne_gauche[]\" value=\"" . $nCurrentBorneGauche . "\" />
									<input type=\"hidden\" name=\"borne_droite[]\" value=\"" . $nCurrentBorneDroite . "\" />
									" . $sUnderHtml . "
								</li>";
		}
		// Fin de l'arborescence
		return $sHtml	. "</ul>";
	}

	/**
	 * @brief	Rendu de l'élément.
	 *
	 * @param	array	$aButtons			: affichage d'un bouton d'ajout, d'édition ou de suppression.
	 * @code
	 * 		$aButtons = array('edit' => "url?id=%s", 'add' => "url?id=%s", 'delete' => "url?id=%s");
	 * @endcode
	 */
	public function renderHTML($aButtons = array()) {
		// Fonctionnalité réalisée si la modification du contenu est autorisée
		if (!$this->_readonly) {
			// Ajout de la feuille de style
			ViewRender::addToStylesheet(FW_VIEW_STYLES . "/ArborescenceHelper.css");

			// Compression du script avec JavaScriptPacker
			ViewRender::addToScripts(FW_VIEW_SCRIPTS . "/ArborescenceHelper.js");
		}

		// Initialisation du contenu HTML
		$sHTML		= "<span class=\"span titre left blue\">\</span><br />";
		$sHTML		.= $this->_buildArborescence($this->_aItems, $aButtons);

		// Renvoi du code HTML
		return $sHTML;
	}
}
