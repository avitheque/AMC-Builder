/**
 * JavaScript de debuggage de l'application.
 *
 * Paramètres d'initialisation du panneau de debuggage au démarrage
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
var DEBUG_PANEL_STATUS		= "closed";
var DEBUG_PANEL_HEIGHT		= 0;
var DEBUG_PANEL_WIDTH		= 0;
var DEBUG_PANEL_MAX			= 300;
var DEBUG_PANEL_OVERFLOW_Y	= "auto";

// Initialisation du panneau de debuggage
function initDebug() {
	// Suppression de la classe HIDDEN
	$("#article-debug").removeClass("hidden");

	// Récupération de la taille du message de débuggage
	DEBUG_PANEL_HEIGHT	= $("#ul-debug", "#article-debug").height() + 15;
	DEBUG_PANEL_WIDTH	= $("body").width();

	// Fonctionnalité réalisée si le panneau doit être fermé au démarrage
	if (DEBUG_PANEL_STATUS == "closed") {
		// Minimisation de l'interface de débuggage
		$("#article-debug").addClass(DEBUG_PANEL_STATUS);
		$("#article-debug").css({width: 0});
		$("#ul-debug", "#article-debug").css({display: "none"});

		// Fonctionnalité réalisée si le panneau est trop grand
		DEBUG_PANEL_OVERFLOW_Y	= "hidden";
		if (DEBUG_PANEL_HEIGHT > DEBUG_PANEL_MAX) {
			// Adaptation de la taille maximale
			DEBUG_PANEL_HEIGHT		= DEBUG_PANEL_MAX;
			DEBUG_PANEL_OVERFLOW_Y	= "auto";
		}

		$("#ul-debug", "#article-debug").css({height: DEBUG_PANEL_HEIGHT + "px", overflowY: DEBUG_PANEL_OVERFLOW_Y});
	}
}
//Animation d'ouverture
function openPanel() {
	// Changement du status
	DEBUG_PANEL_STATUS = "opened";

	// Affichage du message de débuggage
	$("#ul-debug").css({ display: "block" });

	// Masquage du bouton d'ouverture
	$("span.open", "#article-debug").css({ display: "none" });

	// Animation de l'affichage du panneau DEBUG
	$("#article-debug").animate(
		{ width: DEBUG_PANEL_WIDTH },
		"slow",
		function() {
			// Animation de l'affichage du panneau DEBUG
			$(this).animate(
				{ height: DEBUG_PANEL_HEIGHT },
				"slow",
				function() {
					$(this).removeClass("closed");
				}
			);
		}
	);
}
// Animation de fermeture
function closePanel() {
	// Changement du status
	DEBUG_PANEL_STATUS = "closed";
	$("#article-debug").addClass("closed");

	// Animation de l'affichage du panneau DEBUG
	$("#article-debug").animate(
		{ height: 10 },
		"slow",
		function() {
			// Animation du masquage du panneau DEBUG
			$(this).animate(
				{ width: 0 },
				"slow",
				function() {
					// Masquage du message de débuggage
					$("#ul-debug", this).css({ display: "none" });

					// Affichage du bouton d'ouverture
					$("span.open", this).css({ display: "block" });
				}
			);
		}
	);
}
// Fonctionnalité jQuery réalisée à la fin du chargement de la page dans le navigateur client
$(document).ready(function() {
	// Initialisation du panneau de debuggage
	initDebug();

	// Fonctionnalité réalisée au redimentionnement de la fenêtre
	$(window).resize(function(){
		DEBUG_PANEL_WIDTH	= $("body").width();
		closePanel();
	});

	// Affichage du panneau de DEBUG
	$("span.icon", "#article-debug").click(function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Annulation de l'événement
		event.preventDefault();

		// Fonctionnalité réalisée selont le status du panneau
		if (DEBUG_PANEL_STATUS == "opened") {
			// Ouverture du panneau
			closePanel();
		} else {
			// Fermeture du panneau
			openPanel();
		}
	});
});
