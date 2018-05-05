<?php
/**
 * @brief	Helper de création d'un planning au format PDF.
 *
 * Vue étendue de la progression au format PDF.
 *
 * @name		PlanningPDFHelper
 * @category	Helper
 * @package		View
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 111 $
 * @since		$LastChangedDate: 2018-03-25 14:37:49 +0200 (Sun, 25 Mar 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PlanningPDFHelper extends PlanningHelper {

	/**
	 * Constante de construction de la liste des identifiants à exclure du résultat.
	 * @var		char
	 */
	const		EXCLUDE_SEPARATOR				= ",";

	/**
	 * Constante de construction de la liste des jours et heures non travaillées.
	 * @var		char
	 */
	const		DEPRECATED_LIST_SEPARATOR		= ",";
	const		DEPRECATED_ITEM_SEPARATOR		= "-";

	/**
	 * Construction de l'interface graphique HTML
	 * 
	 * @li		PLANNING_DEFAULT_FORMAT			: type visuel de rendu parmis [calendrier, progression]
	 * var		string
	 */
	const		FORMAT_CALENDAR					= "calendar";
	const		FORMAT_PROGRESSION				= "progression";
	const		PLANNING_DEFAULT_FORMAT			= self::FORMAT_CALENDAR;
	
	const		PLANNING_DEFAULT_CLASSNAME		= "diary";
	const		PLANNING_HOLIDAY_CLASSNAME		= "diary holiday";
	const		PLANNING_HEADER_CLASSNAME		= "header";

	const 		PLANNING_VALID_CLASS			= "ui-widget-content ui-state-default";
	const 		PLANNING_DEPRECATED_CLASS		= "ui-widget-content ui-state-disabled";
	const		PLANNING_WIDTH_RATIO			= 99;
	
	const		PLANNING_HEPHEMERIDE			= 86400;

	/**
	 * Construction de l'interface graphique PDF
	 * var		string
	 */
	const		PDF_LIBELLE_CENTRE				= "CENTRE DE FORMATION AUX SYSTEMES D'INFORMATION ET DE COMMUNICATION DE LA GENDARMERIE";
	const		PDF_FORMAT_TITRE				= "Emploi du temps du stage de formation : %s";
	const		PDF_FORMAT_SEMAINE				= "Semaine %d (du %s au %s)";
	const		PDF_FORMAT_SIGNATURE			= "ORIGINAL SIGNÉ LE %s";
	const		PDF_FORMAT_MODIFICATION			= "MODIFIÉ LE %s";
	const		PDF_LIBELLE_BAS_PAGE			= "En position de service, les personnels non positionnés à la progression restent à la disposition de leurs chefs de section respectifs";
	
	const		PDF_PROGRESSION_CELL_HEIGHT		= 30;
	const		PDF_PROGRESSION_CELL_MARGIN		= 2;
	
	const		PDF_POSITION_TITRE				= 0;
	const		PDF_POSITION_DESCRIBE			= 15;
	const		PDF_POSITION_PARTICIPANT		= 20;
	
	const		PDF_INTERLINE_TITRE_SIZE		= 15;
	const		PDF_INTERLINE_PARTICIPANT_SIZE	= 3;
	
	protected	$_planning_cell_width			= 5;
	protected	$_planning_cell_height			= self::PDF_PROGRESSION_CELL_HEIGHT;

	/**
	 * @brief	instance PDFManager du planning.
	 * @var		PDFManager
	 */
	protected	$_document						= null;

	/**
	 * @brief	Titre du document.
	 * @var		string
	 */
	protected	$_document_title				= "Emploi du temps";
	
	/**
	 * @brief	Titre de la signature.
	 * @var		string
	 */
	protected	$_signataire_titre				= "GRD NOM Prénom,";
	
	/**
	 * @brief	Fonction de la signature.
	 * @var		string
	 */
	protected	$_signataire_fonction			= "commandant du Centre.";
	
	/**
	 * @brief	Date de la signature.
	 * @var		string
	 */
	protected	$_signataire_date				= "DD/MM/YYYY";
	
	/**
	 * @brief	Dernière modification.
	 * @var		string
	 */
	protected	$_modification_date				= "DD/MM/YYYY";

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	date	$dDateStart				: Date de début du planning [Y-m-d], possibilité de donner une date au format [jj/mm/aaaa].
	 * @param	integer	$nNbDays				: Nombre de jours à afficher [1-7].
	 * @param	integer	$nStartHour				: Heure de début pour chaque jour.
	 * @param	integer	$nEndHour				: Heure de fin pour chaque jour.
	 * @param	string	$sDeprecatedHours		: Liste d'heures non travaillées, séparées par le caractère [,].
	 * @param	integer	$nTimerSize				: Nombre de minutes par bloc.
	 * @return	string
	 */
	public function __construct($dDateStart = null, $nNbDays = self::PLANNING_DAYS, $nStartHour = self::PLANNING_HOUR_START, $nEndHour = self::PLANNING_HOUR_END, $sDeprecatedHours = self::PLANNING_DEPRECATED_HOURS, $sDeprecatedDays = self::PLANNING_DEPRECATED_DAYS, $nTimerSize = self::PLANNING_TIMER_SIZE) {
		// Construction du PARENT
		parent::__construct($dDateStart, $nNbDays, $nStartHour, $nEndHour, $sDeprecatedHours, $sDeprecatedDays, $nTimerSize);
		
		// Désactivation du rendu HTML
		ViewRender::setNoRenderer(true);
		
		// Construction du document PDF
		$this->_document						= new PDFManager(PDFManager::ORIENTATION_L);

		// Initialisation du FONT par défaut du document
		$this->_document->setFontDefault(PDFManager::FONT_ARIAL, 7, PDFManager::STYLE_BOLD, PDFManager::DEFAULT_FONT_COLOR);
	}

	/**
	 * @brief	Initialisation de la largeur d'une cellule de plannification
	 *
	 * @param	integer	$nWidth					: Largeur en pixels.
	 * @return	string
	 */
	public function setCellWidth($nWidth = self::DEFAULT_CELL_WIDTH) {
		$this->_nCellWidth						= $nWidth;
	}

	/**
	 * @brief	Initialisation du titre du panneau.
	 *
	 * @param	string	$sTitle					: titre du panneau.
	 * @return	void
	 */
	public function setTitre($sTitle = null) {
		$this->_title							= $sTitle;
	}

	/**
	 * @brief	Initialisation du titre du panneau.
	 *
	 * @param	string	$sTitle					: titre du panneau.
	 * @return	void
	 */
	public function setDocumentTitre($sTitle = null) {
		$this->_document_title					= $sTitle;
	}

	/**
	 * @brief	Initialisation du titre de la signature.
	 *
	 * @param	string	$sTitre					: GRD NOM Prénom.
	 * @return	void
	 */
	public function setSignataireTitre($sTitre = null) {
		$this->_signataire_titre				= $sTitre;
	}

	/**
	 * @brief	Initialisation de la fonction de la signature.
	 *
	 * @param	string	$sFonction				: commandant le Centre.
	 * @return	void
	 */
	public function setSignataireFonction($sFonction = null) {
		$this->_signataire_fonction				= $sFonction;
	}

	/**
	 * @brief	Initialisation de la date de la mise en signature.
	 *
	 * @param	string	$pDate					: date de signature de l'original.
	 * @return	void
	 */
	public function setSignataireDate($pDate = null) {
		$sString								= "";
		$sDate									= DataHelper::dateMyToFr($pDate);
		// Fonctionnalité réalisée si la date est valide
		if (DataHelper::isValidDate($sDate)) {
			$sString							= sprintf(self::PDF_FORMAT_SIGNATURE, $sDate);
		}
		$this->_signataire_date					= $sString;
	}

	/**
	 * @brief	Initialisation de la date de modification du document.
	 *
	 * @param	string	$pDate					: date de modification du document.
	 * @return	void
	 */
	public function setModificationDate($pDate = null) {
		$sString								= "";
		$sDate									= DataHelper::dateMyToFr($pDate);
		// Fonctionnalité réalisée si la date est valide
		if (DataHelper::isValidDate($sDate)) {
			$sString							= sprintf(self::PDF_FORMAT_MODIFICATION, $sDate);
		}
		$this->_modification_date				= $sString;
	}

	/**
	 * @brief	Construction du document PDF
	 *
	 * @return	string
	 */
	private function _buildProgressionPDF() {
		if (!$this->_build) {
			// Construction de la plage horaire
			$this->_build						= true;
			// Mise à jour du volume horaire pour prendre en compte la pause méridienne
			$this->_volume_horaire				+= $this->_planning_repas_duree > 10 ? intval($this->_planning_repas_duree/$this->_planning_timer_size) : $this->_planning_repas_duree;
			// Détermination de la largeur d'une cellule
			$this->_planning_cell_width			= ($this->_document->getLineWidth() / $this->_volume_horaire);
		}

		// Découpage du volume horaire
		$nColonne								= 0;
		$nMinuteDebut							= $this->_planning_minute_AM;
		$nMinuteFin								= $this->_planning_duree_cours;
		$nDocumentTop							= $this->_document->getY();
		$nDocumentLeft							= $this->_document->getLeftMargin();
		$nPositionLeft							= $nDocumentLeft;
		
		// Parcours de chaque plage horaire
		for ($heure = $this->_planning_debut ; $heure <= $this->_planning_fin ; $heure += $this->_tranche_horaire) {
			// Positionnement de la cellule d'entête
			$nPositionTop						= $nDocumentTop;
			$nPositionLeft						= $this->_document->getLeftMargin() + ($this->_planning_cell_width * $nColonne);
			$this->_document->setXY($nPositionLeft, $nPositionTop);
			
			// Construction de l'entête
			$this->_document->setFontSize(7);
			$this->_document->setFontStyle(PDFManager::STYLE_BOLD);
			
			// Titre de l'entête
			$sHeadTitle							= sprintf(self::PLANNING_TIME_FORMAT, $heure, $nMinuteDebut) . " - " . sprintf(self::PLANNING_TIME_FORMAT, $heure, $nMinuteFin);
			
			// Fonctionnalié réalisée si l'heure actuelle correspond à la pause méridienne
			if ($heure == $this->_planning_repas) {
				// Modification du titre
				$sHeadTitle = $this->_planning_repas_titre;
				$nMinuteDebut					+= $this->_planning_minute_PM;
				$nMinuteFin						+= $this->_planning_minute_PM;
				// Recalcul des minutes de DEBUT
				if ($nMinuteDebut >= 60) {
					$nMinuteDebut				= 0;
				}
				// Recalcul des minutes de FIN
				if ($nMinuteFin >= 60) {
					$nMinuteFin					= 0;
				}
			}
			
			// Création de l'entête du planning
			$this->_document->setFillColor(200, 200, 200);
			$this->_document->cell($this->_planning_cell_width, 5, $sHeadTitle, 1, 0, PDFManager::ALIGN_CENTER, true);
			
			// Création du corps du planning
			$nPositionTop						+= 5;
			// Parcours de chaque jour
			for ($timestamp = $this->_timestamp_debut ; $timestamp < $this->_timestamp_fin ; $timestamp += self::PLANNING_HEPHEMERIDE) {
				// Passage à la journée suivante
				$this->_document->setXY($nPositionLeft, $nPositionTop);
				
				// Initialisation de la largeur de cellule
				$nWidth							= $this->_planning_cell_width;
				
				// Initialisation du fond de la progression
				if ($nColonne == 0) {
					// Couleur de fond du document
					$this->_document->setFillColor(200, 200, 200);
					$this->_document->cell($this->_document->getLineWidth(), $this->_planning_cell_height, "", 1, 1, PDFManager::ALIGN_CENTER, true);
				}
				
				// Couleur de fond de la tâche
				$this->_document->setFillColor(255);

				// Fonctionnalité réalisée pour l'heure du repas
				if ($heure == $this->_planning_repas) {
					// Extraction des informations de la progression à partir de la DATE
					$this->_planning_jour_id	= date("N", $timestamp);
					// Initialisation du libellé du jour
					$sLibelleJour				= $this->_liste_planning_semaine[$this->_planning_jour_id] . " " . date('d', $timestamp);
					// Renseignement de la cellule avec le libellé du jour
					$this->_document->cell($this->_planning_cell_width, $this->_planning_cell_height, $sLibelleJour, 1, 1, PDFManager::ALIGN_CENTER, true);
				} else {
					// Récupération de la progression si elle existe
					$oItem = @$this->_aItems[date("o-m-d", $timestamp)][sprintf('%02d:%02d', $heure, 0)];
					
					// Remplissage de la progression
					if (is_object($oItem)) {
						// Récupération de la position de la cellule
						$nPositionX				= $this->_document->getX();
						$nDuree					= $oItem->getDuration();
						$nCellWidth				= $this->_planning_cell_width * $nDuree;
						
						$nPositionTitre			= $nPositionTop + self::PDF_POSITION_TITRE;
						$nPositionParticipant	= $nPositionTop + self::PDF_POSITION_PARTICIPANT;
						
						$aParticipants			= $oItem->getParticipant();
						
						// Construction de la cellule avec la description
						$this->_document->setFontSize(8);
						$this->_document->setFontStyle(PDFManager::STYLE_DEFAULT);
						$this->_document->cell($nCellWidth, $this->_planning_cell_height, nl2br($oItem->getDescribe()), 1, 1, PDFManager::ALIGN_CENTER, true);
						
						// Ajout du titre
						$this->_document->setFontSize(7);
						$this->_document->setXY($nPositionX, $nPositionTitre);
						$this->_document->setFontStyle(PDFManager::STYLE_BOLD);
						
						$nTextWidth				= strlen($oItem->getTitle()) * $this->_document->getFontSizePt();
						//$nLineHeight			= (self::PDF_INTERLINE_TITRE_SIZE - ($nTextWidth/$nCellWidth)) * 2;
						$fWidthFactor			= $nCellWidth/$nTextWidth;
						
						$fTestText				= $nTextWidth + $this->_document->getFontSizePt();
						$fTestCell				= $nCellWidth * $this->_document->getFontSizePt() - (self::PDF_PROGRESSION_CELL_MARGIN * $this->_document->getFontSizePt() * 2.5);
						
						// Fonctionnalité réalisée si le TITRE est plus grand que la CELLULE
						$nDecalage				= 0;
						if (intval($fTestText) < intval($fTestCell)) {
							$nLineHeight		= self::PDF_INTERLINE_TITRE_SIZE * 0.7;
						} else {
							$nLineHeight		= (self::PDF_INTERLINE_TITRE_SIZE - 2) / str_word_count($oItem->getTitle());
							$nDecalage			= $this->_document->getFontSize();
						}
						
						// Fonctionnalité réalisée si la taille de FONT est trop grande
						if ($nLineHeight < 1) {
							$this->_document->setFontSize(6);
							$nDecalage			= $this->_document->getFontSize();
						}
						
						// Ajout du nom de la TÂCHE
						$this->_document->setXY($nPositionX, $nPositionTop + $nDecalage + self::PDF_INTERLINE_TITRE_SIZE%($nLineHeight<1 ? 1 : intval($nLineHeight)));
						$this->_document->addCell($nCellWidth, $this->_document->getFontSize(), nl2br($oItem->getTitle()), null, PDFManager::ALIGN_CENTER);
						
						// Ajout des participants PRINCIPAUX
						$this->_document->setXY($nPositionX, $nPositionParticipant);
						$this->_document->setFontStyle(PDFManager::STYLE_BOLD);
						$this->_document->addCell($nCellWidth, self::PDF_INTERLINE_PARTICIPANT_SIZE, implode(" - ", $oItem->getParticipant(Planning_ItemHelper::TYPE_PRINCIPAL)), null, PDFManager::ALIGN_CENTER);

						// Ajout des participants SECONDAIRE
						$this->_document->setX($nPositionX);
						$this->_document->setFontStyle(PDFManager::STYLE_DEFAULT);
						$this->_document->addCell($nCellWidth, self::PDF_INTERLINE_PARTICIPANT_SIZE, nl2br(implode(" - ", $oItem->getParticipant(Planning_ItemHelper::TYPE_SECONDAIRE))), null, PDFManager::ALIGN_CENTER);
					}
					
					// Réinitialisation du FONT
					$this->_document->resetFontDefault();
				}
				// Passage à la cellule suivante
				$nPositionTop					+= $this->_planning_cell_height;
			}
			
			// Passage à la colonne suivante
			$nColonne++;
		}
	}

	/**
	 * @brief	Création de la progression PDF.
	 * @return	string
	 */
	public function buildProgression() {
		// ########################################################################################
		// INITIALISATION DE LA PAGE DU DOCUMENT
		// ########################################################################################
		$this->_document->addPage(PDFManager::ORIENTATION_L);
		$this->_document->setMargins(5, 0, 5);
		
		// ########################################################################################
		// ENTÊTE DU DOCUMENT
		// ########################################################################################
		
		// Entête du CENTRE
		$this->_document->setXY(5, 5);
		$this->_document->resetFontDefault();
		$this->_document->addCell(102, 3, nl2br(self::PDF_LIBELLE_CENTRE), null, PDFManager::ALIGN_CENTER);
		
		// Initialisation de la position de bas de page
		$nTopPosition							= 13;
		
		// Libellé du STAGE
		$this->_document->setCurrentLine($nTopPosition + 6);
		$this->_document->title(sprintf(self::PDF_FORMAT_TITRE, $this->_document_title), 12);
		
		// Libellé de la SEMAINE
		$this->_document->setCurrentLine($nTopPosition + 12);
		$this->_document->title(sprintf(self::PDF_FORMAT_SEMAINE, date(self::PLANNING_WEEK_FORMAT, $this->_timestamp_debut), date(self::PLANNING_DATE_FORMAT, $this->_timestamp_debut), date(self::PLANNING_DATE_FORMAT, $this->_timestamp_fin)), 9);
		
		// ########################################################################################
		// CORPS DU DOCUMENT
		// ########################################################################################
		
		// Construction des éléments si ce n'est pas déjà fait
		$this->_document->setY($nTopPosition + 14);
		$this->_buildProgressionPDF();
		
		// ########################################################################################
		// PIED DE PAGE DU DOCUMENT
		// ########################################################################################
		
		// Initialisation de la position de bas de page
		$nBottomPosition						= 183;
		
		// Libellé de bas de page
		$this->_document->setXY(5, $nBottomPosition);
		$this->_document->addCell(120, 3, nl2br(self::PDF_LIBELLE_BAS_PAGE), null, PDFManager::ALIGN_LEFT);
		
		// Signature de la progression
		$this->_document->setXY(180, $nBottomPosition);
		$this->_document->addCell(112, 3, nl2br($this->_signataire_titre), null, PDFManager::ALIGN_CENTER);
		$this->_document->addLine(1, false, 180);
		$this->_document->addCell(112, 3, nl2br($this->_signataire_fonction), null, PDFManager::ALIGN_CENTER);
		
		// Date de signature
		$this->_document->setXY(180, $nBottomPosition + 10);
		$this->_document->setTextColor(255, 0, 0);
		$this->_document->addCell(112, 0, nl2br($this->_signataire_date), null, PDFManager::ALIGN_CENTER);
		
		// Date de modification
		$this->_document->setXY(180, $nBottomPosition + 15);
		$this->_document->setTextColor(0, 0, 255);
		$this->_document->addCell(112, 0, nl2br($this->_modification_date), null, PDFManager::ALIGN_CENTER);

		// ########################################################################################
		// PURGE DES ÉLÉMENTS
		// ########################################################################################
		$this->_aItems							= array();
	}

	/**
	 * @brief	Rendu final de l'élément sous forme PDF.
	 * @return	string
	 */
	public function renderPDF($sFileName = null, $bDecodeUtf8 = false) {
		// ########################################################################################
		// GÉNÉRATION DU DOCUMENT
		// ########################################################################################
		
		// Renvoi du document au format HTML
		return $this->_document->output($sFileName, 'D', $bDecodeUtf8);
	}
	
}
