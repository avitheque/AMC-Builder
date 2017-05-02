/**
 * JavaScript relatif à la création d'un formulaire REFERENTIEL
 */
var DEFAULT_DATEDEBUT	= new Date();				// Date courante
var DEFAULT_DATEFIN		= new Date(9999, 11, 31);	// Date 31/12/9999

/**
 * Fonctionnalité réalisée à la fin du chargement de la page chez le client
 */
$(document).ready(function() {
	// Expression de la date
	var dateReg = new RegExp(/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/);
	
	// Réinitialisation de la date de fin si elle n'est pas valide
	$("#idDateDebutReferentiel").blur(function() {
		// Fonctionnalitéréalisée si la date n'est pas valide
		if (!dateReg.test($(this).val())) {
			$(this).datepicker("setDate", DEFAULT_DATEDEBUT);
		}
	});
	
	// Réinitialisation de la date de fin si elle n'est pas valide
	$("#idDateFinReferentiel").datepicker("option", "defaultDate", DEFAULT_DATEFIN);
	
	// Réinitialisation de la date de fin si elle n'est pas valide
	$("#idDateFinReferentiel").blur(function() {
		// Fonctionnalitéréalisée si la date n'est pas valide
		if (!dateReg.test($(this).val())) {
			$(this).datepicker("setDate", DEFAULT_DATEFIN);
		}
	});
	
	// Réinitialisation de la date de début par l'utilisateur
	$("#resetDateDebut").click(function() {
		$("#idDateDebutReferentiel").datepicker("setDate", DEFAULT_DATEDEBUT);
	});

	// Réinitialisation de la date de fin par l'utilisateur
	$("#resetDateFin").click(function() {
		$("#idDateFinReferentiel").datepicker("setDate", DEFAULT_DATEFIN);
	});
	
	//=============================================================================================
	//	REFERENTIEL `salle`
	//=============================================================================================

	// Fonctionnalité réalisée au clic sur [Postes reliées au réseau]
	$("#statut_salle_reseau_check").click(function() {
		// Cochage du statut [Salle équipée informatique]
		if ($(this).is(":checked") && !$("#statut_salle_informatique_check").is(":checked")) {
			$("#statut_salle_informatique_check").click();
		}
	});
});