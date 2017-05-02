/**
 * JavaScript relatif à la classe GalleryHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

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
}
/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
function initGallery() {
	// Déclaration des éléments SOURCE / CIBLE
    var $gallery	= $("#gallery");	// Bibliothèque
    var $panel		= $("#panel");		// Zone d'importation de la bibliothèque (dans le formulaire)

    // Initialisation du déplacement de la SOURCE
    $("li", $gallery).draggable({
        cancel:				"a.ui-icon",
        revert:				"false",
        containment:		"document",
        helper:				"clone",
        refreshPositions:	true,
        zIndex:				5000,
        drag:				function(event, ui) {
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
        }
    });

    // Initialisation de la CIBLE
    $panel.droppable({
        accept:				"#gallery > li",
        activeClass:		"ui-state-highlight",
        drop:				function(event, ui) {
            addItem(ui.draggable);
        }
    });

    // Initialisation de la SOURCE, permet de retourner un élément dans la bibliothèque
    $gallery.droppable({
        accept:				"#panel li",
        activeClass:		"custom-state-active",
        drop:				function(event, ui) {
            removeItem(ui.draggable);
        }
    });

    // Bouton d'ajout de l'élément à la liste
    var $recycle_icon	= "<a href=\"#\" title=\"Retirer cet élément\" class=\"ui-icon ui-icon-trash\">Retirer</a>";
    function addItem($content) {
        // Activation de l'alerte d'enregistrement
        MODIFICATION = true;

        // Ajoute l'élément sélectionné
        $content.fadeOut(function() {
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
        MODIFICATION = true;

        // Supprime l'élément sélectionné
        $content.fadeOut(function() {
            $content.find("a.ui-icon-plus").each(function() { $(this).remove(); });
            $content.find("a.ui-icon-trash").each(function() { $(this).remove(); });
            $content.append($add_icon).appendTo($gallery).fadeIn();
        });
    }
    // Actions réalisée lors du clic sur l'icône ZOOM
    $("ul.gallery > li").click(function (event) {
        var $content	= $(this);
        var $target		= $(event.target);

        // Fonctionnalité réalisée selon la classe du ANCHOR
        if ($target.is("a.ui-icon-plus")) {
            // Ajout du contenu
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
}
/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {
	initGallery();

    // Clic sur le bouton [Annuler]
    $("button#reset-gallery").click(function() {
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
                    $(this).val("");
                    break;
            }
        });
    });
});
