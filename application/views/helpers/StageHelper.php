<?php
/**
 * @brief	Helper de création du formulaire STAGE
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un stage.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe AdministrationManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @li Les champs DATE sont verrouillés quand au moins un candidat est enregistré.
 *
 * @name		StagetHelper
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
class StageHelper {

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	private		$_oInstanceStorage	= null;

	/**
	 * @brief	Accès au formulaire en lecture seule.
	 * @var		bool
	 */
	private		$_bReadonly			= false;

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	private		$_aFormulaire		= array();
	private		$_aListeCandidats	= array();

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_html				= "";

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @return	string
	 */
	public function __construct($bReadonly = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage	= InstanceStorage::getInstance();

		//#################################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#################################################################################################

		// Lecture par défaut
		$this->_bReadonly			= $bReadonly;

		// Nom de session des données
		$sSessionNameSpace			= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Données du stage
		$this->_aFormulaire			= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Récupération de la liste des candidats liés au stage
		$this->_aListeCandidats		= $this->_oInstanceStorage->getData('liste_candidats_stage');

		// Zone du formulaire Stage
		$this->_buildFormulaire();

		// Zone de liste des Candidats
		$this->_buildListeCandidats();
	}

	/**
	 * @brief	Zone du formulaire STAGE.
	 * @return	void
	 */
	private function _buildFormulaire() {
		// Icône indicateur de champ saisissable
		$sPencilIcon				= "<span class=\"ui-icon ui-icon-pencil inline-block relative right-22 align-sub\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly					= "";
		$sDisabled					= "";
		$sClassField				= "";
		if ($this->_bReadonly) {
			$sReadonly				= "readonly=\"readonly\"";
			$sDisabled				= "disabled=\"disabled\"";
			$sClassField			= "class=\"readonly\"";
			$sPencilIcon			= "";
		}

		//#################################################################################################
		// GÉNÉRATITÉS SUR LE STAGE
		//#################################################################################################

		// Identifiant du stage
		$nIdStage					= DataHelper::get($this->_aFormulaire,	'stage_id', 			DataHelper::DATA_TYPE_INT,	null);
		// Libellé du stage
		$sLibelleStage				= DataHelper::get($this->_aFormulaire,	'stage_libelle',		DataHelper::DATA_TYPE_STR,	null);
		// Date de début du stage
		$sDateDebutStage			= DataHelper::get($this->_aFormulaire,	'stage_date_debut',		DataHelper::DATA_TYPE_DATE,	null);
		// Date de fin du stage
		$sDateFinStage				= DataHelper::get($this->_aFormulaire,	'stage_date_fin',		DataHelper::DATA_TYPE_DATE,	null);
		// Date de modification du stage
		$dDateModification			= DataHelper::get($this->_aFormulaire,	'stage_datetime',		DataHelper::DATA_TYPE_DATETIME);

		// Construction de la liste déroulante du DOMAINE
		$nIdDomaine					= DataHelper::get($this->_aFormulaire,	'stage_domaine',		DataHelper::DATA_TYPE_INT,	null);
		$sDomaineOptions			= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_domaines'), $nIdDomaine, "-", null, $this->_bReadonly);

		// Construction de la liste déroulante du SOUS-DOMAINE
		$nIdSousDomaine				= DataHelper::get($this->_aFormulaire,	'stage_sous_domaine',	DataHelper::DATA_TYPE_INT,	null);
		$sSousDomaineOptions		= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_sous_domaines'), $nIdSousDomaine, '-', null, $this->_bReadonly);

		// Construction de la liste déroulante de la CATÉGORIE
		$nIdCategorie				= DataHelper::get($this->_aFormulaire,	'stage_categorie',		DataHelper::DATA_TYPE_INT,	null);
		$sCategorieOptions			= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_categories'), $nIdCategorie, '-', null, $this->_bReadonly);

		// Construction de la liste déroulante de la SOUS-CATÉGORIE
		$nIdSousCategorie			= DataHelper::get($this->_aFormulaire,	'stage_sous_categorie',	DataHelper::DATA_TYPE_INT,	null);
		$sSousCategorieOptions		= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_sous_categories'), $nIdSousCategorie, '-', null, $this->_bReadonly);

		// Initialisation du mode d'accès au formulaire
		$sAccessMode				= empty($nIdStage) ? "Ajout" : "Édition";

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sDelete					= "";
		// Création du bouton de suppression si la liste des candidats est vide
		if (!$this->_bReadonly && $nIdStage && empty($this->_aListeCandidats)) {
			$sDelete = "<button type=\"submit\" class=\"confirm-delete red right margin-0\" name=\"button\" value=\"supprimer_stage\">Supprimer</button>";
		} elseif ($this->_bReadonly || !empty($this->_aListeCandidats)) {
			// Verrouillage des champs DATE
			$sReadonly				= "readonly=\"readonly\"";
		}

		$sAdd						= "";
		// Création du bouton d'affichage du formulaire MODAL
		if ($nIdStage && !$this->_bReadonly) {
			$sAdd					= "<a id=\"add_candidat\" class=\"button blue right margin-top-10-important\" href=\"#\">Ajouter des candidats</a>";
		}

		$sInformationModification	= "";
		// Création de l'information sur la date de dernière modification si elle est renseignée
		if ($dDateModification) {
			// Convertion de la date au format [FR]
			$dDateFR = DataHelper::dateTimeMyToFr($dDateModification);
			$sInformationModification = "<h3 class=\"strong center\">&#151;&nbsp;Dernière modification réalisée le " . $dDateFR . "&nbsp;&#151;</h3>";
		}

		// Création du code HTML
		$this->_html				.= "<h2>$sAccessMode d'un stage" . $sDelete . "</h2>
										<p>
											$sInformationModification
										</p>
										<section id=\"stage\">
											<fieldset $sClassField id=\"general\"><legend>Généralités</legend>
												<ol>
													<li>
														<label for=\"idLibelleStage\">Libellé du stage</label>
														<input maxlength=" . AdministrationManager::STAGE_LIBELLE_MAXLENGTH . " type=\"text\" id=\"idLibelleStage\" class=\"half-width\" name=\"stage_libelle\" value=\"" . $sLibelleStage . "\" $sReadonly/>
														$sPencilIcon
														<input type=\"hidden\" id=\"idStage\" name=\"stage_id\" value=\"$nIdStage\" />
													</li>
				
													<li>
														<div class=\"half-width left\">
															<label for=\"idDomaine\">Domaine</label>
															<select id=\"idDomaine\" name=\"stage_domaine\" $sDisabled>" . $sDomaineOptions . "</select>
														</div>
														<div class=\"half-width no-wrap\">
															<label for=\"idSousDomaine\">Sous domaine</label>
															<select id=\"idSousDomaine\" name=\"stage_sous_domaine\" $sDisabled>" . $sSousDomaineOptions . "</select>
														</div>
													</li>
													<li>
														<div class=\"half-width left no-wrap\">
															<label for=\"idCategorie\">Catégorie</label>
															<select id=\"idCategorie\" name=\"stage_categorie\" $sDisabled>" . $sCategorieOptions . "</select>
														</div>
														<div class=\"half-width no-wrap\">
															<label for=\"idSousCategorie\">Sous catégorie</label>
															<select id=\"idSousCategorie\" name=\"stage_sous_categorie\" $sDisabled>" . $sSousCategorieOptions . "</select>
														</div>
													</li>
													<li>
														<label for=\"idDateDebutStage\">Date de début</label>
														<input maxlength=" . AdministrationManager::STAGE_DATE_MAXLENGTH . " type=\"text\" id=\"idDateDebutStage\" class=\"date\" name=\"stage_date_debut\" value=\"" . $sDateDebutStage . "\" $sReadonly/>
														" . $sAdd . "
													</li>
													<li>
														<label for=\"idDateFinStage\">Date de fin</label>
														<input maxlength=" . AdministrationManager::STAGE_DATE_MAXLENGTH . " type=\"text\" id=\"idDateFinStage\" class=\"date\" name=\"stage_date_fin\" value=\"" . $sDateFinStage . "\" $sReadonly/>
													</li>
												</ol>
											</fieldset>
										</section>
										<hr class=\"blue\"/>";
	}

	/**
	 * @brief	Zone de liste des Candidats.
	 *
	 * @li	Affichage d'un tableau récapitulatif de l'ensemble des candidats suivant le stage.
	 * @return	void
	 */
	private function _buildListeCandidats() {
		// Variables de verrouillage des champs
		$sReadonly					= "";
		$sDisabled					= "";
		$sClassField				= "";
		if ($this->_bReadonly) {
			$sReadonly				= "readonly=\"readonly\"";
			$sDisabled				= "disabled=\"disabled\"";
			$sClassField			= "class=\"readonly\"";
		}

		//#################################################################################################
		// LISTE DES CANDIDATS
		//#################################################################################################

		// Construction du tableau
		$oCandidats					= new DatatableHelper("table-candidats", $this->_aListeCandidats);
		$oCandidats->setClassColumn("align-left", array("libelle_court_grade", "nom_candidat", "prenom_candidat", "unite_candidat"));

		// Ajout de la valeur d'ordre du grade à la colonne [libelle_court_grade] masquée
		$oCandidats->prependValueIntoColumn("libelle_court_grade", "ordre_grade", "%03d", "hidden");

		// Personnalisation des noms de colonne
		$aColonnes = array(
			'id_candidat'			=> "IDENTIFIANT",
			'libelle_court_grade'	=> "GRADE",
			'nom_candidat'			=> "NOM",
			'prenom_candidat'		=> "PRÉNOM",
			'unite_candidat'		=> "UNITÉ",
			'code_candidat'			=> "CODE"
		);
		$oCandidats->renameColumns($aColonnes, true);

		// Tri du tableau sur la colonne GRADE par ordre DESC
		$oCandidats->setOrderColumn('nom_candidat',		DatatableHelper::ORDER_ASC);

		if (!$this->_bReadonly) {
			// Boutons [Tout cocher] [Tout décocher]
			$sCheck					= "	<a id=\"check_all\" class=\"button green margin-0\" href=\"#\">Tout cocher</a>
										<a id=\"remove_all\" class=\"button green hidden margin-0\" href=\"#\">Tout décocher</a>";
			$oCandidats->addInputOnColumn("$sCheck", "id_candidat", "selection[]", "checkbox");
			$oCandidats->disableOrderingOnColumn("$sCheck");
		}

		// Format du code candidat, de 1 à 8 chiffres
		$nCodeCandidat				= DataHelper::get($this->_aFormulaire, 'candidat_code', 	DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::CANDIDATS_CODE_DEFAUT);

		// Liste des formats de code candidats entre 1 et 8 chiffres
		$aCodeCandidat				= array();
		for ($n = 1 ; $n <= FormulaireManager::CANDIDATS_CODE_MAXLENGTH ; $n++) {
			$aCodeCandidat[$n]		= $n;
		}
		$sCodeCandidatOptions		= HtmlHelper::buildListOptions($aCodeCandidat, $nCodeCandidat);

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sExport					= "";
		$sBottom					= "";
		if (DataHelper::isValidArray($this->_aListeCandidats)) {
			// Bouton d'exportation
			$sExport				= "	<div class=\"margin-bottom-50\">
											<button type=\"submit\" class=\"green left margin-top-0\" name=\"button\" value=\"generer_stage_candidat\">Générer le fichier PDF</button>
											<button type=\"submit\" class=\"green right margin-top-0\" name=\"button\" value=\"exporter_stage_candidat\">Exporter au format CSV</button>
										</div>";

			// Ensemble des boutons d'action
			$sBottom				= "	<div id=\"action\" class=\"hidden\" align=\"right\">
											<button type=\"submit\" class=\"red margin-top-0\" name=\"button\" value=\"retirer_stage_candidat\">Supprimer la sélection</button>
											<button type=\"submit\" class=\"blue margin-top-0\" name=\"button\" value=\"renouveler_stage_candidat\">Renouveler le code</button>
											<label for=\"idCodeCandidat\">sur
											<select id=\"idCodeCandidat\" class=\"center\" name=\"candidat_code\" required=\"required\" $sDisabled/>" . $sCodeCandidatOptions . "</select> chiffres</label>
										</div>";
		}

		// Création du code HTML
		$this->_html				.= "<section id=\"tableauCandidats\">
											<fieldset $sClassField id=\"liste\"><legend>Liste des candidats</legend>
												" . $sExport . "
												" . $oCandidats->renderHTML() . "
												" . $sBottom . "
											</fieldset>
										</section>";
	}

	/**
	 * @brief	Rendu final du formulaire
	 * @return	string
	 */
	public function render() {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/StageHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/FormulaireHelper.js");
		ViewRender::linkFormulaireScript("helpers/StageHelper.js");

		// Renvoi du code HTML
		return $this->_html;
	}
}
