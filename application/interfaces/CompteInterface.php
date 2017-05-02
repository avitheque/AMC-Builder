<?php
/**
 * @brief	Interface du contrôleur CompteController.
 *
 * @li Remarque :
 * Obligation de passer par une classe parce que les constantes ne supportent pas les tableaux !!!
 *
 * @name		CompteInterface
 * @category	Interface
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
abstract class CompteInterface {

	/**
	 * @brief	Protection contre l'injection de champs via les méthodes POST ou GET.
	 *
	 * Variable de classe permettant d'exploiter l'ensemble des champs du formulaire.
	 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
	 *
	 * @var		array	: au format array('nom_du_champ'	=> DataHelper::DATA_TYPE_*)
	 */
	static $LIST_CHAMPS_FORM	= array(
		// UTILISATEUR ******************************************************* (ordre alphabétique)
		'utilisateur_id'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_confirmation'	=> DataHelper::DATA_TYPE_STR,
		'utilisateur_grade'			=> DataHelper::DATA_TYPE_INT,
		'utilisateur_login'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_nom'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_password'		=> DataHelper::DATA_TYPE_STR,
		'utilisateur_prenom'		=> DataHelper::DATA_TYPE_STR,
		'utilisateur_profil'		=> DataHelper::DATA_TYPE_INT,
		'utilisateur_datetime'		=> DataHelper::DATA_TYPE_DATETIME
	);

}
