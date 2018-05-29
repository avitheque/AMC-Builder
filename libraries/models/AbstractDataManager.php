<?php
//require_once 'connectors/ConnectorFactory.php';

/**
 * @brief	Modèle abstrait d'accès aux données.
 *
 * Classe générique d'accès aux données stockées dans une base de données ou d'un annuaire.
 *
 * @li	Permet aux modèles qui étendent de cette classe d'accéder aux connecteurs PDO-MySQL / LDAP.
 *
 * @name		AbstractDataManager
 * @category	Model
 * @package		DataBase
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 129 $
 * @since		$LastChangedDate: 2018-05-29 22:12:23 +0200 (Tue, 29 May 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
abstract class AbstractDataManager {
	
	protected static $DB_NAME	= PDO_DBNAME;
	
	protected $oSQLConnector	= null;
	protected $oLDAPConnector	= null;

	protected $bConnected		= false;

	protected $aQueries			= array();

	protected $sClassName		= '';

	//#############################################################################################
	//	@todo PDO CONNECTOR
	//#############################################################################################
	
	/**
	 * @brief	Initialise du nom de la base de données.
	 *
	 * @li	Possibilité de changer de base de données en cours d'instance.
	 *
	 * La méthode initialise le nom de la base
	 */
	public function useDatabase($sBaseName = PDO_DBNAME) {
		// Fonctionnalité réalisée si l'instance de la base de données a changé
		if ((bool) PDO_ACTIVE && is_object($this->oSQLConnector) && $sBaseName != self::$DB_NAME) {
			unset($this->oSQLConnector);
		}
		// Modification du nom de la Base de données
		self::$DB_NAME = $sBaseName;
	}

	/**
	 * @brief	Initialise une transaction PDO
	 *
	 * @li	Possibilité d'initialiser le type de transaction.
	 *
	 * La méthode effectue une connexion à la base de données à l'aide d'un tableau de paramètres
	 * construit à partir des constantes définie dans le fichier @a defines.php
	 */
	protected function beginTransaction($bAutoCommit = false) {
		// Fonctionnalité réalisée si le connecteur SQL n'existe pas encore
		if ((bool) PDO_ACTIVE && is_null($this->oSQLConnector)) {
			// Récupération de l'instance de connexion
			$oFactory = Connectors_ConnectorFactory::getInstance();

			// Initialisation du paramètre de connexion à la base
			$sConnection		= 'mysql:dbname=' . self::$DB_NAME . ';host=' . PDO_HOST . ';port=' . PDO_PORT;

			// Construction du tableau de paramètres
			$aPDOConf = array(
				'user'			=> PDO_USER,
				'pass'			=> PDO_PASSWD,
				'connection'	=> $sConnection,
				'final-commit'	=> PDO_FINAL_COMMIT,
				'auto-commit'	=> $bAutoCommit,
				'charset'		=> PDO_CHARSET
			);

			// Connexion à PDO
			$this->oSQLConnector = $oFactory->getConnector(Connectors_ConnectorFactory::SQL_CONNECTOR, $aPDOConf);
		}
	}

	/**
	 * @brief	Effectue une connexion PDO
	 *
	 * @li	L'instance de connexion est récupérée si elle est encore valide.
	 *
	 * La méthode effectue une connexion à la base de données.
	 */
	protected function connectPDO() {
		// Conctionnalité réalisée si la connexion n'est pas valide
		if (is_null($this->oSQLConnector)) {
			$this->beginTransaction(PDO_AUTO_COMMIT);
		}
	}

	/** @brief	Test l'accès à la base de données.
	 *
	 * La procédure renvoi la valeur du bouléen de connexion.
	 */
	public function isConnectedPDO() {
		// Initialisation du résultat
		$bConnected = false;
		// Initialisation de la connexion
		$this->connectPDO();
		// Vérification du status à la connexion
		if (is_object($this->oSQLConnector) && method_exists($this->oSQLConnector, 'getStatus')) {
			// Récupération du statut de la connexion
			$bConnected = $this->oSQLConnector->getStatus();
		}
		// Renvoi du résultat
		return $bConnected;
	}

	/**
	 * @brief	Requête SQL avec résultat
	 *
	 * La méthode génère un résultat (tableau de Entity) à partir d'une requête SQL.
	 * Il s'agit de requête de type SELECT. Les autre types de requête sont traités directement
	 * via le connecteur $oSQLConnector.
	 *
	 * @li La récupération du contenu se fait par l'intermédiaire
	 *
	 * @param	mixed	$xQuery			: chaîne de caractères représentant la requête SQL, ou tableau où chaque ligne composent la requête.
	 * @param	integer	$nOccurrence	: (optionnel) occurrence du tableau de Entity, sinon tout le tableau par défaut.
	 * @return	array, tableau de Entity
	 */
	public function selectSQL($xQuery, $nOccurrence = null) {
		$this->connectPDO();  // Connexion (si nécessaire) à PDO.
		if ($this->oSQLConnector) {
			$sQuery = $xQuery;
			// Fonctionnalité réalisée si la requête est construite sous forme de tableau
			if (DataHelper::isValidArray($xQuery)) {
				$sQuery = implode(" ", (array) $xQuery);
			}

			// Suppression des espaces en trop
			$sQuery = preg_replace("/\s\s+/", " ", strtr($sQuery, array(chr(9) => " ", chr(13) => " ")));

			// Récupération du résultat sous forme d'un tableau
			if (!is_null($aResultat = $this->oSQLConnector->select($sQuery))) {
				// Fonctionnalité réalisée si l'occurrence est renseignée
				if (! is_null($nOccurrence)) {
					$aResultat = DataHelper::get($aResultat, $nOccurrence);
				}

				// Renvoi du résultat
				return $aResultat;
			} else {
				// Initialisation du message pour DEBUG
				$sDebug = "<ul>SQL Request :<li>";
				// Manipulation de la requête sous forme de chaîne de caractères pour le débuggage
				$sDebug .= DataHelper::queryToString($xQuery, null);
				// Finalisation du message pour DEBUG
				$sDebug .= "</li></ul>";

				// Levée d'une exception sur la requête
				throw new ApplicationException('EBadQuery', array($sDebug, $this->oSQLConnector->getError()));
			}
		} else {
			// Levée d'une exception sur la connexion SQL
			throw new ApplicationException('ENoConnector', 'Connecteur SQL');
		}
	}

	/**
	 * @brief	Requête SQL préparée avec résultat
	 *
	 * La méthode génère un résultat (tableau de Entity) à partir d'une requête SQL.
	 * Il s'agit de requête préparée de type SELECT. Les autre types de requête sont traités directement
	 * via le connecteur $oSQLConnector.
	 *
	 * @li	Possibilité de n'afficher qu'un enregistrement par son occurrence, [0] pour le premier.
	 * @code
	 * 		// Exécution de la requête et récupération du 1er résultat
	 * 		$aData = executeSQL($xQuery, $aBind, 0);
	 *
	 * 		// Exécution de la requête et récupération du 2ème résultat
	 * 		$aData = executeSQL($xQuery, $aBind, 1);
	 * @endcode
	 *
	 * @param	mixed	$xQuery			: chaîne de caractères représentant la requête SQL, ou tableau où chaque ligne composent la requête.
	 * @param	array	$aBind			: (optionnel) tableau associatif des étiquettes avec leurs valeurs.
	 * @param	integer	$nOccurrence	: (optionnel) occurrence du tableau de Entity, sinon tout le tableau par défaut.
	 * @return	array|integer|boolean
	 */
	public function executeSQL($xQuery, array $aBind = array(), $nOccurrence = null) {
		// Connexion à PDO si nécessaire
		$this->connectPDO();

		if ($this->oSQLConnector) {
			$sQuery = $xQuery;
			// Fonctionnalité réalisée si la requête est construite sous forme de tableau
			if (DataHelper::isValidArray($xQuery)) {
				$sQuery = implode(" ", $xQuery);
			}

			if (!is_null($this->oStatement = $this->oSQLConnector->prepare($sQuery))) {
				// Chargement des étiquettes
				$aEtiquettes = array();
				foreach ($aBind as $sEtiquette => $sValue) {
					// Fonctionnalité réalisée si l'étiquette ne commence pas par le caractère [:]
					if (preg_match('/^[^:]+.+$/', $sEtiquette)) {
						$sEtiquette = ":" . $sEtiquette;
					}
					// Ajout de la valeur de l'étiquette
					$this->oStatement->bindParam($sEtiquette, $sValue);
					$aEtiquettes[$sEtiquette] = $sValue;
				}

				// Fonctionnalité réalisée si une erreur survient lors de l'exécution de la requête
				$xExecute = $this->oStatement->execute($aEtiquettes);
				if ($xExecute == false) {
					throw new ApplicationException('EBadQuery', DataHelper::queryToString($sQuery, $aEtiquettes));
				}

				// Récupération du type de la requête
				$iType = DataHelper::getTypeSQL($sQuery);
				switch ($iType) {

					// Cas d'une requête SELECT
					case DataHelper::SQL_TYPE_SELECT:
						// Récupération du résultat sous forme d'un tableau
						$xResultat = $this->oStatement->fetchAll(PDO::FETCH_ASSOC);

						// Fonctionnalité réalisée si l'occurrence est renseignée
						if (! is_null($nOccurrence)) {
							$xResultat = DataHelper::get($xResultat, $nOccurrence);
						}
					break;

					// Cas d'une requête INSERT
					case DataHelper::SQL_TYPE_INSERT:
						// Récupération de l'identifiant d'enregistrement
						$xResultat = $this->oSQLConnector->lastInsertId();
					break;

					// La requête s'est correctement déroulée
					default:
						$xResultat = true;
					break;

				}
				// Renvoi du résultat
				return $xResultat;
			} else {
				// Initialisation du message pour DEBUG
				$sDebug = "<ul>SQL Request :<li>";
				// Manipulation de la requête sous forme de chaîne de caractères pour le débuggage
				$sDebug .= DataHelper::queryToString($xQuery, $aBind);
				// Finalisation du message pour DEBUG
				$sDebug .= "</li></ul>";

				// Levée d'une exception sur la requête
				throw new ApplicationException('EBadQuery', array($sDebug, $this->oSQLConnector->getError()));
			}
		} else {
			// Levée d'une exception sur la connexion SQL
			throw new ApplicationException('ENoConnector', 'Connecteur SQL');
		}
	}

	/**
	 * @brief	Enregistrement d'un LOG.
	 * 
	 * @li	Une erreur sur l'enregistrement d'un LOG ne doit pas interrompre le traitement en amont.
	 *
	 * @param	string	$sLogName		: nom de la table de LOG.
	 * @param	array	$aSet			: tableau des données à enregistrer.
	 * @param	array	$aBind			: (optionnel) tableau associatif des étiquettes avec leurs valeurs.
	 * @param	boolean	$bFinalCommit	: (optionnel) TRUE si le commit doit être réalisé immédiatement.
	 * @return	boolean
	 */
	public function logAction($sLogName, array $aSet, $aBind = array(), $bFinalCommit = false) {
		// Initialisation de la requête
		$aInitQuery	= array("INSERT INTO $sLogName SET");

		// Ajout des données à la requête
		$aQuery		= array_merge($aInitQuery, $aSet);

		// Fonctionnalité réalisée si la requête rencontre une erreur
		if (! $this->executeSQL($aQuery, $aBind)) {
			// Levée d'une exception sur le LOG
			throw new ApplicationException('ELogAction', DataHelper::queryToString($aQuery, $aBind));
		} elseif ($bFinalCommit) {
			// Commit de la modification en base de données
			$this->oSQLConnector->commit();
		}
		
		// Renvoi du résultat
		return true;
	}
	
	public function commit() {
		// Commit de la modification en base de données
		$this->oSQLConnector->commit();
	}
	
	public function rollback() {
		// Commit de la modification en base de données
		$this->oSQLConnector->rollback();
	}

	//#############################################################################################
	//	@todo LDAP CONNECTOR
	//#############################################################################################

	/**
	 * @brief	Effectue une connexion LDAP
	 *
	 * @li	L'instance de connexion est récupérée si elle est encore valide.
	 *
	 * La méthode effectue une connexion au LDAP à l'aide d'un tableau de paramètres
	 * construit à partir des constante définie dans le fichier define.php
	 */
	protected function connectLDAP() {
		// Fonctionnalité réalisée si le connecteur LDAP n'existe pas encore
		if ((bool) LDAP_ACTIVE && is_null($this->oLDAPConnector)) {
			// Récupération de l'instance de connexion
			$oFactory = Connectors_ConnectorFactory::getInstance();

			// Construction du tableau de paramètres
			$aLDAPConf = array(
				'host'			=> LDAP_HOST,
				'dn'			=> LDAP_DN,
				'port'			=> LDAP_PORT
			);

			// Connexion à LDAP
			$this->oLDAPConnector = $oFactory->getConnector(Connectors_ConnectorFactory::LDAP_CONNECTOR, $aLDAPConf);
		}
	}

	/** @brief	Test l'accès à la base de données.
	 *
	 * La procédure renvoi la valeur du bouléen de connexion.
	 */
	public function isConnectedLDAP() {
		// Initialisation du résultat
		$bConnected = false;
		// Initialisation de la connexion
		$this->connectLDAP();
		// Vérification du status à la connexion
		if (is_object($this->oLDAPConnector) && method_exists($this->oLDAPConnector, 'getStatus')) {
			// Récupération du statut de la connexion
			$bConnected = $this->oLDAPConnector->getStatus();
		}
		// Renvoi du résultat
		return $bConnected;
	}

	/**
	 * @brief	Requête LDAP avec résultat
	 *
	 * La méthode génère un résultat (tableau de Entity) à partir d'une requête LDAP.
	 * @param	mixed	$xQuery			: requête LDAP.
	 * @param	array	$aBind			: tableau associatif des attributs.
	 * @return	array, tableau de Entity
	 */
	public function selectLDAP($xQuery, $aBind = array()) {
		$this->connectLDAP();	// Connexion (si nécessaire) au LDAP.
		if ($this->oLDAPConnector) {
			// Exécution de la requête LDAP
			$aEntity = $this->oLDAPConnector->select($xQuery, $aBind);

			// Fonctionnalité réalisé si la requête ne renvoie pas d'erreur
			if (DataHelper::isValidArray($aEntity)) {
				// Requête valide
				return $aEntity;
			} else {
				// Initialisation du message pour DEBUG
				$sDebug = "<ul>LDAP Request :<li>";
				// Manipulation de la requête sous forme de chaîne de caractères pour le débuggage
				$sDebug .= DataHelper::queryToString($xQuery, $aBind);
				// Finalisation du message pour DEBUG
				$sDebug .= "</li></ul>";

				// Levée d'une exception sur la requête
				throw new ApplicationException('EBadQuery', array($sDebug, $this->oLDAPConnector->getError()));
			}
		} else {
			// Levée d'une exception sur la connexion LDAP
			throw new ApplicationException('ENoConnector', 'Connecteur LDAP');
		}
	}

	//#############################################################################################
	//	@todo CALLBACK CONNECTOR
	//#############################################################################################

	/**
	 * @brief	Appel d'une fonction native SQL dans PHP.
	 *
	 * @li	Fonctionnalité réalisée afin de récupérer un script SQL sous forme de fichier `fonction.sql`.
	 * @li	la variable PSQL doit être définie dans la configuration de l'application.
	 *
	 * @param	string	$sFunction		: nom de la fonction SQL.
	 * @param	array	$aArgs			: tableau d'arguments.
	 * @throws	ApplicationException
	 * @see		{ROOT_PATH}/application/configs/defines.php
	 */
	public function __call($sFunction, $aArgs) {
		// Fonctionnaltié réalisée si le script n'existe pas
		if (!defined('PSQL') || !file_exists($sFileName = PSQL .'/' . $sFunction . '.sql')) {
			throw new ApplicationException('EQueryNotFound', $sFunction);
		}

		$nCount		= count($aArgs);
		$a			= array();
		$aSQL		= file($sFileName);
		$bSelect	= false;

		// Parcours du contenu de chaque ligne SQL
		foreach ($aSQL as $sSting) {
			// Transformation de la chaîne de caractères en [MAJUSCULE] et suppression des caractères [espace] superflus
			$sSQL = strtoupper(trim($sSting));
			// Détermine si la séquence fait référence à une requête SELECT
			if (preg_match("@^SELECT.*@", $sSQL)) {
				$bSelect = true;
			}
		}

		// Parcours du tableau d'arguments
		$sSQL = implode(' ', $aSQL);
		for($i = $nCount - 1; $i >= 0; $i--) {
			$sSQL = strtr($sSQL, array('$'.strval($i+1) => $aArgs[$i]));
		}

		// Fonctionnalité réalisée si la requête correspond à une instruction SELECT
		if ($bSelect) {
			// Requête SELECT
			return $this->selectSQL($sSQL);
		} else {
			// Requête INSERT, UPDATE ou DELETE
			return $this->execSQL($sSQL);
		}
	}

	/**
	 * @brief	Récupération du nom de l'instance en cours.
	 * @return	string
	 */
	protected function getClassName() {
		$sExport = var_export($this, true);
		if (($n = strpos($sExport, '::')) !== false) {
			$sClassName = substr($sExport, 0, $n);
		} else {
			$sClassName = $sExport;
		}
		return $sClassName;
	}

	/**
	 * @brief	Récupération du récultat d'une requête depuis le cache mémoire.
	 *
	 * @param	string		$sQueryName		: libellé du cache à récupérer.
	 * @return	mixed
	 */
	protected function loadQuery($sQueryName) {
		// Récupération du contenu du cache
		return DataHelper::get($this->aQueries, $sQueryName);
	}

	/**
	 * @brief	Enregistre le récultat d'une requête en cache mémoire.
	 *
	 * @param	string		$sQueryName		: libellé du cache.
	 * @param	string		$sQuery			: contenu du script SQL à stocker dans le cache.
	 * @return	mixed
	 */
	public function defineQuery($sQueryName, $sQuery) {
		// Enregistrement du contenu en cache
		$this->aQueries[$sQueryName] = $sQuery;
	}

}
