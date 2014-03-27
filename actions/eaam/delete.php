<?php
/**
 * Delete af adherent
 *
 * @package elgg-adherents_assoc_manager
 */

$guid = get_input('guid');
$adherent = get_entity($guid);

if (elgg_instanceof($adherent, 'object', 'adherent') && $adherent->canEdit()) {
	$container = $adherent->getContainerEntity();
	if ($adherent->delete()) {
		system_message(elgg_echo("adherent:delete:success"));
		forward("adherent/all");
	}
}

register_error(elgg_echo("adherent:delete:failed"));
forward(REFERER);
