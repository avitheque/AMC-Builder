<?php
// Chargement de la classe de FPDF
require_once FW_HELPERS . "/fpdf/fpdf.php";

/**
 * @brief	Classe de génération de documents PDF.
 *
 * @li	La classe FPDF génère un document avec des caractères au format ISO-8859.
 *
 * @name		PDFManager
 * @category	Model
 * @package		Document
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 120 $
 * @since		$LastChangedDate: 2018-05-07 21:15:40 +0200 (Mon, 07 May 2018) $
 * @see			{ROOT_PATH}/libraries/helpers/fpdf.php
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PDFManager extends FPDF {

	protected	$_filename					= "document";
	protected	$_contentType				= "application/pdf";
	protected	$_extension					= "pdf";

	// ============================================================================================
	//	@todo CONSTANTES DE FPDF
	// ============================================================================================

	const		ORIENTATION_L				= 'L';
	const		ORIENTATION_P				= 'P';

	const		PAGE_A1						= 'A1';
	const		PAGE_A2						= 'A2';
	const		PAGE_A3						= 'A3';
	const		PAGE_A4						= 'A4';
	const		PAGE_A5						= 'A5';

	const		UNIT_CM						= 'cm';
	const		UNIT_MM						= 'mm';
	const		UNIT_PT						= 'pt';

	const		ALIGN_CENTER				= 'C';
	const		ALIGN_JUSTIFY				= 'J';
	const		ALIGN_LEFT					= 'L';
	const		ALIGN_RIGHT					= 'R';

	const		STYLE_BOLD					= 'B';
	const		STYLE_BOLD_UNDERLINE		= 'BU';
	const		STYLE_BOLD_ITALIC			= 'BI';
	const		STYLE_BOLD_ITALIC_UNDERLINE	= 'BIU';
	const		STYLE_DEFAULT				= '';
	const		STYLE_ITALIC				= 'I';
	const		STYLE_ITALIC_UNDERLINE		= 'IU';
	const		STYLE_UNDERLINE				= 'U';

	const		FONT_ARIAL					= 'Arial';

	const		ID_WIDTH					= 0;
	const		ID_HEIGHT					= 1;

	const		FACTEUR_EXTENSION			= 4;

	// ============================================================================================
	//	@todo CONSTANTES DE CONSTRUCTION D'UNE PAGE
	// ============================================================================================

	const		DEFAULT_FONT_FAMILY			= self::FONT_ARIAL;
	const		DEFAULT_FONT_SIZE			= 10;
	const		DEFAULT_FONT_STYLE			= self::STYLE_DEFAULT;
	const		DEFAULT_FONT_COLOR			= "0,0,0";
	
	protected	$_default_font_family		= self::DEFAULT_FONT_FAMILY;
	protected	$_default_font_size			= self::DEFAULT_FONT_SIZE;
	protected	$_default_font_style		= self::DEFAULT_FONT_STYLE;
	protected	$_default_font_color		= self::DEFAULT_FONT_COLOR;

	const		DEFAULT_LINE_HEIGHT			= 10;
	const		DEFAULT_LINE_START			= 0;

	const		DEFAULT_MARGIN_BOTTOM		= 10;
	const		DEFAULT_MARGIN_LEFT			= 10;
	const		DEFAULT_MARGIN_RIGHT		= 10;
	const		DEFAULT_MARGIN_TOP			= 10;

	protected	$decodeUtf8					= true;

	protected	$CurLine					= 0;
	protected	$_middlePage				= null;

	protected	$lMargin					= 10;
	protected	$rMargin					= 10;
	protected	$tMargin					= 10;
	protected	$bMargin					= 10;

	protected	$hLine						= 10;
	protected	$iLine						= 5;

	protected	$_countPages				= 0;
	protected	$_countStartPages			= 0;

	protected	$_showHeader				= true;
	protected	$_showFooter				= true;

	/**
	 * @var		array								R		G		B
	 */
	protected	$_tableBorder				= 1;
	protected	$_tableFill					= true;
	protected	$_tableHeadFillColor		= array(230,	250,	255);
	protected	$_tableEddFillColor			= array(255,	255,	255);
	protected	$_tableOddFillColor			= array(255,	250,	230);

	/**
	 * @brief	Initialisation du document PDF.
	 *
	 * @param	string	$sOrientation
	 * @param	string	$sUnit
	 * @param	string	$sSize
	 * @param	boolean	$bDecodeUTF8
	 */
	public function __construct($sOrientation = self::ORIENTATION_P, $sUnit = self::UNIT_MM, $sSize = self::PAGE_A4, $bDecodeUTF8 = true) {
		// Construction du PARENT
		parent::__construct($sOrientation, $sUnit, $sSize);

		// Initialisation de la FONT
		$this->setFont(self::DEFAULT_FONT_FAMILY, self::DEFAULT_FONT_STYLE, self::DEFAULT_FONT_SIZE);

		// Initialisation des limites de la page
		$this->setLeftMargin(self::DEFAULT_MARGIN_LEFT);
		$this->setRightMargin(self::DEFAULT_MARGIN_RIGHT);
		$this->setTopMargin(self::DEFAULT_MARGIN_TOP);
		$this->setBottomMargin(self::DEFAULT_MARGIN_BOTTOM);

		// Initialisation de l'encodage
		$this->decodeUtf8					= $bDecodeUTF8;
	}

	/**
	 * @brief	Initialisation du paramètre de décodage UTF-8
	 *
	 * @li		Initialisation du décodage automatique des caractères UTF-8.
	 * @param	boolean	$bDecodeUTF8
	 * @return	void
	 */
	public function setDecodeUTF8($bDecodeUTF8 = true) {
		// Décodage des caractères UTF-8
		$this->decodeUtf8					= $bDecodeUTF8;
	}

	/**
	 * @brief	Nom du document
	 *
	 * @param	string	$sFilename
	 * @return	void
	 */
	public function setFilename($sFilename) {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sFilename						= utf8_decode($sFilename);
		}
		// Formatage du nom du fichier
		$this->_filename					= DataHelper::convertToString($sFilename, DataHelper::DATA_TYPE_FILE);
	}

	/**
	 * @brief	Entête de page
	 *
	 * @li		L'ensemble des instructions est réalisé pour chaque page du document.
	 * @return	void
	 */
	public function header() {
		/** @todo	Définition à implémenter dans le modèle par héritage */
	}

	/**
	 * @brief	Pied de page
	 *
	 * @li		L'ensemble des instructions est réalisé pour chaque page du document.
	 * @return	void
	 */
	public function footer() {
		/** @todo	Définition à implémenter dans le modèle par héritage */
	}

	//=============================================================================================
	//	@todo	INITIALISATION DES PARAMÈTRES DU DOCUMENT
	//=============================================================================================
	
	/**
	 * @brief	Initialisation de la couleur du TEXT par défault du document.
	 *
	 * @return	void
	 */
	public function setFontColor($nR, $nG = null, $nB = null) {
		// Fonctionnalité réalisée si le premier argument est un tableau
		if (DataHelper::isValidArray($nR)) {
			// Manipulation de l'argument
			$aParams			= $nR;
			$nR					= isset($aParams[0])	? (int) $aParams[0]		: null;
			$nG					= isset($aParams[1])	? (int) $aParams[1]		: null;
			$nB					= isset($aParams[2])	? (int) $aParams[2]		: null;
		} elseif (!DataHelper::isValidNumeric($nR) && is_null($nG) && is_null($nB)) {
			// Extraction du code couleur depuis le premier paramètre
			list($nR, $nG, $nB) = DataHelper::extractColorFromString($nR);
		} elseif (DataHelper::isValidNumeric($nR)) {
			// Typage du premier argument
			$nR					= (int) $nR;
		}
		
		// Initialisation du paramètre GREEN à partir de RED
		if (is_null($nG)) {
			$nG					= (int) $nR;
		}
		
		// Initialisation du paramètre BLUE à partir de GREEN
		if (is_null($nB)) {
			$nB					= (int) $nG;
		}
		
		// Inititialisation de la couleur du FONT
		$this->setTextColor($nR, $nG, $nB);
	}
	
	/**
	 * @brief	Réinitialisation du FONT par défault du document.
	 *
	 * @return	void
	 */
	public function resetFontDefault() {
		// Initialisation du FONT
		$this->setFont($this->_default_font_family, $this->_default_font_size, $this->_default_font_style);
		// Initialisation de la couleur du TEXT
		$this->setFontColor($this->_default_font_color);
	}
	
	/**
	 * @brief	Initialisation du FONT par défault du document.
	 *
	 * @param	string	$sFontFamily
	 * @param	string	$sFontStyle
	 * @param	string	$sFontSize
	 * @param	mixed	$sFontColor
	 * @return	void
	 */
	public function setFontDefault($sFontFamily = self::DEFAULT_FONT_FAMILY, $sFontStyle = self::DEFAULT_FONT_STYLE, $sFontSize = self::DEFAULT_FONT_SIZE, $sFontColor = self::DEFAULT_FONT_COLOR) {
		// Fonctionnalité réalisée si aucune page n'est présente
		$this->_default_font_family	= $sFontFamily;
		$this->_default_font_style	= $sFontStyle;
		$this->_default_font_size	= $sFontSize;
		$this->_default_font_color	= $sFontColor;
		$this->resetFontDefault();
	}

	/**
	 * @brief	Initialisation du titre dans l'entête de la page.
	 *
	 * @return	void
	 */
	public function autoPageStart() {
		// Fonctionnalité réalisée si aucune page n'est présente
		if (empty($this->page)) {
			// Initialisation de la première page
			$this->addPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
		}
	}

	/**
	 * @brief	Récupération de la marge du bas.
	 *
	 * @return	numeric
	 */
	public function getBottomMargin() {
		// Récupération de la marge du bas
		return $this->bMargin;
	}

	/**
	 * @brief	Récupération de la ligne courante.
	 *
	 * @return	numeric
	 */
	public function getCurentLine() {
		// Récupération de la ligne
		return $this->CurLine;
	}

	/**
	 * @brief	Récupération de la famille de police courante.
	 *
	 * @return	string
	 */
	public function getFontFamily() {
		// Récupération de la famille
		return $this->FontFamily;
	}

	/**
	 * @brief	Récupération de la taille de police courante.
	 *
	 * @return	numeric
	 */
	public function getFontSize() {
		// Récupération de la taille
		return $this->FontSize;
	}

	/**
	 * @brief	Récupération de la taille de police courante PT.
	 *
	 * @return	numeric
	 */
	public function getFontSizePt() {
		// Récupération de la taille
		return $this->FontSizePt;
	}

	/**
	 * @brief	Récupération du style de police courant.
	 *
	 * @return	string
	 */
	public function getFontStyle() {
		// Récupération du style
		return $this->FontStyle;
	}

	/**
	 * @brief	Récupération de la marge de gauche.
	 *
	 * @return	numeric
	 */
	public function getLeftMargin() {
		// Récupération de la marge de gauche
		return $this->lMargin;
	}

	/**
	 * @brief	Récupération de la largeur de ligne.
	 *
	 * @li	Si l'orientation n'est pas définie, la valeur courante sera récupérée.
	 *
	 * @param	string	$sOrientation
	 * @return	numeric
	 */
	public function getLineWidth($sOrientation = null) {
		// Récupération de la valeur courante
		if (empty($sOrientation)) {
			$sOrientation					= $this->CurOrientation;
		}

		// Récupération de la largeur de la page sans les marges
		return $this->getPageWidth($sOrientation) - $this->getLeftMargin() - $this->getRightMargin();
	}

	/**
	 * @brief	Récupération de la hauteur de page.
	 *
	 * @li	Si l'orientation n'est pas définie, la valeur courante sera récupérée.
	 *
	 * @param	string	$sOrientation
	 * @return	numeric
	 */
	public function getPageHeight($sOrientation = null) {
		// Récupération de la valeur courante
		if (empty($sOrientation)) {
			$sOrientation					= $this->CurOrientation;
		}

		// Récupération de l'index de la dimension selon l'orientation de la page
		$iHeight							= strtoupper($sOrientation) == self::ORIENTATION_P	? self::ID_HEIGHT	: self::ID_WIDTH;

		// Récupération de la LARGEUR de la page
		return $this->DefPageSize[$iHeight];
	}

	/**
	 * @brief	Récupération de la largeur de page.
	 *
	 * @li	Si l'orientation n'est pas définie, la valeur courante sera récupérée.
	 *
	 * @param	string	$sOrientation
	 * @return	numeric
	 */
	public function getPageWidth($sOrientation = null) {
		// Récupération de la valeur courante
		if (empty($sOrientation)) {
			$sOrientation					= $this->CurOrientation;
		}

		// Récupération de l'index de la dimension selon l'orientation de la page
		$iWidth								= strtoupper($sOrientation) == self::ORIENTATION_P	? self::ID_WIDTH	: self::ID_HEIGHT;

		// Récupération de la LARGEUR de la page
		return $this->DefPageSize[$iWidth];
	}

	/**
	 * @brief	Récupération de la marge de droite.
	 *
	 * @return	numeric
	 */
	public function getRightMargin() {
		// Récupération de la marge de droite
		return $this->rMargin;
	}

	/**
	 * @brief	Initialisation du style de tableau.
	 *
	 * @return	void
	 */
	public function setTableStyle($nBorder = 1, array $aHeadFillColor = array(230, 250, 255), array $aEddFillColor = array(255, 255, 255), array $aOddFillColor = array(255, 255, 255), $bFill = true) {
		$this->_tableBorder					= $nBorder;
		$this->_tableHeadFillColor			= $aHeadFillColor;
		$this->_tableEddFillColor			= $aEddFillColor;
		$this->_tableOddFillColor			= $aOddFillColor;
		$this->_tableFill					= $bFill;
	}

	/**
	 * @brief	Récupération de la marge du haut.
	 *
	 * @return	numeric
	 */
	public function getTopMargin() {
		// Récupération de la marge du haut
		return $this->tMargin;
	}

	/**
	 * @brief	Initialisation de la marge de bas de la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setBottomMargin($nParam = self::DEFAULT_MARGIN_BOTTOM) {
		$this->bMargin						= floatval($nParam);
		$this->setAutoPageBreak(true, $this->bMargin);
	}

	/**
	 * @brief	Initialisation de la position de ligne dans la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setCurrentLine($nParam) {
		$this->CurLine						= floatval($nParam);
	}

	/**
	 * @brief	Initialisation de la coloration de dessin.
	 *
	 * @param	string	$sParam
	 * @return	void
	 */
	/*public function setDrawColor($sParam) {
		$this->DrawColor = $sParam;
	}*/

	/**
	 * @brief	Initialisation de la famille de FONT.
	 *
	 * @param	string	$sParam
	 * @return	void
	 */
	public function setFontFamilly($sParam) {
		$this->setFont($sParam, $this->getFontStyle(), $this->getFontSizePt());
	}

	/**
	 * @brief	Initialisation de la taille de FONT.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setFontSizePt($nParam) {
		$this->setFont($this->getFontFamily(), $this->getFontStyle(), intval($nParam));
	}

	/**
	 * @brief	Initialisation du style de FONT.
	 *
	 * @param	string	$sParam
	 * @return	void
	 */
	public function setFontStyle($sParam) {
		$this->setFont($this->getFontFamily(), $sParam, $this->getFontSizePt());
	}

	/**
	 * @brief	Initialisation de la hauteur de l'espacement entre les lignes de texte.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setInterLine($nParam) {
		$this->iLine						= intval($nParam);
	}

	/**
	 * @brief	Initialisation de la hauteur de la ligne de texte.
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setLineHeight($nParam) {
		$this->hLine						= intval($nParam);
	}

	/**
	 * @brief	Initialisation du centre de la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setMiddlePosition($nParam) {
		$this->_middlePage					= floatval($nParam);
	}

	/**
	 * @brief	Initialisation de la hauteur de la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setPageHeight($nParam) {
		$this->CurPageSize[self::ID_HEIGHT]	= floatval($nParam);
	}

	/**
	 * @brief	Initialisation du nombre total de pages.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setPageTotal($nParam) {
		$this->_countPages					= intval($nParam);
		$this->_showFooter					= true;
	}

	/**
	 * @brief	Initialisation de la largeur de la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function setPageWidth($nParam) {
		$this->CurPageSize[self::ID_WIDTH]	= floatval($nParam);
	}

	/**
	 * @brief	Initialisation de l'encodage UTF-8.
	 *
	 * @param	bool	$bParam
	 * @return	void
	 */
	public function setUtf8Decode($bParam) {
		$this->decodeUtf8					= (bool) $bParam;
	}

	/**
	 * @brief	Mise à jour du centrage de la page.
	 *
	 * @param	numeric	$nParam
	 * @return	void
	 */
	public function updateMiddlePage($nParam) {
		$this->_middlePage					= floatval($nParam);
	}

	/**
	 * @brief	Mise à jour des dimentions de la page.
	 *
	 * @param	string	$sOrientation
	 * @return	void
	 */
	public function updatePageDimension($sOrientation = self::ORIENTATION_P) {
		// Récupération des informations de LARGEUR et de HAUTEUR selon l'orientation
		$nWidth								= $this->getPageWidth($sOrientation);
		$nHeight							= $this->getPageHeight($sOrientation);

		// Définition de la largeur de la page
		$this->setPageWidth($nWidth);

		// Définition de la hauteur de la page
		$this->setPageHeight($nHeight);

		// Définition du centre de la page
		$this->updateMiddlePage($nWidth / 2);
	}

	//=============================================================================================
	//	@todo	DEBUG
	//=============================================================================================

	/**
	 * @brief	Calibre le centre de la page.
	 *
	 * Méthode permettant de déterminer visuellement la position centrale de la page.
	 *
	 * @li	Méthode chargée en fin de page en [MODE_DEBUG] par défaut.
	 *
	 * @return	void
	 */
	public function testMiddlePosition() {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Ligne positionnée à 5cm
		$this->setCurrentLine(50);

		// Récupération des paramètres de coloration originales
		preg_match("@^([0-9\.]+).([0-9\.]+).([0-9\.]+).*$@", $this->TextColor, $aMatched);
		$nR = DataHelper::get($aMatched, 1, DataHelper::DATA_TYPE_FLT, 0);
		$nG = DataHelper::get($aMatched, 2, DataHelper::DATA_TYPE_FLT, 0);
		$nB = DataHelper::get($aMatched, 3, DataHelper::DATA_TYPE_FLT, 0);

		// Modification de la couleur de texte
		$this->setTextColor(255, 0, 0);

		// Affichage du titre du processus d'évaluation
		$this->title("[MODE_DEBUG]", 40);

		// Réinitialisation de la couleur du text initial
		$this->setTextColor(intval($nR * 255), intval($nG * 255), intval($nB * 255));
		
		// Saut de lignes
		$this->addLine(5);

		// Information de la configuration actuelle
		$this->title("Évaluation de la position centrale de la page", 20);

		// Changement de FONT
		$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 10);
		$this->textMiddle("Actuellement la position centrale est définie à " . $this->_middlePage);

		// Saut de lignes
		$this->addLine(5);

		// Changement de FONT
		$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 30);

		// Texte à gauche sans saut de ligne
		$this->textLeft("| GAUCHE", false);

		// Texte au centre sans saut de ligne
		$this->textMiddle("CENTRE", false);

		// Texte à droite sans saut de ligne
		$this->textRight("DROITE |", false);
	}

	/**
	 * @brief	Calibre le centre de la page.
	 *
	 * Méthode permettant de déterminer visuellement la position des marges de la page.
	 *
	 * @li	Méthode chargée en fin de page en [MODE_DEBUG] par défaut.
	 *
	 * @return	void
	 */
	public function testMargesPosition() {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Couleur de fond de l'indicateur
		$this->setFillColor(0, 0, 0);

		// Taille de l'indicateur
		$nTaille							= 5;

		// Indicateur [+] en haut à gauche
		$this->setXY($this->lMargin, $this->tMargin);
		$this->cell($nTaille, $nTaille, "", 1, null, null, true);

		// Indicateur [+] en haut à droite
		$this->setXY($this->CurPageSize[self::ID_WIDTH] - $this->rMargin - $nTaille, $this->tMargin);
		$this->cell($nTaille, $nTaille, "", 1, null, null, true);

		// Indicateur [+] en bas à gauche
		$nCurrent = $this->CurPageSize[self::ID_HEIGHT] - $this->bMargin - $nTaille;
		$this->setXY($this->lMargin, $nCurrent);
		$this->cell($nTaille, $nTaille, "", 1, null, null, true);

		// Indicateur [+] en bas à droite
		$nCurrent = $this->CurPageSize[self::ID_HEIGHT] - $this->bMargin - $nTaille;
		$this->setXY($this->CurPageSize[self::ID_WIDTH] - $this->rMargin - $nTaille, $nCurrent);
		$this->cell($nTaille, $nTaille, "", 1, null, null, true);
	}

	//=============================================================================================
	//	@todo	SURCHARGE DE FPDF
	//=============================================================================================

	/**
	 * @brief	Surchage de la methode FPDF::addPage().
	 *
	 * @param	string	$sOrientation
	 * @param	string	$sSize
	 * @param	string	$nRotation
	 * @return	void
	 */
	public function addPage($sOrientation = 'P', $sSize = 'A4', $nRotation = 0) {
		// Ajout d'une nouvelle page
		parent::addPage($sOrientation, $sSize, $nRotation);

		// Initialisation des limites de la page
		$this->updatePageDimension($sOrientation);

		// Initialisation de la position de la ligne
		$this->setCurrentLine($this->tMargin + $this->hLine);
	}

	/**
	 * @brief	Surchage de la methode FPDF::output().
	 *
	 * @param	string	$dest
	 * @param	string	$name
	 * @param	boolean	$isUTF8
	 * @return	file
	 */
	public function output($dest = '', $name = '', $isUTF8 = true) {
		// Fonctionnalité réalisée en MODE_DEBUG
		if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
			// Désactivation des entêtes et pieds de pages
			$this->_showHeader				= false;
			$this->_showFooter				= false;
			
			// Réinitialisation de la couleur de texte par défaut
			$this->setTextColor(0, 0, 0);

			// Ajout d'une nouvelle page
			$this->addPage(self::ORIENTATION_P, self::PAGE_A4, 0);
			// Calibration du format de la page
			$this->testMargesPosition();
			$this->testMiddlePosition();

			// Ajout d'une nouvelle page
			$this->addPage(self::ORIENTATION_L, self::PAGE_A4, 0);
			// Calibration du format de la page
			$this->testMargesPosition();
			$this->testMiddlePosition();
		}
		
		// Remplacement des caractères spéciaux
		$sFileName = DataHelper::convertToString(trim($dest), DataHelper::DATA_TYPE_FILE);

		// Rendu du document PDF
		return parent::output($dest, $name, $isUTF8);
	}

	//=============================================================================================
	//	@todo	CRÉATION DES ÉLÉMENTS
	//=============================================================================================

	/**
	 * @brief	Ajout d'une cellule dans la page.
	 *
	 * @li	Section automatique sur plusieurs cellules si le texte est trop long
	 *
	 * @param	numeric	$nWidth
	 * @param	numeric	$nHigth
	 * @param	string	$sText
	 * @param	numeric	$nTableBorder
	 * @param	char	$sAlign
	 * @param	boolean	$bTableFill
	 * @return	void
	 */
	public function addCell($nWidth, $nHigth = 0, $sText = '', $nTableBorder = 0, $sAlign = '', $bTableFill = false) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText							= utf8_decode($sText);
		}

		// Création d'une cellule permettant de gérer la largeur de page
		$this->multiCell($nWidth, $nHigth, $sText, $nTableBorder, $sAlign, $bTableFill);
	}

	/**
	 * @brief	Ajout d'une ligne dans la page.
	 *
	 * @param 	numeric	$nCountLine
	 * @param 	boolean	$bPositionY
	 * @param 	integer	$nX
	 * @return	void
	 */
	public function addLine($nCountLine = 1, $bPositionY = false, $nX = null) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Ajout du nombre de ligne
		$this->CurLine += $this->iLine * $nCountLine;
		for ($nCount = 0 ; $nCount <= $nCountLine ; $nCount++) {
			$this->addCell(0, 0);
		}

		// Fonctionnalité de mise à jour de la position en haut de page
		if ($this->CurLine > $this->getPageHeight()) {
			$this->CurLine					= $this->getTopMargin();
		}
		
		// Fonctionnalité de mise à jour de la position de la ligne
		if ($bPositionY) {
			$this->setY($this->CurLine);
		}
		
		// Fonctionnalité réalisée si la position LEFT est passée en argument
		if (! is_null($nX)) {
			$this->setX($nX);
		}
	}

	/**
	 * @brief	Création d'un texte à gauche.
	 *
	 * @param	string	$sText
	 * @param	boolean	$bAddLine
	 * @return	void
	 */
	public function textLeft($sText = '', $bAddLine = true) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText							= utf8_decode($sText);
		}

		// Ajout du texte à gauche de la page
		$this->text($this->lMargin, $this->CurLine, $sText);

		// Ajout d'une nouvelle ligne
		if ($bAddLine == true) {
			$this->addLine();
		}
	}

	/**
	 * @brief	Création d'un texte centré.
	 *
	 * @param	string	$sText
	 * @param	boolean	$bAddLine
	 * @return	void
	 */
	public function textMiddle($sText = '', $bAddLine = true) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText							= utf8_decode($sText);
		}

		// Ajout du texte au niveau du centre de la page
		$this->text(($this->CurPageSize[self::ID_WIDTH] / 2) - ($this->getStringWidth($sText) / 2), $this->CurLine, $sText);

		// Ajout d'une nouvelle ligne
		if ($bAddLine == true) {
			$this->addLine();
		}
	}

	/**
	 * @brief	Création d'un texte à droite.
	 *
	 * @param	string	$sText
	 * @param	boolean	$bAddLine
	 * @return	void
	 */
	public function textRight($sText = '', $bAddLine = true) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText							= utf8_decode($sText);
		}

		// Ajout du texte à droite de la page
		$this->text($this->CurPageSize[self::ID_WIDTH] - $this->getStringWidth($sText) - $this->rMargin, $this->CurLine, $sText);

		// Ajout d'une nouvelle ligne
		if ($bAddLine == true) {
			$this->addLine();
		}
	}

	/**
	 * @brief	Création d'un titre.
	 * @param	string	$sTitre
	 * @param	integer	$nSize
	 * @param	string	$sStyle
	 * @param	string	$sFamily
	 * @return	void
	 */
	public function title($sTitre, $nSize = 16, $sStyle = self::STYLE_BOLD, $sFamily = self::FONT_ARIAL) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Récupération du format de FONT d'origine
		$sFontFamilyOrigine					= $this->FontFamily;
		$sFontStyleOrigine					= $this->FontStyle;
		$nFontSizeOrigine					= $this->FontSizePt;

		// Changement de la FONT
		$this->setFont($sFamily, $sStyle, $nSize);
		// Ajout du texte au niveau du centre de la page
		$this->textMiddle($sTitre, null);

		// Réinitialisation de la FONT courante
		$this->setFont($sFontFamilyOrigine, $sFontStyleOrigine, $nFontSizeOrigine);

		// Ajout d'une nouvelle ligne
		$this->addLine();
	}

	/**
	 * @brief	Création d'un tableau.
	 *
	 * @param	array	$aArray
	 * @param	array	$aDimensions
	 * @param	array	$aAligns
	 * @param	numeric	$nHeaderSize
	 * @param	string	$sHeaderStyle
	 * @param	string	$sHeaderFamily
	 * @return	void
	 */
	public function table($aArray = array(), $aDimensions = array(), $aAligns = array(), $nHeaderSize = self::DEFAULT_FONT_SIZE, $sHeaderStyle = self::STYLE_BOLD, $sHeaderFamily = self::FONT_ARIAL) {
		// Ajout automatique de la première page
		$this->autoPageStart();

		// Récupération du format de FONT d'origine
		$sFontFamilyOrigine					= $this->FontFamily;
		$sFontStyleOrigine					= $this->FontStyle;
		$nFontSizeOrigine					= $this->FontSizePt;

		// Récupération des informations de l'entête
		$nCount								= count($aArray[0]);

		// Parcours du tableau
		$nLine								= 0;
		foreach($aArray as $aLine) {
			// Détermination de la taille maximale
			$nMaximumWidth					= $this->getPageWidth() - $this->lMargin - $this->rMargin;

			// Formatage UNIQUEMENT de la première ligne
			if ($nLine == 0) {
				// Alignement au centre
				$sAlign						= self::ALIGN_CENTER;
				$sCurrentFamily				= $sFontFamilyOrigine;
				$sCurrentStyle				= $sHeaderStyle;
				$sCurrentSize				= $nHeaderSize;

				// Récupération de la couleur du HEAD
				list($nR, $nG, $nB)			= $this->_tableHeadFillColor;
			} else {
				// Alignement à gauche
				$sAlign						= self::ALIGN_LEFT;
				$sCurrentFamily				= $sFontFamilyOrigine;
				$sCurrentStyle				= $sFontStyleOrigine;
				$sCurrentSize				= $nFontSizeOrigine;

				if ($nLine%2) {
					// Récupération de la couleur des lignes PAIRES
					list($nR, $nG, $nB)		= $this->_tableEddFillColor;
				} else {
					// Récupération de la couleur des lignes IMPAIRES
					list($nR, $nG, $nB)		= $this->_tableOddFillColor;
				}
			}

			// Coloration du fond de la cellule
			$this->setFillColor($nR, $nG, $nB);

			// Définition du format du texte
			$this->setFont($sCurrentFamily, $sCurrentStyle, $sCurrentSize);

			// La première cellule de chaque ligne respecte la marge de gauche
			$nLineStart						= $this->lMargin;
			$nColumn						= 0;
			foreach ((array) $aLine as $sFiled => $sText) {
				// Fonctionnalité réalisée si un format de colonne est défini sur les clés de colonne
				if (isset($aDimensions[$sFiled]) && !isset($aDimensions[$nColumn])) {
					// Enregistrement du format pour les colonnes du TBODY
					$aDimensions[$nColumn]	= $aDimensions[$sFiled];
				}

				// Fonctionnalité réalisée si un format de colonne est défini sur les clés de colonne
				if (isset($aAligns[$sFiled]) && !isset($aAligns[$nColumn])) {
					// Enregistrement du format pour les colonnes du TBODY
					$aAligns[$nColumn]		= $aAligns[$sFiled];
				}

				// Détermination du nombre de colonnes restantes à traiter
				$nColumnRest				= $nCount - $nColumn;
				if ($nColumnRest == 0) {
					$nColumnRest			= 1;
				}

				// Redimensionnement de la cellule
				if (array_key_exists($nColumn, $aDimensions) && preg_match("@^([0-9]+)%$@", $aDimensions[$nColumn], $aMatched)) {
					// Définition de la dimension pour la cellule courante
					$nMaxi					= $this->getPageWidth() - $this->lMargin - $this->rMargin;
					$nWidth					= $nMaxi * $aMatched[1] / 100;
				} else {
					// Définition de la dimension pour la cellule courante
					$nWidth					= isset($aDimensions[$nColumn]) ? floatval($aDimensions[$nColumn]) : $nMaximumWidth / $nColumnRest;
				}
				// Actualisation de la taille maximale d'une colonne
				$nMaximumWidth				-= $nWidth;

				// Alignement de la cellule à partir de la ligne [1]
				if ($nLine > 0 && array_key_exists($nColumn, $aAligns)) {
					// Définition de l'alignement pour la cellule courante
					$sAlign					= $aAligns[$nColumn];
				}

				// Forçage d'un éventuel tableau en texte
				$sText = implode("\n", (array) $sText);

				// Décodage des caractères UTF-8
				if ($this->decodeUtf8) {
					$sText = utf8_decode($sText);
				}

				// Initialisation de la position de la cellule
				$this->setX($nLineStart);

				// Création de la cellule
				$this->cell($nWidth, ($sCurrentSize / 2), $sText, $this->_tableBorder, 0, $sAlign, $this->_tableFill, '');
				$nLineStart					+= $nWidth;
				$nColumn++;
			}

			// Ajout d'une ligne
			$this->setY($this->getY() + ($sCurrentSize / 2));
			$nLine++;
		}

		// Réinitialisation de la taille des caractères par défaut
		$this->setFont($sFontFamilyOrigine, $sFontStyleOrigine, $nFontSizeOrigine);
	}

	//=============================================================================================
	//	@todo	GÉNÉRATION DU DOCUMENT
	//=============================================================================================

	/**
	 * @brief	Génération du document PDF.
	 *
	 * @return	void
	 */
	public function render() {
		// Construction du document au format UTF-8
		return parent::output($this->_filename . "." . $this->_extension, 'D', $this->decodeUtf8);
	}
}
