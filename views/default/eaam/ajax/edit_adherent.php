<?php
/**
 * Add adherent page
 *
 * @package elgg-adherents_assoc_manager
 */

$adherent_GUID = get_input('adherent');

$adherent = get_entity($adherent_GUID);

?>

<div class="elgg-col-1of3 float">
	<form method="post" class="elgg-form elgg-form-eaam-save pam">
		<fieldset>
			<div class="elgg-col-1of2 float">
				<label><?php echo elgg_echo('adherent:lastname'); ?></label><br>
				<?php echo elgg_view('input/text', array('name' => 'lastname', 'value' => $adherent->lastname)); ?>
			</div>
			<div class="elgg-col-1of2 float">
				<label><?php echo elgg_echo('adherent:firstname'); ?></label><br>
				<?php echo elgg_view('input/text', array('name' => 'firstname', 'value' => $adherent->firstname)); ?>
			</div>
			<div class="elgg-col-1of3 float">
				<label><?php echo elgg_echo('adherent:postalcode'); ?></label><br>
				<?php echo elgg_view('input/text', array('name' => 'location', 'value' => $adherent->location)); ?>
			</div>
			<div class="elgg-col-2of3 float">
				<label><?php echo elgg_echo('adherent:city'); ?></label><br>
				<?php echo elgg_view('input/text', array('name' => 'city', 'value' => $adherent->city)); ?>
			</div>
			<div class="">
				<label><?php echo elgg_echo('adherent:mail'); ?></label><br>
				<?php echo elgg_view('input/text', array('name' => 'email', 'value' => $adherent->email)); ?>
			</div>
			<div class="">
				<label><?php echo elgg_echo('adherent:note'); ?></label><br>
				<?php echo elgg_view('input/longtext', array(
					'name' => 'description',
					'value' => $adherent->description,
					'rows' => 3,
					'autoresize' => ''
				)); ?>
			</div>
			<div class="elgg-foot">
				<?php echo elgg_view('input/securitytoken'); ?>
				<?php echo elgg_view('input/submit', array('value' => elgg_echo('save'))); ?>
			</div>
		</fieldset>
	</form>
</div>

<div class="elgg-col-1of3 float pam">
	<?php
		$header = elgg_echo('eaam:operations:list');

		$body = elgg_list_annotations(array(
			'guid' => $adherent_GUID,
			'no_results' => elgg_echo('eaam:no_operation'),
		));

		echo elgg_view_module('aside', $header, $body);
	?>
</div>

<div class="elgg-col-1of3 float pam">
	<?php echo elgg_view_comments($adherent); ?>
</div>