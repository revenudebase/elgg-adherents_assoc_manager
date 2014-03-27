<?php
/**
 * Edit adherent page
 *
 * @package elgg-adherents_assoc_manager
 */

gatekeeper();

$adherent_guid = get_input('guid');
$adherent = get_entity($adherent_guid);

if (!elgg_instanceof($adherent, 'object', 'adherent') || !$adherent->canEdit()) {
	register_error(elgg_echo('adherents:unknown'));
	forward(REFERRER);
}

$vars = eaam_prepare_form_vars($adherent);

$content = elgg_view_form('eaam/save', array(), $vars);

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);