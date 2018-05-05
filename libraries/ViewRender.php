<?php
/**
 * Classe permettant de générer la page HTML à partir de la structure définie dans le SKEL.
 * @see			{ROOT_PATH}/libraries/views/helpers/skel.php
 *
 * @name		ViewRender
 * @package		Helpers
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 119 $
 * @since		$LastChangedDate: 2018-05-05 13:46:10 +0200 (Sat, 05 May 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ViewRender {

	/**
	 * Nom du fichier du squelette de page.
	 * @var 	string
	 */
	const	SKEL						= "skel";

	/**
	 * Types de messages.
	 * @var 	string
	 */
	const	MESSAGE_ERROR				= "alert";
	const	MESSAGE_INFO				= "info";
	const	MESSAGE_SUCCESS				= "success";
	const	MESSAGE_WARNING				= "warning";

	/**
	 * Variable de classe d'activation du rendu de la vue.
	 * @var		boolean
	 */
	static private		$_renderer		= true;

	/**
	 * @brief	Activation|Désactivation du rendu de la vue HTML.
	 * @return	void
	 */
	static function	setNoRenderer($bStatus = false) {
		self::$_renderer = !$bStatus;
	}

	/**
	 * @brief	Activation du rendu de la vue sous forme d'un document autre que HTML.
	 * 
	 * @param	string	$sFileName		: nom du document.
	 * @param	string	$sContentType	: format du document.
	 * @param	string	$sCharset		: encodage du document.
	 * @return	void
	 */
	static function setRenderDocument($sFileName = "document", $sContentType = "text/plain", $sCharset = "utf-8") {
		// Désactivation du mode de rendu par défaut
		self::setNoRenderer(true);

		// Modification de l'entête afin de désactiver le CACHE
		header("Cache-Control: no-cache, must-revalidate");
		header("Cache-Control: post-check=0,pre-check=0");
		header("Cache-Control: max-age=0");
		header("Pragma: no-cache");
		header("Expires: 0");
		header("Content-Type:\"" . trim($sContentType) . "\"; charset=" . trim($sCharset));
		header("Content-Disposition: attachment; filename=\"" . trim($sFileName) . "\"");
	}

	/**
	 * @brief	Génération finale de la vue HTML.
	 * @return	void
	 */
	static function render() {
		// Récupération de l'instance de `SessionMessenger`
		$oSessionMessenger = SessionMessenger::getInstance();

		// Création d'un message par défaut
		self::setMessageInfo(null, $oSessionMessenger->getMessage(self::MESSAGE_INFO));

		// Création des messages d'erreur
		self::setMessageError(null, $oSessionMessenger->getMessage(self::MESSAGE_ERROR));

		// Création des messages de succès
		self::setMessageSuccess(null, $oSessionMessenger->getMessage(self::MESSAGE_SUCCESS));

		// Création des messages d'avertissement
		self::setMessageWarning(null, $oSessionMessenger->getMessage(self::MESSAGE_WARNING));

		// Fonctionnaliré réalisée si le rendu est actif
		if (self::$_renderer) {
			//$oSessionMessenger->unsetIndex('success');
			require_once FW_HELPERS . '/' . self::SKEL . '.php';
		} else {
			// Réinitialisation de l'activation de la vue
			self::$_renderer = true;
		}
	}

	/**
	 * @brief	Ajout du contenu du SKEL.
	 *
	 * @param	string	$sFileName	: chemin du fichier à insérer dans la vue.
	 * @return	void
	 */
	static function linkFileContent($sFileName) {
		require_once FW_HELPERS . '/' . self::SKEL . '/' . $sFileName;
		print chr(10);
	}

	/**
	 * @brief	Ajout de la feuille de style d'un formulaire.
	 *
	 * @param	string	$sFileName	: chemin du fichier de la feuille de style à insérer dans la collection
	 * @return	void
	 */
	static function linkFormulaireStyle($sFileName) {
		// Construction du chemin
		self::addToStylesheet(FW_FORM_STYLES_PATH . '/' . $sFileName);
	}

	/**
	 * @brief	Ajout du JavaScript d'un formulaire.
	 *
	 * @param	string	$sFileName	: chemin du fichier JavaScript à insérer dans le HEAD
	 * @return	void
	 */
	static function linkFormulaireScript($sFileName) {
		// Construction du chemin
		self::addToScripts(FW_FORM_SCRIPTS_PATH . '/' . $sFileName);
	}

	/**
	 * @brief	Ajoute un contenu à la page HTML.
	 *
	 * @param	mixed	$xInput		: chaîne de caractères ou COLLECTION à ajouter dans la page.
	 * @param	string	$sIndex		: index de la collection dans laquelle le contenu sera ajouté.
	 * @return	void
	 */
	static function linkContent($xInput, $sIndex = null) {
		if (empty($sIndex)) {
			print $xInput;
		} else {
			print (string) DataHelper::get($xInput, $sIndex);
		}
		print chr(10);
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_BODY.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToBody($sString) {
		$_SESSION[VIEW_BODY]		= !empty($_SESSION[VIEW_BODY])			? $_SESSION[VIEW_BODY]				: "";
		$_SESSION[VIEW_BODY]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_DEBUG.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToDebug($sString, $sClass = null) {
		$_SESSION[VIEW_DEBUG]		= !empty($_SESSION[VIEW_DEBUG])			? $_SESSION[VIEW_DEBUG]				: "";
		if (DataHelper::isValidArray($sString)) {
			$_SESSION[VIEW_DEBUG]	.= "<li class=\"$sClass\">" . DataHelper::convertToJSON($sString) . "</li>";
		} else {
			$_SESSION[VIEW_DEBUG]	.= "<li class=\"$sClass\">" . DataHelper::convertToText($sString) . "</li>";
		}
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_DIALOG.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToDialog($sString) {
		$_SESSION[VIEW_DIALOG]		= !empty($_SESSION[VIEW_DIALOG])		? $_SESSION[VIEW_DIALOG]			: "";
		$_SESSION[VIEW_DIALOG]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_EXCEPTION.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToException($sString) {
		$_SESSION[VIEW_EXCEPTION]	= !empty($_SESSION[VIEW_EXCEPTION])		? $_SESSION[VIEW_EXCEPTION]			: "";
		$_SESSION[VIEW_EXCEPTION]	.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_FOOTER.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToFooter($sString) {
		$_SESSION[VIEW_FOOTER]		= !empty($_SESSION[VIEW_FOOTER])		? $_SESSION[VIEW_FOOTER]			: "";
		$_SESSION[VIEW_FOOTER]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_FORM_START.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToFormStart($sString) {
		$_SESSION[VIEW_FORM_START]	= !empty($_SESSION[VIEW_FORM_START])	? $_SESSION[VIEW_FORM_START]		: "";

		// Fermeture automatique de la balise FORM
		if (preg_match("@.*<form.*@", $sString) && empty($_SESSION[VIEW_FORM_START])) {
			$sString = "<!-- FORMULAIRE START -->" . chr(10) . $sString;
		}
		$_SESSION[VIEW_FORM_START]	.= $sString;

		// Fermeture automatique de la balise FORM
		if (preg_match("@.*<form.*@", $sString) && empty($_SESSION[VIEW_FORM_END])) {
			self::addToFormEnd("<!-- FORMULAIRE END -->" . chr(10) . "</form>");
		}
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_FORM_END.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToFormEnd($sString) {
		$_SESSION[VIEW_FORM_END]	= !empty($_SESSION[VIEW_FORM_END])		? $_SESSION[VIEW_FORM_END]			: "";
		$_SESSION[VIEW_FORM_END]	.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_HEAD.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToHead($sString) {
		$_SESSION[VIEW_HEAD]		= !empty($_SESSION[VIEW_HEAD])			? $_SESSION[VIEW_HEAD]				: "";
		$_SESSION[VIEW_HEAD]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_HEADER.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToHeader($sString) {
		$_SESSION[VIEW_HEADER]		= !empty($_SESSION[VIEW_HEADER])		? $_SESSION[VIEW_HEADER]			: "";
		$_SESSION[VIEW_HEADER]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu JavaScript à la collection VIEW_JQUERY.
	 *
	 * @param	string	$sString	: contenu JS à ajouter.
	 */
	static function addToJQuery($sScript) {
		$_SESSION[VIEW_JQUERY]		= !empty($_SESSION[VIEW_JQUERY])		? $_SESSION[VIEW_JQUERY]			: "";
		// Compression du fichier script avec JavaScriptPacker
		$oPacker = new JavaScriptPacker($sScript);
		$_SESSION[VIEW_JQUERY]		.= sprintf("\n\t\t\t\t%s", $oPacker->pack());
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_MAIN.
	 *
	 * @param	string	$sString	: contenu HTML à ajouter.
	 */
	static function addToMain($sString) {
		$_SESSION[VIEW_MAIN]		= !empty($_SESSION[VIEW_MAIN])			? $_SESSION[VIEW_MAIN]				: "";
		$_SESSION[VIEW_MAIN]		.= $sString;
	}

	/**
	 * @brief	Ajoute un contenu HTML à la collection VIEW_MENU.
	 *
	 * @param	mixed	$sString	: contenu HTML à ajouter.
	 */
	static function addToMenu($sString) {
		$_SESSION[VIEW_MENU]		= !empty($_SESSION[VIEW_MENU])			? $_SESSION[VIEW_MENU]				: "";
		$_SESSION[VIEW_MENU]		.= $sString;
	}

	/**
	 * @brief	Ajoute un script JS à la collection VIEW_SCRIPTS.
	 *
	 * @li Contrôle si le contenu existe déjà, auquel cas rien ne sera ajouté.
	 * @li Compresse le script avant de l'ajouter à la collection afin de réduire la taille du contenu.
	 *
	 * @param	mixed	$xInput		: script ou fichier à ajouter.
	 */
	static function addToScripts($xInput) {
		$_SESSION[VIEW_SCRIPTS]		= !empty($_SESSION[VIEW_SCRIPTS])		? $_SESSION[VIEW_SCRIPTS]			: "";

		if (is_string($xInput) && preg_match('@.*\/+.*\/+.*\.js$@', strtolower($xInput))) {
			if (file_exists($xInput)) {
				$sFilename = $xInput;
			} elseif (file_exists(ROOT_PATH . $xInput)) {
				$sFilename = ROOT_PATH . $xInput;
			}

			// Récupération du contenu du script
			$sString = file_get_contents($sFilename);
		} elseif (DataHelper::isValidArray($xInput)) {
			// Fonctionnalité réalisée si l'entrée est un tableau
			$sString = implode("\n", (array) $xInput);
		} else {
			$sString = $xInput;
		}

		// Encodage du script au format MD5
		$MD5 = md5(implode(";", (array) $sString));
		// Ajout du script à la collection s'il n'est pas présent
		if (!empty($sString) && (empty($_SESSION[VIEW_MD5][VIEW_SCRIPTS]) || !in_array($MD5, $_SESSION[VIEW_MD5][VIEW_SCRIPTS]))) {
			// Compression du fichier script avec JavaScriptPacker
			$oPacker = new JavaScriptPacker($sString);
			$_SESSION[VIEW_SCRIPTS]					.= sprintf("\n\t\t%s", $oPacker->pack());
			$_SESSION[VIEW_MD5][VIEW_SCRIPTS][]		= $MD5;
		}
	}

	/**
	 * @brief	Ajoute un contenu CSS à la collection VIEW_STYLES.
	 *
	 * @param	string	$sString	: contenu CSS à ajouter.
	 */
	static function addToStyles($sString) {
		$_SESSION[VIEW_STYLES]		= !empty($_SESSION[VIEW_STYLES])		? $_SESSION[VIEW_STYLES]			: "";
		$_SESSION[VIEW_STYLES]		.= $sString;
	}

	/**
	 * @brief	Ajoute une feuille de style à la collection VIEW_SCRIPTS.
	 *
	 * @li Contrôle si le contenu existe déjà, auquel cas rien ne sera ajouté.
	 * @li Compresse le fichier avant de l'ajouter à la collection afin de réduire la taille du contenu.
	 *
	 * @param	string	$sFilename	: fichier à ajouter.
	 */
	static function addToStylesheet($sFilename) {
		$_SESSION[VIEW_STYLESHEET]	= !empty($_SESSION[VIEW_STYLESHEET])	? $_SESSION[VIEW_STYLESHEET]		: "";

		// Encodage du style au format MD5
		$MD5 = md5($sFilename);
		// Ajout du style à la collection s'il n'est pas présent
		if (file_exists($sFilename) && empty($_SESSION[VIEW_MD5][VIEW_STYLESHEET]) || !in_array($MD5, $_SESSION[VIEW_MD5][VIEW_STYLESHEET])) {
			// Compression du fichier CSS avec MiniCSS
			$oMiniCSS = new MiniCSS($sFilename);

			$_SESSION[VIEW_STYLESHEET]				.= sprintf("\n\t\t<style type=\"text/css\">\n\t\t\t\t%s\n\t\t</style>", $oMiniCSS->min());
			$_SESSION[VIEW_MD5][VIEW_STYLESHEET][]	= $MD5;
		}
	}

	//=============================================================================================

	/**
	 * @brief	Initialise le contenu de la collection VIEW_BODY.
	 */
	static function clearBody() {
		$_SESSION[VIEW_BODY]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_DEBUG.
	 */
	static function clearDebug() {
		$_SESSION[VIEW_DEBUG]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_EXCEPTION.
	 */
	static function clearException() {
		$_SESSION[VIEW_EXCEPTION]			= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_DIALOG.
	 */
	static function clearDialog() {
		$_SESSION[VIEW_DIALOG]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_FOOTER.
	 */
	static function clearFooter() {
		$_SESSION[VIEW_FOOTER]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_FORM_START.
	 */
	static function clearFormStart() {
		$_SESSION[VIEW_FORM_START]			= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_FORM_END.
	 */
	static function clearFormEnd() {
		$_SESSION[VIEW_FORM_END]			= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_HEAD.
	 */
	static function clearHead() {
		$_SESSION[VIEW_HEAD]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_HEADER.
	 */
	static function clearHeader() {
		$_SESSION[VIEW_HEADER]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_JQUERY.
	 */
	static function clearJQuery() {
		$_SESSION[VIEW_JQUERY]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_MAIN.
	 */
	static function clearMain() {
		$_SESSION[VIEW_MAIN]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_MENU.
	 */
	static function clearMenu() {
		$_SESSION[VIEW_MENU]				= "";
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_SCRIPTS.
	 */
	static function clearScripts() {
		$_SESSION[VIEW_SCRIPTS]				= "";
		$_SESSION[VIEW_MD5][VIEW_SCRIPTS]	= array();
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_STYLES.
	 */
	static function clearStyles() {
		$_SESSION[VIEW_STYLES]				= "";
		$_SESSION[VIEW_MD5][VIEW_STYLES]	= array();
	}

	/**
	 * @brief	Initialise le contenu de la collection VIEW_STYLESHEET.
	 */
	static function clearStylesheet() {
		$_SESSION[VIEW_STYLESHEET]			= "";
		$_SESSION[VIEW_MD5][VIEW_STYLESHEET]= array();
	}

	/**
	 * @brief	Initialise le contenu de l'ensemble des collection VIEW_*.
	 *
	 * Méthode appelée lors de l'initialisation de la page HTML.
	 */
	static function start() {
		// Récupération de l'instance de `SessionMessenger`
		$oSessionMessenger = SessionMessenger::getInstance();
		$oSessionMessenger->setViewRender(DataHelper::getTime());

		self::clearBody();
		self::clearDebug();
		self::clearDialog();
		self::clearException();
		self::clearFooter();
		self::clearFormStart();
		self::clearFormEnd();
		self::clearJQuery();
		self::clearHead();
		self::clearHeader();
		self::clearMain();
		self::clearMenu();
		self::clearScripts();
		self::clearStyles();
		self::clearStylesheet();
	}

	//=============================================================================================

	/**
	 * @brief	Création d'un message.
	 *
	 * @li Le contenu HTML du message sera ajouté à la collection VIEW_DIALOG.
	 *
	 * @param	string	$sTitre			: Titre du message.
	 * @param	mixed	$xMessage		: Message à afficher, peut être une liste de plusieurs messages.
	 * @return	void
	 */
	static function setMessageBox($sTitre = null, $xMessage = null, $sClass = "message") {
		// Fonctionnalité réalisée si au moins un paramètre est présent
		if (!empty($sTitre) || !empty($xMessage)) {
			// Création du conteneur
			$sMessageBox = "<section class=\"" . $sClass . "\" >";
			// Ajout d'une ancre pour la fermeture du message
			$sMessageBox .= "<a href=\"#\" class=\"margin-0 close\">x</a>";

			// Ajout du titre s'il est présent
			if (!empty($sTitre)) {
				$sMessageBox .= "<h4 class=\"margin-V-10 margin-H-20\">$sTitre</h4>";
			}

			// Ajout du message s'il est présent
			if (!empty($xMessage)) {
				$sMessageBox .= "<p class=\"margin-V-10 margin-H-20\">" . implode("<br />", (array) $xMessage) . "</p>";
			}

			// Finalisation du message
			$sMessageBox .= "</section>";
			// Ajout de l'élément au VIEW_BODY
			self::addToDialog($sMessageBox);
		}
	}

	/**
	 * @brief	Création d'un message d'erreur.
	 *
	 * @param	string	$sTitre			: Titre du message.
	 * @param	string	$sMessage		: Message à afficher.
	 * @return	void
	 */
	static function setMessageError($sTitre = null, $sMessage = null) {
		self::setMessageBox($sTitre, $sMessage, $sClass = "message alert");
	}

	/**
	 * @brief	Création d'un message d'information.
	 *
	 * @param	string	$sTitre			: Titre du message.
	 * @param	string	$sMessage		: Message à afficher.
	 * @return	void
	 */
	static function setMessageInfo($sTitre = null, $sMessage = null) {
		self::setMessageBox($sTitre, $sMessage, $sClass = "message info");
	}

	/**
	 * @brief	Création d'un message de succès.
	 *
	 * @param	string	$sTitre			: Titre du message.
	 * @param	string	$sMessage		: Message à afficher.
	 * @return	void
	 */
	static function setMessageSuccess($sTitre = null, $sMessage = null) {
		self::setMessageBox($sTitre, $sMessage, $sClass = "message success");
	}

	/**
	 * @brief	Création d'un message d'avertissement.
	 *
	 * @param	string	$sTitre			: Titre du message.
	 * @param	string	$sMessage		: Message à afficher.
	 * @return	void
	 */
	static function setMessageWarning($sTitre = null, $sMessage = null) {
		self::setMessageBox($sTitre, $sMessage, $sClass = "message warning");
	}

}
