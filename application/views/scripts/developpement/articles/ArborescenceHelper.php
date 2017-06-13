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
$oArborescence = new ArborescenceHelper('ListeAvecIntervalles');

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
							<li><span class=\"nombre\">0</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span> => <span class=\"nombre\">&nbsp;0</span>,  <span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"public\"</span>,&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">1</span>,&nbsp;&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">2</span>),</li>
							<li><span class=\"nombre\">1</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">1</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">3</span>,&nbsp;&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">8</span>),</li>
							<li><span class=\"nombre\">2</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">2</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1.1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">4</span>,&nbsp;&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">5</span>),</li>
							<li><span class=\"nombre\">3</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">3</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"1.2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">6</span>,&nbsp;&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">7</span>),</li>
							<li><span class=\"nombre\">4</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">4</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">9</span>,&nbsp;&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">28</span>),</li>
							<li><span class=\"nombre\">5</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">5</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">10</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">15</span>),</li>
							<li><span class=\"nombre\">6</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">8</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2\"</span>,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">16</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">27</span>),</li>
							<li><span class=\"nombre\">7</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">6</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1.1\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">11</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">12</span>),</li>
							<li><span class=\"nombre\">8</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">7</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.1.2\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">13</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">14</span>),</li>
							<li><span class=\"nombre\">9</span>&nbsp;&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">9</span>,&nbsp;&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">17</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">24</span>),</li>
							<li><span class=\"nombre\">10</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">13</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.2\"</span>,&nbsp;&nbsp;&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">25</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">26</span>),</li>
							<li><span class=\"nombre\">11</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">10</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.1\"</span>,&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">18</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">19</span>),</li>
							<li><span class=\"nombre\">12</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">11</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.2\"</span>,&nbsp;<span class=\"texte\">'left'</span>&nbsp;=>&nbsp;<span class=\"nombre\">20</span>,&nbsp;<span class=\"texte\">'right'</span>&nbsp;=>&nbsp;<span class=\"nombre\">21</span>),</li>
							<li><span class=\"nombre\">13</span>&nbsp;=>&nbsp;<span class=\"native\">array</span>(<span class=\"texte\">'id'</span>&nbsp;=>&nbsp;<span class=\"nombre\">12</span>,&nbsp;<span class=\"texte\">'label'</span>&nbsp;=>&nbsp;<span class=\"texte\">\"2.2.1.3\"</span>, <span class=\"texte\">'left'</span> => <span class=\"nombre\">22</span>, <span class=\"texte\">'right'</span> => <span class=\"nombre\">23</span>)</li>
						</ul>
						);
					</div>
					" . $oArborescence->renderHTML() . "
					<p id='hidden-ArborescenceHelper' class='code padding-2p hidden width-96p'>
						<button onClick='$(\"#hidden-ArborescenceHelper\").addClass(\"hidden\");$(\"#visible-ArborescenceHelper\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet ArborescenceHelper</span><br/>
						<span class=\"variable\">\$oArborescence</span> = <span class=\"native\">new</span> ArborescenceHelper(<span class=\"texte\">\"ListeAvecIntervalles\"</span>);<br/>
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
