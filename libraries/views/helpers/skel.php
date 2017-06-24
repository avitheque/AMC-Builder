<?php
/**
 * Fichier permettant de construire le "squelette" commun des pages HTML de l'application.
 *
 * @name		skel.php
 * @category	Skeleton
 * @package		Helpers
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 47 $
 * @since		$LastChangedDate: 2017-06-24 18:26:35 +0200 (Sat, 24 Jun 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */

if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
	// Extension des fichiers de bibliothèque jQuery pour le Debuggage
	$sExtensionJQuery		= ".js";
	$sExtensionJQueryCSS	= ".css";
} else {
	// Extension des fichiers de bibliothèque jQuery par défaut
	$sExtensionJQuery		= ".min.js";
	$sExtensionJQueryCSS	= ".min.css";
}
?>
<xml version="1.0" encoding="UTF-8" >
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />

		<!-- Icône de l'application -->
		<link rel="shortcut icon" href="<?php print IMAGES_PATH; ?>/logo.png">
		<title><?php print APP_NAME; ?></title>

		<!--  Bibliothèque jQuery -->
		<script type="text/javascript" src="<?php print JQUERY_PATH . "/" . JQUERY_VERSION . $sExtensionJQuery; ?>"></script>

		<!--  Bibliothèque jQuery UI -->
		<script type="text/javascript" src="<?php print JQUERY_UI_THEME_PATH	. "/jquery-ui" . $sExtensionJQuery; ?>"></script>
		<script type="text/javascript" src="<?php print JQUERY_UI_PATH			. "/ui/i18n/jquery.ui.datepicker-fr.js"; ?>"></script>

		<!--  Bibliothèque CSS de jQuery UI -->
		<link rel="stylesheet" media="screen" type="text/css" href="<?php print JQUERY_UI_THEME_PATH . "/jquery-ui" . $sExtensionJQueryCSS; ?>" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php print JQUERY_UI_THEME_PATH . "/jquery-ui.structure" . $sExtensionJQueryCSS; ?>" />
		<link rel="stylesheet" media="screen" type="text/css" href="<?php print JQUERY_UI_THEME_PATH . "/jquery-ui.theme" . $sExtensionJQueryCSS; ?>" />

		<!--  Addons de jQuery UI -->
<?php foreach (unserialize(SERIAL_JQUERY_UI_ADDON) as $sFilename) { ?>
		<script type="text/javascript" src="<?php print JQUERY_PATH . "/" . $sFilename . "/" . $sFilename . $sExtensionJQuery; ?>"></script>
		<link rel="stylesheet" media="screen" type="text/css" href="<?php print JQUERY_PATH . "/" . $sFilename . "/" . $sFilename . $sExtensionJQueryCSS; ?>" />
<?php } ?>

		<!--  Bibliothèque dataTables -->
		<script type="text/javascript" src="<?php print DATATABLES_JS_PATH . "/jquery.dataTables" . $sExtensionJQuery; ?>"></script>
		<link rel="stylesheet" media="screen" type="text/css" href="<?php print DATATABLES_CSS_PATH . "/jquery.dataTables" . $sExtensionJQueryCSS; ?>" />

		<!-- Feuille de style principale -->
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/main.css" />
		<!-- Feuilles de styles complémentaires -->
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/application.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/buttons.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/errors.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/forms.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/icons.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/menu.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/messages.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/pagination.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/tableaux.css" />
		<!-- Feuilles de styles pour l'impression -->
		<link rel="stylesheet" media="print"  type="text/css" href="/downloader/styles/print.css" />

		<!--  JavaScript -->
		<script type="text/javascript" src="/downloader/scripts/numeric.js"></script>
		<script type="text/javascript" src="/downloader/scripts/main.js"></script>

<?php if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) { ?>
		<!-- Feuille de style du debuggage -->
		<link rel="stylesheet" media="screen" type="text/css" href="/downloader/styles/debug.css" />
		<script type="text/javascript" src="/downloader/scripts/debug.js"></script>
<?php } ?>

		<?php ViewRender::linkContent($_SESSION, VIEW_STYLESHEET); ?>
		<?php ViewRender::linkFileContent('head.php'); ?>
	</head>
	<body>
		<div id="application" class="margin-0 auto-width">
			<a href="/index" class="text">
				<img id="app_logo" src="/images/logo.png" alt="<?php print APP_NAME; ?>" />
				<ul id="app_infos">
					<li><span id="app_name" class="strong italic"><?php print APP_NAME; ?></span></li>
					<li><span id="app_version" class="strong italic">Version <?php print APP_VERSION; ?></span></li>
				</ul>
			</a>
		</div>
		<?php ViewRender::linkFileContent('header.php'); ?>

		<?php ViewRender::linkFileContent('dialog.php'); ?>

		<?php ViewRender::linkFileContent('body.php'); ?>

		<?php ViewRender::linkFileContent('footer.php'); ?>

		<?php if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
			ViewRender::linkFileContent('debug.php');
		} ?>
	</body>

	<!-- DialogBox jQuery -->
	<div id="dialog-confirm" class="hidden">
		<p class="margin-top-25">
			Les données du formulaire actuel n'ont pas été enregistrées.
			<hr />
			Si vous continuez, toutes les modifications seront perdues...
		</p>
	</div>
	<div id="dialog-delete" class="hidden">
		<p class="margin-top-25">
			Vous êtes sur le point de supprimer l'élément actuel.
			<hr />
			Si vous continuez, toutes les données seront définitivement perdues...
		</p>
	</div>

	<!-- Protection du chargement de la page contre les cliqueurs intempestifs -->
	<div id="stop-click">
		<h3><img src="/images/loading.gif" alt="sablier" id="loading"/> Chargement en cours, merci de patienter...</h3>
	</div>
</html>
