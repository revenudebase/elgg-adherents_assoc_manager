<?php
/**
 * Edit adherent page
 *
 * @package elgg-adherents_assoc_manager
 */

$adherent_guid = get_input('guid');
$adherent = get_entity($adherent_guid);

if (!elgg_instanceof($adherent, 'object', 'adherent') || !$adherent->canEdit()) {
	register_error(elgg_echo('adherent:unknown_bookmark'));
	forward(REFERRER);
}

$page_owner = elgg_get_page_owner_entity();

$title = elgg_echo('adherent:edit');
elgg_push_breadcrumb($title);

$vars = adherent_prepare_form_vars($adherent);
$content = elgg_view_form('2am/save', array(), $vars);

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);