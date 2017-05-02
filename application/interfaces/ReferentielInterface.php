<?php
/**
 * @brief	Interface du contrôleur ReferentielController.
 *
 * @li Remarque :
 * Obligation de passer par une classe parce que les constantes ne supportent pas les tableaux !!!
 *
 * @name		ReferentielInterface
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
abstract class ReferentielInterface {

	/**
	 * @brief	Protection contre l'injection de champs via les méthodes POST ou GET.
	 *
	 * Variable de classe permettant d'exploiter l'ensemble des champs du formulaire.
	 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
	 *
	 * @var		array	: au format array('nom_du_champ'	=> DataHelper::DATA_TYPE_*)
	 */
	static $LIST_CHAMPS_FORM	= array(
		// ONGLET ************************************************************ (ordre alphabétique)
		'tabs_active'				=> DataHelper::DATA_TYPE_INT,

		// GÉNÉRALITÉS ******************************************************* (ordre alphabétique)
		'referentiel_date_debut'	=> DataHelper::DATA_TYPE_DATE,
		'referentiel_date_fin'		=> DataHelper::DATA_TYPE_DATE,
		'referentiel_datetime'		=> DataHelper::DATA_TYPE_DATETIME,
		'referentiel_description'	=> DataHelper::DATA_TYPE_TXT,
		'referentiel_id'			=> DataHelper::DATA_TYPE_INT,
		'referentiel_libelle'		=> DataHelper::DATA_TYPE_STR,
		'referentiel_parent'		=> DataHelper::DATA_TYPE_INT,
		'referentiel_table'			=> DataHelper::DATA_TYPE_STR,

		// STATUT DU RÉFÉRENTIEL SALLE *************************************** (ordre alphabétique)
		'statut_salle_id'			=> DataHelper::DATA_TYPE_INT,
		'statut_salle_capacite'		=> DataHelper::DATA_TYPE_INT,
		'statut_salle_examen'		=> DataHelper::DATA_TYPE_BOOL,
		'statut_salle_informatique'	=> DataHelper::DATA_TYPE_BOOL,
		'statut_salle_reseau'		=> DataHelper::DATA_TYPE_BOOL,
		'statut_salle_reservable'	=> DataHelper::DATA_TYPE_BOOL
	);

}
