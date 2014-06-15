require.config({
	paths: {
		leaflet: ['//cdn.leafletjs.com/leaflet-0.7.2/leaflet', elgg.get_site_url() + 'mod/elgg-adherents_assoc_manager/vendors/leaflet/leaflet'],
		test: elgg.get_site_url() + 'mod/elgg-adherents_assoc_manager/vendors/test'
	}
});



elgg.provide('elgg.eaam');

elgg.eaam.init = function() {
	var $ept = $('.elgg-page-topbar'),
		$ta = $('.elgg-layout:not(.hidden) #table-adherents'),
		$ma = $('.elgg-layout:not(.hidden) #map-adherents');

	if ($ta.length) {
		require(['footable', 'jwerty'], function() {
			elgg.eaam.list();
		});
	}

	if ($ma.length) {
		$ept.addClass('shadow');
		require(['leaflet.markercluster', 'dataFrance'], function() {
			elgg.eaam.map();
		});
	} else {
		$ept.removeClass('shadow');
	}

	if ($('.elgg-layout:not(.hidden) 	#statistics-adherents').length) {
		require(['highcharts'], function() {
			elgg.eaam.statistics();
		});
	}

	$('.elgg-menu-item-add-adherent').off().on('click', function() {
		var aap = 'add-adherent-popup';
		elgg.createPopup(aap, elgg.echo('adherents:add-adherent'), function() {
			elgg.get('ajax/view/eaam/ajax/add_adherent', {
				dataType: 'html',
				success: function(json) {
					$('#'+aap+' .elgg-body').html(json);
					$('#'+aap+' .elgg-button-submit').click(function() {
						var form = $(this).closest('form');
						elgg.action('eaam/save', {
							data: form.serialize(),
							success: function(json) {
								//get the footable object
								if ($('#table-adherents').length) {
									var footable = $('#table-adherents').data('footable'),
										date = new Date(),
										data = $.extend(json.output, {
											timestamp: date.getTime(),
											friendlytime: elgg.friendly_time(date.getTime())
										}),
										newRow = Handlebars.compile($('#add-row-table-adherents-template').html())(json.output);

									footable.appendRow(newRow);
									$('#table-adherents').find('.toHighlight').effect('highlight', {}, 3000, function() {
										$(this).removeClass('toHighlight');
									});
								}

								if ($('#map-adherents').length) {
									var ville = dataFrance[json.output.location];

									ville = elgg.isUndefined(ville) ? {lat: 45, long: '-3'} : ville[0];
									markers.push(L.marker(new L.LatLng(ville.lat, ville.long)));
									layer.addLayer(L.marker(new L.LatLng(ville.lat, ville.long)));
									map.addLayer(layer);
								}
							}
						});
						return false;
					})
				}
			})

		});
	})
};
elgg.register_hook_handler('init', 'system', elgg.eaam.init);
elgg.register_hook_handler('history', 'reload_js', elgg.eaam.init);


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



elgg.eaam.map = function() {
	var map = L.map('map-adherents').setView([46.763056, 2.424722], 6);
	L.tileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg', { // http://leaflet-extras.github.io/leaflet-providers/preview/index.html
		attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/">MapQuest</a> &mdash; Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		subdomains: '1234',
		maxZoom: 18
	}).addTo(map);

	markers = [];
	layer = L.markerClusterGroup({
		spiderfyDistanceMultiplier: 2,
		maxClusterRadius: 63
	});

	$.each(map_adherents, function(i, adh) {
		var ville = dataFrance[adh.location];

		ville = elgg.isUndefined(ville) ? {lat: 45, long: '-3'} : ville[0];
		markers[i] = L.marker(new L.LatLng(ville.lat, ville.long));
		layer.addLayer(markers[i]);
	});
	console.log(layer);
	map.addLayer(layer);
};



elgg.eaam.statistics = function() {
	var tmp_series = {},
		nemAdhseries = [],
		countSeries = [];

	$.each(map_adherents, function(i, adh) {
		var arrDate = $.datepicker.formatDate('yy, mm, dd', new Date(adh.time_created*1000)).split(', '),
			date = Date.UTC(arrDate[0], arrDate[1], arrDate[2]);

		if (!tmp_series[date]) tmp_series[date] = {date: date, nbr: 0};
		tmp_series[date].nbr += 1;
	});
	$.each(tmp_series, function(i, e) {
		nemAdhseries.push([e.date, e.nbr]);
	});
	nemAdhseries.sort(function(a, b) {
		return a[0] > b[0];
	});
	for (var i = nemAdhseries.length - 1; i >= 0; i--) {
		var tmpTotal = (i == nemAdhseries.length - 1) ? count_adherents : tmpTotal;

		tmpTotal = tmpTotal-nemAdhseries[i][1];
		countSeries.push([nemAdhseries[i][0], tmpTotal]);
	};

	Highcharts.setOptions({
		lang: {
			months: $.datepicker.regional.fr.monthNames,
			shortMonths: $.datepicker.regional.fr.monthNamesShort,
			weekdays: $.datepicker.regional.fr.dayNames
		}
	});

	$('#statistics-adherents').highcharts({
		title: '',
		chart: {
			spacing: [10,0,15,0]
		},
		credits: {
			enabled: false
		},
		xAxis: {
			type: 'datetime',
			labels: {
				format: '{value:%d %b}',
				y: 25,
				style: {
					color: '#999',
					fontWeight: 'bold',
					fontSize: '12px'
				}
			},
			tickInterval: 24*36e5
		},
		yAxis: {
			title: {
				text: ''
			},
			gridLineColor: '#eee',
			showFirstLabel: false,
			labels: {
				style: {
					color: '#777',
					fontSize: '11px'
				}
			},
			allowDecimals: false,
			min: 0
		},
		tooltip: {
			borderWidth: 3,
			useHTML: true,
			formatter: function() {
				var title = '<h3 class="mbs">' + Highcharts.dateFormat('%a. %e %B %Y', this.x) + '</h3>',
					body = '';

				if (this.series.name == elgg.echo('adherents:chart:new_adherents')) {
					body = elgg.echo('adherents:chart:tooltip:new_adherent' + (this.y > 1 ? 's':''), [this.y]);
				} else {
					body = this.y + ' ' + elgg.echo('adherents').toLowerCase();
				}
				return title + body;
			},
			style: {
				padding: [10,10,12,10]
			}
		},
		legend: {
			layout: 'vertical',
			align: 'left',
			verticalAlign: 'top',
			borderWidth: 0,
			floating: true,
			x: 30,
			y: 6,
			backgroundColor: '#FFF',
			itemStyle: {
				cursor: 'pointer',
				color: '#274b6d',
				fontSize: '13px',
				padding: '5px'
			},
			padding: 4
		},
		plotOptions: {
			series: {
				marker: {
					enabled: false
				}
			},
			column: {
				states: {
					hover: {
						enabled: false
					}
				}
			}
		},
		series: [
			{
				type: 'column',
				name: elgg.echo('adherents:chart:nbr_adherents'),
				color: '#B2E2F5',
				data: countSeries
			},
			{
				type: 'spline',
				name: elgg.echo('adherents:chart:new_adherents'),
				color: '#F1C40F',
				lineWidth: 3,
				marker: {
					lineColor: '#F1C40F'
				},
				data: nemAdhseries
			}
		]
	});
};



elgg.eaam.edit_adherent = function(adherent_GUID) {
	var eap = 'edit-adherent-popup';

	elgg.createPopup(eap, elgg.echo('adherents:edit-adherent'), function() {
		elgg.get('ajax/view/eaam/ajax/edit_adherent', {
			data: {
				adherent: adherent_GUID
			},
			dataType: 'html',
			cache: false,
			success: function(json) {
				$('#'+eap+' .elgg-body').html(json);
			}
		});
	});
};



/**
 * Create a new popup
 * @return void
 */
elgg.createPopup = function(popupID, popupTitle, callback) {
	if (!popupID) return false;
	var popupTitle = popupTitle || '';

	if (!$('#'+popupID).length) {
		$('.elgg-page-body').after(
			Handlebars.compile($('#popup-template').html())({popupID: popupID, popupTitle: popupTitle})
		);
		var popup = $('#'+popupID);

		popup.draggable({
			handle: '.elgg-head',
			stack: '.elgg-popup',
			iframeFix: true,
			opacity: 0.9,
			create: function(e, ui) {
				$('.elgg-popup').css('z-index', '-=1');
				$('#'+popupID).css('z-index', 9500);
			}
		})
		.click(function() {
			$('.elgg-popup').css('z-index', '-=1');
			popup.css('z-index', 9500);
		});
		popup.find('.elgg-icon-push-pin').click(function() {
			popup.toggleClass('pinned');
			return false;
		});
		popup.find('.elgg-icon-delete-alt').click(function() {
			popup.remove();
			$('.tipsy').remove();
			return false;
		});
	} else {
		$('#'+popupID+' > .elgg-head h3').html(popupTitle);
		$('#'+popupID+' > .elgg-body').html($('<div>', {'class': 'elgg-ajax-loader'}));
	}

	if (callback) callback();
};


