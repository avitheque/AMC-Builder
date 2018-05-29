/**
 * @brief	Variable d'instance de PLANNING
 */
if (typeof(PLANNING) == 'undefined') {
	// Constantes de gestion du PLANNING
	var PLANNING				= new Array();
	var PLANNING_MOUSEHOVER		= false;
	var PLANNING_ERROR			= false;
}

/**
 * @brief	Classe JavaScript de gestion du PLANNING.
 *
 * @param	string				id			: identifiant au format MD5.
 * User: durandcedric
 * Date: 12/05/17
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
Planning = function(id) {
	// Initialisation de l'identifiant de l'instance
	this.id					= id;

	// Initialisation de la collection
	this.item				= new Array();

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
	 * @brief	Vérification de l'existance d'une tâche.
	 * 
	 * @param	string			id			: identifiant de la CELLULE du PLANNING.
	 * @return	void
	 */
	this.exists		= function(id) {
		// Renvoi si la tâche est présente dans la collection
		return	typeof(this.item[id]) != 'undefined';
	};

	/**
	 * @brief	Récupération de l'instance d'une tâche.
	 * 
	 * @param	string|object	id			: identifiant de la CELLULE du PLANNING.
	 * @return	object ProgressionItem
	 */
	this.get		= function(id) {
		// Fonctionnalité réalisée si l'élément passé en 2nd argument est un objet
		if (typeof(id) == 'object') {
			$oItem			= id;
			var id			= $oItem.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas
			if (typeof(id) == 'undefined') {
				id			= $oItem.getUniqueId();
			}
		}
		
		// Récupération de la tâche
		$oItem		= {};
		if (this.exists(id)) {
			// La tâche est présente
			$oItem			= this.item[id];
		} else {
			// La tâche est abscente
			$oItem			= false;
		}
		
		// Renvoi de la tâche
		return $oItem;
	};

	/**
	 * @brief	Ajout d'une tâche au PLANNING.
	 * 
	 * @param	string|object	id			: identifiant de la CELLULE du PLANNING.
	 * @param	integer			duration	: valeur de durée pour la tâche.
	 * @param	boolean			bRender		: (optionnel) Actualisation du rendu de la PROGRESSION.
	 * @return	void
	 */
	this.add		= function(id, duration, bRender) {
		// Fonctionnalité réalisée si l'élément passé en 1er argument est un objet
		if (typeof(id) == 'object') {
			$oItem			= id;
			var id			= $oItem.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas encore
			if (typeof(id) == 'undefined') {
				id			= $oItem.getUniqueId();
			}
			
			// Récupération de la durée de la tâche
			var duration	= $oItem.find("input[name^=task_duration]").val();
		}

		try {
			// Fonctionnalité réalisée un élément existe déjà
			if (this.exists(id)) {
				throw "Planning.item['" + id + "'] existe déjà !";
			}
			
			// Ajout d'une tâche à la collection
			this.item[id]		= new PlanningItem(id, duration);
			
			// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
			this.debug("add('" + id + "', " + duration + ")");
			
			// Actualisation de l'affichage
			if (typeof(bRender) == 'boolean' && bRender) {
				this.render();
			}
		} catch (exception) {
			// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
			this.debug("Exception !\n" + exception);
			
			// Arrêt du traîtement
			return false;
		}
	};

	/**
	 * @brief	Modification de la durée d'une tâche.
	 * 
	 * @param	string|object	id			: CELLULE du PLANNING.
	 * @param	integer			duration	: nouvelle valeur pour la durée de la tâche.
	 * @param	boolean			bRender		: (optionnel) Actualisation du rendu de la PROGRESSION.
	 * @return	void
	 */
	this.update		= function(id, duration, bRender) {
		// Fonctionnalité réalisée si l'élément passé en 2nd argument est un objet
		if (typeof(id) == 'object') {
			$oItem			= id;
			var id			= $oItem.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas
			if (typeof(id) == 'undefined') {
				id			= $oItem.getUniqueId();
			}
		}
		
		// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
		this.debug("update('" + id + "', " + duration + ")");

		// Fonctionnalité réalisée si l'élément existe bien dans l'instance
		if (typeof(id) != 'undefined' && this.exists(id) && duration > 0) {
			// Modification de la durée de la tâche
			this.item[id].duration	= duration;

			// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
			this.debug("update('" + id + "', " + duration + ")");
		}
		
		// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
		this.debug("update('" + id + "', " + duration + ")");
		
		// Actualisation de l'affichage
		if (typeof(bRender) == 'boolean' && bRender) {
			this.render();

			// Fonctionnalité réalisée si l'élément existe bien dans l'instance
			if (typeof(id) != 'undefined' && this.exists(id) && duration > 0) {
				// Modification de la durée de la tâche
				this.item[id].duration	= duration;

				// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
				this.debug("update('" + id + "', " + duration + ")");

				// Fonctionnalité réalisée si la largeur de la cellule ne dépend par de la durée
				if ($("section#" + this.id).hasClass("calendar") || $("section#" + this.id).hasClass("static")) {
					duration	= 1;
				}
				
				// Fonctionnalité réalisée si la largeur de cellule par défaut est connue
				if (typeof(PLANNING_CELL_WIDTH[this.id]) == 'undefined' || parseFloat(PLANNING_CELL_WIDTH[this.id]) == 0) {
					// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
					this.debug("\tERROR !");
					// Stop !!!
					return false;
				}

				// Redimentionnement de la CELLULE
				var newWidth	= PLANNING_CELL_WIDTH[this.id];
		        var facteur		= (newWidth * duration / PLANNING_CELL_WIDTH[this.id] - PLANNING_ITEM_MARGIN) + (PLANNING_ITEM_FACTEUR * duration);
				newWidth		= newWidth * duration + facteur;

				// Affectation de la nouvelle valeur à la CELLULE
				$("dd#" + id + " li.item", "section#" + this.id).css({width: newWidth + "px"});
			}
		}
	};

	/**
	 * @brief	Suppression d'une tâche.
	 * 
	 * @param	string|object	id			: CELLULE du PLANNING à supprimer.
	 * @param	boolean			bRender		: (optionnel) Actualisation du rendu de la PROGRESSION.
	 * @return	void
	 */
	this.remove		= function(id, bRender) {
		// Fonctionnalité réalisée si l'élément passé en 1er argument est un objet
		if (typeof(id) == 'object') {
			$oItem			= id;
			var id			= $oItem.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas
			if (typeof(id) == 'undefined') {
				id			= $oItem.getUniqueId();
			}
		}

		// Fonctionnalité réalisée si l'élément existe bien dans l'instance
		if (this.exists(id)) {
			// Suppresson de la tâche par son identifiant
			delete this.item[id];

			// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
			this.debug("remove('" + id + "')");
		}
		
		// Actualisation de l'affichage
		if (typeof(bRender) == 'boolean' && bRender) {
			this.render();
		}
	};

	/**
	 * @brief	Déplacement d'une tâche.
	 * 
	 * @param	string|object	id_from		: CELLULE du PLANNING source.
	 * @param	string|object	id_to		: CELLULE du PLANNING cible.
	 * @param	boolean			bRender		: (optionnel) Actualisation du rendu de la PROGRESSION.
	 * @return	void
	 */
	this.move		= function(id_from, id_to, bRender) {
		// Fonctionnalité réalisée si l'élément passé en 1er argument est un objet
		if (typeof(id_from) == 'object') {
			$oHelper		= id_from.helper;
			var id_from		= $oHelper.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas
			if (typeof(id_from) == 'undefined') {
				id_from		= $oHelper.getUniqueId();
			}
			
			// Récupération de la durée de la tâche en cours de déplacement
			var duration	= $oHelper.find("input[name^=task_duration]").val();
		}
		
		// Fonctionnalité réalisée si l'élément passé en 2nd argument est un objet
		if (typeof(id_to) == 'object') {
			$oItem			= id_to;
			var id_to		= $oItem.id;
			
			// Fonctionnalité réalisée si l'identifiant n'existe pas
			if (typeof(id_to) == 'undefined') {
				id_to		= $oItem.getUniqueId();
			}
		}
		
		var $item = this.get(id_from);
		// Fonctionnalité réalisée si l'élément existe bien dans l'instance
		if (typeof($item) != 'undefined' && typeof(duration) == 'undefined') {
			// Récupère la valeur dans l'instance
			var duration	= $item.duration;
		}
		
		try {
			// Ajout d'une tâche à la collection
			this.add(id_to, duration, false);
			
			// Suppresson de l'ancienne tâche
			this.remove(id_from, false);
			
			// Actualisation de l'affichage
			if (typeof(bRender) == 'boolean' && bRender) {
				this.render();
			}
		} catch (exception) {
			// Arrêt du traîtement
			return false;
		}
	};

	/**
	 * @brief	Affichage du contenu de l'instance sous forme de chaîne de caractères.
	 * 
	 * @return	string
	 */
	this.toString	= function() {
		// Initialisation du message avec l'identifiant du PLANNING
		var message	= "PLANNING['" + this.id + "']: ";
		
		// Parcours de chaque entrée du PLANNING
		var count	= 0;
		var content	= "";
		for (var id in this.item) {
			// Ajout du contenu de la tâche au message
			content	+= "\n\t" + id + " : " + this.item[id].duration;
			count++;
		}
		
		// Concaténation du nombre d'éléments avec le contenu
		message += "array(" + count + ")" + content;
		
		// Renvoi du contenu du message de DEBUG
		return message;
	};

	/**
	 * @brief	Affichage du contenu de l'instance sur le PLANNING.
	 * 
	 * @return	string
	 */
	this.render		= function() {
		// Initialisation de la liste des conflits
		var $aConflictItem	= new Array();
		
		// Suppression de tous l'indicateur de survol par défaut pour tout le PLANNING
		$("dl[class*=diary].hover",		"section#" + this.id).removeClass("hover");
		$("dd[id^=planning-].error",	"section#" + this.id).removeClass("error");
		$("dd[id^=planning-].set",		"section#" + this.id).removeClass("set");

		// Parcours de l'ensemble de la collection
		for (var uniqueId in this.item) {
			// Indicateur sur l'ensemble de la période
			var $aItem		= uniqueId.split("-");

			// Coloration des cellules voisines sur toute la période de la tâche
			for (var i = 0 ; i < parseInt(this.item[uniqueId].duration) ; i++) {
				// Période de la tâche
				var $periode = i > 0 ? 1 : 0;
				// Récupération de l'heure à partir des éléments contenus dans ID d'origine
				$aItem[4]	= parseInt($aItem[4]) + $periode;
				
				// Fonctionnalité réalisée si un conflit est détecté
				if (typeof($aConflictItem[$aItem.join("-")]) != 'undefined') {
					// Ajout d'un indicateur de conflit
					$("#" + $aItem.join("-"), "section#" + this.id).addClass("error");
				} else {
					// Ajout d'un indicateur d'occupation
					$("#" + $aItem.join("-"), "section#" + this.id).addClass("set");
				}

				// Ajout de l'identifiant de la tâche dans la CELLULE
				$("#" + $aItem.join("-"), "section#" + this.id).addClass(uniqueId);
				$aConflictItem[$aItem.join("-")] = uniqueId;
			}
			
			// Affichage de la tâche
			this.item[uniqueId].render();
		}

		// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
		this.debug(this.toString());
	};
};
