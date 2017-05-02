<?php
/** @brief	Connecteur LDAP.
 *
 * Connecteur à un Annuaire LDAP.
 *
 * @name		LDAPConnector
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
class Connectors_LDAPConnector {

	private	$oLDAP;
	public	$bConnected		= false;

	private $nRowCount		= 0;
	private $nFieldCount	= 0;
	private $xSearch;
	private $xEntry;
	private $aRow;
	private $aFields;
	private $aConfig;


	/**	@brief	Connexion LDAP
	 *
	 * La fonction ouvre une connexion vers un LDAP.
	 * @param	$aConfig Tableau associatif de paramètre de connexion :
	 * 		indice 'host' contient l'URL du serveur LDAP
	 * 		indice 'port' contient le numéro du port
	 * @return	Boolean traduisant le succès de l'opération
	 */
	public function open($aConfig) {
		$this->aConfig = $aConfig;
		if ($this->oLDAP = ldap_connect($this->aConfig['host'], $this->aConfig['port'])) {
			$this->bConnected = true;
			ldap_set_option($this->oLDAP, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->oLDAP, LDAP_OPT_SIZELIMIT, 1000);
		} else {
			$this->bConnected = false;
		}
		return $this->bConnected;
	}

	/** @brief	Récupération du statut de la connexion.
	 *
	 * La fonction renvoi l'état de la connexion en cours.
	 */
	public function getStatus() {
		return $this->bConnected;
	}

	/**	@brief	Fermeture LDAP
	 *
	 * La fonction ferme la connexion au LDAP (seulement si une connexion a déja été établie).
	 */
	public function close() {
		if ($this->bConnected) {
			ldap_close($this->oLDAP);
			$this->bConnected	= false;
			$this->oLDAP	= null;
		}
	}

	public function commit() {}

	public function rollback() {}

	/**	@brief	Exécution de Requete LDAP
	 *
	 * La fonction Lance une requete LDAP et renvoie le boolean traduisant le succes de la requete.
	 * @param	xQuery tableau associatif contenant :
	 * 		indice 'query'		: requête LDAP
	 * 		indice 'dn'			: Noeud du LDAP a partir d'où on effectue la requête.
	 * 		indice 'attributes'	: liste des attributs recherchés
	 * 		indice 'maxentries'	: nombre max de resultats souhaités.
	 * @return	Boolean, traduisant le succes de la requete.
	 */
	public function	execute($xQuery) {
		$this->xSearch = @ldap_search(
			$this->cnxLDAP,
			$this->aConfig['dn'],
			$xQuery['query'],
			$this->aFields = $xQuery['attributes'],
			0,
			$xQuery['maxentries']
		);
		$nErr = ldap_errno($this->oLDAP);
		if ($nErr == 0 || $nErr == 4) {
			$this->nRowCount = ldap_count_entries($this->oLDAP,$this->xSearch);
			$this->nFieldCount = count($this->aFields);
			return true;
		}
		return false;
	}

	/**	@brief	Dernier auto increment
	 *
	 * Le dernier auto increment n'a pas de sens pour LDAP.
	 * La fonction renvoie toujours 0
	 */
	public function lastId() {
		return 0;
	}

	/**	@brief	Positionner le curseur a la première ligne.
	 *
	 * Apres lexécution d'une requete on peut se positionner sur la première ligne de résultat
	 * Apres l'exécution de cette fonction, on peut acceder aux champs de la première ligne.
	 * @return	tableau a indice numérique, chaque élément de ce tableau est un triplé (tableau de 'name', 'type', 'value')
	 */
	public function	firstRow() {
		if ($this->getRowCount()) {
			$this->xEntry = ldap_first_entry($this->oLDAP, $this->xSearch);
			$this->buildRow($this->aFields);
			return $this->xEntry;
		} else {
			return false;
		}
	}

	/**	@brief	Charger la ligne de resultat suivante
	 *
	 * La fonction passe a la ligne de resultat suivante.
	 * Et renvoie cette nouvelle ligne dans un format similaire a ce que renvoie la fonction 'firstRow'
	 * @return	tableau a indice numérique, chaque élément de ce tableau est un triplé (tableau de 'name', 'type', 'value')
	 */
	public function	nextRow() {
		$this->xEntry = ldap_next_entry($this->oLDAP,$this->xEntry);
		$this->buildRow($this->aFields);
		return $this->xEntry;
	}

	/**	@brief	Construit une ligne de résultat
	 */
	private function buildRow($aFields) {
		$aAttributes = ldap_get_attributes($this->oLDAP, $this->xEntry);
		$this->aRow = array();

		foreach ($aFields as $v) {
			if (isset($aAttributes[$v])) {
				$aAttrZone = $aAttributes[$v];
			} else {
				$aAttrZone = array('');
			}
			$xValue = $aAttrZone[0];
			if (FW_UTF8_ENCODE) {
				$this->aRow[] = array(
					'name' => $v,
					'type' => DataHelper::DATA_TYPE_STR,
					'value' => $xValue
				);
			} else {
				$this->aRow[] = array(
					'name' => utf8_decode($v),
					'type' => DataHelper::DATA_TYPE_STR,
					'value' => utf8_decode($xValue)
				);
			}
		}
	}

	/**	@brief	Libère l'espace mémoire occupé par le résultat d'une requete.
	 */
	private function freeResult() {
		$this->nRowCount = 0;
		$this->nFieldCount = 0;
		ldap_free_result($this->xSearch);
	}

	/** @brief	Nombre de ligne du résultat.
	 *
	 * Renvoie le Nombre de ligne du résultat.
	 * @return	Entier
	 */
	public function getRowCount() {
		return $this->nRowCount;
	}

	/** @brief	Nombre de champs.
	 *
	 * Renvoie le Nombre de champs contenus dans une ligne.
	 * @return	Entier
	 */
	public function getFieldCount() {
		return $this->nFieldCount;
	}

	/** @brief	Récupère un champs
	 *
	 * Renvoie l'instance d'un champ, stocké dans la ligne de résultat en cours
	 * @param	nField numéro du champ a récuperer
	 * @return	Valeur du champs.
	 */
	public function getField($nField) {
		return $this->aRow[$nField];
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
		return ldap_error($this->oLDAP);
	}
}
