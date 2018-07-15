<?php
/**
 * Documentation sur l'exploitation des tableaux.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Présentation du style CSS affecté aux tableaux
$sArticle .= "	<fieldset class='padding-H-20'>
					<legend>Style de tableau par défaut avec la classe CSS <strong class='red italic'>.table</strong></legend>
					<table class='table margin-auto'>
						<thead>
							<tr>
								<th>Titre A</th>
								<th>Titre B</th>
								<th>Titre C</th>
								<th>Titre D</th>
								<th>Titre E</th>
								<th>Titre F</th>
								<th>Titre G</th>
							</tr>
						</thead>
						<tbody>
							<tr class='odd'>
								<td>Cellule A1</td>
								<td>Cellule B1</td>
								<td>Cellule C1</td>
								<td>Cellule D1</td>
								<td>Cellule E1</td>
								<td>Cellule F1</td>
								<td>Cellule G1</td>
							</tr>
							<tr class='even'>
								<td>Cellule A2</td>
								<td>Cellule B2</td>
								<td>Cellule C2</td>
								<td>Cellule D2</td>
								<td>Cellule E2</td>
								<td>Cellule F2</td>
								<td>Cellule G2</td>
							</tr>
							<tr class='odd'>
								<td>Cellule A3</td>
								<td>Cellule B3</td>
								<td>Cellule C3</td>
								<td>Cellule D3</td>
								<td>Cellule E3</td>
								<td>Cellule F3</td>
								<td>Cellule G3</td>
							</tr>
							<tr class='even'>
								<td>Cellule A4</td>
								<td>Cellule B4</td>
								<td>Cellule C4</td>
								<td>Cellule D4</td>
								<td>Cellule E4</td>
								<td>Cellule F4</td>
								<td>Cellule G4</td>
							</tr>
							<tr class='odd'>
								<td>Cellule A5</td>
								<td>Cellule B5</td>
								<td>Cellule C5</td>
								<td>Cellule D5</td>
								<td>Cellule E5</td>
								<td>Cellule F5</td>
								<td>Cellule G5</td>
							</tr>
							<tr class='even'>
								<td>Cellule A6</td>
								<td>Cellule B6</td>
								<td>Cellule C6</td>
								<td>Cellule D6</td>
								<td>Cellule E6</td>
								<td>Cellule F6</td>
								<td>Cellule G6</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan=7>Bas de tableau</td>
							</tr>
						</tfoot>
					</table>
					<div id='hidden-tableau' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-tableau\").addClass(\"hidden\");$(\"#visible-tableau\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<strong class='blue'>&lt;table class=<span class='texte'>\"table margin-auto\"</span>&gt;<br />
						<ul>
							<li>&lt;thead&gt;
								<ul>&lt;tr&gt;
									<li>
										<ul>
											<li>
												&lt;th&gt;<span class='texte'>Titre&nbsp;A</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;B</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;C</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;D</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;E</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;F</span>&lt;/th&gt;
												&lt;th&gt;<span class='texte'>Titre&nbsp;G</span>&lt;/th&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								&lt;/thead&gt;
							</li>
							<li>&lt;tbody&gt;
								<ul>&lt;tr class=<span class='texte'>\"odd\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F1</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G1</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								<ul>&lt;tr class=<span class='texte'>\"even\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F2</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G2</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								<ul>&lt;tr class=<span class='texte'>\"odd\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F3</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G3</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								<ul>&lt;tr class=<span class='texte'>\"even\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F4</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G4</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								<ul>&lt;tr class=<span class='texte'>\"odd\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F5</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G5</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								<ul>&lt;tr class=<span class='texte'>\"even\"</span>&gt;
									<li>
										<ul>
											<li>
												&lt;td&gt;<span class='texte'>Cellule&nbsp;A6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;B6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;C6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;D6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;E6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;F6</span>&lt;/td&gt;
												&lt;td&gt;<span class='texte'>Cellule&nbsp;G6</span>&lt;/td&gt;
											</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								/tbody&gt;
							</li>
							<li>&lt;tfoot&gt;
								<ul>&lt;tr&gt;
									<li>
										<ul>
											<li>&lt;td colspan=<span class='nombre'>7</span>&gt;<span class='texte'>Bas de tableau</span>&lt;/td&gt;</li>
										</ul>
									</li>
									&lt;/tr&gt;
								</ul>
								&lt;/tfoot&gt;
							</li>
						</ul>
						&lt;/table&gt;</strong>
					</div>
					<p id='visible-tableau' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-tableau\").addClass(\"hidden\");$(\"#hidden-tableau\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>";
