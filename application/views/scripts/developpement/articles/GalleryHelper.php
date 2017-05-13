<?php
/**
 * Documentation sur l'exploitation de l'objet GalleryHelper.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Initialisation de l'objet GalleryHelper
$oGallery	= new GalleryHelper();

// Ajout de l'élément [A] dans la bibliothèque
$sElementA	= "<article class=\"miniature padding-0\" title=\"Élément A\">
					<h3 class=\"strong left\">Élément A</h3>
					<p>
						Appuyez sur le bouton [zoom] afin de voir le détail...
					</p>
				</article>";
// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]
$oGallery->addItem($sElementA, "A", "/developpement/gallery?id=%s");

// Ajout de l'élément [B] dans la bibliothèque
$sElementB	= "<article class=\"miniature padding-0\" title=\"Élément B\">
					<h3 class=\"strong left\">Élément B</h3>
					<p>
						Appuyez sur le bouton [zoom] afin de voir le détail...
					</p>
				</article>";
// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]
$oGallery->addItem($sElementB, "B", "/developpement/gallery?id=%s");

// Ajout de l'élément [C] dans la bibliothèque
$sElementC	= "<article class=\"miniature padding-0\" title=\"Élément C\">
					<h3 class=\"strong left\">Élément C</h3>
					<p>
						Appuyez sur le bouton [zoom] afin de voir le détail...
					</p>
				</article>";
// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]
$oGallery->addItem($sElementC, "C", "/developpement/gallery?id=%s");

// Ajout de l'élément [D] dans la bibliothèque
$sElementD	= "<article class=\"miniature padding-0\" title=\"Élément D\">
					<h3 class=\"strong left\">Élément D</h3>
					<p>
						Appuyez sur le bouton [zoom] afin de voir le détail...
					</p>
				</article>";
// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]
$oGallery->addItem($sElementD, "D", "/developpement/gallery?id=%s");

// Ajout du Datatable à l'article
$sArticle .= "<fieldset class='padding-H-20'>
					<legend>Exploitation de l'objet <span class='strong italic'>GalleryHelper</span></legend>
					" . $oGallery->renderHTML() . "<br />
					<p id='hidden-GalleryHelper' class='code padding-20 hidden'>
						<button onClick='$(\"#hidden-GalleryHelper\").addClass(\"hidden\");$(\"#visible-GalleryHelper\").removeClass(\"hidden\");''>Masquer le code</button><br />
						<br/>
						<span class=\"commentaire\">// Initialisation de l'objet GalleryHelper</span><br/>
						<span class=\"variable\">\$oGallery</span> = <span class=\"native\">new</span> GalleryHelper();<br/>
						<br />
						<span class=\"commentaire\">// Ajout de l'élément [A] dans la bibliothèque</span><br/>
						<span class=\"variable\">\$sElementA</span> = <span class=\"texte\">\"&lt;article class=\\\"miniature padding-0\\\" title=\\\"Élément A\\\"&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;h3 class=\"strong left\"&gt;Élément A&lt;/h3&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>Appuyez sur le bouton [zoom] afin de voir le détail...<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/article&gt;\"</span>;<br />
						<span class=\"commentaire\">// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]</span><br/>
						<span class=\"variable\">\$oGallery</span>->addItem(<span class=\"variable\">\$sElementA</span>, <span class=\"texte\">\"A\"</span>, <span class=\"texte\">\"/developpement/gallery?id=%s\"</span>);<br />
						<br />
						<span class=\"commentaire\">// Ajout de l'élément [B] dans la bibliothèque</span><br/>
						<span class=\"variable\">\$sElementB</span> = <span class=\"texte\">\"&lt;article class=\\\"miniature padding-0\\\" title=\\\"Élément B\\\"&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;h3 class=\"strong left\"&gt;Élément B&lt;/h3&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>Appuyez sur le bouton [zoom] afin de voir le détail...<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/article&gt;\"</span>;<br />
						<span class=\"commentaire\">// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]</span><br/>
						<span class=\"variable\">\$oGallery</span>->addItem(<span class=\"variable\">\$sElementB</span>, <span class=\"texte\">\"B\"</span>, <span class=\"texte\">\"/developpement/gallery?id=%s\"</span>);<br />
						<br />
						<span class=\"commentaire\">// Ajout de l'élément [C] dans la bibliothèque</span><br/>
						<span class=\"variable\">\$sElementC</span> = <span class=\"texte\">\"&lt;article class=\\\"miniature padding-0\\\" title=\\\"Élément C\\\"&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;h3 class=\"strong left\"&gt;Élément C&lt;/h3&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>Appuyez sur le bouton [zoom] afin de voir le détail...<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/article&gt;\"</span>;<br />
						<span class=\"commentaire\">// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]</span><br/>
						<span class=\"variable\">\$oGallery</span>->addItem(<span class=\"variable\">\$sElementC</span>, <span class=\"texte\">\"C\"</span>, <span class=\"texte\">\"/developpement/gallery?id=%s\"</span>);<br />
						<br />
						<span class=\"commentaire\">// Ajout de l'élément [D] dans la bibliothèque</span><br/>
						<span class=\"variable\">\$sElementD</span> = <span class=\"texte\">\"&lt;article class=\\\"miniature padding-0\\\" title=\\\"Élément D\\\"&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;h3 class=\"strong left\"&gt;Élément D&lt;/h3&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>Appuyez sur le bouton [zoom] afin de voir le détail...<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/p&gt;<br />
						<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span>&lt;/article&gt;\"</span>;<br />
						<span class=\"commentaire\">// Injection du contenu HTML | id | structure du chemin HREF lors du clic sur le zoom [Voir le contenu]</span><br/>
						<span class=\"variable\">\$oGallery</span>->addItem(<span class=\"variable\">\$sElementD</span>, <span class=\"texte\">\"D\"</span>, <span class=\"texte\">\"/developpement/gallery?id=%s\"</span>);<br />
						<br />
						<span class=\"commentaire\">// Rendu final sous forme de code HTML</span><br/>
						<span class=\"native\">print</span> <span class=\"variable\">\$oGallery</span>->renderHTML();
					</p>
					<p id='visible-GalleryHelper' class='code padding-H-20 transparent'>
						<button onClick='$(\"#visible-GalleryHelper\").addClass(\"hidden\");$(\"#hidden-GalleryHelper\").removeClass(\"hidden\");'>Voir le code</button>
					</p>
				</fieldset>";
