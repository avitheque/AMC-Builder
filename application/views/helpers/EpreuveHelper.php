<?php
/**
 * @brief	Helper de création du formulaire QCM
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier un QCM.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe FormulaireManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		FormulaireHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 78 $
 * @since		$LastChangedDate: 2017-08-29 18:14:10 +0200 (Tue, 29 Aug 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EpreuveHelper extends FormulaireHelper {

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @param	boolean	$bDisable		: Fait disparaître certains boutons.
	 * @param	boolean	$bBibliotheque	: Fait apparaître la bibliothèque.
	 * @param	boolean	$bGeneration	: Fait apparaître la zone de paramétrage supplémentaire.
	 *
	 * @return	void
	 */
	public function __construct($bReadonly = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage		= InstanceStorage::getInstance();

		//#########################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#########################################################################################

		// Lecture par défaut
		$this->_bReadonly				= $bReadonly;

		// Désactivation de certains boutons du formulaire
		$this->_bDisable				= $this->_bReadonly ? true : false;

		// Nom de session du QCM
		$sSessionNameSpace				= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Données du QCM
		$this->_aQCM					= $this->_oInstanceStorage->getData($sSessionNameSpace);

		//#########################################################################################
		// RÉCUPÉRATION DE L'ONGLET SÉLECTIONNÉ PAR DÉFAUT
		//#########################################################################################
		$this->_action					= DataHelper::get($this->_aQCM, 'action_button',					DataHelper::DATA_TYPE_STR,		null);
		$this->_activeTab				= DataHelper::get($this->_aQCM, 'formulaire_active_tab',			DataHelper::DATA_TYPE_INT,		FormulaireManager::TAB_DEFAULT);

		// Dans le cas de la génération : Ajout d'un onglet récapitulatif en premier
		$this->_html					.= "<section id=\"controle\" class=\"tabs\">
												<ul>
													<li><a href=\"#tabs-epreuve\">Épreuve</a></li>
													<li><a href=\"#tabs-questionnaire\">Questionnaire</a></li>
												</ul>
												<input type=\"hidden\" name=\"formulaire_active_tab\" value=\"" . $this->_activeTab . "\" />";

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE QCM
		//#########################################################################################

		// Zone du formulaire QCM
		$this->_buildEpreuveQCM();
	}
	/**
	 * @brief	Zone du formulaire QCM.
	 *
	 * @return	void
	 */
	protected function _buildEpreuveQCM() {
		// Initialisation du conteneur du questionnaire
		$this->_html					.= "	<section id=\"qcm\">";

		// Icône indicateur de champ saisissable
		$sPencilIcon					= "<span class=\"ui-icon ui-icon-pencil inline-block absolute\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly						= "";
		$sDisabled						= "";
		$sClassField					= "";
		if ($this->_bReadonly) {
			$sReadonly					= "readonly=\"readonly\"";
			$sDisabled					= "disabled=\"disabled\"";
			$sClassField				= "disabled";
			$sPencilIcon				= "";
		}

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE RELATIF À L'ÉPREUVE QCM
		//#########################################################################################

		// Identifiant du contrôle
		$nIdControle					= DataHelper::get($this->_aQCM, 'controle_id', 						DataHelper::DATA_TYPE_INT,		null);

		// Identifiant du questionnaire
		$nIdFormulaire					= DataHelper::get($this->_aQCM, 'formulaire_id', 					DataHelper::DATA_TYPE_INT,		null);

		// Nom du questionnaire
		$sNomFormulaire					= DataHelper::get($this->_aQCM, 'formulaire_titre',					DataHelper::DATA_TYPE_STR,		FormulaireManager::TITRE_DEFAUT);

		// Note finale du questionnaire
		$nNoteFinale					= DataHelper::get($this->_aQCM, 'formulaire_note_finale',			DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::NOTE_FINALE_DEFAUT);
		// Nombre maximum de réponses par question
		$nNbMaxReponses					= DataHelper::get($this->_aQCM, 'formulaire_nb_max_reponses',		DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::NB_MAX_REPONSES_DEFAUT);
		// Pénalité des questions du formulaire
		$pPenaliteFormulaire			= DataHelper::get($this->_aQCM, 'formulaire_penalite',				DataHelper::DATA_TYPE_INT_ABS,	FormulaireManager::PENALITE_DEFAUT);

		// Nombre de question du formulaire
		$nNbTotalQuestions				= DataHelper::get($this->_aQCM, 'formulaire_nb_total_questions',DataHelper::DATA_TYPE_INT_ABS,		FormulaireManager::NB_TOTAL_QUESTIONS_DEFAUT);

		// Présentation du questionnaire
		$sPresentation					= DataHelper::get($this->_aQCM, 'formulaire_presentation',			DataHelper::DATA_TYPE_TXT,		FormulaireManager::PRESENTATION_DEFAUT);

		//#########################################################################################
		// CONSTRUCTION DU CODE HTML
		//#########################################################################################

		// Questionnaire
		$this->_html					.= "	<div id=\"tabs-epreuve\">
													<span id=\"tabs-epreuve-top\"><a class=\"page-top\" href=\"#tabs-epreuve-bottom\" title=\"Bas de page...\">" . self::ICON_DOWN . "</a></span>
													<fieldset class=\"" . $sClassField . "\" id=\"general\"><legend>Informations sur l'épreuve QCM</legend>
														<ol>
															<li>
																<h3 class=\"strong center\">&#151;&nbsp;" . $sNomFormulaire . "&nbsp;&#151;</h3>
																<input type=\"hidden\" id=\"idControle\" name=\"controle_id\" value=\"" . $nIdControle . "\" />
															</li>
															<li class=\"max-width\">
																<hr class=\"half-width\" />
															</li>
															<li>
																<label class=\"titre\">Nombre de réponses maximum par question :</label>&nbsp;" . $nNbMaxReponses . "
															</li>
															<li>
																<label class=\"titre\">Nombre total de questions :</label>&nbsp;" . $nNbTotalQuestions . "
															</li>
															<li>
																<label class=\"titre\">Note finale rapportée sur :</label>&nbsp;" . $nNoteFinale . "&nbsp;points
															</li>
														</ol>
													</fieldset>
													<hr class=\"margin-V-25 blue\"/>
													<fieldset class=\"" . $sClassField . "\" id=\"presentation\"><legend>Objectif de l'épreuve</legend>
														<div class=\"margin-H-25 justify strong justify\">
															 " . nl2br($sPresentation) . "
														</div>
													</fieldset>
													<span id=\"tabs-generalite-bottom\"><a class=\"page-bottom\" href=\"#tabs-generalite-top\" title=\"Haut de page...\">" . self::ICON_UP . "</a></span>
												</div>";

		//#########################################################################################
		// CONSTRUCTION DE LA LISTE DES QUESTIONS
		//#########################################################################################

		// Identifiant de la question active
		$this->_activeQuestion			= DataHelper::get($this->_aQCM, 'formulaire_active_question',		DataHelper::DATA_TYPE_INT,		0);

		// Boucle de création de la liste des questions
		$this->_html					.= "	<div id=\"tabs-questionnaire\" class=\"active\">
													<input type=\"hidden\" name=\"formulaire_active_question\" value=\"" . $this->_activeQuestion . "\" />
													<span id=\"tabs-questionnaire-top\"><a class=\"page-top\" href=\"#tabs-questionnaire-bottom\" title=\"Bas de page...\">" . self::ICON_DOWN . "</a></span>
													<section id=\"questionnaire\" class=\"accordion ". $sClassField . "\">";

		// Initialisation du conteneur des questions
		$aListeIdQuestion = array();
		$oQuestion = new QuestionHelper($this->_aQCM, $this->_bReadonly);
		$oQuestion->setIdControle($nIdControle);
		$oQuestion->setIdFormulaire($nIdFormulaire);
		for ($nQuestion = 0 ; $nQuestion < $nNbTotalQuestions ; $nQuestion++) {
			// Construction de chaque question
			$oQuestion->buildQuestion($nQuestion, $nNbMaxReponses);
			// Mise à jour du nombre de questions
			$this->_nOccurrenceQuestion++;
		}
		// Ajout de la construction HTML au formulaire
		$this->_html 					.= $oQuestion->renderHTML();

		// Finalisation de la liste des questions
		$this->_html 					.= "		</section>
													<span id=\"tabs-questionnaire-bottom\"><a class=\"page-bottom\" href=\"#tabs-questionnaire-top\" title=\"Haut de page...\">" . self::ICON_UP . "</a></span>
												</div>
											</section>
										</section>";

		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/EpreuveHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/EpreuveHelper.js");
	}

}
