<!-- Template for popups -->
<script id="popup-template" type="text/x-handlebars-template">
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