<?php
/**
 * @brief	Interface du contrôleur GestionController.
 *
 * @li Remarque :
 * Obligation de passer par une classe parce que les constantes ne supportent pas les tableaux !!!
 *
 * @name		GestionInterface
 * @category	Interface
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 33 $
 * @since		$LastChangedDate: 2017-06-11 21:24:20 +0200 (Sun, 11 Jun 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class GestionInterface {

	/**
	 * @brief	Protection contre l'injection de champs via les méthodes POST ou GET.
	 *
	 * Variable de classe permettant d'exploiter l'ensemble des champs du formulaire.
	 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
	 *
	 * @var		array	: au format array('nom_du_champ'	=> DataHelper::DATA_TYPE_*)
	 */
	static $LIST_CHAMPS_FORM	= array(
		// CANDIDAT ********************************************************** (ordre alphabétique)
		'candidat_code'				=> DataHelper::DATA_TYPE_STR,
		'candidat_id'				=> DataHelper::DATA_TYPE_STR,
		'candidat_grade'			=> DataHelper::DATA_TYPE_INT,
		'candidat_nom'				=> DataHelper::DATA_TYPE_STR,
		'candidat_prenom'			=> DataHelper::DATA_TYPE_STR,
		'candidat_unite'			=> DataHelper::DATA_TYPE_STR,
		'candidat_datetime'			=> DataHelper::DATA_TYPE_DATETIME,

		// STAGE ************************************************************* (ordre alphabétique)
		'stage_date_debut'			=> DataHelper::DATA_TYPE_DATE,
		'stage_date_fin'			=> DataHelper::DATA_TYPE_DATE,
		'stage_domaine'				=> DataHelper::DATA_TYPE_INT,
		'stage_sous_domaine'		=> DataHelper::DATA_TYPE_INT,
		'stage_categorie'			=> DataHelper::DATA_TYPE_INT,
		'stage_sous_categorie'		=> DataHelper::DATA_TYPE_INT,
		'stage_id'					=> DataHelper::DATA_TYPE_INT,
		'stage_libelle'				=> DataHelper::DATA_TYPE_STR,
		'stage_datetime'			=> DataHelper::DATA_TYPE_DATETIME,

		// UTILISATEUR ******************************************************* (ordre alphabétique)
		'utilisateur_id'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_confirmation'	=> DataHelper::DATA_TYPE_STR,
		'utilisateur_grade'			=> DataHelper::DATA_TYPE_INT,
		'utilisateur_groupe'		=> DataHelper::DATA_TYPE_INT,
		'utilisateur_login'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_modifiable'	=> DataHelper::DATA_TYPE_BOOL,
		'utilisateur_nom'			=> DataHelper::DATA_TYPE_STR,
		'utilisateur_password'		=> DataHelper::DATA_TYPE_STR,
		'utilisateur_prenom'		=> DataHelper::DATA_TYPE_STR,
		'utilisateur_profil'		=> DataHelper::DATA_TYPE_INT,
		'utilisateur_datetime'		=> DataHelper::DATA_TYPE_DATETIME,

		// GROUPE *********************************************************** (ordre alphabétique)
		'groupe_id'					=> DataHelper::DATA_TYPE_INT,
		'groupe_datetime'			=> DataHelper::DATA_TYPE_DATETIME
	);

}
