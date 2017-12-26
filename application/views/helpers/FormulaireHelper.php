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
 * @version		$LastChangedRevision: 88 $
 * @since		$LastChangedDate: 2017-12-26 11:14:42 +0100 (Tue, 26 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class FormulaireHelper {

	/**
	 * Icônes des flèches HAUT et BAS de page.
	 * @var		InstanceStorage
	 */
	const		ICON_UP					= "&#8593;";
	const		ICON_DOWN				= "&#8595;";

	/**
	 * Singleton de l'instance des échanges entre contrôleurs.
	 * @var		InstanceStorage
	 */
	protected	$_oInstanceStorage		= null;

	/**
	 * @brief	Accès au formulaire en lecture seule.
	 * @li	Exploité lors de la validation et la génération du QCM.
	 * @var		bool
	 */
	protected	$_bReadonly				= false;

	/**
	 * @brief	Désactive certains boutons du formulaire.
	 * @li	Exploité lors de l'importation.
	 * @var		bool
	 */
	protected	$_bDisable				= false;

	/**
	 * @brief	Code HTML du bouton [Ajouter une question].
	 * @li	Exploité lors de la rédaction ou la modification d'un QCM.
	 * @var		string
	 */
	private		$_sBoutonAjouter		= null;
	
	/**
	 * @brief	Identifiant du formulaire QCM.
	 * @var		integer
	 */
	protected	$_nIdFormulaire			= 0;
	
	/**
	 * @brief	Onglets HTML.
	 * @var		array
	 */
	protected	$_aTabs					= array(
		// Onglet "Épreuve" uniquement visible à partir du profil [Validator]
		0 => array(
				'href'					=> "#tabs-epreuve",
				'label'					=> "Épreuve"
		),
		// Onglet "Généralités"
		1 => array(
				'href'					=> "#tabs-generalite",
				'label'					=> "Généralités"
		),
		// Onglet "Questionnaire"
		2 => array(
				'href'					=> "#tabs-questionnaire",
				'label'					=> "Questionnaire"
		)
	);

	/**
	 * @brief	Formulaire PHP.
	 * @var		array
	 */
	protected	$_aQCM					= array();

	/**
	 * @brief	Action de l'utilisateur dans le formulaire.
	 * @var		string
	 */
	protected	$_action				= "";

	/**
	 * @brief	Formulaire HTML.
	 * @var		string
	 */
	protected	$_correction			= "";
	protected	$_html					= "";

	/**
	 * @brief	Identifiant du TABS sélectionné par défaut.
	 *
	 * @li	Par défaut, le premier identifiant est 0.
	 *
	 * @var		integer
	 */
	protected	$_activeTab				= 0;

	/**
	 * @brief	Identifiant de l'ACCORDION sélectionné par défaut.
	 *
	 * @li	Par défaut, le premier identifiant est 0.
	 *
	 * @var		integer
	 */
	protected	$_activeQuestion		= 0;

	/**
	 * @brief	Nombre de questions dans le formulaire.
	 *
	 * @li	Par défaut, la première question est à 0.
	 *
	 * @var		integer
	 */
	protected	$_nOccurrenceQuestion	= -1;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @param	boolean	$bDisable		: Fait disparaître certains boutons.
	 * @param	boolean	$bBibliotheque	: Fait apparaître la bibliothèque.
	 *
	 * @return	void
	 */
	public function __construct($bReadonly = false, $bDisable = false, $bBibliotheque = false) {
		// Récupération de l'instance du singleton InstanceStorage permettant de gérer les échanges avec le contrôleur
		$this->_oInstanceStorage		= InstanceStorage::getInstance();

		// Nom de session du QCM
		$sSessionNameSpace				= $this->_oInstanceStorage->getData('SESSION_NAMESPACE');

		// Récupération de l'instance du singleton SessionManager
		$oSessionManager				= SessionManager::getInstance($sSessionNameSpace);

		//#########################################################################################
		// INITIALISATION DES VALEURS PAR DÉFAUT
		//#########################################################################################

		// Protection du formulaire contre la modification si une épreuve est en cours
		$this->_bReadonly				= $oSessionManager->issetIndex('CONTROLE_EPREUVE_EXISTS') ? $oSessionManager->getIndex('CONTROLE_EPREUVE_EXISTS') : $bReadonly;

		// Désactivation de certains boutons du formulaire
		$this->_bDisable				= $this->_bReadonly ? true : $bDisable;

		// Données du QCM
		$this->_aQCM					= $this->_oInstanceStorage->getData($sSessionNameSpace);

		//#########################################################################################
		// RÉCUPÉRATION DE L'ONGLET SÉLECTIONNÉ PAR DÉFAUT
		//#########################################################################################
		$this->_action					= DataHelper::get($this->_aQCM, 'action_button',					DataHelper::DATA_TYPE_STR,		null);
		$this->_activeTab				= DataHelper::get($this->_aQCM, 'formulaire_active_tab',			DataHelper::DATA_TYPE_INT,		FormulaireManager::TAB_DEFAULT);

		// Dans le cas de la création / édition : Ajout d'un bouton [Ajouter une question]
		$this->_sBoutonAjouter			= "";
		if (! $this->_bDisable) {
			$this->_sBoutonAjouter		= "<button type=\"submit\" id=\"bouton-ajouter\" class=\"blue no-margin right\" name=\"button\" value=\"ajouter\" role=\"touche_N\">Ajouter une question</button>";
		}

		// Construction de l'entête du questionnaire
		$this->_html					.= "<section id=\"questionnaire\" class=\"tabs\">
												<ul>
													<li><a href=\"#tabs-generalite\">Généralités</a></li>
													<li><a href=\"#tabs-questionnaire\" class=\"ui-tabs-active\">Questionnaire</a></li>
													" . $this->_sBoutonAjouter . "
												</ul>";

		//#####################################################################################
		// CONSTRUCTION DU FORMULAIRE QCM
		//#####################################################################################
		
		// Zone du formulaire QCM
		$this->_buildFormulaireQCM($bBibliotheque);
	}

	/**
	 * @brief	Constructeur de la bibliothèque.
	 *
	 * @param	array	$aListExcludeId	: Liste des identifiants de questions à exclure.
	 * @param	boolean	$bReadonly		: Verrouillage de la modification des champs.
	 * @return	void
	 */
	private function _buildBibliotheque($aListExcludeId = array()) {
		// Fonctionnalité réalisée si le formulaire n'est pas en lecture seule
		if (! $this->_bReadonly) {
			// Fonctionnalité réalisée si au moins une question est présente
			if ($this->_nOccurrenceQuestion >= 0) {
				// Ajout d'un rappel du bouton [Ajouter une question]
				$this->_html			.= "<button type=\"submit\" id=\"bouton-ajouter-bottom\" class=\"blue margin-0 max-width margin-top-20 padding-V-20\" name=\"button\" value=\"ajouter\" role=\"touche_N\">Ajouter une nouvelle question</button>";
			}

			// Récupération de la bibliothèque de questions en rapport aux paramètres du formulaire
			$aListeBibliotheque	= $this->_oInstanceStorage->getData("liste_bibliotheque");
			// Ajout du conteneur GalleryHelper
			$oGallery					= new GalleryHelper();
			$oGallery->setExcludeByListId($aListExcludeId);

			// Parcours des éléments à ajouter dans la bibliothèque
			$aListeEnable				= array();
			if (isset($aListeBibliotheque['question_id'])) {
				// Initialisation du Helper
				$oBibliotheque			= new QuestionHelper($aListeBibliotheque, true);
				$oBibliotheque->setMiniRender(true);

				// Parcours de la liste de la bibliothèque
				for ($nOccurrence = 0 ; $nOccurrence < count($aListeBibliotheque['question_id']) ; $nOccurrence++) {
					// Construction de la bibliothèque
					$oBibliotheque->buildQuestion($nOccurrence);

					// Récupération de l'identifiant
					$nId = $oBibliotheque->getQuestionIdByOccurrence($nOccurrence);

					// Ajout de la liste des questions
					$oGallery->addItem($oBibliotheque->renderHTML(true), $nId, "/search/question?id=%d");
				}

				// Ajout de l'identifiant à la collection
				$aListeEnable[]			= $aListeBibliotheque['question_id'];
			}

			// Ajout des éléments de la bibliothèque non enregistrés
			$nCount						= 0;
			foreach ($this->_aQCM['bibliotheque_id'] as $nOccurrence => $nId) {
				if (!in_array($nId, $aListeEnable) && !in_array($nId, $this->_aQCM["question_id"])) {
					// Récupération du titre de la question
					$sTitre				= DataHelper::get($this->_aQCM['bibliotheque_titre'],			$nOccurrence, DataHelper::DATA_TYPE_TXT);
					// Traitement du titre adapté en miniature
					$sMiniTitre 		= DataHelper::subString($sTitre, 0, GalleryHelper::MINI_TITRE_LENGHT);

					// Récupération de l'énoncé de la question
					$sEnonce			= DataHelper::get($this->_aQCM['bibliotheque_enonce'],			$nOccurrence, DataHelper::DATA_TYPE_TXT);
					// Traitement de l'énoncé en miniature
					$sMiniEnonce		= DataHelper::subString($sEnonce, 0, GalleryHelper::MINI_ENONCE_LENGHT);

					// Récupération de l'aspect LIBRE de la question
					$bLibre				= DataHelper::get($this->_aQCM['bibliotheque_libre'],			$nOccurrence, DataHelper::DATA_TYPE_BOOL);

					// Récupération de nombre de réponses à la question
					$nbReponses			= DataHelper::get($this->_aQCM['bibliotheque_nombre_reponses'],	$nOccurrence, DataHelper::DATA_TYPE_INT);
					$sPluriel			= ($nbReponses > 1) ? "s" : "";

					// Inidicateur du nombre de réponses
					$sInfoReponses		= $bLibre ? "<span class=\"small strong right italic\">(Saisie libre)</span>" : "<span class=\"strong orange right italic \">" . $nbReponses . " réponse" . $sPluriel . "</span>";

					// Construction de l'interface de la question
					$sItemHTML			= $oGallery->buildMiniItem($nCount, $sMiniTitre, $sEnonce, $sMiniEnonce, $sInfoReponses);

					// Ajout de la question aux éléments du PANNEAU d'importation de la bibliothèque
					$oGallery->addItemToPanel($sItemHTML, $nId, "/search/question?id=%d");
					$nCount++;
				} else {
					unset($this->_aQCM['biliotheque_id'][$nOccurrence]);
					unset($this->_aQCM['bibliotheque_titre'][$nOccurrence]);
					unset($this->_aQCM['bibliotheque_enonce'][$nOccurrence]);
				}
			}

			// Construction de la liste des éléments à charger
			$aListeSearchItem = array(
				"domaine"				=> array('type'	=> "select",	'index'	=> "liste_domaines",		'default' => "formulaire_domaine",			'label'	=> "Domaine"),
				"sous_domaine"			=> array('type'	=> "select",	'index'	=> "liste_sous_domaines",	'default' => "formulaire_sous_domaine",		'label'	=> "Sous-domaine"),
				"categorie"				=> array('type'	=> "select",	'index'	=> "liste_categories",		'default' => "formulaire_categorie",		'label'	=> "Catégorie"),
				"sous_categorie"		=> array('type'	=> "select",	'index'	=> "liste_sous_categories",	'default' => "formulaire_sous_categorie",	'label'	=> "Sous-catégorie"),
				"orphelin"				=> array('type'	=> "checkbox",	'index'	=> null,					'default' => null,							'label'	=> "Question(s) non référencée(s)")
			);

			// Ajout de la construction HTML au formulaire
			$this->_html				.= $oGallery->renderHTML("/search/bibliotheque", $aListeSearchItem);
		};
	}

	/**
	 * @brief	Zone du formulaire QCM.
	 *
	 * @param	boolean	$bBibliotheque	: Fait apparaître la bibliothèque.
	 * @return	void
	 */
	protected function _buildFormulaireQCM($bBibliotheque = false) {
		// Initialisation du conteneur du questionnaire
		$this->_html					.= "	<section id=\"qcm\">";

		// Icône indicateur de champ saisissable
		$sPencilIcon					= "<span class=\"ui-icon ui-icon-pencil inline-block absolute\">&nbsp;</span>";

		// Variables de verrouillage des champs
		$sReadonly						= "";
		$sDisabled						= "";
		$sClassField					= "";
		// Fonctionnalité réalisée si le formulaire est protégé en écriture
		if ($this->_bReadonly) {
			$sReadonly					= "readonly=\"readonly\"";
			$sDisabled					= "disabled=\"disabled\"";
			$sClassField				= "disabled";
			$sPencilIcon				= "";
		}

		// Niveau de validation du questionnaire
		$nValidationFormulaire			= DataHelper::get($this->_aQCM, 'formulaire_validation',			DataHelper::DATA_TYPE_INT,		FormulaireManager::VALIDATION_DEFAUT);
		if (! $this->_bReadonly) {
			switch ($nValidationFormulaire) {

				// Validation en attente
				case FormulaireManager::VALIDATION_ATTENTE:
					// Affichage d'un message d'information
					ViewRender::setMessageInfo("Le formulaire est en attente de validation...");
					break;

					// Validation en réalisée
				case FormulaireManager::VALIDATION_REALISEE:
					// Affichage d'un message d'avertissement
					ViewRender::setMessageError("Le formulaire ayant été validé, toute modification devra faire l'objet d'une nouvelle validation...");
					break;

				default:
					break;
			}
		}

		//#########################################################################################
		// CONSTRUCTION DU FORMULAIRE RELATIF AUX GÉNÉRATITÉS DU QUESTIONNAIRE QCM
		//#########################################################################################

		// Identifiant du questionnaire
		$this->_nIdFormulaire			= DataHelper::get($this->_aQCM, 'formulaire_id', 					DataHelper::DATA_TYPE_INT,		null);

		// Fonctionnalité réalisée si le formulaire est valide
		if (!empty($this->_nIdFormulaire)) {
			$this->_correction			= "<a href=\"/visualisation?id_formulaire=" . $this->_nIdFormulaire . "\" class=\"button red no-margin right tooltip\" target=\"_blank\" title=\"Affiche la feuille de correction\" role=\"touche_P\">Aperçu de la correction</a>";
		}

		// Nom du questionnaire
		$sNomFormulaire					= DataHelper::get($this->_aQCM, 'formulaire_titre',					DataHelper::DATA_TYPE_STR,		null);

		// Saisie des réponses strictes par défaut
		$bStrictFormulaire				= DataHelper::get($this->_aQCM,	'formulaire_strict',				DataHelper::DATA_TYPE_BOOL,		FormulaireManager::QUESTION_STRICTE_DEFAUT);
		$sStrictFormulaireValue			= $bStrictFormulaire	? "true"				: "false";
		$sStrictChecked					= $bStrictFormulaire	? "checked=\"checked\""	: "";
		$sClassFacteur					= $bStrictFormulaire	? "hidden"				: "no-wrap";

		// Fonctionnalité réalisée si le nom du formulaire est trop long
		if (strlen($sNomFormulaire) > FormulaireManager::FORMULAIRE_NOM_MAXLENGTH) {
			$sTitre = DataHelper::subString($sNomFormulaire, 0, FormulaireManager::FORMULAIRE_NOM_MAXLENGTH - 3);
		}

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

		// Construction de la liste déroulante du DOMAINE
		$nIdDomaine						= DataHelper::get($this->_aQCM, 'formulaire_domaine',				DataHelper::DATA_TYPE_INT,		null);
		$sDomaineOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_domaines'), $nIdDomaine, '-', null, $this->_bReadonly);

		// Construction de la liste déroulante du SOUS-DOMAINE
		$nIdSousDomaine					= DataHelper::get($this->_aQCM, 'formulaire_sous_domaine',			DataHelper::DATA_TYPE_INT,		null);
		$sSousDomaineOptions			= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_sous_domaines'), $nIdSousDomaine, '-', null, $this->_bReadonly);

		// Construction de la liste déroulante de la CATÉGORIE
		$nIdCategorie					= DataHelper::get($this->_aQCM, 'formulaire_categorie',				DataHelper::DATA_TYPE_INT,		null);
		$sCategorieOptions				= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_categories'), $nIdCategorie, '-', null, $this->_bReadonly);

		// Construction de la liste déroulante de la SOUS-CATÉGORIE
		$nIdSousCategorie				= DataHelper::get($this->_aQCM, 'formulaire_sous_categorie',		DataHelper::DATA_TYPE_INT,		null);
		$sSousCategorieOptions			= HtmlHelper::buildListOptions($this->_oInstanceStorage->getData('liste_sous_categories'), $nIdSousCategorie, '-', null, $this->_bReadonly);

		//#########################################################################################
		// CONSTRUCTION DU CODE HTML
		//#########################################################################################

		// Questionnaire
		$this->_html					.= "	<div id=\"tabs-generalite\">
													<span id=\"tabs-generalite-top\"><a class=\"page-top\" href=\"#tabs-generalite-bottom\" title=\"Bas de page...\">" . self::ICON_DOWN . "</a></span>
													<fieldset class=\"" . $sClassField . "\" id=\"general\"><legend>Informations du formulaire</legend>
														<ol>
															<li>
																<label for=\"idNomFormulaire\">Nom du formulaire</label>
																<input maxlength=" . FormulaireManager::FORMULAIRE_NOM_MAXLENGTH . " type=\"text\" id=\"idNomFormulaire\" class=\"max-width\" name=\"formulaire_titre\" value=\"" . $sNomFormulaire . "\" $sReadonly />
																$sPencilIcon
																<input type=\"hidden\" id=\"idFormulaire\" name=\"formulaire_id\" value=\"" . $this->_nIdFormulaire . "\" />
																<input type=\"hidden\" id=\"validationFormulaire\" name=\"formulaire_validation\" value=\"" . $nValidationFormulaire . "\" />
															</li>
															<li class=\"inline-block max-width\">
																<hr class=\"half-width\" />
															</li>
															<li>
																<div class=\"half-width no-wrap left\">
																	<label for=\"idDomaine\">Domaine</label>
																	<select id=\"idDomaine\" name=\"formulaire_domaine\" $sDisabled>" . $sDomaineOptions . "</select>
																</div>
																<div class=\"half-width no-wrap right\">
																	<label for=\"idSousDomaine\">Sous domaine</label>
																	<select id=\"idSousDomaine\" name=\"formulaire_sous_domaine\" $sDisabled>" . $sSousDomaineOptions . "</select>
																</div>
															</li>
															<li>
																<div class=\"half-width no-wrap left\">
																	<label for=\"idCategorie\">Catégorie</label>
																	<select id=\"idCategorie\" name=\"formulaire_categorie\" $sDisabled>" . $sCategorieOptions . "</select>
																</div>
																<div class=\"half-width no-wrap right\">
																	<label for=\"idSousCategorie\">Sous catégorie</label>
																	<select id=\"idSousCategorie\" name=\"formulaire_sous_categorie\" $sDisabled>" . $sSousCategorieOptions . "</select>
																</div>
															</li>
															<li class=\"inline-block max-width\">
																<hr class=\"half-width\" />
															</li>
															<li>
																<input type=\"checkbox\" id=\"idStrictCheckboxDefaut\" name=\"strict_defaut\" value=\"true\" $sStrictChecked $sDisabled/>
																<label for=\"idStrictCheckboxDefaut\">Réponse stricte attendue aux questions par défaut (tout ou rien)</label>
								
																<input type=\"hidden\" id=\"idFormulaireStrictDefaut\" name=\"formulaire_strict\" value=\"" . $sStrictFormulaireValue . "\" />
															</li>
															<li id=\"idPenalite\" class=\"" . $sClassFacteur . "\">
																<label for=\"idPenaliteDefaut\" class=\"strong\">Facteur de pénalité par défaut pour les questions à choix multiple</label>
																<input maxlength=3 type=\"number\" id=\"idPenaliteDefaut\" class=\"numeric center width-50\" name=\"formulaire_penalite\" value=\"" . $pPenaliteFormulaire . "\" $sReadonly/>
																<label for=\"idPenaliteDefaut\" class=\"strong\">%</label>";

		// Ajout d'un bouton permettant de forcer la pénalité par défaut s'il y a au moins une question
		if (! $this->_bReadonly && $this->_aQCM['formulaire_nb_total_questions']) {
			$this->_html				.= "					<div class=\"max-width center\">
																	<button type=\"submit\" class=\"red\" name=\"button\" value=\"forcer\">Forcer la pénalité pour toutes les questions</button>
																</div>";
		}

		// Poursuite de la création du formulaire
		$this->_html					.= "				</li>
															<li class=\"max-width\">
																<hr class=\"half-width\" />
															</li>
															<li>
																<label for=\"idNbMaxReponses\">Nombre de réponses maximum par question</label>
																<input type=\"number\" id=\"idNbMaxReponses\" class=\"numeric center width-50\" name=\"formulaire_nb_max_reponses\" value=\"" . $nNbMaxReponses . "\" $sReadonly/>
															</li>
															<li>
																<label for=\"idNbTotalQuestions\">Nombre total de questions</label>
																<input maxlength=3 type=\"number\" id=\"idNbTotalQuestions\" class=\"numeric center width-50\" name=\"formulaire_nb_total_questions\" value=\"" . $nNbTotalQuestions . "\" $sReadonly/>
															</li>";

		// Ajout d'un bouton permettant de forcer la pénalité par défaut s'il y a au moins une question
		if (! $this->_bReadonly) {
			$this->_html				.= "				<li class=\"max-width center\">
																<button type=\"submit\" class=\"blue\" name=\"button\" value=\"actualiser\">Actualiser le questionnaire</button>
															</li>";
		}

		// Poursuite de la création du formulaire
		$this->_html					.= "				<li class=\"max-width\">
																<hr class=\"half-width\" />
															</li>
															<li>
																<label for=\"idNoteFinale\">Note finale rapportée sur</label>
																&nbsp;
																<input maxlength=3 type=\"number\" id=\"idNoteFinale\" class=\"numeric center width-50\" name=\"formulaire_note_finale\" value=\"" . $nNoteFinale . "\" $sReadonly/>
																&nbsp;
																<label for=\"idNoteFinale\">points</label>
															</li>
														</ol>
													</fieldset>
													<hr class=\"margin-V-25 blue\"/>
													<fieldset class=\"" . $sClassField . "\" id=\"presentation\"><legend>Objectif de l'épreuve</legend>
														<div class=\"margin-H-25\">
															<label for=\"idPresentation\">Le texte ci-dessous est destiné à présenter le cadre de l'évaluation des candidats.</label>
															<textarea rows=5 id=\"idPresentation\" class=\"max-width\" name=\"formulaire_presentation\" $sReadonly>" . $sPresentation . "</textarea>
															$sPencilIcon
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
		$oQuestion = new QuestionHelper($this->_aQCM, $this->_bReadonly, $bStrictFormulaire, $this->_bDisable);
		$oQuestion->setIdFormulaire($this->_nIdFormulaire);
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
													<span id=\"tabs-questionnaire-bottom\"><a class=\"page-bottom\" href=\"#tabs-questionnaire-top\" title=\"Haut de page...\">" . self::ICON_UP . "</a></span>";

		//#########################################################################################
		// CONSTRUCTION DE LA BIBLIOTHÈQUE DES QUESTIONS
		//#########################################################################################
		if ($bBibliotheque) {
			// Récupération de la liste des identifiants de question
			$aListeIdQuestion = $oQuestion->getAllQuestionsId();

			// Construction de la bibliothèque
			$this->_html				.= $this->_buildBibliotheque($aListeIdQuestion);
		}

		// Finalisation du formulaire
		$this->_html					.= "	</div>
											</section>
										</section>";

		// Ajout de la feuille de style
		ViewRender::linkFormulaireStyle("helpers/FormulaireHelper.css");

		// Ajout du JavaScript
		ViewRender::linkFormulaireScript("helpers/FormulaireHelper.js");
	}

	/**
	 * @brief	Rendu du bouton de correction.
	 *
	 * @return	string
	 */
	public function getCorrectionButton() {
		return $this->_correction;
	}

	/**
	 * @brief	Rendu final du formulaire.
	 *
	 * @return	string
	 */
	public function render() {
		// Activation de l'onglet sélectionné
		ViewRender::addToJQuery("$(\"section.tabs\").tabs({ active: " . $this->_activeTab . " });");

		// Activation de la question sélectionnée dans la liste
		ViewRender::addToJQuery("$(\"section.accordion\").accordion({ active: " . $this->_activeQuestion . " });");

		// Déplacement du SCROLL vers l'occurrence de la question sélectionnée
		ViewRender::addToJQuery("scrollToQuestionOccurrence(" . $this->_activeQuestion . ");");

		// Activation de la variable de modification du formulaire
		if ($this->_oInstanceStorage->getData('FORMULAIRE_UPDATED')) {
			ViewRender::addToJQuery("MODIFICATION = true;");
		}

		// Renvoi du code HTML
		return $this->_html;
	}
}
