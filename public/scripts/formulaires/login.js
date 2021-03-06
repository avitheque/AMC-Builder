/**
 * JavaScript de connexion à l'application.
 * 
 * @li		Manipulation de la VARIABLE GLOBALE JavaScript `FW_FORM_UPDATE`
 * @see		ViewRender::setFormUpdateStatus(boolean);
 * @see		/public/scripts/main.js;
 * @code
 * 		var	FW_FORM_UPDATE	= false;
 * @endcode
 */

// Fonctionnalité jQuery réalisée à la fin du chargement de la page dans le navigateur client
$(document).ready(function() {

	// Fonctionnalité réalisée avec un léger décalage par rapport au chargement
	setTimeout(function () {
		// Focus automatique sur le premier champ vide
		if ($("input#idLogin").val() == "") {
			// Focus sur le Login
			$("input#idLogin").focus();
		} else if ($("input#idPassword").val() == "") {
			// Focus sur le Password
			$("input#idPassword").focus();
		}
	}, 250);

	// Désactivation du contrôle de la saisie sur les champs
	$("input#idLogin, input#idPassword").change(function () {
		// Réinitialisation de l'indicateur de modification du formulaire
		FW_UPDATE_FORM = false;
	});

	// Fonctionnalité réalisée lors de la saisie d'une touche dans un champ
	$("input").keypress(function(event) {
		// Fonctionnalité réalisée lors de la saisie de la touche [Entrée]
		if (event.keyCode == 13) {
			// Contrôle de la validité des champs avant envoi du formulaire
			if ($("input#idLogin").val() != "" && $("input#idPassword").val() != "") {
				// Validation du formulaire
				$("button#idConnexion").click();
			}
		}
	});

});
