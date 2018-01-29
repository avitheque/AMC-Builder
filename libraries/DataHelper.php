<?php
/** @brief	Classe de vérification de types.
 *
 * Cette classe permet de vérifier la validité des valeurs par rapport à leur type déclaré.
 * Elle est utilisée pour vérifier que les paramètre GETou POST transmis correspondent bien aux
 * type attendu, proposant ainsi un premier pallier de sécurité face aux SQL injections.
 *
 * @name		DataHelper
 * @package		Helpers
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 105 $
 * @since		$LastChangedDate: 2018-01-29 18:47:23 +0100 (Mon, 29 Jan 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class DataHelper {

	const DEFAULT_TIMEZONE		= "Europe/Paris";

	const ARRAY_SEPARATOR		= ",";

	const DATA_TYPE_NONE		= -1;			# Type non pris en compte
	const DATA_TYPE_ANY			= 0;			# Type quelconque, pris tel quel
	const DATA_TYPE_BOOL		= 1;			# Type Booléen
	const DATA_TYPE_MYBOOL		= 2;			# Type Booléen au format MySQL		(TINYINT 0/1)
	const DATA_TYPE_INT			= 3;			# Type Entier
	const DATA_TYPE_INT_ABS		= 4;			# Type Entier en valeur absolue		(SIGNED)
	const DATA_TYPE_FLT			= 5;			# Type Float au format FR avec séparateur décimal [,]
	const DATA_TYPE_FLT_ABS		= 6;			# Type Float en valeur absolue au format FR avec séparateur décimal [,]
	const DATA_TYPE_MYFLT		= 7;			# Type Float au format MySQL avec séparateur décimal [.]
	const DATA_TYPE_MYFLT_ABS	= 8;			# Type Float en valeur absolue au format MySQL avec séparateur décimal [.]
	const DATA_TYPE_ARRAY		= 9;			# Type Array
	const DATA_TYPE_MYARRAY		= 10;			# Type Array au format MySQL [item1|item2|item3]
	const DATA_TYPE_MYARRAY_NUM	= 11;			# Type Array au format MySQL [item1|item2|item3] mais uniquement avec des valeurs numériques
	const DATA_TYPE_STR			= 12;			# Type Chaîne
	const DATA_TYPE_NAME		= 13;			# Type nom (que des lettres, lettres accentuées, espace, tiret, apostrophe)
	const DATA_TYPE_CLASSID		= 14;			# Type identifiant de classe (que des lettre, pas d'espace)
	const DATA_TYPE_TXT			= 15;			# Type Texte pouvant être vide par défaut
	const DATA_TYPE_MYTXT		= 16;			# Type Texte au format MySQL
	const DATA_TYPE_HTML		= 17;			# Type Texte au format HTML
	const DATA_TYPE_LATEX		= 18;			# Type Texte au format LaTeX
	const DATA_TYPE_DATE		= 19;			# Type Date au format FR	[d/m/Y]
	const DATA_TYPE_MYDATE		= 20;			# Type Date au format MySQL	[Y-m-d]
	const DATA_TYPE_TIME		= 21;			# Type Time au format FR	[H:i]
	const DATA_TYPE_DATETIME	= 22;			# Type Date-Time sous MySQL [Y-m-d H:i:s]
	const DATA_TYPE_PDF			= 23;			# Type texte PDF

	/** @brief	Vérifie le type d'une valeur
	 *
	 * Cette fonction statique vérifie si la valeur transmise est bein d'un type donné.
	 * @param	mixed		$xValue			: valeur de la variable.
	 * @param	integer		$nType			: code type (voir constante DATA_TYPE_*)
	 * @return	boolean traduisant la cohérence de la valeur par rapport au type
	 */
	public static function checkValue($xValue = null, $nType = null) {
		switch ($nType) {
			case self::DATA_TYPE_INT:
				return preg_match('@^-?[0-9]+$@', strval($xValue));
			break;

			case self::DATA_TYPE_NAME:
				return preg_match('@^[a-zàäâìïîùüûéêèëòöô][-\' a-zàäâìïîùüûéêèëòöô]*$@', $xValue);
			break;

			case self::DATA_TYPE_DATE:
				return preg_match('@^[0-9]{2,4}[-/][0-9]{2}[-/][0-9]{2,4}$@', $xValue);
			break;

			case self::DATA_TYPE_TIME:
				return preg_match('@^[0-9]{2}[:][0-9]{2}$@', $xValue);
			break;

			case self::DATA_TYPE_DATETIME:
				return preg_match('@^[0-9]{2,4}[-/][0-9]{2}[-/][0-9]{2,4} [0-9]{2}:[0-9]{2}:[0-9]{2}$@', $xValue);
			break;

			case self::DATA_TYPE_CLASSID:
				return preg_match('@^[a-z][a-z0-9\\._]*$@', $xValue);
			break;

			case self::DATA_TYPE_MYDATE:
				return preg_match('@^[0-9]{4}-[0-9]{2}-[0-9]{2}$@', $xValue);
			break;

			default:
				return true;
			break;
		}
	}

	/**
	 * @brief	Validation d'un tableau.
	 * Méthode permettant de vérifier si un élément de type tableau comporte au moins une occurence.
	 * @param	array		$aArray			: tableau à vérifier.
	 * @param	integer		$nCount			: (facultatif) contrainte du nombre d'occurence(s) attendu.
	 * @return	bool renvoie TRUE si l'élément comporte au moins une occurence par défaut.
	 */
	static function isValidArray($aArray, $nCount = null) {
		// Initialisation du résultat
		$bValide = false;
		if (!is_null($aArray) && (is_array($aArray) || is_object($aArray)) && count($aArray) > 0) {
			// Fonctionnalité réalisée si le nombre d'élément attendu est valide
			if (!is_null($nCount)) {
				// Fonctionnalité réalisée si le nombre d'occurrence attendue est valide
				$bValide = count($aArray) == $nCount;
			} else {
				// Fonctionnalité réalisée si le tableau n'est pas vide
				$bValide = count($aArray) >= 1;
			}
		}
		// Renvoi du résultat
		return $bValide;
	}

	/** @brief	Validation d'un numérique.
	 * Méthode permettant de vérifier la validité d'un numérique.
	 * @param	string		$sString		: chaîne de caractères représentant un nombre.
	 * @param	boolean		$bEmpty			: (optionnel) accepte la valeur [0].
	 * @return	bool résultat de la vérification.
	 */
	static function isValidNumeric($sString, $bEmpty = true) {
		// Initialisation du résultat
		$bValid = false;
		// Fonctionnalité réalisée si la chaîne ne commence pas par [0]
		if (preg_match('@^[-+]{0,1}[1-9]+[0-9]*$@', $sString)) {
			$bValid = true;
		} elseif ($bEmpty) {
			$bValid = empty($sString);
		} else {
			$bValid = false;
		}
		// Renvoi du résultat
		return $bValid;
	}

	/** @brief	Validation d'un bouléen.
	 * Méthode permettant de vérifier la validité d'un bouléen.
	 * @param	char|bool	$bChar			: caractère ou booléen à vérifier.
	 * @param	bool		$bNumeric		: (optionnel) accepte les valeurs [0|1].
	 * @return	bool résultat de la vérification.
	 */
	static function isValidBoolean($bChar, $bNumeric = true) {
		// Initialisation du résultat
		$bValid = false;
		// Fonctionnalité réalisée si le bouléen est au format numérique [0|1]
		if ($bNumeric && ($bChar === 0 || $bChar === 1)) {
			$bValid = true;
		} elseif ($bChar === false || $bChar === true) {
			$bValid = true;
		}
		// Renvoi du résultat
		return $bValid;
	}

	/** @brief	Validation d'un jour.
	 * Méthode permettant de vérifier la validité d'un jour par son numéro.
	 * @param	integer		$nNumber		: numéro du jour à tester.
	 * @return	bool résultat de la vérification.
	 */
	static function isValidDay($nNumber) {
		return $nNumber >= 1 && $nNumber <= 31;
	}

	/** @brief	Validation d'un mois.
	 * Méthode permettant de vérifier la validité d'un mois par son numéro.
	 * @param	integer		$nNumber		: numéro du mois à tester.
	 * @return	bool résultat de la vérification.
	 */
	static function isValidMonth($nNumber) {
		return $nNumber >= 1 && $nNumber <= 12;
	}

	/** @brief	Validation d'une date.
	 * Méthode permettant de vérifier la validité d'une date.
	 * @param	string		$sString		: chaîne de caractères représentant une date.
	 * @param	bool		$bMySQL			: (optionnel) si la chaîne est au format [Y-m-d].
	 * @return	bool résultat de la vérification.
	 */
	static function isValidDate($sString, $bMySQL = false) {
		$bValide = false;

		// Fonctionnalité réalisée si la date est au format MySQL
		if ($bMySQL && preg_match('@^([0-9]{2,4})\-([0-9]{1,2})\-([0-9]{1,2})$@', $sString, $aMatched)) {
			// Test le format de DATE [Y-m-d]
			$bValide = self::isValidMonth($aMatched[2]) && self::isValidDay($aMatched[3]);
		} elseif (!$bMySQL && preg_match('@^([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})$@', $sString, $aMatched)) {
			// Test le format de DATE [d/m/Y]
			$bValide = self::isValidDay($aMatched[1]) && self::isValidMonth($aMatched[2]);
		}

		// Renvoi du résultat
		return $bValide;
	}

	/** @brief	Validation d'une chaîne.
	 * Méthode permettant de vérifier la validité d'une chaîne de caractères.
	 * @param	string		$sString		: chaîne de caractères représentant une chaîne.
	 * @param	bool		$bNotEmpty		: (optionnel) si la chaîne ne peut être vide.
	 * @return	bool résultat de la vérification.
	 */
	static function isValidString($sString, $bNotEmpty = false) {
		$bValide = true;
		if ($bNotEmpty) {
			$sVerif	= trim($sString);
			$bValide = !empty($sString) && !empty($sVerif) && !self::isValidNumeric($sString);
		}
		// Fonctionnalité réalisée si la chaîne ne commence pas par [0]
		return is_string($sString) && $bValide;
	}


	/** @brief	Validation d'une comparaison entre deux valeurs.
	 * Méthode permettant de vérifier la validité d'une opération.
	 * @param	mixed		$xValueLEFT		: valeur de référence.
	 * @param	string		$sOperator		: opérateur.
	 * @param	mixed		$xValueRIGHT	: valeur de comparaison.
	 * @return	bool résultat de la vérification.
	 */
	static function isValidOperation($xValueLEFT, $sOperator, $xValueRIGHT) {
		switch (trim($sOperator)) {
			case ">":
				$bTest	= $xValueLEFT > $xValueRIGHT;
				break;

			case ">=":
				$bTest	= $xValueLEFT >= $xValueRIGHT;
				break;

			case "=":
			case "==":
				$bTest	= $xValueLEFT == $xValueRIGHT;
				break;

			case "===":
				$bTest	= $xValueLEFT === $xValueRIGHT;
				break;

			case "<=":
				$bTest	= $xValueLEFT <= $xValueRIGHT;
				break;

			case "<":
				$bTest	= $xValueLEFT < $xValueRIGHT;
				break;

			case "<>":
			case "!=":
				$bTest	= $xValueLEFT != $xValueRIGHT;
				break;

			default:
				$bTest	= false;
				break;
		}

		return $bTest;
	}

	/** @brief	Converti un nombre en TIME
	 *
	 * @param	string		$sString		: chaîne de caractères représentant un nombre ou une heure.
	 * @return	string, résultat de la convertion au format [H:i].
	 */
	static function numberToTime($sString = 0) {
		// Initialisation des valeurs par défaut
		$nHeure			= 0;
		$nMinute		= 0;

		if (preg_match('@^([0-9]+)\:([0-9]+).*@', $sString, $aMatches)) {
			// Base 60
			$nHeure		= (int) $aMatches[1];
			$nMinute	= (int) $aMatches[2];
		} elseif (preg_match('@^([0-9]+)[\.\,]([0-9]+)@', $sString, $aMatches)) {
			// Base 10
			$nHeure		= (int) $aMatches[1];
			$nMinute	= (int) $aMatches[2] * 6;
		}

		// Renvoi du résultat au format [H:i]
		return sprintf('%02d:%02d', $nHeure, $nMinute);
	}


	/** @brief	Converti une date Francaise au format MySQL
	 *
	 * @todo	ATTENTION au passage au 31/12/2032...
	 *
	 * La fonction transforme une date [JJ/MM/AAAA] au format [YYYY-MM-DD]
	 * @param	date		$sDate			: chaîne de caractères formant une date jj/mm/aaaa.
	 * @return	chaîne de caractères représentant la date, au format MySQL.
	 */
	public static function dateFrToMy($sDate) {
		if (preg_match('@^([0-9]{2}).([0-9]{2}).([0-9]{4})@', $sDate, $aMatches)) {
			return sprintf('%d-%02d-%02d', (int) $aMatches[3], (int) $aMatches[2], (int) $aMatches[1]);
		} else {
			// Le paramètre est déjà au bon format
			return	$sDate;
		}
	}

	/** @brief	Converti une date MySQL au format Français
	 *
	 * @todo	ATTENTION au passage au 31/12/2032...
	 *
	 * La fonction transforme une date [YYYY-MM-DD] au format [JJ/MM/AAAA]
	 * @param	date		$sDate			: chaîne de caractères formant une date Y-m-d.
	 * @return	chaîne de caractères représentant la date, au format FR.
	 */
	public static function dateMyToFr($sDate) {
		if (preg_match('@^([0-9]{4}).([0-9]{2}).([0-9]{2})@', $sDate, $aMatches)) {
			return sprintf('%02d/%02d/%d', (int) $aMatches[3], (int) $aMatches[2], (int) $aMatches[1]);
		} else {
			// Le paramètre est déjà au bon format
			return	$sDate;
		}
	}

	/** @brief	Converti un dateTime Français au format MySQL
	 *
	 * @todo	ATTENTION au passage au 2032-12-31...
	 *
	 * La fonction transforme une date [JJ/MM/AAAA H:i:s] au format [YYYY-MM-DD H:i:s]
	 * @param	date		$sDateTime		: chaîne de caractères formant une date Y-m-d H:i:s.
	 * @param	string		$sFormat		: chaîne de caractères correspondant au format attendu, par défaut [d/m/Y H:i:s].
	 * @return	chaîne de caractères représentant la date, au format FR.
	 */
	public static function dateTimeFrToMy($sDateTime, $sFormat = "Y-m-d H:i:s") {
		if (preg_match('@^([0-9]+)\/([0-9]+)\/([0-9]+)\s([0-9]+):([0-9]+):*([0-9]*)@', $sDateTime, $aMatches)) {
			return date($sFormat, mktime((int) $aMatches[4], (int) $aMatches[5], (int) $aMatches[6], (int) $aMatches[2], (int) $aMatches[1], (int) $aMatches[3]));
		} else {
			// Le paramètre est déjà au bon format
			return	$sDateTime;
		}
	}

	/** @brief	Converti un dateTime MySQL au format Français
	 *
	 * @todo	ATTENTION au passage au 31/12/2032...
	 *
	 * La fonction transforme une date [YYYY-MM-DD H:i:s] au format [JJ/MM/AAAA H:i:s]
	 * @param	date		$sDateTime		: chaîne de caractères formant une date Y-m-d H:i:s.
	 * @param	string		$sFormat		: chaîne de caractères correspondant au format attendu, par défaut [d/m/Y H:i:s].
	 * @return	chaîne de caractères représentant la date, au format FR.
	 */
	public static function dateTimeMyToFr($sDateTime, $sFormat = "d/m/Y H:i:s") {
		if (preg_match('@^([0-9]+)\-([0-9]+)\-([0-9]+)\s([0-9]+):([0-9]+):*([0-9]*)@', $sDateTime, $aMatches)) {
			return date($sFormat, mktime((int) $aMatches[4], (int) $aMatches[5], (int) $aMatches[6], (int) $aMatches[2], (int) $aMatches[3], (int) $aMatches[1]));
		} else {
			// Le paramètre est déjà au bon format
			return	$sDateTime;
		}
	}

	/** @brief	Converti un TIMESTAMP au format MySQL
	 *
	 * @todo	ATTENTION au passage au 2032-12-31...
	 *
	 * La fonction transforme un TIMESTAMP au format [YYYY-MM-DD H:i:s]
	 * @param	timestamp	$nTimeStamp		: valeur du TIMESTAMP, s'il est NULL l'heure du système est pris en charge.
	 * @param	string		$sFormat		: chaîne de caractères correspondant au format attendu, par défaut [d/m/Y H:i:s].
	 * @return	chaîne de caractères représentant la date, au format FR.
	 */
	public static function timesampToMyDatetime($nTimeStamp = null, $sFormat = "Y-m-d H:i:s") {
		// Fonctionnalité réalisée si le paramètre est vide
		if (!DataHelper::isValidNumeric($nTimeStamp, false)) {
			$nTimeStamp = mktime(0, 0, 0, 0, 0, 0);
		}
		// Renvoi du résultat
		return date($sFormat, $nTimeStamp);
	}

	/**
	 * @brief	Vérifie si le format d'une date saisie est jj/mm/aaaa.
	 *
	 * @param	date		$sDate			: chaîne de caractères formant une date jj/mm/aaaa.
	 * @return	boolean true si la date est correcte au format jj/mm/aaaa.
	 */
	public static function isDateString($sDate) {
		if (! isset($sDate) || $sDate == "") {
			return false;
		}

		list($nDay, $nMonth, $nYear) = explode("/", $sDate);
		if ($nDay != "" && $nMonth != "" && $nYear != "") {
			if (is_numeric($nYear) && is_numeric($nMonth) && is_numeric($nDay)) {
				return checkdate($nMonth, $nDay, $nYear);
			}
		}
		return false;
	}

	/**
	 * @brief	Récupère l'heure système avec les millisecondes.
	 *
	 * @li	Possibilité de ne récupérer que la partie TIMESTAMP.
	 *
	 * @param	boolean		$bMillisecond	: récupération des millisecondes.
	 * @param	integer		$nDecimales		: nombre de décimales.
	 * @return	float TIMESTAMP avec les millisecondes en partie décimale.
	 */
	public static function getTime($bMillisecond = true, $nDecimales = 3) {
		// Extraction de l'heure système sous forme array(MILLISECONDES, TIMESTAMP)
		$aTime = explode(' ', microtime());

		// Récupération du TIMESTAMP
		$fTime	= (int) $aTime[1];

		// Ajout des millisecondes au timestamp
		if ($bMillisecond) {
			// Ajout de la partie des millisecondes en décimales
			$fTime += $aTime[0];
		}

		// Renvoi du résultat
		return $fTime;
	}

	/**
	 * @brief	Remplacement des caractères HTML
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 *
	 * Constantes des correspondances entre les caractères spéciaux et leurs équivalent alphabétique.
	 * @code
	 * 		"&#10;"		=> '\n',
	 * 		"&#13;"		=> '\n',
	 *
	 * 		"&#33;"		=> '!',
	 * 		"&#34;"		=> '"',
	 * 		"&#35;"		=> '#',
	 * 		"&#36;"		=> '$',
	 * 		"&#37;"		=> '%',
	 * 		"&#38;"		=> '&',
	 * 		"&#39;"		=> "'",
	 * 		"&#40;"		=> '(',
	 * 		"&#41;"		=> ')',
	 * 		"&#42;"		=> '*',
	 * 		"&#43;"		=> '+',
	 * 		"&#44;"		=> ',',
	 * 		"&#45;"		=> '-',
	 * 		"&#46;"		=> '.',
	 * 		"&#47;"		=> '/',
	 *
	 * 		"&#58;"		=> ':',
	 * 		"&#59;"		=> ';',
	 * 		"&#60;"		=> '<',
	 * 		"&#61;"		=> '=',
	 * 		"&#62;"		=> '>',
	 * 		"&#63;"		=> '?',
	 * 		"&#64;"		=> '@',
	 *
	 * 		"&#94;"		=> '^',
	 * 		"&#95;"		=> '_',
	 * 		"&#96;"		=> '`',
	 *
	 * 		"&#123;"	=> '{',
	 * 		"&#124;"	=> '|',
	 * 		"&#125;"	=> '}',
	 * 		"&#126;"	=> '~',
	 *
	 * 		"&#150;"	=> '–',
	 *
	 * 		"&#161;"	=> '¡',
	 * 		"&#162;"	=> '¢',
	 * 		"&#163;"	=> '£',
	 * 		"&#164;"	=> '¤',
	 * 		"&#165;"	=> '¥',
	 * 		"&#166;"	=> '¦',
	 * 		"&#167;"	=> '§',
	 * 		"&#168;"	=> '¨',
	 * 		"&#169;"	=> '©',
	 * 		"&#170;"	=> 'ª',
	 * 		"&#171;"	=> '«',
	 * 		"&#172;"	=> '¬',
	 * 		"&#174;"	=> '®',
	 * 		"&#175;"	=> '¯',
	 * 		"&#176;"	=> '°',
	 * 		"&#177;"	=> '±',
	 * 		"&#178;"	=> '²',
	 * 		"&#179;"	=> '³',
	 * 		"&#180;"	=> '´',
	 * 		"&#181;"	=> 'µ',
	 * 		"&#182;"	=> '¶',
	 * 		"&#183;"	=> '·',
	 * 		"&#184;"	=> '¸',
	 * 		"&#185;"	=> '¹',
	 * 		"&#186;"	=> 'º',
	 * 		"&#187;"	=> '»',
	 * 		"&#188;"	=> '¼',
	 * 		"&#189;"	=> '½',
	 * 		"&#190;"	=> '¾',
	 * 		"&#191;"	=> '¿',
	 *
	 * 		"&#247;"	=> '÷',
	 *
	 * 		"&#8211;"	=> '–',
	 * 		"&#8212;"	=> '—',
	 *
	 * 		"&#8216;"	=> '‘',
	 * 		"&#8217;"	=> '’',
	 * 		"&#8218;"	=> '‚',
	 *
	 * 		"&#8220;"	=> '“',
	 * 		"&#8221;"	=> '”',
	 * 		"&#8222;"	=> '„',
	 *
	 * 		"&#8224;"	=> '†',
	 * 		"&#8225;"	=> '‡',
	 * 		"&#8226;"	=> '•',
	 *
	 * 		"&#8230;"	=> '…',
	 *
	 * 		"&#8240;"	=> '‰',
	 *
	 * 		"&#8264;"	=> '€',
	 *
	 * 		"&#8282;"	=> '™',
	 * @endcode
	 * @var		array
	 */
	public static $HTML_REPLACE	= array(
		"<br />"	=> "&#10;",
		"\n"		=> "&#10;",

		"`"			=> "&#39;",		// accent grave
		"´"			=> "&#39;",		// accent aiguë

		"'"			=> "&#39;",		// simple quote
		"‘"			=> "&#39;",		// simple quote gauche
		"’"			=> "&#39;",		// simple quote droite

		'"'			=> "&#34;",		// double quote
		"“"			=> "&#34;",		// double quote gauche
		"”"			=> "&#34;",		// double quote droite

		"–"			=> "&#150;",

		"\\"		=> "&#92;"
	);

	/**
	 * @brief	Remplacement des caractères spéciaux par leur équivalent ASCII
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 * @var		array
	 */
	public static $STR_REPLACE	= array(
		"<br />"	=> " ",
		"&#10;"		=> " ",
		"\n"		=> ' ',
		"&#34;"		=> '"',			// double quote
		"&#39;"		=> "'",			// simple quote
		"&#92;"		=> "\\\\",
		"&#150;"	=> "-",
		"&#8211;"	=> "-",
		"&#8212;"	=> "-",
	);

	/**
	 * @brief	Remplacement des caractères spéciaux par leur équivalent ASCII
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 * @var		array
	 */
	public static $PDF_REPLACE	= array(
		"<br />"	=> "\n",
		"&#10;"		=> "\n",
		"&#34;"		=> '"',			// double quote
		"&#39;"		=> "'",			// simple quote
		"&#92;"		=> "\\\\",
		"&#150;"	=> "-",
		"&#8211;"	=> "-",
		"&#8212;"	=> "-",
		"œ"			=> "oe",
	);

	/**
	 * @brief	Remplacement des caractères spéciaux par leur équivalent ASCII
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 * @var		array
	 */
	public static $MYSQL_REPLACE	= array(
		"\n"		=> "&#10;",
		'"'			=> "&#34;",			// double quote
		"'"			=> "&#39;"			// simple quote
	);

	/**
	 * @brief	Remplacement des caractères spéciaux ISO
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 * @var		array
	 */
	public static $ISO_REPLACE		= array(
		// Attention : UTF-8 (BOM)
		"ï»¿"		=> "",
		"\t"		=> " ",
		"\\"		=> "&#92;"
	);

	/**
	 * @brief	Remplacement des caractères spéciaux interprétés par le format LaTeX
	 *
	 * @code
	 * 		char	~	: espace insécable
	 * @endcode
	 *
	 * @li	Tableau exploité via la méthode PHP @strtr().
	 * @return	array
	 */
	public static $LATEX_REPLACE	= array(
		"\n"		=> "\n\t\t\\\\",
		"&#10;"		=> "\n\t\t\\\\",
		"&#34;"		=> '"',			// double quote
		"&#39;"		=> "'",			// simple quote
		"&#150;"	=> "-",
		"&#8211;"	=> "-",
		"&#8212;"	=> "-",
		"̀"			=> "'",
		" /"		=> "~/",
		" :"		=> "~:",
		" ;"		=> "~;",
		" !"		=> "~!",
		" ?"		=> "~?",
		"[<<]"		=> "«~",
		"[>>]"		=> "~»",
		">>"		=> ">{}>",
		"<<"		=> "<{}<",
		"--"		=> "-{}-{}",
		"_"			=> "\\_",
		"&"			=> "\\&",
		"%"			=> "\\%",
		"#"			=> "\\#",
		"{"			=> "\\{",
		"}"			=> "\\}",
		"~"			=> "\\textasciitilde{}",
		"^"			=> "\\textasciicircum{}",
		"\\"		=> "\\textbackslash{}",
		"¦"			=> "\\textbrokenbar",
		"¢"			=> "\\textcent{}",
		"¤"			=> "\\textcurrency{}",
		"$"			=> "\\textdollar{}",
		"€"			=> "\\texteuro{}",
		"£"			=> "\\textsterling{}",
		"¥"			=> "\\textyen"
	);

	/**
	 * @brief	Récupère le contenu d'une entrée sous forme de texte
	 *
	 * @li	Le contenu peut être un tableau multidimensionnel : incompatibilité avec PHP @implode().
	 *
	 * @param	mixed		$xInput			: Élément d'entrée, peut être du texte ou un tableau de chaînes de caractères.
	 * @param	string		$sGlue			: Élément utilisé pour transformer un tableau en une chaîne de caractères.
	 * @return	string
	 */
	public static function convertToText($xInput = null, $sGlue = " ") {
		if (empty($xInput)) {
			return		"";
		} elseif (is_array($xInput)) {
			$sString	= "";
			// Parcours du contenu du tableau sans utiliser la méthode de PHP implode() à cause des N dimensions
			foreach ($xInput as $sElement) {
				if (is_array($sElement)) {
					$sString .= self::convertToText($sElement, $sGlue);
				} elseif (!empty($sString)) {
					$sString .= $sGlue . trim($sElement);
				} else {
					$sString .= trim($sElement);
				}
			}
		} else {
			$sString	= trim($xInput);
		}

		// Suppression de caractères parasites
		$sTextISO		= utf8_encode($sString);
		return utf8_decode(strtr($sTextISO, self::$ISO_REPLACE));
	}

	/**
	 * @brief	Récupère le contenu d'une entrée sous forme de texte [FR]
	 *
	 * @li	Le contenu peut être un tableau multidimensionnel.
	 * @li	Convertion des dates [Y-m-d] au format [d/m/Y]
	 *
	 * @param	mixed		$xInput			: Élément d'entrée, peut être du texte ou un tableau de chaînes de caractères.
	 * @param	string		$sFormat		: Expression du formatage à réaliser.
	 * @return	string
	 */
	public static function convertToString($xInput = null, $sFormat = self::DATA_TYPE_STR) {
		// Extraction du contenu sous forme de texte
		$sInput = self::convertToText($xInput, chr(32));

		// Formatage du texte
		if (!is_null($sFormat) && preg_match("@\%[0-9bcdeEfFgGosuxX]+@", $sFormat)) {
			// Formatage selon le format exploité par la méthode PHP sprintf()
			$sText = sprintf($sFormat, $sInput);
		} else {
			// Formatage selon le type
			switch ($sFormat) {

				// Formatage en date FR du type [d/m/Y]
				case self::DATA_TYPE_DATE:
					$sText = self::dateMyToFr($sInput);
					break;

				// Formatage en date et heure FR du type [d/m/Y à H:i:s]
				case self::DATA_TYPE_DATETIME:
					$sText = self::dateTimeMyToFr($sInput);
					break;

				// Formatage en chaîne de caractères
				case self::DATA_TYPE_STR:
					$sText = strtr($sInput, self::$STR_REPLACE);
					break;

				default:
					$sText = $sInput;
					break;
			}
		}

		// Suppression des espaces en trop
		return preg_replace("/\s\s+/", chr(32), strtr($sText, array(chr(9) => chr(32), chr(10) => chr(32), chr(13) => chr(32))));
	}

	/**
	 * @brief	Récupère le contenu d'une entrée sous forme JSON
	 *
	 * @li	Le contenu peut être un tableau multidimensionnel.
	 *
	 * @param	mixed		$xInput			: Élément d'entrée, peut être du texte ou un tableau de chaînes de caractères.
	 * @param	string		$sLabel			: (optionnel) Libellé affecté à la valeur JSON.
	 * @param	boolean		$bForceKey		: (optionnel) Force les clés dans les objets JSON.
	 * @return	string
	 */
	public static function convertToJSON($xInput = null, $sLabel = null) {
		// Traitement réalisé si le libellé contient un [espace]
		if (preg_match('@^.+\s.+$@', $sLabel)) {
			$sLabel				= "'" . trim($sLabel) . "'";
		}

		// Initialisation du résultat
		$sJSON					= "";
		if (is_array($xInput)) {
			// Formatage du contenu par défaut
			$sFormat			= "{%s}";

			// Exploitation du libellé s'il existe
			if (!is_null($sLabel)) {
				$sJSON			.= $sLabel . ": ";
			}

			// Parcours du contenu du tableau sans utiliser la méthode de PHP implode() à cause des N dimensions
			$sContent			= "";
			$nOccurrence		= 0;
			foreach ($xInput as $xItem => $sElement) {
				// Fonctionnalité réalisée à partir de la deuxième entrée
				if ($nOccurrence > 0) {
					$sContent	.= ", ";
				}

				// Fonctionnalité réalisée si la clé est un numérique
				if (is_numeric($xItem)) {
					$xItem		= null;
					$sFormat	= "[%s]";
				}

				$sContent		.= self::convertToJSON($sElement, $xItem);
				$nOccurrence++;
			}

			// Formatage du contenu au format JSON
			$sJSON .= sprintf($sFormat, $sContent);
		} else {
			// Initialisation du format de sortie
			if (preg_match('@^[0-9A-Z\_]+$@', $xInput) || is_null($xInput)) {
				$sFormat		= !is_null($sLabel) ?	trim($sLabel).': %s'	: '%s';
			} else {
				$sFormat		= !is_null($sLabel) ?	trim($sLabel).': "%s"'	: '"%s"';
			}

			// Formatage du contenu au format JSON
			$sJSON				.= sprintf($sFormat, is_null($xInput) ? "null" : trim($xInput));
		}

		// Renvoi de la chaîne au format JSON
		return $sJSON;
	}

	/**
	 * Transforme une chaîne de caractères de type "chaine[id]" en "chaine_id" exploitable par JavaScript
	 *
	 * Il est TRES IMPORTANT que les noms de champ de type "tableau[id]" aient un id de type "tableau_id" pour pouvoir
	 * être exploité correctement par JavaScript et les plugins JQuery tels que "DatePicker".
	 *
	 * En effet, JavaScript ne sais pas manipuler des "id" contenant des caractères spéciaux comme "[" et "]".
	 *
	 * @param	string		$sName			: nom du champ à convertir contenant les caractères "[" et "]"
	 * @param	string		$sValue			: (optionnel) valeur à injecter dans l'identifiant
	 * @return	string modifié exploitable par JavaScript
	 * @author	durandcedric
	 */
	static function convertStringToId($sName, $sValue = null) {
		// Recherche de la présence des caractères "[" et "]" représentatif d'un tableau
		if (preg_match('@^([a-zA-Z0-9_-]*)\[*(.*)\]*$@', $sName, $aMatches)) {
			// Récupération du nom sans les caractères "[" et "]"
			$sIdentifiant = sprintf('%s', $aMatches[1]);
		} else {
			// Aucune ocurence : l'identifiant correspond au nom du
			$sIdentifiant = $sName;
		}

		// Ajout de la valeur dans l'identifiant
		$sValue = trim($sValue);
		if (!is_null($sValue) && is_numeric($sValue)) {
			// Injection de la valeur
			$sIdentifiant .= sprintf('_%s', $sValue);
		}
		return $sIdentifiant;
	}

	/**
	 * Transforme une chaîne de caractères en tableau.
	 *
	 * @param	string		$sSeparator		: caractère de séparation entre chaque élément
	 * @param	string		$sText			: texte à analyser
	 * @return	array
	 * @author	durandcedric
	 */
	static function convertStringToArray($sSeparator, $sText) {
	    // Extraction du contenu sous forme de tableau
		$aResultat = explode($sSeparator, $sText);

		// Fonctionnalité réalisée si le résultat est vide
		if (count($aResultat) == 1 && $aResultat[0] == "") {
			$aResultat = array();
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * Coupe une chaîne de caractères en préservant les caractères UTF-8
	 *
	 * @li	Supprime les caractères [espace] en trop en début et en fin de chaîne.
	 *
	 * @param	string		$sString		: chaîne de caractères à traiter.
	 * @param	integer		$nStart			: occurrence du caractère de début, [0] pour le premier.
	 * @param	integer		$nLenght		: longueur de la chaîne à extraire.
	 * @param	string		$sSuffix		: chaîne à injecter à la fin.
	 * @return	string modifié
	 * @author	durandcedric
	 */
	static function subString($sString = "", $nStart = 0, $nLenght = null, $sSuffix = "...") {
		// Fonctionnalité réalisée si la longueur n'est pas définie
		if (empty($nLenght)) {
			$nLenght = strlen($sString);
			$sSuffix = null;
		} elseif ($nSuffix = strlen($sSuffix) && $nLenght < $sSuffix) {
			// Retrait de la longueur du suffixe
			$nLenght -= $nSuffix;
		}

		// Coupe la chaîne décodée pour le pas perdre les caractères spéciaux
		$sTemp		= substr(trim(utf8_decode($sString)), $nStart, $nLenght);

		// Réencodage de la chaîne
		$sResult	= utf8_encode($sTemp);

		// Fonctionnalité réalisée si la chaîne est plus petite
		if (strlen($sResult) < $nLenght) {
			$sSuffix = null;
		}

		// Renvoi de la chaîne au format UTF8
		return $sResult . $sSuffix;
	}

	/**
	 * @brief	Récupération des éléments de la liste.
	 * Extraction des éléments présents dans la liste passée en paramètre.
	 * @code
	 * 		// La liste contient les éléments 8, 13 et 19
	 * 		$sListe = DataHelper::getArrayFromList("8,13,19");
	 *
	 * 		// La liste contient les éléments 7, 8, 13, 19 et 20
	 * 		$sListe = DataHelper::getArrayFromList("7-8,13,19-20");
	 *
	 * 		// La liste contient les éléments 5{17,18,19,20,21,22,23}, 6{} et 7{}
	 * 		$sListe = DataHelper::getArrayFromList("5:17-23,6-7");
	 * @endcode
	 *
	 * @param	string	$sInputList	: Liste des éléments à extraire.
	 * @return	array
	 */
	static public function getArrayFromList($sInputList, $sSeparatorList = ",", $sSeparatorItem = "-", $sConcatItem = ":") {
		$aResultat					= array();
		$aInputList					= explode($sSeparatorList, $sInputList);
		foreach ($aInputList as $xList) {
			$aConcatList			= explode($sConcatItem, $xList);
			if (isset($aConcatList[1])) {
				// Fonctionnalité réalisée si le contenu est du style 5:8-12
				$aResultat[$aConcatList[0]] = self::getArrayFromList($aConcatList[1], $sSeparatorList, $sSeparatorItem, $sConcatItem);
			} else {
				$aPlage = self::convertStringToArray($sSeparatorItem, $aConcatList[0]);
				// Fonctionnalité réalisée si le contenu est du style 8-12
				if (isset($aPlage[0]) && isset($aPlage[1])) {
					// Extraction des paramètres
					for ($nEntry = $aPlage[0] ; $nEntry <= $aPlage[1] ; $nEntry++) {
						$aResultat[$nEntry] = $nEntry;
					}
				} else {
					$aResultat[$xList] = $xList;
				}
			}
		}
		return $aResultat;
	}

	/** @brief	Récupère un groupe de clés d'un tableau.
	 *
	 * Cette fonction extrait des lignes d'un tableau ayant la clé resemblant à l'expression passée en paramètre.
	 * @code
	 * 	// Soit le tableau du formulaire suivant
	 * 	$aForm = array(
	 * 		// Identifiant des questions
	 * 		'question_id'		=>	array(
	 * 			0	=>	1,					// Identifiant de la question [0]
	 * 			1	=>	2,					// Identifiant de la question [1]
	 * 			2	=>	3					// Identifiant de la question [2]
	 * 		),
	 * 		// Titre des questions
	 * 		'question_id'		=>	array(
	 * 			0	=>	"Question n°1",		// Titre de la question [0]
	 * 			1	=>	"Question n°2",		// Titre de la question [1]
	 * 			2	=>	"Question n°3",		// Titre de la question [2]
	 * 		),
	 * 		// Identifiant des réponses
	 * 		'reponse_id'		=>	array(
	 * 			0	=>	array(				// Liste des réponses à la question [0]
	 * 				0	=> 10,				// Identifiant de la réponse [0] à la question [0]
	 * 				1	=> 11,				// Identifiant de la réponse [1] à la question [0]
	 * 				2	=> 12,				// Identifiant de la réponse [2] à la question [0]
	 * 				3	=> 13				// Identifiant de la réponse [3] à la question [0]
	 * 			),
	 * 			1	=>	array(				// Liste des réponses à la question [1]
	 * 				0	=> 14,				// Identifiant de la réponse [0] à la question [1]
	 * 				1	=> 15,				// Identifiant de la réponse [1] à la question [1]
	 * 				2	=> 16,				// Identifiant de la réponse [2] à la question [1]
	 * 				3	=> 17				// Identifiant de la réponse [3] à la question [1]
	 * 			),
	 * 			2	=>	array(				// Liste des réponses à la question [2]
	 * 				0	=> 18,				// Identifiant de la réponse [0] à la question [2]
	 * 				1	=> 19,				// Identifiant de la réponse [1] à la question [2]
	 * 				2	=> 20,				// Identifiant de la réponse [2] à la question [2]
	 * 				3	=> 21				// Identifiant de la réponse [3] à la question [2]
	 * 			)
	 * 		)
	 *	);
	 *
	 * 	// Récupère tous les champs du tableau commançant par par la chaîne [question_].
	 * 	$aTableau = DataHelper::getLinesFromArrayLike($aForm, "^question_");
	 *
	 *	// Le résultat sera sous forme suivante
	 *	$aTableau = array(
	 * 		'question_id'		=>	array(
	 * 			0	=>	1,
	 * 			1	=>	2,
	 * 			2	=>	3
	 * 		),
	 * 		'question_titre'	=>	array(
	 * 			0	=>	"Question n°1",
	 * 			1	=>	"Question n°2",
	 * 			2	=>	"Question n°3",
	 * 		)
	 *	);
	 * @endcode
	 *
	 * @li	Possibilité de passer un tableau d'expressions régulières.
	 *
	 * @param	array		$aArray			: Tableau à traiter.
	 * @param	string		$xRegExp		: Expression régulière du paramètre.
	 * @param	bool		$sKeyOnly		: (optionnel) ne récupère que les clés.
	 * @return	array, tableau final
	 * @author	durandcedric
	 */
	static function getLinesFromArrayLike($aArray, $xRegExp = ".*", $bKeyOnly = false) {
		// Initialisation du résultat
		$aResult	= array();

		// Manipulation de l'expression régulière sous forme de tableau
		$aListeExp	= (array) $xRegExp;

		// Parcours de l'ensemble du tableau
		foreach ($aArray as $sKey => $xValue) {
			foreach ($aListeExp as $sRegExp) {
				// Modèle
				$sPattern	= sprintf("@%s@", $sRegExp);

				// Fonctionnalité réalisée si l'entrée correspond
				if (preg_match($sPattern, $sKey)) {
					// Ajout du contenu à la collection
					if (isset($aResult[$sKey])) {
						// Le champ existe déjà dans la collection
						$aResult[$sKey] = array_merge_recursive($aResult[$sKey], $aArray[$sKey]);
					} else {
						// Le champ n'existe pas dans la collection
						$aResult[$sKey] = $aArray[$sKey];
					}
				}
			}
		}

		// Renvoi du résultat
		if ($bKeyOnly) {
			// Extraction des clés
			$aResult = array_keys($aResult);
		}
		return $aResult;
	}

	/**
	 * Récupération d'éléments d'un tableau selon un filtre.
	 *
	 * Le filtre permet de récupérer les lignes du tableau.
	 *
	 * @param	array		$aRequest		: données à traiter
	 * @param	array		$aFiltre		: filtre des colonnes à récupérer
	 * @return	array, tableau final
	 * @author	durandcedric
	 */
	static function getLinesFromRequest($aRequest, $aFiltre) {
		// Initialisation du résultat
		$aResultat = array();

		// Parcours des données
		foreach ($aRequest as $nOccurrence => $aLine) {
			// Initialisation de la variable de boucle
			$bValide = false;

			// Parcours du filtre
			foreach ($aFiltre as $sString) {
				// Fonctionnalité réalisée si l'élément est présent
				if (in_array($sString, (array) $aLine)) {
					$bValide = true;
				}
			}

			// Fonctionnalité réalisée si l'élément a été trouvé
			if ($bValide) {
				// Ajout de la ligne au résultat final
				$aResultat[] = (array) $aLine;
			}
		}

		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * Suppression d'éléments d'un tableau selon un filtre.
	 *
	 * Le filtre permet d'ignorer les lignes du tableau.
	 *
	 * @param	array		$aRequest		: données à traiter
	 * @param	array		$aFiltre		: filtre des données à ignorer
	 * @return	array, tableau final
	 * @author	durandcedric
	 */
	static function removeLinesFromRequest($aRequest, $aFiltre) {
		// Initialisation du résultat
		$aResultat = array();

		// Parcours des données
		foreach ($aRequest as $nOccurrence => $aLine) {
			// Initialisation de la variable de boucle
			$bValide = true;

			// Parcours du filtre
			foreach ($aFiltre as $sString) {
				// Fonctionnalité réalisée si l'élément est présent
				if (in_array($sString, (array) $aLine)) {
					$bValide = false;
					unset($aRequest[$nOccurrence]);
				}
			}

			// Fonctionnalité réalisée si l'élément n'a pas été trouvé
			if ($bValide) {
				// Ajout de la ligne au résultat final
				$aResultat[] = (array) $aLine;
			}
		}
		// Renvoi du résultat
		return $aResultat;
	}

	/**
	 * @brief	Extraction d'un texte contenu entre une balise HTML.
	 *
	 * @param	string		$sHTML			: balise HTML avec son contenu.
	 * @return	string
	 */
	static public function extractContentFromHTML($sHTML) {
		// Initialisation du résultat
		$sString		= "";
		// Fonctionnalité réalisée si un tag HTML est détecté
		if (preg_match("@>(.*)</@", $sHTML, $aMatched)) {
			$sString	= $aMatched[1];
		} elseif (preg_match("@value=(.*)@", $sHTML, $aMatched)) {
			$sString	= $aMatched[1];
		} else {
			$sString	= $sHTML;
		}
		// Renvoi du résultat
		return trim($sString);
	}

	/** @brief Extraction d'un tableau BIDIMENSIONNEL.
	 * Méthode permettant d'extraire un ensemble de libellés à partir d'une requête.
	 *
	 * @li Extraction des champs dans l'ordre de la construction du filtre.
	 * @example
	 * @code
	 * 	$aRequest = array(
	 * 		0 => array('nom'=>'TOTO', 'prenom'=>'Toto', 'age'=>15),
	 * 		1 => array('nom'=>'TATA', 'prenom'=>'Tata', 'age'=>26),
	 * 		2 => array('nom'=>'TOTO', 'prenom'=>'Tata', 'age'=>37),
	 * 		3 => array('nom'=>'TITI', 'prenom'=>'Titi', 'age'=>48),
	 * 		4 => array('nom'=>'TOTO', 'prenom'=>'Tutu', 'age'=>59)
	 * 	);
	 *
	 *	// Récupération de tous les champs, dans l'ordre 'prenom' puis 'nom', sans le champ portant le libellé 'age'
	 * 	$aLabel = array('prenom', 'nom');
	 *
	 *	// Résultat de la manipulation générée
	 * 	$aResult = array(
	 * 		0 => array('prenom'=>'Toto', 'nom'=>'TOTO'),
	 * 		1 => array('prenom'=>'Tata', 'nom'=>'TATA'),
	 * 		2 => array('prenom'=>'Tata', 'nom'=>'TOTO'),
	 * 		3 => array('prenom'=>'Titi', 'nom'=>'TITI'),
	 * 		4 => array('prenom'=>'Tutu', 'nom'=>'TOTO')
	 * 	);
	 * @endcode
	 *
	 * @li Possibilité de renommer le libellé des champs souhaités du type array('champ' => 'Nouveau libellé')
	 * @example
	 * @code
	 *	// Changement du libellé, sauf le champ 'age' qui n'est plus à prendre en compte
	 * 	$aLabel = array(
	 * 		'nom'		=> "Nom de l'utilisateur",
	 * 		'prenom'	=> "Prénom de l'utilisateur"
	 * 	);
	 *
	 * 	// Résultat : le champ 'age' n'est plus pris en compte et le libellé des champs a été changé
	 * 	$aResult = array(
	 * 		0 => array('Nom de l'utilisateur'=>'TOTO', 'Prénom de l'utilisateur'=>'Toto'),
	 * 		1 => array('Nom de l'utilisateur'=>'TATA', 'Prénom de l'utilisateur'=>'Tata'),
	 * 		2 => array('Nom de l'utilisateur'=>'TOTO', 'Prénom de l'utilisateur'=>'Tata'),
	 * 		3 => array('Nom de l'utilisateur'=>'TITI', 'Prénom de l'utilisateur'=>'Titi'),
	 * 		4 => array('Nom de l'utilisateur'=>'TOTO', 'Prénom de l'utilisateur'=>'Tutu')
	 * 	);
	 * @endcode
	 *
	 * @param	array		$aRequest		: tableau d'éléments de type array(array('key'=>'value')).
	 * @param	array		$aFiltre		: ensemble des libellés du tableau à récupérer.
	 * @param	array		$aChangeContent	: (facultatif) format des champs à modifier leur contenu.
	 * @param	bool		$bShowHeader	: (facultatif) ajout du titre des colonnes.
	 * @param	bool		$bSqlRequest	: (facultatif) contenu déstiné à une requête SQL (incompatible avec $bShowHeader).
	 * @return	array liste des éléments du tableau
	 */
	static function extractArrayFromRequestByLabel($aRequest = array(), array $aFiltre = array(), $aChangeContent = array(), $bShowHeader = false, $bSqlRequest = false) {
		// Initialisation du tableau de résultat
		$aResult = array();
		// Fonctionnalité réalisée si les paramètres contiennent des données
		if (self::isValidArray($aRequest) && self::isValidArray($aFiltre)) {
			// Parcours du filtre des champs à récupérer dans un premier temps
			foreach ($aFiltre as $sChamp => $sLibelle) {
				// Fonctionnalité réalisée si le champ correspont au libellé
				if (self::isValidNumeric($sChamp)) {
					$sChamp = $sLibelle;
				}

				// Initialisation de la recherche du champ
				$bTrouve = false;
				// Fonctionnalité permettant d'extraire la valeur de la clef recherchée
				foreach($aRequest as $sOccurrence => $xContent) {
					if (self::isValidArray($xContent)) {
						// Extraction de chaque valeur du tableau
						$aResult[$sOccurrence] = self::extractArrayFromRequestByLabel($xContent, $aFiltre, $aChangeContent, false, $bSqlRequest);
					} else {
						// Fonctionnalité réalisée si la donnée est une chaîne non vide
						if (self::isValidString($xContent, true)) {
							// Suppression des caractères [espace] en trop !
							$xContent = $bSqlRequest ? str_ireplace('\n', chr(13), $xContent) : $xContent;
						}

						// Récupération du contenu de l'entrée
						$sValue = trim($xContent);

						// Fonctionnalité réalisée si le contenu doit être formaté
						if (!empty($sValue) && array_key_exists($sChamp, (array) $aChangeContent)) {
							$sValue = sprintf($aChangeContent[$sChamp], $sValue);
						}

						// Fonctionnalité réalisée si le libellé doit être récupéré
						if (self::isValidNumeric($sChamp) && $sOccurrence === $sLibelle) {
							// Construction du tableau sans changement du nom du champ
							$aResult[trim($sLibelle)] = $sValue;
							$bTrouve = true;
						} elseif (self::isValidString($sOccurrence) && $sOccurrence === $sChamp) {
							// Construction du tableau avec prise en compte du nouveau libellé
							$aResult[trim($sLibelle)] = $sValue;
							$bTrouve = true;
						} elseif (!$bTrouve) {
							// Le contenu n'existe pas
							$aResult[trim($sLibelle)] = null;
						}
					}
				}
			}
		} else {
			// Le résultat n'est pas manipulé
			$aResult = $aRequest;
		}

		// Construction de l'entête
		if ($bShowHeader && !$bSqlRequest) {
			// Extraction de chaque valeur du tableau
			$aResult = array_merge_recursive(array($aFiltre), $aResult);
		}

		// Renvoi du résultat
		return $aResult;
	}

	/**
	 * @brief	Supprime les apostrophes en trop.
	 *
	 * @code
	 * 		// La variable est issue d'un formulaire
	 * 		$aFile	= $_POST['file'];
	 *
	 * 		// Récupération du contenu en supprimant éventuellement les caractères ["] en début/fin de chaîne
	 * 		$aNew	= DataHelper::stripApostrophes($aFile);
	 *
	 * 		var_dump($aFile['type']);			// string(10) ""text/csv""
	 * 		var_dump($aNew['type']);			// string(8) "text/csv"
	 * @endcode
	 *
	 * @param	mixed		$xInput			: Élément d'entrée.
	 * @return	array|string
	 */
	public static function stripApostrophes($xInput) {
		// Fonctionnalité réalisée si l'élément est un tableau
		if (self::isValidArray($xInput)) {
			$xResultat = array();
			// Parcours de chaque élément
			foreach ($xInput as $xKey => $xValue) {
				// Fonctionnalité récursive
				$xResultat[$xKey] = self::stripApostrophes($xValue);
			}
		} else {
			// Récupération du contenu en supprimant éventuellement les caractères ["] en début/fin de chaîne
			$xResultat = preg_replace('@^\"(.*)\"$@', '$1', $xInput);
		}

		// Renvoi du résultat
		return $xResultat;
	}

	/**
	 * @brief	Récupère le contenu d'une variable
	 *
	 * @li	Exploitation des constantes de typage DataHelper::DATA_TYPE_*
	 *
	 * @param	mixed		$xInput			: Élément d'entrée
	 * @param	string		$sIndex			: Référence à récupérer de l'entrée
	 * @param	integer		$iType			: (optionnel) Constante de typage de variable
	 * @param	mixed		$xDefault		: (optionnel) Valeur de l'élément par défaut
	 * @param	bool		$bForceEmpty	: (optionnel) Force la valeur par défaut si le contenu est vide : "", '', 0, NULL
	 * @return	array|date|string|float|integer|boolean
	 */
	public static function get($xInput, $sIndex = null, $iType = DataHelper::DATA_TYPE_ANY, $xDefault = null, $bForceEmpty = false) {
		$xValue = null;

		// Vérification de la présence de entrée
		if (is_array($xInput) && array_key_exists($sIndex, $xInput) && isset($xInput[$sIndex])) {
			$xValue = $xInput[$sIndex];
		}

		// Fonctionnalité réalisée si la valeur est nulle
		if (is_null($xValue)) {
			// Injection de la valeur par défaut
			$xValue = $xDefault;
		} elseif ($bForceEmpty && empty($xValue)) {
			// Remplacement de la valeur par défaut
			$xValue = $xDefault;
		}

		// Typage de la variable
		switch ($iType) {

			// Booléen
			case self::DATA_TYPE_BOOL:
				$bValue = false;
				if (($xValue === true) || ($xValue === "true") || ($xValue === "1") || ($xValue === 1)) {
					$bValue = true;
				}
				return $bValue;
			break;

			// Booléen en MySQL
			case self::DATA_TYPE_MYBOOL:
				$bValue = false;
				if (($xValue === true) || ($xValue === "true") || ($xValue === "1") || ($xValue === 1)) {
					$bValue = true;
				}
				return (int) $bValue;
			break;

			// Integer
			case self::DATA_TYPE_INT:
				return intval($xValue);
			break;

			// Integer en valeur absolue
			case self::DATA_TYPE_INT_ABS:
				return intval(abs($xValue));
			break;

			// Float
			case self::DATA_TYPE_FLT:
				if (empty($xValue)) {
					return 0;
				} else {
					// Remplacement du séparateur de décimal [.] par [,]
					return str_replace(".", ",", $xValue);
				}
			break;

			// Float en valeur absolue
			case self::DATA_TYPE_FLT_ABS:
				if (empty($xValue)) {
					return 0;
				} else {
					// Remplacement du séparateur de décimal [.] par [,]
					return abs(str_replace(".", ",", $xValue));
				}
			break;

			// Float MySQL
			case self::DATA_TYPE_MYFLT:
				if (empty($xValue)) {
					return 0;
				} else {
					// Remplacement du séparateur de décimal [.] par [,]
					return (float) str_replace(",", ".", $xValue);
				}
			break;

			// Float MySQL en valeur absolue
			case self::DATA_TYPE_MYFLT_ABS:
				if (empty($xValue)) {
					return 0;
				} else {
					// Remplacement du séparateur de décimal [.] par [,]
					return (float) abs(str_replace(",", ".", $xValue));
				}
			break;

			// Array
			case self::DATA_TYPE_ARRAY:
				if (empty($xValue)) {
					return array();
				} elseif (is_string($xValue) && preg_match("@^.*\\" . self::ARRAY_SEPARATOR . "*@", $xValue)) {
					// Chaîne de caractères représentant un tableau
					$xValue = self::convertStringToArray(self::ARRAY_SEPARATOR, $xValue);
				}
				// Renvoi sous forme de tableau
				return (array) $xValue;
			break;

			// Array
			case self::DATA_TYPE_MYARRAY:
				if (empty($xValue)) {
					return "";
				}
				// Renvoi de sous forme de chaîne
				return implode(self::ARRAY_SEPARATOR, (array) $xValue);
			break;

			// Array
			case self::DATA_TYPE_MYARRAY_NUM:
				if (empty($xValue)) {
					return "";
				}
				// Suppression
				foreach ((array) $xValue as $nOccurrence => $nData) {
					if (!self::isValidNumeric($nData)) {
						unset($xValue[$nOccurrence]);
					}
				}
				// Renvoi de sous forme de chaîne
				return implode(self::ARRAY_SEPARATOR, (array) $xValue);
				break;

			// Time FR
			case self::DATA_TYPE_TIME:
				if (empty($xValue)) {
					return date("H:00");
				}
				// Renvoi de la chaîne sans les caractères [espace] en trop
				return self::numberToTime($xValue);
			break;

			// Date FR
			case self::DATA_TYPE_DATE:
				if (empty($xValue)) {
					return date("d/m/Y");
				}
				// Renvoi de la chaîne sans les caractères [espace] en trop
				return self::dateMyToFr($xValue);
			break;

			// Date SQL
			case self::DATA_TYPE_MYDATE:
				if (empty($xValue)) {
					return date("Y-m-d");
				}
				// Renvoi de la chaîne sans les caractères [espace] en trop
				return self::dateFrToMy($xValue);
			break;

			// String
			case self::DATA_TYPE_STR:
				// Conversion du contenu en texte
				$xValue	= self::convertToText($xValue, chr(32));
				// Renvoi de la chaîne sans les caractères [espace] en trop
				return (string) trim(stripslashes(strtr(preg_replace("/\s\s+/", chr(32), $xValue), self::$HTML_REPLACE)));
			break;

			// Text
			case self::DATA_TYPE_TXT:
				// Conversion du contenu en texte
				$xValue	= self::convertToText($xValue, chr(13));
				// Renvoi de la chaîne sans les caractères spéciaux interprétés
				return (string) strtr(preg_replace("/\s\s+/", chr(32), nl2br($xValue)), self::$HTML_REPLACE);
			break;

			case self::DATA_TYPE_PDF:
				// Conversion du contenu en texte
				$xValue	= self::convertToText($xValue, chr(13));
				// Renvoi de la chaîne sans les caractères spéciaux interprétés
				$sText = trim(stripslashes(strtr(preg_replace("/\s\s+/", chr(32), $xValue), self::$PDF_REPLACE)));
				return (string) $sText;
				//return (string) utf8_decode($sText);
			break;


			// Text SQL
			case self::DATA_TYPE_MYTXT:
				if (empty($xValue)) {
					return $bForceEmpty ? 'NULL' : "";
				}
				// Renvoi de la chaîne sans les caractères spéciaux interprétés
				$sText = trim(stripslashes(strtr(preg_replace("/\s\s+/", chr(32), nl2br($xValue)), self::$HTML_REPLACE)));
				return (string) strtr($sText, self::$MYSQL_REPLACE);
			break;

			// LaTeX
			case self::DATA_TYPE_LATEX:
				if (empty($xValue)) {
					return "";
				} elseif (self::isValidArray($xValue)) {
					$xValue = self::convertToText($xValue, "\n");
				}

				// Renvoi de la chaîne sans les caractères spéciaux interprétés
				return (string) trim(strtr($xValue, self::$LATEX_REPLACE));
				break;

			// par défaut
			default:
				if (is_null($xValue)) {
					return $xDefault;
				} else {
					return $xValue;
				}
			break;
		}
	}

	/**
	 * @brief	Insertion d'une entrée dans un tableau MULTIDIMENSIONNEL selon l'identifiant.
	 *
	 * @param	array		$aInput			: Tableau à traiter d'entrée.
	 * @param	array		$aWhere			: Tableau associatif de la clé recherchée et sa valeur attendue.
	 * @param	array		$aData			: Tableau associatif de la clé à ajouter et de sa valeur correspondante.
	 * @return	array
	 */
	public static function arrayMerge($aInput, $aWhere = array('id' => 0), $aData = array('items' => array())) {
		// Élément à rechercher
		$aSearch		= array_keys($aWhere);
		$sSearchKey		= $aSearch[0];
		$sSearchValue	= $aWhere[$sSearchKey];

		// Données à ajouter
		$aMerge			= array_keys($aData);
		$sMergeKey		= $aMerge[0];
		$xMergeValue	= $aData[$sMergeKey];

		// Parcours du contenu de façon récursive
		foreach ($aInput as $sKey => $xValue) {
			// Fonctionnalité réalisée si l'élément recherché correspond
			if ($sSearchKey == $sKey && $xValue == $sSearchValue) {
				// Ajout des données
				$aInput[$sMergeKey][] = $xMergeValue;
			} elseif (self::isValidArray($xValue)) {
				// Parcours du sous-ensemble
				if (array_key_exists($sSearchKey, $xValue) && $xValue[$sSearchKey] == $sSearchValue) {
					$aInput[$sKey][$sMergeKey][] = $xMergeValue;
				} else {
					$aInput[$sKey] = self::arrayMerge($xValue, $aWhere, $aData);
				}
			}
		}

		// Renvoi du résultat
		return $aInput;
	}

	const SQL_TYPE_UNDEFINED	= 0;			# Type non reconnu
	const SQL_TYPE_SELECT		= 1;			# Type SELECT
	const SQL_TYPE_INSERT		= 2;			# Type INSERT
	const SQL_TYPE_UPDATE		= 3;			# Type UPDATE
	const SQL_TYPE_DELETE		= 4;			# Type DELETE

	/**
	 * @brief	Récupère le type d'une requête SQL
	 *
	 * @param	mixed		$xQuery			: Chaîne de caractères représentant une requête SQL.
	 * @return	indice, type de la requête.
	 */
	public static function getTypeSQL($xQuery, $bString = false) {
		// Fonctionnalité réalisée si la requête est sous forme d'un tableau
		if (self::isValidArray($xQuery)) {
			$sQuery = implode(chr(32), $xQuery);
		} else {
			$sQuery = $xQuery;
		}

		// Extraction de l'instruction
		preg_match('@^([a-zA-Z]+)[\s\t\n]+@', trim($sQuery), $aType);

		// Récupération du type de la requête
		$sType = strtoupper(trim($aType[1]));

		// Détermination du type de la requête
		switch ($sType) {

			// Requête de type SELECT
			case 'SELECT':
				$iType = self::SQL_TYPE_SELECT;
			break;

			// Requête de type INSERT
			case 'INSERT':
				$iType = self::SQL_TYPE_INSERT;
			break;

			// Requête de type UPDATE
			case 'UPDATE':
				$iType = self::SQL_TYPE_UPDATE;
			break;

			// Requête de type DELETE
			case 'DELETE':
				$iType = self::SQL_TYPE_DELETE;
			break;

			// Requête non reconnue
			default:
				$iType = self::SQL_TYPE_UNDEFINED;
			break;
		}

		// Renvoi du résultat
		return $bString ? $sType : $iType;
	}

	/**
	 * @brief	Convertion d'un tableau de requête en liste.
	 *
	 * @exemple	Tableau représentant une commune par ligne de résultat
	 * @code
	 * 		$aRequest	=	array(
	 * 			0	=>	array(
	 * 					'code_postal'	=> "01000",
	 * 					'libelle'		=> "BOURG-EN-BRESSE"
	 * 			),
	 * 			...
	 * 			56	=>	array(
	 * 					'code_postal'	=> "57000",
	 * 					'libelle'		=> "METZ"
	 * 			),
	 * 			...
	 * 			74	=>	array(
	 * 					'code_postal'	=> "75000",
	 * 					'libelle'		=> "PARIS"
	 * 			),
	 * 			...
	 * 			97	=>	array(
	 * 					'code_postal'	=> "98800",
	 * 					'libelle'		=> "NOUMÉA"
	 * 			)
	 * 		);
	 * @endcode
	 *
	 * @exemple	Format de sortie du tableau final
	 * @code
	 * 		$aFormat	=	array(
	 * 			'code_postal'	=> "libelle"
	 * 		);
	 * @encode
	 *
	 * @exemple Résultat de l'instruction @a DataHelper::requestToList($aRequest, $aFormat)
	 * @code
	 * 		$aResult = array(
	 * 			'01000' => "BOURG-EN-BRESSE",
	 * 			...
	 * 			'57140'	=> "WOIPPY",
	 * 			...
	 * 			'75000' => "PARIS",
	 * 			...
	 * 			'98800'	=> "NOUMÉA"
	 * 		);
	 * @endcode
	 *
	 * @param	array		$aRequest		: tableau de données résultant d'une requête.
	 * @param	array		$aFormat		: format des champs du tableau à récupérer du type array('code_postal' => 'ville').
	 * @param	string		$sFirst			: (facultatif) insertion d'une entrée en première position.
	 * @param	string		$sTri			: (facultatif) ordre du tri pour le résultat parmis 'ASC' ou 'DESC'.
	 * @param	bool		$bShowKey		: (facultatif) si la valeur de la clef doit être affichée.
	 * @param	bool		$bKeyOrder		: (facultatif) ordre du tri sur la clé.
	 * @return	array ensemble des éléments du tableau
	 */
	public static function requestToList($aRequest, $aFormat, $sFirst = null, $sTri = 'ASC', $bShowKey = false, $bKeyOrder = false) {
		// Initialisation du tableau de résultat
		$aResult = array();
		// Fonctionnalité permettant d'extraire la valeur de la clef recherchée
		if (self::isValidArray($aRequest)) {
			// Parcours de chaque résultat
			foreach ($aRequest as $nOccurrence => $aTableau) {
				// Fonctionnalité réalisée si le format est une chaîne
				if (is_string($aFormat)) {
					$aFormat = (array) $aFormat;
				}

				// Parcours des clés du tableau de format
				foreach ($aFormat as $sKey => $sChamp) {
					// Fonctionnalité réalisée si la clé est un numérique
					if (self::isValidNumeric($sKey)) {
						$sKey = $sChamp;
					}
					// Fonctionnalité permettant d'afficher la valeur de la clef
					if ($bShowKey) {
						$sClause = " (".$aTableau[$sKey].")";
					} else {
						$sClause = "";
					}
					// Construction du tableau avec la valeur de la clef
					$aResult[$aTableau[$sKey]] = $aTableau[$sChamp].$sClause;
				}
			}
		}

		// Fonctionnalité permettant de réaliser le tri
		switch (strtoupper($sTri)) {
			case 'DESC':
				if ($bKeyOrder) {
					krsort($aResult);
				} else {
					rsort($aResult);
				}
			break;

			case 'ASC':
				if ($bKeyOrder) {
					ksort($aResult);
				} else {
					asort($aResult);
				}
				break;

			default:
				// Tri désactivé
				break;
		}

		// Attribution du libellé au premier élément
		if (!is_null($sFirst)) {
			$aResult = array(0 => $sFirst) + $aResult;
		}

		// Renvoi du résultat
		return $aResult;
	}

	/**
	 * @brief	Transforme une requête SQL sous forme de chaîne de caractères.
	 *
	 * @param	mixed		$xQuery			: chaîne de caractères représentant la requête SQL, ou tableau où chaque ligne composent la requête.
	 * @param	array		$aBind			: (optionnel) tableau associatif des étiquettes et leurs valeurs.
	 * @return	indice, type de la requête.
	 */
	public static function queryToString($xQuery, $aBind = array()) {
		// Récupération de la requête sous forme de chaîne
		$sQuery = self::convertToString($xQuery);
		// Remplacement des étiquettes par leur valeur
		return strtr($sQuery, (array) $aBind);
	}

	/**
	 * @brief	Méthode d'affichage de l'exception sous forme de message d'erreur.
	 *
	 * @param	Exception	$oException		: exception qui est rencontrée.
	 * @param	string		$sTitre			: titre de l'exception.
	 * @param	string		$sMessage		: message supplémentaire à afficher.
	 * @return	string
	 * @author	durandcedric
	 */
	public static function displayException($oException, $sTitre = null, $sMessage = null) {
		try {
			// Ajout du détail de l'exception en MODE_DEBUG
			if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
				$sTitre .= "<span class=\"right italic\">" . get_class($oException) . "</span>";
			}

			// Début du message
			$sExceptionHTML = '';

			// Affichage d'un message
			if (!empty($sTitre)) {
				$sExceptionHTML .= '<h2>' . $sTitre . '</h2>';
			}

			// Affichage d'un message
			if (!empty($sMessage)) {
				$sExceptionHTML .= '<p>' . $sMessage . '</p>';
			}

			// Vérification de la présence de l'exception
			if (!is_null($oException)) {
				$sExceptionHTML .= '<hr /><ul id="exception-detail">';
				// Récupération de l'objet de l'exception parent
				if (method_exists($oException, "getCause") && is_object($oException->getCause())) {
					$sExceptionHTML .= '<li id="exception-cause">' . $oException->getCause()->getMessage() . '</li>';
				}

				// Récupération de l'objet de l'exception enfant
				if ($oException->getMessage()) {
					$sExceptionHTML .= sprintf('<li id="exception-message">%s</li>', $oException->getMessage());
				}

				// Fonctionnalité réalisée en MODE_DEBUG
				if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
					// Récupération de l'instance d'échange
					$oInstanceStorage	= InstanceStorage::getInstance();

					// Récupération de la trace de l'exception enfant
					if ($oException->getTraceAsString()) {
						$sExceptionHTML .= sprintf('<p id="exception-trace">%s</p><hr />', implode("<br />#", explode("#", $oException->getTraceAsString())));
					}

					// Récupération du code de l'exception enfant
					if ($nCode = $oException->getCode()) {
						$sExceptionHTML .= sprintf('<li id="exception-code">Code erreur : %d</li>', $nCode);
					}

					// Récupération du fichier de l'exception enfant
					if ($oException->getFile()) {
						$sExceptionHTML .= sprintf('<li id="exception-file">%s</li>', $oException->getFile());

						// Récupération de la Ligne de l'exception enfant
						if ($oException->getLine()) {
							$sExceptionHTML .= sprintf('<li id="exception-line">Ligne %d</li>', $oException->getLine());
						}
					}

					// Récupération du code de l'exception enfant
					if ($oException->getPrevious()) {
						$sExceptionHTML .= sprintf('<li id="exception-previous"><p>%s</p></li>', $oException->getPrevious());
					}

					// Récupération du contrôleur ayant l'exception
					if (method_exists($oException, 'getController') && $sController = $oException->getController()) {
						$sExceptionHTML .= sprintf('<li id="exception-controller no-wrap"><span>Contrôleur : </span><i>%s</i></li>', $sController);
					}

					// Récupération de l'action du contrôleur ayant l'exception
					if (method_exists($oException, 'getAction') && $sAction = $oException->getAction()) {
						$sExceptionHTML .= sprintf('<li id="exception-action no-wrap"><span>Action : </span><i>%s</i></li>', $sAction);
					}

					// Récupération des paramètres de l'exception
					$sTextParams = "";
					if (method_exists($oException, 'getParams') && $aParams = $oException->getParams()) {
						if (DataHelper::isValidArray($aParams)) {
							foreach ($aParams as $sValue) {
								$sTextParams .= '<ul>' . $sValue . '</ul>';
							}
							$sExceptionHTML .= sprintf('<li id="exception-params no-wrap"><span>Params[] : </span>%s</li>', $sTextParams);
						}
					}

					/** OPTIONNEL */
					// Récupération de l'action du contrôleur ayant l'exception
					$sTextExtras = "";
					if (method_exists($oException, 'getExtra') && $aExtras = $oException->getExtra()) {
						if (DataHelper::isValidArray($aExtras)) {
							foreach ($aExtras as $sValue) {
								$sTextExtras .= '<ul>' . $sValue . '</ul>';
							}
							$sExceptionHTML .= sprintf('<li id="exception-extras no-wrap">%s</li>', $sTextExtras);
						}
					}
				}
				$sExceptionHTML .= '</ul>';
			}
			return $sExceptionHTML;
		} catch (Exception $e) {
			// Affichage de l'exception sous forme de message d'erreur
			return self::displayException($e, "Une erreur s'est produite lors de la récupération de l'Exception.");
		}
	}

	/**
	 * @brief	Méthode d'affichage du contenu d'un tableau.
	 *
	 * @param	array		$aArray			: contenu du tableau à afficher.
	 * @param	string		$sClass			: class CSS de l'élément.
	 * @param	boolean		$bRecursive		: (optionnel) si la méthode est appelée par elle-même.
	 * @return	string
	 * @author	durandcedric
	 */
	public static function debugArray($aArray = array(), $sClass = "code", $bRecursive = false) {
		// Initialisation des variables de construction
		$sStart					= $sClass		? "<span class=\"$sClass\">"	: "";
		$sEnd					= $sClass		? "</span>"						: "";
		$sClose					= $bRecursive	? ""							: "<br />";

		// Initialisation du format du contenu par défaut
		$sContentFormat			= "<span class=\"texte\">\"%s\"</span>";
		$sContent				= $aArray;

		// Fonctionnalité réalisée si le paramètre est un tableau
		if (DataHelper::isValidArray($aArray)) {
			// Récupération du nombre d'éléments
			$nCount				= count($aArray);

			// Initialisation du contenu
			$sContentFormat		= "%s";
			$sContent			= "<span class=\"native\">array</span>(<span class=\"nombre\">$nCount</span>) {<ul>";

			// Initialisation de la variable de boucle
			$nBoucle = 0;
			foreach ($aArray as $sKey => $sValue) {
				// Incrémentation du nombre de passage
				$nBoucle++;

				// Ajout d'une virgule de séparation
				$sNext			= "";
				if ($nCount > 1 && $nBoucle < $nCount) {
					$sNext		.= ",";
				}

				// Initialisation du format pour la clé par défaut
				$sKeyFormat		= "<span class=\"texte\">'%s'</span>";
				// Fonctionnalité réalisée si la clé est au format numérique
				if (DataHelper::isValidNumeric($sKey)) {
					$sKeyFormat	= "<span class=\"nombre\">%d</span>";
				}

				// Ajout de l'élément à la liste
				$sContent		.= "<li>[" . sprintf($sKeyFormat, $sKey) . "] => " . self::debugArray($sValue, $sClass, true) . $sNext . "</li>";
			}

			// Finalisation du contenu
			$sContent			.= "</ul>}";
		} elseif (strtolower($aArray) == "null" || strtolower($aArray) == "true" || strtolower($aArray) == "false") {
			// Le contenu représente un mot clé PHP
			$sContentFormat		= "<span class=\"native\">%s</span>";
			$sContent			= strtolower($aArray);
		} elseif (DataHelper::isValidBoolean($aArray)) {
			// Le contenu représente un bouléen
			$sContentFormat		= "<span class=\"native\">%s</span>";
			$sContent			= $aArray ? "true" : "false";
		} elseif (DataHelper::isValidNumeric($aArray)) {
			// Le contenu représente un nombre
			$sContentFormat		= "<span class=\"nombre\">%d</span>";
		}

		// Renvoi du contenu formaté
		return $sStart . sprintf($sContentFormat, $sContent) . $sEnd . $sClose;
	}

}
