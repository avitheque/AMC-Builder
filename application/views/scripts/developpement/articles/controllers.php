<?php
/**
 * Documentation sur l'exploitation des contrôleurs
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:29
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

global $sArticle;
// Présentation de récupération des données d'un formulaire par le contrôleur
$sArticle .= "<fieldset><legend>Récupération des données d'un formulaire</legend>
				<span class=\"titre\">Dans le contrôleur, il est possible de récupérer les champs saisis d'un formulaire avec la méthode d'instance <span class=\"strong italic\">\$this->getParam()</span> :</span>
				<section class=\"code padding-H-20\">
					<span class=\"php\">&lt;?php</span>
					<br />
					<span class=\"native\">class</span> LoginController <span class=\"native\">extends</span> AbstractFormulaireController {
					<br />
					<br />
					<ul>
						<li class=\"commentaire\">
							/**<br />
							&nbsp;*	Instance du modèle de gestion des utilisateurs<br />
							&nbsp;*	@var&nbsp;&nbsp;UserManager<br />
							&nbsp;*/
						</li>
						<li>
							<span class=\"native\">protected</span> <span class=\"methode\">\$_oUserManager</span> = <span class=\"native\">null</span>;
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
						<li>
							<span class=\"native\">public function</span> indexAction() {<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Récupération des champs du formulaire</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sLogin</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;= <span class=\"variable\">\$this</span>->getParam(<span class=\"texte\">'login'</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sPassword</span>&nbsp;&nbsp;= <span class=\"variable\">\$this</span>->getParam(<span class=\"texte\">'password'</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Récupération de l'utilisateur par son login et son mot de passe</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$aUtilisateurs</span> = <span class=\"variable\">\$this</span>-><span class=\"methode\">_oUserManager</span>->getUtilisateurByLoginPassword(<span class=\"variable\">\$sLogin</span>, <span class=\"variable\">\$sPassword</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span>(...)<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							}
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
					</ul>
					}
				</section>
			</fieldset>";

// Présentation d'échange des données entre contrôleurs
$sArticle .= "<hr/>
			<fieldset><legend>Échange des données entre contrôleurs</legend>
				<span class=\"titre\">Dans un contrôleur, il est possible de stocker des données avec la méthode d'instance <span class=\"strong italic\">\$this->addToData()</span> :</span>
				<section class=\"code padding-H-20\">
					<span class=\"php\">&lt;?php</span>
					<br />
					<span class=\"native\">class</span> LoginController <span class=\"native\">extends</span> AbstractFormulaireController {
					<br />
					<ul>
						<li class=\"padding-V-20\">
							(...)
						</li>
						<li>
							<span class=\"native\">public function</span> indexAction() {<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Récupération des champs du formulaire</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sLogin</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;= <span class=\"variable\">\$this</span>->getParam(<span class=\"texte\">'login'</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sPassword</span>&nbsp;&nbsp;= <span class=\"variable\">\$this</span>->getParam(<span class=\"texte\">'password'</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Construction d'un tableau de variables</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$aConnexion</span> = <span class=\"native\">array</span>(<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"texte\">'login'</span>&nbsp;&nbsp;&nbsp;&nbsp;=> <span class=\"variable\">\$sLogin</span>,<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"texte\">'password'</span>&nbsp;=> <span class=\"variable\">\$sPassword</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Stockage des données dans la variable d'instance par un nom de déclaration</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$this</span>->addToData(<span class=\"texte\">'connexion'</span>, <span class=\"variable\">\$aConnexion</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span>(...)<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							}
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
					</ul>
					}
				</section>
				<br />
				<span class=\"titre\">Dans un autre contrôleur, il est alors possible de récupérer ces données avec la méthode d'instance <span class=\"strong italic\">\$this->getData()</span> :</span>
				<section class=\"code padding-H-20\">
					<span class=\"php\">&lt;?php</span>
					<br />
					<span class=\"native\">class</span> DeveloppementController <span class=\"native\">extends</span> AbstractAuthenticateController {
					<br />
					<br />
					<ul>
						<li class=\"commentaire\">
							/**<br />
							&nbsp;*	Instance du modèle de gestion des utilisateurs<br />
							&nbsp;*	@var&nbsp;&nbsp;UserManager<br />
							&nbsp;*/
						</li>
						<li>
							<span class=\"native\">protected</span> <span class=\"methode\">\$_oUserManager</span> = <span class=\"native\">null</span>;
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
						<li>
							<span class=\"native\">public function</span> indexAction() {<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Récupération des données stockées dans la variable d'instance par le nom de déclaration</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$aConnexion</span> = <span class=\"variable\">\$this</span>->getData(<span class=\"texte\">'connexion'</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Extraction des informations d'authentification</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sLogin</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;= <span class=\"variable\">\$aConnexion</span>[<span class=\"texte\">'login'</span>];<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$sPassword</span>&nbsp;&nbsp;= <span class=\"variable\">\$aConnexion</span>[<span class=\"texte\">'password'</span>];<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Récupération de l'utilisateur par son login et son mot de passe</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$aUtilisateurs</span> = <span class=\"variable\">\$this</span>-><span class=\"methode\">_oUserManager</span>->getUtilisateurByLoginPassword(<span class=\"variable\">\$sLogin</span>, <span class=\"variable\">\$sPassword</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span>(...)<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							}
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
					</ul>
					}
				</section>
			</fieldset>";

// Présentation d'une variable envoyée par le contrôleur
$sArticle .= "<hr/>
			<fieldset><legend>Transmission des données du contrôleur à la vue</legend>
				<span class=\"titre\">Dans le contrôleur, les données sont stockées avec la méthode d'instance <span class=\"strong italic\">\$this->addToData()</span> :</span>
				<section class=\"code padding-H-20\">
					<span class=\"php\">&lt;?php</span>
					<br />
					<span class=\"native\">class</span> DeveloppementController <span class=\"native\">extends</span> AbstractAuthenticateController {
					<br />
					<ul>
						<li class=\"padding-V-20\">
							(...)
						</li>
						<li>
							<span class=\"native\">public function</span> indexAction() {<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Déclaration de la variable</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$aConnexion</span> = <span class=\"native\">array</span>(<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"texte\">'login'</span>&nbsp;&nbsp;&nbsp;&nbsp;=> <span class=\"texte\">\"" . $aConnexion['login'] . "\"</span>,<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"texte\">'password'</span>&nbsp;=> <span class=\"texte\">\"" . $aConnexion['password'] . "\"</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;|&nbsp;&rarr;&nbsp;</span><span class=\"texte\">'webmaster'</span>=> <span class=\"nombre\">" . (int) $aConnexion['webmaster'] . "</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span>);<br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"commentaire\">// Stockage de la variable avec un nom de déclaration</span><br />
							<span class=\"indentation\">|&nbsp;&rarr;&nbsp;</span><span class=\"variable\">\$this</span>->addToData(<span class=\"texte\">'connexion'</span>, <span class=\"variable\">\$aConnexion</span>);<br />
							}
						</li>
						<li class=\"padding-V-20\">
							(...)
						</li>
					</ul>
					}
				</section>
				<br />
				<span class=\"titre\">Dans la vue, la variable est récupérée grâce au singleton de la classe <span class=\"strong italic\">InstanceStorage</span> et à sa méthode <span class=\"strong\">getData()</span> :</span>
				<section class=\"code padding-H-20\">
					<div class=\"php\">&lt;?php</div>
					<div class=\"commentaire\">// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges entre contrôleurs</div>
					<span class=\"variable\">\$oInstanceStorage</span> = InstanceStorage::getInstance();<br />
					<br />
					<span class=\"commentaire\">// Récupération de la variable par son nom</span><br />
					<span class=\"variable\">\$aConnexion</span> = <span class=\"variable\">\$oInstanceStorage</span>->getData(<span class=\"texte\">'connexion'</span>);<br />
					<br />
					(...)
				</section>
				<br />
				<span class=\"titre\">Le contenu de la variable peut ainsi être évaluée avec la commande PHP <span class=\"strong italic\">var_dump(\$aConnexion);</span></span>
				<section class=\"code padding-H-20\">
					array(" . count($aConnexion) . ")&nbsp;{<ul>";

$nCount = 0;
// Fonctionnalité réalisée pour chaque entrée du tableau
foreach ($aConnexion as $sClef => $sValeur) {
	$nCount++;
	// Ajout d'une virgule si la liste n'est pas terminée
	$sVirgule	= count($aConnexion) > $nCount ? "," : "";
	// Formatage selon le style
	$sFormat	= DataHelper::isValidNumeric($sValeur) ? '%d'		: '"%s"';
	// Affichage du contenu de l'entrée sous la forme [KEY] => VALUE
	$sArticle	.= "<li>&nbsp;['" . $sClef . "'</span>]&nbsp;=>&nbsp;" . sprintf($sFormat, $sValeur) . $sVirgule . "</li>";
}

$sArticle .= 		"</ul>}
				</section>
			</fieldset>";
