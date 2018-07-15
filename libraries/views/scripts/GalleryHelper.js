/**
 * JavaScript relatif à la classe GalleryHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 * 
 * @li		Manipulation de la VARIABLE GLOBALE JavaScript `FW_FORM_UPDATE`
 * @see		ViewRender::setFormUpdateStatus(boolean);
 * @see		/public/scripts/main.js;
 * @code
 * 		var	FW_FORM_UPDATE	= false;
 * @endcode
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

var GALLERY_MARGIN_ITEM = 10;
var GALLERY_DRAGGABLE	= true;

// Mise à jour du MODAL
function updateGalleryHeight() {
	// Récupération des éléments du MODAL
	var titleItem		= $("article#modal-gallery").parent().find("div.ui-dialog-titlebar");
	var searchItem		= $("ul#gallery");
	var formItem		= $("form#search-bibliotheque");
	var documentItem	= $(document);

	// Adaptation de la hauteur de la MODALE de selon le résultat
	var newHeight		= searchItem.innerHeight() + formItem.innerHeight() + titleItem.innerHeight() * 1.5 + GALLERY_MARGIN_ITEM;

	// Fonctionnalité réalisée si la nouvelle hauteur est trop grande par rapport à la zone d'affichage
	if (documentItem.innerHeight() <= newHeight) {
		// Récupération de la hauteur disponible
		newHeight		= documentItem.innerHeight() - formItem.innerHeight() + titleItem.innerHeight() * 1.5 + GALLERY_MARGIN_ITEM;

		// Réduction de la zone de résultat en conséquence
		searchItem.css({height: parseInt(newHeight - formItem.innerHeight() - titleItem.innerHeight() * 1.5) + "px"});
	}

	// Mise à jour de la nouvelle hauteur du MODAL
	$("article#modal-gallery").dialog("option", "height", newHeight);
};

// Affichage d'un modal contenant la question
function viewItem($link) {
	var href	= $link.attr("href");
	var id		= $link.attr("id");
	var title	= $link.text();

	// Suppression éventuelle de toutes les MODALES précédentes
	$("#gallery-viewer").each(function() {
		$(this).dialog("close");
		$(this).remove();
	});

	/** @todo APPEL DU MODAL S'IL EXISTE DÉJÀ... */

	// Construction du MODAL
	var $modal = $("<article id=\"gallery-viewer\" class=\"modal blue hidden\" href=\"" + href + "\"></article>").appendTo("dialog");
	setTimeout(function() {
		// Récupération du contenu via l'URL
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"HTML",
			url:		href,
			beforeSend:	function(any) {
				// Indicateur de chargement
				$modal.html("<span class=\"loading\">Chargement en cours...</span>");
			},
			success:	function(response) {
				$modal.html(response);
			}
		});

		// Activation du MODAL
		$modal.dialog({
			closeText:	"Fermer",
			title:		title,
			width:		800,
			maxHeight:	document.body.clientHeight - 100,
			modal:		true
		});
	}, 1);
};

// Ajoute un élément à la liste des exclus
function pushToExclude($element) {
	// Récupération de l'identifiants de l'élément à ajouter à la bibliothèque
	var $id		= $("input#idBibliotheque", $element).val();

	// Récupération de la liste des identifiants exclus
	var $exlude	= $("input[name=bibliotheque_exclude]").val();

	// Fonctionnalité réalisée si des éléments sont déjà présents
	if ($exlude.length > 0) {
		// Transformation du contenu en tableau
		$exlude	= $exlude.split(",");
	} else {
		// Initialisation du tableau
		$exlude	= new Array();
	}

	// Fonctionnalité réalisée si l'identifiant n'est pas dans la collection
	if ($exlude.indexOf($id) < 0) {
		// Ajout de l'identifiant de l'élément à la collection
		$exlude.push($id);

		// Mise à jour du champ caché de la collection des identifiants exclus
		$("input[name=bibliotheque_exclude]").val($exlude.join(","));
	}
};

// Retire un élément de la liste des exclus
function deleteFromExclude($element) {
	// Récupération de l'identifiants de l'élément à ajouter à la bibliothèque
	var $id		= $("input#idBibliotheque", $element).val();

	// Récupération de la liste des identifiants exclus
	var $exlude	= $("input[name=bibliotheque_exclude]").val();

	// Transformation du contenu en tableau
	$exlude	= $exlude.split(",");

	// Fonctionnalité réalisée si l'identifiant est dans la collection
	if ($exlude.indexOf($id) >=0) {
		// Suppression de l'identifiant de la collection
		$exlude.splice($exlude.indexOf($id), 1);

		// Mise à jour du champ des collection
		$("input[name=bibliotheque_exclude]").val($exlude.join(","));
	}
};

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
function initGallery() {
	// Déclaration des éléments SOURCE / CIBLE
	var $gallery	= $("ul#gallery");		// Bibliothèque
	var $panel		= $("div#panel");		// Zone d'importation de la bibliothèque (dans le formulaire)

	// Initialisation du déplacement de la SOURCE
	$("li", $gallery).draggable({
		cancel:				"a.ui-icon",
		revert:				"false",
		containment:		"document",
		helper:				"clone",
		refreshPositions:	true,
		zIndex:				5000,
		start:				function(event, ui) {
			GALLERY_DRAGGABLE = true;
		},
		drag:				function(event, ui) {
			// Protection contre la propagation intempestive
			event.stopPropagation();

			// Récupération du parent
			var $parent = $(this).parent();
			var $position = "absolute";

			// Fonctionnalité réalisée depuis la SOURCE
			if ($parent.attr("id") == "gallery") {
				// Actualisation de la postition du clône par rapport au MODAL
				ui.position.top += $parent.offset().top;
				ui.position.left += $parent.offset().left;

				// Modification du style pour extraire le clône de sont conteneur
				$position = "fixed";
			}

			// Modification du style pour extraire le clône de sont conteneur
			ui.helper.css({position: $position, top: ui.position.top+"px", left: ui.position.left+"px", zIndex: 10000});

			// Fonctionnalité réalisée lors d'un événement clavier
			$(document).keydown(function(event) {
				// Fonctionnalité réalisée sur la touche [Echap]
				if (typeof(event.keyCode) != 'undefined' && event.keyCode == 27) {
					// Protection contre la propagation intempestive
					event.stopPropagation();

					// Désactivation du déplacement
					GALLERY_DRAGGABLE = false;

					// Arrêt du déplacement de l'élément clôné
					ui.helper.stop();

					// Mascage de l'élément clôné
					ui.helper.hide();

					// Annulation de l'événement
					event.preventDefault();
					return false;
				}
			});
		}
	});

	// Initialisation de la CIBLE
	$panel.droppable({
		accept:				"ul#gallery > li",
		activeClass:		"ui-state-highlight",
		drop:				function(event, ui) {
			// Protection contre la propagation intempestive
			event.stopPropagation();

			// Fonctionnalité réalisée si le déplacement est autorisé
			if (GALLERY_DRAGGABLE) {
				// Ajout de l'élément
				addItem(ui.draggable);
			}
		}
	});

	// Initialisation de la SOURCE, permet de retourner un élément dans la bibliothèque
	$gallery.droppable({
		accept:				"div#panel li",
		activeClass:		"custom-state-active",
		drop:				function(event, ui) {
			// Protection contre la propagation intempestive
			event.stopPropagation();

			// Fonctionnalité réalisée si le déplacement est autorisé
			if (GALLERY_DRAGGABLE) {
				// Suppression de l'élément
				removeItem(ui.draggable);
			}
		}
	});

	// Bouton d'ajout de l'élément à la liste
	var $recycle_icon	= "<a href=\"#\" title=\"Retirer cet élément\" class=\"ui-icon ui-icon-trash\">Retirer</a>";
	function addItem($content) {
		// Activation de l'alerte d'enregistrement
		FW_FORM_UPDATE = true;

		// Ajout de l'élément à la liste des exclus
		pushToExclude($content);

		// Ajoute l'élément sélectionné
		$content.fadeOut(function() {
			// Création d'un nouveau container s'il n'existe pas encore
			var $list = $("ul", $panel).length ?
				$("ul", $panel) :
				$("<ul class=\'gallery ui-helper-reset\'/>").appendTo($panel);

			$content.find("a.ui-icon-plus").each(function() { $(this).remove(); });
			$content.find("a.ui-icon-trash").each(function() { $(this).remove(); });
			$content.append($recycle_icon).appendTo($list).fadeIn(function() {
				$content
			});
		});
	}

	// Bouton de suppression de l'élément de la liste
	var $add_icon = "<a href=\'#' title=\'Ajouter cet élément\' class=\'ui-icon ui-icon-plus\'>Ajouter</a>";
	function removeItem($content) {
		// Activation de l'alerte d'enregistrement
		FW_FORM_UPDATE = true;

		// Suppression de l'élément à la liste des exclus
		deleteFromExclude($content);

		// Supprime l'élément sélectionné
		$content.fadeOut(function() {
			$content.find("a.ui-icon-plus").each(function() { $(this).remove(); });
			$content.find("a.ui-icon-trash").each(function() { $(this).remove(); });
			$content.append($add_icon).appendTo($gallery).fadeIn();
		});
	}

	// Actions réalisée lors du clic sur l'icône ZOOM
	$("ul.gallery > li").click(function (event) {
		// Protection contre la propagation intempestive
		event.stopPropagation();

		var $content	= $(this);
		var $target		= $(event.target);

		// Fonctionnalité réalisée selon la classe du ANCHOR
		if ($target.is("a.ui-icon-plus")) {
			// Ajout de l'élément
			addItem($content);
		} else if ($target.is("a.ui-icon-zoomin")) {
			// Affichage du contenu
			viewItem($target);
		} else if ($target.is("a.ui-icon-trash")) {
			// Suppression de l'élément
			removeItem($content);
		}

		// Désactivation du renvoi
		return false;
	});
};

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {
	initGallery();

	// Clic sur le bouton [Annuler]
	$("button#reset-gallery").click(function(event) {
		// Protection contre la propagation intempestive
		event.stopPropagation();

		// Réinitialise tous les champs SELECT de la bibliothèque
		$("select", "form#search-bibliotheque").each(function() {
			$(this).find("option:selected").removeAttr("selected");
		});

		// Réinitialise tous les champs TEXTAREA de la bibliothèque
		$("textarea", "form#search-bibliotheque").each(function() {
			$(this).text("");
		});

		// Réinitialise tous les champs INPUT de la bibliothèque
		$("input", "form#search-bibliotheque").each(function() {
			// Fonctionnalité réalisée selon le TYPE
			switch ($(this).attr("type")) {
				case "radio":
					// Champ RADIO
					$(this).removeAttr("checked");
					break;

				case "checkbox":
					// Champ CHECKBOX
					if ($(this).is("[type=checkbox]") && $(this).is(":checked")) {
						// Cas particulier du template de l'application
						$(this).click();
					}
					break;

				default:
					// Champ TEXT
					$(this).not("[name=bibliotheque_exclude]").val("");
					break;
			}
		});
	});
});
