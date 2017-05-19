/**
 * @brief	Variable d'instance de PLANNING
 */
if (typeof(PLANNING_ITEM) == 'undefined') {
	// Constantes du MODAL
	var MODAL_MD5_PREFIXE		= "search-content-";

	// Constantes de gestion du PLANNING
	var PLANNING_MD5_PREFIXE	= "planning-item-";
	var PLANNING_ITEM			= new Array();
	var PLANNING_ITEM_REGEXP	= /^planning-[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]+$/;
	var PLANNING_ITEM_FACTEUR	= 0.65;
	var PLANNING_ITEM_MARGIN	= 10;
	var PLANNING_ITEM_ATTRIBUTE	= ["tache_annee", "tache_mois", "tache_jour", "tache_heure"];
	var PLANNING_ITEM_IGNORE	= ["tache_participant", "tache_duree"];
}

/**
 * @brief	Classe JavaScript de gestion d'une tâche du PLANNING.
 *
 * @param	string	id			: identifiant de la tâche.
 * @param	integer	duration	: durée de la tâche en minutes.
 * @param	object	htmlElement	: contenu de l'élément.
 * User: durandcedric
 * Date: 12/05/17
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
PlanningItem = function(id, duration, htmlElement) {
	// Initialisation de l'identifiant
	this.id				= id;
	this.duration		= duration;
    this.htmlElement	= htmlElement;

	/**
	 * @brief	Débuggage du contenu de l'instance
	 * 
	 * @li		La variable d'instance PLANNING_DEBUG doit être à initialisée à TRUE.
	 * 
	 * @return	string
	 */
	this.debug		= function(message) {
		// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
		if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
			// Affichage du message de DEBUGGAGE
			console.debug(message);
		}
	};

	/**
	 * @brief	Affichage du contenu de l'instance sous forme de chaîne de caractères.
	 * 
	 * @return	string
	 */
	this.toString	= function() {
		// Initialisation du message avec l'identifiant du PLANNING
		var message	= "item['" + this.id + "']: ";

		// Concaténation avec la durée
		message += this.duration;
		
		// Renvoi du contenu du message de DEBUG
		return message;
	};

    /**
     * @brief	Récupère l'identifiant de la CELLULE sous la forme `planning-Y-m-d-H`.
     */
	this.getUniqueId	= function() {
		// Renvoi de l'identifiant de la tâche
		return this.id;
	};

    /**
	 * @brief	Affichage du contenu de l'instance sur le PLANNING.
     */
	this.render	= function() {
		// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
		this.debug(this.toString());
	};
};
