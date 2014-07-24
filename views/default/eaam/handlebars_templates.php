<!-- Template for popups -->
<script id="popup-template" type="text/x-handlebars-template">
	<div id="{{id}}" class="elgg-popup"{{#if style}} style="{{style}}"{{/if}}>
		<div class="elgg-head">
			<h3 class="float pls">{{title}}</h3>
			<ul class="elgg-menu elgg-menu-popup clearfix float-alt">
				{{#if pin}}
				<li class="elgg-menu-item-pin-popup">
					<a href="#" class="pin">
						<span class="elgg-icon elgg-icon-push-pin tooltip s" title="<?php echo htmlspecialchars(elgg_echo('popups:pin')); ?>"></span>
					</a>
				</li>
				{{/if}}
				{{#if close}}
				<li class="elgg-menu-item-close-popup">
					<a href="#">
						<span class="elgg-icon elgg-icon-delete-alt tooltip s" title="<?php echo elgg_echo('popups:close'); ?>"></span>
					</a>
				</li>
				{{/if}}
			</ul>
		</div>
		<div class="elgg-body">
			<div class="elgg-ajax-loader"></div>
		</div>
	</div>
</script>

<script id="add-row-table-adherents-template" type="text/x-handlebars-template">
	<tr class="row toHighlight">
		<td><b>{{{lastname}}}</b>&nbsp;{{{firstname}}}</td>
		<td>{{{description}}}</td>
		<td data-type="numeric" data-value="{{timestamp}}">{{friendlytime}}</td>
		<td data-type="numeric" data-value="{{timestamp}}">{{friendlytime}}</td>
		<td>{{location}}&nbsp;{{city}}</td>
		<td class="adherent-checkbox">
			<input type="hidden" name="adherents[]" value="0"><input type="checkbox" name="adherents[]" value="{{guid}}" class="elgg-input-checkbox">
		</td>
	</tr>
</script>

<script id="map-popup-user" type="text/x-handlebars-template">
	<div class="elgg-image-block clearfix">
		<div class="elgg-image">
			<div class="elgg-avatar elgg-avatar-small">
				<a href="{{url}}" class="">
					<img src="{{avatar.small}}" alt="{{username}}" title="{{username}}" class="">
				</a>
			</div>
		</div>
		<div class="elgg-body">
			<h3>{{name}}</h3>
			<h4>{{username}}</h4>
		</div>
	</div>
</script>



