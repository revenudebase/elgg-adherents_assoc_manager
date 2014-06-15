<?php
/**
 *	elgg-adherents_assoc_manager plugin
 *	@package elgg-adherents_assoc_manager
 *	@author Emmanuel Salomon @ManUtopiK
 *	@license GNU Affero General Public License, version 3 or late
 *	@link https://github.com/revenudebase/elgg-adherents_assoc_manager
 **/

// eaam = Elgg Adherents Assoc Manager
elgg_register_event_handler('init','system','eaam_init');

/**
 * elgg-adherents_assoc_manager init
 */
function eaam_init() {

	$root = dirname(__FILE__);
	$http_base = '/mod/elgg-adherents_assoc_manager';

	elgg_register_library('elgg:eaam', "$root/lib/eaam.php");

	// actions
	$action_path = "$root/actions/eaam";
	elgg_register_action('eaam/save', "$action_path/save.php");
	elgg_register_action('eaam/delete', "$action_path/delete.php");
	elgg_register_action('eaam/share', "$action_path/share.php");

	elgg_extend_view('css/elgg', 'eaam/css');
	elgg_extend_view('js/elgg', 'eaam/js');
	elgg_extend_view('page/elements/foot', 'eaam/handlebars_templates');

	elgg_register_ajax_view('eaam/ajax/add_adherent');
	elgg_register_ajax_view('eaam/ajax/edit_adherent');

	elgg_define_js('footable', array(
		'src' => "$http_base/vendors/footable/footable.all.min",
		'deps' => array('jquery')
	));
	elgg_define_js('jwerty', array(
		'src' => "$http_base/vendors/jwerty/jwerty.min"
	));
	elgg_define_js('leaflet.markercluster', array(
		'src' => "$http_base/vendors/leaflet/leaflet.markercluster",
		'deps' => array('leaflet')
	));
	elgg_define_js('dataFrance', array(
		'src' => "$http_base/lib/cacheOfLocalgroupsJSON_11022014"
	));
	elgg_define_js('highcharts', array(
		'src' => "$http_base/vendors/Highcharts-3.0.10/js/highcharts"
	));

	elgg_register_page_handler('adherents', 'eaam_page_handler');

	// hook to add item in topbar menu
	elgg_register_event_handler('pagesetup', 'system', 'eaam_page_setup');

	// add location to javascript loggedin user object
	elgg_register_plugin_hook_handler('to:object', 'entity' , 'eaam_to_object_entity');

	// Register a URL handler for adherent
	elgg_register_plugin_hook_handler('entity:url', 'object', 'adherent_url');

//elgg_trigger_plugin_hook('get_sql', 'access', $options, $clauses);

	// Limit access for adherents only (and non member of the network)
	elgg_register_plugin_hook_handler('default', 'access', 'eaam_default_access');
	elgg_register_plugin_hook_handler('permissions_check', 'user', 'eaam_default_access', 0);
	// include a hook for plugin authors to include public pages
	//elgg_register_plugin_hook_handler('public_pages', 'walled_garden', null, array()); // /engine/classes/ElggSite.php line 560
}



/**
 * Dispatcher for adherents.
 *
 * URLs take the form of
 *  All adherents:        adherents/all
 *  Statistics:           adherents/statistics
 *  Map:                  adherents/map
 *  View an adherent:     adherents/view/<guid>/<title>
 *  New adherent:         adherents/add/<guid> (container: user, group, parent)
 *
 * Title is ignored
 *
 * @param array $page
 * @return bool
 */
function eaam_page_handler($page) {

	elgg_load_library('elgg:eaam');

	if (!isset($page[0])) {
		$page[0] = 'all';
	}

	elgg_set_context('adherents');

	elgg_push_breadcrumb(elgg_echo('adherents'), 'adherents/all');

	$pages = dirname(__FILE__) . '/pages/eaam';

	switch ($page[0]) {
		case 'all':
		case 'list':
			include "$pages/list.php";
			break;
		case 'map':
			elgg_set_context('map_adherents');
			include "$pages/map.php";
			break;
		case 'statistics':
			elgg_set_context('statistics_adherents');
			include "$pages/statistics.php";
			break;

		case 'view':
			set_input('guid', $page[1]);
			include "$pages/view.php";
			break;
		case 'add':
			gatekeeper();
			include "$pages/add.php";
			break;
		default:
			return false;
	}

	elgg_pop_context();
	return true;
}


/**
 * hook to add item in topbar menu
 */
function eaam_page_setup() {
	if (elgg_is_logged_in()) {
		elgg_register_menu_item('topbar', array(
			'name' => 'adherents',
			'href' => 'adherents/list',
			'text' => defined('MFRB_TEMPLATE') ? '' : elgg_echo('adherents'),
			'section' => 'alt',
			'priority' => 10,
			'link_class' => 'fi-results-demographics',
			'selected' => elgg_get_context() == 'adherents' ? true : false
		));
		elgg_register_menu_item('topbar', array(
			'name' => 'adherents_list',
			'section' => 'alt',
			'parent_name' => 'adherents',
			'href' => 'adherents/list',
			'text' => elgg_echo('adherent:list'),
			'priority' => 100,
			'link_class' => 'fi-list-thumbnails ',
		));
		elgg_register_menu_item('topbar', array(
			'name' => 'adherents_statistics',
			'section' => 'alt',
			'parent_name' => 'adherents',
			'href' => 'adherents/statistics',
			'text' => elgg_echo('adherents:statistics'),
			'priority' => 110,
			'link_class' => 'fi-graph-bar',
		));
		elgg_register_menu_item('topbar', array(
			'name' => 'add-adherent',
			'section' => 'alt',
			'parent_name' => 'adherents',
			'href' => '#',
			'text' => elgg_echo('adherent:add'),
			'priority' => 120,
			'item_class' => 'elgg-menu-hover-admin',
			'link_class' => 'fi-torsos-plus',
		));
	}

	// map
	elgg_register_menu_item('topbar', array(
		'name' => 'map-adherent',
		'section' => 'left',
		'href' => 'adherents/map',
		'text' => ' ',
		'priority' => 100,
		'link_class' => 'mfrb-icon',
		'selected' => elgg_get_context() == 'map_adherents' ? true : false
	));
}

/**
 * Populates the ->getUrl() method for adherent objects
 *
 * @param ElggEntity $entity The adherent object
 * @return string adherent item URL
 */
function adherent_url($hook, $type, $return, $params) {
	$title = $params['entity']->title;
	$title = elgg_get_friendly_title($title);
	return elgg_get_site_url() . "adherents/view/{$params['entity']->getGUID()}/$title";
}



/**
 * Hook to add location info in loggedin ElggUser object passed to javascript
 */
function eaam_to_object_entity($hook, $type, $return, $params) {
	//if ($params['entity'] instanceof ElggUser) {
	if ($params['entity']->getType() == 'user') {
		$return->location = $params['entity']->location;
	}
	return $return;
}



/**
 * Hook to 
 */
function eaam_default_access($hook, $type, $return, $params) {
	//global $fb; $fb->info($params['entity'], 'e');
	/*if (!$params['entity']->getSubtype() == 'adherent') {
		$session = _elgg_services()->session;
		//global $fb; $fb->info($session);
		$session->removeLoggedInUser();
		$session->set('last_forward_from', current_page_url());
		register_error(elgg_echo('loggedinrequired'));
		forward('', 'login');
	}*/
	return $return;
}

