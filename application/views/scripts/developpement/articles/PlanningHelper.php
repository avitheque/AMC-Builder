<?php
/**
 * Documentation sur l'exploitation de l'objet PlanningHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Initialisation de l'objet PlanningHTMLHelper sur une semaine de 7 jours à compter du 02/01/2017
// Remarque : ici la semaine se termine le vendredi à 16:00
$oPlanningProgression = new PlanningHTMLHelper("2017-01-02", 7, 8, 19, "8,13,18", "5:16-23,6,7", 60);
// Rendu du planning sous forme de progression
$oPlanningProgression->setPlanningRender(PlanningHTMLHelper::FORMAT_PROGRESSION);
// Ajout d'un jour férié : ici il ne sera pas visible !
$oPlanningProgression->addDateToDeprecated("2017-01-01");
// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningProgression->setModalAction("/planning/tache");

// Création d'une entrée sur le PLANNING
$oItem_2017_01_02_09		= new Planning_ItemHelper(1, "Administration", "Remise des documents");
// Attribution de la durée de l'événement
$oItem_2017_01_02_09->setDuration(4);
// Attribution de la date et l'heure de l'événement
$oItem_2017_01_02_09->setDateTime("2017-01-02 09:00");
// Ajout d'un participant PRINCIPAL à l'événement
$oItem_2017_01_02_09->setParticipant(1, "John DOE", Planning_ItemHelper::TYPE_PRINCIPAL);
// Ajout de l'entrée au PLANNING
$oPlanningProgression->addItem($oItem_2017_01_02_09);

// Ajout du Datatable à l'article
$sArticle	.= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>PlanningHTMLHelper</span> avec le rendu <span class='strong red italic'>PlanningHTMLHelper::</span><span class='strong italic'>FORMAT_PROGRESSION</span></legend>
					" . $oPlanningProgression->renderHTML() . "
					<p id='hidden-PlanningProgression' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-PlanningProgression\").addClass(\"hidden\");$(\"#visible-PlanningProgression\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class='commentaire'>// Initialisation de l'objet PlanningHTMLHelper sur une semaine de 7 jours à compter du 02/01/2017</span><br/>
						<span class='commentaire'>// Remarque : ici la semaine se termine le vendredi à 16:00</span><br/>
						<span class='variable'>\$oPlanningProgression</span> = <span class='native'>new</span> PlanningHTMLHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [Y-m-d]\">\"2017-01-02\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">7</span>, <span class='nombre pointer hover-bold' title=\"Heure de début de chaque jour de la semaine\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin de chaque jour de la semaine\">19</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"8,13,18\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
						<span class='commentaire'>// Rendu du planning sous forme de progression</span><br/>
						<span class='variable'>\$oPlanningProgression</span>->setPlanningRender(PlanningHTMLHelper::<span class='methode italic'>FORMAT_PROGRESSION</span>);<br/>
						<span class='commentaire'>// Ajout d'un jour férié : ici il ne sera pas visible !</span><br/>
						<span class='variable'>\$oPlanningProgression</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date du jour à enregistrer comme non travaillé au format [Y-m-d]\">\"2017-01-01\"</span>);<br/>
						<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
						<span class='variable'>\$oPlanningProgression</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
						<br />
						<span class='commentaire'>// Création d'une entrée sur le PLANNING</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span> = <span class='native'>new</span> Planning_ItemHelper(<span class='nombre pointer hover-bold' title=\"Identifiant de la tâche\">1</span>, <span class='texte pointer hover-bold' title=\"Titre de la tâche\">\"Administration\"</span>, <span class='texte pointer hover-bold' title=\"Description de la tâche\">\"Remise des documents\"</span>);<br/>
						<span class='commentaire'>// Attribution de la date et l'heure de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDateTime(<span class='texte pointer hover-bold' title=\"Enregistrement de la date et l'heure de la tâche au format [Y-m-d H:i]\">\"2017-01-02 09:00\"</span>);<br/>
						<span class='commentaire'>// Attribution de la durée de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDuration(<span class='nombre pointer hover-bold' title=\"Renseignement de la durée de l'événement (1 par défaut)\">4</span>);<br/>
						<span class='commentaire'>// Ajout d'un participant PRINCIPAL à l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setParticipant(<span class='nombre pointer hover-bold' title=\"Identifiant du groupe associé à la liste des participants\">1</span>, <span class='texte pointer hover-bold' title=\"Nom du participant\">\"John DOE\"</span>, Planning_ItemHelper::<span class='methode italic'>TYPE_PRINCIPAL</span>);<br/>
						<span class='commentaire'>// Ajout de l'entrée au PLANNING</span><br/>
						<span class='variable'>\$oPlanningProgression</span>->addItem(<span class='variable pointer hover-bold' title=\"Instance de Planning_ItemHelper\">\$oItem_2017_01_02_09</span>);<br/>
						<br />
						<span class='commentaire'>// Rendu final sous forme de code HTML</span><br/>
						<span class='native'>print</span> <span class='variable'>\$oPlanningProgression</span>->renderHTML();
					</p>
					<p id='visible-PlanningProgression' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-PlanningProgression\").addClass(\"hidden\");$(\"#hidden-PlanningProgression\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>
				<hr class=\"blue\"/>";

// Initialisation de l'objet PlanningHTMLHelper sur 60 jours à compter du 01/01/2017
// Remarque : ici les semaines se terminent toutes le vendredi à 16:00
$oPlanningCalendar = new PlanningHTMLHelper("01/01/2017", 60, 8, 19, "0-8,13,18-23", "5:16-23,6-7", 60);
// Rendu du planning sous forme de calendrier
$oPlanningCalendar->setPlanningRender(PlanningHTMLHelper::FORMAT_CALENDAR);
// Ajout d'un jour férié : ici il sera visible !
$oPlanningCalendar->addDateToDeprecated("01/01/2017");
// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningCalendar->setModalAction("/planning/tache");

// Création d'une entrée sur le PLANNING
$oItem_2017_01_02_09		= new Planning_ItemHelper(1, "Administration", "Remise des documents");
// Attribution de la date et l'heure de l'événement
$oItem_2017_01_02_09->setDateTime("02/01/2017 09:00");
// Attribution de la durée de l'événement
$oItem_2017_01_02_09->setDuration(4);
// Ajout d'un participant PRINCIPAL à l'événement
$oItem_2017_01_02_09->setParticipant(1, "John DOE", Planning_ItemHelper::TYPE_PRINCIPAL);
// Ajout de l'entrée au PLANNING
$oPlanningCalendar->addItem($oItem_2017_01_02_09);

// Ajout du Datatable à l'article
$sArticle	.= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>PlanningHTMLHelper</span> avec le rendu <span class='strong red italic'>PlanningHTMLHelper::</span><span class='strong italic'>FORMAT_CALENDAR</legend>
					" . $oPlanningCalendar->renderHTML() . "
					<p id='hidden-PlanningCalendar' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-PlanningCalendar\").addClass(\"hidden\");$(\"#visible-PlanningCalendar\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class='commentaire'>// Initialisation de l'objet PlanningHTMLHelper sur 60 jours à compter du 01/01/2017</span><br/>
						<span class='commentaire'>// Remarque : ici les semaines se terminent toutes le vendredi à 16:00</span><br/>
						<span class='variable'>\$oPlanningCalendar</span> = <span class='native'>new</span> PlanningHTMLHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [d/m/Y]\">\"01/01/2017\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">60</span>, <span class='nombre pointer hover-bold' title=\"Heure de début de chaque jour de la semaine\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin de chaque jour de la semaine\">19</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"0-8,13,18-23\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
						<span class='commentaire'>// Rendu du planning sous forme de calendrier</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->setPlanningRender(PlanningHTMLHelper::<span class='methode italic'>FORMAT_CALENDAR</span>);<br/>
						<span class='commentaire'>// Ajout d'un jour férié : ici il sera visible !</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date du jour à enregistrer comme non travaillé au format [d/m/Y]\">\"01/01/2017\"</span>);<br/>
						<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
						<br />
						<span class='commentaire'>// Création d'une entrée sur le PLANNING</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span> = <span class='native'>new</span> Planning_ItemHelper(<span class='nombre pointer hover-bold' title=\"Identifiant de la tâche\">1</span>, <span class='texte pointer hover-bold' title=\"Titre de la tâche\">\"Administration\"</span>, <span class='texte pointer hover-bold' title=\"Description de la tâche\">\"Remise des documents\"</span>);<br/>
						<span class='commentaire'>// Attribution de la date et l'heure de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDateTime(<span class='texte pointer hover-bold' title=\"Enregistrement de la date et l'heure de la tâche au format [d/m/Y H:i]\">\"02/01/2017 09:00\"</span>);<br/>
						<span class='commentaire'>// Attribution de la durée de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDuration(<span class='nombre pointer hover-bold' title=\"Renseignement de la durée de l'événement (1 par défaut)\">4</span>);<br/>
						<span class='commentaire'>// Ajout d'un participant PRINCIPAL à l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setParticipant(<span class='nombre pointer hover-bold' title=\"Identifiant du groupe associé à la liste des participants\">1</span>, <span class='texte pointer hover-bold' title=\"Nom du participant\">\"John DOE\"</span>, Planning_ItemHelper::<span class='methode italic'>TYPE_PRINCIPAL</span>);<br/>
						<span class='commentaire'>// Ajout de l'entrée au PLANNING</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->addItem(<span class='variable pointer hover-bold' title=\"Instance de Planning_ItemHelper\">\$oItem_2017_01_02_09</span>);<br/>
						<br />
						<span class='commentaire'>// Rendu final sous forme de code HTML</span><br/>
						<span class='native'>print</span> <span class='variable'>\$oPlanningCalendar</span>->renderHTML();
					</p>
					<p id='visible-PlanningCalendar' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-PlanningCalendar\").addClass(\"hidden\");$(\"#hidden-PlanningCalendar\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>
				<hr class=\"blue\"/>
				<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>PlanningPDFHelper</span> permettant de créer une progression au format PDF</legend>
					<p class=\"center\">
						<a href='/planning/exemple' class=\"button green\" target=\"_blank\">Exemple de planning au format PDF</a>
					</p>
					<p id='hidden-PlanningPDF' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-PlanningPDF\").addClass(\"hidden\");$(\"#visible-PlanningPDF\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class='commentaire'>// Initialisation de l'objet PlanningPDFHelper sur une semaine de 5 jours à compter du 02/01/2017</span><br/>
						<span class='variable'>\$oPlanningPDF</span> = <span class='native'>new</span> PlanningPDFHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [Y-m-d]\">\"2017-01-02\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">5</span>, <span class='nombre pointer hover-bold' title=\"Heure de début de chaque jour de la semaine\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin de chaque jour de la semaine\">19</span>, <span class='nombre pointer hover-bold' title=\"Heure du repas\">13</span>, <span class='nombre pointer hover-bold' title=\"Durée du repas\">1</span>);<br/>
						<span class='commentaire'>// Nom de la formation</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->setFormationName(<span class='texte pointer hover-bold' title=\"Nom donnée à la formation\">\"EXEMPLE\"</span>);<br/>
						<span class='commentaire'>// Titre du signataire du document</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->setSignataireTitre(<span class='texte pointer hover-bold' title=\"Nom porté par la personne signataire\">\"M. Martin DUPONT,\"</span>);<br/>
						<span class='commentaire'>// Fonction du signataire du document</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->setSignataireFonction(<span class='texte pointer hover-bold' title=\"Libellé de la responsabilité du signataire\">\"responsable de la progression.\"</span>);<br/>
						<span class='commentaire'>// Date de signature du document</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->setSignataireDate(<span class='texte pointer hover-bold' title=\"Date de signature du document\">\"2016-12-22\"</span>);<br/>
						<span class='commentaire'>// Information en bas du document</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->setLegend(<span class='texte pointer hover-bold' title=\"Texte affiché en dessous de la progression\">\"Toute modification fera l'objet d'un nouveau document.\"</span>);<br/>
						<br />
						<span class='commentaire'>// Création d'une entrée sur le PLANNING</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span> = <span class='native'>new</span> Planning_ItemHelper(<span class='nombre pointer hover-bold' title=\"Identifiant de la tâche\">1</span>, <span class='texte pointer hover-bold' title=\"Titre de la tâche\">\"Administration\"</span>, <span class='texte pointer hover-bold' title=\"Description de la tâche\">\"Remise des documents\"</span>);<br/>
						<span class='commentaire'>// Attribution de la date et l'heure de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDateTime(<span class='texte pointer hover-bold' title=\"Enregistrement de la date et l'heure de la tâche au format [Y-m-d H:i]\">\"2017-01-02 09:00\"</span>);<br/>
						<span class='commentaire'>// Attribution de la durée de l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setDuration(<span class='nombre pointer hover-bold' title=\"Renseignement de la durée de l'événement (1 par défaut)\">4</span>);<br/>
						<span class='commentaire'>// Ajout d'un participant PRINCIPAL à l'événement</span><br/>
						<span class='variable'>\$oItem_2017_01_02_09</span>->setParticipant(<span class='nombre pointer hover-bold' title=\"Identifiant du groupe associé à la liste des participants\">1</span>, <span class='texte pointer hover-bold' title=\"Nom du participant\">\"John DOE\"</span>, Planning_ItemHelper::<span class='methode italic'>TYPE_PRINCIPAL</span>);<br/>
						<span class='commentaire'>// Ajout de l'entrée au PLANNING</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->addItem(<span class='variable pointer hover-bold' title=\"Instance de Planning_ItemHelper\">\$oItem_2017_01_02_09</span>);<br/>
						<br />
						<span class='commentaire'>// Construction de la page du document après avoir ajouté tous les éléments</span><br/>
						<span class='variable'>\$oPlanningPDF</span>->buildProgressionPage();<br/>
						<br />
						<span class='commentaire'>// Rendu final sous forme de document PDF</span><br/>
						<span class='native'>print</span> <span class='variable'>\$oPlanningPDF</span>->renderPDF(<span class='texte pointer hover-bold' title=\"Nom du fichier généré\">\"Exemple de progression PDF\");
					</p>
					<p id='visible-PlanningPDF' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-PlanningPDF\").addClass(\"hidden\");$(\"#hidden-PlanningPDF\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>
				<hr class=\"blue\"/>";