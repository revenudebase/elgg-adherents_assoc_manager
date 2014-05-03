<?php
/**
* ElggObject adherent save action
*
* @package elgg-adherents_assoc_manager
*/

$guid = get_input('guid', false);
$firstname = get_input('firstname');
$lastname = get_input('lastname');
$email = get_input('email');

if (!$guid) { // action is new adherent
	global $CONFIG;

	if (!$firstname || !$lastname || !$email) {
		register_error(elgg_echo('adherent:save:failed00'));
		forward(REFERER);
	}

	$username = str_replace(' ', '' , $firstname . ucfirst($lastname));
	if (!validate_username($username)) {
		register_error(elgg_echo('adherent:save:failed0'));
		forward(REFERER);
	}

	$adherent = new ElggUser();
	$adherent->username = $username;
	$adherent->email = $email;
	$adherent->name = ucfirst($firstname) . ' ' . ucfirst($lastname);
	$adherent->access_id = ACCESS_PUBLIC;
	$adherent->salt = _elgg_generate_password_salt();
	$adherent->password = generate_user_password($adherent, generate_random_cleartext_password());
	$adherent->owner_guid = 0; // Users aren't owned by anyone, even if they are admin created.
	$adherent->container_guid = 0; // Users aren't contained by anyone, even if they are admin created.
	$adherent->language = $CONFIG->language;

	$adherent->created_by = elgg_get_logged_in_user_guid();
	$adherent->subtype = 'adherent';

	$new = true;

} else { // action is modification of an adherent

	$adherent = get_entity($guid);

	if (!$adherent->canEdit()) {
		system_message(elgg_echo('adherent:save:failed1'));
		forward(REFERRER);
	}
}

$description = get_input('description');
$location = get_input('location');
$city = get_input('city');

$adherent->description = $description;
$adherent->location = $location;
$adherent->city = $city;

$adherent->firstname = $firstname;
$adherent->lastname = $lastname;

if ($adherent->save()) {

	system_message(elgg_echo('adherent:save:success'));

	//add to river only if new
	if ($new) {
		elgg_create_river_item(array(
			'view' => 'river/object/adherent/create',
			'action_type' => 'create',
			'subject_guid' => elgg_get_logged_in_user_guid(),
			'object_guid' => $adherent->getGUID(),
			'target_guid' => null, // adherent managment group set in plugin settings
			//'access_id' => $entity->group_acl,
			//'posted' => $posted,
			//'annotation_id' => $annotation_id,
		));
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
