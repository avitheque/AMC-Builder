<?php
/**
 * @brief	Groupe
 *
 * Vue de gestion d'un groupe.
 * User: durandcedric
 * Date: 25/09/16
 * Time: 11:43
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

// Récupération de l'instance d'authentification
$oAuth	= AuthenticateManager::getInstance();

//#################################################################################################
// INTERFACE DE CRÉATION DU FORMULAIRE
//#################################################################################################

// Zone du formulaire injecté dans $_SESSION[VIEW_MAIN]
$oGroupe = new GroupeHelper(!$oAuth->isModifiable());
ViewRender::addToMain($oGroupe->render());
