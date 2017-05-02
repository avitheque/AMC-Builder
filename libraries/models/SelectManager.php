<?php
/**
 * Classe de gestion des requêtes SELECT en la base de données SQL.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * Étend la classe d'accès à la base de données AbstractDataManager.
 * @see			{ROOT_PATH}/libraries/models/AbstractDataManager.php
 *
 * @name		SelectManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 24 $
 * @since		$LastChangedDate: 2017-04-30 20:38:39 +0200 (dim., 30 avr. 2017) $
 * 
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class SelectManager extends AbstractDataManager {

	const		DEFAULT_OPERATOR	= "=";

	const		WHERE_AND			= "AND";
	const		WHERE_OR			= "OR";

	const		JOIN_INNER			= "INNER";
	const		JOIN_LEFT			= "LEFT";
	const		JOIN_RIGHT			= "RIGHT";

	private		$_table				= null;
	private		$_structure			= array();

	private		$_query				= null;
	private		$_alias				= array();
	private		$_join				= array();
	private		$_and				= array();
	private		$_or				= array();
	private		$_group				= array();
	private		$_order				= array();
	private		$_finally			= array();
	private		$_bind				= array();
	private		$_occurrence		= 0;

	/**
	 * @brief	Constructeur de la classe.
	 *
	 * @li Initialisation de la requête SELECT si les champs sont déclarés à la construction.
	 *
	 * @param	string	$sTable			: nom de la table
	 * @param	mixed	$xField			: ensemble des champs à récupérer
	 */
	public function __construct($sTable, $xField = null) {
		// Initialisation du connecteur s'il n'est pas chargé
		$this->connectPDO();

		// Initialisation du nom de la table
		$this->_table	= $sTable;

		// Récupération de la structure de la table
		$this->_structure = $this->oSQLConnector->describe($this->_table);

		// Fonctionnalité réalisée si la liste des champs est renseignée
		if (!is_null($xField)) {
			// Initialisation du SELECT
			$this->select((array) $xField, $this->_table, true);
		}
	}

	/**
	 * @brief	Initialisation des variables d'instance.
	 *
	 * @li	Seules les variables de la TABLE et de STRUCTURE sont préservées.
	 */
	private function _reset() {
		$this->_query		= null;
		$this->_alias		= array();
		$this->_join		= array();
		$this->_and			= array();
		$this->_or			= array();
		$this->_group		= array();
		$this->_order		= array();
		$this->_finally		= array();
		$this->_bind		= array();
		$this->_occurrence	= 0;
	}

	/**
	 * @brief	Requête SELECT préparée.
	 *
	 * @param	mixed	$xField			: ensemble des champs à récupérer
	 * @code
	 *  // Initialisation de l'instance
	 *  $oMaTable = new TableManager("ma_table");
	 *
	 *  // Construction de la liste des champs
	 * 	$xField = array(
	 * 		// En utilisant l'alias, le résultat exploitera l'alias
	 * 		'nom_du_champ'	=> "Alias 1",
	 * 		// En utilisant le format `prefixe_%s`, de la table sera prise en compte
	 * 		'prefixe_%s'	=> "Alias 2"
	 *  );
	 *
	 *  // Initialisation de la requête
	 *  $oMaTable->select($xField);
	 *
	 *  // Exécution de la requête
	 *  $aData = $oMaTable->fetchAll();
	 *
	 *  // La requête sera de la forme
	 *  // SELECT
	 *  // 		nom_du_champ		AS "Alias 1",
	 *  // 		prefixe_ma_table	AS "Alias 2"
	 *  // FROM
	 *  // 		ma_table
	 * @encode
	 * @param	string	$sTable			: (optionnel) nom de la table
	 * @param	string	$bInit			: (optionnel) si les variables d'instance doivent être réinitialisés.
	 * @return	array
	 */
	public function select($xField = "*", $sTable = null, $bInit = true) {
		// Fonctionnalité réalisée si les variables sont à réinitialiser
		if ($bInit) {
			$this->_reset();
		}

		// Récupération de la structure de la table
		if (empty($sTable) || $sTable == $this->_table) {
			// Exploitation des données de l'instance
			$sTable		= $this->_table;
			$aStructure	= $this->_structure;
		} else {
			// Initialisation du connecteur s'il n'est pas chargé
			$this->connectPDO();
			$aStructure	= $this->oSQLConnector->describe($sTable);
		}

		// Construction de la liste des ALIAS
		$aAlias = array();
		if (DataHelper::isValidArray($xField)) {
			foreach ($xField as $sFormat => $sAlias) {
				// Fonctionnalité réalisée si le label est un raccourcis
				if (preg_match("@%s$@", $sFormat)) {
					$sLabel = sprintf($sFormat, $sTable);
				} else {
					$sLabel = $sFormat;
				}

				// Fonctionnalité réalisée si le nom du champ est valide ou si une fonction CONCAT est détecté
				if (!is_numeric($sFormat) && in_array($sLabel, (array) $aStructure) || preg_match("@^CONCAT\(.*@", strtoupper($sLabel))) {
					// Ajout de l'alias à la collection
					$this->_alias[] = $sLabel . " AS " . $sAlias;
				} elseif (in_array($sAlias, (array) $aStructure)) {
					// Ajout du champ à la collection
					$this->_alias[] = $sAlias;
				}
			}
		} elseif ($xField == "*") {
			// Ajout de l'ensemble des champs
			foreach ($aStructure as $sAlias) {
				$this->_alias[] = $sAlias;
			}
		} else {
			throw new ApplicationException('EFieldNotFound', $xField, $sTable);
		}

		// Renvoi de la structure
		return $aStructure;
	}

	/**
	 * @brief	Ajout d'une étiquette.
	 *
	 * @li	Enregistrement de l'étiquette
	 * @param	string	$sEtiquette		: nom de l'étiquette.
	 * @param	string	$xValue			: valeur de l'étiquette.
	 */
	public function bindValue($sEtiquette, $xValue) {
		if (!is_numeric($sEtiquette)) {
			$sFormat = preg_match("@^\:@", $sEtiquette) ? "%s" : ":%s";
			$this->_bind[sprintf($sFormat, $sEtiquette)] = $xValue;
		}
	}

	/**
	 * @brief	Ajout d'un ensemble d'étiquettes.
	 * @param	string	$xEntry			: ensemble des éléments sous forme de tableau.
	 */
	public function bind($xEntry) {
		foreach ($xEntry as $sEtiquette => $xValue) {
			$this->bindValue($sEtiquette, $xValue);
		}
	}

	/**
	 * @brief	Construction de la clause WHERE.
	 *
	 * @li	Méthode de construire la clause WHERE à partir d'un tableau
	 *
	 * @param	mixed	$xWhere			: Clause WHERE de la requête.
	 * @code
	 * 	$xWhere = array(
	 * 		'nom_du_champ'			=> "valeur du champ",
	 * 		'nom_du_champ = ?'		=> "valeur du champ",
	 * 		'nom_du_champ = %s'		=> "valeur du text",
	 * 		'nom_du_champ >= %d'	=> "valeur du numérique",
	 * 		'nom_du_champ LIKE %s'	=> "valeur de la recherche",
	 * 	);
	 * @endcode
	 * @return	array
	 */
	public function where($xWhere = array(), $sType = self::WHERE_AND) {
		// Forçage du typage en tableau
		$xWhere = (array) $xWhere;

		if (DataHelper::isValidArray($xWhere)) {
			$aWhere = "";
			foreach ($xWhere as $sFormat => $xValue) {
				if (!is_numeric($sFormat)) {
					// Récupération du nom
					preg_match("@^[\:]*(.*)\s+(.*)\s+([\%\:\?]*.*)$@", trim($sFormat), $aMatches);

					// Récupération du nom du champ
					$sChamp = $sFormat;
					if (isset($aMatches[1])) {
						$sChamp		= $aMatches[1];
					}

					// Récupération de l'opérateur
					$sOperateur	= isset($aMatches[2]) ? $aMatches[2] : self::DEFAULT_OPERATOR;

					// Ajout de l'étiquette
					$sEtiquette = sprintf(":%s", $sChamp);
					$this->bindValue($sEtiquette, $xValue);

					// Ajout dans la clause WHERE par étiquette
					$aWhere[] = sprintf("%s %s %s", $sChamp, $sOperateur, $sEtiquette);
				} else {
					// Ajout du champ par étiquette
					$aWhere[] = $xValue;
				}
			}

			switch (strtoupper($sType)) {
				case self::WHERE_AND:
					// Ajout à la clause AND
					$this->_and = array_merge($this->_and, $aWhere);
					break;

				case self::WHERE_OR:
					// Ajout à la clause OR
					$this->_or = array_merge($this->_or, $aWhere);
					break;

				default:
					// Le type n'existe pas
					throw new ApplicationException('EWhereNotFount', $sType);
				break;
			}
		}
	}

	/**
	 * @brief	Ajout d'un AND dans la clause WHERE.
	 *
	 * @li	Méthode d'ajout d'un élément dans le AND de la clause WHERE.
	 *
	 * @param	mixed	$xWhere			: Clause WHERE à ajouter dans le AND de la requête.
	 * @return	void
	 */
	public function andWhere($xWhere) {
		$this->where($xWhere, self::WHERE_AND);
	}

	/**
	 * @brief	Ajout d'un OR dans la clause WHERE.
	 *
	 * @li	Méthode d'ajout d'un élément dans le OR de la clause WHERE.
	 *
	 * @param	mixed	$xWhere			: Clause WHERE à ajouter dans le OR de la requête.
	 * @return	void
	 */
	public function orWhere($xWhere) {
		$this->where($xWhere, self::WHERE_OR);
	}

	/**
	 * @brief	Jointure avec une table exploitant USING()
	 *
	 * @li	Sélection de tous les champs par défaut
	 * @code
	 * 		$xField = "*";
	 * @endcode
	 *
	 * @li	Possibilité de sélectionner plusieurs champs
	 * @code
	 * 		$xField = array(
	 * 			'nom_du_champ_1'	=> "alias 1",
	 * 			'nom_du_champ_2'	=> "alias 2",
	 * 			...
	 * 			'nom_du_champ_X'	=> "alias X"
	 * 		);
	 * @endcode
	 *
	 * @param	string	$sType			: type de jointure, [JOIN INNER] par défaut
	 * @param	string	$sTable			: nom de la table à joindre
	 * @param	string	$sForeignKey	: nom de la clé étrangère
	 * @param	mixed	$xField			: ensemble des champs à récupérer
	 */
	public function joinUsing($sType = self::JOIN_INNER, $sTable, $sForeignKey, $xField = "*") {
		// Implémentation des champs à récupérer
		$aStructure = $this->select($xField, $sTable, false);

		// Fonctionnalité réalisée si la clé est un format
		if (preg_match("@^.*\%s$@", $sForeignKey)) {
			// Formatage du champ à partir du nom de la table
			$sForeignKey = sprintf($sForeignKey, $sTable);
		}

		// Construction de la jointure
		$this->_join[] = sprintf(strtoupper($sType) . " JOIN %s USING(%s)", $sTable, $sForeignKey);
	}

	/**
	 * @brief	Ordonnance
	 *
	 * @param	string	$sType			: type de jointure
	 * @param	string	$sTable			: nom de la table à joindre
	 * @param	string	$sForeignKey	: nom de la clé permettant la jointure
	 * @param	mixed	$xField			: ensemble des champs à récupérer
	 */
	public function order($aOrder) {
		$this->_order += (array) $aOrder;
	}

	/**
	 * @brief	Ajout d'un script SQL en fin de requête.
	 * @param	string	$sSQL			: script SQL.
	 */
	public function append($sSQL = null) {
		if (!empty($sSQL)) {
			$this->_finally += (array) $sSQL;
		}
	}

	/**
	 * @brief	Mise en forme de la requête SQL.
	 * @return	string
	 */
	public function toString() {
		// Construction de la requête
		$sQuery = sprintf("SELECT %s FROM %s", implode(", ", $this->_alias), $this->_table);

		// Ajout des jointures
		if ($this->_join) {
			$sQuery .= " " . implode(" ", $this->_join);
		}

		// Ajout des clauses AND dans le WHERE
		if ($this->_and) {
			// Initialisation de la clause WHERE
			$sQuery .= " WHERE ";
			foreach ($this->_and as $nOccurrence => $sAnd) {
				if ($nOccurrence > 0 && $nOccurrence < count($this->_and)) {
					$sQuery .= " AND ";
				}
				$sQuery .= "(" . $sAnd . ")";
			}
		}

		// Ajout des clauses OR dans le WHERE
		if ($this->_or) {
			// Fonctionnalité réalisée si la clause WHERE existe déjà
			$sQuery .= $this->_and ? " OR " : " WHERE ";
			foreach ($this->_or as $nOccurrence => $sOr) {
				if ($nOccurrence > 0 && $nOccurrence < count($this->_or)) {
					$sQuery .= " OR ";
				}
				$sQuery .= "(" . $sOr . ")";
			}
		}

		// Ajout du groupe
		if ($this->_group) {
			$sQuery .= " GROUP BY " . implode(" ", $this->_group);
		}

		// Ajout du tri
		if ($this->_order) {
			$sQuery .= " ORDER BY " . implode(" ", $this->_order);
		}

		// Finalisation de la requête
		if ($this->_finally) {
			$sQuery .= " " . implode(" ", $this->_finally);
		}

		// Suppression des espaces en trop
		$sQuery = preg_replace("/\s\s+/", " ", strtr($sQuery, array(chr(9) => " ", chr(13) => " ")));

		// Ajout du contenu au VIEW_DEBUG
		ViewRender::addToDebug($sQuery);

		// Renvoi de la requête SQL
		return $sQuery;
	}

	/**
	 * @brief	Récupère une entrée par séquence.
	 * @return	array
	 */
	public function fetch() {
		// Construction de la requête
		$this->_query = $this->toString();
		// Exécution de la requête
		return $this->executeSQL($this->_query, $this->_bind, $this->_occurrence);
	}

	/**
	 * @brief	Récupère la prochaine entrée.
	 *
	 * @li	Passage à l'occurrence suivante et appel la méthode fetch().
	 *
	 * @return	array
	 */
	public function next() {
		$this->_occurrence++;
		return $this->fetch();
	}

	/**
	 * @brief	Récupère toutes les entrées.
	 *
	 * @return	array
	 */
	public function fetchAll() {
		// Construction de la requête
		$this->_query = $this->toString();
		// Exécution de la requête
		$aResult = $this->executeSQL($this->_query, $this->_bind);
		// Suppression des données
		$this->_reset();
		// Renvoi du résultat
		return $aResult;
	}

}
