<?php
/**
 * Fichier permettant de construire le corps des pages HTML de l'application. 
 *
 * @name		body.php
 * @category	Skeleton
 * @package		Helpers
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 19 $
 * @since		$LastChangedDate: 2017-04-30 15:27:06 +0200 (dim., 30 avr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

// Ajout du contenu du BODY
ViewRender::linkContent($_SESSION, VIEW_BODY);

// Initialisation du formulaire
ViewRender::linkContent($_SESSION, VIEW_FORM_START);
if (DataHelper::get($_SESSION, VIEW_MAIN)) { ?>
		<main>

		<?php ViewRender::linkFileContent('main.php'); ?>

		</main>
<?php	} ?>
