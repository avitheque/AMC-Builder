<?php
/**
 * @brief	Interface du contrôleur relatifs aux créations de QCM.
 *
 * @li Remarque :
 * Obligation de passer par une classe parce que les constantes ne supportent pas les tableaux !!!
 *
 * @name		FormulaireQCMInterface
 * @category	Interface
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 44 $
 * @since		$LastChangedDate: 2017-06-17 21:23:52 +0200 (Sat, 17 Jun 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class FormulaireQCMInterface {

	/**
	 * @brief	Protection contre l'injection de champs via les méthodes POST ou GET.
	 *
	 * Variable de classe permettant d'exploiter l'ensemble des champs du formulaire.
	 * ATTENTION : si un champ n'est pas référencé dans la liste, il ne sera pas pris en compte !
	 *
	 * @var		array	: au format array('nom_du_champ'	=> DataHelper::DATA_TYPE_*)
	 */
	static $LIST_CHAMPS_FORM	= array(

		// FORMAT ************************************************************ (ordre alphabétique)
		'generation_cartouche_candidat'	=> DataHelper::DATA_TYPE_TXT,
		'generation_choix_salles'		=> DataHelper::DATA_TYPE_STR,
		'generation_code_candidat'		=> DataHelper::DATA_TYPE_INT,
		'generation_consignes'			=> DataHelper::DATA_TYPE_TXT,
		'generation_date_epreuve'		=> DataHelper::DATA_TYPE_DATE,
		'generation_exemplaires'		=> DataHelper::DATA_TYPE_INT,
		'generation_format'				=> DataHelper::DATA_TYPE_STR,
		'generation_id'					=> DataHelper::DATA_TYPE_INT,
		'generation_langue'				=> DataHelper::DATA_TYPE_STR,
		'generation_nom_epreuve'		=> DataHelper::DATA_TYPE_STR,
		'generation_seed'				=> DataHelper::DATA_TYPE_INT,

		// EPREUVE *********************************************************** (ordre alphabétique)
		'epreuve_id'					=> DataHelper::DATA_TYPE_INT,
		'epreuve_date'					=> DataHelper::DATA_TYPE_DATE,
		'epreuve_duree'					=> DataHelper::DATA_TYPE_INT,
		'epreuve_heure'					=> DataHelper::DATA_TYPE_TIME,
		'epreuve_libelle'				=> DataHelper::DATA_TYPE_STR,
		'epreuve_liste_salles'			=> DataHelper::DATA_TYPE_ARRAY,
		'epreuve_stage'					=> DataHelper::DATA_TYPE_INT,
		'epreuve_table_affectation'		=> DataHelper::DATA_TYPE_BOOL,
		'epreuve_table_aleatoire'		=> DataHelper::DATA_TYPE_BOOL,
		'epreuve_type'					=> DataHelper::DATA_TYPE_STR,

		// GÉNÉRALITÉS ******************************************************* (ordre alphabétique)
		'formulaire_active_tab'			=> DataHelper::DATA_TYPE_INT,
		'formulaire_active_question'	=> DataHelper::DATA_TYPE_INT,
		'formulaire_categorie'			=> DataHelper::DATA_TYPE_INT,
		'formulaire_domaine'			=> DataHelper::DATA_TYPE_INT,
		'formulaire_id'					=> DataHelper::DATA_TYPE_INT,
		'formulaire_nb_max_reponses'	=> DataHelper::DATA_TYPE_INT,
		'formulaire_nb_total_questions'	=> DataHelper::DATA_TYPE_INT,
		'formulaire_note_finale'		=> DataHelper::DATA_TYPE_INT_ABS,
		'formulaire_penalite'			=> DataHelper::DATA_TYPE_INT_ABS,
		'formulaire_presentation'		=> DataHelper::DATA_TYPE_TXT,
		'formulaire_sous_categorie'		=> DataHelper::DATA_TYPE_INT,
		'formulaire_sous_domaine'		=> DataHelper::DATA_TYPE_INT,
		'formulaire_strict'				=> DataHelper::DATA_TYPE_BOOL,
		'formulaire_titre'				=> DataHelper::DATA_TYPE_STR,
		'formulaire_validation'			=> DataHelper::DATA_TYPE_INT,

		// QUESTIONNAIRE ***************************************************** (ordre alphabétique)
		'question_bareme'				=> DataHelper::DATA_TYPE_MYFLT_ABS,
		'question_correction'			=> DataHelper::DATA_TYPE_TXT,
		'question_enonce'				=> DataHelper::DATA_TYPE_TXT,
		'question_id'					=> DataHelper::DATA_TYPE_INT,
		'question_penalite'				=> DataHelper::DATA_TYPE_INT_ABS,
		'question_stricte'				=> DataHelper::DATA_TYPE_BOOL,
		'question_stricte_checkbox'		=> DataHelper::DATA_TYPE_BOOL,
		'question_libre'				=> DataHelper::DATA_TYPE_BOOL,
		'question_libre_checkbox'		=> DataHelper::DATA_TYPE_BOOL,
		'question_lignes'				=> DataHelper::DATA_TYPE_INT,
		'question_titre'				=> DataHelper::DATA_TYPE_STR,

		// RÉPONSES ********************************************************** (ordre alphabétique)
		'reponse_id'					=> DataHelper::DATA_TYPE_INT,
		'reponse_texte'					=> DataHelper::DATA_TYPE_TXT,
		'reponse_penalite'				=> DataHelper::DATA_TYPE_MYFLT_ABS,
		'reponse_sanction'				=> DataHelper::DATA_TYPE_BOOL,
		'reponse_valeur'				=> DataHelper::DATA_TYPE_MYFLT_ABS,
		'reponse_valide'				=> DataHelper::DATA_TYPE_BOOL,

		// BIBLIOTHÈQUE ****************************************************** (ordre alphabétique)
		'bibliotheque_id'				=> DataHelper::DATA_TYPE_INT
	);

}
