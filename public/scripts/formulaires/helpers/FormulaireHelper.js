/**
 * JavaScript relatif à la création d'un formulaire QCM
 */

/**
 * Initialisation du contenu de la bibliothèque MODALE
 *
 * @param	bool		bReset				: initialisation des champs du formulaire.
 */
function initFormulaireHelperModal(bReset) {
	// Affichage d'un message
	$("#gallery").html("<h3 class=\"strong center margin-top-50\">Veuillez valider votre filtre recherche...</h3>");

	// Sélection du filtre sur les éléments orphelins
	if (typeof(bReset) != undefined && bReset == true) {
		// Champ coché
		$("#id_orphelin").attr("checked", "checked");

		// Initialisation des valeurs à 0
		$("#id_domaine").val(0);
		$("#id_sous_domaine").val(0);
		$("#id_categorie").val(0);
		$("#id_sous_categorie").val(0);
	} else {
		// Champ NON coché
		$("#id_orphelin").removeAttr("checked");
	}
}

/**
 * Mise à jour automatique du contenu d'une liste déroulante
 *
 * @param	string		sSelector			: pointeur jQuery de l'élément SELECT.
 * @param	object		oJSON				: contenu JSON de la liste déroulante.
 */
function updateListe(sSelector, oJSON) {
	// Récupération de la valeur courante
	var current	= $(sSelector).val();

	// Purge de la liste originale
	$(sSelector).html("");

	// Ajout d'une première référence vide sélectionnée par défaut
	$(sSelector).append("<option value=\"0\">-</option>");

	// Parcours du contenu JSON
	if (typeof(oJSON) == 'object' && oJSON != []) {
		for (var key in oJSON) {
			// Fonctionnalité réalisée si la valeur courrante est sélectionnée par défaut
			selected = (current == key) ? "selected=\"selected\"" : "";

			// Ajout de l'option
			$(sSelector).append("<option value=\"" + key + "\" " + selected + ">" + oJSON[key] + "</option>");
		}
	}
}

/**
 * Mise à jour automatique de la répartition des points à chaque réponse de la question
 *
 * @param	integer		questionSelected	: occurrence de la question sélectionnée.
 */
function updateValeurs(questionSelected) {
	// Détermine le nombre de réponses valides
	var nReponsesValides = $("input[id^=idValide_" + questionSelected + "_]:checked").length;

	// Mise à jour de chaque valeur des réponses à la question
	$("input[id^=idValide_" + questionSelected + "_]").each(function() {
		// Récupération de la valeur de l'identifiant sélectionné du type [{name}_{question}_{reponse}]
		var aTableauID = $(this).attr("id").split("_");

		// Récupération de l'occurrence de la réponse
		var reponse		= aTableauID[2];

		// Fonctionnalité réalisée si la réponse est valide
		if ($(this).is(":checked")) {
			// La valeur correspond à 100% répartis sur le nombre total de réponses valides
			var valeur = 100 / nReponsesValides;
			// Mise à jour de la valeur avec un arrondi à 3 chiffres après la virgule
			var decimal = (Math.round(valeur*1000)/1000);
			// Transformation sous forme de chaîne de caractères
			var string = "" + decimal;
			// Injection de la valeur en remplaçant le caractère de séparation des décimales [.] par [,]
			$("input[id=idValeur_" + questionSelected + "_" + reponse + "]").val(string.replace(".", ","));
		} else {
			// La valeur correspond à 0%
			$("input[id=idValeur_" + questionSelected + "_" + reponse + "]").val(0);
		}
	});
}

/**
 * Mise à jour automatique des réponses strictes
 *
 * @param	integer		questionSelected	: occurrence de la question sélectionnée.
 * @param	boolean		updateValues		: (optionnel) mise à jour des valeurs.
 */
function updateReponses(questionSelected, updateValues) {
	// Activation de l'arrière-plan de protection contre les cliqueurs intempestifs durant le chargement
	$("div#stop-click").css({display: "block"});

	// Traitement différé afin que l'élément précédent soit actif
	setTimeout(function () {
		// Affichage / Masquage du champ de la valeur selon l'état du checkbox [Valide]
		$("input[id^=idValide_" + questionSelected + "_]", "ol#reponses_" + questionSelected).each(function() {
			// Récupération de la valeur de l'identifiant sélectionné du type [{name}_{question}_{reponse}]
			var aValideID = $(this).attr("id").split("_");

			// Récupération de l'occurrence de la réponse
			var reponse	= aValideID[2];

			// Fonctionnalité réalisée si le champs [Réponse stricte attendue] est coché
			if ($("input#idStrictCheckbox_" + questionSelected).is(":checked")) {
				// Valeur du champ caché de la réponse stricte
				$("input#idStricteValue_" + questionSelected).val("true");

				// Masquage du facteur de pénalité
				$("#facteur_" + questionSelected).addClass("hidden");

				// Masquage de la valeur
				$("#valeur_" + questionSelected + "_" + reponse).addClass("hidden");
			} else {
				// Valeur du champ caché de la réponse stricte
				$("input#idStricteValue_" + questionSelected).val("false");

				// Affichage du facteur de pénalité
				$("#facteur_" + questionSelected).removeClass("hidden");

				// Affichage / Masquage du champ de la valeur selon l'état du checkbox [Valide]
				if ($("input[id=idValide_" + questionSelected + "_" + reponse + "]").is(":checked")) {
					// Affichage de la valeur
					$("#valeur_" + questionSelected + "_" + reponse).removeClass("hidden");
					// Masquage de la sanction
					$("#penalite_" + questionSelected + "_" + reponse).addClass("hidden");
				} else {
					// Masquage de la valeur
					$("#valeur_" + questionSelected + "_" + reponse).addClass("hidden");
					// Affichage de la sanction
					$("#penalite_" + questionSelected + "_" + reponse).removeClass("hidden");
				}

				// Fonctionnalité réalisée si les valeurs doivent être mises à jour
				if (typeof(updateValues) == "boolean" && updateValues) {
					updateValeurs(questionSelected);
				}
			}

			// Fonctionnalité réalisée si le champs [Réponse libre] est coché
			if ($("input#idLibreCheckbox_" + questionSelected).is(":checked")) {
				// Valeur du champ caché de la réponse stricte
				$("input#idLibreValue_" + questionSelected).val("true");
				$("ol#reponses_" + questionSelected).addClass("hidden");
				$("div#idLignesQuestion_" + questionSelected).removeClass("hidden");
				$("ol#correction_" + questionSelected).removeClass("hidden");
			} else {
				// Valeur du champ caché de la réponse stricte
				$("input#idLibreValue_" + questionSelected).val("false");
				$("ol#reponses_" + questionSelected).removeClass("hidden");
				$("div#idLignesQuestion_" + questionSelected).addClass("hidden");
				$("ol#correction_" + questionSelected).addClass("hidden");
			}
		});

		// Fonctionnalité réalisée si la réponse attendue n'est pas stricte
		if (! $("input[id^=idStrictCheckbox_" + questionSelected + "]", "ol#reponses_" + questionSelected).is(":checked")) {
			// Affichage / Masquage du champ de la pénalité selon l'état du checkbox [Sanction]
			$("input[id^=idSanction_" + questionSelected + "_]").each(function () {
				// Récupération de la valeur de l'identifiant sélectionné du type [{name}_{question}_{reponse}]
				var aSanctionID = $(this).attr("id").split("_");

				// Récupération de l'occurrence de la réponse
				var reponse = aSanctionID[2];

				// Affichage / Masquage du champ de la sanction selon l'état du checkbox [Valide]
				if ($("input[id=idSanction_" + questionSelected + "_" + reponse + "]").is(":checked")) {
					// Affichage de la sanction
					$("#penalite_" + questionSelected + "_" + reponse).removeClass("hidden");
				} else {
					// Masquage de la sanction
					$("#penalite_" + questionSelected + "_" + reponse).addClass("hidden");
				}
			});
		}

		// Actualisation de l'affichage ACCORDION
		$("section.accordion").accordion("refresh");

		// Suppression la protection contre les clics intempestifs
		$("div#stop-click").css({display: "none"});
	}, 250);
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
	// MODIFICATION DES VALEURS ET SANCTIONS D'UNE RÉPONSE
	//#############################################################################################

	// Clic sur le champ [Valide] d'une réponse
	$("input[id^=idValide_]").click(function() {
		// Récupération de la valeur de l'identifiant sélectionné du type [{name}_{question}_{reponse}]
		var aTableauID = $(this).attr("id").split("_");

		// Récupération des variables dans l'identifiant
		var name		= aTableauID[0];
		var question	= aTableauID[1];
		var reponse		= aTableauID[2];

		// Fonctionnalité réalisée si le checkbox [Valide] est coché
		if ($(this).is(":checked")) {
			// Suppression de l'état du checkbox [Sanction]
			$("input#idSanction_" + question + "_" + reponse).removeAttr("checked");
		}

		// Mise à jour avec actualisation de la valeur des réponses
		updateReponses(question, true);
	});

	// Clic sur le champ [Sanction] d'une réponse
	$("input[id^=idSanction_]").click(function() {
		// Récupération de la valeur de l'identifiant sélectionné
		var aTableauID = $(this).attr("id").split("_");

		// Récupération des variables dans l'identifiant du type [{name}_{question}_{reponse}]
		var name		= aTableauID[0];
		var question	= aTableauID[1];
		var reponse		= aTableauID[2];

		// Fonctionnalité réalisée si le checkbox [Sanction] est coché
		if ($(this).is(":checked")) {
			// Récupération du barème de la question
			var bareme = $("#idBareme_" + question).val();

			// Suppression de l'état du checkbox [Valide]
			$("input#idValide_" + question + "_" + reponse).removeAttr("checked");

			// Ajout de la valeur du barème par défaut
			$("input#idPenalite_" + question + "_" + reponse).val(bareme);
		} else {
			// Suppression de la valeur de la pénalité courante
			$("input#idPenalite_" + question + "_" + reponse).val(0);
		}

		// Mise à jour avec actualisation de la valeur des réponses
		updateReponses(question, true);
	});

	// Modification sur le champ [Sanction] d'une réponse
	$("input[id^=idPenalite_]").keyup(function(event) {
		// Stop la propagation du presseur-fou !
		event.stopPropagation();

		// Récupération de la valeur de l'identifiant sélectionné
		var aTableauID = $(this).attr("id").split("_");

		// Récupération des variables dans l'identifiant du type [{name}_{question}_{reponse}]
		var name = aTableauID[0];
		var question = aTableauID[1];
		var reponse = aTableauID[2];

		// Pénalité de la question
		if (reponse == undefined) {
			// Récupération du barème de la question
			var maximum = 100;

			// Fonctionnalité réalisée si la valeur du facteur dépasse les 100%
			if (Math.abs($(this).val()) > maximum) {
				// Remplacement de la valeur actuelle avec celle du barème de la question
				$(this).val(maximum);
			}
		} else {
			// Récupération du barème de la question
			var maximum = $("#idBareme_" + question).val();

			// Fonctionnalité réalisée si la valeur de la réponse dépasse le barème
			if (Math.abs($(this).val()) > maximum) {
				// Remplacement de la valeur actuelle avec celle du barème de la question
				$(this).val(maximum);
			}
		}
	});

	//#############################################################################################
	// CHOIX DE RÉPONSES STRICTES ATTENDUES
	//#############################################################################################

	// Clic sur le champ [Réponse stricte attendue aux questions par défaut] du formulaire général
	$("input#idStrictCheckboxDefaut").click(function() {
		if ($(this).is(":checked")) {
			$("li#idPenalite").addClass("hidden");
			$("#idFormulaireStrictDefaut").val("true");
		} else {
			$("li#idPenalite").removeClass("hidden");
			$("#idFormulaireStrictDefaut").val("false");
		}
	});

	// Clic sur le champ [Stricte] d'une question
	$("input[id^=idStrictCheckbox_]").click(function() {
		// Récupération de la valeur de l'identifiant sélectionné
		var aTableauID = $(this).attr("id").split("_");

		// Récupération des variables dans l'identifiant du type [{name}_{question}]
		var name		= aTableauID[0];
		var question	= aTableauID[1];

		// Fonctionnalité réalisée si le checkbox est coché
		var updateValues= false;
		if ($(this).is(":checked")) {
			// Masque les sanctions
			$("dl.reponse_sanction_" + question).addClass("hidden");
		} else {
			// Affiche les sanctions
			$("dl.reponse_sanction_" + question).removeClass("hidden");

			// Actualise les valeurs
			updateValues = true;
		}

		// Mise à jour avec actualisation de la valeur des réponses
		updateReponses(question, updateValues);
	});

	//#############################################################################################
	// CHOIX D'UNE RÉPONSE LIBRE
	//#############################################################################################

	// Clic sur le champ [Libre] d'une question
	$("input[id^=idLibreCheckbox_]").click(function() {
		// Récupération de la valeur de l'identifiant sélectionné
		var aTableauID = $(this).attr("id").split("_");

		// Récupération des variables dans l'identifiant du type [{name}_{question}]
		var name		= aTableauID[0];
		var question	= aTableauID[1];

		// Fonctionnalité réalisée si le checkbox est coché
		if ($(this).is(":checked")) {
			// Masque les réponses

		} else {
			// Affiche les réponses

		}

		// Mise à jour avec actualisation de la valeur des réponses
		updateReponses(question, false);
	});

	//#############################################################################################
	// OPTIONS DE L'ÉPREUVE
	//#############################################################################################

	// Surcharge de l'autocomplétion lors du changement de destinataire
	$("#idDestinataires").autocomplete({
		close:		function() {
			// Recherche du stage par son identifiant
			$.ajax({
				async:		false,
				type:		"POST",
				dataType:	"JSON",
				url:		"/search/stage",
				data:		{id: $("#epreuve_stage").val()},
				success:	function(response) {
					// Fonctionnalité réalisée si le stage est valide
					if (typeof(response) == 'object') {
						// Initialisation de la date de fin du stage
						var dateFin = new Date(response.date_fin_stage);
						// Renseignement de la date de fin
						$('#idDateEpreuve').datepicker('setDate', dateFin);
					}
				}
			});
		}
	});

	// Attribution des tables d'examens lors de la sélection d'une salle
	$("input[id^=epreuve_liste_salles_check_]").click(function() {
		var bValide = false;
		$("input[id^=epreuve_liste_salles_check_]").each(function() {
			if ($(this).is(":checked")) {
				bValide = true;
			}
		});

		if (bValide) {
			$("#attribution-tables").removeClass("hidden");
		} else {
			$("#attribution-tables").addClass("hidden");
		}
	});

	// Attribution d'une table à chaque candidat
	$("input#epreuve_table_affectation_check").click(function(event) {
		// Stop la propagation du presseur-fou !
		event.stopPropagation();

		if (!$(this).is(":checked") && $("input#epreuve_table_aleatoire_check").is(":checked")) {
			$("input#epreuve_table_aleatoire_check").click();
		}
	});

	// Distribution aléatoire des tables
	$("input#epreuve_table_aleatoire_check").click(function(event) {
		// Stop la propagation du presseur-fou !
		event.stopPropagation();

		if ($(this).is(":checked") && !$("input#epreuve_table_affectation_check").is(":checked")) {
			$("input#epreuve_table_affectation_check").click();
		}
	});

	//#############################################################################################
	// FILTRE DE RECHERCHE DU FORMULAIRE PRINCIPAL
	//#############################################################################################

	// Modification de la liste des sous-domaines dans le formulaire
	$("#idDomaine").change(function() {
		// Recherche de la liste des sous-domaines par l'identifiant du domaine
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/sous_domaine",
			data:		{id_domaine: $(this).val()},
			beforeSend:	function() {
				$("#idSousDomaine").addClass("loading");
				$("#idCategorie").addClass("loading");
				$("#idSousCategorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des sous-domaines
				updateListe("#idSousDomaine", response);

				// Modification de la liste des sous-catégories
				updateListe("#idSousCategorie", undefined);

				// Modification de la liste des sous-domaines dans la bibliothèque
				updateListe("#id_sous_domaine", response);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#idSousDomaine").removeClass("loading");
				$("#idCategorie").removeClass("loading");
				$("#idSousCategorie").removeClass("loading");
			}
		});

		// Recherche de la liste des catégories par l'identifiant du domaine
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/categorie",
			data:		{id_domaine: $(this).val()},
			beforeSend:	function() {
				// Indicateur de traitement
				$("#idCategorie").addClass("loading");
				$("#idSousCategorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des catégories
				updateListe("#idCategorie", response);

				// Modification de la liste des catégories dans la bibliothèque
				updateListe("#id_categorie", response);

				// Modification de la liste des sous-catégories dans la bibliothèque
				updateListe("#id_sous_categorie", undefined);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#idCategorie").removeClass("loading");
				$("#idSousCategorie").removeClass("loading");
			}
		});

		// Modification de la sélection du domaine dans la bibliothèque
		$("#id_domaine").val($(this).val());

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	// Modification de la liste des sous-domaines dans le formulaire
	$("#idSousDomaine").change(function() {
		// Modification de la sélection du sous-domaine dans la bibliothèque
		$("#id_sous_domaine").val($(this).val());

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	// Modification de la liste des sous-catégories dans le formulaire
	$("#idCategorie").change(function() {
		// Recherche de la liste des sous-catégories par l'identifiant de la catégorie
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/sous_categorie",
			data:		{id_categorie: $(this).val()},
			beforeSend:	function() {
				// Indicateur de traitement
				$("#idSousCategorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des sous-catégories
				updateListe("#idSousCategorie", response);

				// Modification de la liste des sous-catégories dans la bibliothèque
				updateListe("#id_sous_categorie", response);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#idSousCategorie").removeClass("loading");
			}
		});

		// Modification de la sélection de la catégorie dans la bibliothèque
		$("#id_categorie").val($(this).val());

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	// Modification de la liste des sous-catégories dans le formulaire
	$("#idSousCategorie").change(function() {
		// Modification de la sélection de la sous-categorie dans la bibliothèque
		$("#id_sous_categorie").val($(this).val());

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	//#############################################################################################
	// FILTRE DE RECHERCHE DE LA BIBLIOTHÈQUE
	//#############################################################################################

	// Modification de la liste des sous-domaines lors du changement du domaine dans la bibliothèque
	$("#id_domaine").change(function() {
		// Recherche de la liste des sous-domaines par l'identifiant du domaine
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/sous_domaine",
			data:		{id_domaine: $(this).val()},
			beforeSend:	function() {
				// Indicateur de traitement
				$("#id_sous_domaine").addClass("loading");
				$("#id_categorie").addClass("loading");
				$("#id_sous_categorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des sous-domaines dans la bibliothèque
				updateListe("#id_sous_domaine", response);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#id_sous_domaine").removeClass("loading");
				$("#id_categorie").removeClass("loading");
				$("#id_sous_categorie").removeClass("loading");
			}
		});

		// Recherche de la liste des catégories par l'identifiant du domaine
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/categorie",
			data:		{id_domaine: $(this).val()},
			beforeSend:	function() {
				// Indicateur de traitement
				$("#id_categorie").addClass("loading");
				$("#id_sous_categorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des catégories dans la bibliothèque
				updateListe("#id_categorie", response);

				// Modification de la liste des sous-catégories
				updateListe("#id_sous_categorie", undefined);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#id_categorie").removeClass("loading");
				$("#id_sous_categorie").removeClass("loading");
			}
		});

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	// Modification de la liste des sous-catégories lors du changement de la catégorie dans la bibliothèque
	$("#id_categorie").change(function() {
		// Recherche de la liste des sous-catégories par l'identifiant de la catégorie
		$.ajax({
			async:		false,
			type:		"POST",
			dataType:	"JSON",
			url:		"/search/sous_categorie",
			data:		{id_categorie: $(this).val()},
			beforeSend:	function() {
				// Indicateur de traitement
				$("#id_sous_categorie").addClass("loading");
			},
			success:	function(response) {
				// Modification de la liste des sous-catégories dans la bibliothèque
				updateListe("#id_sous_categorie", response);
			},
			complete:	function(response) {
				// Fin de traitement
				$("#id_sous_categorie").removeClass("loading");
			}
		});

		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal(false);
	});

	// Modification de la liste des sous-catégories lors du changement de la catégorie dans la bibliothèque
	$("#id_orphelin").change(function() {
		// Purge du résultat de la bibliothèque
		initFormulaireHelperModal($(this).is(":checked"));
	});
});
