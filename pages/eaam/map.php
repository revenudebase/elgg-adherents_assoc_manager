<?php
/**
 * elgg-adherents_assoc_manager plugin everyone page
 *
 * @package elgg-adherents_assoc_manager
 */

// Get adherents
$adherents = elgg_get_entities(array(
	'type' => 'user',
	'subtype' => array('adherent', ELGG_ENTITIES_NO_VALUE),
	'limit' => 100
));
/*elgg_get_metadata(array(
	'key' => value,
))*/
if ($adherents) {
	$json_adherents = new stdClass();
	foreach ($adherents as $adherent) {
		$guid = $adherent->getGUID();
		$json_adherents->$guid = $adherent->toObject();
	}
	$script = '<script>map_adherents = ' . json_encode($json_adherents) . ';</script>';
} else {
	$script = '<script>map_adherents = null;</script>';
}

$content = <<<HTML
<div class="elgg-layout">
	$script
	<div id="map-adherents"></div>
	<ul class="buttons_map">
		<li>
			<ul>
				<li><a href="#" class="mfrb-icon fi-world" onclick="$('#map-adherents').click();map.setView(L.latLng(0, 0), 3);"></a></li>
				<li class="elgg-menu-item-map-adherent"><a href="#" class="mfrb-icon t tooltip n" onclick="$('#map-adherents').click();map.setView(elgg.eaam.map.settings.centerFrance, elgg.eaam.map.settings.defaultZoom);return false;" original-title="Centrer sur la France"></a></li>
				<li><a href="#" class="mfrb-icon fi-marker t" onclick="elgg.eaam.map.showMyMarker();"></a></li>
			</ul>
		</li>
	</ul>
</div>
HTML;

echo elgg_view_page($title, $content);

