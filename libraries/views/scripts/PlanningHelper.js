/**
 * JavaScript relatif à la classe PlanningHelper.
 *
 * Les premiers éléments d'initialisation du JavaScript sont chargés par PHP dans PlanningHelper.
 *
 * @li	Les CELLULES du PLANNING sont identifiées par un nom unique de type `planning-Y-m-d-h`
 * @code
 * 	<dl class="diary">
 * 		<dt>
 * 			<h3>{JOUR DE LA SEMAINE}</h3>
 * 		</dt>
 * 		<dd id="planning-AAAA-MM-JJ-H" class="planning">
 * 			<h4>{HEURE}</h4>
 * 		</dd>
 * 	</dl>
 * @endcode
 *
 * @li	Variables injectées par PHP via la classe PlanningHelper
 * @code
 * 	globale	PLANNING_DEBUG		: boolean
 * 	globale	PLANNING_MD5		: array
 *  globale PLANNING_CELL_WIDTH	: array
 * @endcode
 *
 * @li	Variables déclarées dans `main.js`
 * @code
 * 	globale	MODIFICATION		: boolean
 * @endcode
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

var PLANNING_CURRENT_MD5		= "";

// Variable d'instance de PLANNING_HELPER
if (typeof(PLANNING_HELPER) == 'undefined') {
	// Constantes du MODAL
	var MODAL_MD5_PREFIXE		= "search-content-";

	// Constantes de gestion du PLANNING
	var PLANNING_MD5_PREFIXE	= "planning-item-";
	var PLANNING_ITEM_REGEXP	= /^planning-[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}-[0-9]+$/;
	var PLANNING_ITEM_FACTEUR	= 0.65;
	var PLANNING_ITEM_MARGIN	= 10;
	var PLANNING_ITEM_ATTRIBUTE	= ["tache_annee", "tache_mois", "tache_jour", "tache_heure"];
	var PLANNING_ITEM_IGNORE	= ["tache_participant", "tache_duree"];
	var PLANNING_MOUSEHOVER		= false;
	var PLANNING_ERROR			= false;
	var PLANNING_HELPER			= new Array();
}

//=================================================================================================

(function(factory){
	if (typeof define === 'function' && define.amd){
		define(['jquery'], factory);
	} else {
		factory(window.jQuery);
	}
}(function($) {

	// Récupération de l'identification de la tâche à partir de ses attributs
	$.fn.getUniqueId = function() {
		// Déclaration de la collection sous forme de tableau
		var uniqueId = [];

		// Initialisation de la collection avec le premier terme
		uniqueId.push("planning");

		// Parcours chaque attribut de la tâche
		for (var $i in PLANNING_ITEM_ATTRIBUTE) {
			// Ajout de la valeur à la collection
			uniqueId.push($(this).find("input[name^=" + PLANNING_ITEM_ATTRIBUTE[$i] + "]").val());
		}

		// Formatage sous forme de chaîne de caractères `planning-{tache_annee}-{tache_mois}-{tache_jour}-{tache_heure}`
		return uniqueId.join("-");
	};

	// Survol d'une tâche sur la cellule : coloration selon l'occupation de la PROGRESSION
	$.fn.dragging = function(event, ui) {
		// Protection contre la propagation intempestive
		event.stopPropagation();

		// Message de debuggage
		var debug_info;

		// Récupération des informations de l'élément en cours de déplacement
		var uniqueId	= ui.helper.getUniqueId();

		// Récupération de l'identifiant du PLANNING
		var MD5			= $(this).parents("section").attr("id");

		// Suppression de tous l'indicateur de survol par défaut pour tout le PLANNING
		$("dl[class*=diary].hover", "section#" + MD5).removeClass("hover");
		// Ajout de l'indicateur de survol sur le PLANNING courant
		$(this).parent("dl").addClass("hover");

		// Suppression de tous l'indicateur de survol par défaut pour toutes les CELLULES
		$("dd[class*=planning].hover", "section#" + MD5).removeClass("hover");
		// Ajout de l'indicateur de survol sur la CELLULE courante
		$(this).addClass("hover");

		// Mise en évidence de la cellule survolée
		if ($(this).hasClass("ui-state-highlight")) {
			// Mise en place de l'opacité de l'élément en cours de déplacement
			ui.helper.css({opacity: 1});

			// Indicateur sur l'ensemble de la période
			var $aItem = $(this).attr("id").split("-");
			var $duree = ui.helper.find("input[name^=tache_duree]").val();

			// Suppression de la coloration erronée de la cellule par défaut
			$("dd[class*=planning].conflict", "section#" + MD5).removeClass("conflict");

			// Coloration des cellules voisines sur toute la période de la tâche
			var $aConflictItems	= [];
			for (var i = 0 ; i < parseInt($duree) ; i++) {
				// Période de l'élément
				var $periode = i > 0 ? 1 : 0;
				// Récupération de l'heure à partir des éléments contenus dans ID d'origine
				$aItem[4] = parseInt($aItem[4]) + $periode;

				// Mise en évidence de la cellule voisine
				$("#" + $aItem.join("-"), "section#" + MD5).addClass("hover");

				// Fonctionnalité réalisée si la cellule existe
				if (typeof($("dd#" + $aItem.join("-"), "section#" + MD5).attr("class")) != 'undefined') {
					// Extraction des attributs de la classe
					var $aClass	= $("dd#" + $aItem.join("-"), "section#" + MD5).attr("class").split(" ");

					// Recherche dans les attributs de la classe si la cellule est déjà affectée
					for (var k in $aClass) {
						// Fonctionnalité réalisée si un attribut correspont à une autre tâche
						if (PLANNING_ITEM_REGEXP.exec($aClass[k]) && $aClass[k] != uniqueId) {
							// La cellule est déjà occupée !
							$aConflictItems.push($aClass[k]);
							// Ajout de l'indicateur de conflit
							$("#" + $aItem.join("-"), "section#" + MD5).addClass("conflict");
						}
					}
				}
			}

			if ($aConflictItems.length > 0) {
				// Mise à jour de la variable de détection des conflits
				PLANNING_ERROR = true;

				// Ajout de l'indicateur de conflit
				ui.helper.addClass("error");
				debug_info = " : ERROR";
			} else {
				// Mise à jour de la variable de détection des conflits
				PLANNING_ERROR = false;

				// Suppression de l'indicateur de conflit
				ui.helper.removeClass("error");
				debug_info = " : OK";

				// Suppression de la coloration de la cellule
				$("dd[class*=planning].conflict", "section#" + MD5).each(function() {
					$(this).removeClass("conflict");
				});
			}
		}

		// Affichage en MODE_DEBUG
		$("#var-debug").html($(this).attr("id") + debug_info);
	};

	// Initialisation des fonctionnalité du PLANNING
	$.fn.planning = function(config, callback) {
		// Fonctionnalité réalisée pour chaque PLANNING
		$(this).each(function() {
			// Récupération de l'identifiant du PLANNING
			var MD5		= $(this).attr("id");

			var parent	= $(this).parents(".accordion");
			if (typeof(parent.accordion) == 'function' ) {
				// Fonctionnalité réalisée dans le cas d'une inclusion dans un plugin jQuery.accordion();
				parent.accordion({
					activate:	function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Fonctionnalité réalisée si le PANEL activé contient le PLANNING
						if (typeof(ui.newPanel.find("section.planningHelper").html()) != 'undefined') {
							// Fonctionnalité réalisée pour chaque session de PLANNING
							for (var session in PLANNING) {
								// Actualisation de la largeur des cellules
								updateCellWidth(true, session);
							}
						}
					}
				});
			} else if (parent = $(this).parents(".tabs") && typeof(parent.tabs) == 'function' ) {
				// Fonctionnalité réalisée dans le cas d'une inclusion dans un plugin jQuery.tabs();
				parent.tabs({
					activate:	function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Fonctionnalité réalisée si le PANEL activé contient le PLANNING
						if (typeof(ui.newPanel.find("section.planningHelper").html()) != 'undefined') {
							// Fonctionnalité réalisée pour chaque session de PLANNING
							for (var session in PLANNING) {
								// Actualisation de la largeur des cellules
								updateCellWidth(true, session);
							}
						}
					}
				});
			}

			// Déplacement d'un élément entre les progressions du même PLANNING
			$(this).droppable({
				accept:				"#" + PLANNING_MD5_PREFIXE + MD5 + " li.item",
				// Fonctionnalité réalisée lorsque la souris survole le PLANNING tout entier
				over:				function(event, ui) {
					// Protection contre la propagation intempestive
					event.stopPropagation();

					PLANNING_MOUSEHOVER	= true;
				},
				// Fonctionnalité réalisée lorsque la souris ne survole aucun PLANNING
				out:				function(event, ui) {
					// Protection contre la propagation intempestive
					event.stopPropagation();

					PLANNING_MOUSEHOVER	= false;
					// Fonctionnalité réalisée lorsque la souris sort du PLANNING
					if (typeof(PLANNING[MD5]) != 'undefined') {
						PLANNING[MD5].render();
					}
				},
				// Fonctionnalité réalisée lorsque la fonctionnalité est terminée
				deactivate:			function(event, ui) {
					// Protection contre la propagation intempestive
					event.stopPropagation();

					// Fonctionnalité réalisée lorsque la souris sort du PLANNING
					if (typeof(PLANNING[MD5]) != 'undefined') {
						PLANNING[MD5].render();
					}
				}
			});

			// Initialisation du comportement de chaque journée du PLANNING
			$("dl.diary dd.planning", this).each(function() {
				// Initialisation du déplacement des éléments sur la grille
				$("li.item", this).draggable({
					handle:				"a.draggable-item",						// Icône en forme de PUNAISE qui permet de déplacer la cellule
					revert:				"false",
					containment:		"document",
					helper:				"clone",
					refreshPositions:	true,
					zIndex:				5000,
					drag:				function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Récupération du parent
						var $parent = $(this).parent();
						var $position = "absolute";

						// Modification de l'icône PUNAISE en cours de déplacement
						ui.helper.find("a.draggable-item").removeClass("ui-icon-pin-s").addClass("ui-icon-pin-w");

						// Modification du style pour extraire le clône de sont conteneur
						ui.helper.css({position: $position, top: ui.position.top+"px", left: ui.position.left+"px", zIndex: 10000});

						// Affichage en MODE_DEBUG de la cellule CIBLE
						$("#var-debug").html($(this).attr("id"));
					}
				});

				// Déplacement d'un élément entre les progressions compatibles (MD5)
				$(this).droppable({
					accept:				"li.item",
					activeClass:		"ui-state-highlight",
					activate:			function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Initialisation des variables de gestion des conflits
						PLANNING_ERROR		= false;
						PLANNING_MOUSEHOVER	= false;
					},
					// Fonctionnalité réalisée lorsque la souris survole la cellule
					over:				function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Récupération de l'identifiant de la SECTION parent (MODAL pour l'ajout / PLANNING pour le déplacement)
						var sectionItem		= ui.helper.parents("section").attr("id").replace(MODAL_MD5_PREFIXE, "");

						// Fonctionnalité réalisée si la CELLULE est compatible avec la SOURCE
						if (sectionItem == MD5) {
							// Prévisualisation de la future position de la tâche sur le PLANNING
							$(this).dragging(event, ui);
						} else {
							// Message informant de l'incompatibilité de la CELLULE
							$("#var-debug").text('Emplacement non compatible !');
							return false;
						}
					},
					// Fonctionnalité réalisée lors déplacement d'un élément dans la cellule
					drop:				function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Récupération de l'identifiant de la SECTION parent (MODAL pour l'ajout / PLANNING pour le déplacement)
						var sectionItem		= ui.helper.parents("section").attr("id").replace(MODAL_MD5_PREFIXE, "");

						// Fonctionnalité réalisée si la CELLULE est compatible avec la SOURCE
						if (sectionItem == MD5 && ui.draggable.getUniqueId() != this.id) {
							// Ajout du clône dans la nouvelle cellule dans le PLANNING
							addItem(MD5, ui, this);
						} else if (sectionItem == MD5) {
							// Message informant que la CELLULE de destination est identique à la source
							$("#var-debug").text('Emplacement identique !');
							return false;
						} else {
							// Message informant de l'incompatibilité de la CELLULE
							$("#var-debug").text('Emplacement non compatible !');
							return false;
						}
					},
					// Fonctionnalité réalisée lors déplacement d'un élément dans la cellule
					deactivate:			function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Récupération de l'identifiant de la SECTION parent (MODAL pour l'ajout / PLANNING pour le déplacement)
						var sectionItem		= ui.helper.parents("section").attr("id").replace(MODAL_MD5_PREFIXE, "");

						// Fonctionnalité réalisée afin de limiter la fonctionnalité au PLANNING en cours
						if (sectionItem == MD5) {
							// Nettoyage du survol
							clearPlanning(sectionItem);
						}
					}
				});

				// Action au survol de l'élément du CALENDAR
				$(this).mouseenter(function(event) {
					if ($(this).parents("section").is(".calendar")) {
						var $aItem = $(this).attr("id").split("-");
						var $heure = $aItem[4];

						// Ajout d'un indicateur de survol
						$(this).parents("dl").addClass("hover");

						// Affichage de l'heure positionnée en entête de tableau
						if (! $("h4", "section#" + MD5 + " dd[id=planning-0-" + $heure + "]").hasClass("visible")) {
							$("h4", "section#" + MD5 + " dd[id=planning-0-" + $heure + "]").addClass("visible");
						}
					}
				}).mouseleave(function(event) {
					if ($(this).parents("section").is(".calendar")) {
						var $aItem = $(this).attr("id").split("-");
						var $heure = $aItem[4];

						// Suppression de l'indicateur de survol
						$(this).parents("dl").removeClass("hover");

						// Masquage de l'heure positionnée en entête de tableau
						$("h4", "section#" + MD5 + " dd[id^=planning-0-" + $heure + "]").removeClass("visible");
					}
				});

				// Action sur le clic droit sur une progression
				$(this).bind("contextmenu", function(event) {
					// Fermeture de tout MODAL de planning existant
					$(".modal").not("#modal-item-" + MD5).each(function() {
						$(this).dialog("close");
					});

					// Protection contre le syndrome du cliqueur intempestif
					event.stopPropagation();
					// Affichage de la bibliothèque
					$("#modal-item-" + MD5).dialog("open");
					// Désactivation des scrollBars sur la bibliothèque
					$("#modal-item-" + MD5).css({overflow: "hidden", overflowY: "auto"});

					// Actualisation de la largeur des cellules lors du changement de la durée
					$("#id_item_duree", "#search-planning-" + MD5).change(function(event) {
						// Protection de la durée minimale
						if ($(this).val() <= 0) {
							// Par défaut la durée est de 1 heure
							$(this).val(1);
						}

						// Actualisation des éléments du planning
						setPlanningItemAttribute('tache_duree', $(this).val(), MD5);

						// Actualisation de la largeur des cellules
						updateCellWidth(false, MD5);
					});

					// Annulation de l'événement
					event.preventDefault();
				});
			});
		});
	};

	// Modification de la durée de la tâche avec dedimensionnement de la CELLULE
	$.fn.updateDuree = function(newDuree, MD5) {
		// Récupération de l'identifiant unique de la tâche
		var origineUniqueId	= this.getUniqueId();
		// Récupération de la valeur de l'attribut de durée d'origine
		var origineDuree	= this.find("input[name^=tache_duree]").val();

		// Fonctionnalité réalisée si la valeur de la durée de la tâche est différente
		if (newDuree != undefined && newDuree != origineDuree) {
			// Récupération de l'identifiant du PLANNING
			MD5 = $(this).parents("section").attr("id").replace(MODAL_MD5_PREFIXE, "");

			// Suppression de l'ancien indicateur d'occupation
			$("dd[class*=planning]." + origineUniqueId, "section#" + MD5).removeClass(origineUniqueId);

			// Modification du champ caché de l'attribut de durée de la tâche
			$(this).find("input[name^=tache_duree]").val(newDuree);

			// Récupération de la nouvelle identité
			var uniqueId	= $(this).getUniqueId();

			// Indicateur sur l'ensemble de la période
			var $aItem		= uniqueId.split("-");

			// Coloration des cellules voisines sur toute la période de la tâche
			var $aConflicts	= [];
			for (var i = 0 ; i < parseInt(newDuree) ; i++) {
				// Période de l'élément
				var $periode = i > 0 ? 1 : 0;
				// Récupération de l'heure à partir des éléments contenus dans ID d'origine
				$aItem[4] = parseInt($aItem[4]) + $periode;

				// Mise en évidence de la cellule voisine
				if (typeof($("dd#" + $aItem.join("-"), "section#" + MD5).attr("class")) != 'undefined') {
					// Extraction des attributs de la classe
					var $aClass	= $("dd#" + $aItem.join("-"), "section#" + MD5).attr("class").split(" ");

					// Recherche dans les attributs de la classe si la cellule est déjà affectée
					for (var x in $aClass) {
						// Fonctionnalité réalisée si un attribut correspont à une autre tâche
						if (PLANNING_ITEM_REGEXP.exec($aClass[x]) && $aClass[x] != uniqueId) {
							// Récupération du nom de cellule en conflit
							$aConflicts.push($aClass[x]);
						}
					}

				} else {
					// Ajout d'un indicateur d'occupation
					$("#" + $aItem.join("-"), "section#" + MD5).addClass("set");
				}

				// Ajout de l'identifiant de la tâche dans la CELLULE
				$("#" + $aItem.join("-"), "section#" + MD5).addClass(uniqueId);
			}
		} else {
			// Écrasement de la valeur par la durée d'origine
			newDuree	= origineDuree;
		}

		// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
		if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
			console.debug("$.fn.updateDuree(" + newDuree + ", '" + MD5 + "')");
		}

		// Fonctionnalité réalisée si la largeur de cellule par défaut est connue
		if (typeof(newDuree) == 'undefined' || typeof(PLANNING_CELL_WIDTH[MD5]) == 'undefined' || parseFloat(PLANNING_CELL_WIDTH[MD5]) == 0) {
			// Affichage d'un message de DEBUGGAGE en mode `PLANNING_DEBUG`
			if (typeof(PLANNING[MD5]) != 'undefined') {
				PLANNING[MD5].debug("\tERROR !");
			}
			// Stop !!!
			return false;
		}

		// Mise à jour de l'instance du PLANNING ***************************************************
		if (typeof(PLANNING[MD5]) != 'undefined') {
			PLANNING[MD5].update(this, newDuree, true);
		}

		// Fonctionnalité réalisée si la largeur de la cellule ne dépend par de la durée
		if ($(this).parents("section").hasClass("calendar") || $(this).parents("section").hasClass("static")) {
			newDuree	= 1;
		}

		// Redimentionnement de la CELLULE
		var newWidth	= PLANNING_CELL_WIDTH[MD5];
        var facteur		= (newDuree - PLANNING_ITEM_MARGIN) + (PLANNING_ITEM_FACTEUR * newDuree);
		newWidth		= newWidth * newDuree + facteur;

		// Affectation de la nouvelle valeur à la CELLULE
		$(this).css({width: newWidth + "px"});
	};

	// Affichage d'un MODAL contenant la tâche sélectionnée
	$.fn.viewItem = function() {
		// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
		if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
			console.debug("$.fn.viewItem()");
		}

		// Fonctionnalité réalisée si le compteur n'existe pas encore
		if (typeof(compteurModal) == 'undefined') {
			// Initialisation du compteur
			var compteurModal = 0;
		} else {
			// Incrémentation du compteur
			compteurModal = compteurModal + 1;
		}

		// Protection contre un affichage infini
		if (compteurModal >= 1) {
			// Affichage en MODE_DEBUG
			$("#var-debug").html("ATTENTION : Affichage du modal INFINIT !!!");
			// STOP !
			return false;
		}

		// Suppression éventuelle de toutes les MODALES précédentes
		$("#panning-viewer").each(function() {
            $(this).dialog("close");
            $(this).remove();
		});

		// Récupération du contenu de la tâche
		var $article = $(this).find("article").html();

		// Fonctionnalité réalisée si le contenu est valide
		if (typeof($article) != 'undefined' && $article.length > 0) {
			// Construction d'un MODAL avec le contenu
			var $modal = $("<article id=\"panning-viewer\" class=\"modal center blue hidden\">" + $article + "</article>").appendTo("dialog");

			// Variables temporaires de manipulation des éléments
			var $item		= $(this);
			var MD5			= $item.parents("section").attr("id");
			var $dureeItem	= $item.find("input[name^=tache_duree]");
			var dureeValue	= $dureeItem.val();

			// Champs DUREE
			$modal.append("<hr /><label for=\"id_modal_duree\" class=\"strong title\">Durée :</label><input type=\"number\" id=\"id_modal_duree\" name=\"modal_duree\" value=\"" + dureeValue + "\"/>");

			// Fonctionnalité réalisée lors du changement de la durée
			$("#id_modal_duree").change(function() {
				// Protection de la durée minimale
				if ($(this).val() <= 0) {
					// Par défaut la durée est de 1 heure
					$(this).val(1);
				}

				// Actualisation du champ caché du MODAL
				$modal.find("input[name^=tache_duree]").val($(this).val());
			});

			// Affichage du MODAL après un délais
			setTimeout(function() {
				// Activation du MODAL
				$modal.dialog({
					closeText:	"Fermer",
					title:		"Édition d'une tâche",
					width:		300,
					maxHeight:	document.body.clientHeight - 100,
					modal:		true,
					create:		function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();

						// Affichage en MODE_DEBUG
						$("#var-debug").html("Création du MODAL #id_modal_duree");
					},
					buttons: {
						"Annuler": function() {
							// Fermeture du MODAL
							$(this).dialog("close");
						},
						"Valider": function(event) {
							// Protection contre la propagation intempestive
							event.stopPropagation();

							// Mise à jour de la durée de l'élément
							$item.updateDuree($("#id_modal_duree").val(), MD5);

							// Mise à jour de la durée de la tâche
							if (typeof(PLANNING[MD5]) != 'undefined') {
								PLANNING[MD5].update($item, $("#id_modal_duree").val(), true);
							}

							// Fermeture du MODAL
							$modal.dialog("close");
							// Suppression du modal
							$modal.remove();
						}
					},
					close:		function(event, ui) {
						// Protection contre la propagation intempestive
						event.stopPropagation();
						// Suppression du modal
						$modal.remove();
						// Réinitialisation du compteur
						compteurModal = 0;
					}
				});
			}, 10);
		}
	};

	// Suppression de la tâche dans la PROGRESSION
	$.fn.removeItem = function() {
		// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
		if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
			console.debug("$.fn.removeItem()");
		}

		// Récupération de l'identifiant du PLANNING
		var MD5 = $(this).parents("section").attr("id");

		// Activation de l'alerte d'enregistrement
		MODIFICATION = true;

		// Récupération de l'identifiant unique selon les attributs de la tâche
		var uniqueId = $(this).getUniqueId();

		// Réinitialisation de l'affichage de la cellule
		$("dd[class*=" + uniqueId + "]", "section#" + MD5)
			.removeClass("set")
			.removeClass(uniqueId);

		// Supprime l'élément sélectionné
		$(this).fadeOut(function() {
			$(this).find("a.ui-icon-trash").each(function() { $(this).remove(); });
		});

		// Mise à jour de l'instance du PLANNING ***************************************************
		if (typeof(PLANNING[MD5]) != 'undefined') {
			PLANNING[MD5].remove($(this), true);
		}
	};
}));

/**
 * @brief	Ajout d'un élément de plannification dans la progression
 *
 * @li	Parcours chaque tâche afin d'actualiser les dimensions selon la durée et le rapport en pixels du volume horaire.
 *
 * @param	string		MD5				: Identifiant du PLANNING.
 * @param	string		$source			: Source d'origine contenu.
 * @param	string		$destination	: Sélecteur servant de support à l'élément.
 */
function addItem(MD5, $source, $destination) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("addItem('" + MD5 + "', '" + $source + "', '" + $destination + ")");
	}

	// Annulation si une erreur est détectée
	if (PLANNING_ERROR) {
		// STOP !
		return false;
	}

	// Récupération du contenu en cours de déplacement
	$content = $source.draggable;

	// Bouton d'ajout de l'élément à la liste
	var $recycle_icon	= "<a href=\"#\" title=\"Retirer cet élément\" class=\"ui-icon ui-icon-trash\">&nbsp;</a>";

	// Bouton de visualisation de l'élément
	var $zoomin_icon	= "<a href=\"#\" title=\"Éditer cet élément\" class=\"ui-icon ui-icon-zoomin\">&nbsp;</a>";

	// Suppression d'une éventuelle mise en évidence d'un conflit précédent
	$content.removeClass("error");

	// Activation de l'alerte d'enregistrement
	MODIFICATION = true;

	// Récupération des attributs de destination contenus dans l'ID du type `planning-annee-mois-jour-heure`
	var attributes = $($destination).attr("id").split('-');

	// Mise à jour des attributs de la CELLULE
	$($content).find("input[name^=tache_annee]").val(attributes[1]);	// Valeur de l'année
	$($content).find("input[name^=tache_mois]").val(attributes[2]);		// Valeur du mois
	$($content).find("input[name^=tache_jour]").val(attributes[3]);		// Valeur du jours
	$($content).find("input[name^=tache_heure]").val(attributes[4]);	// Valeur de l'heure
	$($content).find("input[name^=tache_minute]").val(attributes[5]);	// Valeur de la minute

	// Ajoute l'élément sélectionné
	$content.fadeOut(function() {
		var $list = $("ul", $destination).length ? $("ul", $destination) : $("<ul class=\"planning-item ui-helper-reset\"/>").appendTo($destination);
		// Suppression des icônes redondantes à chaque insertion dans la même cellule
		$content.find("a.ui-icon-trash, a.ui-icon-zoomin").each(function() { $(this).remove(); });

		// Ajout des icônes
		$("section.item-bottom", $content).append($zoomin_icon);
		$("section.item-bottom", $content).append($recycle_icon);

		// Ajout du contenu SOURCE au PLANNING
		$content.appendTo($list).fadeIn(function() {
			$content
		});

		// Récupération des informations de l'élément en cours de déplacement
		var uniqueIdSource	= $source.helper.getUniqueId();
		var uniqueIdContent	= $content.getUniqueId();

		// Suppression de l'indicateur d'occupation
		$("dd[class*=planning]", "section#" + MD5).each(function() {
			// Suppression de l'indicateur d'occupation
			$(this).removeClass(uniqueIdSource);
		});

		// Ajout d'un indicateur de présence dans la cellule
		$($destination).addClass(uniqueIdContent);

		// Indicateur sur l'ensemble de la période
		var $aItem = $($destination).attr("id").split("-");
		var $duree = $("input[name^=tache_duree]", $content).val();

		// Coloration des cellules voisines sur toute la période de la tâche
		for (var i = 1 ; i < parseInt($duree) ; i++) {
			// Récupération de l'heure à partir des éléments contenus dans ID
			$aItem[4] = parseInt($aItem[4]) + 1;
			// Mise en évidence de la cellule voisine
			$("#" + $aItem.join("-"), "section#" + MD5).addClass(uniqueIdContent);
		}
	});

	// Affichage en MODE_DEBUG
	$("#var-debug").html($($destination).attr("id"));

	// Mise à jour de l'instance du PLANNING *******************************
	if (typeof(PLANNING[MD5]) != 'undefined') {
		PLANNING[MD5].move($source, $destination, true);
	}
};

/**
 * @brief	Mise à jour de la largeur d'une cellule
 *
 * @li	Parcours chaque tâche afin d'actualiser les dimensions selon la durée par rapport aux pixels affecté par unité de volume horaire.
 *
 * @param	bool		bResize			: TRUE si les tâches doivent être redimensionnées également dans le MODAL.
 * @param	string		MD5				: Identifiant du PLANNING au format MD5.
 */
function updateCellWidth(bResize, MD5) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("updateCellWidth(" + bResize + ", '" + MD5 + "')");
	}

	var LISTE_MD5 = new Array();
	if (typeof(MD5) == 'undefined') {
		$("section.planningHelper").each(function() {
			// Récupération de l'identifiant
			var MD5 = $(this).attr("id");

			// Ajout de l'identifiant à la collection
			LISTE_MD5.push(MD5);
		});
	} else {
		// Ajout de l'identifiant à la collection
		LISTE_MD5.push(MD5);
	}

	// Traitement de chaque élément de la collection
	for (var $id in LISTE_MD5) {
		// Identifiant
		var MD5 = LISTE_MD5[$id];

		// Récupération de la durée du formulaire MODAL
		var duree	= $("#id_item_duree", "#" + MODAL_MD5_PREFIXE + MD5).val();
		if (duree <= 0) {
			return false;
		}

		// Récupération de la largeur des cellules dans le navigateur CLIENT
		PLANNING_CELL_WIDTH[MD5] = $("dd.planning", "section#" + MD5).innerWidth();

		// Fonctionnalité réalisée si le paramètre d'entrée active le redimentionnement des tâches du PLANNING
		if (typeof(bResize) == 'boolean' && bResize == true) {
			// Redimentionnement des éléments déjà affichés dans le PLANNING
			$("dd[class*=planning]", "section#" + MD5).each(function() {
				// Fonctionnalité réalisée pour chaque cellule
				$("li.item", this).each(function() {
					// Récupération de la durée contenue dans les attributs de la tâche
					duree	= $(this).find("input[name^=tache_duree]").val();
					// Modification de la durée de la tâche
					$(this).updateDuree(duree, MD5);
				});
			});
		} else {
			// Actualisation des éléments déjà affichés dans le MODAL
			$("li.item", "section#" + MODAL_MD5_PREFIXE + MD5).each(function() {
				// Mise à jour de la taille des tâches récupérées dans le moteur de recherche du MODAL
				$(this).updateDuree(duree, MD5);
			});
		}
	}
};

/**
 * @brief	Vérifie si la première entrée est supérieure à la seconde
 *
 * Compare les identifiants de cellule de PLANNING afin de déterminer si le premier paramètre est supérieur au second.
 * @li	Découpe chaque attributs et les compares un à un.
 *
 * @param	string		$a				: identifiant du PLANNING de la forme `planning-Y-m-d-h`.
 * @param	string		$b				: identifiant du PLANNING de la forme `planning-Y-m-d-h`.
 * @return	boolean
 */
function isSupPlanningItem($a, $b) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("isSupPlanningItem('" + $a + ", '" + $b + "')");
	}

	// Récupération des paramètres de la CLASSE du type `planning-AAAA-MM-JJ-HH`
	var $aItemA = $a.split("-");
	var $aItemB = $b.split("-");
	$bSup = true;
	// Parcours de l'ensemble des éléments de la classe
	for (var i = 1 ; i < $aItemA.length ; i++) {
		if (parseInt($aItemB[i]) > parseInt($aItemA[i])) {
			$bSup = false;
		}
	}
	return $bSup;
};

/**
 * Mise à jour d'un attribut des éléments du planning
 *
 * @param	string		name			: Nom de l'attriput.
 * @param	mixed		value			: Valeur de l'attribut.
 * @param	string		MD5				: Identifiant du PLANNING au format MD5.
 */
function setPlanningItemAttribute(name, value, MD5) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("setPlanningItemAttribute('" + name + "', " + value + ", '" + MD5 + "')");
	}

	// Parcours chaque entrée caché
	$("li.item", "#" + PLANNING_MD5_PREFIXE + MD5).each(function() {
		// Change le contenu de la valeur input
		$("input[name^=" + name + "]", this).val(value);
	});
};

/**
 * @brief	Réinitialisation de la plannification
 *
 * @li	Fonctionnalité appelée lors de la fin de l'ajout ou du déplacement d'une entrée sur le PLANNING.
 *
 * @param	string		MD5				: identifiant du PLANNING au format MD5.
 */
function clearPlanning(MD5) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("clearPlanning('" + MD5 + "')");
	}

	// Suppression de l'indicateur de survol
	$("dl.hover",		"section#" + MD5).removeClass("hover");
	$("dd.hover",		"section#" + MD5).removeClass("hover");
	$("dd.conflict",	"section#" + MD5).removeClass("conflict");
};

/**
 * @brief	Construction du PLANNING.
 *
 * @param	string		MD5		: identifiant du PLANNING au format MD5.
 * @return	object
 */
function setPlanning(MD5) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("setPlanning('" + MD5 + "')");
	}

	// Construction d'un nouveau planning
	PLANNING[MD5] = new Planning(MD5);

	// Parcours des tâches déjà présentes afin de les intégrer à l'instance en cours
	$("li.item", "section#" + MD5).each(function() {
		// Ajout de la tâche à la collection
		PLANNING[MD5].add($(this));
	});

	// Actualisation des cellules du PLANNING
	PLANNING[MD5].render();
}

/**
 * @brief	Récupération du PLANNING par son identifiant.
 *
 * @param	string		MD5		: identifiant du PLANNING au format MD5.
 * @return	object
 */
function getPlanning(MD5) {
	// Fonctionnalité réalisée si le planning n'existe pas
	if (typeof(PLANNING[MD5]) == 'undefined') {
		// Construction d'un nouveau PLANNING
		setPlanning(MD5);
	};

	// Renvoi du PLANNING
	return PLANNING[MD5];
}

/**
 * @brief	Initialisation de la gestion de la plannification depuis la SOURCE
 *
 * La SOURCE correspond au contenu édité par le formulaire MODAL.
 *
 * @li	Fonctionnalité appelée lors de l'actualisation du moteur de recherche dans le MODAL.
 * @li	Déclaration et initialisation du déplacement des éléments de la SOURCE.
 * @li	Adaptation de des cellules selon la résolution de l'affichage CLIENT.
 *
 * @param	string		MD5				: identifiant du PLANNING au format MD5.
 */
function initPlanning(MD5) {
	// Fonctionnalité réalisée si le MODE_DEBUG est actif sur `PLANNING_HELPER`
	if (typeof(PLANNING_DEBUG) == 'boolean' && PLANNING_DEBUG) {
		console.debug("initPlanning('" + MD5 + "')");
	}

	// Création de l'objet s'il n'existe pas déjà
	var $oPlanning	= getPlanning(MD5);

	// Déclaration des éléments SOURCE / CIBLE
	var $item		= $("#" + PLANNING_MD5_PREFIXE + MD5);							// Liste des éléments de la progression sous forme de cellule

	// Initialisation du déplacement de la SOURCE : création d'un clône
	$("li.item", $item).draggable({
		handle:				"a.draggable-item",										// Icône en forme de PUNAISE qui permet de déplacer la cellule
		revert:				"true",													// Possibilité de revenir en arrière
		containment:		"document",												// Conteneur de la cellule clônée
		helper:				"clone",												// Mode de déplacement, ici en clône
		refreshPositions:	true,													// Actualisation des positions (X, Y)
		zIndex:				5000,													// Option de l'attribut CSS `z-index`
		drag:				function(event, ui) {									// Fonctionnalités réalisées lors du déplacement du clône
			// Protection contre la propagation intempestive
			event.stopPropagation();

			// Récupération de l'identifiant de la SECTION parent (MODAL pour l'ajout / PLANNING pour le déplacement)
			var sectionItem		= ui.helper.parents("section").attr("id").replace(MODAL_MD5_PREFIXE, "");

			// Fonctionnalité réalisée si la CELLULE n'est pas compatible avec la SOURCE
			if (sectionItem != MD5) {
				// Message informant de l'incompatibilité de la CELLULE
				$("#var-debug").text('Emplacement non compatible !');
				return false;
			}

			// Récupération du parent
			var $parent		= $(this).parent();
			var $position	= "absolute";

			// Fonctionnalité réalisée depuis la SOURCE : correction de la position initiale de la cellule
			if ($parent.attr("id") == (PLANNING_MD5_PREFIXE + MD5)) {
				// Actualisation de la postition du clône par rapport au MODAL
				ui.position.top += $parent.offset().top - $("a.draggable-item").height();
				ui.position.left += $parent.offset().left - $("a.draggable-item").innerWidth();

				// Modification du style pour extraire le clône de sont conteneur
				$position = "fixed";
			}

			// Modification de l'icône PUNAISE en cours de déplacement
			ui.helper.find("a.draggable-item").removeClass("ui-icon-pin-s").addClass("ui-icon-pin-w");

			// Modification du style pour extraire le clône de sont conteneur
			ui.helper.css({position: $position, top: ui.position.top+"px", left: ui.position.left+"px", opacity: 0.3, cursor: "move", zIndex: 10000});
		}
	});

	// Mise en évidence de la semaine au survol du PLANNING
	$("td[class*=day-]",	"section#" + MD5).mouseenter(function(event) {
		// Protection contre la propagation intempestive
		event.stopPropagation();

		var role = $(this).attr("role");
		$("th." + role,		"section#" + MD5).addClass("hover");
	}).mouseleave(function(event) {
		// Protection contre la propagation intempestive
		event.stopPropagation();

		var role = $(this).attr("role");
		$("th." + role,		"section#" + MD5).removeClass("hover");
	});

	// Actualisation des cellules du PLANNING
	updateCellWidth(false, MD5);
};

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {
	// Initialisation de la fonctionnalité de planification
	$("section.planningHelper").planning();

	// Actions réalisée lors du clic sur l'icône ZOOM
	$(document).on("click", "a.ui-icon-zoomin", function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Récupération du conteneur parent
		var $target = $(this).parents("li.item");

		// Affichage du contenu
		$target.viewItem();

		// Protection contre la validation du formulaire
		return false;
	});

	// Actions réalisée lors du clic sur l'icône POUBELLE
	$(document).on("click", "a.ui-icon-trash", function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Récupération du conteneur parent
		var $target = $(this).parents("li.item");

		// Suppression de l'élément
		$target.removeItem();

		// Protection contre la validation du formulaire
		return false;
	});

	// Action réalisée au double-clic sur une tâche
	$(document).on("dblclick", "li.item", function(event) {
		// Édition de la tâche sélectionnée
		$(this).viewItem();
	});

	// Fonctionnalité réalisée lors du redimentionnement de la fenêtre
	$(window).bind("resize", function(event) {
		// Déclaration de l'élément visible uniquement en MODE_DEBUG
		var output = $("#var-debug");
		// Message informant du redimentionnement
		$(output).text('En cours de redimentionnement...');

		// Attente de la fin du redimentionnement
		waitForFinalEvent(function() {
			// Message informant de la fin du redimentionnement
			$(output).text('Redimentionnement terminé !');
			// Actualisation de la largeur des cellules dans le PLANNING et dans le MODAL
			updateCellWidth(true);
		}, 500);
	});

	// Actualisation de la largeur des cellules au chargement
	updateCellWidth(true);
});
