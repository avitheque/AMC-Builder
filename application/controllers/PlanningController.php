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
 * @version		$LastChangedRevision: 129 $
 * @since		$LastChangedDate: 2018-05-29 22:12:23 +0200 (Tue, 29 May 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class PlanningController extends AbstractFormulaireController {

	const	DEFAULT_DURATION	= 1;
	const	DEFAULT_TIME		= 60;
	const	DEFAULT_LAYOUT		= "plain";

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
	 * @brief	Action du contrôleur réalisée pour la recherche des tâches.
	 *
	 * Fonctionnalité exploitée par le formulaire MODAL du PlanningHelper.
	 * @see	/planning/search.phtml
	 */
	public function tacheAction() {
		// Récupération du paramètre de la durée depuis le formulaire MODAL
		$nDuree						= $this->issetParam('item_duree') ? $this->getParam('item_duree') : self::DEFAULT_DURATION;

		/** @TOTO Réaliser une requête en base de données afin de récupérer les tâches disponibles de la forme suivante :
		 *
		 * @code
		 *	array(
		 *		'task_id'			=> 1111,														// Identifiant de la tâche en base de données
		 *
		 *		'task_matter'		=> "Élément A",													// Titre de la matière affectée à la tâche
		 *		'task_matterId'		=> 1,															// Identifiant de la matière
		 *
		 *		'task_location'		=> "Localisation de la tâche...",								// Libellé de la localisation affectée à la tâche
		 *		'task_locationId'	=> 1,															// Identifiant de la localisation
		 *
		 *		'task_team'			=> array("<B>Principal</B>", "Secondaire A, "Secondaire B"),	// Liste des participants, les participants PRINCIPAUX sont contenus entre les balses <B>*</B>
		 *		'task_teamId'		=> 1,															// Identifiant de la liste des participants
		 *
		 *		'task_year'			=> null,														// \
		 *		'task_month'		=> null,														//  \
		 *		'task_day'			=> null,														//   \ L'ensemble de ces champs sont
		 *		'task_hour'			=> null,														//   / modifiés automatiquement par JS
		 *		'task_minute'		=> null,														//  /
		 *		'task_duration'		=> null,														// /
		 *
		 *		'task_update'		=> 0															// Indicateur de modification de la tâche
		 *	);
		 * @encode
		 */
		// MOCK : Simulation des éléments récupérés par la base de données
		$aMock = array(
			array(
				'task_id'			=> 1000,
				'task_matter'		=> "Élément A",
		 		'task_matterId'		=> 1,
				'task_location'		=> "Description de la tâche...",
				'task_locationId'	=> 1,
				'task_team'			=> array("<B>John DOE</B>"),		
				'task_teamId'		=> 1,
				'task_year'			=> null,
				'task_month'		=> null,
				'task_day'			=> null,
				'task_hour'			=> null,
				'task_minute'		=> null,
				'task_duration'		=> $nDuree,
				'task_update'		=> 0
			),
			array(
				'task_id'			=> 1001,
				'task_matter'		=> "Élément B",
		 		'task_matterId'		=> 2,
				'task_location'		=> "Description de la tâche...",
				'task_locationId'	=> 1,
				'task_team'			=> array("<B>John DOE</B>"),		
				'task_teamId'		=> 1,
				'task_year'			=> null,
				'task_month'		=> null,
				'task_day'			=> null,
				'task_hour'			=> null,
				'task_minute'		=> null,
				'task_duration'		=> $nDuree,
				'task_update'		=> 0
			),
			array(
				'task_id'			=> 1002,
				'task_matter'		=> "Élément C",
		 		'task_matterId'		=> 3,
				'task_location'		=> "Description de la tâche...",
				'task_locationId'	=> 1,
				'task_team'			=> array("<B>John DOE</B>"),		
				'task_teamId'		=> 1,
				'task_year'			=> null,
				'task_month'		=> null,
				'task_day'			=> null,
				'task_hour'			=> null,
				'task_minute'		=> null,
				'task_duration'		=> $nDuree,
				'task_update'		=> 0
			),
			array(
				'task_id'			=> 1003,
				'task_matter'		=> "Élément D",
		 		'task_matterId'		=> 4,
				'task_location'		=> "Description de la tâche...",
				'task_locationId'	=> 1,
				'task_team'			=> array("<B>John DOE</B>"),		
				'task_teamId'		=> 1,
				'task_year'			=> null,
				'task_month'		=> null,
				'task_day'			=> null,
				'task_hour'			=> null,
				'task_minute'		=> null,
				'task_duration'		=> $nDuree,
				'task_update'		=> 0
			)
		);

		// Transmission de la liste des tâches à la vue
		$this->addToData('liste_taches', $aMock);

		// Transmission des attibuts du formulaire MODAL du PLANNING à la vue
		$this->addToData('item_duree', $nDuree);
	}

	/**
	 * @brief	Action d'affichage d'un Planning au format PDF.
	 */
	public function exempleAction() {}

}
