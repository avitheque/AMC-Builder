/**
 * @brief	Classe JavaScript de gestion du PLANNING.
 *
 * @param	string	id		: identifiant au format MD5.
 * User: durandcedric
 * Date: 12/05/17
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
Planning = function(id) {
	// Initialisation de l'identifiant
	this.id		= id;

	// Initialisation de la collection
	this.item	= new Array();

	this.add	= function(id, duration) {
		// Ajout d'une tâche à la collection
		this.item[id]			= new PlanningItem(id, duration);
	};

	this.get	= function(id) {
		// Renvoi de la tâche
		return this.item[id];
	};

	this.update	= function(id, duration) {
		// Modification de la durée de la tâche
		this.item[id].duration	= duration;
	};

	this.remove	= function(id, duration) {
		// Suppresson de la tâche par son identifiant
		delete this.item[id];
	};

	this.move	= function(id_from, id_to) {
		// Changement de l'identifiant de la tâche
		this.item[id_from].id	= id_to;
		// Déplacement de la tâche
		this.item[id_to]		= this.item[id_from];
		// Suppresson de l'ancienne tâche
		this.remove(id_from);
	};

	this.exists	= function(id) {
		// Renvoi si la tâche est présente dans la collection
		return	typeof(this.item[id]) != 'undefined';
	};

	this.render	= function() {
		/** @todo ACTUALISATION DE L'AFFICHAGE ET MISE EN ÉVIDENCE DES CONFLITS */
	};

};

/**
 * @todo	DÉVELOPPEMENT EN COURS...
 *
var test = new Planning(123456);
test.add("planning-2017-01-02-9", 2);

alert("planning-2017-01-02-9 : " + test.exists("planning-2017-01-02-9"));

test.move("planning-2017-01-02-9", "planning-2017-01-02-8");

alert("planning-2017-01-02-9 : " + test.exists("planning-2017-01-02-9"));
alert("planning-2017-01-02-8 : " + test.exists("planning-2017-01-02-8"));
 */
