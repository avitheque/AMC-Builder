<?php
/** @brief	Connecteur PDO.
 *
 * Connecteur à une base de donnée MySQL.
 *
 * @name		SQLConnector
 * @category	Model
 * @package		Connector
 * @subpackage	Library
 * @author		durandcedric@avitheque.net
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class Connectors_SQLConnector {

	protected	$oPDO;
	protected	$aConfig;
	protected	$bAutoCommit	= false;
	protected	$bFinalCommit	= false;

	public		$bConnected		= false;

	public		$oStatement;
	public		$oRow;
	public		$bFirst;
	public		$nLastId;

	protected	$sError;

	/** @brief	ouverture de base de données
	 *
	 * Ouvre la base de données désignée par les paramètres en entrée.
	 * @param	array	$aConfig			: configuration de l'accès à PDO.
	 * string	$aConfig['connection']		: Chaîne de caractères de connection PDO.
	 * string	$aConfig['user']			: Login.
	 * string	$aConfig['pass']			: Mot de passe.
	 * boolean	$aConfig['final-commit']	: TRUE si le commit est effectué à la fin.
	 * boolean	$aConfig['auto-commit']		: TRUE si le commit est effectué après chaque requête.
	 */
	public function open($aConfig) {
		$this->aConfig		= $aConfig;
		$sConnection		= DataHelper::get($this->aConfig, 'connection');
		$sUser				= DataHelper::get($this->aConfig, 'user');
		$sPassword			= DataHelper::get($this->aConfig, 'pass');
		$this->bFinalCommit	= DataHelper::get($this->aConfig, 'final-commit');
		$this->bAutoCommit	= DataHelper::get($this->aConfig, 'auto-commit');

		// Fonctionnalité réalisées si la connexion à PDO est déjà en cours
		if ($this->bConnected) {
			$this->close();
		}

		$aOptions = array();
		// Fonctionnalité réalisée si le format d'échange est en UTF-8
		if (strtoupper($this->aConfig['charset']) == "UTF8" || strtoupper($this->aConfig['charset']) == "UTF-8") {
			// Ajout de l'option à la collection
			$aOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
		}

		try {
			// Initialisation de la connexion PDO
			$this->oPDO = new PDO($sConnection, $sUser, $sPassword, $aOptions);
			
			// Désactivation de la configuration `ONLY_FULL_GROUP_BY` de MySQL
			if (defined('PDO_DISABLE_FULL_GROUP_BY') && (bool) PDO_DISABLE_FULL_GROUP_BY) {
				$this->oPDO->query("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
			}
			
			$this->bConnected = true;
			$this->oPDO->beginTransaction();
		} catch (Exception $e) {
			$this->oPDO = null;
		}
	}

	/** @brief	Récupération du statut de la connexion.
	 *
	 * La fonction renvoi l'état de la connexion en cours.
	 */
	public function getStatus() {
		return $this->bConnected;
	}

	/** @brief	Fermeture base de données.
	 *
	 * La fonction ferme la base de donnée et effectue un dernier commit dans le cas où
	 * le flag auto-commit est a été déclaré lors de l'appel de la fonction open().
	 */
	public function close() {
		if ($this->bFinalCommit) {
			$this->commit();
		} else {
			$this->rollback();
		}
		$this->oPDO = null;
	}

	/** @brief	commit
	 *
	 * Effectue un commit la transaction en cours.
	 */
	public function commit() {
		$this->oPDO->commit();
		$this->oPDO->beginTransaction();
	}

	/** @brief	rollback
	 *
	 * Effectue un rollback la transaction en cours
	 */
	public function rollback() {
		$this->oPDO->rollback();
		$this->oPDO->beginTransaction();
	}

	/** @brief	Préparation de Requete SQL
	 *
	 * La fonction prépare une requete SQL et renvoie l'objet Statement.
	 * @param	xQuery chaîne de caractères : requête SQL contenant des étiquettes.
	 * @return	object.
	 */
	public function prepare($xQuery) {
	  	return $this->oStatement = $this->oPDO->prepare($xQuery);
	}

	/** @brief	Exécution de Requete SQL
	 *
	 * La fonction Lance une requêr33zvte SQL préparée et renvoie le boolean traduisant le succès de la requete.
	 * @param	xQuery chaîne de caractères : requete SQL :
	 * @return	boolean, traduisant le succès de la requête.
	 */
	public function execute($xQuery) {
	  	$this->oStatement = $this->oPDO->prepare($xQuery);
	  	if ($this->oStatement == false) {
	  		$a = $this->oStatement->errorInfo();
	  		$this->sError = $a[2];
	  		return false;
	  	}

		if ($this->oStatement->execute() == false) {
	  		$a = $this->oStatement->errorInfo();
	  		$this->sError = $a[2];
	  		return false;
		}

		$n = $this->oPDO->lastInsertId();
		if ($n) {
			$this->nLastId = $n;
		}

		if ($this->bAutoCommit) {
			$this->commit();
		}

		$this->nextRow();
		return true;
	}

	/** @brief	Dernier enregistrement généré
	 *
	 * La fonction renvoie le dernier enregistrement généré par le dernier ordre INSERT
	 * @return	integer
	 */
	function lastInsertId() {
		return $this->oPDO->lastInsertId();
	}

	/** @brief	Dernier auto increment généré
	 *
	 * La fonction renvoie le dernier auto-incrément généré par le dernier ordre INSERT
	 * @return	integer
	 */
	function lastId() {
		return $this->nLastId;
	}

	/** @brief	Positionner le curseur a la première ligne.
	 *
	 * Apres lexécution d'une requete on peut se positionner sur la première ligne de résultat
	 * Apres l'exécution de cette fonction, on peut acceder aux champs de la première ligne.
	 * @return	array, tableau à indices numériques, chaque élément de ce tableau est un triplé array('name', 'type', 'value').
	 */
	public function firstRow() {
		if (!$this->bFirst) {
			$this->bFirst = true;
			$this->oStatement->closeCursor();
			$this->oStatement->execute();
			return $this->nextRow();
		}
		return true;
	}

	/** @brief	Charger la ligne de resultat suivante
	 *
	 * La fonction passe a la ligne de resultat suivante.
	 * Et renvoie cette nouvelle ligne dans un format similaire a ce que renvoie la fonction 'firstRow'
	 * @return	array, tableau à indices numériques, chaque élément de ce tableau est un triplé array('name', 'type', 'value').
	 */
	public function nextRow() {
		$this->bFirst = false;
		$xRowAvail = $this->oStatement->fetch(PDO::FETCH_ASSOC);
		if ($xRowAvail) {
			$this->buildRow($xRowAvail);
			return true;
		} else {
			return false;
		}
	}

	/** @brief	Nombre de ligne du résultat.
	 *
	 * Renvoie le Nombre de ligne du résultat.
	 * @return	integer
	 */
	private function buildRow($oRow) {
		$this->oRow = array();
		foreach ($oRow as $k => $v) {
			$this->oRow[] = array(
				'name' => $k,
				'type' => 'str',
				'value' => $v
			);
		}
	}

	/** @brief	Nombre de ligne du résultat.
	 *
	 * Renvoie le Nombre de ligne du résultat.
	 * @return	integer
	 */
	public function getRowCount() {
		return $this->oStatement->rowCount();
	}


	/** @brief	Nombre de champs.
	 *
	 * Renvoie le Nombre de champs contenus dans une ligne.
	 * @return	integer
	 */
	public function getFieldCount() {
		return $this->oStatement->columnCount();
	}

	/** @brief	Récupère un champs
	 *
	 * Renvoie l'instance d'un champ, stocké dans la ligne de résultat en cours
	 * @param	nField numéro du champ a récuperer
	 * @return	string, valeur du champs.
	 */
	public function getField($xField) {
		return DataHelper::get($this->oRow, $xField);
	}

	/** @brief	Exécute une requête vers le connecteur
	 *
	 * La fonction exécute une requête et génère un résultat.
	 * Ce résultat est un tableau d'instance Entity.
	 *
	 * @param	$xQuery requête compréhensible par le connecteur SQL
	 * @return	array, tableau de Entity
	 */
	public function select($xQuery) {
		// Fonctionnalité réalisée si la requête est impossible
		if (!$this->execute($xQuery)) {
			return null;
		}

		// Récupération de la première entrée
		$bRow = $this->firstRow();
		$aEntities = array();
		$y = 0;

		// Parcours l'ensemble des entrées
		while($bRow) {
			// Parcours l'ensemble de la collection
			for ($x = 0; $x < $this->getFieldCount(); $x++) {
				$aRes = $this->getField($x);
				$aEntities[$y][$aRes['name']] = $aRes['value'];
			}
			// Passage à l'entrée suivante
			$bRow = @$this->nextRow();
			$y++;
		}

		// Renvoi de la structure des données sous forme de tableau
		return $aEntities;
	}

	/** @brief	Récupère l'erreur
	 *
	 * @return	string
	 */
	public function getError() {
		return $this->sError;
	}

	/** @brief	Récupère la liste des noms de champ d'une table
	 *
	 * Méthode permettant de récupérer la description d'une table.
	 *
	 * @author	durandcedric
	 * @param	string		$sTable		: Nom de la table en base de données.
	 * @return	array
	 */
	public function describe($sTable) {
	  	$this->oStatement = $this->oPDO->prepare(sprintf("DESCRIBE %s;", $sTable));
	  	if ($this->oStatement->execute()) {
			return $this->oStatement->fetchAll(PDO::FETCH_COLUMN);
	  	}
	}
}
