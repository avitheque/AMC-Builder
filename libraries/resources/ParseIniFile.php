<?php
/**
 * @brief	Extraction d'une configuration à partir d'un fichier `*.ini`
 *
 * Classe permettant de lire le contenu d'un fichier de configuration selon la section passée en paramètre et de retourner le résultat sous forme de tableau.
 *
 * @li	Une section peut étendre d'une autre
 * @code
 * 		; Configuration parent : la section `default` initialise les paramètres
 * 		[default]
 * 		php.memory_limit = 128M
 *
 * 		; Configuration enfant : la section `developpement` écrasera les paramètres de la section `default`
 * 		[developpement:default]
 * 		php.memory_limit = 512M
 * @endcode
 *
 * @li Par défaut le répertoire de configuration est défini dans la variable globale CONFIGS.
 * @li Retourne la configuration sous forme de tableau.
 *
 * @name		ParseIniFile
 * @category	Resource
 * @package		Main
 * @subpackage	Framework
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 136 $
 * @since		$LastChangedDate: 2018-07-14 17:20:16 +0200 (Sat, 14 Jul 2018) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class ParseIniFile {

	/**
	 * @brief	Variable de stockage au cours du traitement.
	 *
	 * @var		array
	 */
	protected static $_config = array();

	/**
	 * Loads in the ini file specified in filename, and returns the settings in
	 * it as an associative multi-dimensional array
	 *
	 * @param	string	$section			: Nom de la section à extraire exclusivement au cours du processus, `default` par défaut.
	 * @param	string	$filename			: Nom du fichier ini à parcourir
	 * @throws	ApplicationException
	 * @return	void
	 */
	public static function parse($section = 'default', $filename = "application.ini") {
		// Réinitialisation the result array
		self::$_config = array();

		// Initialisation du chemin complet du fichier de configuration
		$sConfigFile = CONFIGS . "/" . $filename;

		// Extraction du contenu du fichier sous forme de section du type `[nom_de_la_section]`
		$aIni = parse_ini_file($sConfigFile, true, INI_SCANNER_NORMAL);

		// Parcours de l'ensemble des entrées
		if (DataHelper::isValidArray($aIni)) {
			// Parcours de toutes les sections présentes
			foreach ($aIni as $sSection => $xContents) {
				// Extraction de la section
				self::_configSection($sSection, $xContents);
			}

			//  Fonctionnalité réalisée si une section est renseignée en paramètre
			if (!empty($section)) {
				// Fonctionnalité réalisée si la section existe bien
				if (array_key_exists($section, self::$_config)) {
					// Initialisation du tableau avec la section
					self::$_config = self::$_config[$section];
				} else {
					// Fonctionnalité réalisée si aucune section n'est présente dans le fichier
					throw new ApplicationException(sprintf("La section <b><e>\"%s\"</e></b> n'existe par dans le fichier <e>\"%s\"</e>", $section, $sConfigFile), self::$_config);
				}
			}
		}

		// Renvoi du tableau
		return self::$_config;
	}

	/**
	 * @brief	Extrait la configuration d'une section.
	 *
	 * Extraction de la configuration selon le nom de la section passée en paramètre.
	 * @li	La section notée [final : source] correspond à la section `final` qui étend la section `source`.
	 * @li	Les instructions présentes dans la section `[source]` seron écrasées par celles définis dans `final`.
	 *
	 * @param	string	$section			: Nom de la section à extraire pouvant exploiter la notation [final : source].
	 * @param	array	$aConfig			: Tableau de configuration du type array('param' => "instruction").
	 * @throws	ApplicationException
	 * @return	void
	 */
	private static function _configSection($section, array $aConfig) {
		// Fonctionnalité réalisée si la section ne contient pas d'information d'extension
		if (stripos($section, ':') === false) {
			// Enregistrement de la configuration sans traitement supplémentaire
			self::$_config[$section]	= self::_configSectionContents($aConfig);
		} else {
			// Découpage de la section sous forme `[final : source]`
			list($sFinal, $sSource)		= explode(':', $section);
			$final						= trim($sFinal);	// Nom de la section `final` sans les caractère [espace] superflus
			$source						= trim($sSource);	// Nom de la section `source` sans les caractère [espace] superflus

			// Fonctionnalité réalisée si la section `source` n'existe pas
			if (!isset(self::$_config[$source])) {
				throw new ApplicationException(sprintf("Impossible d'étendre la section <i>`%s`</i> depuis la section <b><i>`%s`</i></b> : cette dernière n'existe pas !", $final, $source), $aConfig);
			}

			// Enregistrement de la configuration `final`
			self::$_config[$final]		= self::_configSectionContents($aConfig);

			// Fusionnement des configurations `source` avec la `final` de façon récursive
			self::$_config[$final]		= self::_configMergeRecursive(self::$_config[$source], self::$_config[$final]);
		}
	}

	/**
	 * @brief	Extraction du contenu de la section.
	 *
	 * Parcours l'ensemble des paramètres du fichier `*.ini` à la recherche de sous-paramètres.
	 * @li	Le paramètre noté `a.b.c.d` sera interprété comme une arborescence de sous-paramètres.
	 * @code
	 * 		[a]
	 * 		 |__[b]
	 * 			 |__[c]
	 * 				 |__[d] = "Instruction"
	 * @endcode
	 *
	 * @param	array	$aConfigs			: Tableau de configuration de la section courante.
	 * @return	array
	 */
	private static function _configSectionContents(array $aConfigs) {
		// Initialisation du tableau final
		$aParams = array();

		// loop through each line and convert it to an array
		foreach ($aConfigs as $sParam => $sInstruction) {
			// Extraction de l'arborescence de sous-paramètres du type `a.b.c.d` en tableau MULTIDIMENSIONNEL
			$aArborescence	= self::_configContentEntry($sParam, $sInstruction);

			// Fusion de la configuration avec l'arborescence trouvée précédemment
			$aParams		= self::_configMergeRecursive($aParams, $aArborescence);
		}

		// Renvoi du résultat final
		return $aParams;
	}

	/**
	 * @brief	Conversion de paramètre noté `a.b.c.d` en tableau MULTIDIMENSIONNEL.
	 *
	 * Les instructions du type `a.b.c.d` sont convertis en une arborescence de sous-paramètres.
	 * @code
	 * 		[a]
	 * 		 |__[b]
	 * 			 |__[c]
	 * 				 |__[d] = "Instruction"
	 * @endcode
	 *
	 * @li	Méthode récursive.
	 *
	 * @param	string	$sParam				: Nom du paramètre sous forme de chaîne de caractère.
	 * @param	mixed	$sInstruction		: Valeur de l'instruction correspondante.
	 * @return	array
	 */
	private static function _configContentEntry($sParam, $sInstruction) {
		// Initialisation du tableau final
		$aParams		= array();

		// Récupération de la première position du caractère [.] dans la chaîne afin d'identifier le premier sous-paramètre
		$nSubPosition	= strpos($sParam, '.');

		// Fonctionnalité réalisée si le paramètre n'est pas décomposés en successions de sous-paramètre
		if ($nSubPosition === false) {
			// Initialisation du paramètre sans arborescence
			$aParams	= array($sParam => $sInstruction);
		} else {
			// Initialisation du nouveau paramètre qui sera la première entrée de l'arbre
			$sFirst		= substr($sParam, 0, $nSubPosition);
			// Initialisation du second paramètre qui servira de seconde référence
			$sSuite		= substr($sParam, $nSubPosition + 1);

			// Construction récursive de l'arborescence à partir des sous-paramètres
			$aParams	= array(
				// Fonctionnalité réalisée tant qu'un sous-paramètre sera trouvé
				$sFirst => self::_configContentEntry($sSuite, $sInstruction),
			);
		}

		// Renvoi du résultat final
		return $aParams;
	}

	/**
	 * @brief	Fusionnement de paramètres entre deux configurations.
	 *
	 * Méthode récursive permettant de fusionner les paramètres du deuxième tableau de configuration au premier.
	 * @li	En cas de conflit sur un même paramètre, c'est la dernière instruction qui sera prise en compte.
	 *
	 * @li	Méthode récursive.
	 *
	 * @param	array	$aLeft				: Tableau initial de configuration dans lequel les données seront fusionnées.
	 * @param	array	$aRight				: Tableau de paramètres à fusionner au premier.
	 * @return	array
	 */
	private static function _configMergeRecursive($aLeft = array(), $aRight = array()) {
		// Fonctionnalité réalisée si l'entrée principale est valide
		if (DataHelper::isValidArray($aLeft)) {
			// Parcours du second tableau afin d'ajouter son contenu au tableau final
			foreach ((array) $aRight as $sParam => $xInstruction) {
				// Fonctionnalité réalisée si la clé est déjà présente dans le tableau final
				if (isset($aLeft[$sParam])) {
					// Remplacement du contenu au tableau final de façon récursive
					$aLeft[$sParam]	= self::_configMergeRecursive($aLeft[$sParam], $xInstruction);
				} elseif ($sParam === 0) {
					// Cas particulier réalisée lors de l'initialisation la collection
					$aLeft			= array(0 => self::_configMergeRecursive($aLeft, $xInstruction));
				} else {
					// Ajout de l'instruction à la collection en supprimant les caractères [espace] superflus
					$aLeft[$sParam]	= is_string($xInstruction) ? trim($xInstruction) : $xInstruction;;
				}
			}
		} else {
			// Écrasement du premier paramètre par le second
			$aLeft = $aRight;
		}

		// Renvoi de la configuration
		return $aLeft;
	}

}
