<?php
/**
 * @brief	Classe contrôleur de recherche.
 *
 * @li	Classe exploité par les moteurs de recherche de l'application.
 * @li	Désactivation du rendu de la page HTML par défaut
 * @code
 * 	ViewRender::setNoRenderer(true);
 * @endcode
 *
 * Étend la classe abstraite AbstractAuthenticateController.
 * @see			{ROOT_PATH}/libraries/controller/AbstractAuthenticateController.php
 *
 * @name		SearchController
 * @category	Controller
 * @package		Main
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
class SearchController extends AbstractAuthenticateController {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__);

		// Désactivation du rendu de la page HTML
		ViewRender::setNoRenderer(true);
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {}

	/**
	 * @brief	Action finale du contrôleur.
	 */
	public function finalAction() {}


	/**
	 * @brief	Construction du référentiel.
	 *
	 * @return	void
	 */
	private function _buildReferentiel() {
		// Initialisation de l'instance du référentiel
		$oReferentielManager	= new ReferentielManager();

		// Construction du référentiel
		$this->addToData('liste_domaines',			$oReferentielManager->findListeDomaines());
		$this->addToData('liste_sous_domaines',		$oReferentielManager->findListeSousDomaines());
		$this->addToData('liste_categories',		$oReferentielManager->findListeCategories());
		$this->addToData('liste_sous_categories',	$oReferentielManager->findListeSousCategories());
	}

	/**
	 * @brief	Recherche d'un sous-domaine.
	 *
	 * @li	Recherche la liste des sous-domaines selon l'identifiant du domaine passé via AJAX.
	 *
	 * @return	JSON
	 */
	public function sous_domaineAction() {
		// Récupération de l'identifiant du domaine AJAX
		$nIdDomaine				= $this->getParam('id_domaine');

		// Initialisation de l'instance du référentiel
		$oReferentielManager	= new ReferentielManager();

		// Recherche de la la liste des sous-domaines selon l'identifiant du domaine
		$aReferentielSearch		= $oReferentielManager->findListeSousDomaines($nIdDomaine);

		// Encodage du tableau au format JSON
		print json_encode($aReferentielSearch);
	}

	/**
	 * @brief	Recherche d'un sous-domaine.
	 *
	 * @li	Recherche la liste des catégories selon l'identifiant du domaine passé via AJAX.
	 *
	 * @return	JSON
	 */
	public function categorieAction() {
		// Récupération de l'identifiant du domaine AJAX
		$nIdDomaine			= $this->getParam('id_domaine');

		// Initialisation de l'instance du référentiel
		$oReferentielManager	= new ReferentielManager();

		// Recherche de la la liste des sous-catégories selon l'identifiant de la catégorie
		$aReferentielSearch		= $oReferentielManager->findListeCategories($nIdDomaine);

		// Encodage du tableau au format JSON
		print json_encode($aReferentielSearch);
	}

	/**
	 * @brief	Recherche d'une sous-catégorie.
	 *
	 * @li	Recherche la liste des sous-catégories selon l'identifiant de la catégorie passée via AJAX.
	 *
	 * @return	JSON
	 */
	public function sous_categorieAction() {
		// Récupération de l'identifiant de la catégorie AJAX
		$nIdCategorie			= $this->getParam('id_categorie');

		// Initialisation de l'instance du référentiel
		$oReferentielManager	= new ReferentielManager();

		// Recherche de la la liste des sous-catégories selon l'identifiant de la catégorie
		$aReferentielSearch		= $oReferentielManager->findListeSousCategories($nIdCategorie);

		// Encodage du tableau au format JSON
		print json_encode($aReferentielSearch);
	}

	/**
	 * @brief	Recherche d'une liste de candidats disponibles.
	 *
	 * @return	HTML
	 */
	public function candidatAction() {
		// Récupération des filtres AJAX
		$dDebut					= $this->getParam('debut');
		$dFin					= $this->getParam('fin');

		// Instance de l'administration
		$oAdministrationManager	= new AdministrationManager();

		// Liste des candidats disponibles pour le stage
		$this->addToData('liste_candidats',	$oAdministrationManager->findCandidatByDate($dDebut, $dFin));
	}

	/**
	 * @brief	Recherche d'un stage par son identifiant.
	 *
	 * @return	JSON
	 */
	public function stageAction() {
		// Récupération des filtres AJAX
		$nId					= $this->getParam('id');

		// Instance de l'administration
		$oAdministrationManager	= new AdministrationManager();

		// Récupération des données du stage
		$aStage					= $oAdministrationManager->getStageById($nId);

		// Encodage du tableau au format JSON
		print json_encode($aStage);
	}

	/**
	 * @brief	Recherche de la liste des salles disponibles.
	 *
	 * @return	HTML
	 */
	public function salleAction() {
		// Récupération des filtres AJAX
		$dDate					= $this->getParam('date');
		$tHeure					= $this->getParam('heure');
		$nDuree					= $this->getParam('duree');
		$aChoix					= $this->getParam('epreuve_liste_salles');

		// Instance de l'administration
		$oReferentielManager	= new ReferentielManager();

		// Récupération des données du stage
		$this->addToData('liste_salles',			$oReferentielManager->findListeSalles($dDate, $tHeure, $nDuree));
		$this->addToData('epreuve_liste_salles',	(array) $aChoix);
	}

	/**
	 * @brief	Recherche d'une bibliothèque.
	 *
	 * @li	Recherche d'une bibliothèque de questions selon les filtres passés via AJAX.
	 *
	 * @return	HTML
	 */
	public function bibliothequeAction() {
		// Récupération des filtres AJAX
		$nIdDomaine				= $this->getParam('domaine');
		$nIdSousDomaine			= $this->getParam('sous_domaine');
		$nIdCategorie			= $this->getParam('categorie');
		$nIdSousCategorie		= $this->getParam('sous_categorie');
		$bOrphelin				= $this->getParam('orphelin') == "true";

		// Initialisation des critères
		if ($bOrphelin) {
			$nIdDomaine = $nIdSousDomaine = $nIdCategorie = $nIdSousCategorie = 0;
		}

		// Construction des critères de recherche à partir des filtres
		$aCriteres				= array(
			'id_domaine'		=> $nIdDomaine,
			'id_sous_domaine'	=> $nIdSousDomaine,
			'id_categorie'		=> $nIdCategorie,
			'id_sous_categorie'	=> $nIdSousCategorie
		);

		// Récupère la liste des identifiants à exclude du résultat
		$aListeExcludeId		= explode(GalleryHelper::EXCLUDE_SEPARATOR, $this->getParam('exclude'));

		// Initialisation du référentiel dans la vue
		$this->_buildReferentiel();

		// Instance du modèle de gestion des formulaires
		$oFormulaireManager		= new FormulaireManager();

		// Recherche de la question par son identifiant
		$aListeBibliotheque		= array();
		if (!empty($nIdDomaine) || !empty($nIdCategorie)) {
			// Recherche des questions selon les critères de recherche
			$aListeBibliotheque	= $oFormulaireManager->findAllQuestionsByCriteres($aCriteres, $aListeExcludeId, false);
		} elseif ($bOrphelin) {
			// Recherche des questions orphelines : questions non référencées dans un formulaire
			$aListeBibliotheque	= $oFormulaireManager->findAllQuestionsByCriteres($aCriteres, $aListeExcludeId, $bOrphelin);
		}

		// Envoi de la question à la vue
		$this->addToData('liste_bibliotheque',	(array) $aListeBibliotheque);
	}

	/**
	 * @brief	Recherche d'une question.
	 *
	 * @li	Recherche des information d'une question selon son identifiant passé via AJAX.
	 *
	 * @return	HTML
	 */
	public function questionAction() {
		// Initialisation du référentiel dans la vue
		$this->_buildReferentiel();

		// Récupération de l'identifiant de la question AJAX
		$nIdQuestion			= $this->getParam('id');

		// Instance du modèle de gestion des formulaires
		$oFormulaireManager		= new FormulaireManager();

		// Recherche de la question par son identifiant
		$aQuestionSearch		= $oFormulaireManager->getQuestionReponsesByIdQuestion($nIdQuestion);

		// Envoi de la question à la vue
		$this->addToData('question_search',	(array) $aQuestionSearch);
	}

}
