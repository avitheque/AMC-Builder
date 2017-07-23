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
				<table class=\"exemple max-width\">
					<tr>
						<td class=\"width-45p\">
							<span class=\"strong titre right\">Élement INPUT</span><br />
							<span class=\"right\">&lt;input type=''button'' /&gt;</span>
						</td>
						<td class=\"width-5p\">
							&nbsp;
						</td>
						<td class=\"left half-width\">
							<ul>
								<li class=\"margin-V-5\">
									<input type=\"button\" value=\"Enable\"/>
								</li>
								<li class=\"margin-V-5\">
									<input type=\"button\" value=\"Disabled\" disabled />
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td colspan=3>
							<hr class=\"blue\">
						</td>
					</tr>
					<tr>
						<td class=\"width-45p\">
							<span class=\"strong titre right\">Élement BUTTON</span><br />
							<span class=\"right\">&lt;button&gt;&lt;/button&gt;</span>
						</td>
						<td class=\"width-5p\">
							&nbsp;
						</td>
						<td class=\"half-width\">
							<ul>
								<li class=\"margin-V-5\">
									<button type=\"button\">Enable</button>
								</li>
								<li class=\"margin-V-5\">
									<button type=\"button\" disabled>Disabled</button>
								</li>
								<li class=\"margin-V-5\">
									<button class=\"red\">class=\"red\"</button>
								</li>
								<li class=\"margin-V-5\">
									<button class=\"green\">class=\"green\"</button>
								</li>
								<li class=\"margin-V-5\">
									<button class=\"blue\">class=\"blue\"</button>
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td colspan=3>
							<hr class=\"blue\">
						</td>
					</tr>
					<tr>
						<td class=\"width-45p\">
							<span class=\"strong titre right\">Élement ANCHOR</span><br />
							<span class=\"right\">&lt;a class=''button''&gt;&lt;/a&gt;</span>
						</td>
						<td class=\"width-5p\">
							&nbsp;
						</td>
						<td class=\"left half-width\">
							<ul>
								<li class=\"margin-top-10\">
									<a href=\"#\" class=\"button\">class=\"button\"</a>
								</li>
								<li>
									&nbsp;
								</li>
								<li>
									<a href=\"#\" class=\"button disabled\">class=\"button disabled\"</a>
								</li>
								<li>
									&nbsp;
								</li>
								<li>
									<a href=\"#\" class=\"button red\">class=\"button red\"</a>
								</li>
								<li>
									&nbsp;
								</li>
								<li>
									<a href=\"#\" class=\"button green\">class=\"button green\"</a>
								</li>
								<li>
									&nbsp;
								</li>
								<li class=\"margin-bottom-10\">
									<a href=\"#\" class=\"button blue\">class=\"button blue\"</a>
								</li>
							</ul>
						</td>
					</tr>
				</table>
			</fieldset>";
