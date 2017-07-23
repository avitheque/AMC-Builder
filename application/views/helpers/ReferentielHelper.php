<?php
/**
 * @brief	Helper de création du formulaire REFERENTIEL
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un référentiel.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe AdministrationManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		ReferentielHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 67 $
 * @since		$LastChangedDate: 2017-07-19 00:09:56 +0200 (Wed, 19 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ReferentielHelper {

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
	 * @brief	Nom du référentiel chargé.
	 * @var		string
	 */
	private		$_sTableName		= null;

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	private		$_aFormulaire		= array();
	private		$_aListeRefentiel	= array();

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

		// Récupération du nom de la table
		$this->_sTableName			= DataHelper::get($this->_aFormulaire, 'referentiel_table', DataHelper::DATA_TYPE_STR, $this->_oInstanceStorage->getData('TABLE_NAME'));

		// Données du candidat
		$this->_aFormulaire			= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Récupération de la liste des stages liés au référentiel
		$this->_aListeRefentiel		= $this->_oInstanceStorage->getData('liste_referentiel');

		// Zone du formulaire
		$this->_buildFormulaire();

		// Zone de liste
		$this->_buildListeReferentiel();
	}

	/**
	 * @brief	Zone du formulaire QCM.
	 */
	private function _buildFormulaire() {
		// Icône indicateur de champ saisissable
		$sPencilIcon				= "<span class=\"ui-icon ui-icon-pencil inline-block relative right-22 align-top\">&nbsp;</span>";

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
		// GÉNÉRATITÉS SUR LE RÉFÉRENTIEL
		//#################################################################################################

		// Identifiant du référentiel
		$nIdReferentiel				= DataHelper::get($this->_aFormulaire,	'referentiel_id', 			DataHelper::DATA_TYPE_INT,	null);
		// Libellé du référentiel
		$sLibelleReferentiel		= DataHelper::get($this->_aFormulaire,	'referentiel_libelle', 		DataHelper::DATA_TYPE_STR,	null);
		// Description du référentiel
		$sDescriptionReferentiel	= DataHelper::get($this->_aFormulaire,	'referentiel_description', 	DataHelper::DATA_TYPE_TXT,	null);
		// Date de début du référentiel
		$dDateDebut					= DataHelper::get($this->_aFormulaire,	'referentiel_date_debut',	DataHelper::DATA_TYPE_DATE,	null);
		// Date de fin du référentiel
		$dDateFin					= DataHelper::get($this->_aFormulaire,	'referentiel_date_fin',		DataHelper::DATA_TYPE_DATE,	"31/12/9999");
		// Date de modification du référentiel
		$dDateModification			= DataHelper::get($this->_aFormulaire,	'referentiel_datetime',		DataHelper::DATA_TYPE_DATETIME);

		// Construction de la liste déroulante du GRADE
		$nIdParent					= DataHelper::get($this->_aFormulaire,	'referentiel_parent',		DataHelper::DATA_TYPE_INT,	null);
		$sParentOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_parent'), $nIdParent, "-", null, $this->_bReadonly);

		// Initialisation du mode d'accès au formulaire
		$sAccessMode				= empty($nIdReferentiel) ? "Ajout" : "Édition";

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sDelete					= "";
		// Création du bouton de suppression si la liste des stages est vide
		if (!$this->_bReadonly && $nIdReferentiel && empty($this->_aListeStages)) {
			$sDelete				= "<button type=\"submit\" class=\"confirm-delete red right margin-0\" name=\"button\" value=\"supprimer\">Supprimer</button>";
		}

		$sInformationModification	= "";
		// Création de l'information sur la date de dernière modification si elle est renseignée
		if ($dDateModification) {
			// Convertion de la date au format [FR]
			$dDateFR = DataHelper::dateTimeMyToFr($dDateModification);
			$sInformationModification = "<h3 class=\"strong center\">&#151;&nbsp;Dernière modification réalisée le " . $dDateFR . "&nbsp;&#151;</h3>";
		}

		// Création du formulaire
		$this->_html				.= "<h2>$sAccessMode au référentiel <span class=\"strong upper\">" . $this->_sTableName . "</span>" . $sDelete . "</h2>
										<p>
										</p>
										<section id=\"referentiel\">
											<fieldset $sClassField id=\"general\"><legend>Généralités</legend>
												<ol>
													<li class=\"margin-bottom-20\">
														$sInformationModification
													</li>
													<li>
														<label for=\"idLibelleReferentiel\">Libellé du référentiel</label>
														<input maxlength=" . AdministrationManager::REFERENTIEL_LIBELLE_MAXLENGTH . " type=\"text\" id=\"idLibelleReferentiel\" class=\"half-width\" name=\"referentiel_libelle\" value=\"" . $sLibelleReferentiel . "\" $sReadonly/>
														$sPencilIcon
														<input type=\"hidden\" id=\"idReferentiel\" name=\"referentiel_id\" value=\"" . $nIdReferentiel . "\"/>
													</li>";

		// Fonctionnalité réalisée si le référentiel dépend d'un parent
		if (in_array($this->_sTableName, array_keys(ReferentielManager::$REF_TABLE_PARENT))) {
			$this->_html			.= "			<li>
														<label for=\"idParentReferentiel\">Référentiel parent</label>
														<select id=\"idParentReferentiel\" name=\"referentiel_parent\" $sDisabled>" . $sParentOptions . "</select>
													</li>";
		}

		// Poursuite de la création du formulaire
		$this->_html				.= "			<li>
														<label for=\"idDescriptionReferentiel\">Description</label>
														<textarea id=\"idDescriptionReferentiel\" class=\"half-width\" name=\"referentiel_description\" $sReadonly>" . $sDescriptionReferentiel . "</textarea>
														$sPencilIcon
													</li>";

		// Poursuite de la création du formulaire
		$this->_html				.= "			<li>
														<label for=\"idDateDebutReferentiel\">Date de début</label>
														<input maxlength=" . AdministrationManager::DATE_MAX_LENGTH . " type=\"date\" id=\"idDateDebutReferentiel\" class=\"date\" name=\"referentiel_date_debut\" value=\"" . $dDateDebut . "\" $sReadonly/>
														<span id=\"resetDateDebut\" class=\"icon-blue icon-reset pointer padding-left-10\" title=\"Réinitialiser la date\">&nbsp;</span>
													</li>
													<li>
														<label for=\"idDateFinReferentiel\">Date de fin</label>
														<input maxlength=" . AdministrationManager::DATE_MAX_LENGTH . " type=\"date\" id=\"idDateFinReferentiel\" class=\"date\" name=\"referentiel_date_fin\" value=\"" . $dDateFin . "\" $sReadonly/>
														<span id=\"resetDateFin\" class=\"icon-blue icon-reset pointer padding-left-10\" title=\"Réinitialiser la date\">&nbsp;</span>
													</li>
												</ol>
											</fieldset>";


		// Fonctionnalité réalisée si le référentiel correspond à table `salle`
		if ($this->_sTableName == ReferentielManager::TABLE_SALLE) {

			// Récupération de l'identifiant de statut
			$nIdStatutSalle			= DataHelper::get($this->_aFormulaire,	'statut_salle_id',				DataHelper::DATA_TYPE_INT);

			// Récupération de la capacité
			$nCapacite				= DataHelper::get($this->_aFormulaire,	'statut_salle_capacite',		DataHelper::DATA_TYPE_INT_ABS,	AdministrationManager::REFERENTIEL_CAPACITE_DEFAUT);

			$bInformatique			= DataHelper::get($this->_aFormulaire,	'statut_salle_informatique',	DataHelper::DATA_TYPE_BOOL);

			$bReseau				= DataHelper::get($this->_aFormulaire,	'statut_salle_reseau',			DataHelper::DATA_TYPE_BOOL);

			$bExamen				= DataHelper::get($this->_aFormulaire,	'statut_salle_examen',			DataHelper::DATA_TYPE_BOOL);

			$bReservable			= DataHelper::get($this->_aFormulaire,	'statut_salle_reservable',		DataHelper::DATA_TYPE_BOOL);

			// Création d'un élément CHECKBOX pour le statut INFORMATIQUE
			$oCheckInformatique		= new CheckboxHelper("statut_salle_informatique",	"Salle équipée informatique");
			// Fonctionnalité réalisée si la salle est sélectionnée
			if ($bInformatique) {
				$oCheckInformatique->setAttribute("checked", "checked");
			}

			// Création d'un élément CHECKBOX pour le statut RESEAU
			$oCheckReseau			= new CheckboxHelper("statut_salle_reseau",			"Postes reliés au réseau");
			// Fonctionnalité réalisée si la salle est sélectionnée
			if ($bReseau) {
				$oCheckReseau->setAttribute("checked", "checked");
			}

			// Création d'un élément CHECKBOX pour le statut EXAMEN
			$oCheckExamen			= new CheckboxHelper("statut_salle_examen",			"Examens possibles");
			// Fonctionnalité réalisée si la salle est sélectionnée
			if ($bExamen) {
				$oCheckExamen->setAttribute("checked", "checked");
			}

			// Création d'un élément CHECKBOX pour le statut RÉSERVABLE
			$oCheckReservable		= new CheckboxHelper("statut_salle_reservable",		"Réservations possibles");
			// Fonctionnalité réalisée si la salle est sélectionnée
			if ($bExamen) {
				$oCheckReservable->setAttribute("checked", "checked");
			}

			$this->_html			.= "	<hr class=\"blue\">
											<fieldset $sClassField id=\"caracteristiques\"><legend>Caractéristiques</legend>
												<ol>
													<li>
														<label for=\"idCapaciteSalle\">Capacité de la salle</label>
														<input maxlength=" . AdministrationManager::CAPACITE_MAX_LENGTH . " type=\"number\" id=\"idCapaciteSalle\" class=\"numeric center width-50\" name=\"statut_salle_capacite\"  value=\"" . $nCapacite . "\" $sReadonly/>
				
														<input type=\"hidden\" id=\"idStatutSalle\" name=\"statut_salle_id\" value=\"" . $nIdStatutSalle . "\"/>
													</li>
													<li>
														<table class=\"half-width margin-auto\">
															<tr>
																<td class=\"no-wrap\">" . $oCheckInformatique->renderHTML() . "</td>
																<td class=\"width-200\">&nbsp;</td>
																<td class=\"no-wrap\">" . $oCheckReseau->renderHTML() . "</td>
															</tr>
															<tr>
																<td class=\"no-wrap\">" . $oCheckReservable->renderHTML() . "</td>
																<td class=\"width-200\">&nbsp;</td>
																<td class=\"no-wrap\">" . $oCheckExamen->renderHTML() . "</td>
															</tr>
														</table>
													</li>
												</ol>
											</fieldset>";
		}

		// Finalisation
		$this->_html				.= "</section>
										<hr class=\"blue\">";
	}

	/**
	 * @brief	Zone de liste du référentiel.
	 *
	 * @li	Affichage d'un tableau récapitulatif de l'ensemble du référentiel.
	 * @return	void
	 */
	private function _buildListeReferentiel() {
		// Construction du tableau
		$oReferentiel				= new DatatableHelper("table-referentiels",	$this->_aListeRefentiel);
		$oReferentiel->setClassColumn("align-left strong",			"libelle_referentiel");
		$oReferentiel->setFormatOnColumn('date_debut_referentiel',	DataHelper::DATA_TYPE_DATE);
		$oReferentiel->setFormatOnColumn('date_fin_referentiel',	DataHelper::DATA_TYPE_DATE);

		// Personnalisation des noms de colonne
		$aColonnes					= array(
			'libelle_referentiel'				=> "LIBELLÉ"
		);

		// Fonctionnalité réalisée si le référentiel correspond à la table `salle`
		if ($this->_sTableName == ReferentielManager::TABLE_SALLE) {
			// Ajout du champ CAPACITÉ
			$aColonnes['capacite_statut_salle']	= "CAPACITÉ";
		}

		// Fonctionnalité réalisée si le référentiel est lié à un parent
		if (array_key_exists($this->_sTableName, ReferentielManager::$REF_TABLE_PARENT)) {
			$oReferentiel->setClassColumn("align-left",				"libelle_parent");
			$aColonnes['libelle_parent']		= "PARENT";
		}
		$aColonnes['date_debut_referentiel']	= "DEBUT";
		$aColonnes['date_fin_referentiel']		= "FIN";
		$oReferentiel->renameColumns($aColonnes, true);

		// Tri du tableau sur la colonne LIBELLÉ par ordre ASC
		$oReferentiel->setOrderColumn('LIBELLÉ', DatatableHelper::ORDER_ASC);

		// Ajout d'une colonne [ACTION] avec un lien HREF
		$oReferentiel->addAnchorOnColumn("ACTION", 0, "Éditer", "/referentiel/editer?table=" . $this->_sTableName . "&id=", "confirm green");

		$this->_html				.= "<section id=\"tableauReferentiel\">
											<fieldset class=\"padding-20\" id=\"liste\"><legend>Liste du référentiel</legend>
												" . $oReferentiel->renderHTML() . "
											</fieldset>
										</section>";
	}

	/**
	 * @brief	Rendu final du formulaire
	 * @return	string
	 */
	public function render() {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/ReferentielHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/ReferentielHelper.js");

		// Renvoi du code HTML
		return $this->_html;
	}
}
