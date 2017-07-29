/**
 * JavaScript relatif à la réalisation d'une épreuve QCM
 */

/**
 * Initialisation du décompte automatique
 *
 * @li		Fonctionnalité réalisée en MODE CONTRÔLE.
 *
 * @param	bool		bReset				: initialisation des champs du formulaire.
 */
function initCompteur(bReset) {

}

/**
 * Déplacement du SCROLL de la fenêtre vers la question sélectionnée par son sélecteur.
 *
 * @param	string		selector			: sélecteur de la question du type [#Q999].
 */
function scrollToQuestionById(selector) {
	// 1er ancrage sur la question sélectionnée
	setTimeout(function() {
		// Récupération de la position de la question dans la fenêtre
		var scroll = $(selector).offset().top - $("#article-main").offset().top + parseInt($("#article-main").css("margin-top"));
		// Déplacement de la fenêtre sur la question sélectionnée
		$("main").scrollTop(scroll);
	}, 500);
	// 2ème ancrage sur la question sélectionnée
	window.location.replace(selector);
}

/**
 * Déplacement du SCROLL de la fenêtre vers la question sélectionnée par son numéro.
 *
 * @li	ATTENTION : la première question commence à 1.
 * @li	ATTENTION : la première occurrence commence à 0.
 *
 * @param	integer		questionSelected	: numéro de la question sélectionnée.
 */
function scrollToQuestionSelected(questionSelected) {
	// Transtypage de l'identifiant sur 3 caractères au format [0-9]{3}
	questionSelected = "" + parseInt(questionSelected);
	while (questionSelected.length < 3) {
		questionSelected = "0" + questionSelected;
	}
	// Récupération de l'identifiant du titre
	var selector = "#Q" + questionSelected;
	scrollToQuestionById(selector);
}

/**
 * Déplacement du SCROLL de la fenêtre vers la question sélectionnée par son occurrence.
 *
 * @li	ATTENTION : la première question commence à 1.
 * @li	ATTENTION : la première occurrence commence à 0.
 *
 * @param	integer		occurrence			: occurrence de la question sélectionnée.
 */
function scrollToQuestionOccurrence(occurrence) {
	// Déplacement de la fenêtre vers l'occurrence de la question sélectionnée
	var questionSelected = parseInt(occurrence) + 1;
	scrollToQuestionSelected(questionSelected);
}

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {
	// Navigation dans les TABS
	$("a", "li[role=tab]").click(function() {
		// Récupération de l'occurrence de l'onglet actif
		var tabSelected = parseInt($(this).attr("id").replace("ui-id-", ""));
		// Stockage de l'occurence de l'onglet actif
		$("input[name=formulaire_active_tab]").val(tabSelected - 1);
		// Déplacement du SCROLL vers la question sélectionnée
		scrollToQuestionOccurrence($("input[name=formulaire_active_question]").val());
	});

	// Navigation dans l'ACCORDEON
	$("h3.item-title").click(function() {
		// Récupération du numéro de la question sélectionnée
		var questionSelected = parseInt($(this).attr("id").replace("Q", ""));
		// Stockage de l'occurence de la question
		$("input[name=formulaire_active_question]").val(questionSelected - 1);
		// Déplacement du SCROLL vers la question sélectionnée
		scrollToQuestionSelected(questionSelected);
	});

    //#############################################################################################
    // CHOIX D'AUCUNE RÉPONSE PAR LE CANDIDAT									-	MODE CONTRÔLE
    //#############################################################################################

    // Clic sur le champ [Aucune réponse n'est correcte] d'une question lors d'un contrôle
    $("input[id^=idAucuneCheckbox_]").click(function() {
        // Récupération de la valeur de l'identifiant sélectionné
        var aTableauID = $(this).attr("id").split("_");

        // Récupération des variables dans l'identifiant du type [{name}_{question}]
        var name		= aTableauID[0];
        var question	= aTableauID[1];

        // Fonctionnalité réalisée si le checkbox est coché
        if ($(this).is(":checked")) {
            // Désactive les réponses
            $("input[id^=idReponseCandidat_" + question + "]:checked").each(function() {
                $(this).removeAttr("checked");
            });
        }
    });

    // Clic sur le champ [Checkbox] d'une question lors d'un contrôle
    $("input[id^=idReponseCandidat_]").click(function() {
        // Récupération de la valeur de l'identifiant sélectionné
        var aTableauID = $(this).attr("id").split("_");

        // Récupération des variables dans l'identifiant du type [{name}_{question}]
        var name		= aTableauID[0];
        var question	= aTableauID[1];

        // Fonctionnalité réalisée si le checkbox est coché
        if ($(this).is(":checked")) {
            // Désactive le champ [Aucune réponse n'est correcte]
            $("input#idAucuneCheckbox_" + question + ":checked").removeAttr("checked");
        }
    });
});
