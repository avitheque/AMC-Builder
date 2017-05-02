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
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
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
		 *		'tache_titre'		=> "Élément A",
		 *		'tache_texte'		=> "Appuyez sur le bouton [zoom] afin de voir le détail...",
		 *		'tache_participant'	=> "Personne 1, Personne 2, Personne 3",
		 *		'tache_annee'		=> null,
		 *		'tache_mois'		=> null,
		 *		'tache_jour'		=> null,
		 *		'tache_duree'		=> $nDuree,
		 *	);
		 * @encode
		 */
		// MOCK : Simulation des éléments récupérés par la base de données
		$aMock = array(
			array(
				'planning_id'		=> null,
				'tache_id'			=> "A",								// Identifiant de la tâche en base de données
				'tache_titre'		=> "Élément A",						// Titre de la tâche
				'tache_texte'		=> "Description de la tâche...",
				'tache_participant'	=> null,
				'tache_annee'		=> null,
				'tache_mois'		=> null,
				'tache_jour'		=> null,
				'tache_duree'		=> null
				),
			array(
				'planning_id'		=> null,
				'tache_id'			=> "B",								// Identifiant de la tâche en base de données
				'tache_titre'		=> "Élément B",						// Titre de la tâche
				'tache_texte'		=> "Description de la tâche...",
				'tache_participant'	=> null,
				'tache_annee'		=> null,
				'tache_mois'		=> null,
				'tache_jour'		=> null,
				'tache_duree'		=> null
			),
			array(
				'planning_id'		=> null,
				'tache_id'			=> "C",								// Identifiant de la tâche en base de données
				'tache_titre'		=> "Élément C",						// Titre de la tâche
				'tache_texte'		=> "Description de la tâche...",
				'tache_participant'	=> null,
				'tache_annee'		=> null,
				'tache_mois'		=> null,
				'tache_jour'		=> null,
				'tache_duree'		=> null
			),
			array(
				'planning_id'		=> null,
				'tache_id'			=> "D",								// Identifiant de la tâche en base de données
				'tache_titre'		=> "Élément D",						// Titre de la tâche
				'tache_texte'		=> "Description de la tâche...",
				'tache_participant'	=> null,
				'tache_annee'		=> null,
				'tache_mois'		=> null,
				'tache_jour'		=> null,
				'tache_duree'		=> null
			)
		);

		// Transmission de la liste des tâches à la vue
		$this->addToData('liste_taches', $aMock);

		// Transmission des attibuts du formulaire MODAL du PLANNING à la vue
		$this->addToData('item_duree', $this->getParam('item_duree'));
	}
}
