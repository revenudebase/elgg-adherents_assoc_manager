<?php
/**
 * elgg-adherents_assoc_manager plugin everyone page
 *
 * @package elgg-adherents_assoc_manager
 */

elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('adherent'));

elgg_register_title_button();

$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'adherent',
	'limit' => 10,
	'full_view' => false,
	'view_toggle_type' => false
));

if (!$content) {
	$content = elgg_echo('adherent:none');
}

$title = elgg_echo('adherent:everyone');

$body = elgg_view_layout('content', array(
	'filter_context' => 'all',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('adherent/sidebar'),
));

echo elgg_view_page($title, $body);