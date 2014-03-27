<?php
/**
 * elgg-adherents_assoc_manager plugin everyone page
 *
 * @package elgg-adherents_assoc_manager
 */

// Get adherents
$adherents = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'adherent',
	'limit' => 100
));
/*elgg_get_metadata(array(
	'key' => value,
))*/
if ($adherents) {
	$json_adherents = array();
	foreach ($adherents as $adherent) {
		$json_adherents[] = $adherent->toObject();
	}
	$content = '<script>map_adherents = ' . json_encode($json_adherents) . ';</script>';
} else {
	$content = elgg_echo('adherent:none');
}

$content .= '<div id="map-adherents"></div>';

echo elgg_view_page($title, $content);