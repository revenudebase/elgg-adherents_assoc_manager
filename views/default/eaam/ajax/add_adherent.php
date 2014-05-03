<?php
/**
 * Add adherent page
 *
 * @package elgg-adherents_assoc_manager
 */
?>

<form method="post" class="elgg-form elgg-form-eaam-save pam">
	<fieldset>
		<div class="elgg-col-1of2 float">
			<label><?php echo elgg_echo('adherent:lastname'); ?></label><br>
			<?php echo elgg_view('input/text', array('name' => 'lastname')); ?>
		</div>
		<div class="elgg-col-1of2 float">
			<label><?php echo elgg_echo('adherent:firstname'); ?></label><br>
			<?php echo elgg_view('input/text', array('name' => 'firstname')); ?>
		</div>
		<div class="elgg-col-1of3 float">
			<label><?php echo elgg_echo('adherent:postalcode'); ?></label><br>
			<?php echo elgg_view('input/text', array('name' => 'location')); ?>
		</div>
		<div class="elgg-col-2of3 float">
			<label><?php echo elgg_echo('adherent:city'); ?></label><br>
			<?php echo elgg_view('input/text', array('name' => 'city')); ?>
		</div>
		<div class="">
			<label><?php echo elgg_echo('adherent:mail'); ?></label><br>
			<?php echo elgg_view('input/text', array('name' => 'email')); ?>
		</div>
		<div class="">
			<label><?php echo elgg_echo('adherent:note'); ?></label><br>
			<?php echo elgg_view('input/longtext', array('name' => 'description')); ?>
		</div>
		<div class="elgg-foot">
			<?php echo elgg_view('input/securitytoken'); ?>
			<?php echo elgg_view('input/submit', array('value' => elgg_echo('save'))); ?>
		</div>
	</fieldset>
</form>