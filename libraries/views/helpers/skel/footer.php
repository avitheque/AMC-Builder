<?php
/**
 * Fichier permettant de construire le pied des pages HTML de l'application.
 *
 * @name		footer.php
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
?>
<!-- FOOTER -->
		<footer>
			<?php ViewRender::linkContent($_SESSION, VIEW_FOOTER); ?>
		</footer>

		<?php ViewRender::linkContent($_SESSION, VIEW_FORM_END); ?>
