<?php
/**
 * Documentation sur l'exploitation de l'objet ArborescenceHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;

// Liste exploitant des INTERVALLES à transformer en arborescence
$aListeAvecIntervalles	= array(
	// Occurrence    | Identifiant    | Libellé	             | Borne GAUCHE    | Borne DROITE
	0	=> array('id' => 0,		'label' => "public",	'left' => 1,	'right' =>	2),
	1	=> array('id' => 1,		'label' => "1",			'left' => 3,	'right' =>	8),
	2	=> array('id' => 2,		'label' => "1.1",		'left' => 4,	'right' =>	5),
	3	=> array('id' => 3,		'label' => "1.2",		'left' => 6,	'right' =>	7),
	4	=> array('id' => 4,		'label' => "2",			'left' => 9,	'right' =>	28),
	5	=> array('id' => 5,		'label' => "2.1",		'left' => 10,	'right' =>	15),
	6	=> array('id' => 8,		'label' => "2.2",		'left' => 16,	'right' =>	27),
	7	=> array('id' => 6,		'label' => "2.1.1",		'left' => 11,	'right' =>	12),
	8	=> array('id' => 7,		'label' => "2.1.2",		'left' => 13,	'right' =>	14),
	9	=> array('id' => 9,		'label' => "2.2.1",		'left' => 17,	'right' =>	24),
	10	=> array('id' => 13,	'label' => "2.2.2",		'left' => 25,	'right' =>	26),
	11	=> array('id' => 10,	'label' => "2.2.1.1",	'left' => 18,	'right' =>	19),
	12	=> array('id' => 11,	'label' => "2.2.1.2",	'left' => 20,	'right' =>	21),
	13	=> array('id' => 12,	'label' => "2.2.1.3",	'left' => 22,	'right' =>	23)
);

// Liste exploitant un IDENTIFIANT PARENT à transformer en arborescence
$aListeAvecParent		= array(
	0	=> array('id' => 0,		'parent' => NULL,	'label' => 'public'),
	1	=> array('id' => 1,		'parent' => NULL,	'label' => '1'),
	2	=> array('id' => 4,		'parent' => NULL,	'label' => '2'),
	3	=> array('id' => 2,		'parent' => 1,		'label' => '1.1'),
	4	=> array('id' => 3,		'parent' => 1,		'label' => '1.2'),
	5	=> array('id' => 5,		'parent' => 4,		'label' => '2.1'),
	6	=> array('id' => 8,		'parent' => 4,		'label' => '2.2'),
	7	=> array('id' => 6,		'parent' => 5,		'label' => '2.1.1'),
	8	=> array('id' => 7,		'parent' => 5,		'label' => '2.1.2'),
	9	=> array('id' => 9,		'parent' => 8,		'label' => '2.2.1'),
	10	=> array('id' => 13,	'parent' => 8,		'label' => '2.2.2'),
	11	=> array('id' => 10,	'parent' => 9,		'label' => '2.2.1.1'),
	12	=> array('id' => 11,	'parent' => 9,		'label' => '2.2.1.2'),
	13	=> array('id' => 12,	'parent' => 9,		'label' => '2.2.1.3')
);

// Liste exploitant des SOUS-ENSEMBLES à transformer en arborescence
$aListeImbriquee = array(
	0	=> array('public'				=> null),
	1	=> array('1'					=> array(
		2	=> array('1.1'				=> null),
		3	=> array('1.2'				=> null)
	)),
	4	=> array('2'					=> array(
		5	=> array('2.1'				=> array(
			6	=> array('2.1.1'		=> null),
			7	=> array('2.1.1'		=> null)
		)),
		8	=> array('2.2'				=> array(
			9	=> array('2.2.1'		=> array(
				10	=> array('2.2.1.1'	=> null),
				11	=> array('2.2.1.2'	=> null),
				12	=> array('2.2.1.3'	=> null)
			)),
			13	=> array('2.2.2'		=> null)
		))
	))
);

// Initialisation de l'objet ArborescenceHelper
$oArborescence = new ArborescenceHelper('ListeAvecIntervalles', false);

// Initialisation de la déclaration des champs nécessaires à la manipulation de la variable `$aListeAvecIntervalles`
$oArborescence->setIdPosition('id');					// Identifiant de champ
$oArborescence->setLabelPositionInterval('label');		// Libellé du champ
$oArborescence->setLeftPosition('left');				// Borne GAUCHE de l'intervalle
$oArborescence->setRightPosition('right');				// Borne DROITE de l'intervalle

// Transformation de la passée en paramètre en arborescence
$oArborescence->setListeItemsFromIntervalles($aListeAvecIntervalles);

// Ajout du Datatable à l'article
$sArticle .= "<fieldset class='padding-H-2p'>
					<legend>Exploitation de l'objet <span class='strong italic'>ArborescenceHelper</span></legend>
					<div class='code right padding-5'>
						<span class=\"commentaire\">// Exemple de construction d'une liste avec des intervalles</span><br />
						<span class=\"variable\">\$aListeAvecIntervalles</span> = <span class=\"native\">array</span>(
						<ul class='margin-right-0'>
							<li class=\"commentaire\">// Occurrence    | Identifiant    | Libellé	             | Borne GAUCHE    | Borne DROITE</li>
							<li><span class=\"nombre\">&nbsp;0</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;0</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"public\"</span>,&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_0\" class=\"nombre\">&nbsp;1</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_0\" class=\"nombre\">&nbsp;2</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_0\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;1</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;1</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_1\" class=\"nombre\">&nbsp;3</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_1\" class=\"nombre\">&nbsp;8</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_1\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;2</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;2</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1.1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_2\" class=\"nombre\">&nbsp;4</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_2\" class=\"nombre\">&nbsp;5</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_2\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;3</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;3</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1.2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_3\" class=\"nombre\">&nbsp;6</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_3\" class=\"nombre\">&nbsp;7</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_3\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;4</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;4</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_4\" class=\"nombre\">&nbsp;9</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_4\" class=\"nombre\">28</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_4\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;5</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;5</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_5\" class=\"nombre\">10</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_5\" class=\"nombre\">15</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_5\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;6</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;8</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_8\" class=\"nombre\">16</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_8\" class=\"nombre\">27</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_8\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;7</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;6</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1.1\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_6\" class=\"nombre\">11</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_6\" class=\"nombre\">12</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_6\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;8</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;7</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1.2\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_7\" class=\"nombre\">13</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_7\" class=\"nombre\">14</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_7\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">&nbsp;9</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">&nbsp;9</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_9\" class=\"nombre\">17</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_9\" class=\"nombre\">24</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_9\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">10</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">13</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.2\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_13\" class=\"nombre\">25</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_13\" class=\"nombre\">26</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_13\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">11</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">10</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.1\"</span>,&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_10\" class=\"nombre\">18</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_10\" class=\"nombre\">19</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_10\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">12</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">11</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.2\"</span>,&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_11\" class=\"nombre\">20</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_11\" class=\"nombre\">21</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_11\" class=\"nombre\">&nbsp;0</span>),</li>
							<li><span class=\"nombre\">13</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">12</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.3\"</span>,&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span id=\"text_left_12\" class=\"nombre\">22</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span id=\"text_right_12\" class=\"nombre\">23</span>,&nbsp;<span class=\"texte\">'changed'</span>&nbsp;=>&nbsp;<span id=\"text_changed_12\" class=\"nombre\">&nbsp;0</span>)</li>
						</ul>
						);
					</div>
					" . $oArborescence->renderHTML() . "
					<p id='hidden-ArborescenceHelper' class='code padding-2p hidden width-96p'>
						<button onClick='$(\"#hidden-ArborescenceHelper\").addClass(\"hidden\");$(\"#visible-ArborescenceHelper\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet ArborescenceHelper</span><br/>
						<span class=\"variable\">\$oArborescence</span> = <span class=\"native\">new</span> ArborescenceHelper(<span class=\"texte\">\"ListeAvecIntervalles\"</span>, <span class=\"native\">true</span>);<br/>
						<br />
						<span class=\"commentaire\">// Initialisation de la déclaration des champs nécessaires à la manipulation de la variable `\$aListeAvecIntervalles`</span><br/>
						<span class=\"variable\">\$oArborescence</span>->setIdPosition(<span class=\"texte\">\"id\"</span>);<span class=\"indentation margin-left-9\">&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Identifiant du champ</span><br />
						<span class=\"variable\">\$oArborescence</span>->setLabelPositionInterval(<span class=\"texte\">\"label\"</span>);<span class=\"indentation margin-left-1\">&rarr;&nbsp;</span><span class=\"commentaire\">// Libellé du champ</span><br />
						<span class=\"variable\">\$oArborescence</span>->setLeftPosition(<span class=\"texte\">\"left\"</span>);<span class=\"indentation margin-left-3\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span></span><span class=\"commentaire\">// Borne GAUCHE de l'intervalle</span><br />
						<span class=\"variable\">\$oArborescence</span>->setRightPosition(<span class=\"texte\">\"right\"</span>);<span class=\"indentation\">&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span></span><span class=\"commentaire\">// Borne DROITE de l'intervalle</span><br />
						<br />
						<span class=\"commentaire\">// Transformation de la liste avec des intervalles passée en paramètre en arborescence</span><br/>
						<span class=\"variable\">\$oArborescence</span>->setListeItemsFromIntervalles(<span class=\"variable\">\$aListeAvecIntervalles</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oArborescence</span>->renderHTML();
					</p>
					<p id='visible-ArborescenceHelper' class='code padding-H-20 transparent max-width'>
						<button onClick='$(\"#visible-ArborescenceHelper\").addClass(\"hidden\");$(\"#hidden-ArborescenceHelper\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>";

// Mise à jour des intervalles dans le tableau à chaque modification de l'utilisateur
ViewRender::addToJQuery('	$("section.racine").ready(function() {
							$("section.racine").on("change", "input[name^=item_changed]", function(event) {
								// Protection contre la propagation intempestive
								event.stopPropagation();
								
								// Attente de la fin de la propagation
								waitForFinalEvent(function() {
									// Parcours chaque élément
									$("input[name^=item_changed]").each(function() {
										// Récupération du parent
										var element				= $(this).parent("li[class*=branche]");
										
										// Récupération des paramètres
										var element_id			= element.children("input[name^=item_id]").val();
										var element_left		= element.children("input[name^=item_left]").val();
										var element_right		= element.children("input[name^=item_right]").val();
										var element_changed		= element.children("input[name^=item_changed]").val();
										
										// Actualisation de chaque BORNE
										$("span#text_left_"		+ element_id).text(element_left <= 9	? " " + element_left	: element_left);
										$("span#text_right_"	+ element_id).text(element_right <= 9	? " " + element_right	: element_right);
										$("span#text_changed_"	+ element_id).text(element_changed <= 9	? " " + element_changed	: element_changed);
									});
								}, 500);
							});
						});');
