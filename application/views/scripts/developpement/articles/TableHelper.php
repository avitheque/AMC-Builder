<?php
/**
 * Documentation sur l'exploitation de l'objet TableHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Début de la SECTION
$sArticle .= "<section id='TableHelper'>";

// ================================================================================================
// Utilisation simple
// ================================================================================================

// Initialisation de l'objet TableHelper à partir de la liste
$oTableau	= new TableHelper("TableHelper-simple", $aListeItem);

// Ajout du tableau à l'article
$sArticle .= "	<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>TableHelper</span> simple</legend>
					" . $oTableau->renderHTML() . "<br />
					<p id='hidden-simple' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-simple\").addClass(\"hidden\");$(\"#visible-simple\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet TableHelper à partir de la liste</span><br/>
						<span class=\"variable\">\$oTableau</span> = <span class=\"native\">new</span> TableHelper(<span class=\"texte\">\"TableHelper-simple\"</span>, <span class=\"variable\">\$aListeItem</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oTableau</span>->renderHTML();
					</p>
					<p id='visible-simple' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-simple\").addClass(\"hidden\");$(\"#hidden-simple\").removeClass(\"hidden\");'>Voire le code</button>
					</p>
				</fieldset><hr />";


// ================================================================================================
// + renommage des colonnes
// ================================================================================================

// Initialisation de l'objet TableHelper à partir de la liste
$oTableau	= new TableHelper("TableHelper-renameColumns", $aListeItem);

// @todo Renommage des noms de colonnes
$oTableau->renameColumns($aTitreItem);

// Ajout du tableau à l'article
$sArticle .= "	<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>TableHelper</span> avec renommage des colonnes</legend>
					" . $oTableau->renderHTML() . "
					<p id='hidden-renameColumns' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-renameColumns\").addClass(\"hidden\");$(\"#visible-renameColumns\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet TableHelper à partir de la liste</span><br/>
						<span class=\"variable\">\$oTableau</span> = <span class=\"native\">new</span> TableHelper(<span class=\"texte\">\"TableHelper-renameColumns\"</span>, <span class=\"variable\">\$aListeItem</span>);<br/>
						<br />
						<span class=\"commentaire\">// Renommage des noms de colonnes</span><br/>
						<span class=\"variable\">\$oTableau</span>->renameColumns(<span class=\"variable\">\$aTitreItem</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oTableau</span>->renderHTML();
					</p>
					<p id='visible-renameColumns' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-renameColumns\").addClass(\"hidden\");$(\"#hidden-renameColumns\").removeClass(\"hidden\");'>Voire le code</button>
					</p>
				</fieldset><hr />";


// ================================================================================================
// + suppression de colonnes
// ================================================================================================

// Initialisation de l'objet TableHelper à partir de la liste
$oTableau	= new TableHelper("TableHelper-removeColumn", $aListeItem);

// @todo Suppression des colonnes de DATE
$oTableau->removeColumns(array('date_debut', 'date_fin'));

// Renommage des noms de colonnes
$oTableau->renameColumns($aTitreItem);

// Ajout du tableau à l'article
$sArticle .= "	<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>TableHelper</span> avec suppression des colonnes <span class='blue italic'>date_debut</span> et <span class='blue italic'>date_fin</span></legend>
					" . $oTableau->renderHTML() . "
					<p id='hidden-removeColumn' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-removeColumn\").addClass(\"hidden\");$(\"#visible-removeColumn\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet TableHelper à partir de la liste</span><br/>
						<span class=\"variable\">\$oTableau</span> = <span class=\"native\">new</span> TableHelper(<span class=\"texte\">\"TableHelper-removeColumn\"</span>, <span class=\"variable\">\$aListeItem</span>);<br/>
						<br />
						<span class=\"commentaire\">// Suppression des colonnes de DATE</span><br/>
						<span class=\"variable\">\$oTableau</span>->removeColumns(<span class=\"native\">array</span>(<span class=\"texte\">'date_debut'</span>, <span class=\"texte\">'date_fin'</span>));<br />
						<br />
						<span class=\"commentaire\">// Renommage des noms de colonnes</span><br/>
						<span class=\"variable\">\$oTableau</span>->renameColumns(<span class=\"variable\">\$aTitreItem</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oTableau</span>->renderHTML();
					</p>
					<p id='visible-removeColumn' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-removeColumn\").addClass(\"hidden\");$(\"#hidden-removeColumn\").removeClass(\"hidden\");'>Voire le code</button>
					</p>
				</fieldset>";

// Finalisation de la SECTION
$sArticle .= "</section>";
