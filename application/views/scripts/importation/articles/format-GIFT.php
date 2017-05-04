<?php
/**
 * @brief   Format GIFT.
 * 
 * Documentation sur l'exploitation du format GIFT.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

// Ajout du Datatable à l'article
$sArticle .= "	<p>
					Un fichier texte au format <span class='strong italic'>GIFT</span> se présente sous la forme suivante :
				</p>
				<br/>
				<p class='code padding-20 height-25p'>
					<span class='commentaire pointer hover-bold' title='Commentaire : cette ligne ne sera pas prise en compte'>// Exemple de question avec un seul et unique choix</span><br/>
					<span class='pointer hover-bold' title='Titre de la question'>::Q001::</span><span class='pointer hover-bold' title='Énoncé de la question'>Quelle est la capitale de l'Australie ?</span> {<br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse juste'>=Canberra</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Bonne réponse, donne la totalité des points</span><br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse fausse'>~Sydney</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Mauvaise réponse</span><br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse fausse'>~Cranberry</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Une autre mauvaise réponse !</span><br/>
					}<br />
					<br />
					<span class='commentaire pointer hover-bold' title='Commentaire : cette ligne ne sera pas prise en compte'>// Exemple de question avec choix multiple</span><br/>
					<span class='pointer hover-bold' title='Titre de la question'>::Q002::</span><span class='pointer hover-bold' title='Énoncé de la question'>Parmi les années suivantes, lesquels sont bissextiles ?</span> {<br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse fausse 1/2'>~%-50%1998</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Réponse fausse, retire 50% des points !</span><br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse fausse 2/2'>~%-50%2001</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Réponse fausse, retire 50% des points !</span><br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse juste 1/2'>=%50%2012</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Réponse juste, donne 50% des points</span><br/>
					<span class='indentation'>&nbsp;&nbsp;</span><span class='pointer hover-bold' title='Réponse juste 2/2'>=%50%2016</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#Réponse juste, donne également 50% des points</span><br/>
					}<br />
					<br />
					<span class='commentaire pointer hover-bold' title='Commentaire : cette ligne ne sera pas prise en compte'>// Exemple de question Vrai/Faux</span><br/>
					<span class='pointer hover-bold' title='Titre de la question'>::Q003::</span><span class='pointer hover-bold' title='Énoncé de la question'>Une journée est composée de 24 heures ?</span> {<span class='pointer hover-bold' title='Réponse juste'>TRUE</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#La bonne réponse est dans la question !</span>}<br />
					<br />
					<span class='commentaire pointer hover-bold' title='Commentaire : cette ligne ne sera pas prise en compte'>// Autre forme possible pour une question Vrai/Faux</span><br/>
					<span class='pointer hover-bold' title='Titre de la question'>::Q004::</span><span class='pointer hover-bold' title='Énoncé de la question'>3 x 8 = 36</span> {<span class='pointer hover-bold' title='Réponse fausse'>F</span> <span class='pointer hover-bold' title='Commentaire de la réponse'>#La bonne réponse était 24 !</span>}<br />
					<br />
				</p>";
