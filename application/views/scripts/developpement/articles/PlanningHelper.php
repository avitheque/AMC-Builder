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
$oPlanningHelper->setPlanningFormat(PlanningHelper::FORMAT_PROGRESSION);

// Ajout d'un jour férié : ici il ne sera pas visible !
$oPlanningHelper->addDateToDeprecated("2017-01-01");

// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningHelper->setModalAction("/planning/tache");

// Ajout du Datatable à l'article
$sArticle .= "<fieldset class='padding-H-20'>
							<legend>Exploitation de l'objet <span class='strong italic'>PlanningHelper</span> avec le rendu <span class='strong red italic'>PlanningHelper::</span><span class='strong italic'>FORMAT_PROGRESSION</span></legend>
							" . $oPlanningHelper->renderHTML() . "<br/>
							<p id='hidden-PlanningHelper' class='code padding-20 hidden'>
								<button onClick='$(\"#hidden-PlanningHelper\").addClass(\"hidden\");$(\"#visible-PlanningHelper\").removeClass(\"hidden\");'>Masquer le code</button><br />
								<br/>
								<span class='commentaire'>// Initialisation de l'objet PlanningHelper sur une semaine de 7 jours à compter du 02/01/2017</span><br/>
								<span class='commentaire'>// Remarque : ici la semaine se termine le vendredi à 16:00</span><br/>
								<span class='variable'>\$oPlanningHelper</span> = <span class='native'>new</span> PlanningHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [Y-m-d]\">\"2017-01-02\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">7</span>, <span class='nombre pointer hover-bold' title=\"Heure de début\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin\">18</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"8,13,18\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
								<span class='variable'>\$oPlanningHelper</span>->setPlanningFormat(PlanningHelper::<span class='methode italic'>FORMAT_PROGRESSION</span>);<br/>
								<br />
								<span class='commentaire'>// Ajout d'un jour férié : ici il ne sera pas visible !</span><br/>
								<span class='variable'>\$oPlanningHelper</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date au format [Y-m-d]\">\"2017-01-01\"</span>);<br/>
								<br />
								<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
								<span class='variable'>\$oPlanningHelper</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
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
$oPlanningCalendar->setPlanningFormat(PlanningHelper::FORMAT_CALENDAR);

// Ajout d'un jour férié : ici il sera visible !
$oPlanningCalendar->addDateToDeprecated("01/01/2017");

// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL
$oPlanningCalendar->setModalAction("/planning/tache");

// Ajout du Datatable à l'article
$sArticle .= "<fieldset class='padding-H-20'>
							<legend>Exploitation de l'objet <span class='strong italic'>PlanningHelper</span> avec le rendu <span class='strong red italic'>PlanningHelper::</span><span class='strong italic'>FORMAT_CALENDAR</legend>
							" . $oPlanningCalendar->renderHTML() . "<br /><br />
							<p id='hidden-PlanningHelper-Calendar' class='code padding-20 hidden'>
								<button onClick='$(\"#hidden-PlanningHelper-Calendar\").addClass(\"hidden\");$(\"#visible-PlanningHelper-Calendar\").removeClass(\"hidden\");'>Masquer le code</button><br />
								<br/>
								<span class='commentaire'>// Initialisation de l'objet PlanningHelper sur 60 jours à compter du 01/01/2017</span><br/>
								<span class='commentaire'>// Remarque : ici les semaines se terminent toutes le vendredi à 16:00</span><br/>
								<span class='variable'>\$oPlanningCalendar</span> = <span class='native'>new</span> PlanningHelper(<span class='texte pointer hover-bold' title=\"Date de début au format [d/m/Y]\">\"01/01/2017\"</span>, <span class='nombre pointer hover-bold' title=\"Nombre de jours à afficher\">60</span>, <span class='nombre pointer hover-bold' title=\"Heure de début\">8</span>, <span class='nombre pointer hover-bold' title=\"Heure de fin\">18</span>, <span class='texte pointer hover-bold' title=\"Liste des heures non travaillées\">\"0-8,13,18-23\"</span>, <span class='texte pointer hover-bold' title=\"Liste des identifiants de jours non travaillés\n1 = Lundi ;\n2 = Mardi ;\n3 = Mercredi ;\n4 = Jeudi ;\n5 = Vendredi ;\n6 = Samedi ;\n7 = Dimanche\">\"5:16-23,6-7\"</span>, <span class='nombre pointer hover-bold' title=\"Taille d'un bloc en minutes\">60</span>);<br/>
								<span class='variable'>\$oPlanningCalendar</span>->setPlanningFormat(PlanningHelper::<span class='methode italic'>FORMAT_CALENDAR</span>);<br/>
								<br />
								<span class='commentaire'>// Ajout d'un jour férié : ici il sera visible !</span><br/>
								<span class='variable'>\$oPlanningCalendar</span>->addDateToDeprecated(<span class='texte pointer hover-bold' title=\"Date au format [d/m/Y]\">\"01/01/2017\"</span>);<br/>
								<br />
								<span class='commentaire'>// Chemin du moteur de recherche des tâches : bouton [Rechercher] du MODAL</span><br/>
								<span class='variable'>\$oPlanningCalendar</span>->setModalAction(<span class='texte pointer hover-bold' title=\"URL exploitée par le formulaire de recherche\">\"/planning/tache\"</span>);<br/>
								<br />
								<span class='commentaire'>// Rendu final sous forme de code HTML</span><br/>
								<span class='native'>print</span> <span class='variable'>\$oPlanningCalendar</span>->renderHTML();
							</p>
							<p id='visible-PlanningHelper-Calendar' class='code padding-H-20 transparent'>
								<button onClick='$(\"#visible-PlanningHelper-Calendar\").addClass(\"hidden\");$(\"#hidden-PlanningHelper-Calendar\").removeClass(\"hidden\");'>Voir le code</button>
							</p>
						</fieldset>";
