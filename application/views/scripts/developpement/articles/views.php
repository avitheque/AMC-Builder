<?php
/**
 * Documentation sur l'exploitation des vues.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:34
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Présentation d'une redirection de vue
$sArticle .= "<fieldset><legend>Changement de la vue du contrôleur</legend>
				<span class=\"titre\">Il est possible de rediriger la vue vers un autre fichier que le fichier <span class=\"strong italic\">" . FW_DEFAULTVIEW . ".phtml</span> par défaut</span>
				<p>
					<a href=\"/developpement/redirection\" class=\"button\">Afficher la vue correspondant à <i>\"/developpement/redirection\"</i></a>
				</p>
			</fieldset>";

// Présentation d'un ajout de JavaScript
$sArticle .= "<hr/>
			<fieldset><legend>Insertion d'un JavaScript dans la page</legend>
				<span class=\"titre\">Il est possible d'insérer un script JavaScript au chargement de la page via la méthode de classe <span class=\"strong\">ViewRender::addToScripts()</span>.</span>
				<p>
					<a href=\"/developpement/javascript\" class=\"button\">Afficher une vue avec <i>JavaScript</i></a>
				</p>
			</fieldset>";

// Présentation d'une redirection vers une page générant une exception
$sArticle .= "<hr/>
			<fieldset><legend>Erreurs de l'application</legend>
				<div class=\"margin-left-20\">
					Lorsqu'une exception est relevée dans l'application, un message d'erreur est affiché dans la page.
					<br />
					Le contenu est enrichi quand le <span class=\"strong italic\">MODE_DEBUG</span> est actif.
				</div>
				<p>
					<a href=\"/exception\" class=\"button\">Voir un exemple de message d'exception...</a>
				</p>
			</fieldset>";
