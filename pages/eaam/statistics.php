<?php
/**
 * elgg-adherents_assoc_manager plugin everyone page
 *
 * @package elgg-adherents_assoc_manager
 */

elgg_push_breadcrumb(elgg_echo('adherent:statistics'));

// Get adherents
$adherents = elgg_get_entities(array(
	'type' => 'user',
	'subtype' => 'adherent',
	'limit' => 100
));
$count_adherents = elgg_get_entities(array(
	'type' => 'user',
	'subtype' => 'adherent',
	'limit' => 0,
	'count' => true
));
/*elgg_get_metadata(array(
	'key' => value,
))*/
if ($adherents) {
	$json_adherents = array();
	foreach ($adherents as $adherent) {
		$json_adherents[] = eaam_prepare_adherent($adherent);
	}
	$content = '<script>map_adherents = ' . json_encode($json_adherents) . ';count_adherents = ' . $count_adherents . ';</script>';
} else {
	$content = elgg_echo('adherent:none');
}

$content .= '<div id="statistics-adherents"></div>';

$title = elgg_echo('adherent:statistics');

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('eaam/sidebar')
));

echo elgg_view_page($title, $body);