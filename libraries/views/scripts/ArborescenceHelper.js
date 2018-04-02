/**
 * JavaScript relatif à la classe ArborescenceHelper.
 * User: durandcedric
 * Date: 13/06/17
 * Time: 06:34
 *
 * Permet d'exploiter le référentiel de la langue chargé par le plugin DataTables (jQuery).
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

(function(factory){
	if (typeof define === 'function' && define.amd){
		define(['jquery'], factory);
	} else {
		factory(window.jQuery);
	}
}(function($) {

	// Mise à jour des intervalles
	var ARBORESCENCE									= [];
	var ARBORESCENCE_LEFT								= 0;
	var ARBORESCENCE_RIGHT								= 0;
	var ARBORESCENCE_MIN								= 0;
	var ARBORESCENCE_MAX								= [];
	var ARBORESCENCE_LEVEL								= 0;
	var ARBORESCENCE_NODE								= 0;
	var ARBORESCENCE_DEBUG								= "";
	$.fn.updateIntervals = function(init) {
		if (typeof(init) == 'boolean' && init == true) {
			if (typeof(ARBORESCENCE_DEBUG) == 'boolean' && ARBORESCENCE_DEBUG) {
				var start								= new Date();
				console.debug("ARBORESCENCE_DEBUG : " + start.toString());
				console.debug("-------------------------------------------------------------");
			}
			ARBORESCENCE								= [];
			ARBORESCENCE_LEFT							= 0;
			ARBORESCENCE_RIGHT							= 0;
			ARBORESCENCE_MIN							= 0;
			ARBORESCENCE_MAX							= [];
			ARBORESCENCE_LEVEL							= 0;
			ARBORESCENCE_NODE							= 0;
			ARBORESCENCE_DEBUG							= "";
		}
		$(this).find("ul[class*=arborescence]").each(function() {
			// Initialisation du noeud
			if (typeof(ARBORESCENCE_MAX[ARBORESCENCE_NODE]) == 'undefined') {
				ARBORESCENCE_MAX[ARBORESCENCE_NODE]					= ARBORESCENCE_RIGHT;
			}
			ARBORESCENCE_LEFT					= ARBORESCENCE_LEFT + 1;
			$(this).find("li[class*=branche]").children("span.titre").each(function() {
				var item								= $(this).parent();
				var item_id								= item.find('input[name^=item_id]').val();
				var item_label							= item.find('input[name^=item_label]').val();

				if (typeof(ARBORESCENCE[item_id]) == 'undefined') {
					var UL_COUNT = $(this).parent().find("ul[class*=arborescence]").length - 1;
					var UL_CHILDREN_COUNT = item.children("ul.arborescence").length - 1;
					var IL_COUNT = $(this).parent().find("ul[class*=arborescence] li[class*=branche]").length - 1;
					var IL_CHILDREN_COUNT = item.children("ul.arborescence").find("li[class*=branche]").length - 1;
					var DIFF = $(this).parents("ul[class*=arborescence]").length - ARBORESCENCE_LEVEL;
					ARBORESCENCE_LEVEL = $(this).parents("ul[class*=arborescence]").length;

					// Fonctionnalité réalisée lorsqu'un changement d'arborescence est détecté
					if (DIFF < 0 && ARBORESCENCE_LEVEL == 1) {
						// Récupération de la BORNE MAX du noeud précédent
						ARBORESCENCE_LEFT				= ARBORESCENCE_MAX[ARBORESCENCE_LEVEL] + 1;
						// Réinitialisation de l'ensemble des BORNE MAX de l'arborescence
						ARBORESCENCE_MAX							= [];
						ARBORESCENCE_MAX[ARBORESCENCE_LEVEL]		= ARBORESCENCE_LEFT + 1;
						ARBORESCENCE_DEBUG				= "(retour au niveau principal :";
					} else if (DIFF < 0) {
						// Récupération de la BORNE MAX du noeud précédent
						ARBORESCENCE_LEFT				= ARBORESCENCE_MAX[ARBORESCENCE_LEVEL + 1] + 1;
						ARBORESCENCE_DEBUG				= "(déplacement au niveau inférieur :";
					} else if (DIFF == 0) {
						// Récupération de la BORNE DROITE précédente
						ARBORESCENCE_LEFT				= ARBORESCENCE_RIGHT + 1;
						ARBORESCENCE_DEBUG				= "(pas de changement de niveau :";
					} else if (DIFF > 0) {
						// Initialisation du nouveau noeud
						ARBORESCENCE_NODE				= ARBORESCENCE_NODE + 1;
						ARBORESCENCE_MAX[ARBORESCENCE_LEVEL]		= ARBORESCENCE_RIGHT;
						ARBORESCENCE_DEBUG				= "(déplacement au niveau supérieur :";
					}

					// Calcul de la borne DROITE
					ARBORESCENCE_RIGHT = ARBORESCENCE_LEFT + 1;
					if (UL_COUNT > 0 || UL_CHILDREN_COUNT > 0 || IL_COUNT > 0 || IL_CHILDREN_COUNT > 0) {
						ARBORESCENCE_RIGHT				= ARBORESCENCE_RIGHT + UL_COUNT * 2;
						ARBORESCENCE_RIGHT	 			= ARBORESCENCE_RIGHT + parseInt((UL_COUNT / IL_COUNT) - 0.5);
						ARBORESCENCE_RIGHT				= ARBORESCENCE_RIGHT + UL_CHILDREN_COUNT * 2;
						ARBORESCENCE_RIGHT				= ARBORESCENCE_RIGHT + parseInt((UL_CHILDREN_COUNT / IL_CHILDREN_COUNT) - 0.5);
					}

					// Enregistrement de la BORNE MIN du prochain noeud
					ARBORESCENCE_MIN					= ARBORESCENCE_LEFT + 1;

					// Enregistrement de la BORNE MAX du noeud
					if (ARBORESCENCE_RIGHT > ARBORESCENCE_MAX[ARBORESCENCE_LEVEL]) {
						ARBORESCENCE_MAX[ARBORESCENCE_LEVEL]		= ARBORESCENCE_RIGHT;
						ARBORESCENCE_MAX[ARBORESCENCE_LEVEL + 1]	= ARBORESCENCE_RIGHT + 2;
					}

					ARBORESCENCE[item_id]				= {};
					ARBORESCENCE[item_id].label			= item_label;
					ARBORESCENCE[item_id].left			= ARBORESCENCE_LEFT;
					ARBORESCENCE[item_id].right			= ARBORESCENCE_RIGHT;
					ARBORESCENCE[item_id].changed		= false;

					// Fonctionnalité réalisée si la borne GAUCHE a été modifiée
					if (item.find("input[name^=item_left]").val() != ARBORESCENCE_LEFT) {
						// Mise à jour des valeurs de l'élément HTML
						item.find("input[name^=item_left]").val(ARBORESCENCE_LEFT);
						// Mise à jour de l'indicateur de changement
						item.find("input[name^=item_changed]").val(parseInt(item.find("input[name^=item_changed]").val()) + 1).trigger('change');
						ARBORESCENCE[item_id].changed	= true;
					}

					// Fonctionnalité réalisée si la borne DROITE a été modifiée
					if (item.find("input[name^=item_right]").val() != ARBORESCENCE_RIGHT) {
						// Mise à jour des valeurs de l'élément HTML
						item.find("input[name^=item_right]").val(ARBORESCENCE_RIGHT);
						// Mise à jour de l'indicateur de changement
						item.find("input[name^=item_changed]").val(parseInt(item.find("input[name^=item_changed]").val()) + 1).trigger('change');
						ARBORESCENCE[item_id].changed	= true;
					}

					// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `ARBORESCENCE_HELPER`
					//if (typeof(ARBORESCENCE_DEBUG) == 'boolean' && ARBORESCENCE_DEBUG) {
						console.debug(DIFF, ARBORESCENCE_DEBUG, ARBORESCENCE_MAX[ARBORESCENCE_LEVEL], "MAX)");
						console.debug("[" + ARBORESCENCE_LEVEL + "] : UL = " + UL_COUNT + " / LI = " + IL_COUNT);
						console.debug("label \"" + item_label + "\" -> [" + ARBORESCENCE_LEFT + " ; " + ARBORESCENCE_RIGHT + "]");
						console.debug("-------------------------------------------------------------");
					//}

					if ($(this).find("li[class*=branche]").children("ul.arborescence")) {
						ARBORESCENCE_LEFT				= ARBORESCENCE_LEFT + 1;
						$(this).updateIntervals();
					}
				}
			});
		});
		return ARBORESCENCE;
	};

}));

//=================================================================================================

// Initialisation de la variable de déplacement
var ARBORESCENCE_DROPPABLE_VALID						= true;

// Initialisation de la variable de clônage
var ARBORESCENCE_HELPER_ITEM							= null;

// Réinitialisation des indicateurs de survol dans l'arborescence
function resetHoverItems() {
	// Suppression des indicateurs de survol de la branche
	$("ul.arborescence.hover").removeClass("hover");

	// Suppression de l'indicateurs de survol d'un noeud
	$("li.branche.hover").removeClass("hover");
};

// Initialisation du TRI
function initSortable() {
	// Fonctionnalité réalisée lors d'un événement clavier
	$(document).keydown(function(event) {
		// Fonctionnalité réalisée sur la touche [Echap]
		if (typeof(event.keyCode) != 'undefined' && event.keyCode == 27) {
			// Désactivation du déplacement
			ARBORESCENCE_DROPPABLE_VALID				= false;
		}
	});

	// Fonctionnalité réalisée pour chaque arborescence
	$("ul.arborescence").each(function() {
		// Activation du plugin jQuery `sortable` sur les blocs d'arborescence
		$(this).sortable({
			placeholder:		"sortable-placeholder",
			tolerance:			"pointer",
			cursor:				"move",
			revert:				true,
			scroll:				false,
			zIndex:				9999,
			start:				function(event, ui) {
				// Initialisation de la variable de déplacement
				ARBORESCENCE_DROPPABLE_VALID			= true;
				// Initialisation de la variable de clônage
				ARBORESCENCE_HELPER_ITEM				= null;
			},
			over:				function(event, ui) {
				// Protection contre la propagation intempestive
				event.stopPropagation();
				// Récupération du clône
				ARBORESCENCE_HELPER_ITEM				= ui.helper;

				// Suppression de tous les indicateurs de survol
				$(this).addClass("hover").parent("li").addClass("hover");

				// Récupération des intervalles du conteneur
				var borne_gauche						= typeof($(this).parent("li").find("input[name^=item_left]").val()) != 'undefined' ? $(this).parent("li").find("input[name^=item_left]").val() : $(this).parent("input[name^=item_left]").val();
				var borne_droite						= typeof($(this).parent("li").find("input[name^=item_right]").val()) != 'undefined' ? $(this).parent("li").find("input[name^=item_right]").val() : $(this).parent("input[name^=item_right]").val();

				// Récupération des intervalles de l'élément à déplacer
				var helper_gauche						= typeof($(ui.helper).find("input[name^=item_left]").val()) != 'undefined' ? $(ui.helper).find("input[name^=item_left]").val() : $(ui.helper).parent("li").find("input[name^=item_left]").val();
				var helper_droite						= typeof($(ui.helper).find("input[name^=item_right]").val()) != 'undefined' ? $(ui.helper).find("input[name^=item_right]").val() : $(ui.helper).parent("li").find("input[name^=item_right]").val();

				// Cas où le déplacement est valide
				var cas_A								= parseInt(helper_gauche) > parseInt(borne_droite);
				var cas_B								= parseInt(helper_gauche) > parseInt(borne_gauche) && parseInt(helper_droite) < parseInt(borne_droite);
				var cas_C								= parseInt(helper_droite) < parseInt(borne_gauche);
				var cas_D								= typeof(borne_gauche) == 'undefined' && typeof(borne_droite) == 'undefined';

				// Fonctionnalité réalisée si le déplacement est valide
				if (cas_A || cas_B || cas_C || cas_D) {
					// Validation du déplacement
					ARBORESCENCE_DROPPABLE_VALID		= true;
				} else {
					// Désactivation du déplacement
					ARBORESCENCE_DROPPABLE_VALID		= false;
				}

				// Fonctionnalité réalisée lors d'un événement clavier
				$(document).keydown(function(event) {
					// Protection contre la propagation intempestive
					event.stopPropagation();

					// Fonctionnalité réalisée sur la touche [Echap]
					if (typeof(event.keyCode) != 'undefined' && event.keyCode == 27) {
						// Désactivation du déplacement
						ARBORESCENCE_DROPPABLE_VALID	= false;

						// Arrêt du déplacement de l'élément clôné
						ARBORESCENCE_HELPER_ITEM.stop();

						// Mascage de l'élément clôné
						ARBORESCENCE_HELPER_ITEM.hide();

						// Réinitialisation des indicateurs de survol
						resetHoverItems();

						// Annulation de l'événement
						event.preventDefault();
						return false;
					}
				});
			},
			out:				function(event, ui) {
				// Protection contre la propagation intempestive
				event.stopPropagation();

				// Suppression de tous les indicateurs de survol
				$(this).removeClass("hover").parent("li").removeClass("hover");
			},
			receive: function(event, ui) {
				// Protection contre la propagation intempestive
				event.stopPropagation();

				// Fonctionnalité réalisée si le déplacement est valide
				if (ARBORESCENCE_DROPPABLE_VALID) {
					// Récupération du conteneur parent
					var parentContainer					= $(ui.item).parent("ul");

					// Suppression de l'élément EMPTY dans le container de destination
					$(this).children("li.empty").remove();

					// Suppression de l'élément original : le clône est à la bonne place
					ui.item.remove();

					// Fonctionnalité réalisée si le container parent est vide
					if (parentContainer.children("li").length == 0) {
						// Ajout de l'élément EMPTY dans le container d'origine
						parentContainer.append("<li class=\"branche empty\"><span>&nbsp;</span></li>");
					}
				} else {
					// Suppression du clône : l'élément original ne peut pas être déplacé
					ARBORESCENCE_HELPER_ITEM.remove();
				}
			},
			update:				function(event, ui) {
				// Protection contre la propagation intempestive
				event.stopPropagation();

				// Initialisation suite au changement
				initSortable();

				//	Mise à jour des intervalles
				$(this).parents("section.racine").updateIntervals(true);
			},
			stop:				function(event, ui) {
				// Protection contre la propagation intempestive
				event.stopPropagation();

				// Réinitialisation des indicateurs de survol
				resetHoverItems();
			}
		});

		// Initialisation du déplacement de chaque branche de l'arborescence
		$("li.branche", this).draggable({
			connectToSortable:	"ul.arborescence",
			containment:		"document",
			helper:				"clone",
			revert:				true,
			refreshPositions:	true,
			zIndex:				5000
		});
	});
}

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {

	// Initialisation du TRI
	initSortable();

});
