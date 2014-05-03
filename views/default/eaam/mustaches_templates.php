<!-- Template for popups -->
<script id="popup-template" type="text/template">
	<div id="{{popupID}}" class="elgg-popup ui-draggable">
		<div class="elgg-head">
			<h3 class="float pls">{{popupTitle}}</h3>
			<ul class="elgg-menu elgg-menu-popup clearfix float-alt">
				<li class="elgg-menu-item-pin-popup">
					<a href="#" class="pin">
						<span class="elgg-icon elgg-icon-push-pin tooltip s" title="<?php echo htmlspecialchars(elgg_echo('popups:pin')); ?>"></span>
					</a>
				</li>
				<li class="elgg-menu-item-close-popup">
					<a href="#">
						<span class="elgg-icon elgg-icon-delete-alt tooltip s" title="<?php echo elgg_echo('popups:close'); ?>"></span>
					</a>
				</li>
			</ul>
		</div>
		<div class="elgg-body">
			<div class="elgg-ajax-loader"></div>
		</div>
	</div>
</script>

<script id="add-row-table-adherents-template" type="text/template">
	<tr class="row toHighlight">
		<td><b>{{lastname}}</b>&nbsp;{{firstname}}</td>
		<td>{{description}}</td>
		<td data-type="numeric" data-value="{{timestamp}}">{{friendlytime}}</td>
		<td data-type="numeric" data-value="{{timestamp}}">{{friendlytime}}</td>
		<td>{{location}}&nbsp;{{city}}</td>
		<td class="adherent-checkbox"><input type="hidden" name="adherents[]" value="0"><input type="checkbox" name="adherents[]" value="64" class="elgg-input-checkbox">
		</td>
	</tr>
</script>


<!-- Template for linkbox -->
<script id="linkbox-template" type="text/template">
	<div class="elgg-image-block clearfix">
		{{#mainimage}}
		<ul class="elgg-image">
			<div class="link_picture image-wrapper center tooltip sw t25 gwfb" title="<?php echo elgg_echo('deck_river:linkbox:hidepicture'); ?>">
				<img height="80px" src="{{mainimage}}">
			</div>
			{{#images}}
				<li class="image-wrapper center t25"><img height="80px" src="{{src}}"></li>
			{{/images}}
		</ul>
		{{/mainimage}}
		<div class="elgg-body pts">
			<ul class="elgg-menu elgg-menu-entity elgg-menu-hz float-alt">
				<span class="elgg-icon elgg-icon-delete link"></span>
			</ul>
			<div class="">
				<h4 class="link_name pas mrl" {{#editable}}contenteditable="true"{{/editable}}>{{title}}</h4>
				{{#url}}
				<div class="elgg-subtext pls">
					{{url}}
				</div>
				{{/url}}
				<input type="hidden" name="link_url" value="{{url}}">
				<div class="link_description pas" {{#editable}}contenteditable="true"{{/editable}}>{{description}}</div>
			</div>
		</div>
	</div>
</script>