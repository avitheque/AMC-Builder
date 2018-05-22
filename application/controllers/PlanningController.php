<?php
/**
 * @brief	Classe contrôleur du helper PlanningHelper.
 *
 * Ce contrôleur est appelé par le formulaire MODAL de PlanningHelper afin de récupérer toutes les tâches disponibles selon le moteur de recherche.
 *
 * Étend la classe abstraite AbstractFormulaireController.
 * @see			{ROOT_PATH}/libraries/controller/AbstractFormulaireController.php
 *
 * @name		PlanningController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 126 $
 * @since		$LastChangedDate: 2018-05-22 19:53:26 +0200 (Tue, 22 May 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PlanningController extends AbstractFormulaireController {

	const	DEFAULT_TIME	= 60;
	const	DEFAULT_LAYOUT	= "plain";

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Désactivation du rendu de la page HTML.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'PLANNING', LoginInterface::$LIST_CHAMPS_FORM);
		ViewRender::setNoRenderer(true);
	}

	/**
	 * @brief	Action par défaut du contrôleur.
	 */
	public function indexAction() {}

	/**
	 * @brief	Action du contrôleur réalisée pour la recherche des taches.
	 */
	public function tacheAction() {

		/** @TOTO Réaliser une requête en base de données afin de récupérer les tâches disponibles de la forme suivante :
		 *
		 * @code
		 *	array(
		 *		'planning_id'		=> null,
		 *		'tache_id'			=> "A",
		 *		'tache_title'		=> "Élément A",
		 *		'tache_location'	=> 1,
		 *		'tache_describe'	=> "Description de la tâche...",
		 *		'tache_groupe'		=> 1,
		 *		'tache_participant'	=> "Personne 1, Personne 2, Personne 3",
		 *		'tache_annee'		=> null,
		 *		'tache_mois'		=> null,
		 *		'tache_jour'		=> null,
		 *		'tache_duree'		=> 1,
		 *		'tache_update'		=> 0
		 *	);
		 * @encode
		 */
		// MOCK : Simulation des éléments récupérés par la base de données
		$aMock = array(
				array(
						'planning_id'		=> null,
						'tache_id'			=> "A",								// Identifiant de la tâche en base de données
						'tache_title'		=> "Élément A",						// Titre de la tâche
						'tache_location'	=> 1,
						'tache_describe'	=> "Description de la tâche...",
						'tache_groupe'		=> 1,
						'tache_participant'	=> array("<B>John DOE</B>"),		// Participant PRINCIPAL
						'tache_annee'		=> null,
						'tache_mois'		=> null,
						'tache_jour'		=> null,
						'tache_duree'		=> null,
						'tache_update'		=> 0
				),
				array(
						'planning_id'		=> null,
						'tache_id'			=> "B",								// Identifiant de la tâche en base de données
						'tache_title'		=> "Élément B",						// Titre de la tâche
						'tache_location'	=> 1,
						'tache_describe'	=> "Description de la tâche...",
						'tache_groupe'		=> 1,
						'tache_participant'	=> array("<B>John DOE</B>"),		// Participant PRINCIPAL
						'tache_annee'		=> null,
						'tache_mois'		=> null,
						'tache_jour'		=> null,
						'tache_duree'		=> null,
						'tache_update'		=> 0
				),
				array(
						'planning_id'		=> null,
						'tache_id'			=> "C",								// Identifiant de la tâche en base de données
						'tache_title'		=> "Élément C",						// Titre de la tâche
						'tache_location'	=> 1,
						'tache_describe'	=> "Description de la tâche...",
						'tache_groupe'		=> 1,
						'tache_participant'	=> array("<B>John DOE</B>"),		// Participant PRINCIPAL
						'tache_annee'		=> null,
						'tache_mois'		=> null,
						'tache_jour'		=> null,
						'tache_duree'		=> null,
						'tache_update'		=> 0
				),
				array(
						'planning_id'		=> null,
						'tache_id'			=> "D",								// Identifiant de la tâche en base de données
						'tache_title'		=> "Élément D",						// Titre de la tâche
						'tache_location'	=> 1,
						'tache_describe'	=> "Description de la tâche...",
						'tache_groupe'		=> 1,
						'tache_participant'	=> array("<B>John DOE</B>"),		// Participant PRINCIPAL
						'tache_annee'		=> null,
						'tache_mois'		=> null,
						'tache_jour'		=> null,
						'tache_duree'		=> null,
						'tache_update'		=> 0
				)
		);

		// Transmission de la liste des tâches à la vue
		$this->addToData('liste_taches', $aMock);

		// Transmission des attibuts du formulaire MODAL du PLANNING à la vue
		$this->addToData('item_duree', $this->getParam('item_duree'));
	}

	/**
	 * @brief	Action d'affichage d'un Planning au format PDF.
	 */
	public function exempleAction() {}

}
