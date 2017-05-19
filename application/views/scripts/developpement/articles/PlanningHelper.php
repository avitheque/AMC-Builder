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
// Initialisation de l'objet PlanningHelper sur une semaine de 7 jours à compter du 02/01/2017
// Remarque : ici la semaine se termine le vendredi à 16:00
$oPlanningHelper = new PlanningHelper("2017-01-02", 7, 8, 18, "8,13,18", "5:16-23,6,7", 60);
// Rendu du planning sous forme de progression
$oPlanningHelper->setPlanningRender(PlanningHelper::FORMAT_PROGRESSION);
// Ajout d'un jour férié : ici il ne sera pas visible !
$oPlanningHelper->addDateToDeprecated("2017-01-01");
// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningHelper->setModalAction("/planning/tache");

// Création d'une entrée sur le PLANNING
$oItem		= new Planning_ItemHelper(1, "Administration", "Remise des documents");
// Attribution de la durée de l'événement
$oItem->setDuration(4);
// Attribution de la date et l'heure de l'événement
$oItem->setDateTime("2017-01-02 09:00");
// Ajout de l'entrée au PLANNING
$oPlanningHelper->addItem($oItem);

// Ajout du Datatable à l'article
$sArticle	.= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>PlanningHelper</span> avec le rendu <span class='strong red italic'>PlanningHelper::</span><span class='strong italic'>FORMAT_PROGRESSION</span></legend>
					" . $oPlanningHelper->renderHTML() . "
					<p id='hidden-PlanningHelper' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-PlanningHelper\").addClass(\"hidden\");$(\"#visible-PlanningHelper\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class='commentaire'>// Initialisation de l'objet PlanningHelper sur une semaine de 7 jours à compter du 02/01/2017</span><br/>
						<span class='commentaire'>// Remarque : ici la semaine se termine le vendredi à 16:00</span><br/>
						<span class='variable'>\$oPlanningHelper</span> = <span class='native'>new</span> PlanningHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [Y-m-d]\">\"2017-01-02\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">7</span>, <span class='nombre pointer hover-bold' title=\"Heure de début\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin\">18</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"8,13,18\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
						<span class='commentaire'>// Rendu du planning sous forme de progression</span><br/>
						<span class='variable'>\$oPlanningHelper</span>->setPlanningRender(PlanningHelper::<span class='methode italic'>FORMAT_PROGRESSION</span>);<br/>
						<span class='commentaire'>// Ajout d'un jour férié : ici il ne sera pas visible !</span><br/>
						<span class='variable'>\$oPlanningHelper</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date du jour à enregistrer comme non travaillé au format [Y-m-d]\">\"2017-01-01\"</span>);<br/>
						<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
						<span class='variable'>\$oPlanningHelper</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
						<br />
						<span class='commentaire'>// Création d'une entrée sur le PLANNING</span><br/>
						<span class='variable'>\$oItem</span> = <span class='native'>new</span> Planning_ItemHelper(<span class='nombre pointer hover-bold' title=\"Identifiant de la tâche\">1</span>, <span class='texte pointer hover-bold' title=\"Titre de la tâche\">\"Administration\"</span>, <span class='texte pointer hover-bold' title=\"Description de la tâche\">\"Remise des documents\"</span>);<br/>
						<span class='commentaire'>// Attribution de la date et l'heure de l'événement</span><br/>
						<span class='variable'>\$oItem</span>->setDateTime(<span class='texte pointer hover-bold' title=\"Enregistrement de la date et l'heure de la tâche au format [Y-m-d H:i]\">\"2017-01-02 09:00\"</span>);<br/>
						<span class='commentaire'>// Attribution de la durée de l'événement</span><br/>
						<span class='variable'>\$oItem</span>->setDuration(<span class='nombre pointer hover-bold' title=\"Renseignement de la durée de l'événement (1 par défaut)\">4</span>);<br/>
						<span class='commentaire'>// Ajout de l'entrée au PLANNING</span><br/>
						<span class='variable'>\$oPlanningHelper</span>->addItem(<span class='variable pointer hover-bold' title=\"Instance de Planning_ItemHelper\">\$oItem</span>);<br/>
						<br />
						<span class='commentaire'>// Rendu final sous forme de code HTML</span><br/>
						<span class='native'>print</span> <span class='variable'>\$oPlanningHelper</span>->renderHTML();
					</p>
					<p id='visible-PlanningHelper' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-PlanningHelper\").addClass(\"hidden\");$(\"#hidden-PlanningHelper\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>
				<hr class=\"blue\"/>";

// Initialisation de l'objet PlanningHelper sur 60 jours à compter du 01/01/2017
// Remarque : ici les semaines se terminent toutes le vendredi à 16:00
$oPlanningCalendar = new PlanningHelper("01/01/2017", 60, 8, 18, "0-8,13,18-23", "5:16-23,6-7", 60);
// Rendu du planning sous forme de calendrier
$oPlanningCalendar->setPlanningRender(PlanningHelper::FORMAT_CALENDAR);
// Ajout d'un jour férié : ici il sera visible !
$oPlanningCalendar->addDateToDeprecated("01/01/2017");
// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningCalendar->setModalAction("/planning/tache");

// Création d'une entrée sur le PLANNING
$oItem		= new Planning_ItemHelper(1, "Administration", "Remise des documents");
// Attribution de la date et l'heure de l'événement
$oItem->setDateTime("02/01/2017 09:00");
// Attribution de la durée de l'événement
$oItem->setDuration(4);
// Ajout de l'entrée au PLANNING
$oPlanningCalendar->addItem($oItem);

// Ajout du Datatable à l'article
$sArticle	.= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>PlanningHelper</span> avec le rendu <span class='strong red italic'>PlanningHelper::</span><span class='strong italic'>FORMAT_CALENDAR</legend>
					" . $oPlanningCalendar->renderHTML() . "
					<p id='hidden-PlanningHelper-Calendar' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-PlanningHelper-Calendar\").addClass(\"hidden\");$(\"#visible-PlanningHelper-Calendar\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class='commentaire'>// Initialisation de l'objet PlanningHelper sur 60 jours à compter du 01/01/2017</span><br/>
						<span class='commentaire'>// Remarque : ici les semaines se terminent toutes le vendredi à 16:00</span><br/>
						<span class='variable'>\$oPlanningCalendar</span> = <span class='native'>new</span> PlanningHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [d/m/Y]\">\"01/01/2017\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">60</span>, <span class='nombre pointer hover-bold' title=\"Heure de début\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin\">18</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"0-8,13,18-23\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
						<span class='commentaire'>// Rendu du planning sous forme de calendrier</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->setPlanningRender(PlanningHelper::<span class='methode italic'>FORMAT_CALENDAR</span>);<br/>
						<span class='commentaire'>// Ajout d'un jour férié : ici il sera visible !</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date du jour à enregistrer comme non travaillé au format [d/m/Y]\">\"01/01/2017\"</span>);<br/>
						<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
						<br />
						<span class='commentaire'>// Création d'une entrée sur le PLANNING</span><br/>
						<span class='variable'>\$oItem</span> = <span class='native'>new</span> Planning_ItemHelper(<span class='nombre pointer hover-bold' title=\"Identifiant de la tâche\">1</span>, <span class='texte pointer hover-bold' title=\"Titre de la tâche\">\"Administration\"</span>, <span class='texte pointer hover-bold' title=\"Description de la tâche\">\"Remise des documents\"</span>);<br/>
						<span class='commentaire'>// Attribution de la date et l'heure de l'événement</span><br/>
						<span class='variable'>\$oItem</span>->setDateTime(<span class='texte pointer hover-bold' title=\"Enregistrement de la date et l'heure de la tâche au format [d/m/Y H:i]\">\"02/01/2017 09:00\"</span>);<br/>
						<span class='commentaire'>// Attribution de la durée de l'événement</span><br/>
						<span class='variable'>\$oItem</span>->setDuration(<span class='nombre pointer hover-bold' title=\"Renseignement de la durée de l'événement (1 par défaut)\">4</span>);<br/>
						<span class='commentaire'>// Ajout de l'entrée au PLANNING</span><br/>
						<span class='variable'>\$oPlanningCalendar</span>->addItem(<span class='variable pointer hover-bold' title=\"Instance de Planning_ItemHelper\">\$oItem</span>);<br/>
						<br />
						<span class='commentaire'>// Rendu final sous forme de code HTML</span><br/>
						<span class='native'>print</span> <span class='variable'>\$oPlanningCalendar</span>->renderHTML();
					</p>
					<p id='visible-PlanningHelper-Calendar' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-PlanningHelper-Calendar\").addClass(\"hidden\");$(\"#hidden-PlanningHelper-Calendar\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>";
