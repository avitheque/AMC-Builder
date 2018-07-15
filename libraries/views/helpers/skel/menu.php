<?php
/**
 * Fichier permettant de construire la barre de navigation des pages HTML de l'application.
 *
 * @name		menu.php
 * @category	Skeleton
 * @package		Helpers
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 136 $
 * @since		$LastChangedDate: 2018-07-14 17:20:16 +0200 (Sat, 14 Jul 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
?>
<!-- MENU -->
			<nav>
			<?php if (DataHelper::get($_SESSION, VIEW_MENU)) { ?>
				<ul id="ul-nav">
					<?php ViewRender::linkContent($_SESSION, VIEW_MENU); ?>
				</ul>
			<?php } ?>
			</nav>
			<form id="view-render" name="view-render" method="post" action="#">
				<button type="submit" name="render" id="fullscreen" class="display small white tooltip-left" value="fullscreen" title="Passer en vue plein écran">□</button>
				<button type="submit" name="render" id="panel" class="display small white tooltip-left" value="panel" title="Passer en vue panneau">_</button>
			</form>
