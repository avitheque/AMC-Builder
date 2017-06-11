<?php
/**
 * Classe de gestion des utilisateurs.
 *
 * @li Par convention d'écriture, les méthodes
 * 		- find*	renvoient l'ensemble des données sous forme d'un tableau BIDIMENSIONNEL.
 * 		- get*	ne renvoient qu'un seul élément	sous forme d'un tableau UNIDIMENSIONNEL.
 *
 * @name		UserManager
 * @category	Model
 * @package		Main
 * @subpackage	Application
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 2 $
 * @since		$LastChangedDate: 2017-02-27 18:41:31 +0100 (Mon, 27 Feb 2017) $
 * @see			{ROOT_PATH}/libraries/models/AbstractDataManager.php
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
class UserManager extends AbstractDataManager {

	/**
	 * @brief	Enregistrement du LOG sur la table `connexion`.
	 *
	 * @li Commit réalisé à la fin de la requête.
	 *
	 * @param	integer	$nIdUtitlisateur		: Identifiant de l'utilisateur.
	 * @return	boolean
	 */
	public function logConnexion($nIdUtitlisateur) {
		// Construction du tableau associatif des champs à enregistrer
		$aSet	= array(
			"id_session = :id_session,",
			"ip_adresse = :ip_adresse,",
			"id_utilisateur = :id_utilisateur"
		);

		// Construction du tableau associatif des étiquettes et leurs valeurs
		$aBind	= array(
			':id_session'		=> session_id(),
			':ip_adresse'		=> $_SERVER['REMOTE_ADDR'],
			':id_utilisateur'	=> $nIdUtitlisateur
		);

		// Enregistrement du LOG avec FinalCommit
		return $this->logAction('log_connexion', $aSet, $aBind, true);
	}

	/**
	 * @brief	Méthode de recherche de tous les utilisateurs.
	 *
	 * @return	array, tableau contenant l'ensemble des résultats de la requête.
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function findAllUtilisateurs() {
		// Requête SELECT
		$sQuery = "SELECT * FROM utilisateur";
		try {
			return $this->selectSQL($sQuery);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $e);
		}
	}

	/**
	 * @brief	Méthode de récupération d'un utilisateur par son Login.
	 *
	 * @li Le mot de passe est stocké en base en MD5.
	 *
	 * @param	string		$sLogin			: nom du compte à rechercher
	 * @param	string		$sPassword		: mot de passe à comparer en MD5
	 * @return	array, tableau ne contenant qu'un seul résultat.
	 * @code
	 * 		array(
	 * 			'id_utilisateur'		=> "STRING	: identifiant de l'utilisateur",
	 * 			'login_utilisateur'		=> "STRING	: libellé du compte utilisateur",
	 * 			'id_profil'				=> "INTEGER	: identifiant du profil",
	 * 			'libelle_profil'		=> "STRING	: libellé du profil",
	 * 			'role_profil'			=> "STRING	: rôle de l'utilisateur",
	 * 			'id_grade'				=> "INTEGER : identifiant du grade",
	 * 			'libelle_grade'			=> "STRING	: libellé du grade",
	 * 			'libelle_court_grade']	=> "STRING	: libellé court du grade",
	 * 			'nom_utilisateur'		=> "STRING	: nom de l'utilisateur",
	 * 			'prenom_utilisateur'	=> "STRING	: prénom de l'utilisateur",
	 * 			'display_name'			=> "STRING	: texte complet d'identification de l'utilisateur [GRD NOM Prénom]"
	 * 		);
	 * @endcode
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getUtilisateurByLoginPassword($sLogin, $sPassword) {
		// Requête préparée de sélection
		$aQuery[]	= "SELECT * FROM utilisateur";
		// Jointure avec la table des profils
		$aQuery[]	= "JOIN profil USING(id_profil)";
		// Jointure avec la table des grades
		$aQuery[]	= "JOIN grade USING(id_grade)";
		// Clause WHERE
		$aQuery[]	= "WHERE login_utilisateur = :login_utilisateur AND password_utilisateur = :password_utilisateur";

		// Liste des étiquettes et leurs valeurs
		$aEtiquettes	= array(
			':login_utilisateur'	=> $sLogin,
			':password_utilisateur'	=> md5($sPassword)
		);

		try {
			// Exécution de la requête avec récupération de la première occurrence [0]
			return $this->executeSQL($aQuery, $aEtiquettes, 0);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $aQuery);
		}
	}

	/**
	 * @brief	Méthode de récupération d'un utilisateur par son identifiant.
	 *
	 * @param	integer		$nIdUtilisateur	: identifiant de l'utilisateur.
	 * @return	array, tableau ne contenant qu'un seul résultat.
	 * @code
	 * 		array(
	 * 			'id_utilisateur'		=> "STRING	: identifiant de l'utilisateur",
	 * 			'login_utilisateur'		=> "STRING	: libellé du compte utilisateur",
	 * 			'id_profil'				=> "INTEGER	: identifiant du profil",
	 * 			'libelle_profil'		=> "STRING	: libellé du profil",
	 * 			'role_profil'			=> "STRING	: rôle de l'utilisateur",
	 * 			'id_grade'				=> "INTEGER : identifiant du grade",
	 * 			'libelle_grade'			=> "STRING	: libellé du grade",
	 * 			'libelle_court_grade']	=> "STRING	: libellé court du grade",
	 * 			'nom_utilisateur'		=> "STRING	: nom de l'utilisateur",
	 * 			'prenom_utilisateur'	=> "STRING	: prénom de l'utilisateur",
	 * 			'display_name'			=> "STRING	: texte complet d'identification de l'utilisateur [GRD NOM Prénom]"
	 * 		);
	 * @endcode
	 * @throws	ApplicationException si la requête ne fonctionne pas.
	 */
	public function getUtilisateurById($nIdUtilisateur) {
		// Requête préparée de sélection
		$aQuery[]	= "SELECT * FROM utilisateur";
		// Jointure avec la table des profils
		$aQuery[]	= "JOIN profil USING(id_profil)";
		// Jointure avec la table des grades
		$aQuery[]	= "JOIN grade USING(id_grade)";
		// Clause WHERE
		$aQuery[]	= "WHERE id_utilisateur = :id_utilisateur";

		// Liste des étiquettes et leurs valeurs
		$aEtiquettes	= array(
			':id_utilisateur'		=> $nIdUtilisateur
		);

		try {
			// Exécution de la requête avec récupération de la première occurrence [0]
			return $this->executeSQL($aQuery, $aEtiquettes, 0);
		} catch (Exception $e) {
			throw new ApplicationException(Constantes::ERROR_BADQUERY, $aQuery);
		}
	}

}
