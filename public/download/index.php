<?php
	/**
	 * @brief	Fonctionnalité permettant de naviguer dans l'aborescence.
	 *
	 * Permet de lister le contenu du répertoire alors que l'option PHP `autoindex` est désactivée
	 * et de protéger ainsi l'arborescence de l'architecture de la vue de l'utilisateur.
	 *
	 * @li	La RACINE de l'arborescence correspont au répertoire du présent fichier, qui correspond à `dirname(__FILE__)`.
	 * @li	Affichage de l'arborescence en deux partie :
	 * 			- partie gauche : navigation dans les répertoires à partir de la RACINE (répertoire d'origine) ;
	 * 			- partie droite : navigation au sein du répertoire courant.
	 * @li	Impossibilité de remonter l'arborescence au delà du répertoire RACINE.
	 * @li	Lorsqu'un fichier est sélectionné, son contenu est directement téléchargé.
	 *
	 * @name		index.php
	 * @package		Init
	 * @subpackage	Application
	 * @author		durandcedric@avitheque.net
	 * @update		$LastChangedBy: durandcedric $
	 * @version		$LastChangedRevision: 80 $
	 * @since		$LastChangedDate: 2017-10-21 13:24:25 +0200 (Sat, 21 Oct 2017) $
	 */

	// Répertoire du fichier actuel
	$_ROOT			= realpath(dirname(__FILE__));
	$aRoot			= explode("/", $_ROOT);
	$_PWD			= $aRoot[count($aRoot) - 1];		// Répertoire courant

	// Liste des extensions exclues de l'affichage
	$_aDISABLED_EXT	= array(
		'.php'
	);

	// Fonctionnalité réalisée si le répertoire est celui du parent avec protection contre la remontée de l'arborescence
	$_PATH			= $_ROOT . (!empty($_POST['dir_name']) && !preg_match('@/\.\.@', $_POST['dir_name']) ? stripslashes(htmlspecialchars_decode($_POST['dir_name'])) : "");

	// Dossier listé pour afficher la sous-arborecence
	$dir_name		= str_replace($_ROOT, '', $_PATH);

	// Initialisation des variables de l'arborescence
	$aRacine		= array();							// Tableau contenant le nom de chaque dossier de $_ROOT
	$aDossier		= array();							// Tableau contenant le nom de chaque dossier de $_PATH
	$aFichier		= array();							// Tableau contenant le nom de chaque fichier de $_PATH

	// Extraction de la construction du chemin
	$aChemin		= explode("/", $dir_name == '/..' ? '' : $dir_name);
	$nCount			= count($aChemin);

	// Début de la mise en tampon du fichier avec compression automatique
	ob_start('ob_gzhandler');

	// Initialisation du HEADER
	header("Content-Transfer-Encoding: binary");

	// Suppression d'éléments du HEADER
	header_remove("Pragma");
	header_remove("Set-Cookie");
	header_remove("X-Powered-By");

	// Désactivation du cache
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	header("Expires: 0");

	// Ouverture du répertoire courant
	if (is_dir($_PATH)) {
		// Récupération de l'arborescence $_ROOT
		$oRoot									= opendir($_ROOT) or die("Erreur : le répertoire principal n'existe pas !");
		// Parcours de l'ensemble de l'arborescence $_ROOT
		while ($element = readdir($oRoot)) {
			if ($element != '.' && $element != '..') {
				// Récupération du chemin
				$sDirectoryName					= $_ROOT . '/' . $element;

				// Fonctionnalité réalisée si le chemin est relatif à un fichier
				if (preg_match("@^\.[a-zA-Z0-9]*@", $element)) {
					// Le fichier ou le répertoire ne doit pas être affiché
					continue;
				} elseif (is_dir($sDirectoryName)) {
					// L'élément est un dossier
					$aRacine['/' . $element]	= $element;
				}
			}
		}
		// Fermeture du répertoire $_ROOT
		closedir($oRoot);

		// Récupération de l'arborescence $_PATH
		$oDirectory							= opendir($_PATH) or die("Erreur : le répertoire sélectionné n'existe pas !");
		// Fonctionnalité réalisée si le répertoire courant n'est pas $_ROOT
		if ($_ROOT . "/" != $_PATH && strlen($_PATH) > strlen($_ROOT)) {
			// Suppression de la dernière entrée
			unset($aChemin[$nCount - 1]);

			// Construction du chemin PARENT
			$sParent							= implode('/', $aChemin);
			// Ajout du répertoire parent
			$aDossier[$sParent]					= '..';
		}
		// Parcours de l'ensemble de l'arborescence $_PATH
		while ($element = readdir($oDirectory)) {
			if ($element != '.' && $element != '..' && !in_array(strrchr($element, '.'), $_aDISABLED_EXT)) {
				// Récupération du chemin
				$sFileName						= $dir_name . '/' . $element;

				// Fonctionnalité réalisée si le chemin est relatif à un fichier
				if (preg_match("@^\.[a-zA-Z0-9]*@", $element)) {
					// Le fichier ou le répertoire ne doit pas être affiché
					continue;
				} elseif (!is_dir($_ROOT . $sFileName)) {
					// L'élément est un fichier
					$aFichier[$sFileName]		= $element;
				} else {
					// L'élément est un dossier
					$aDossier[$sFileName]		= $element;
				}
			}
		}
		// Fermeture du répertoire $_PATH
		closedir($oDirectory);

		// Initialisation du ContenType du HEADER
		header("Content-Type: text/html");
?>
<xml version="1.0" encoding="UTF-8" >
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
	<head>
		<title>Liste des documents</title>
		<meta charset="utf-8">
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="expires" content="Sat, 22 Sep 1979 13:30:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />
		<style type="text/css">
			/* Tous les tags HTML */
			*							{ margin: 0; padding: 0; }

			/* Corps HTML */
			body						{ background: #EEE !important; color: #777 !important; overflow: hidden; }
			header						{ width: 100%; height: 5%; margin-top: 1%; }
			section						{ width: 100%; height: auto; }

			/* Titre de la page */
			h3							{ text-align: center; }

			/* Conteneur */
			#main-panel					{ position: fixed; padding-top: 5%; width: 100%; height: 100%; }

			/* Panneaux */
			#left-panel,
			#right-panel				{ height: 50%; font-size: 15px; vertical-align: top; overflow: auto; }

			/* Panneau de gauche */
			#left-panel					{ min-width: 30%; margin: 10px; }
			.panel						{ overflow: auto; background: #FFF; height: 50%; border: solid 1px #333; padding: 20px 0; text-decoration: none; list-style: none; }
			.sub-panel					{ text-decoration: none; list-style: none; padding-left: 20px; }

			/* Panneau de droite */
			#right-panel				{ min-width: 70%; }

			/* Panneau de l'arborescence */
			#arborescence				{ background: #FFF; height: 50%; border: solid 1px #333; padding: 20px; text-decoration: none; list-style: none; overflow: auto; }

			/* Répertoires */
			.directory,
			.sub-directory				{ background: #FFF url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QkbChAI0krP3QAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAGUSURBVDjLpZO/qhNREMZ/J3ezdlpsk1gJdntfII2NT5AuT+Jb+BKWgVRRUtpYWgpBhCsWiiisV01ikj1n5rPY3JtdEkXwwIH5+82cb+YESfzPyRaLhVar1YljOBwyGo1Cnud/B9hsNkwmkxPHcrlkPp/LzE58eZ4zHo9DCIHsT8hlWVKW5VnfbDY7dnAjvH/2BLkjF7iDAxIyB3GwN/cyGe+evlSnA4+R+48ed0uFE6Fzvr56fQRQclRH6i9vjglnAQRuSIm03rYAzPEYsW3djQ+hSfBE8ITkt7r9anFAdBQTvtuDHGToEIgn5EY4VFaqUapJP+50OfDdlvTzG1g6JBqe9qjeIttDOthxANJ2cASI1xXp+zX1xw8o7pDVyCJIzTPO0BHUmsLm6i3x4QPqz5+aqNCdRugKjdZrASg62iV8fUNiOD/BVjfhbtbiIBm+S9i6buWednJbMAS4F8j6/T7T6ZRBFFcvnoP3kDkygTWbKXMwNdsI0OsRLi4YXA4IkqiqSlVVkVL6ty+cZRRFQVEU4Te3RAKlbKxdoQAAAABJRU5ErkJggg==') no-repeat; border: solid 1px #FFF; width: 100%; padding-left: 20px; font-weight: bold; text-align: left; white-space: nowrap; cursor: pointer; }
			.opened .directory,
			.opened .sub-directory		{ color: #3366ff; }
			.selected .directory,
			.selected .sub-directory,
			.directory:hover,
			.sub-directory:hover		{ border: solid 1px #333; background: orange; color: #eef7ed; background: orange url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QkbChAI0krP3QAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAGUSURBVDjLpZO/qhNREMZ/J3ezdlpsk1gJdntfII2NT5AuT+Jb+BKWgVRRUtpYWgpBhCsWiiisV01ikj1n5rPY3JtdEkXwwIH5+82cb+YESfzPyRaLhVar1YljOBwyGo1Cnud/B9hsNkwmkxPHcrlkPp/LzE58eZ4zHo9DCIHsT8hlWVKW5VnfbDY7dnAjvH/2BLkjF7iDAxIyB3GwN/cyGe+evlSnA4+R+48ed0uFE6Fzvr56fQRQclRH6i9vjglnAQRuSIm03rYAzPEYsW3djQ+hSfBE8ITkt7r9anFAdBQTvtuDHGToEIgn5EY4VFaqUapJP+50OfDdlvTzG1g6JBqe9qjeIttDOthxANJ2cASI1xXp+zX1xw8o7pDVyCJIzTPO0BHUmsLm6i3x4QPqz5+aqNCdRugKjdZrASg62iV8fUNiOD/BVjfhbtbiIBm+S9i6buWednJbMAS4F8j6/T7T6ZRBFFcvnoP3kDkygTWbKXMwNdsI0OsRLi4YXA4IkqiqSlVVkVL6ty+cZRRFQVEU4Te3RAKlbKxdoQAAAABJRU5ErkJggg==') no-repeat; }
			.selected .directory,
			.selected .sub-directory	{ color: #FFF; }

			/* Indicateur au survol d'un répertoire */
			.directory .flag,
			.sub-directory .flag		{ display: none; float: left; application color: #000; }
			.selected .directory .flag,
			.selected .sub-directory .flag,
			.directory:hover .flag,
			.sub-directory:hover .flag	{ display: block; color: #000; }

			/* Fichiers */
			.file						{ background: #FFF url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QkbCh8cTwgHbwAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAADrSURBVDjL3ZE9asNAEIU/rfWHYAu1AqlXo84unGuYnCz4FoGQA8QQg3rpAOp0ARkVMbPeNCISxFISJ1UeTDMw37w341hrKcvSighLcl2XJEnIssxRSo19gPP5jfV6Mzt8OLyw3d7RNA1t29qiKBytNQAKQMQsbu+6jjiOyfMcrTVVVdm+70eAMcLlcpmt06kDIIoi0jTF933qurYfEWQAzEkpxX7/gOd5hGE4VDTewIhZBOx29596x+Mr33ZwTWJk4uAGgJkC5IsIVx0Mn/sjB0aw1v7iBjdEMNMI/+ALq5Xi6fnxR4AgCAB4B493prkXlbMhAAAAAElFTkSuQmCC') no-repeat; border: none; margin-left: 20px; padding-left: 20px; text-decoration: none; color: #000; text-align: left; cursor: pointer; }
			.file:hover					{ color: #3366ff; font-weight: bold; }
		</style>
	</head>
	<body>
		<header>
			<h3>Liste des documents consultables au <?php print date("d/m/Y H:i:s"); ?></h3>
		</header>
		<section>
			<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">
				<table id="main-panel">
				<tr>
				<td id="left-panel">
					<ul class="panel">
					<?php
						// Initialisation de la classe du répertoire parcouru (sélectionné ou ouvert)
						$sRacineClass						= empty($dir_name)				? "selected"							: "";
						// Ajour d'un indicateur pour le répertoire
						$sRacinePrefixe						= empty($dir_name)				? "&#9658;"								: "&#8656;";
						// Ajour d'un titre pour le répertoire
						$sRacineTitle						= empty($dir_name)				? "Répertoire courant"					: "Remonter à la racine";
						// Construction de la ligne relative répertoire de $_PWD
						print '<li class="' . $sRacineClass . '"><button type="submit" name="dir_name" title="' . $sRacineTitle . '" value="" class="directory"><span class="flag">' . $sRacinePrefixe . '&nbsp;</span>/</button></li>';

						// Fonctionnalité réalisée si des répertoires sont présents dans $_ROOT
						if (!empty($aRacine)) {
							// Début de construction de la liste
							print '<ul class="sub-panel">';

							// Tri des répertoires
							ksort($aRacine, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
							foreach($aRacine as $sDirectoryName => $sName) {
								// Initialisation de la classe du répertoire parcouru (sélectionné ou ouvert)
								$sMainClass					= $sDirectoryName == $dir_name	? "selected"							: "";
								$sUnderList					= "";

								// Fonctionnalité réalisée si le répertoire courant est sélectionné
								if (!empty($dir_name) && in_array($sName, $aChemin)) {
									// Le répertoire principal est ouvert
									$sMainClass				= "opened";

									// Récupération de l'ensemble des sous-répertoires
									$sDirectory				= "";
									$aDirectory				= explode('/', $dir_name);

									// Construction de la sous-arborescence en cours de navigation
									for ($occurrence		= 1 ; $occurrence < count($aDirectory) ; $occurrence++) {
										// Initialisation du nom du sous-répertoire courant
										$sDirectory 		.= '/' . $aDirectory[$occurrence];
										if (isset($aDirectory[$occurrence]) && $aDirectory[$occurrence] != $sName) {
											for ($count = 0 ; $count < $occurrence ; $count++) {
												$sUnderList	.= '<ul class="sub-panel">';
											}
											// Initialisation de la classe du répertoire sélectionné ou ouvert
											$sUnderClass	= $sDirectory == $dir_name		? "selected"							: $sMainClass;
											$sIdSelected	= $sMainClass == "opened"		? "current_directory"					: null;
											$sIdSelected	= $sUnderClass == "selected"	? "current_directory"					: $sIdSelected;
											// Ajour d'un indicateur pour le répertoire
											$sUnderPrefixe	= $sDirectory == $dir_name		? "&#9658;"								: "&#8656;";
											// Ajour d'un titre pour le répertoire
											$sUnderTitle	= $sDirectory == $dir_name		? "Répertoire courant"					: "Remonter au répertoire précédent";
											// Construction du sous-répertoire courant
											$sUnderList		.= '<li class="' . $sUnderClass . '" id="' . $sIdSelected . '"><button type="submit" name="dir_name" title="' . $sUnderTitle . '" value="' . htmlspecialchars(addslashes($sDirectory)) . '" class="sub-directory"><span class="flag">' . $sUnderPrefixe . '&nbsp;</span>' . htmlspecialchars($aDirectory[$occurrence]) . '</button></li>';
											for ($count = 0 ; $count < $occurrence ; $count++) {
												$sUnderList	.= '</ul>';
											}
										}
									}
								}

								// Identifiant de l'élément s'il est sélectionné par défaut
								$sIdSelected				= $sMainClass == "selected"		? "current_directory"					: null;

								// Ajour d'un indicateur pour le répertoire
								$sMainPrefixe				= $sMainClass == "selected"		? "&#9658;"								: "&#8656;";
								// Ajour d'un titre pour le répertoire
								$sMainTitle					= $sMainClass == "selected"		? "Répertoire courant"					: "Remonter au répertoire précédent";
								// Construction de la ligne du répertoire de $_ROOT
								print '<li class="' . $sMainClass . '" id="' . $sIdSelected . '"><button type="submit" name="dir_name" title="' . $sMainTitle . '" value="' . htmlspecialchars(addslashes($sDirectoryName)) . '" class="directory"><span class="flag">' . $sMainPrefixe . '&nbsp;</span>' . htmlspecialchars($sName) . '</button>' . $sUnderList . '</li>';
							}

							// Finalitation de la liste
							print '</ul>';
						}
					?>
					</ul>
				</td>
				<td id="right-panel">
					<ul id="arborescence">
						<?php
							// Fonctionnalité réalisée si des répertoires sont présents dans $_PATH
							if (!empty($aDossier)) {
								// Tri des répertoires
								ksort($aDossier, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
								foreach($aDossier as $sFileName => $sName) {
									// Ajour d'un indicateur pour le répertoire
									$sPrefixe				= $sName == '..'				? "&#8656;"								: "&#9658;";
									// Ajour d'un titre pour le répertoire
									$sTitle					= $sName == '..'				? "Remonter au répertoire précédent"	: "Ouvrir le répertoire";
									// Construction de la ligne du répertoire de $_PATH
									print '<li><button type="submit" name="dir_name" title="' . $sTitle . '" value="' . htmlspecialchars(addslashes($sFileName)) . '" class="directory"><span class="flag">' . $sPrefixe . '&nbsp;</span>' . htmlspecialchars($sName) . '</button></li>';
								}
							}

							// Fonctionnalité réalisée si des fichiers sont présents dans $_PATH
							if (!empty($aFichier)){
								// Tri des fichiers
								ksort($aFichier, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
								foreach($aFichier as $sFileName => $sName) {
									// Construction de la ligne du fichier de $_PATH
									print '<li><button type="submit" name="dir_name" title="Ouvrir le fichier" value="' . htmlspecialchars(addslashes($sFileName)) . '" class="file">' . htmlspecialchars($sName) . '</button></li>';
								}
							}
						?>
					</ul>
				</td>
				</tr>
				</table>
			</form>
		</section>
		<script type="text/javascript">
			// Focus sur le répertoire sélectionné
			location.href = "#current_directory";
		</script>
	</body>
</html>
<?php
	// Téléchargement du fichier
	} elseif (file_exists($_PATH)) {
		// Liste des extensions
		$aMimeTypes = array(
			// internet
			'.txt'	=> "text/plain",
			'.htm'	=> "text/html",
			'.html'	=> "text/html",
			'.php'	=> "text/html",
			'.css'	=> "text/css",
			'.js'	=> "application/javascript",
			'.json'	=> "application/json",
			'.xml'	=> "application/xml",
			'.swf'	=> "application/x-shockwave-flash",
			'.flv'	=> "video/x-flv",
			'.eml'	=> "message/rfc822",

			// images
			'.png'	=> "image/png",
			'.jpe'	=> "image/jpeg",
			'.jpeg'	=> "image/jpeg",
			'.jpg'	=> "image/jpeg",
			'.gif'	=> "image/gif",
			'.bmp'	=> "image/bmp",
			'.ico'	=> "image/vnd.microsoft.icon",
			'.tiff'	=> "image/tiff",
			'.tif'	=> "image/tiff",
			'.svg'	=> "image/svg+xml",
			'.svgz'	=> "image/svg+xml",

			// archives
			'.zip'	=> "application/zip",
			'.rar'	=> "application/x-rar-compressed",
			'.exe'	=> "application/x-msdownload",
			'.msi'	=> "application/x-msdownload",
			'.cab'	=> "application/vnd.ms-cab-compressed",

			// audio/video
			'.mp3'	=> "audio/mpeg",
			'.qt'	=> "video/quicktime",
			'.mov'	=> "video/quicktime",

			// adobe
			'.pdf'	=> "application/pdf",
			'.psd'	=> "image/vnd.adobe.photoshop",
			'.ai'	=> "application/postscript",
			'.eps'	=> "application/postscript",
			'.ps'	=> "application/postscript",

			// ms office
			'.doc'	=> "application/msword",
			'.docx'	=> "application/msword",
			'.rtf'	=> "application/rtf",
			'.xls'	=> "application/vnd.ms-excel",
			'.xlsx'	=> "application/vnd.ms-excel",
			'.ppt'	=> "application/vnd.ms-powerpoint",
			'.pptx'	=> "application/vnd.ms-powerpoint",

			// open office
			'.odt'	=> "application/vnd.oasis.opendocument.text",
			'.ods'	=> "application/vnd.oasis.opendocument.spreadsheet"
		);

		// Récupération du nom du fichier
		$sFileName	= addslashes($aChemin[$nCount - 1]);

		// Récupération des informations du fichier à exploiter
		$aFileInfos = array(
			// Nom du fichier
			'name'		=> $sFileName,
			// Taille du fichier
			'size'		=> filesize($_PATH),
			// Dernière modification du fichier
			'mdate'		=> filemtime($_PATH),
			// Récupération de l'extension du fichier
			'content'	=> $aMimeTypes[strrchr($sFileName, '.')]
		);

		// Initialisation de l'entête du fichier pour le téléchargement
		header("Content-disposition: attachment; filename=\"" . $aFileInfos['name'] . "");

		// Initialisation du ContenType selon l'extension du fichier
		header("Content-Type: " . $aFileInfos['content']);

		// Récupération du contenu SANS passer par le CACHE
		readfile($_PATH);
	} else {
		// Initialisation du ContenType du HEADER
		header("Content-Type: text/html");
		// Affichage d'un message d'erreur
		print '<h1>ERREUR</h1>';
		// Le fichier n'est pas correctement reconnu
		print '<h3>Le fichier <em style="color: red;">`' . $aChemin[$nCount - 1] . '`</em> n\'est pas accessible !</h3>';
	}

	// Ajout de la taille du contenu
	header("Content-Length: " . ob_get_length());
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	// Fin de la mise en tampon du fichier
	ob_end_flush();
	exit();
?>
