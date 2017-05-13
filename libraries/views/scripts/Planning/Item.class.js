/**
 * @brief	Classe JavaScript de gestion d'une tâche du PLANNING.
 *
 * @param	string	id			: identifiant de la tâche.
 * @param	integer	duration	: durée de la tâche en minutes.
 * User: durandcedric
 * Date: 12/05/17
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
PlanningItem = function(id, duration) {
	// Initialisation de l'identifiant
	this.id			= id;
	this.duration	= duration;
};
