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
			elgg.eaam.map.init();
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
		var aap = '#add-adherent-popup';
		elgg.popup.create(aap, {
			title: elgg.echo('adherents:add-adherent'),
			callback: function() {
				elgg.get('ajax/view/eaam/ajax/add_adherent', {
					dataType: 'html',
					success: function(json) {
						$(aap+' .elgg-body').html(json);
						$(aap+' .elgg-button-submit').click(function() {
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
											newRow = elgg.handlebars('add-row-table-adherents-template')(json.output);

										footable.appendRow(newRow);
										$('#table-adherents').find('.toHighlight').effect('highlight', {}, 3000, function() {
											$(this).removeClass('toHighlight');
										});
									}

									if ($('#map-adherents').length) {
										elgg.eaam.map.addAdherent(json.output);
										/*var ville = dataFrance[json.output.location];

										ville = elgg.isUndefined(ville) ? {lat: 45, long: '-3'} : ville[0];
										markers.push(L.marker(new L.LatLng(ville.lat, ville.long)));
										layer.addLayer(L.marker(new L.LatLng(ville.lat, ville.long)));
										layer.addLayer(layer);*/


										/*var ville = dataFrance[adh.location];

			ville = elgg.isUndefined(ville) ? {lat: 45, long: '-3'} : ville[0];
			markers[i] = L.marker(new L.LatLng(ville.lat, ville.long));
			markers[i].bindPopup(elgg.handlebars('map-popup-user')(adh));
			layer.addLayer(markers[i]);*/
									}
								}
							});
							return false;
						})
					}
				})
			}
		});
	})
};
elgg.register_hook_handler('init', 'system', elgg.eaam.init);
elgg.register_hook_handler('history', 'reload_js', elgg.eaam.init);



elgg.eaam.edit_adherent = function(adherent_GUID) {
	var eap = '#edit-adherent-popup';

	elgg.popup.create(eap, {
		title: elgg.echo('adherents:edit-adherent'),
		callback: function() {
			elgg.get('ajax/view/eaam/ajax/edit_adherent', {
				data: {
					adherent: adherent_GUID
				},
				dataType: 'html',
				cache: false,
				success: function(json) {
					$(eap+' .elgg-body').html(json);
				}
			});
		}
	});
};



elgg.provide('elgg.popup');

/**
 * Create a new popup
 * @param  {string}     popupID    name of the popup. First letter could be "#". eg: 'a-popup' or '#a-popup'.
 * @param  {object}     params     Params of the popup.
 * @return {[type]}         [description]
 */
elgg.popup.create = function(popupID, params) {
	if (!popupID) return false;
	if (/^[^#]/.test(popupID)) popupID = '#' + popupID;

	var params = $.extend({
			title: '',
			showTitle: true,
			draggable: { // params for draggable handler
				handle: '.elgg-head',
				stack: '.elgg-popup',
				containment: 'window',
				snap: true,
				snapMode: 'outer',
				iframeFix: true,
				opacity: 0.9,
				start: function(e, ui) { // allow popup to exceed containment
					var draggable = ui.helper.data('uiDraggable');
					draggable.helperProportions = {height: 30, width: 30};
					ui.helper.data('uiDraggable', draggable);
					ui.helper.data('uiDraggable')._setContainment();
				}
			},
			pin: true,
			close: true,
			width: false,
			height: false,
			maxHeight: false,
			beforeCallback: $.noop,	// called before popup is created
			createdCallback: $.noop,	// called after popup is created
			updatedCallback: $.noop,	// called after popup is updated (already exist)
			callback: $.noop			// called at the end
		}, params);

	params.beforeCallback();

	if (!$(popupID).length) {
		var style = '';
		if (params.width) style += 'width: ' + params.width + '; ';
		if (params.height) style += 'height: ' + params.height + '; ';
		if (params.maxHeight) style += 'max-height: ' + params.maxHeight + ';';

		$('.elgg-page-body').after(
			elgg.handlebars('popup-template')($.extend({id: popupID.substr(1), style: style}, params))
		);
		var popup = $(popupID),
			moveOnTop = function(thisPopup) { // force popup to be on top when user click on it. draggable.stack do that only on drag.
				var thisPopup = thisPopup || popup,
					maxZ = 0;
				$.each($('.elgg-popup'), function() {
					$(this).css('z-index', '-=1');
					maxZ = Math.max($(this).css('z-index'), maxZ);
				});
				popup.css('z-index', maxZ+1);
			};

		if (params.draggable) popup.draggable(params.draggable);
		moveOnTop();
		popup.click(function() {
			moveOnTop($(this));
		});

		elgg.popup.center(popup);

		popup.find('.elgg-icon-push-pin').click(function() {
			popup.toggleClass('pinned');
			return false;
		});
		popup.find('.elgg-icon-delete-alt').click(function() {
			popup.remove();
			$('.tipsy').remove();
			return false;
		});

		params.createdCallback();

	} else {
		$(popupID+' > .elgg-head h3').html(params.title);
		$(popupID+' > .elgg-body').html($('<div>', {'class': 'elgg-ajax-loader'}));

		params.updatedCallback();

	}

	params.callback();

};



/**
 * Set position of the popup in the center of the window
 * @param  {jQueryObject|string} popup         jQuery Object of he popup, or name of the popup. First letter could be "#". eg: 'a-popup' or '#a-popup'.
 * @param  {[type]}              params        offset to change position.
 */
elgg.popup.center = function(popup, params) {
	if (!popup) return false;
	if (!(popup instanceof jQuery)) {
		if (/^[^#]/.test(popup)) {
			popup = $('#' + popup);
		} else {
			popup = $(popup);
		}
	}

	var params = $.extend({
			offsetTop: 0,
			offsetLeft: 0,
		}, params)
		pW = popup.width(),
		pH = popup.height(),
		wW = $(window).width()
		wH = $(window).height();

		popup.css({
			'top': wH/2 -pH/2 + params.offsetTop,
			'left': wW/2 -pW/2 + params.offsetLeft
		});
};

