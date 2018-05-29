/**
 * JavaScript principal de l'application.
 *
 * Les variables CONTROLLER et ACTION sont initialisées dans le contrôleur abstrait de l'application.
 * @see		{ROOT_PATH}/libraries/controllers/AbscractApplicationController.php
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

/**
 * Initialisation de l'indicateur de modification du formulaire.
 * 
 * Fonctionnalité réalisée si la VARIABLE GLOBALE initialisée dans le SKEL n'est pas initialisée.
 * @see		ViewRender::setFormUpdateStatus(boolean);
 */
if (typeof(FW_FORM_UPDATE) == 'undefined') {
	// Initialisation de la VARIABLE GLOBALE d'indicateur de modification du formulaire
	var FW_FORM_UPDATE = false;
}

/**
 * Plugin jQuery permettant de déplacer le scroll sur un élément.
 * @code
 *	// Déplacement sur le fieldset ayant pour attribut id="idFieldset"
 * 	$("main").scrollTo("#idFieldset");
 * @endcode
 */
// Fonctionnalité permettant de déplacer le SCROLL automatiquement du CLIENT sur un élément HTML
$.fn.scrollTo = function(target, options, callback){
	if (typeof options == 'function' && arguments.length == 2){
		callback			= options;
		options				= target;
	}
	var settings			= $.extend({
		'scrollTarget':		target,
		'offsetTop':		100,
		'duration':			1000,
		'easing':			'swing'
	}, options);
	return this.each(function() {
		var scrollPane		= $(this);
		var scrollTarget	= (typeof settings.scrollTarget == "number")	? settings.scrollTarget	: $(settings.scrollTarget);
		var scrollY			= (typeof scrollTarget == "number")				? scrollTarget			: scrollTarget.offset().top + scrollPane.scrollTop() - parseInt(settings.offsetTop);
		scrollPane.animate({ scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function() {
			if (typeof callback == 'function') {
				callback.call(this);
			}
		});
	});
};

// Fonctionnalité permettant de lancer une action à la fin du redimentionnement de la fenêtre CLIENT
var waitForFinalEvent = (function() {
	var timers = {};
	return function(callback, ms, uniqueId) {
		// Fonctionnalité réalisée si aucun uniqueId n'est présent
		if (!uniqueId) {
			// Évite d'appeler cette fonctionnalité deux fois sans un uniqueId !!!
			var now = new Date();
			uniqueId = now.toISOString();
		}
		// Fonctionnalité réalisée si un évènement existe déjà
		if (timers[uniqueId]) {
			// Suppression de l'évènement
			clearTimeout(timers[uniqueId]);
		}
		// Enregistrement d'un nouvel évènement
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();

// Initialisation du plugin DatePicker
function setDatePicker(selector) {
	// Le champ ne doit pas être en lecture seule
	$(selector).not("[readonly]").datepicker({
		'renderer':			$.ui.datepicker.defaultRenderer,
		'showOn':			"both",
		'buttonImage':		"/images/calendar.png",
		'buttonImageOnly':	true,
		'showWeek':			true,
		'changeMonth':		true,
		'changeYear':		true,
		'showButtonPanel':	true,
		'isRTL':			false,
		'onSelect':			function(dateText, inst) {
			// Remove input state when select
			$(this).removeClass("valid");
			$(this).removeClass("invalid");
		}
	});
};

// Initialisation du plugin TimePicker
function setTimePicker(selector) {
	// Le champ ne doit pas être en lecture seule
	$(selector).not("[readonly]").timepicker({
		'currentText':		"Actuelle",
		'closeText':		"OK",
		'amNames':			["AM", "A"],
		'pmNames':			["PM", "P"],
		'timeFormat':		"HH:mm",
		'timeSuffix':		"",
		'timeOnlyTitle':	"Saisissez l'heure",
		'timeText':			"",
		'hourText':			"Heure",
		'minuteText':		"Minute",
		'secondText':		"Second",
		'millisecText':		"Millisecond",
		'microsecText':		"Microsecond",
		'timezoneText':		"Time Zone",
		'isRTL':			false
	});
};

// Initialisation de la boîte de dialogue de confirmation
function setDialogConfirm(selector, button_or_url) {
	$(selector).dialog({
		autoOpen:	false,
		draggable:	false,
		resizable:	false,
		modal:		true,
		title:		"ATTENTION !",
		height:		260,
		width:		"auto",
		buttons:	{
			"OK": function () {
				var url		= "";
				var button	= "";
				// Fonctionnalité réalisée si l'élément passé en paramètre est un objet
				if (typeof(button_or_url) == "object") {
					// Récupération de la page actuelle
					url		= "/" + CONTROLLER + "/" + ACTION;
					// Récupération de la valeur du bouton
					button	= $(button_or_url).attr("value");
				} else {
					// Renvoi de la page
					url		= button_or_url;
				}

				// Activation de l'arrière-plan de protection contre les cliqueurs intempestifs durant le chargement
				$("div#stop-click").css({display: "block"});

				// Traitement différé afin que la boîte de dialogue soit visible
				setTimeout(function () {
					// Traitement AJAX
					$.ajax({
						async:		false,
						type:		"POST",
						dataType:	"HTML",
						url:		url,
						data:		{button: button, render: "body"},
						success:	function(response) {
							// Fermeture du MODAL
							$(selector).dialog('close');

							// Récupération de la réponse
							$("body").html(response);
						}
					});
				}, 500);
			},
			"Annuler": function () {
				$(this).dialog('close');
			}
		}
	});
};

/**
 * Méthode de comparaison entre deux tableaux
 * @param	array	a1
 * @param	array	a2
 * @returns {Boolean}
 */
function arrayCompare(a1, a2) {
	if (a1.length != a2.length) {
		return false;
	}
	var length = a2.length;
	for (var i = 0; i < length; i++) {
		if (a1[i] !== a2[i]) {
			return false;
		}
	}
	return true;
};

/**
 * Méthode de recherche d'une entrée dans un tableau
 * @param	string	needle
 * @param	array	haystack
 * @returns {Boolean}
 */
function inArray(needle, haystack) {
	var length = haystack.length;
	for (var i = 0; i < length; i++) {
		if (typeof haystack[i] == 'object') {
			if (arrayCompare(haystack[i], needle)) {
				return true;
			}
		} else if (haystack[i] == needle) {
			return true;
		}
	}
	return false;
};

/**
 * Méthode de récupération de la clé d'une entrée dans un tableau
 * @param	string	needle
 * @param	array	haystack
 * @returns {Mixed}
 */
function getArrayKey(needle, haystack) {
	var length = haystack.length;
	for (var i = 0; i < length; i++) {
		if (typeof haystack[i] == 'object') {
			if (arrayCompare(haystack[i], needle)) {
				return i;
			}
		} else if (haystack[i] == needle) {
			return i;
		}
	}
	return null;
};

/**
 * Méthode de protection du chargement de la page contre les cliqueurs intempestifs
 * @param	object	event
 * @returns void
 */
function waitingStatement(event) {
	// Protection contre le syndrome du cliqueur intempestif
	event.stopImmediatePropagation();

	// Fonctionnalité réalisée si aucune modification n'a été réalisée dans le formulaire
	if (!FW_FORM_UPDATE) {
		// Activation de l'arrière-plan de protection contre les cliqueurs intempestifs durant le chargement
		$("div#stop-click").css({display: "block"});

		// Désactivation décalée afin de permettre la saisie du formulaire si la page n'est pas rechargée
		setTimeout(function () {
			// Suppression la protection contre les clics intempestifs
			$("div#stop-click").css({display: "none"});
		}, 1000);
	}
};

/**
 * Méthode d'initialisation des TOOLTIPS personnalisés
 * @param	string	className
 * @param	mixed	selector
 * @param	string	option
 * @returns void
 */
function resetTooltip(className, selector, option) {
	// Fonctionnalité réalisée si le selecteur n'est pas initialisé
	if (typeof(selector) == 'undefined') {
		selector = "." + className;
	}

	// Fonctionnalité réalisée selon le nom de la classe
	switch (className) {
		case "tooltip":
			// Activation des TOOLTIPS statiques
			$(selector).tooltip({
				position:	{my: "top+20", at: "center"}
			});
			break;

        case "tooltip-right":
            // Activation des TOOLTIPS qui suivent la souris de l'utilisateur
            $(selector).tooltip({
                position:	{my: "left+30", at: "right center"}
            });
            break;

		case "tooltip-track":
			// Activation des TOOLTIPS qui suivent la souris de l'utilisateur
			$(selector).tooltip({
				track:		true,
				position:	{my: "left+30", at: "right center"}
			});
			break;

		default:
            // Activation du TOOLTIP avec les paramètres par défaut
            $(selector).tooltip();
			break;
	}

	// Fonctionnalité réalisée une option est passée en paramètre
	if (typeof(option) != 'undefined') {
		// Activation de l'option du TOOLTIP
		$(selector).tooltip(option);
	}
};

// Fonctionnalité jQuery réalisée à la fin du chargement de la page dans le navigateur client
$(document).ready(function() {
	// Initialisation des options du DATEPICKER
	$.datepicker.setDefaults($.datepicker.regional["fr"]);

	// Activation du plugin DatePicker sur les champs INPUT de classe DATE
	setDatePicker("input.date");

	// Activation du plugin TimePicker sur les champs INPUT de classe TIME
	setTimePicker("input.time");

	// Activation des TOOLTIPS de pagination
	$(".page-top, .page-bottom").tooltip({
		position:	{my: "left+20", at: "center"}
	});

	// Activation des TOOLTIPS disposés à côté du bouton DELETE
	$(".delete").tooltip({
		position:	{my: "left+10", at: "right center"}
	});

	// Activation des TOOLTIPS statiques
	resetTooltip("tooltip");

    // Activation des TOOLTIPS positionnés à droite
    resetTooltip("tooltip-right");

	// Activation des TOOLTIPS qui suivent la souris de l'utilisateur
	resetTooltip("tooltip-track");

	// Fonctionnaltié réalisée lors du clic sur un élément TOOLTIP, notamment lors de l'ouverture d'un lien vers une nouvelle fenêtre
	$(document).on("click", "[class*=tooltip]", function(event) {
		// Forçage de la fermeture du TOOLTIP
		$(this).tooltip("destroy");

		// Récupération de la classe de l'élément
		var string		= $(this).attr("class");

		// Recherche du nom du TOOLTIP personnalisé
		var mached		= string.match("\s*(tooltip.*)\s*");
		var className	= mached[1];

		// Fonctionnalité réalisée lors de la première entrée du curseur sur l'élément suivant le clic
		$(this).one("mouseenter", function() {
			// Fonctionnalité réalisée si la classe du TOOLTIP a été trouvé
			if (className != '') {
				// Réinitialisation du TOOLTIP personnalisé
				resetTooltip(className, this, "open");
			}
		});
	});

	// Activation des ONGLETS
	$("section.tabs").tabs();

	// Activation des éléments redimentionnables
	$("section.resizable").tabs();

	// Activation des ACCORDIONS
	$("section.accordion").accordion({collapsible: true});

	// Activation de la saisie des champs NUMERIC
	$("input.numeric").numeric({ decimal: false, negative: false });

	// Activation de la saisie des champs DECIMAL
	$("input.decimal").numeric({ decimal: ",", negative: false });

	// Rafraichissement de la page sans envoi des données du formulaire
	$(document).keypress(function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Fonctionnalité réalisée dans le cas de la touche [F5]
		if (event.keyCode == 116) {
			// Annulation de l'événement
			event.preventDefault();

			// Protection contre la validation du formulaire
			return false;
		}
	});

	// Inhibition de la validation accidentelle du formulaire
	$("input, select").keypress(function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Fonctionnalité réalisée dans le cas de la touche [Entrée], [Tabulation] ou [F5]
		if (event.keyCode == 13 || event.keyCode == 9 || event.keyCode == 116) {
			// Récupération des entrées du formulaire
			var inputs	= $(this).closest("form").find(':input');
			var item	= inputs.index(this) + 1;

			// Déplacement du focus
			inputs.eq(item).focus();

			// Annulation de l'événement
			event.preventDefault();

			// Protection contre la validation du formulaire
			return false;
		}
	});

	// Fermeture du message de l'application au clic sur le bouton [X]
	$("a.close").click(function() {
		// Effacement progressif
		$(this).parent("section.message").fadeOut("slow", function() {
			// Masque l'élément à la fin
			$(this).css({display: "none"});
		});
	});

	// Affichage d'un message de confirmation avant le changement de page
	$("button[type=submit].confirm, button[type=submit].force-confirm, button[type=submit].final-confirm").click(function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Fonctionnalité réalisée si l'événement doit être annulé
		if ($(this).prop("disabled")) {
			// Annulation de l'événement
			event.preventDefault();
			// Arrêt du traitement
			return false;
		}

		// Initialisation de l'action du formulaire par défaut
		var button_or_url	= "/" + CONTROLLER + "/reset";

		// Initialisation du sélecteur de la boîte dialogue par défaut
		var selector		= "#dialog-confirm";

		// Fonctionnalité réalisée dans le cas d'une boîte de dialogue pour finalisation
		if (!FW_FORM_UPDATE && $(this).hasClass("final-confirm")) {
			// Modification de l'URL de la boîte de dialogue
			button_or_url	= $(this);

			// Modification du sélecteur de la boîte de dialogue
			selector		= "#dialog-final";
		}

		// Fonctionnalité réalisée si une modification a été réalisée sur le formulaire
		if (FW_FORM_UPDATE || $(this).hasClass("force-confirm") || $(this).hasClass("final-confirm")) {
			// Demande une confirmation avant le changement de la page
			setDialogConfirm(selector, button_or_url);

			$(selector).dialog("open");
			$(selector).removeClass("hidden");

			// Annulation de l'événement
			event.preventDefault();
		} else {
			// Activation de l'arrière-plan de protection durant le chargement
			waitingStatement(event);
		}
	});

	// Affichage d'un message de confirmation avant la suppression d'un élément
	$("button[type=submit].confirm-delete").click(function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Fonctionnalité réalisée si l'événement doit être annulé
		if ($(this).prop("disabled") || $(this).hasClass("disabled")) {
			// Annulation de l'événement
			event.preventDefault();
			// Arrêt du traitement
			return false;
		}

		// Demande une confirmation avant la perte des données
		setDialogConfirm("#dialog-delete", this);
		$("#dialog-delete").dialog("open");
		$("#dialog-delete").removeClass("hidden");

		// Annulation de l'événement
		event.preventDefault();
	});

	// Affichage d'un message de confirmation avant le changement de page
	$(document).on("click", "a[class*=confirm]", function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopPropagation();

		// Fonctionnalité réalisée si l'événement doit être annulé
		if ($(this).prop("disabled") || $(this).hasClass("disabled")) {
			// Annulation de l'événement
			event.preventDefault();
			// Arrêt du traitement
			return false;
		}

		// Récupération de l'URL
		var url = $(this).attr("href");

		// Fonctionnalité réalisée si une modification a été réalisée sur le formulaire ou si la CLASS comporte `force-confirm`
		if (FW_FORM_UPDATE || $(this).hasClass("force-confirm")) {
			// Demande une confirmation avant le changement de la page
			setDialogConfirm("#dialog-confirm", url);

			$("#dialog-confirm").dialog("open");
			$("#dialog-confirm").removeClass("hidden");

			// Annulation de l'événement
			event.preventDefault();
		} else if ($(this).hasClass("confirm-delete")) {
			// Demande une confirmation avant la perte des données
			setDialogConfirm("#dialog-delete", url);
			$("#dialog-delete").dialog("open");
			$("#dialog-delete").removeClass("hidden");

			// Annulation de l'événement
			event.preventDefault();
		}
	});

	// Informe de la modification d'un champ du formulaire
	$("form").on("change", "input, select, textarea", function() {
		FW_FORM_UPDATE = true;
	});

	// Empêhe le focus du champ via le LABEL si le champ est désactivé ou en lecture seule
	$("label", "form").click(function(event) {
		// Récupération de l'identifiant du champ via l'attribut FOR
		var selector = "#" + $(this).attr("for");

		// Fonctionnalité réalisée si le focus doit être annulé
		if ($(selector).prop("readonly") || $(selector).prop("disabled")) {
			// Annulation de l'événement
			event.preventDefault();
		}
	});

	// Fonctionnalité réalisée lors de la navigation entre menus
	$("a", "nav").click(function(event) {
		// Fonctionnalité réalisée si une modification a été réalisée sur le formulaire
		if (FW_FORM_UPDATE) {
			// Récupération de l'URL
			var url = $(this).attr("href");

			// Demande une confirmation avant le changement de la page
			setDialogConfirm("#dialog-confirm", url);

			$("#dialog-confirm").dialog("open");
			$("#dialog-confirm").removeClass("hidden");

			// Annulation de l'événement
			event.preventDefault();
		}
	});

	//#############################################################################################
	// RACCOURCIS CLAVIERS SUR LES BOUTONS
	//#############################################################################################

	// Fonctionnalité réalisée lors du click sur un élément `.tabs-link` ayant un attribut FOR associé à un TABS
	$(document).on("keypress", "body, input, select, textarea", function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopImmediatePropagation();
		var message		= "...";

		// Inhibition de l'action par défaut
		var bDefault	= true;

		// Fonctionnalité réalisée dans le cas de la touche [SAVE]
		if (event.ctrlKey) {
			switch (event.which) {

				case 65:		// Touche [a]
				case 97:		// Touche [A]
					message		= "TOUCHE A";
					bDefault	= false;
					$("[role=touche_A]").click();
					break;

				case 67:		// Touche [c]
				case 99:		// Touche [C]
					message		= "TOUCHE C";
					bDefault	= false;
					$("[role=touche_C]").click();
					break;

				case 68:		// Touche [d]
				case 100:		// Touche [D]
					message		= "TOUCHE D";
					$("[role=touche_D]").click();
					break;

				case 69:		// Touche [e]
				case 101:		// Touche [E]
					message		= "TOUCHE E";
					$("[role=touche_E]").click();
					break;

				case 70:		// Touche [f]
				case 102:		// Touche [F]
					message		= "TOUCHE F";
					$("[role=touche_F]").click();
					break;

				case 78:		// Touche [n]
				case 110:		// Touche [N]
					message		= "TOUCHE N";
					$("[role=touche_N]").click();
					break;

				case 80:		// Touche [p]
				case 112:		// Touche [P]
					message		= "TOUCHE P";
					$("[role=touche_P]").click();
					break;

				case 82:		// Touche [r]
				case 114:		// Touche [R]
					message		= "RELOAD";
					window.location.reload(true);
					break;

				case 13:		// Touche [Entrée]
				case 83:		// Touche [s]
				case 115:		// Touche [S]
					message		= "TOUCHE S";
					$("[role=touche_S]").click();
					break;

				case 84:		// Touche [t]
				case 116:		// Touche [T]
					message		= "TOUCHE T";
					$("[role=touche_T]").click();
					break;

				case 86:		// Touche [v]
				case 118:		// Touche [V]
					message		= "TOUCHE V";
					bDefault	= false;
					$("[role=touche_V]").click();
					break;

				case 88:		// Touche [x]
				case 120:		// Touche [X]
					message		= "TOUCHE X";
					bDefault	= false;
					$("[role=touche_X]").click();
					break;

				default:
					message		= "[Ctrl] + ";
					break;
			}

			// Message de debuggage
			$("#var-debug").text(message + " [" + event.which + "]");

			// Fonctionnalité réalisée si l'événement doit être inhibé
			if (bDefault) {
				// Annulation de l'événement
				event.preventDefault();
			}
		}
	});

	//#############################################################################################
	// RACCOURCIS D'ACTIVATION D'UN ÉLÉMENT TABS
	//#############################################################################################

	// Fonctionnalité réalisée lors du click sur un élément `.tabs-link` ayant un attribut FOR associé à un TABS
	$(".tabs-link[for]").click(function() {
		// Séléction de l'onglet par son ID selon l'attribut FOR
		$("a[href=\"#" + $(this).attr("for") + "\"]").click();
	});

	//#############################################################################################
	// RACCOURCIS D'ACTIVATION D'UN ÉLÉMENT ACCORDION
	//#############################################################################################

	// Fonctionnalité réalisée lors du click sur un élément `.accordion-link` ayant un attribut FOR associé à un ACCORDION
	$(".accordion-link[for]").click(function() {
		// Séléction de l'onglet par son ID selon l'attribut FOR
		$("h3[id=\"" + $(this).attr("for") + "\"]").click();
	});

	//#############################################################################################
	// PROTECTION CONTRE LE SYNDROME DU CLIQUEUR INTEMPESTIF !
	//#############################################################################################

	// Empêche les clics multiples sur les liens de redirection et les boutons de type [submit]
	$(document).on("click", 'a:not("[class*=confirm], [href^=\'#\'], .paginate_button"), button[type=submit]:not("[class*=confirm]")', function(event) {
		// Activation de l'arrière-plan de protection durant le chargement
		waitingStatement(event);
	});

	//#############################################################################################
	// DÉSACTIVATION DU MENU CONTEXTUEL
	//#############################################################################################

	// Arrêt de l'événement au clic droit de la souris
	$(document).bind("contextmenu",function(event) {
		// Protection contre le syndrome du cliqueur intempestif
		event.stopImmediatePropagation();

		// Annulation de l'événement
		event.preventDefault();
	});
});
