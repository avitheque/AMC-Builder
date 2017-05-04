<?php
/**
 * Documentation sur l'exploitation de l'objet DatatableHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Initialisation de l'objet DatatableHelper à partir de la liste
$oDataTable	= new DatatableHelper("DatatableHelper", $aListeItem);

// Attribution d'une classe CSS à la 1ère colonne par son nom
$oDataTable->setClassColumn("align-left strong",	"column_1");

// Formatage des colonnes en type DATE
$oDataTable->setFormatOnColumn('date_debut',	DataHelper::DATA_TYPE_DATE);
$oDataTable->setFormatOnColumn('date_fin',		DataHelper::DATA_TYPE_DATE);

// Renommage des colonnes et mascage de celles qui n'y sont pas présentes
$oDataTable->renameColumns($aTitreItem, true);

// Ajout du Datatable à l'article
$sArticle .= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>DatatableHelper</span></legend>
					" . $oDataTable->renderHTML() . "
					<p id='hidden-DatatableHelper' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-DatatableHelper\").addClass(\"hidden\");$(\"#visible-DatatableHelper\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet DatatableHelper à partir de la liste</span><br/>
						<span class=\"variable\">\$oDataTable</span> = <span class=\"native\">new</span> DatatableHelper(<span class=\"texte\">\"DatatableHelper\"</span>, <span class=\"variable\">\$aListeItem</span>);<br/>
						<br />
						<span class=\"commentaire\">// Attribution d'une classe CSS à la 1ère colonne par son nom</span><br/>
						<span class=\"variable\">\$oDataTable</span>->setClassColumn(<span class=\"texte\">\"align-left strong\"</span>, <span class=\"texte\">\"column_1\"</span>);<br />
						<br />
						<span class=\"commentaire\">// Formatage des colonnes en type DATE</span><br/>
						<span class=\"variable\">\$oDataTable</span>->setFormatOnColumn(<span class=\"texte\">'date_debut'</span>, DataHelper::<span class=\"methode italic\">DATA_TYPE_DATE</span>);<br />
						<span class=\"variable\">\$oDataTable</span>->setFormatOnColumn(<span class=\"texte\">'date_fin'</span>, DataHelper::<span class=\"methode italic\">DATA_TYPE_DATE</span>);<br />
						<br />
						<span class=\"commentaire\">// Renommage des colonnes et mascage de celles qui n'y sont pas présentes</span><br/>
						<span class=\"variable\">\$oDataTable</span>->renameColumns(<span class=\"variable\">\$aTitreItem</span>, <span class=\"native\">true</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oDataTable</span>->renderHTML();
					</p>
					<p id='visible-DatatableHelper' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-DatatableHelper\").addClass(\"hidden\");$(\"#hidden-DatatableHelper\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>";
