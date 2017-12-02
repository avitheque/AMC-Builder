<?php
/** @brief	Interface du contrôleur abstrait de l'application.
 *
 * @name		iAbstractApplicationController
 * @category	Controllers
 * @package		Interfaces
 * @subpackage	Libraries
 * @author		durandcedric@avitheque.net
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 81 $
 * @since		$LastChangedDate: 2017-12-02 15:25:25 +0100 (Sat, 02 Dec 2017) $
 *
 * Copyright (c) 2015-2017 Cédric DURAND (durandcedric@avitheque.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
interface Interfaces_iAbstractApplicationController {

	/** @brief	Initialisation de la vue.
	 *
	 * Cette procédure est invoquée afin de modifier la vue chargée par l'action du contrôleur.
	 *
	 * @param	string	$sView			: Nom de la vue (sans extension *.phtml)
	 * @return	void
	 */
	public function render($sView = FW_DEFAULTVIEW);

	/** @brief	Récupère le nom de la vue.
	 *
	 * Cette procédure est invoquée afin de récupérer la vue à charger par l'action du contrôleur.
	 *
	 * @return	string	: nom de la vue à réaliser.
	 */
	public function getViewRender();

	/** @brief	Initialisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée avant chaque action.
	 *
	 * @return	void.
	 */
	public function initAction();

	/** @brief	Initialisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée par défaut.
	 *
	 * @return	string	: nom de la vue que l'on souhaite afficher par défaut.
	 */
	public function indexAction();

	/** @brief	Action de réinitialisation du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée pour réinitialiser les données de session.
	 *
	 * @return	void.
	 */
	public function resetAction();

	/** @brief	Finalisation de l'action du contrôleur.
	 *
	 * Cette méthode doit être rédigée dans la classe fille pour orchestrer l'action.
	 *
	 * @li Méthode abstraite du contrôleur exécutée à la fin de chaque action.
	 *
	 * @return	void.
	 */
	public function finalAction();

	/** @brief	Tableau de stockage des données à destination de la vue.
	 *
	 * Renvoie le tableau contenant les objets de stockage a destination de la vue.
	 *
	 * @return	array	: Tableau associatif.
	 */
	public function getData();

	/** @brief	Tableau de stockage des messages.
	 *
	 * Cette fonction renvoie le tableau contenant les messages stockés par le contrôleur via la méthode storeMessage().
	 *
	 * @return	array	: Tableau de chaînes.
	 */
	public function getMessages();

	/** @brief	Tableau de stockage des messages d'erreur.
	 *
	 * Cette fonction renvoie le tableau contenant les messages d'erreur, stockés par le contrôleur via la méthode storeError().
	 *
	 * @return	array	: Tableau de chaînes.
	 */
	public function getErrors();

	/** @brief	Tableau de stockage des messages de succès.
	 *
	 * Cette fonction renvoie le tableau contenant les messages de succès, stockés par le contrôleur via la méthode storeSuccess().
	 *
	 * @return	array	: Tableau de chaînes.
	 */
	public function getSuccesses();

	/** @brief	Tableau de stockage des messages de succès.
	 *
	 * Cette fonction renvoie le tableau contenant les messages d'avertissement, stockés par le contrôleur via la méthode storeWarning().
	 *
	 * @return	array	: Tableau de chaînes.
	 */
	public function getWarnings();

	/** @brief	Nom du contrôleur.
	 *
	 * Cette fonction renvoie le nom du contrôleur actif.
	 *
	 * @return	string	: Nom du contrôleur qui a été invoquée.
	 */
	public function getController();

	/** @brief	Nom de l'action.
	 *
	 * Cette fonction renvoie le nom de l'action du contrôleur.
	 *
	 * @return	string	: Nom de l'action du contrôleur qui a été invoquée.
	 */
	public function getAction();
}
