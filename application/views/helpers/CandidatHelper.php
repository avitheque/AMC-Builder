<?php
/**
 * @brief	Helper de création du formulaire CANDIDAT
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un candidat.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe AdministrationManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		CandidatHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 95 $
 * @since		$LastChangedDate: 2017-12-29 18:45:58 +0100 (Fri, 29 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class CandidatHelper {

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
	private		$_aListeStages		= array();

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_html				= "";

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	boolean	$bReadonly	: Verrouillage de la modification des champs.
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

		// Données du candidat
		$this->_aFormulaire			= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Récupération de la liste des stages liés au candidat
		$this->_aListeStages		= $this->_oInstanceStorage->getData('liste_stages_candidat');

		// Zone du formulaire Candidat
		$this->_buildFormulaire();

		// Zone de liste des Stages
		$this->_buildListeStages();
	}

	/**
	 * @brief	Zone du formulaire QCM.
	 */
	private function _buildFormulaire() {
		// Icône indicateur de champ saisissable
		$sPencilIcon				= "<span class=\"ui-icon ui-icon-pencil inline-block relative right-22 vertical-align-sub\">&nbsp;</span>";

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
		// GÉNÉRATITÉS SUR LE CANDIDAT
		//#################################################################################################

		// Identifiant du candidat
		$nIdCandidat				= DataHelper::get($this->_aFormulaire, 'candidat_id', 		DataHelper::DATA_TYPE_STR,	null);
		// Nom du candidat
		$sNomCandidat				= DataHelper::get($this->_aFormulaire, 'candidat_nom',		DataHelper::DATA_TYPE_STR,	null);
		// Prénom du candidat
		$sPrenomCandidat			= DataHelper::get($this->_aFormulaire, 'candidat_prenom',	DataHelper::DATA_TYPE_STR,	null);
		// Unité du candidat
		$sUniteCandidat				= DataHelper::get($this->_aFormulaire, 'candidat_unite',	DataHelper::DATA_TYPE_STR,	null);// Date de modification du référentiel
		// Date de modification du candidat
		$dDateModification			= DataHelper::get($this->_aFormulaire, 'candidat_datetime',	DataHelper::DATA_TYPE_DATETIME);

		// Construction de la liste déroulante du GRADE
		$nIdGrade					= DataHelper::get($this->_aFormulaire, 'candidat_grade',	DataHelper::DATA_TYPE_INT,	null);
		$sGradeOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_grades'), $nIdGrade, null, null, $this->_bReadonly);

		// Initialisation du mode d'accès au formulaire
		$sAccessMode				= empty($nIdCandidat) ? "Ajout" : "Édition";

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sDelete					= "";
		// Création du bouton de suppression si la liste des stages est vide
		if (!$this->_bReadonly && $nIdCandidat && empty($this->_aListeStages)) {
			$sDelete				= "<button type=\"submit\" class=\"confirm-delete red right margin-0\" name=\"button\" value=\"supprimer_candidat\">Supprimer</button>";
		}

		$sInformationModification	= "";
		// Création de l'information sur la date de dernière modification si elle est renseignée
		if ($dDateModification) {
			// Convertion de la date au format [FR]
			$dDateFR = DataHelper::dateTimeMyToFr($dDateModification);
			$sInformationModification = "<h3 class=\"strong center\">&#151;&nbsp;Dernière modification réalisée le " . $dDateFR . "&nbsp;&#151;</h3>";
		}

		// Création du formulaire
		$this->_html				.= "<h2>$sAccessMode d'un candidat" . $sDelete . "</h2>
										<p>
											$sInformationModification
										</p>
										<section id=\"candidat\">
											<fieldset $sClassField id=\"general\"><legend>Généralités</legend>
												<ol>
													<li>
														<label for=\"idGradeCandidat\">Grade du candidat</label>
														<select id=\"idGradeCandidat\" name=\"candidat_grade\" $sDisabled>" . $sGradeOptions . "</select>
													</li>
													<li>
														<label for=\"idCandidat\">Identifiant du candidat</label>
														<input placeholder=\"IDENTIFIANT\" maxlength=" . AdministrationManager::CANDIDAT_ID_MAXLENGTH . " type=\"text\" id=\"idCandidat\" name=\"candidat_id\" value=\"" . $nIdCandidat . "\" $sReadonly/>
														$sPencilIcon
													</li>
													<li>
														<label for=\"idNomCandidat\">Nom du candidat</label>
														<input maxlength=" . AdministrationManager::CANDIDAT_NOM_MAXLENGTH . " type=\"text\" id=\"idNomCandidat\" class=\"half-width\" name=\"candidat_nom\" value=\"" . strtoupper($sNomCandidat) . "\" $sReadonly/>
														$sPencilIcon
													</li>
													<li>
														<label for=\"idPrenomCandidat\">Prénom du candidat</label>
														<input maxlength=" . AdministrationManager::CANDIDAT_PRENOM_MAXLENGTH . " type=\"text\" id=\"idPrenomCandidat\" class=\"half-width\" name=\"candidat_prenom\" value=\"" . ucfirst($sPrenomCandidat) . "\" $sReadonly/>
														$sPencilIcon
													</li>
													<li>
														<label for=\"idUniteCandidat\">Unité du candidat</label>
														<input maxlength=" . AdministrationManager::CANDIDAT_UNITE_MAXLENGTH . " type=\"text\" id=\"idUniteCandidat\" class=\"half-width\" name=\"candidat_unite\" value=\"" . strtoupper($sUniteCandidat) . "\" $sReadonly/>
														$sPencilIcon
													</li>
												</ol>
											</fieldset>
										</section>
										<hr class=\"blue\"";
	}

	/**
	 * @brief	Zone de liste des Stages.
	 *
	 * @li	Affichage d'un tableau récapitulatif de l'ensemble des stages que suit le candidat.
	 * @return	void
	 */
	private function _buildListeStages() {
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
		// LISTE DES STAGES
		//#################################################################################################

		// Construction du tableau
		$oStages = new DatatableHelper("table-stages", $this->_aListeStages);
		$oStages->setClassColumn("align-left strong",	"libelle_stage");
		$oStages->setFormatOnColumn('date_debut_stage',	DataHelper::DATA_TYPE_DATE);
		$oStages->setFormatOnColumn('date_fin_stage',	DataHelper::DATA_TYPE_DATE);

		// Personnalisation des noms de colonne
		$aColonnes = array(
			'libelle_stage'				=> "LIBELLÉ DU STAGE",
			'total_candidats'			=> "NOMBRE DE CANDIDATS",
			'date_debut_stage'			=> "DEBUT",
			'date_fin_stage'			=> "FIN"
		);
		$oStages->renameColumns($aColonnes, true);

		// Tri du tableau sur la colonne FIN par ordre DESC
		$oStages->setOrderColumn('FIN', DatatableHelper::ORDER_DESC);

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$this->_html				.= "<section id=\"tableauStages\">
											<fieldset $sClassField id=\"liste\"><legend>Liste des stages</legend>
												" . $oStages->renderHTML() . "
											</fieldset>
										</section>";
	}

	/**
	 * @brief	Rendu final du formulaire
	 * @return	string
	 */
	public function render() {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/CandidatHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/CandidatHelper.js");

		// Renvoi du code HTML
		return $this->_html;
	}
}
