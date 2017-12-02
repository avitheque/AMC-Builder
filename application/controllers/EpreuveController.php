<?php
/**
 * @brief	Classe contrôleur de passage d'une épreuve QCM.
 *
 * Étend la classe abstraite AbstractFormulaireQCMController.
 * @see			{ROOT_PATH}/libraries/controllers/AbstractFormulaireQCMController.php
 *
 * @name		EpreuveController
 * @category	Controller
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class EpreuveController extends AbstractFormulaireQCMController {

	const 		DECIMAL_PRECISION				= 5;

	/**
	 * @brief	Constante de programmation de l'épreuve.
	 *
	 * @var		string
	 */
	const		STATUT_PROGRAMMATION			= EpreuveManager::STATUT_PROGRAMMATION;
	const		NON_MODIFIABLE					= 0;
	const		MODIFIABLE						= 1;

	/**
	 * @brief	Constantes des actions dans le formulaire.
	 *
	 * @var		string
	 */
	const		ACTION_TEMPORAIRE				= "temporaire";
	const		ACTION_FINALIZE					= "finalize";

	/**
	 * @brief	Constantes du formulaire.
	 *
	 * @var		string
	 */
	const		ID_CONTROLE						= 'ID_CONTROLE';
	const		ID_EPREUVE						= 'ID_EPREUVE';

	/**
	 * @var		integer
	 */
	protected	$_idEpreuve						= null;
	protected	$_idControle					= null;

	/**
	 * @brief	Instance du gestionnaire des contrôles QCM.
	 * @var		EpreuveManager
	 */
	protected	$_oEpreuveManager				= null;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation du tableau des données du formulaire.
	 */
	public function __construct() {
		// Initialisation du contôleur parent
		parent::__construct(__CLASS__, 'QCM_EPREUVE');

		// Instance du modèle de gestion des formulaires
		$this->_oEpreuveManager					= new EpreuveManager();

		// Récupération de l'identifiant de l'épreuve en session
		$this->_idEpreuve						= $this->getDataFromSession(self::ID_EPREUVE);
		$this->_idControle						= $this->getDataFromSession(self::ID_CONTROLE);

		// Récupération de l'identifiant de l'épreuve passé en GET
		$nIdEpreuve								= $this->getParam('id_epreuve');
		if (empty($this->_idEpreuve) && !empty($nIdEpreuve)) {
			// Chargement de l'épreuve sélectionnée
			$this->_idEpreuve					= $nIdEpreuve;
		}

		// Fonctionnalité réalisée selon l'action du bouton
		$sButton = strtolower($this->getParam('button'));
		switch ($sButton) {

			case self::ACTION_TEMPORAIRE:
				// Message de débuggage
				$this->debug("TEMPORAIRE");
				// Exécution de l'action
				$this->temporaireAction();
				break;

			case self::ACTION_FINALIZE:
				// Message de débuggage
				$this->debug("FINALIZE");
				// Exécution de l'action
				$this->finalizeAction();
				break;

			default:
				break;
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée par défaut.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function indexAction() {
		// Chargement du formulaire si l'identifiant est présent en session
		if ($this->_idEpreuve && empty($this->_idFormulaire)) {
			// Récupération de l'état de la programmation
			$nStatutEpreuve	= $this->_oEpreuveManager->getProgrammationSatementByIdEpreuve($this->_idEpreuve);

			// Récupération de l'identifiant du formulaire rattaché à l'épreuve
			$nIdFormulaire	= $this->_oFormulaireManager->getIdFormulaireFromIdEpreuve($this->_idEpreuve);

			// Chargement du formulaire
			$this->chargerAction($nIdFormulaire);

			// Enregistrement de l'identifiant de l'épreuve en session
			$this->sendDataToSession($this->_idEpreuve, self::ID_EPREUVE);

			// Enregistrement de l'identifiant de l'épreuve en session
			$this->sendDataToSession($this->_idFormulaire, self::ID_FORMULAIRE);

			// Enregistrement de l'état de programmation en session
			$this->sendDataToSession($nStatutEpreuve, self::STATUT_PROGRAMMATION);

			// Redirection avec l'action
			$this->redirect($this->_controller . '/controle');
		} elseif (!empty($this->_aForm['formulaire_id'])) {
			// Rendu de la vue de modification de l'épreuve
			$this->render("controle");
		} else {
			// Recherche de la liste des épreuves selon l'identifiant de l'utilisateur connecté
			$aListeEpreuve		= $this->_oEpreuveManager->findAllEpreuvesModifiablesByIdCandidat($this->_oAuth->getIdUtilisateur());

			// Recherche de la liste des épreuves corrigées selon l'identifiant de l'utilisateur connecté
			$aListeCorrection	= $this->_oEpreuveManager->findAllEpreuvesCorrectionByIdCandidat($this->_oAuth->getIdUtilisateur());

			// Envoi de la liste à la vue
			$this->addToData('liste_epreuve',		$aListeEpreuve);
			$this->addToData('liste_correction',	$aListeCorrection);
		}
	}

	/**
	 * @brief	Action du contrôleur réalisée lors d'un contrôle.
	 *
	 * @li	Contrôle si le candidat est autorisé à passer l'épreuve.
	 * @li	Contrôle si la programmation de l'épreuve est valide selon `datetime_epreuve` et `duree_epreuve`.
	 *
	 * Si l'identifiant du formulaire est connu, le document est créé, sinon une liste complète est affichée.
	 */
	public function controleAction() {
		// Fonctionnalité réalisée si l'identifiant du contrôle n'existe pas encore
		if (!empty($this->_idEpreuve) && empty($this->_idControle) || empty($this->_aForm['id_controle'])) {
			// Récupération de l'identifiant du contrôle en cours
			$this->_idControle = $this->_oEpreuveManager->initControleByCandidatEpreuve($this->_oAuth->getIdUtilisateur(), $this->_idEpreuve);

			// Chargement du formulaire avec la récupération des données du candidat en base de données
			$this->_aForm = $this->_oEpreuveManager->chargerControle($this->_aForm, $this->_idControle);
		}

		// Fonctionnalité réalisée si l'identifiant du contrôle n'est pas valide
		if (empty($this->_idControle)) {
			// Redirection à la page d'erreur
			$this->redirect($this->_controller . "/error");
		} else {
			// Initialisation de l'identifiant du contrôle dans le formulaire
			$this->_aForm['controle_id']	= $this->_idControle;

			// Enregistrement de l'identifiant du contrôle en session
			$this->sendDataToSession($this->_idControle, self::ID_CONTROLE);
		}
	}

	/**
	 * @brief	Enregistrement temporaire du formulaire.
	 *
	 * @return	void
	 */
	public function temporaireAction() {
		// Enregistrement temporaire du formulaire
		$this->_aForm = $this->_oEpreuveManager->enregistrerControle($this->_aForm, $this->_idControle);

		// Retour au formulaire
		$this->render('controle');
	}

	/**
	 * @brief	Enregistrement définitif du formulaire.
	 *
	 * @li	Verrouillage empêchant la modification du contrôle.
	 * @li	Parcours de chaque réponse du candidat pour évaluer la note de chaque question.
	 *
	 * Lors de l'évaluation de la réponse du candidat, par défaut, la note attribuée est la valeur du barème pour la question.
	 *
	 * Dans le cas d'une réponse LIBRE, tant que l'identifiant du correcteur n'est pas renseignée la question est considérée comme non corrigée.
	 * => Le candidat n'est pas pénalisé tant que la question n'a pas fait l'objet d'une correction individualisée.
	 * Par contre, si la saisie du candidat est vide, la note attribuée est [0] et ne fera plus l'objet d'une correction personnalisée.
	 *
	 * Dans le cas d'une réponse STRICTE, une seule faute attribura la note [0].
	 *
	 * Dans le cas d'une réponse PÉNALISANTE (faisant l'objet de retrait de point(s)), un retrait sera réalisé sur la note obtenue pour la réponse du candidat.
	 *
	 * @return	void
	 */
	public function finalizeAction() {
		try {
			// Parcours de l'ensemble du formulaire afin d'attribuer une note pour chaque réponse du candidat
			foreach ($this->_aForm['question_id'] as $nQuestion => $nIdQuestion) {
				$bLibre            = $this->_aForm['question_libre'][$nQuestion];
				$bStricte          = $this->_aForm['question_stricte'][$nQuestion];
				$fBaremeQuestion   = $this->_aForm['question_bareme'][$nQuestion];
				$pPenaliteQuestion = $this->_aForm['question_penalite'][$nQuestion];

				// Attribution par défaut du barème de la question comme résultat
				$fResultatReponse = $fBaremeQuestion;

				// Récupération de la réponse du candidat en supprimant les caractères [ESPACE] en trop
				$xReponseCandidat = trim($this->_aForm['controle_candidat_libre_reponse'][$nQuestion]);

				// Fonctionnalité réalisée si la question LIBRE est VIDE
				if ($bLibre && strlen($xReponseCandidat) == 0) {
					// Attribution directe de la note [0]
					$fResultatReponse = 0;
				}
				else {
					// Initialisation de la liste des réponses JUSTES parmi celles proposées
					$aReponseJuste = array();
					// Initialisation de la liste des réponses FAUSSES parmi celles proposées
					$aReponseFausse = array();

					// Parcours du modèle des réponses à la question
					foreach ($this->_aForm['reponse_id'][$nQuestion] as $nReponse => $nIdReponse) {
						// Construction des éléments BONUS / MALUS proposées à la question
						if ($this->_aForm['reponse_valide'][$nQuestion][$nReponse]) {
							// La réponse proposée est juste
							$aReponseJuste[] = $nReponse;
						}
						elseif ($this->_aForm['reponse_sanction'][$nQuestion][$nReponse] || $pPenaliteQuestion) {
							// La réponse proposée est fausse
							$aReponseFausse[] = $nReponse;
						}
					}

					// Récupération des éléments sélectionnés par le candidat du type array($nReponse => 'checked')
					$aListeChoixReponses = array_keys($this->_aForm['controle_candidat_liste_reponses'][$nQuestion]);

					// Initialisation de l'évaluation de la réponse du candidat
					$fBonus = 0;
					$fMalus = 0;

					// Fonctionnalité réalisée si au moins un élément est sélectionné
					if (DataHelper::isValidArray($aListeChoixReponses)) {
						// Récupération de la réponse attendue sous forme de chaîne de caractères
						$sReponseAttendue = implode(DataHelper::ARRAY_SEPARATOR, $aReponseJuste);
						// Récupération de la réponse du candidat sous forme de chaîne de caractères
						$sReponseCandidat = implode(DataHelper::ARRAY_SEPARATOR, $aListeChoixReponses);

						// Cas d'une réponse TOTALEMENT JUSTE et/ou STRICTE
						if ($sReponseAttendue === $sReponseCandidat || empty($sReponseAttendue) && $sReponseCandidat === EpreuveManager::AUCUNE_REPONSE) {
							// Attribution total du barème à la question comme BONUS
							$fBonus = (float) $fBaremeQuestion;
						}
						// Cas d'une réponse NON STRICTE
						elseif (!$bStricte) {
							// Parcours des éléments sélectionnés par le candidat
							foreach ($aListeChoixReponses as $nReponse) {
								// Fonctionnalité réalisée si la réponse sélectionnée est JUSTE
								if (in_array($nReponse, $aReponseJuste) && $this->_aForm['reponse_valide'][$nQuestion][$nReponse]) {
									// La réponse proposée est valide
									$fBonus += (float) $fBaremeQuestion * ($this->_aForm['reponse_valeur'][$nQuestion][$nReponse] / 100);
								}
								// Fonctionnalité réalisée si la réponse sélectionnée est FAUSSE
								elseif (in_array($nReponse, $aReponseFausse)) {
									// La réponse proposée est pénalisante
									if ($this->_aForm['reponse_sanction'][$nQuestion][$nReponse] && $this->_aForm['reponse_penalite'][$nQuestion][$nReponse] > 0) {
										$fMalus += (float) $this->_aForm['reponse_penalite'][$nQuestion][$nReponse];
									} elseif ($pPenaliteQuestion) {
										$fMalus += (float) $fBaremeQuestion * ($pPenaliteQuestion / 100);
									}
								}
							}

							// Fonctionnalité réalisée si le candidat donne trop d'éléments de réponse alors qu'il n'a pas de MALUS
							if (empty($fMalus) && count($aListeChoixReponses) >= count($aReponseJuste)) {
								// Attribution directe de la note [0]
								$fBonus = 0;
							}
						}
					}

					// Attribution de la note résultant du calcul : [BONUS - MALUS]
					$fResultatReponse = (float) $fBonus - $fMalus;
				}

				// Affectation de la valeur du résultat à la question
				$this->_oEpreuveManager->enregistrerResultatReponseControle($nIdQuestion, $this->_idControle, $fResultatReponse);
			}

			// Mise à jour définitive des informations du contrôle en base de données
			$this->_oEpreuveManager->finalizeControleById($this->_idControle);
		} catch(ApplicationException $e) {
			// Affichage d'un message d'erreur
			ViewRender::setMessageError("Erreur rencontrée lors de l'enregistrement...");
		}

		// Effacement du formulaire
		$this->resetAction(null);
	}

	/**
	 * @brief	Affichage d'une erreur.
	 *
	 * @return	void
	 */
	public function errorAction() {
		$this->render("error");
	}
}
