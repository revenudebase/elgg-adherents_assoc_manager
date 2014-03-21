<?php
/**
 * Add adherent page
 *
 * @package elgg-adherents_assoc_manager
 */

$page_owner = elgg_get_page_owner_entity();

$title = elgg_echo('adherent:add');
elgg_push_breadcrumb($title);

$vars = adherents_assoc_manager_prepare_form_vars();
$content = elgg_view_form('a2m/save', array(), $vars);

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);