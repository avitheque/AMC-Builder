<?php
/**
 * @brief	Helper de création du formulaire UTILISATEUR
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un utilisateur de l'application.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe AdministrationManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		UtilisateurHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 6 $
 * @since		$LastChangedDate: 2017-03-03 00:04:19 +0100 (ven., 03 mars 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class UtilisateurHelper {

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
	 * @brief	Modification du champ du mot de passe.
	 * @var		bool
	 */
	private		$_bModifiable		= false;

	/**
	 * @brief	Formulaire PHP.
	 * @var		string
	 */
	private		$_aFormulaire		= array();

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_html				= "";

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @li	En lecture seule, les champs accessibles par l'utilisateur lui-même sont modifiables.
	 *
	 * @param	boolean	$bReadonly		: Verrouillage des champs du compte utilisateur.
	 * @param	boolean	$bModifiable	: Champ du mot de passe modifiable.
	 * @return	string
	 */
	public function __construct($bReadonly = false, $bModifiable = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage	= InstanceStorage::getInstance();

		//#################################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#################################################################################################

		// Lecture par défaut si l'utilisateur n'a pas le droit de modification
		$this->_bReadonly			= $bReadonly || !$bModifiable;

		// Lecture par défaut
		$this->_bModifiable			= $bModifiable;

		// Nom de session des données
		$sSessionNameSpace			= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Données de l'utilisateur
		$this->_aFormulaire			= $this->_oInstanceStorage->getData($sSessionNameSpace);

		// Zone du formulaire Utilisateur
		$this->_buildFormulaire();
	}

	/**
	 * @brief	Zone du formulaire QCM.
	 */
	private function _buildFormulaire() {
		// Récupération de l'instance d'authentification
		$oAuth						= AuthenticateManager::getInstance();

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

		// Classe du fieldset du profil
		$sClassProfil				= "class=\"readonly\"";
		if ($this->_bModifiable) {
			$sClassProfil			= "";
		}

		//#################################################################################################
		// GÉNÉRATITÉS SUR LE CANDIDAT
		//#################################################################################################

		// Construction de la liste déroulante du GRADE
		$nIdGradeUtilisateur		= DataHelper::get($this->_aFormulaire, 'utilisateur_grade',			DataHelper::DATA_TYPE_INT,	null);
		$sGradeOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_grades'), $nIdGradeUtilisateur, null, null, $this->_bReadonly);

		// Identifiant de l'utilisateur
		$nIdUtilisateur				= DataHelper::get($this->_aFormulaire, 'utilisateur_id', 			DataHelper::DATA_TYPE_STR,	null);
		// Nom de l'utilisateur
		$sNomUtilisateur			= DataHelper::get($this->_aFormulaire, 'utilisateur_nom',			DataHelper::DATA_TYPE_STR,	null);
		// Prénom de l'utilisateur
		$sPrenomUtilisateur			= DataHelper::get($this->_aFormulaire, 'utilisateur_prenom',		DataHelper::DATA_TYPE_STR,	null);
		// Unité de l'utilisateur
		$sUniteUtilisateur			= DataHelper::get($this->_aFormulaire, 'utilisateur_unite',			DataHelper::DATA_TYPE_STR,	null);

		// Construction de la liste déroulante du PROFIL
		$nIdProfilUtilisateur		= DataHelper::get($this->_aFormulaire, 'utilisateur_profil',		DataHelper::DATA_TYPE_INT,	null);
		$sProfilOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_profils'), $nIdProfilUtilisateur, null, null, $this->_bReadonly);

		// Login de l'utilisateur
		$sLoginUtilisateur			= DataHelper::get($this->_aFormulaire, 'utilisateur_login',			DataHelper::DATA_TYPE_STR,	null);
		// Mot de passe de l'utilisateur
		$sPasswordUtilisateur		= DataHelper::get($this->_aFormulaire, 'utilisateur_password',		DataHelper::DATA_TYPE_STR,	null);
		// Confirmation du mot de passe de l'utilisateur
		$sConfirmationUtilisateur	= DataHelper::get($this->_aFormulaire, 'utilisateur_confirmation',	DataHelper::DATA_TYPE_STR,	null);

		// Date de modification de l'utilisateur
		$dDateModification			= DataHelper::get($this->_aFormulaire, 'utilisateur_datetime',		DataHelper::DATA_TYPE_DATETIME);

		// Initialisation du mode d'accès au formulaire
		if ($this->_bReadonly) {
			$sAccessMode			= "Compte";
		} else {
			$sAccessMode			= empty($nIdUtilisateur) ? "Ajout" : "Édition";
		}

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sDelete					= "";
		// Création du bouton de suppression si l'utilisateur est valide
		if (!$this->_bReadonly && $nIdUtilisateur) {
			$sDelete				= "<button type=\"submit\" class=\"confirm-delete red right margin-0\" name=\"button\" value=\"supprimer_utilisateur\">Supprimer</button>";
		}

		$sInformationModification	= "";
		// Création de l'information sur la date de dernière modification si elle est renseignée
		if ($dDateModification) {
			// Convertion de la date au format [FR]
			$dDateFR = DataHelper::dateTimeMyToFr($dDateModification);
			$sInformationModification = "<h3 class=\"strong center\">&#151;&nbsp;Dernière modification réalisée le " . $dDateFR . "&nbsp;&#151;</h3>";
		}

		// Création du formulaire
		$this->_html				.= "<h2>$sAccessMode d'un utilisateur" . $sDelete . "</h2>
										<p>
											$sInformationModification
										</p>
										<section id=\"utilisateur\">
											<fieldset $sClassField id=\"general\"><legend>Généralités</legend>
												<ol class=\"no-wrap\">
													<li>
														<label for=\"idGradeUtilisateur\">Grade de l'utilisateur</label>
														<select id=\"idGradeUtilisateur\" name=\"utilisateur_grade\" $sDisabled>" . $sGradeOptions . "</select>
													</li>
													<li>
														<label for=\"idUtilisateur\">Identifiant de l'utilisateur</label>
														<input placeholder=\"IDENTIFIANT\" maxlength=" . AdministrationManager::UTILISATEUR_ID_MAXLENGTH . " type=\"text\" id=\"idUtilisateur\" name=\"utilisateur_id\" value=\"" . $nIdUtilisateur . "\" $sReadonly/>
														$sPencilIcon
													</li>
													<li>
														<label for=\"idNomUtilisateur\">Nom de l'utilisateur</label>
														<input maxlength=" . AdministrationManager::UTILISATEUR_NOM_MAXLENGTH . " type=\"text\" id=\"idNomUtilisateur\" class=\"half-width\" name=\"utilisateur_nom\" value=\"" . strtoupper($sNomUtilisateur) . "\" $sReadonly/>
														$sPencilIcon
													</li>
													<li>
														<label for=\"idPrenomUtilisateur\">Prénom de l'utilisateur</label>
														<input maxlength=" . AdministrationManager::UTILISATEUR_PRENOM_MAXLENGTH . " type=\"text\" id=\"idPrenomUtilisateur\" class=\"half-width\" name=\"utilisateur_prenom\" value=\"" . ucfirst($sPrenomUtilisateur) . "\" $sReadonly/>
														$sPencilIcon
													</li>
												</ol>
											</fieldset>
											<hr class=\"blue\">
											<fieldset $sClassProfil id=\"utilisateur\"><legend>Profil de l'utilisateur</legend>
												<ol class=\"no-wrap\">
													<li>
														<label for=\"idProfilUtilisateur\">Profil de l'utilisateur</label>
														<select id=\"idProfilUtilisateur\" name=\"utilisateur_profil\" $sDisabled>" . $sProfilOptions . "</select>
													</li>
													<li>
														<label for=\"idLoginUtilisateur\">Login de connexion</label>
														<input maxlength=" . AdministrationManager::UTILISATEUR_LOGIN_MAXLENGTH . " type=\"text\" id=\"idLoginUtilisateur\" class=\"half-width\" name=\"utilisateur_login\" value=\"" . $sLoginUtilisateur . "\" $sReadonly/>
														$sPencilIcon
													</li>";

		// Fonctionnalité réalisée si l'utilisateur peut modifier le mot-de-passe
		if ($this->_bModifiable) {
			$this->_html			.= "			<li>
														<label for=\"idPasswordUtilisateur\">Réinitialisation du mot de passe</label>
														<input maxlength=" . AdministrationManager::UTILISATEUR_PASSWORD_MAXLENGTH . " type=\"password\" id=\"idPasswordUtilisateur\" name=\"utilisateur_password\" value=\"" . $sPasswordUtilisateur . "\" class=\"editable\"/>
														<span class=\"ui-icon ui-icon-pencil inline-block relative right-22 align-sub\">&nbsp;</span>
													</li>
													<li>
														<label for=\"idConfirmationUtilisateur\">Confirmation du mot de passe</label>
														<input maxlength=" . AdministrationManager::UTILISATEUR_PASSWORD_MAXLENGTH . " type=\"password\" id=\"idConfirmationUtilisateur\" name=\"utilisateur_confirmation\" value=\"" . $sConfirmationUtilisateur . "\" class=\"editable\"/>
														<span class=\"ui-icon ui-icon-pencil inline-block relative right-22 align-sub\">&nbsp;</span>
													</li>";
		}

		// Finalisation du formulaire
		$this->_html				.= "		</ol>
											</fieldset>";

		// Fonctionnalité réalisée si le formulaire n'est pas en lecture seule
		if (! $this->_bReadonly) {
			// Ajout des informations des profils
			$this->_html			.= "	<hr class=\"blue\">
											<section class='accordion'>
												<h3 class='item-title' id='tabs-invite'>Invité</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>Par défaut, tout utilisateur non authentifié possède le profil <span class='strong italic'>Invité</span>.</strong>
													<br/>
													<br/>
													<u>Il peut également :</u>
													<ul>
														<li>&bull; Consulter l'état de réservation des salles ;</li>
														<li>&bull; S'authentifier s'il possède un compte sur l'application.</li>
													</ul>
												</div>
												<h3 class='item-title' id='tabs-utilisateur'>Utilisateur</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>L'utilisateur doit être authentifié dans l'application.</strong>
													<br/>
													<br/>
													<u>Il peut également en plus du profil <span class='accordion-link strong italic pointer' for='tabs-invite'>Invité</span> :</u>
													<ul>
														<li>&bull; Gérer son compte ;</li>
														<li>&bull; Réserver une salle.</li>
													</u>
												</div>
												<h3 class='item-title' id='tabs-redacteur'>Rédacteur</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>L'utilisateur doit être authentifié dans l'application.</strong>
													<br/>
													<br/>
													<u>Il peut également en plus du profil <span class='accordion-link strong italic pointer' for='tabs-utilisateur'>Utilisateur</span> :</u>
													<ul>
														<li>&bull; Importer un formulaire QCM d'une autre plateforme prise en charge ;</li>
														<li>&bull; Rédiger un nouveau formulaire QCM ;</li>
														<li>&bull; Modifier un formulaire QCM existant.</li>
													</u>
												</div>
												<h3 class='item-title' id='tabs-validateur'>Valideur</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>L'utilisateur doit être authentifié dans l'application.</strong>
													<br/>
													<br/>
													<u>Il peut également en plus du profil <span class='accordion-link strong italic pointer' for='tabs-utilisateur'>Utilisateur</span> :</u>
													<ul>
														<li>&bull; Valider un formulaire QCM ;</li>
														<li>&bull; Générer un formulaire QCM ;</li>
														<li>&bull; Exporter un formulaire QCM au format <i>LaTeX</i>.</li>
													</ul>
												</div>
												<h3 class='item-title' id='tabs-administrateur'>Administrateur</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>L'utilisateur doit être authentifié dans l'application.</strong>
													<br/>
													<br/>
													<u>Il peut également en plus du profil <span class='accordion-link strong italic pointer' for='tabs-validateur'>Valideur</span> :</u>
													<ul>
														<li>&bull; Administrer le référentiel de l'application <i>(dont les salles)</i> ;</li>
														<li>&bull; Administrer les utilisateurs de l'application ;</li>
														<li>&bull; Administrer les stages ;</li>
														<li>&bull; Administrer les candidats.</li>
													</ul>
												</div>
												<h3 class='item-title' id='tabs-webmaster'>Webmaster</h3>
												<div class='item-content auto-height'>
													<strong class='blue italic'>L'utilisateur doit être authentifié dans l'application.</strong>
													<br/>
													<br/>
													<u>Il peut également en plus du profil <span class='accordion-link strong italic pointer' for='tabs-administrateur'>Administrateur</span> :</u>
													<ul>
														<li>&bull; Superviser le serveur de l'application.</li>
													</ul>
												</div>
											</section>";
		}

		// Finalisation du formulaire
		$this->_html				.= "</section>";
	}

	/**
	 * @brief	Rendu final du formulaire
	 * @return	string
	 */
	public function render() {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/UtilisateurHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/UtilisateurHelper.js");

		// Renvoi du code HTML
		return $this->_html;
	}
}
