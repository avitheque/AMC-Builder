<?php
/**
 * @brief	Helper de création du formulaire GROUPE
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un groupe.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe AdministrationManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		GroupeHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 32 $
 * @since		$LastChangedDate: 2017-06-11 01:31:10 +0200 (Sun, 11 Jun 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class GroupeHelper {

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
	private		$_oArborescence		= null;
	private		$_aFormulaire		= array();
	private		$_aListeGroupes		= array();

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

		// Récupération de la liste des groupes liés au candidat
		$this->_aListeGroupes		= $this->_oInstanceStorage->getData('liste_groupes');

		// Zone du formulaire Candidat
		$this->_buildFormulaire();

		// Zone de liste des Groupes
		$this->_buildListeGroupes();
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
		// GÉNÉRATITÉS SUR LE GROUPE
		//#################################################################################################

		// Identifiant du groupe
		$nIdGroupe					= DataHelper::get($this->_aFormulaire, 'groupe_id', 		DataHelper::DATA_TYPE_INT,	null);
		// Libellé du groupe
		$sLibelleGroupe				= DataHelper::get($this->_aFormulaire, 'groupe_libelle',	DataHelper::DATA_TYPE_STR,	null);
		// Date de modification du groupe
		$dDateModification			= DataHelper::get($this->_aFormulaire, 'groupe_datetime',	DataHelper::DATA_TYPE_DATETIME);

		// Initialisation du mode d'accès au formulaire
		$sAccessMode				= empty($nIdGroupe) ? "Ajout" : "Édition";

		//#################################################################################################
		// CONSTRUCTION DE L'ARBORESCENCE
		//#################################################################################################

		// Initialisation de l'instance
		$this->_oArborescence		= new ArborescenceHelper('groupes');
		$this->_oArborescence->setIdPosition('id_groupe');
		$this->_oArborescence->setLabelPositionInterval('libelle_groupe');
		$this->_oArborescence->setLeftPosition('borne_gauche');
		$this->_oArborescence->setRightPosition('borne_droite');
		// Chargement à partir de la liste exploitant des intervalles
		$this->_oArborescence->setListeItemsFromIntervalles($this->_aListeGroupes);
		// Mise en évidence de l'élément actif
		$this->_oArborescence->setActiveById($nIdGroupe);

		//#################################################################################################
		// CODE HTML
		//#################################################################################################

		$sDelete					= "";
		// Création du bouton de suppression si la liste des groupes est vide
		if (!$this->_bReadonly && $nIdGroupe && empty($this->_aListeGroupes)) {
			$sDelete				= "<button type=\"submit\" class=\"confirm-delete red right margin-0\" name=\"button\" value=\"supprimer_groupe\">Supprimer</button>";
		}

		$sInformationModification	= "";
		// Création de l'information sur la date de dernière modification si elle est renseignée
		if ($dDateModification) {
			// Convertion de la date au format [FR]
			$dDateFR = DataHelper::dateTimeMyToFr($dDateModification);
			$sInformationModification = "<h3 class=\"strong center\">&#151;&nbsp;Dernière modification réalisée le " . $dDateFR . "&nbsp;&#151;</h3>";
		}

		// Création du formulaire
		$this->_html				.= "<h2>$sAccessMode d'un groupe" . $sDelete . "</h2>
										<p>
											$sInformationModification
										</p>
										<section id=\"groupe\">
											<fieldset $sClassField id=\"general\"><legend>Généralités</legend>
											
											</fieldset>
										</section>
										<hr class=\"blue\"";
	}

	/**
	 * @brief	Zone de liste des Groupes.
	 *
	 * @li	Affichage d'un tableau récapitulatif de l'ensemble des groupes que suit le candidat.
	 * @return	void
	 */
	private function _buildListeGroupes() {
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
		// CODE HTML
		//#################################################################################################

		$this->_html				.= "<section id=\"tableauGroupes\">
											<fieldset $sClassField id=\"liste\"><legend>Selection du groupe parent</legend>
												" . $this->_oArborescence->renderHtml(true) . "
											</fieldset>
										</section>";
	}

	/**
	 * @brief	Rendu final du formulaire
	 * @return	string
	 */
	public function render() {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/GroupeHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/GroupeHelper.js");

		// Renvoi du code HTML
		return $this->_html;
	}
}
