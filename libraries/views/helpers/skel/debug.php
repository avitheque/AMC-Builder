<?php
/**
 * Fichier permettant de construire la zone de debuggage si elle est active et renseignée dans le fichier de configuration.
 *
 * @name		debug.php
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
	<!-- DEBUG -->
	<var id="var-debug">...</var>
<?php
	if (DataHelper::get($_SESSION, VIEW_DEBUG)) { ?>
		<article id="article-debug" class="closed">
			<span class="icon open">&#187;</span>
			<ul id="ul-debug">
				<?php ViewRender::linkContent($_SESSION, VIEW_DEBUG); ?>
			</ul>
			<span class="icon close">&#171;</span>
		</article>
<?php } ?>
