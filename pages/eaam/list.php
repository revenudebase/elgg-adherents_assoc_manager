<?php
/**
 * elgg-adherents_assoc_manager plugin everyone page
 *
 * @package elgg-adherents_assoc_manager
 */

elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('adherent'));

if (true) {
	elgg_register_menu_item('title', array(
		'name' => 'add_adherent',
		'href' => "#",
		'text' => elgg_echo('adherent:add'),
		'link_class' => 'elgg-button elgg-button-action fi-torsos-plus pbrm',
	));
	elgg_register_menu_item('title', array(
		'name' => 'bulk_adherent',
		'href' => "#",
		'text' => elgg_echo('adherent:bulk'),
		'link_class' => 'elgg-button elgg-button-action',
	));
}

global $CONFIG, $jsonexport;
$dbprefix = $CONFIG->dbprefix;

// Get adherents
$adherents = elgg_get_entities(array(
	'type' => 'user',
	'subtype' => 'adherent',
	'limit' => 100
));

$metadatas = elgg_get_metadata(array(
	'type' => 'user',
	'subtype' => 'adherent',
	'metadata_names' => array('location', 'objection'),
	'limit' => 100
));
//global $fb; $fb->info($adherents);

foreach ($metadatas as $key => $metadata) {
	$datas[$metadata->entity_guid][$metadata->name] = $metadata->value;
}

if ($adherents) {
	$rows = '';
	$even_odd = null;
	foreach ($adherents as $key => $adherent) {
			// This function controls the alternating class
			$even_odd = ( 'odd' != $even_odd ) ? 'odd' : 'even';

			$frienly_time_created = elgg_get_friendly_time($adherent->time_created);
			$frienly_time_updated = elgg_get_friendly_time($adherent->time_updated);

			$checkbox = elgg_view('input/checkbox', array(
				'name' => 'adherents[]',
				'value' => $adherent->getGUID()
			));

			$lastname = ucfirst($adherent->lastname);

			$rows .= <<< END
				<tr class="row {$even_odd}">
					<td><b>{$lastname}</b>&nbsp;{$adherent->firstname}</td>
					<td>{$adherent->description}</td>
					<td data-type="numeric" data-value="{$adherent->time_created}">{$frienly_time_created}</td>
					<td data-type="numeric" data-value="{$adherent->time_updated}">{$frienly_time_updated}</td>
					<td>{$datas[$adherent->guid]['location']} {$adherent->city}</td>
					<td class="adherent-checkbox">{$checkbox}</td>
				</tr>
END;
	}

	$echo_name = elgg_echo('adherent:name');
	$echo_note = elgg_echo('adherent:note');
	$echo_creation = elgg_echo('adherent:creation'); // 'entity:default:strapline'
	$echo_modification = elgg_echo('adherent:modification');
	$echo_city = elgg_echo('adherent:city');

	$content = <<<TABLE
<table id="table-adherents" class="elgg-table-alt toggle-arrow" data-filter="#filter" tabindex="1" data-page-size="100" style="opacity: 0;">
<thead>
	<tr>
		<th data-toggle="true">$echo_name</th>
		<th data-hide="all">$echo_note</th>
		<th data-hide="s1000" data-sort-initial="descending">$echo_creation</th>
		<th>$echo_modification</th>
		<th data-hide="phone">$echo_city</th>
		<th data-sort-ignore="true"><input type="checkbox" id="all-adherents-checkboxes" class="elgg-input-checkbox"></th>
	</tr>
</thead>
$rows
</table>
TABLE;

} else {
	$content = elgg_echo('adherent:none');
}

$title = elgg_echo('adherents:everyone');

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('eaam/sidebar'),
	'class' => 'large-layout'
));

echo elgg_view_page($title, $body);