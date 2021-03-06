<?php
/**
 * View an adherent
 *
 * @package elgg-adherents_assoc_manager
 */

$adherent = get_entity(get_input('guid'));
if (!$adherent) {
	register_error(elgg_echo('noaccess'));
	$_SESSION['last_forward_from'] = current_page_url();
	forward('');
}

$page_owner = elgg_get_page_owner_entity();

$crumbs_title = $page_owner->name;

if (elgg_instanceof($page_owner, 'group')) {
	elgg_push_breadcrumb($crumbs_title, "adherent/group/$page_owner->guid/all");
} else {
	elgg_push_breadcrumb($crumbs_title, "adherent/owner/$page_owner->username");
}

$title = $adherent->title;

elgg_push_breadcrumb($title);


$content = '<div class="row-fluid adherent-view">';
$content .= '<div class="span6">' . elgg_view_entity($adherent, array('full_view' => true)) . elgg_view_comments($adherent) . '</div>';

$end_clarification = ($adherent->time_created + $adherent->clarification * 60 * 60 * 24) * 1000;
$heading = '<div class="elgg-heading-basic pam"><h3>' . elgg_echo('adherent:clarification:time_left') . '</h3><div class="countdown ptm mts" data-end_clarification="'. $end_clarification .'"></div></div>';
$heading .= '<div class="pam mtm">' . elgg_echo('adherent:clarification:description') . '</div>';
$heading .= '<div class="pam">' . elgg_view('output/url', array(
	'href' => '#',
	'text' => elgg_echo('auie'),
	'class' => 'elgg-button elgg-button-submit adherent-time-less'
	)) . elgg_view('output/url', array(
	'href' => '#',
	'text' => elgg_echo('auie'),
	'class' => 'elgg-button elgg-button-delete adherent-time-more mll'
	)) . '</div>';

$content .= '<div class="span6">' . $heading . '</div>';
$content .= '</div>';

$body = elgg_view_layout('content', array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
));

echo elgg_view_page($title, $body);
