<?php
/**
 * elgg-adherents_assoc_manager sidebar
 */

$body = <<<BODY
<input id="filter" type="text"/>
Status: <select class="filter-status">
<option></option>
<option value="active">Active</option>
<option value="disabled">Disabled</option>
<option value="suspended">Suspended</option>
</select>
<a href="#clear" class="clear-filter" title="clear filter">[clear]</a>
<a href="#api" class="filter-api" title="Filter using the Filter API">[filter API]</a>
BODY;

echo elgg_view('page/components/module', array(
	'type' => 'aside',
	'title' => elgg_echo('adherents:search'),
	'body' => $body
));


echo elgg_view('page/components/module', array(
	'type' => 'aside',
	'title' => elgg_echo('adherents:list:help'),
	'body' => elgg_echo('adherents:list:help:content')
));

