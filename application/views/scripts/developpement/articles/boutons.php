<?php
/**
 * Documentation sur l'exploitation des boutons
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:33
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Présentation des différents boutons
$sArticle .= "<fieldset><legend>Boutons de l'application</legend>
				<div class=\"exemple\">
					<span class=\"titre width-200\">Élement INPUT</span>
					<input type=\"button\" value=\"Enable\"/> <input type=\"button\" value=\"Disabled\" disabled />
				</div>
				<div class=\"exemple\">
					<span class=\"titre width-200\">Élement BUTTON</span>
					<button type=\"button\">Enable</button> <button type=\"button\" disabled>Disabled</button>
					<button class=\"red\">class=\"red\"</button>
					<button class=\"green\">class=\"green\"</button>
					<button class=\"blue\">class=\"blue\"</button>
				</div>
				<div class=\"exemple\">
					<span class=\"titre width-200\">Élement ANCHOR</span>
					<a href=\"#\" class=\"button\">class=\"button\"</a>
					<a href=\"#\" class=\"button red\">class=\"button red\"</a>
					<a href=\"#\" class=\"button green\">class=\"button green\"</a>
					<a href=\"#\" class=\"button blue\">class=\"button blue\"</a>
				</div>
			</fieldset>";
