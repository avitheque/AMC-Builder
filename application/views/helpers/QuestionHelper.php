<?php
/**
 * @brief	Helper de création d'une question QCM
 *
 * Vue de contenu du formulaire permettant de créer ou de modifier une question QCM.
 *
 * @li Les champs INPUT de type TEXT sont limités en taille en rapport avec le champ en base de données.
 * Les constantes de la classe FormulaireManager::*_MAXLENGHT doivent être impactés si la structure de la table est modifiée.
 *
 * @name		QuestionHelper
 * @category	Helper
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 61 $
 * @since		$LastChangedDate: 2017-07-08 15:25:46 +0200 (Sat, 08 Jul 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class QuestionHelper {

	const		QUESTION_FORMAT_ID		= "Q%03d";
	const		QUESTION_FORMAT_NUMBER	= "%03d";

	/**
	 * @brief	Identifiant du formulaire.
	 * @li	Exploité lors de la construction du QCM.
	 * @var		bool
	 */
	private		$_nIdFormulaire			= 0;

	/**
	 * @brief	Tableau PHP représentant le contenu HTML du questionnaire.
	 * @var		array
	 * @code
	 * 		// Données relatives à une question
	 * 		$aQuestions['question_id']					: Identifiant de la question (en Base de données)
	 * 		$aQuestions['question_titre']				: Titre de la question
	 * 		$aQuestions['question_bareme']				: Nombre de points affecté à la question
	 * 		$aQuestions['question_stricte']				: Flag de réponse stricte attendue à la question (tout ou rien)
	 * 		$aQuestions['question_penalite']			: Facteur de pénalité (en %) si le candidat répond mal à la question
	 * 		$aQuestions['question_enonce']				: Texte de l'énoncé de la question
	 * 		$aQuestions['question_libre']				: (Optionnel) Flag de saisie libre de la réponse par le candidat
	 * 		$aQuestions['question_lignes']				: (Optionnel) Nombre de lignes pour la réponse libre
	 *
	 * 		// Ensembles des réponses à une question
	 * 		$aQuestions['reponse_id'][$nQuestion]		: Identifiant de la réponse (en Base de données)
	 * 		$aQuestions['reponse_texte'][$nQuestion]	: Texte de la réponse
	 * 		$aQuestions['reponse_valide'][$nQuestion]	: Flag de réponse valide
	 * 		$aQuestions['reponse_valeur'][$nQuestion]	: Points donnés au candidat s'il valide la réponse
	 * 		$aQuestions['reponse_sanction'][$nQuestion]	: Flag de réponse mauvaise
	 * 		$aQuestions['reponse_penalite'][$nQuestion]	: Points retirés si la réponse est sélectionnée par le candidat
	 * @endcode
	 */
	private		$_aQuestions			= false;

	/**
	 * @brief	Accès au formulaire en lecture seule.
	 * @li	Exploité lors de la validation et la génération du QCM.
	 * @var		bool
	 */
	private		$_bReadonly				= false;

	/**
	 * @brief	Désactive certains boutons du formulaire.
	 * @li	Exploité lors de l'importation.
	 * @var		bool
	 */
	private		$_bDisable				= false;

	/**
	 * @brief	Affichage du numéro de question.
	 * @var		bool
	 */
	private		$_bShowQuestion			= true;

	/**
	 * @brief	Réponse stricte attendue par défaut.
	 * @var		bool
	 */
	private		$_bStrict				= false;		// Information du formulaire
	private		$_bStrictQuestion		= false;
	private		$_bLibreQuestion		= false;
	private		$_sClassLibreCorrection	= null;
	private		$_sClassLibreReponse	= null;
	private		$_sClassListeReponse	= null;

	/**
	 * @brief	Rendu du contenu version mini.
	 * @var		bool
	 */
	private		$_bMiniRender			= false;

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_html					= "";

	/**
	 * @brief	Liste des identifiants de questions QCM.
	 * @var		array
	 */
	private 	$_aListeIdQuestion		= array();

	/**
	 * @brief	Liste des types de questions QCM.
	 * @var		array
	 */
	private 	$_aListeTypeQuestion	= array();

	const		TYPE_LIBRE				= "libre";
	const		TYPE_MULTIPLE			= "multiple";
	const		TYPE_UNIQUE				= "unique";

	/**
	 * @brief	Constructeur de la classe
	 *
	 * @param	array	$aQuestions			: tableau PHP représentant le contenu HTML du questionnaire.
	 * @code
	 * 		// Données relatives à une question
	 * 		$aQuestions['question_id']					: Identifiant de la question (en Base de données)
	 * 		$aQuestions['question_titre']				: Titre de la question
	 * 		$aQuestions['question_bareme']				: Nombre de points affecté à la question
	 * 		$aQuestions['question_stricte']				: Flag de réponse stricte attendue à la question (tout ou rien)
	 * 		$aQuestions['question_penalite']			: Facteur de pénalité (en %) si le candidat répond mal à la question
	 * 		$aQuestions['question_enonce']				: Texte de l'énoncé de la question
	 * 		$aQuestions['question_libre']				: (Optionnel) Flag de saisie libre de la réponse par le candidat
	 * 		$aQuestions['question_lignes']				: (Optionnel) Nombre de lignes pour la réponse libre
	 *
	 * 		// Ensembles des réponses à une question
	 * 		$aQuestions['reponse_id'][$nQuestion]		: Identifiant de la réponse (en Base de données)
	 * 		$aQuestions['reponse_texte'][$nQuestion]	: Texte de la réponse
	 * 		$aQuestions['reponse_valide'][$nQuestion]	: Flag de réponse valide
	 * 		$aQuestions['reponse_valeur'][$nQuestion]	: Points donnés au candidat s'il valide la réponse
	 * 		$aQuestions['reponse_sanction'][$nQuestion]	: Flag de réponse mauvaise
	 * 		$aQuestions['reponse_penalite'][$nQuestion]	: Points retirés si la réponse est sélectionnée par le candidat
	 * @endcode
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @param	boolean	$bStrict		: Réponse stricte aux questions par défaut.
	 * @param	boolean	$bDisable		: Fait disparaître certains boutons.
	 * @return	string
	 */
	public function __construct($aQuestions = array(), $bReadonly = false, $bStrict = false, $bDisable = false) {
		// Initialisation du contenu
		$this->_aQuestions				= $aQuestions;

		//#########################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#########################################################################################

		// Lecture par défaut
		$this->_bReadonly				= $bReadonly;
		$this->_bStrict					= $bStrict;

		// Désactivation de certains boutons du formulaire
		$this->_bDisable				= $this->_bReadonly ? true : $bDisable;
	}

	/**
	 * @brief	Initialise les variables du Helper.
	 *
	 * @li	Permet de contruire les questions pas à pas.
	 *
	 * @return	void
	 */
	public function init() {
		$this->_html					= "";
		$this->_aListeIdQuestion		= array();
		$this->_aListeTypeQuestion		= array();
	}

	/**
	 * @brief	Changement de l'identifiant du formulaire
	 *
	 * @param	integer	$nIdFormulaire		: identifiant du formulaire.
	 * @return	void
	 */
	public function setIdFormulaire($nIdFormulaire) {
		$this->_nIdFormulaire			= $nIdFormulaire;
	}

	/**
	 * @brief	Changement du rendu du questionnaire en miniatures
	 *
	 * @li	Seules les entêtes des questions seront construites.
	 * @li	En rendu miniature, le contenu du questionnaire n'est accessible qu'en lecture seule.
	 *
	 * @param	boolean	$bMiniRender	: Rendu miniature du QCM.
	 * @return	void
	 */
	public function setMiniRender($bMiniRender = true) {
		$this->_bMiniRender				= $bMiniRender;
	}

	/**
	 * @brief	Changement du rendu de la question
	 *
	 * @param	boolean	$bShowQuestion	: Rendu miniature du QCM.
	 * @return	void
	 */
	public function setShowQuestion($bShowQuestion = true) {
		$this->_bShowQuestion			= $bShowQuestion;
	}

	/**
	 * @brief	Construction des réponses à une question.
	 *
	 * @param	integer		$nQuestion		: occurrence de la question.
	 * @param	integer		$nNbMaxReponses	: nombre de réponses maxi par question.
	 */
	private function _buildResponsesQuestion($nQuestion = 0, $nNbMaxReponses = 1) {
		// Icône indicateur de champ saisissable
		$sPencilIcon					= "<span class=\"ui-icon ui-icon-pencil inline-block absolute align-sub\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly						= "";
		$sDisabled						= "";
		if ($this->_bReadonly) {
			$sReadonly					= "readonly=\"readonly\"";
			$sDisabled					= "disabled=\"disabled\"";
			$sPencilIcon				= "";
		}

		// Récupération du nombre de réponses
		if (array_key_exists('reponse_id', $this->_aQuestions) && isset($this->_aQuestions['reponse_id'][$nQuestion]) && count($this->_aQuestions['reponse_id'][$nQuestion]) > $nNbMaxReponses) {
			// Recherche si le nombre de réponse est valide
			$nNbMaxReponses = count($this->_aQuestions['reponse_id'][$nQuestion]);
		}

		// Construction de la liste des réponses possibles
		$this->_html 					.= "		<ol id=\"reponses_" . $nQuestion . "\" class=\"margin-right-0 " . $this->_sClassListeReponse . "\">";

		// Boucle de création de la liste des réponses
		$nCountChoix					= 0;
		$nCountReponse					= 0;
		for ($nReponse = 0 ; $nReponse < $nNbMaxReponses ; $nReponse++) {
			// Identifiant de la réponse en base
			$nId						= null;
			if (isset($this->_aQuestions['reponse_id'][$nQuestion])) {
				$nId					= DataHelper::get($this->_aQuestions['reponse_id'][$nQuestion],			$nReponse,	DataHelper::DATA_TYPE_INT,		$nId);
			}

			// Texte de la réponse
			$sTexteReponse				= null;
			if (isset($this->_aQuestions['reponse_texte'][$nQuestion])) {
				$sTexteReponse			= DataHelper::get($this->_aQuestions['reponse_texte'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_TXT,		$sTexteReponse);
			}

			// Fonctionnalité réalisée si le texte de la réponse n'est pas vide en lecture seule
			if ($this->_bReadonly && strlen($sTexteReponse) == 0) {
				// Passage à la réponse suivante
				continue;
			} else {
				// Incrémentation du numéro de réponse
				$nCountReponse++;
			}

			// Validité de la réponse
			$bValideReponse				= false;
			if (isset($this->_aQuestions['reponse_valide'][$nQuestion])) {
				$bValideReponse			= DataHelper::get($this->_aQuestions['reponse_valide'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_BOOL,		$bValideReponse);

				// Fonctionnalité réalisée si la question est valide
				if ($bValideReponse) {
					$nCountChoix++;
				}
			}

			// Valeur de la réponse
			$fValeurReponse				= 0;
			if (isset($this->_aQuestions['reponse_valeur'][$nQuestion])) {
				$fValeurReponse			= DataHelper::get($this->_aQuestions['reponse_valeur'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS,$fValeurReponse);
			}

			// Valeur de la pénalité de la réponse
			$fPenaliteReponse			= 0;
			if (isset($this->_aQuestions['reponse_penalite'][$nQuestion])) {
				$fPenaliteReponse		= DataHelper::get($this->_aQuestions['reponse_penalite'][$nQuestion],	$nReponse,	DataHelper::DATA_TYPE_MYFLT_ABS,$fPenaliteReponse);
			}

			// Identifiants CSS des champ de la réponse
			$sIdReponse					= "idReponse_"	. $nQuestion . "_" . $nReponse;
			$sIdValide					= "idValide_"	. $nQuestion . "_" . $nReponse;
			$sIdValeur					= "idValeur_"	. $nQuestion . "_" . $nReponse;
			$sIdSanction				= "idSanction_"	. $nQuestion . "_" . $nReponse;
			$sIdPenalite				= "idPenalite_"	. $nQuestion . "_" . $nReponse;

			// Sanction de la réponse réalisée si la pénalité est supérieur à 0
			$bSanctionReponse			= false;
			if (!empty($fPenaliteReponse) && isset($this->_aQuestions['reponse_sanction'][$nQuestion])) {
				$bSanctionReponse		= DataHelper::get($this->_aQuestions['reponse_sanction'][$nQuestion],	$nReponse,	DataHelper::DATA_TYPE_BOOL,		$bSanctionReponse);
			}

			// Options du champ [Valide]
			$sValideChecked				= $bValideReponse			? "checked=\"checked\""		: "";
			$sClassReponse				= "hidden";
			$sClassSanction				= "hidden";

			// Options du champ [Sanction]
			$sSanctionChecked			= $bSanctionReponse			? "checked=\"checked\""		: "";

			$sClassSanctionVisible		= "hidden";
			if (!$this->_bStrictQuestion) {
				$sClassSanctionVisible	= "";
				$sClassReponse			= $bValideReponse			? ""						: "hidden";
				$sClassSanction			= $bSanctionReponse			? ""						: "hidden";
			}

			// Ajout d'un séparateur vertical entre les réponses
			if ($nReponse > 0) {
				$this->_html			.= "		<hr />";
			}

			// Construction de la réponse
			$this->_html				.= "		<li id=\"reponse_" . $nQuestion . "_" . $nReponse . "\" class=\"max-width inline-block\">
														<div class=\"max-width\">
															<input type=\"hidden\" name=\"reponse_id[" . $nQuestion . "][" . $nReponse . "]\" value=\"" . $nId . "\" />

															<label for=\"" . $sIdReponse . "\" class=\"strong black\">Réponse n°" . $nCountReponse . "</label>
															<textarea id=\"" . $sIdReponse . "\" class=\"max-width\" name=\"reponse_texte[" . $nQuestion . "][" . $nReponse . "]\" $sReadonly>" . $sTexteReponse . "</textarea>
															$sPencilIcon
														</div>
														<div class=\"max-width\">
															<dl class=\"no-margin reponse_valide_" . $nQuestion . "\">
																<dt>
																	<input type=\"checkbox\" id=\"" . $sIdValide . "\" name=\"reponse_valide[" . $nQuestion . "][" . $nReponse . "]\" value=\"true\" $sValideChecked $sDisabled/>
																			<label for=\"" . $sIdValide . "\">Valide</label>
																</dt>
																<dd class=\"valide " . $sClassReponse . "\" id=\"valeur_" . $nQuestion . "_" . $nReponse . "\">
																	<label for=\"" . $sIdValeur . "\"><u>Valeur de la réponse :</u></label>
																	<input maxlength=6 type=\"text\" id=\"" . $sIdValeur . "\" class=\"decimal center width-60\" name=\"reponse_valeur[" . $nQuestion . "][" . $nReponse . "]\" value=\"" . str_replace(".", ",", $fValeurReponse) . "\" $sReadonly/>
																			<label for=\"" . $sIdValeur . "\">%</label>
																</dd>
															</dl>
															<dl class=\"no-margin reponse_sanction_" . $nQuestion . " $sClassSanctionVisible\">
																<dt>
																	<input type=\"checkbox\" id=\"" . $sIdSanction . "\" name=\"reponse_sanction[" . $nQuestion . "][" . $nReponse . "]\" value=\"true\" $sSanctionChecked $sDisabled/>
																			<label for=\"" . $sIdSanction . "\">Sanction</label>
																</dt>
																<dd class=\"sanction " . $sClassSanction . "\" id=\"penalite_" . $nQuestion . "_" . $nReponse . "\">
																	<label for=\"" . $sIdPenalite . "\"><u>Nombre de points à retirer :</u></label>
																	<input maxlength=3 type=\"text\" id=\"" . $sIdPenalite . "\" class=\"decimal center width-60\" name=\"reponse_penalite[" . $nQuestion . "][" . $nReponse . "]\" value=\"" . str_replace(".", ",", $fPenaliteReponse) . "\" $sReadonly/>
																</dd>
															</dl>
														</div>
													</li>";
		}

		// Détermination du type de question
		if ($this->_bLibreQuestion) {
			// La question est libre
			$this->_aListeTypeQuestion[$nQuestion] = self::TYPE_LIBRE;
		} else {
			// La question est à choix multiple ou à choix unique
			$this->_aListeTypeQuestion[$nQuestion] = ($nCountChoix > 1) ? self::TYPE_MULTIPLE : self::TYPE_UNIQUE;
		}
	}

	/**
	 * @brief	Construction d'une question.
	 *
	 * @param	integer		$nQuestion		: occurrence de la question.
	 * @param	integer		$nNbMaxReponses	: nombre de réponses maxi par question.
	 */
	public function buildQuestion($nQuestion = 0, $nNbMaxReponses = 1) {
		// Icône indicateur de champ saisissable
		$sPencilIcon					= "<span class=\"ui-icon ui-icon-pencil inline-block absolute\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly						= "";
		$sDisabled						= "";
		if ($this->_bReadonly) {
			$sReadonly					= "readonly=\"readonly\"";
			$sDisabled					= "disabled=\"disabled\"";
			$sPencilIcon				= "";
		}

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE RELATIF AUX GÉNÉRATITÉS DU QUESTIONNAIRE QCM
		//#########################################################################################

		// Nombre maximum de réponses par question
		$nNbMaxReponses					= DataHelper::get($this->_aQuestions,	'formulaire_nb_max_reponses',		DataHelper::DATA_TYPE_INT_ABS,			FormulaireManager::NB_MAX_REPONSES_DEFAUT);
		// Pénalité des questions du formulaire
		$pPenaliteFormulaire			= DataHelper::get($this->_aQuestions,	'formulaire_penalite',				DataHelper::DATA_TYPE_INT_ABS,			FormulaireManager::PENALITE_DEFAUT);
		// Nombre de question du formulaire
		$nNbTotalQuestions				= DataHelper::get($this->_aQuestions,	'formulaire_nb_total_questions',	DataHelper::DATA_TYPE_INT_ABS,			FormulaireManager::NB_TOTAL_QUESTIONS_DEFAUT);

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE RELATIF AUX QUESTIONS
		//#########################################################################################

		// Saisie des réponses strictes par défaut
		$sStrictFormulaireValue			= $this->_bStrict			? "true"					: "false";
		$sStrictChecked					= $this->_bStrict			? "checked=\"checked\""		: "";
		$sClassFacteur					= $this->_bStrict			? "hidden"					: "";

		// Titre de la question
		$sTitre							= null;
		if (array_key_exists("question_titre", $this->_aQuestions)) {
			$sTitre						= DataHelper::get($this->_aQuestions['question_titre'],		$nQuestion,		DataHelper::DATA_TYPE_TXT,				$sTitre);
		}

		// Fonctionnalité réalisée si le titre est trop long
		if (strlen($sTitre) > FormulaireManager::QUESTION_TITRE_MAXLENGTH) {
			$sTitre						= DataHelper::subString($sTitre, 0, FormulaireManager::QUESTION_TITRE_MAXLENGTH - 3);
		}

		// Identifiant de la question
		$nIdQuestion					= null;
		if (array_key_exists("question_id", $this->_aQuestions)) {
			$nIdQuestion				= DataHelper::get($this->_aQuestions['question_id'],		$nQuestion,		DataHelper::DATA_TYPE_INT,				$nIdQuestion);

			// Ajout de l'identifiant de la question à la liste
			if (!empty($nIdQuestion)) {
				$this->_aListeIdQuestion[$nQuestion] = $nIdQuestion;
			}
		}

		// Barème de la question
		$fBareme						= FormulaireManager::QUESTION_BAREME_DEFAUT;
		if (array_key_exists("question_id", $this->_aQuestions)) {
			$fBareme					= DataHelper::get($this->_aQuestions['question_bareme'], 	$nQuestion,		DataHelper::DATA_TYPE_MYFLT_ABS,		$fBareme);
		}

		// Facteur de pénalité
		$pPenalite						= $pPenaliteFormulaire;
		if (array_key_exists("question_penalite", $this->_aQuestions)) {
			$pPenalite					= DataHelper::get($this->_aQuestions['question_penalite'],	$nQuestion,		DataHelper::DATA_TYPE_INT_ABS,			$pPenalite);
		}

		// Énoncé de la question
		$sEnonce						= null;
		if (array_key_exists("question_enonce", $this->_aQuestions)) {
			$sEnonce					= DataHelper::get($this->_aQuestions['question_enonce'],	$nQuestion,		DataHelper::DATA_TYPE_TXT,				$sEnonce);
		}

		// Attente d'une réponse stricte
		$this->_bStrictQuestion			= $this->_bStrict;
		if (array_key_exists("question_stricte", $this->_aQuestions)) {
			// Traitement de la valeur sous forme INTEGER
			$this->_bStrictQuestion		= DataHelper::get($this->_aQuestions['question_stricte'],	$nQuestion,		DataHelper::DATA_TYPE_BOOL,				$this->_bStrictQuestion);
		}
		$sStrictValue					= $this->_bStrictQuestion	? "true"					: "false";
		$sStrictChecked					= $this->_bStrictQuestion	? "checked=\"checked\""		: "";
		$sClassFacteur					= $this->_bStrictQuestion	? "hidden"					: "";

		// Saisie libre de la réponse (pas de case à cocher)
		$this->_bLibreQuestion			= null;
		if (array_key_exists("question_libre", $this->_aQuestions)) {
			$this->_bLibreQuestion		= DataHelper::get($this->_aQuestions['question_libre'],		$nQuestion,		DataHelper::DATA_TYPE_BOOL,				$this->_bLibreQuestion);
		}
		$sLibreValue					= $this->_bLibreQuestion	? "true"					: "false";
		$sLibreChecked					= $this->_bLibreQuestion	? "checked=\"checked\""		: "";
		$this->_sClassLibreReponse	 	= $this->_bLibreQuestion	? ""						: "hidden";
		$this->_sClassListeReponse	 	= $this->_bLibreQuestion	? "hidden"					: "";

		// Nombre de lignes par défaut pour la zone de saisie
		$nLignesQuestion				= FormulaireManager::QUESTION_LIBRE_LIGNES_DEFAUT;
		if (array_key_exists("question_lignes", $this->_aQuestions)) {
			$nLignesQuestion			= DataHelper::get($this->_aQuestions['question_lignes'],	$nQuestion,		DataHelper::DATA_TYPE_INT_ABS,			$nLignesQuestion);
		}

		// Informations supplémentaires
		$sInformations					= $this->_bLibreQuestion ? "<span class=\"small right italic pointer\">(Saisie libre)</span>" : "";
		$sInformations					.= !empty($sTitre) ? "<span class=\"small flex pointer italic margin-left-25\">" . $sTitre . "</span>" : "";

		$sRemoveQuestion				= "";
		// Fonctionnalité réalisable uniquement si le formulaire est en cours de rédaction et que le formulaire existe en base
		if (!$this->_bDisable && !empty($this->_nIdFormulaire)) {
			// Bouton de suppression
			$sRemoveQuestion			.= "<button type=\"submit\" class=\"no-margin red confirm right delete\" name=\"button\" value=\"retirer_" . $nQuestion . "\" title=\"Retirer la question au QCM\">X</button>";
		}

		//#########################################################################################
		// CONSTRUCTION DU CODE HTML
		//#########################################################################################
		// Titre de la question
		$sTitleQuestion					= null;
		// Identifiant de la question courante
		$nCurrentQuestionNumber			= sprintf(self::QUESTION_FORMAT_NUMBER,	$nQuestion + 1);
		$sCurrentQuestionId				= sprintf(self::QUESTION_FORMAT_ID,		$nQuestion + 1);
		if (is_numeric($nQuestion) && $this->_bShowQuestion) {
			$sTitleQuestion				= "Question n°" . $nCurrentQuestionNumber;
		}

		$sInfoNombreReponses			= null;
		if (!$this->_bLibreQuestion && isset($this->_aQuestions['reponse_id'][$nQuestion])) {
			// Initialisation des informations
			$sInfoNombreReponses		= "<div class=\"right small italic nowrap\">";

			// Comptage du nombre de réponses
			$nbReponses					= 0;
			for ($nReponse = 0 ; $nReponse < count($this->_aQuestions['reponse_id'][$nQuestion]) ; $nReponse++) {
				// Récupération du texte de la réponse
				$sTexteReponse			= DataHelper::get($this->_aQuestions['reponse_texte'][$nQuestion],		$nReponse,	DataHelper::DATA_TYPE_TXT,	null);
				// Fonctionnalité réalisée si le texte de la réponse n'est pas vide
				if (strlen($sTexteReponse) > 0) {
					$nbReponses++;
				}
			}

			// Comptage du nombre de réponses valides
			$nbReponsesValides			= 0;
			$aListeValides				= DataHelper::get($this->_aQuestions['reponse_valide'],					$nQuestion, DataHelper::DATA_TYPE_ARRAY, array());
			foreach ($aListeValides as $bValide) {
				// Le contenu est BOOLEAN
				if ((bool)$bValide) {
					$nbReponsesValides++;
				}
			}

			// Comptage du nombre de réponses sanctionnées
			$nbReponsesSanctions		= 0;
			$aListeSanctions			= DataHelper::get($this->_aQuestions['reponse_penalite'],				$nQuestion, DataHelper::DATA_TYPE_ARRAY, array());
			foreach ($aListeSanctions as $nPenalite) {
				// Le contenu est NUMERIQUE
				if ($nPenalite) {
					$nbReponsesSanctions++;
				}
			}

			// Inidicateur du nombre de réponses
			$sPluriel					= ($nbReponses > 1)				? "s"	: "";
			$sInfoNombreReponses		.= "<span class=\"strong orange\">" . $nbReponses . " réponse" . $sPluriel . "</span>";

			if (!$this->_bMiniRender) {
				// Inidicateur du nombre de réponses
				$sPlurielValides		= ($nbReponsesValides > 1) ? "s" : "";
				$sInfoNombreReponses	.= "&nbsp;(<span class=\"green\">" . $nbReponsesValides . " bonne" . $sPlurielValides . "</span>&nbsp;;&nbsp;";

				// Inidicateur du nombre de sanctions
				$sPlurielSanctions		= ($nbReponsesSanctions > 1) ? "s" : "";
				$sInfoNombreReponses	.= "<span class=\"red\">" . $nbReponsesSanctions . " sanction" . $sPlurielSanctions . "</span>)";
			}

			// Finalisation des informations
			$sInfoNombreReponses		.= "</div>";
		}

		// Construction de la question QCM
		if ($this->_bMiniRender) {
			// Traitement du titre en miniature
			$sMiniTitre 				= DataHelper::subString($sTitre,	0, GalleryHelper::MINI_TITRE_LENGHT);
			// Traitement de l'énoncé en miniature
			$sMiniEnonce				= DataHelper::subString($sEnonce,	0, GalleryHelper::MINI_ENONCE_LENGHT);
			// Traitement des informations en miniature
			$sMiniInformations			= $this->_bLibreQuestion ? "<span class=\"small strong right italic pointer\">(Saisie libre)</span>" : $sInfoNombreReponses;

			// Construction de la miniature
			$this->_html				.= "<article class=\"miniature padding-0\" title=\"" . $sEnonce . "\" id=\"mini-Q" . $sCurrentQuestionId . "\">
												<h3 class=\"strong left\">" . $sMiniTitre . "</h3>
												<input type=\"hidden\" id=\"idBibliotheque\" name=\"bibliotheque_id[]\" value=\"" . $nIdQuestion . "\" />
												<p>
													" . $sMiniEnonce . "
												</p>
												" . $sMiniInformations . "
											</article>";

		} else {
			// Texte de la correction
			$sTexteCorrection			= DataHelper::get($this->_aQuestions['question_correction'],			$nQuestion,	DataHelper::DATA_TYPE_TXT,		"");
			$sIdCorrection				= "idCorrection_"	. $nQuestion;

			// Construction complète
			$this->_html				.= "<h3 class=\"item-title\" id=\"" . $sCurrentQuestionId . "\" title=\"" . $sEnonce . "\">" . $sTitleQuestion . $sInfoNombreReponses . $sInformations . "</h3>
											<div class=\"item-content auto-height\">
												" . $sRemoveQuestion . "
												<table class=\"max-width\">
													<tr>
														<td class=\"questions align-top\">
															<input type=\"hidden\" id=\"idQuestion\" name=\"question_id[" . $nQuestion . "]\" value=\"" . $nIdQuestion . "\" />
			
															<div class=\"no-wrap padding-bottom-25\">
																<label for=\"idTitre_" . $nQuestion . "\" class=\"strong black\">Titre</label>
																<br />
																<input maxlength=" . FormulaireManager::QUESTION_TITRE_MAXLENGTH . " type=\"text\" id=\"idTitre_" . $nQuestion . "\" class=\"max-width left\" name=\"question_titre[" . $nQuestion . "]\" value=\"" . $sTitre . "\" $sReadonly/>
																$sPencilIcon
																<div class=\"no-wrap strong left max-width\">
																	<label for=\"idBareme_" . $nQuestion . "\">Barème par défaut</label>
																	<input maxlength=" . FormulaireManager::QUESTION_BAREME_MAXLENGTH . " type=\"text\" id=\"idBareme_" . $nQuestion . "\" class=\"decimal center width-50\" name=\"question_bareme[" . $nQuestion . "]\" value=\"" . str_replace(".", ",", $fBareme) . "\" $sReadonly/>
																	<label for=\"idBareme_" . $nQuestion . "\">/&nbsp;" . $nNbTotalQuestions . "</label>
																</div>
															</div>
															<hr class=\"margin-V-25\"/>
															<div>
																<input type=\"checkbox\" id=\"idStrictCheckbox_" . $nQuestion . "\" name=\"question_stricte_checkbox[" . $nQuestion . "]\" value=\"true\" $sStrictChecked $sDisabled/>
																<label for=\"idStrictCheckbox_" . $nQuestion . "\">Réponse stricte attendue à la question (tout ou rien)</label>
			
																<div id=\"facteur_" . $nQuestion . "\" class=\"strong " . $sClassFacteur . "\">
																	<label for=\"idPenalite_" . $nQuestion . "\">Facteur de pénalité</label>
																	<input maxlength=" . FormulaireManager::QUESTION_PENALITE_MAXLENGTH . " type=\"text\" id=\"idPenalite_" . $nQuestion . "\" class=\"numeric center width-50\" name=\"question_penalite[" . $nQuestion . "]\" value=\"" . intval($pPenalite) . "\" $sReadonly/>
																	<label for=\"idPenalite_" . $nQuestion . "\">%</label>
																</div>
			
																<input type=\"hidden\" id=\"idStricteValue_" . $nQuestion . "\" name=\"question_stricte[" . $nQuestion . "]\" value=\"" . $sStrictValue . "\"/>
															</div>
															<hr class=\"margin-V-25\"/>
															<div>
																<input type=\"checkbox\" id=\"idLibreCheckbox_" . $nQuestion . "\" name=\"question_libre_checkbox[" . $nQuestion . "]\" value=\"true\" $sLibreChecked $sDisabled/>
																<label for=\"idLibreCheckbox_" . $nQuestion . "\">Réponse libre du candidat (pas de case à cocher)</label>
			
																<input type=\"hidden\" id=\"idLibreValue_" . $nQuestion . "\" name=\"question_libre[" . $nQuestion . "]\" value=\"" . $sLibreValue . "\"/>
			
																<div id=\"idLignesQuestion_" . $nQuestion . "\" class=\"strong " . $this->_sClassLibreReponse . "\">
																	<label for=\"idLignes_" . $nQuestion . "\">Nombre de lignes allouées pour la réponse</label>
																	<input maxlength=" . FormulaireManager::QUESTION_LIBRE_LIGNES_MAX . "type=\"number\" id=\"idLignes_" . $nQuestion . "\" class=\"numeric center width-50\" name=\"question_lignes[" . $nQuestion . "]\" value=\"" . $nLignesQuestion . "\" $sDisabled/>
																</div>
															</div>
															<hr class=\"margin-V-25\"/>
															<div>
																<label for=\"idEnonce_" . $nQuestion . "\" class=\"strong black\">Énoncé</label>
																<textarea rows=15 id=\"idEnonce_" . $nQuestion . "\" class=\"max-width\" name=\"question_enonce[" . $nQuestion . "]\" $sReadonly>" . $sEnonce . "</textarea>
																$sPencilIcon
															</div>
														</td>
														<td class=\"reponses align-bottom\">
															<ol id=\"correction_" . $nQuestion . "\" class=\"margin-right-0 " . $this->_sClassLibreReponse . "\">
																<li id=\"correction_" . $nQuestion . "_0\" class=\"max-width inline-block\">
																	<div class=\"max-width\">
																		<label for=\"" . $sIdCorrection . "\" class=\"strong black\">Réponse type attentue</label>
																		<textarea rows=" . $nLignesQuestion . " id=\"" . $sIdCorrection . "\" class=\"max-width\" name=\"question_correction[" . $nQuestion . "]\" $sReadonly>" . $sTexteCorrection . "</textarea>
																		$sPencilIcon
																	</div>
																</li>
															</ol>";

			// Construction de la liste des réponses
			$this->_buildResponsesQuestion($nQuestion, $nNbMaxReponses);

			// Finalisation de la liste des réponses
			$this->_html				.= "				</ol>
														</td>
													</tr>
												</table>
											</div>";
		}
	}

	/**
	 * @brief	Récupère la liste des identifiants de questions
	 * @return	array
	 */
	public function getAllQuestionsId() {
		return $this->_aListeIdQuestion;
	}

	/**
	 * @brief	Récupère l'identifiant d'une question par son occurrence
	 * @return	integer
	 */
	public function getQuestionIdByOccurrence($nOccurrence = 0) {
		return $this->_aListeIdQuestion[$nOccurrence];
	}

	/**
	 * @brief	Récupère la liste des types de questions
	 * @return	array
	 */
	public function getAllQuestionsType() {
		return $this->_aListeTypeQuestion;
	}

	/**
	 * @brief	Rendu final de la question
	 *
	 * @li	Possibilité de réinitialiser les variables d'instance lors d'un traitement récurrent.
	 *
	 * @param	bool		$bResetAfter	: réinitialisation des variables d'instance à la fin du rendu.
	 * @return	string
	 */
	public function renderHTML($bResetAfter = false) {
		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/QuestionnaireHelper.css");

		// Récupération du contenu HTML
		$sHTML							= $this->_html;

		// Fonctionnalité réalisée afin de réinitialiser les variables à la fin du traitement
		if ($bResetAfter) {
			// Réinitialisation des variables d'instance
			$this->init();
		}

		// Renvoi du code HTML
		return $sHTML;
	}
}
