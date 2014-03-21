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
function adherents_assoc_manager_prepare_form_vars($adherent = null) {
	// input names => defaults
	$values = array(
		'title' => '',
		'description' => '',
		'access_id' => ACCESS_DEFAULT,
		'tags' => '',
		'container_guid' => elgg_get_page_owner_guid(),
		'guid' => null,
		'entity' => $adherent,
		'clarification' => '7',
		'objection' => '7',
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
