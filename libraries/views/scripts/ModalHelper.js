/**
 * JavaScript relatif à la classe ModalHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

// Fonctionnalité jQuery réalisée à la fin du chargement de la page dans le navigateur client
$(document).ready(function() {
	// Fermeture de la boîte de dialogue
	$(".modal").on("click", ".closeDialog", function() {
		$(".modal").dialog("close");
		$(".modal").addClass("hidden");
	});
});
