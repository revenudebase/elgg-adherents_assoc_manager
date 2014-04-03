<?php
/**
* ElggObject adherent save action
*
* @package elgg-adherents_assoc_manager
*/

$lastname = get_input('lastname');
$firstname = get_input('firstname');
$location = get_input('location');
$city = get_input('city');
$guid = get_input('guid', false);
$description = get_input('description');

elgg_make_sticky_form('adherent');

if (!$lastname || !$firstname) {
	register_error(elgg_echo('adherent:save:failed'));
	forward(REFERER);
}

if (!$guid) {
	$adherent = new ElggObject;
	$adherent->subtype = "adherent";
//	$adherent->container_guid = $container_guid;
	$new = true;
} else {
	$adherent = get_entity($guid);
	if (!$adherent->canEdit()) {
		system_message(elgg_echo('adherent:save:failed1'));
		forward(REFERRER);
	}
}

//$adherent->title = $title;
$adherent->description = $description;
//$adherent->access_id = $access_id;
$adherent->firstname = $firstname;
$adherent->lastname = $lastname;
$adherent->location = $location;
$adherent->city = $city;

if ($adherent->save()) {

	elgg_clear_sticky_form('adherent');

	system_message(elgg_echo('adherent:save:success'));

	//add to river only if new
	if ($new) {
		add_to_river('river/object/adherent/create','create', elgg_get_logged_in_user_guid(), $adherent->getGUID());
	}

	echo json_encode(array(
		'lastname' => $lastname,
		'firstname' => $firstname,
		'location' => $location,
		'city' => $city,
		'description' => $description
	));
} else {
	register_error(elgg_echo('adherent:save:failed2'));
	forward(REFERER);
}
