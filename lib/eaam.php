<?php
/**
 * elgg-adherents_assoc_manager functions
 *
 * @package elgg-adherents_assoc_manager
 */

/**
 * Prepare the add/edit form variables
 *
 * @param ElggObject $adherent A adherent object.
 * @return array
 */
function eaam_prepare_form_vars($adherent = null) {
	// input names => defaults
	$values = array(
		'title' => '',
		'description' => '',
		'access_id' => ACCESS_DEFAULT,
		'tags' => '',
		'guid' => null
	);

	if ($adherent) {
		foreach (array_keys($values) as $field) {
			if (isset($adherent->$field)) {
				$values[$field] = $adherent->$field;
			}
		}
	}

	if (elgg_is_sticky_form('adherent')) {
		$sticky_values = elgg_get_sticky_values('adherent');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('adherent');

	return $values;
}



/**
 * Prepare adherent for json
 *
 * @param ElggObject $adherent A adherent object.
 * @return array
 */
function eaam_prepare_adherent($adherent) {
	$return['guid'] = $adherent->getGUID();
	$return['location'] = $adherent->location;
	$return['time_created'] = $adherent->getTimeCreated();
	$return['time_updated'] = $adherent->getTimeUpdated();
	$return['url'] = $adherent->getURL();
	return $return;
}


/**
 * Return all adherents for map
 */
/*function eaam_map_adherents() {
	$adherents = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'adherent',
		'limit' => 0
	));
	/*$metadatas = elgg_get_metadata(array(
		'type' => 'object',
		'subtype' => 'adherent',
		'metadata_names' => array('location'),
		'limit' => 0
	));

	foreach ($metadatas as $key => $metadata) {
		$datas[$metadata->entity_guid][$metadata->name] = $metadata->value;
	}*

	$return = array();
	foreach ($adherents as $adherent) {
		$return[] = $adherent->toObject();
	}

	return $return;
}*/