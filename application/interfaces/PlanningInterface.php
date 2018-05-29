<?php
/**
 * @brief	Interface du contrôleur PlanningController.
 *
 * @li Remarque :
 * Obligation de passer par une classe parce que les constantes ne supportent pas les tableaux !!!
 *
 * @name		PlanningInterface
 * @category	Interface
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 130 $
 * @since		$LastChangedDate: 2018-05-29 22:21:20 +0200 (Tue, 29 May 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class PlanningInterface {

	/**
	 * @brief	Protection contre l'injection de champs via les méthodes POST ou GET.
	 *
	 * Variable de classe permettant d'exploiter l'ensemble des champs du formulaire.
	 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
	 *
	 * @var		array	: au format array('nom_du_champ'	=> DataHelper::DATA_TYPE_*)
	 */
	static $LIST_CHAMPS_FORM	= array(
		// TÂCHE ************************************************************* (ordre alphabétique)
		'task_id'			=> DataHelper::DATA_TYPE_INT,
		'task_day'			=> DataHelper::DATA_TYPE_INT,
		'task_duration'		=> DataHelper::DATA_TYPE_INT,
		'task_hour'			=> DataHelper::DATA_TYPE_INT,
		'task_minute'		=> DataHelper::DATA_TYPE_INT,
		'task_month'		=> DataHelper::DATA_TYPE_INT,
		'task_locationId'	=> DataHelper::DATA_TYPE_INT,
		'task_matterId'		=> DataHelper::DATA_TYPE_INT,
		'task_teamId'		=> DataHelper::DATA_TYPE_INT,
		'task_update'		=> DataHelper::DATA_TYPE_INT,
		'task_year'			=> DataHelper::DATA_TYPE_INT
	);

}
