<?php
/**
 * @brief	Helper de génération d'une épreuve.
 *
 * Vue permettant de paramétrer la génération d'une épreuve.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe FormulaireManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		GenerationHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 83 $
 * @since		$LastChangedDate: 2017-12-03 12:14:06 +0100 (Sun, 03 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class GenerationHelper extends FormulaireHelper {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @param	boolean	$bDisable		: Fait disparaître certains boutons.
	 *
	 * @return	void
	 */
	public function __construct($bReadonly = false, $bDisable = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage		= InstanceStorage::getInstance();

		// Nom de session du QCM
		$sSessionNameSpace				= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Récupération de l'instance du singleton SessionManager
		$oSessionManager				= SessionManager::getInstance($sSessionNameSpace);

		//#########################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#########################################################################################

		// Protection du formulaire contre la modification si une épreuve est en cours
		$this->_bReadonly				= $oSessionManager->issetIndex('CONTROLE_EPREUVE_EXISTS') ? $oSessionManager->getIndex('CONTROLE_EPREUVE_EXISTS') : $bReadonly;

		// Désactivation de certains boutons du formulaire
		$this->_bDisable				= $this->_bReadonly ? true : $bDisable;

		// Données du QCM
		$this->_aQCM					= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Identifiant du questionnaire
		$this->_nIdFormulaire			= DataHelper::get($this->_aQCM, 'formulaire_id', 					DataHelper::DATA_TYPE_INT,		null);

		//#########################################################################################
		// RÉCUPÉRATION DE L'ONGLET SÉLECTIONNÉ PAR DÉFAUT
		//#########################################################################################
		$this->_action					= DataHelper::get($this->_aQCM, 'action_button',					DataHelper::DATA_TYPE_STR,		null);
		$this->_activeTab				= DataHelper::get($this->_aQCM, 'formulaire_active_tab',			DataHelper::DATA_TYPE_INT,		FormulaireManager::TAB_DEFAULT);

		// Construction des onglets
		$this->_html					.= "<section id=\"generation\" class=\"tabs\">
												<ul>
													<li><a href=\"#tabs-epreuve\">Épreuve</a></li>";

		// Fonctionnalité réalisée si un formulaire QCM est à associer à l'épreuve
		if ($this->_nIdFormulaire > 0) {
			// Construction des onglets
			$this->_html				.= "		<li><a href=\"#tabs-generalite\">Généralités</a></li>
													<li><a href=\"#tabs-questionnaire\">Questionnaire</a></li>";
		}

		// Finalisation des onglets
		$this->_html				.= "		</ul>
												<input type=\"hidden\" name=\"formulaire_active_tab\" value=\"" . $this->_activeTab . "\" />";

		// Zone des paramètres de génération du document
		$this->_buildFormulaireGeneration();

		// Fonctionnalité réalisée si un formulaire QCM est à associer à l'épreuve
		if ($this->_nIdFormulaire > 0) {
			//#####################################################################################
			// CONSTRUCTION DU FORMULAIRE QCM
			//#####################################################################################

			// Protection du formulaire contre la modification
			$this->_bReadonly				= true;

			// Désactivation de certains boutons du formulaire
			$this->_bDisable				= true;

			// Zone du formulaire QCM
			$this->_buildFormulaireQCM(false);
		}
	}

	/**
	 * @brief	Zone des paramètres de génération du document.
	 *
	 * @return	void
	 */
	private function _buildFormulaireGeneration() {
		// Icône indicateur de champ saisissable
		$sPencilIcon					= "<span class=\"ui-icon ui-icon-pencil inline-block absolute\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly						= "";
		$sDisabled						= "";
		$sClassField					= "";
		// Fonctionnalité réalisée si le formulaire est protégé en écriture
		if ($this->_bDisable) {
			$sReadonly					= "readonly=\"readonly\"";
			$sDisabled					= "disabled=\"disabled\"";
			$sClassField				= "disabled";
			$sPencilIcon				= "";
		}

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE RELATIF AUX PARAMÈTRES DE GÉNÉRATION DU QUESTIONNAIRE QCM
		//#########################################################################################

		// Nom du questionnaire
		$sNomFormulaire					= DataHelper::get($this->_aQCM, 'formulaire_titre',					DataHelper::DATA_TYPE_STR,		null);

		// Identifiant de la génération du document
		$nIdGeneration					= DataHelper::get($this->_aQCM, 'generation_id', 					DataHelper::DATA_TYPE_INT,		null);

		// Identifiant du stage
		$nIdStage						= DataHelper::get($this->_aQCM, 'epreuve_stage', 					DataHelper::DATA_TYPE_INT,		null);
		$sLibelleStage					= DataHelper::get($this->_aQCM, 'epreuve_stage_libelle', 			DataHelper::DATA_TYPE_STR,		'-');

		// Format du document à générer
		$sLanqueGeneration				= DataHelper::get($this->_aQCM, 'generation_langue',				DataHelper::DATA_TYPE_STR,		FormulaireManager::GENERATION_LANGUE_DEFAUT);
		$nSeedGeneration				= DataHelper::get($this->_aQCM, 'generation_seed',					DataHelper::DATA_TYPE_INT_ABS,	LatexFormManager::DOCUMENT_RANDOMISEED_DEFAUT);

		// Format du code candidat, de 1 à 8 chiffres
		$nCodeCandidat					= DataHelper::get($this->_aQCM, 'generation_code_candidat', 		DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::CANDIDATS_CODE_DEFAUT);

		// Liste des formats de code candidats entre 1 et 8 chiffres
		$aCodeCandidat					= array();
		for ($n = 1 ; $n <= 8 ; $n++) {
			$aCodeCandidat[$n]			= $n;
		}
		$sCodeCandidatOptions			= HtmlHelper::buildListOptions($aCodeCandidat, $nCodeCandidat);

		// Texte du cartouche destiné à la saisie manuelle du code candidat
		$sTexteCandidat					= DataHelper::get($this->_aQCM, 'generation_cartouche_candidat',	DataHelper::DATA_TYPE_TXT,		FormulaireManager::CANDIDATS_CARTOUCHE_DEFAUT);

		// Données de l'épreuve
		$nIdEpreuve						= DataHelper::get($this->_aQCM, 'epreuve_id', 						DataHelper::DATA_TYPE_INT,		null);
		$tHeureEpreuve					= DataHelper::get($this->_aQCM,	'epreuve_heure',					DataHelper::DATA_TYPE_TIME,		FormulaireManager::EPREUVE_HEURE_DEFAUT);
		$nDureeEpreuve					= DataHelper::get($this->_aQCM,	'epreuve_duree',					DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::EPREUVE_DUREE_DEFAUT);
		$sNomEpreuveGeneration			= DataHelper::get($this->_aQCM,	'generation_nom_epreuve',			DataHelper::DATA_TYPE_STR,		'-');
		$dDateEpreuveGeneration			= DataHelper::get($this->_aQCM,	'generation_date_epreuve',			DataHelper::DATA_TYPE_DATE,		date(FormulaireManager::EPREUVE_DATE_FORMAT));

		// Récupération du libellé de l'épreuve, à défaut, le nom et la date de l'épreuve à partir de la génération
		$sLibelleEpreuve				= DataHelper::get($this->_aQCM,	'epreuve_libelle',					DataHelper::DATA_TYPE_STR,		$sNomFormulaire);
		$dDateEpreuve					= DataHelper::get($this->_aQCM,	'epreuve_date',						DataHelper::DATA_TYPE_DATE,		$dDateEpreuveGeneration);

		// Type de l'épreuve
		$sTypeEpreuve					= DataHelper::get($this->_aQCM,	'epreuve_type',						DataHelper::DATA_TYPE_STR,		FormulaireManager::EPREUVE_TYPE_DEFAUT);
		$sTypeOptions					= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_types'), $sTypeEpreuve);

		$nExemplaires					= DataHelper::get($this->_aQCM,	'generation_exemplaires',			DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::GENERATION_EXEMPLAIRES_DEFAUT);
		$sConsignes						= DataHelper::get($this->_aQCM,	'generation_consignes',				DataHelper::DATA_TYPE_TXT,		FormulaireManager::GENERATION_CONSIGNES_DEFAUT);

		// Format d'impression du questionnaire
		$sIdFormat						= DataHelper::get($this->_aQCM, 'generation_format',				DataHelper::DATA_TYPE_STR,		FormulaireManager::GENERATION_FORMAT_DEFAUT);
		$sFormatOptions					= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_formats'), $sIdFormat);

		// Construction du champ AutoComplete exploitant le plugin jQuery.autoComplete()
		$oAutocomplete 					= new AutocompleteHelper("epreuve_stage_libelle", $this->_oInstanceStorage->getData('liste_stages'), $sLibelleStage, !empty($nIdStage));
		$oAutocomplete->setId("idStageLibelle");
		$oAutocomplete->setClass("half-width");
		$oAutocomplete->setHiddenInputName("epreuve_stage");
		$oAutocomplete->setRequired(true);
		if ($this->_bDisable) {
			$oAutocomplete->setAttribute('disabled', true);
		}

		// Fonctionnalité réalisée si le stage a été identifié parmis les libellés du champ AutocompleteHelper
		if (empty($nIdStage)) {
			// Récupère l'identifiant du stage
			$nIdStage = $oAutocomplete->getHiddenKey();
		}

		// Disposition des réponses des candidats sur les feuilles séparées
		$bSeparate						= DataHelper::get($this->_aQCM, 'generation_separate',				DataHelper::DATA_TYPE_BOOL,		FormulaireManager::GENERATION_SEPARATE_DEFAUT);

		// Création d'une case à cocher afin de permettre la génération des réponses sur feuilles séparées
		$oSeparateCheckbox				= new CheckboxHelper("generation_separate", "Imprimer les réponses sur des pages séparées");
		// Fonctionnalité réalisée si les questions doivent être séparées
		if ($bSeparate) {
			$oSeparateCheckbox->setAttribute('checked', "checked");
		}
		// Fonctionnalité réalisée si le formulaire est protégé en écriture
		if ($this->_bDisable) {
			$oSeparateCheckbox->setAttribute('disabled', true);
		}

		// Récupération de la liste des salles disponibles pour l'épreuve
		$aListeSalles					= $this->_oInstanceStorage->getData('liste_salles');
		$aChoixSalles					= DataHelper::get($this->_aQCM,	'epreuve_liste_salles',				DataHelper::DATA_TYPE_ARRAY,	null);

		// Récupération des options d'attribution des tables
		$bTableAleatoire				= DataHelper::get($this->_aQCM,	'epreuve_table_aleatoire',			DataHelper::DATA_TYPE_BOOL,		false);
		$bTableAffectation				= DataHelper::get($this->_aQCM,	'epreuve_table_affectation',		DataHelper::DATA_TYPE_BOOL,		false);
		// Sélection automatique de l'affectation si une attribution aléatoire est réalisée
		if ($bTableAleatoire) {
			$bTableAffectation			= true;
		}

		//#########################################################################################
		// CONSTRUCTION DU CODE HTML
		//#########################################################################################

		// Épreuves
		$this->_html					.= "	<div id=\"tabs-epreuve\">
													<span id=\"tabs-epreuve-top\"><a class=\"page-top\" href=\"#tabs-epreuve-bottom\" title=\"Bas de page...\">" . self::ICON_DOWN . "</a></span>
													<fieldset class=\"" . $sClassField . "\" id=\"epreuve\"><legend>Paramètres de l'épreuve</legend>
														<ol>";

		// Fonctionnalité réalisée si un identifiant de formulaire est présent
		if ($this->_nIdFormulaire) {
			$this->_html				.= "				<li class=\"center margin-bottom-20\">
																<h3 class=\"strong center\">&#151;&nbsp;" . $sNomFormulaire . "&nbsp;&#151;</h3>
															</li>
															<li>
																<label for=\"idFormat\" class=\"width-225\">Format de sortie</label>
																<select id=\"idFormat\" name=\"generation_format\" $sDisabled>" . $sFormatOptions . "</select>
															</li>
															<li>
																<label for=\"idExemplaires\" class=\"width-225\">Nombre d'exemplaires</label>
																<input maxlength=2 type=\"number\" id=\"idExemplaires\" class=\"numeric center width-50\" name=\"generation_exemplaires\" value=\"" . $nExemplaires . "\" required=\"required\" $sReadonly/>
															</li>
															<li>
																<label for=\"idCodeCandidat\" class=\"width-225\">Format du code candidat sur</label>
																<select id=\"idCodeCandidat\" class=\"center\" name=\"generation_code_candidat\" required=\"required\" $sDisabled/>" . $sCodeCandidatOptions . "</select>
																<label for=\"idCodeCandidat\">chiffres</label>
															</li>
															<li class=\"center margin-V-20\">
																" . $oSeparateCheckbox->renderHTML() . "
															</li>
															<hr class=\"blue\" />
															</li>";
		} elseif (empty($nIdEpreuve)) {
			$sConsignes					= "";
		}

		// Poursuite du formulaire
		$this->_html					.= "				<li>
																<label for=\"idTypeEpreuve\" class=\"width-225\">Type d'épreuve</label>
																<select id=\"idTypeEpreuve\" name=\"epreuve_type\" $sDisabled>" . $sTypeOptions . "</select>
								
																<input type=\"hidden\" id=\"idGeneration\" name=\"generation_id\" value=\"" . $nIdGeneration . "\" />
																<input type=\"hidden\" id=\"idEpreuve\" name=\"epreuve_id\" value=\"" . $nIdEpreuve . "\" />
															</li>
															<li>
																<label for=\"idStageLibelle\" class=\"width-225\">Stage concerné par l'épreuve</label>
																" . $oAutocomplete->renderHTML() . "
															</li>
															<li>
																<label for=\"idEpreuveLibelle\" class=\"width-225\">Nom de l'épreuve</label>
																<input maxlength=50 type=\"text\" class=\"half-width\" id=\"idEpreuveLibelle\" name=\"epreuve_libelle\" value=\"" . $sLibelleEpreuve . "\" $sReadonly/>
															</li>
															<li>
																<label for=\"idDateEpreuve\" class=\"width-225\">Date prévue de l'épreuve</label>
																<input maxlength=10 type=\"text\" class=\"date\" id=\"idDateEpreuve\" name=\"epreuve_date\" value=\"" . $dDateEpreuve . "\" $sReadonly/>
															</li>
															<li>
																<label for=\"idHeureEpreuve\" class=\"width-225\">Heure prévue de l'épreuve</label>
																<input maxlength=5 type=\"text\" class=\"time\" id=\"idHeureEpreuve\" name=\"epreuve_heure\" value=\"" . $tHeureEpreuve . "\" $sReadonly/>
															</li>
															<li>
																<label for=\"idDureeEpreuve\" class=\"width-225\">Durée de l'épreuve</label>
																<input maxlength=3 type=\"number\" id=\"idDureeEpreuve\" class=\"numeric center width-50\" name=\"epreuve_duree\" value=\"" . $nDureeEpreuve . "\" required=\"required\" $sReadonly/>
																<label for=\"idDureeEpreuve\">minutes</label>
															</li>
														</ol>
													</fieldset>";

		// Fonctionnalité réalisée si des salles d'épreuve sont disponibles
		$sTableClasse					= "hidden";
		if (DataHelper::isValidArray($aListeSalles) && !empty($nIdStage)) {
			// Initialisation du code HTML du choix des salles
			$sChoixSalles				= "";

			// Parcours de la liste des salles disponibles
			$nOccurrence				= 1;
			foreach ($aListeSalles as $nId => $sLibelle) {
				// Création d'un élément CHECKBOX
				$oCheckbox				= new CheckboxHelper("epreuve_liste_salles[$nId]", $sLibelle);

				// Fonctionnalité réalisée si la salle est sélectionnée
				if (DataHelper::isValidArray($aChoixSalles) && in_array($nId, $aChoixSalles)) {
					$oCheckbox->setAttribute('checked', "checked");
					$sTableClasse		= "";
				}

				// Fonctionnalité réalisée si le formulaire est protégé en écriture
				if ($this->_bDisable) {
					$oCheckbox->setAttribute('disabled', true);
				}

				// Ajout du choix de la salle
				$sChoixSalles			.= $oCheckbox->renderHTML();

				// Fonctionnalité réalisée si le nombre n'est pas encore atteint
				if ($nOccurrence < count($aListeSalles)) {
					$sChoixSalles		.= "		<span class=\"margin-10\">&nbsp</span>";
				}
				$nOccurrence++;
			}

			// Attribution d'une table à chaque candidat
			$oAffectationCheckbox		= new CheckboxHelper("epreuve_table_affectation", "Attribution d'une table à chaque candidat");
			if ($bTableAffectation) {
				$oAffectationCheckbox->setAttribute('checked', "checked");
			}

			// Le choix de la table est réalisée de façon aléatoire
			$oAleatoireCheckbox			= new CheckboxHelper("epreuve_table_aleatoire", "Distribution des tables de façon aléatoire");
			if ($bTableAleatoire) {
				$oAleatoireCheckbox->setAttribute('checked', "checked");
			}

			// Fonctionnalité réalisée si le formulaire est protégé en écriture
			if ($this->_bDisable) {
				$oAffectationCheckbox->setAttribute('disabled', true);
				$oAleatoireCheckbox->setAttribute('disabled', true);
			}

			// Finalisation du formulaire relatif à la génération du document
			$this->_html				.= "		<hr class=\"margin-V-25 blue\"/>
													<fieldset class=\"" . $sClassField . "\" id=\"lieux\"><legend>Lieu de l'épreuve</legend>
														<div class=\"margin-H-25\">
															<div>
																<span>Veuillez sélectionner le(s) lieu(x) où se déroulera l'épreuve</span>
															</div>
															<div id=\"liste_salles\" class=\"center margin-top-20\">
																" . $sChoixSalles . "
															</div>
														</div>
														<div id=\"attribution-tables\" class=\"max-width center $sTableClasse\">
															<hr class=\"half-width\" />
															<ul>
																<li>" . $oAffectationCheckbox->renderHTML() . "</li>
																<li>" . $oAleatoireCheckbox->renderHTML() . "</li>
															</ul>
														</div>
													</fieldset>";
		}

		// Finalisation du formulaire relatif à la génération du document
		$this->_html					.= "		<hr class=\"margin-V-25 blue\"/>
													<fieldset class=\"" . $sClassField . "\" id=\"consignes\"><legend>Consignes de l'épreuve</legend>
														<div class=\"margin-H-25\">
															<label for=\"idConsignes\">Le texte ci-dessous est destiné à présenter les consignes particulières aux candidats.</label>
															<textarea rows=5 id=\"idConsignes\" class=\"max-width\" name=\"generation_consignes\" $sReadonly>" . $sConsignes . "</textarea>
															$sPencilIcon
														</div>
													</fieldset>
													<span id=\"tabs-epreuve-bottom\"><a class=\"page-bottom\" href=\"#tabs-epreuve-top\" title=\"Haut de page...\">" . self::ICON_UP . "</a></span>
												</div>";
	}

}
