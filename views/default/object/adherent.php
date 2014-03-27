<?php
/**
 * elgg-adherents_assoc_manager save form
 *
 * @package elgg-adherents_assoc_manager
 */

$full = elgg_extract('full_view', $vars, FALSE);
$adherent = elgg_extract('entity', $vars, FALSE);

if (!$adherent) {
	return;
}

$owner = $adherent->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$container = $adherent->getContainerEntity();
$categories = elgg_view('output/categories', $vars);

$description = elgg_view('output/longtext', array('value' => $adherent->description, 'class' => 'pbl'));

$owner_link = elgg_view('output/url', array(
	'href' => "adherents/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));

$date = elgg_view_friendly_time($adherent->time_created);

$comments_count = $adherent->countComments();
//only display if there are commments
if ($comments_count != 0) {
	$text = elgg_echo("comments") . " ($comments_count)";
	$comments_link = elgg_view('output/url', array(
		'href' => $adherent->getURL() . '#comments',
		'text' => $text,
		'is_trusted' => true,
	));
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $adherent,
	'handler' => 'adherent',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "$author_text $date $comments_link $categories";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}




if ($full && !elgg_in_context('gallery')) {

	$params = array(
		'entity' => $adherent,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	$body = <<<HTML
<div class="adherent elgg-content mts">
	$description
</div>
HTML;

	echo elgg_view('object/elements/full', array(
		'entity' => $adherent,
		'icon' => $owner_icon,
		'summary' => $summary,
		'body' => $body,
	));

} else { // brief view

	$excerpt = elgg_get_excerpt($adherent->description);

	$params = array(
		'entity' => $adherent,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);
	$params = $params + $vars;
	$body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($owner_icon, $body);
}
