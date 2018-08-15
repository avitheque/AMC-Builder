<?php
/**
 * Classe de gestion d'accès à une base MySQL.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * Étend la classe d'accès à la base de données AbstractDataManager.
 * @see			{ROOT_PATH}/libraries/models/AbstractDataManager.php
 *
 * @name		MySQLManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 146 $
 * @since		$LastChangedDate: 2018-08-15 13:10:06 +0200 (Wed, 15 Aug 2018) $
 * @see			{ROOT_PATH}/libraries/models/AbstractDataManager.php
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class MySQLManager extends AbstractDataManager {

	/**
	 * @brief	Instance de l'authentification.
	 * @var AuthenticateManager
	 */
	protected $_oAuth			= null;
	protected $_idUtilisateur	= null;
	protected $_idGroupe		= null;
	protected $_borneGauche		= null;
	protected $_borneDroite		= null;
	protected $_dateFormat		= "%d/%m/%Y";

	/**
	 * @brief	Constructeur de la classe
	 */
	public function __construct($sBaseName = PDO_DBNAME, $bAutoCommit = false) {
		// Constructeur de la classe parent
		$this->beginTransaction($sBaseName, $bAutoCommit);
		
		// Récupération du SINGLETON de l'instance AuthenticateManager
		$this->_oAuth			= AuthenticateManager::getInstance();

		// Récupération de l'identifiant de l'utilisateur
		$this->_idUtilisateur	= $this->_oAuth->getIdUtilisateur();

		// Récupération du groupe de l'utilisateur
		$this->_idGroupe		= $this->_oAuth->getIdGroupe();
		$this->_borneGauche		= $this->_oAuth->getBorneGauche();
		$this->_borneDroite		= $this->_oAuth->getBorneDroite();

		// Chargement du format de la date
		if (defined('DATE_FORMAT')) {
			// Récupération de la structure avec les caractères de séparation
			preg_match("@(.+)([\.\-\/]+)(.+)([\.\-\/]+)(.+)@", DATE_FORMAT, $aMatched);
			// Initialisation du format de la date
			$this->_dateFormat = sprintf("%%%s%s%%%s%s%%%s", $aMatched[1], $aMatched[2], $aMatched[3], $aMatched[4], $aMatched[5]);
		}
	}

	/**
	 * @brief	Debbuggage
	 */
	public function debug() {
		// Fonctionnalité réalisée uniquement en MODE_DEBUG
		if (defined('MODE_DEBUG') && (bool) MODE_DEBUG) {
			// Récupération des arguments passés à la procédure
			$aArgs = func_get_args();

			// La première entrée correspond à la méthode appelée
			$sMethode = $aArgs[0];
			unset($aArgs[0]);

			// Fonctionnalité réalisée pour chaque entrée supplémentaire
			$aPieces = array();
			if (count($aArgs)) {
				foreach ($aArgs as $xContent) {
					// Récupération du type du contenu
					$sType		= gettype($xContent);

					// Fonctionnalité réalisée selon le type
					switch (strtolower($sType)) {
						case "array":
							$aPieces[]	= "Array[" . count($xContent) . "]";
							break;

						case "bool":
						case "boolean":
							$aPieces[]	= $xContent ? "true" : "false";
							break;

						default:
							$aPieces[]	= $xContent;
							break;
					}
				}
			}

			// Ajout du message de DEBUG
			ViewRender::addToDebug(sprintf('%s(%s)', $sMethode, implode(", ", $aPieces)));
		}
	}

	/**********************************************************************************************
	 * @todo	RECHERCHER
	 **********************************************************************************************/

    /** @brief  Récupère la liste des noms de champ d'une table
     *
     * Méthode permettant de récupérer la description d'une table.
     *
     * @param   string      $sTable         : Nom de la table en base de données.
     * @param   string      $iFetchAll      : Type du format PDO du résultat.
     * @return  array|object
     */
    public function describe($sTable, $iFetchAll = PDO::FETCH_COLUMN) {
        // Préparation de la requête
        $this->oStatement                   = $this->oSQLConnector->prepare(sprintf("DESCRIBE `%s`;", $sTable));
        // Exécution de la requête
        if ($this->oStatement->execute()) {
            // Renvoi du résultat selon le format attendu
            return $this->oStatement->fetchAll($iFetchAll);
        }
    }

    /** @brief  Récupère le prochain identifiant d'une table.
     *
     * Méthode permettant de récupérer la description d'une table.
     *
     * @param   string      $sTable         : Nom de la table en base de données.
     * @param   string      $sPrimary       : Clé primaire de la table.
     * @return  integer
     */
    public function nextInsertId($sTable, $sPrimary) {
        // Préparation de la requête
        $this->oStatement                   = $this->oSQLConnector->prepare(sprintf("SELECT MAX(`%s`) AS 'next_id' FROM `%s`;", $sPrimary, $sTable));

        // Exécution de la requête
        if ($this->oStatement->execute()) {
            // Récupération du résultat au format TABLEAU
            $aQuery = $this->oStatement->fetchAll(PDO::FETCH_COLUMN);
            // Renvoi du résultat
            return (int) $aQuery[0] + 1;
        }
    }

	/**
	 * @brief	Méthode générique de récupération des données d'une table par son idendifiant.
	 *
	 * @li	Requête exploitant la procédure stockée.
	 *
	 * @param	string		$sTable			: nom de la table.
	 * @param	integer		$nIdTable		: identifiant de la table.
	 * @param	array		$aFields		: (optional) liste des champs à récupérer, TOUS par défaut.
	 * @param	string		$sPrimaryFormat	: (optional) format de la clé primaire à partir du nom de la table.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 * @return	array, tableau contenant le résultat de la requête.
	 */
	protected function getTableById($sTable, $nIdTable, $aFields = "*", $sPrimaryFormat = "id_%s") {
		// Initialisation de la clé primaire
		$sPrimaryKey	= $sPrimaryFormat;
		if (preg_match("@%s@", $sPrimaryFormat)) {
			$sPrimaryKey = sprintf($sPrimaryFormat, $sTable);
		}

		// Construction de la requête SELECT
		$oQuery = new SelectManager($sTable, $aFields);

		// Ajout de la clause WHERE sur l'identifiant de la table
		$aWhere = array($sPrimaryKey => $nIdTable);
		$oQuery->where($aWhere);

		// Exécution de la requête et envoi du premier résultat
		return $oQuery->fetch();
	}

	/**********************************************************************************************
	 * @todo	ENREGISTRER
	 **********************************************************************************************/

	/**
	 * @brief	Méthode générique d'enregistrement.
	 *
	 * @li	Requête exploitant la procédure stockée.
	 *
	 * @param	array		$aInitQuery		: initialisation de la requête préparée.
	 * @param	array		$aSet			: ensemble des champs à enregistrer.
	 * @param	array		$aBind			: ensemble des étiquettes et leur valeur.
	 * @param	boolean		$bFinalCommit	: (optionnel) active la transaction.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 * @return	integer|boolean, identifiant de l'enregistrement ou statut de l'enregistrement.
	 */
	protected function _save($aInitQuery, $aSet, $aBind, $bFinalCommit = true) {
		// Activation de la transaction
		if (!$bFinalCommit) {
			$this->beginTransaction($this->_DB_NAME, $bFinalCommit);
		}

		// Initialisation du résultat
		$nIdResult = false;

		// Tri du tableau dans l'ordre des clés
		ksort($aSet);

		try {
			// Exécution de la requête
			$nIdResult = $this->executeSQL(array_merge($aInitQuery, $aSet), $aBind);
			// Validation des modifications
			if ($bFinalCommit) {
				$this->oSQLConnector->commit();
			}
			// Affichage d'un message de confirmation
			ViewRender::setMessageSuccess("Enregistrement réalisé avec succès !");
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			// Affichage d'un message d'erreur
			ViewRender::setMessageError("Erreur rencontrée lors de l'enregistrement...");
			// Personnalisation de l'exception
			throw new ApplicationException('EQueryCascade', DataHelper::queryToString(array_merge($aInitQuery, $aSet), $aBind));
		}

		// Renvoi du résultat
		return $nIdResult;
	}

	/**********************************************************************************************
	 * @todo	SUPPRIMER
	 **********************************************************************************************/

	/**
	 * @brief	Méthode générique de suppression.
	 *
	 * @li	Requête exploitant la procédure stockée.
	 *
	 * @param	array		$aQuery			: requête préparée à exécuter.
	 * @param	array		$aBind			: ensemble des étiquettes et leur valeur.
	 * @param	boolean		$bFinalCommit	: (optionnel) active la transaction.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 * @return	boolean, statut de la suppression.
	 */
	protected function _delete($aQuery, $aBind, $bFinalCommit = true) {
		// Activation de la transaction
		if (!$bFinalCommit) {
			$this->beginTransaction($this->_DB_NAME, $bFinalCommit);
		}

		// Initialisation du résultat
		$bExecute = false;

		try {
			// Exécution de la requête et renvoi du résultat
			$bExecute = $this->executeSQL($aQuery, $aBind);
			// Validation des modifications
			if ($bFinalCommit) {
				$this->oSQLConnector->commit();
			}
			// Affichage d'un message de confirmation
			ViewRender::setMessageSuccess("Suppression réalisée avec succès !");
		} catch (ApplicationException $e) {
			// Annulation des modifications
			$this->oSQLConnector->rollBack();
			// Affichage d'un message d'erreur
			ViewRender::setMessageError("Erreur rencontrée lors de la suppression...");
			// Personnalisation de l'exception
			throw new ApplicationException('EQueryCascade', DataHelper::queryToString($aQuery, $aBind));
		}

		// Renvoi du résultat
		return $bExecute;
	}

}
