<?php
/**
 * Fichier permettant de construire le contenu de l'entête des pages HTML de l'application.
 *
 * @name		head.php
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<!-- SCRIPTS -->
		<script type="text/javascript">
		<!--
		<?php ViewRender::linkContent($_SESSION, VIEW_SCRIPTS);

		if (DataHelper::get($_SESSION, VIEW_JQUERY)) { ?>
		$(document).ready(function() {
			<?php ViewRender::linkContent($_SESSION, VIEW_JQUERY); ?>
		});
		<?php } ?>
		//-->
		</script>

		<!-- STYLES -->
		<style>
		<!--
		<?php ViewRender::linkContent($_SESSION, VIEW_STYLES); ?>
		-->
		</style>
