<?php
/**
 * Documentation sur l'exploitation des helpers.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:34
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $aListeItem;
global $aTitreItem;
global $sArticle;

// Initialisation de la liste à exploiter dans le Helper
$aListeItem	= array(
	array(
		// Key		=> VALEUR DE LA COLONNE
		'column_1'	=> "A1",
		'column_2'	=> "B1",
		'column_3'	=> "C1",
		'column_4'	=> "D1",
		'column_5'	=> "E1",
		'date_debut'=> "1970-01-01",
		'date_fin'	=> "1979-09-22"
	),
	array(
		// Key		=> VALEUR DE LA COLONNE
		'column_1'	=> "A2",
		'column_2'	=> "B2",
		'column_3'	=> "C2",
		'column_4'	=> "D2",
		'column_5'	=> "E2",
		'date_debut'=> "1979-09-22",
		'date_fin'	=> date("Y-m-d")
	),
	array(
		// Key		=> VALEUR DE LA COLONNE
		'column_1'	=> "A3",
		'column_2'	=> "B3",
		'column_3'	=> "C3",
		'column_4'	=> "D3",
		'column_5'	=> "E3",
		'date_debut'=> date("Y-m-d"),
		'date_fin'	=> "9999-12-31",
	)
);

// Personnalisation des noms de colonnes
$aTitreItem	= array(
	// Key		=> TITRE DE LA COLONNE
	'column_1'	=> "Titre A",
	'column_2'	=> "Titre B",
	'column_3'	=> "Titre C",
	'column_4'	=> "Titre D",
	'column_5'	=> "Titre E",
	'date_debut'=> "Début",
	'date_fin'	=> "Fin",
);

// Présentation de la variable $aTitreItem
$sArticle .= "	<div class='justify width-45p padding-left-5 right green'>Soit la variable <strong class='pointer italic'>\$aTitreItem</strong>
 					un tableau associatif entre les clés du tableau <strong class='italic blue'>\$aListeItem</strong>
 					et le libellé de colonne à afficher<br />
					<br/>
					<div id='hidden-aListe2' class='code black padding-H-20 hidden'>
						<span class=\"commentaire\">// Contenu de la variable \$aTitreItem</span><br/>
						<span class='variable'>\$aTitreItem</span> = " . DataHelper::debugArray($aTitreItem) . "
					</div>
				</div>";

// Présentation de la variable $aListeItem
$sArticle .= "	<div class='justify width-45p padding-right-5 blue border-right-solid'>Soit la variable <strong class='italic'>\$aListeItem</strong>
 					un tableau BIDIMENTIONNEL à afficher<br />
					<br/>
					<div id='hidden-aListe1' class='code black padding-H-20 hidden-aListe hidden'>
						<button onClick='$(\"#hidden-aListe1, #hidden-aListe2\").addClass(\"hidden\");$(\"#visible-aListe\").removeClass(\"hidden\");'>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Contenu de la variable \$aListeItem</span><br />
						<span class='variable'>\$aListeItem</span> = " . DataHelper::debugArray($aListeItem) . "
					</div>
					<div id='visible-aListe' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-aListe\").addClass(\"hidden\");$(\"#hidden-aListe1, #hidden-aListe2\").removeClass(\"hidden\");'>Voir le code</button>
					</div>
				</div>
				<br />
				<div class='max-width'>
					<section class='accordion'>";