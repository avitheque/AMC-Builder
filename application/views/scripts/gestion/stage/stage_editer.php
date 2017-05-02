<?php
/**
 * @brief	Candidat
 *
 * Vue de gestion d'un candidat.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

// Récupération de l'instance d'authentification
$oAuth	= AuthenticateManager::getInstance();

//#################################################################################################
// INTERFACE DE CRÉATION DU FORMULAIRE
//#################################################################################################

// Zone du formulaire injecté dans $_SESSION[VIEW_MAIN]
$oStage = new StageHelper(!$oAuth->isModifiable());
ViewRender::addToMain($oStage->render());

// Zone du formulaire MODAL dans $_SESSION[VIEW_BODY]
$oModal = new ModalHelper("recherche_candidats", "/gestion/stage?search=candidat");
$oModal->setEnctype(ModalHelper::ENCTYPE_MULTIPART_FORMDATA);
$oModal->setTitle("Recherche de candidats");
$oModal->setWidth(800);
$oModal->linkContent("<section id=\"search\"><span class=\"loading\">Chargement en cours...</span></section>");

// Script de recherche des candidats disponibles
$sScript = '$("#add_candidat").click(function() {
				$.ajax({
					async:		false,
					type:		"POST",
					dataType:	"HTML",
					url:		"/search/candidat",
					data:		{debut: $("#idDateDebutStage").val(), fin: $("#idDateFinStage").val()},
					beforeSend:	function(any) {
						// Indicateur de chargement
						$("section#search").html("<span class=\"loading\">Chargement en cours...</span>");
					},
					success:	function(response) {
						$("section#search").html(response);
					}
				});
			});';

$oModal->addScript($sScript);

// Rendu final du formulaire MODAL
$oModal->renderHTML();