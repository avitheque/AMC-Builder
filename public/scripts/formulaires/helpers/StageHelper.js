/**
 * JavaScript relatif à la création d'un formulaire STAGE
 */

// Modification des dates de début et de fin de stage
function updateDatepicker(minDate, maxDate) {
	// Fonctionnalité réalisée si la date de début n'est pas valide
	if (typeof(minDate) != 'object') {
		minDate = new Date();
	}

	// Fonctionnalité réalisée si la date de fin n'est pas valide
	if (typeof(maxDate) != 'object') {
		maxDate = new Date();
	}

	// Fonctionnalité réalisée si la date de début est plus grande que la date de fin
	if (minDate > maxDate) {
		$('#idDateDebutStage').datepicker('setDate', minDate);
	}

	// Actualisation du minimum pour la date de fin
	$('#idDateFinStage').datepicker('option', 'minDate', minDate);
}

//Fonctionnalité jQuery réalisée à la fin du chargement de la page dans le navigateur client
$("main").ready(function() {

	//=============================================================================================
	//	ÉDITER
	//=============================================================================================

	// Initialisation du minimum pour la date de fin
	$("#idDateFinStage").datepicker('option', 'minDate', $("#idDateDebutStage").val());

	// Modification de la date de début
	$("#idDateDebutStage").datepicker('option', 'onSelect', function() {
		var minDate = $(this).datepicker('getDate');
		var maxDate = $("#idDateFinStage").datepicker('getDate');
		updateDatepicker(minDate, maxDate);
	});

	//=============================================================================================
	//	AJOUTER
	//=============================================================================================

	// Affichage du MODAL d'importation
	$("#add_candidat").click(function() {
		$("#recherche_candidats").dialog("open");
		$("#recherche_candidats").removeClass("hidden");
	});

	//=============================================================================================
	//	SUPPRIMER LA SÉLECTION / RENOUVELER LE CODE
	//=============================================================================================

	// Fonctionnalité réalisée lors de la sélection d'un candidat dans le DataTable
	$("table#table-candidats").on("click", "input[id^=selection_]", function() {
		var nombre = 0;
		$("input[id^=selection_]").each(function() {
			if ($(this).is(":checked")) {
				nombre++;
			}
		});

		// Affichage des options de selection si au moin un candidat est sélectionné
		if (nombre > 0) {
			$("div#action").removeClass("hidden");
		} else {
			$("div#action").addClass("hidden");

			// Affichage du bouton [Tout cocher]
			$("a#check_all").removeClass("hidden");
			$("a#remove_all").addClass("hidden");
		}
	});

	// Sélection de tous les candidats
	$("a#check_all").click(function() {
		$(this).addClass("hidden");
		$("a#remove_all").removeClass("hidden");

		// Parcours de tous les champs
		$("input[id^=selection_]").each(function(index) {
			// Cochage des champs non sélectionnés
			if (!$(this).is(":checked")) {
				// ATTENTION : L'attribut `checked` fonctionne mal avec le CSS, l'événement [click] passe mieux
				$(this).click();
			}
		});
	});

	// Désélection de tous les candidats
	$("a#remove_all").click(function() {
		$(this).addClass("hidden");
		$("a#check_all").removeClass("hidden");

		// Parcours de tous les champs
		$("input[id^=selection_]").each(function() {
			// Décochage de chaque champ
			$(this).removeAttr("checked");
		});

		// Masquage des options
		$("div#action").addClass("hidden");
	});

	//=============================================================================================
	//	IMPORTER - FICHIER CSV
	//=============================================================================================

	// Liste de choix des colonnes lors de l'importation du fichier CSV
	$("select[id^=filtre_]").change(function() {
		// Liste de sélection
		var FILTRE_SELECTION = [];

		// Initialisation de la sélection
		$("select[id^=filtre_] option:selected").each(function() {
			$(this).removeAttr("hidden");
		});

		// Ajout de la collection
		$("select[id^=filtre_] option:selected").each(function() {
			var selection = $(this).attr("value");
			if (selection != 0) {
				FILTRE_SELECTION.push(selection);
			}
		});

		// Suivi de sélection sur l'ensemble des champs SELECT
		$("select[id^=filtre_] option").not(":selected").each(function() {
			var selected = false;
			var selection = $(this).attr("value");

			// Parcours de la collection
			for (i in FILTRE_SELECTION) {
				// Fonctionnalité réalisée si le choix correspond
				if (selection == FILTRE_SELECTION[i]) {
					selected = true;
				}
			}

			// Fonctionnalité réalisée si la valeur est déjà sélectionnée
			if (selected) {
				// Masquage du choix
				$(this).addClass("hidden");
			} else {
				// Affichage du choix
				$(this).removeClass("hidden");
			}
		});

	});
});
