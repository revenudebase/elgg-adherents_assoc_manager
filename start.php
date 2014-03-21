<?php
/**
 *	elgg-adherents_assoc_manager plugin
 *	@package elgg-adherents_assoc_manager
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/elgg-adherents_assoc_manager
 **/

elgg_register_event_handler('init','system','adherents_assoc_manager_init');

/**
 * elgg-adherents_assoc_manager init
 */
function adherents_assoc_manager_init() {

	$root = dirname(__FILE__);
	elgg_register_library('elgg:2am', "$root/lib/2am.php");

	// actions
	$action_path = "$root/actions/2am";
	elgg_register_action('2am/save', "$action_path/save.php");
	elgg_register_action('2am/delete', "$action_path/delete.php");
	elgg_register_action('2am/share', "$action_path/share.php");

	elgg_register_page_handler('adherent', 'adherents_assoc_manager_page_handler');

	elgg_extend_view('css/elgg', '2am/css');
	elgg_extend_view('js/elgg', '2am/js');

	// Register a URL handler for adherent
	elgg_register_entity_url_handler('object', 'adherent', 'adherent_url');
}



/**
 * Dispatcher for adherent.
 *
 * URLs take the form of
 *  All adherent:         adherent/all
 *  View adherent:        adherent/view/<guid>/<title>
 *  New adherent:         adherent/add/<guid> (container: user, group, parent)
 *  Edit adherent:        adherent/edit/<guid>
 *
 * Title is ignored
 *
 * @param array $page
 * @return bool
 */
function adherents_assoc_manager_page_handler($page) {

	elgg_load_library('elgg:2am');

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	elgg_push_breadcrumb(elgg_echo('adhrent'), 'adhrent/all');

	$pages = dirname(__FILE__) . '/pages/2am';

	switch ($page[0]) {
		case "all":
			include "$pages/all.php";
			break;
		case 'view':
			set_input('guid', $page[1]);
			include "$pages/view.php";
			break;
		case 'add':
			gatekeeper();
			include "$pages/add.php";
			break;
		case 'edit':
			gatekeeper();
			set_input('guid', $page[1]);
			include "$pages/edit.php";
			break;

		default:
			return false;
	}

	elgg_pop_context();
	return true;
}



/**
 * Populates the ->getUrl() method for adherent objects
 *
 * @param ElggEntity $entity The adherent object
 * @return string adherent item URL
 */
function adherent_url($entity) {
	global $CONFIG;

	$title = $entity->title;
	$title = elgg_get_friendly_title($title);
	return $CONFIG->url . "adherent/view/" . $entity->getGUID() . "/" . $title;
}


