<?php
/** @brief      Vue des Exceptions de l'application.
 * 
 * Cette vue est exploitée afin de présenter des erreurs d'accès à l'application, suite à une Exception.
 * 
 * @name		VwError.php
 * @package		Helpers
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 89 $
 * @since		$LastChangedDate: 2017-12-27 00:05:27 +0100 (Wed, 27 Dec 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
global $aERRORSTRINGS;
$oInstanceStorage = InstanceStorage::getInstance();

// Liste des types d'erreurs
$aErrStr = array_merge(
	(array) $oInstanceStorage->getParam('errorstrings'),
	array(
		// Identifiant			=> Littéral
		'ERessourceAclNotFound'	=> Constantes::ERROR_ACL_NORESSOURCE,
		'ERoleAclNotFound'		=> Constantes::ERROR_ACL_NOROLE,
		'EAccessAclNotFound'	=> Constantes::ERROR_ACL_UNVALID,
		'EAclNotAllowed'		=> Constantes::ERROR_ACL_UNALLOWED,

		'EAuthNotFind'			=> Constantes::ERROR_AUTH_BAD,

		'EActionNotFound'		=> Constantes::ERROR_BAD_ACTION,
		'EControllerNotFound'	=> Constantes::ERROR_BAD_CONTROLLER,
		'ENoConnector'			=> Constantes::ERROR_BAD_NOCONNECTOR,
		'ENoController'			=> Constantes::ERROR_BAD_NOCONTROLLER,
		'EParamTypeMismatch'	=> Constantes::ERROR_BAD_TYPE,
		'EParamBadValue'		=> Constantes::ERROR_BAD_VALUE,
		'EViewNotFound'			=> Constantes::ERROR_BAD_VIEW,

		'ELogAction'			=> Constantes::ERROR_LOG_ACTION,

		'EFieldNotFound'		=> Constantes::ERROR_SQL_BADFIELD,
		'EBadQuery'				=> Constantes::ERROR_SQL_BADQUERY,
		'EQueryCascade'			=> Constantes::ERROR_SQL_CASCADE,
		'EQueryData'			=> Constantes::ERROR_SQL_BADDATA,
		'EQueryDelete'			=> Constantes::ERROR_SQL_NODELETE,
		'EQueryNotFound'		=> Constantes::ERROR_SQL_NOQUERY,
		'EQuerySave'			=> Constantes::ERROR_SQL_NOSAVE,
		'EWhereNotFount'		=> Constantes::ERROR_SQL_NOWHERE,

		'EUnknown'				=> Constantes::ERROR_UNDEFINED
	)
);

if (!isset($aErrStr[$e->getMessage()])) {
	$aErrors	= explode('::', $aErrStr['EUnknown']);
	$aErrors[]	= Constantes::ERROR_TRIGGERED . $e->getMessage();
} else {
	$aErrors = explode('::', $aErrStr[$e->getMessage()]);
}

// Extraction du titre
$sTitle = $aErrors[0];
unset($aErrors[0]);

// Contruction du contenu HTML
$sAlertBox =	"<section class=\"alertBox\">";
$sAlertBox .=		DataHelper::displayException($e, $sTitle, implode("<br />", $aErrors));
$sAlertBox .=		"<div class=\"center padding-top-25\">
						<a class=\"button\" href=\"" . INDEX . "\">Retour à la page d'accueil</a>
					</div>";
$sAlertBox .=	"</section>";

// Intégration du contenu à la page HTML
ViewRender::addToException($sAlertBox);

// Rendu final de la vue
ViewRender::render();
