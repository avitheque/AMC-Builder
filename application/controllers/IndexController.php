<?php
/**
 * @brief	Classe contrôleur par défaut de la page d'accueil.
 *
 * @li Récupération des paramètres d'un formulaire grâce à la variable de classe aParams.
 * @code
 * 	// Récupération de l'ensemble des paramètres
 * 	$aForm = $this->getParams();
 * 	// Récupération d'un seul paramètre par son nom
 * 	$sLogin = $this->getParam('login');
 * @endcode
 *
 * @li Envoie des données d'un formulaire grâce à la variable de classe aData.
 * @code
 * 	// Création d'un tableau de données
 * 	$aConnexion = array(
 * 		'login' 	=> "sic",
 * 		'passwd'	=> "master"
 *  );
 * 	// En voie du tableau de données à la vue
 *  $this->addToData('connexion', $aConnexion);
 *
 * @li Récupération des données dans la vue.
 * @code
 * 	// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
 * 	$oInstanceStorage = InstanceStorage::getInstance();
 * 	// Création d'un tableau de données
 * 	$aConnexion = $oInstanceStorage->getData('connexion'));
 * @endcode
 *
 * Étend la classe abstraite AbstractAuthenticateController.
 * @see			{ROOT_PATH}/application/controllers/AbstractAuthenticateController.php
 *
 * @name		IndexController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (lun., 27 févr. 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class IndexController extends AbstractAuthenticateController {

	/**
	 * @brief	Constructeur de la classe.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__);
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 */
	public function indexAction() {}

}
