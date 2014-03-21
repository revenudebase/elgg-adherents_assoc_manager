<?php
/**
* ElggObject adherent save action
*
* @package elgg-adherents_assoc_manager
*/

$title = get_input('title', '', false);
$description = get_input('description');
$access_id = get_input('access_id');
$tags = get_input('tags');
$guid = get_input('guid');
$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());

elgg_make_sticky_form('adherent');

if (!$title || !$description) {
	register_error(elgg_echo('adherent:save:failed'));
	forward(REFERER);
}

if ($guid == 0) {
	$adherent = new ElggObject;
	$adherent->subtype = "adherent";
	$adherent->container_guid = $container_guid;
	$new = true;
} else {
	$adherent = get_entity($guid);
	if (!$adherent->canEdit()) {
		system_message(elgg_echo('adherent:save:failed'));
		forward(REFERRER);
	}
}

$tagarray = string_to_tag_array($tags);

$adherent->title = $title;
$adherent->description = $description;
$adherent->access_id = $access_id;
$adherent->tags = $tagarray;
$adherent->clarification = $clarification;
$adherent->objection = $objection;

if ($adherent->save()) {

	elgg_clear_sticky_form('adherent');

	system_message(elgg_echo('adherent:save:success'));

	//add to river only if new
	if ($new) {
		add_to_river('river/object/adherent/create','create', elgg_get_logged_in_user_guid(), $adherent->getGUID());
	}

	forward($adherent->getURL());
} else {
	register_error(elgg_echo('adherent:save:failed'));
	forward(REFERER);
}
