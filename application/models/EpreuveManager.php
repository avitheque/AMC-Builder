<?php
/**
 * @brief	Classe de génération du document PDF de l'épreuve.
 *
 * @name		EpreuveManager
 * @category	Model
 * @package		Document
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EpreuveManager extends PDFManager {

	private		$_libelleEpreuve	= null;
	private		$_libelleDuree		= null;
	private		$_libelleSalle		= null;
	private		$_libelleStage		= null;
	private		$_titreEpreuve		= null;

	/**
	 * @brief	Initialisation des information de l'épreuve.
	 *
	 * @param	string	$sText
	 * @return	void
	 */
	public function setEpreuve($sText = '') {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText = utf8_decode($sText);
		}

		$this->_libelleEpreuve = $sText;
	}

	/**
	 * @brief	Initialisation de la durée de l'épreuve.
	 *
	 * @param	string	$sText
	 * @return	void
	 */
	public function setDuree($sText = '') {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText = utf8_decode($sText);
		}

		$this->_libelleDuree = $sText;
	}

	/**
	 * @brief	Initialisation de la salle où aura lieu l'épreuve.
	 *
	 * @param	string	$sText
	 * @return	void
	 */
	public function setSalle($sText = '') {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText = utf8_decode($sText);
		}

		$this->_libelleSalle = $sText;
	}

	/**
	 * @brief	Initialisation du nom du stage.
	 *
	 * @param	string	$sText
	 * @return	void
	 */
	public function setStage($sText = '') {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText = utf8_decode($sText);
		}

		$this->_libelleStage = $sText;
	}

	/**
	 * @brief	Initialisation du titre de l'épreuve.
	 *
	 * @param	string	$sText
	 * @return	void
	 */
	public function setTitre($sText = '') {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sText = utf8_decode($sText);
		}

		$this->_titreEpreuve = $sText;
	}

	/**
	 * @brief	Page de répartition des candidats pour l'épreuve par salle
	 *
	 * @param	array	$aArray
	 * @param	array	$aDimensions
	 * @param	array	$aAligns
	 * @param	array	$nHeaderSize
	 * @param	array	$sHeaderStyle
	 */
	public function buildPage($aArray = array(), $aDimensions = array(), $aAligns = array(), $nHeaderSize = self::DEFAULT_FONT_SIZE, $sHeaderStyle = self::STYLE_BOLD, $sHeaderFamily = self::FONT_ARIAL) {
		// Ajout automatique de la première page
		$this->addPage();

		// Définition des marges de la page
		$this->setMargins(5, 5, 5);

		// Initialisation de la position verticale
		$nY = 0;

		// Initialisation de la position en respectant les marges de la page
		$this->setXY($this->lMargin, $nY);

		// Titre de la page avec le libellé du stage
		if (!empty($this->_libelleStage)) {
			$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 40);
			$this->cell($this->getLineWidth(), $this->FontSizePt, $this->_libelleStage, 0, 0, self::ALIGN_CENTER);

			// Saut de ligne
			$nY += ($this->FontSizePt / 2);
			$this->setY($nY);
		}

		// Titre de l'épreuve avec le libellé du stage
		if (!empty($this->_titreEpreuve)) {
			$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 30);
			$this->cell($this->getLineWidth(), $this->FontSizePt, $this->_titreEpreuve, 0, 0, self::ALIGN_CENTER);

			// Saut de ligne
			$nY += ($this->FontSizePt / 2) + 10;
			$this->setY($nY);
		}

		// Information de la salle
		if (!empty($this->_libelleSalle)) {
			$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 30);
			$this->cell($this->getLineWidth(), $this->FontSizePt, $this->_libelleSalle, 0, 0, self::ALIGN_CENTER);

			// Saut de ligne
			$nY += ($this->FontSizePt / 2);
			$this->setY($nY);
		}

		// Information de l'épreuve
		if (!empty($this->_libelleEpreuve)) {
			$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 20);
			$this->cell($this->getLineWidth(), $this->FontSizePt, $this->_libelleEpreuve, 0, 0, self::ALIGN_CENTER);

			// Saut de ligne
			$nY += ($this->FontSizePt / 2);
			$this->setY($nY);
		}

		// Durée de l'épreuve
		if (!empty($this->_libelleEpreuve)) {
			$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD, 20);
			$this->cell($this->getLineWidth(), $this->FontSizePt, $this->_libelleDuree, 0, 0, self::ALIGN_CENTER);

			// Saut de ligne
			$nY += ($this->FontSizePt / 2);
			$this->setY($nY);
		}

		// Saut de ligne
		$nY = $this->getY();
		$this->setXY($this->lMargin, $nY);

		// Saut de ligne
		$nY += 10;
		$this->setY($nY);

		// Construction du tableau
		$this->setFont(self::FONT_ARIAL, self::STYLE_DEFAULT, 15);
		$this->table($aArray, $aDimensions, $aAligns, $nHeaderSize, $sHeaderStyle, $sHeaderFamily);
	}


	/**
	 * @brief	Ajout de la zone d'émargement des surveillants.
	 *
	 * @param	string	$sString
	 */
	public function addSignature($sString = "Émargement des surveillants") {
		// Décodage des caractères UTF-8
		if ($this->decodeUtf8) {
			$sString = utf8_decode($sString);
		}

		// Initialisation de la position verticale
		$nY = $this->getY();
		$this->setY($nY);

		// Zone d'émargement des surveillants
		$this->setFont(self::FONT_ARIAL, self::STYLE_BOLD . self::STYLE_ITALIC . self::STYLE_UNDERLINE, 20);
		$this->cell($this->getLineWidth(), $this->FontSizePt, $sString, 0, 0, self::ALIGN_LEFT);
	}
}
