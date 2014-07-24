elgg.eaam.list = function() {
	var $ta = $('#table-adherents');

	$ta.focus() // set focus do get jwerty working at page load
	.footable({
		detailSeparator: ' :',
		breakpoints: {
			100: 100,
			s1000: 1000,
			big: 2048
		},
		log: function(e) {
			if (e == 'footable_initialized') $ta.animate({opacity: 1});
		},
		debug: true
	})
	.bind({
		footable_sorting: function(e) {
			//return confirm('Do you want to sort by column: ' + e.column.name + ', direction: ' + e.direction);
		},
		keydown: function(e) {
			var $focus = $('.focus', $ta) || null,
				$next = null;

			if (jwerty.is('up', e)) {
				if (!$focus.length || $('.row:first', $ta).hasClass('focus')) {
					$next = $('.row:last', $ta);
				} else {
					$next = $focus.prevAll('.row:first');
				}
			}
			if (jwerty.is('down', e)) {
				if (!$focus.length || $('.row:last', $ta).hasClass('focus')) {
					$next = $('.row:first', $ta);
				} else {
					$next = $focus.nextAll('.row:first');
				}
			}
			if ($next) {
				$focus.removeClass('focus');
				$next.addClass('focus');
				return false; //  and prevent page scroll
			}

			if (jwerty.is('enter', e)) {
				elgg.eaam.edit_adherent($focus.find('input[type="checkbox"]').val());
			}
			if (jwerty.is('space', e)) {
				$focus.trigger('footable_toggle_row');
				return false; // in case of focus is on a checkbox, and prevent page scroll
			}
			if (jwerty.is('s', e)) {
				var $check = $('.elgg-input-checkbox', $focus);
				$check.prop('checked', !$check.prop('checked'));
			}
		}
	})
	.on('mouseover', 'tbody > tr', function() { // Set row focus on mouseover
		$('.focus', $ta).removeClass('focus');
		$(this).addClass('focus');
	});

	// keep focus on table, else on other focusable elements
	$('.elgg-page-body *:focusable').on('blur', function() {
		setTimeout(function() {
			if (!$('*:focus').length) {
				$ta.focus();
			}
		}, 10);
	});

	// All chekboxes
	$('#all-adherents-checkboxes').click(function() {
		var state = $(this).is(':checked');

		$('.row:visible .adherent-checkbox input').prop('checked', state);
	});
};