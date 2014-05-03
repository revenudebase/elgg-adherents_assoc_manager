require.config({
	paths: {
		leaflet: ['//cdn.leafletjs.com/leaflet-0.7.2/leaflet', elgg.get_site_url() + 'mod/elgg-adherents_assoc_manager/vendors/leaflet/leaflet'],
		test: elgg.get_site_url() + 'mod/elgg-adherents_assoc_manager/vendors/test'
	}
});


elgg.provide('elgg.eaam');

elgg.eaam.init = function() {
	var $ept = $('.elgg-page-topbar'),
		$ta = $('#table-adherents'),
		$ma = $('#map-adherents');

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

	if ($('#statistics-adherents').length) {
		require(['highcharts'], function() {
			elgg.eaam.statistics();
		});
	}

	$('.elgg-menu-item-add-adherent').on('click', function() {
		var aap = 'add-adherent-popup';
		elgg.createPopup(aap, elgg.echo('adherents:add-adherent'), function() {
			elgg.get('ajax/view/eaam/ajax/add_adherent', {
				dataType: 'html',
				cache: false,
				success: function(json) {
					$('#'+aap+' .elgg-body').html(json);
					$('#'+aap+' .elgg-button-submit').click(function() {
						var form = $(this).closest('form');
						elgg.action('eaam/save', {
							data: form.serialize(),
							success: function(json) {
								//get the footable object
								if ($ta.length) {
									var footable = $ta.data('footable'),
										date = new Date(),
										data = $.extend(json.output, {
											timestamp: date.getTime(),
											friendlytime: elgg.friendly_time(date.getTime())
										}),
										newRow = Mustache.render($('#add-row-table-adherents-template').html(), json.output);

									footable.appendRow(newRow);
									$ta.find('.toHighlight').effect('highlight', {}, 3000, function() {
										$(this).removeClass('toHighlight');
									});
								}

								if ($ma.length) {
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
			Mustache.render($('#popup-template').html(), {popupID: popupID, popupTitle: popupTitle})
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



/*
 * ! Installing mustache for waiting which MVC elgg core team going to choose.
 * version 0.8.1
 * Compressed with http://refresh-sf.com/yui/ from https://raw.githubusercontent.com/janl/mustache.js/96c43e4c21df692f7d17a9cc4dedd171e583cd9b/mustache.js
 *
 * mustache.js - Logic-less {{mustache}} templates with JavaScript
 * http://github.com/janl/mustache.js
 */
(function(a,b){var c={};b(c);a.Mustache=c}(this,function(a){var g=RegExp.prototype.test;function r(z,y){return g.call(z,y)}var j=/\S/;function h(y){return !r(j,y)}var v=Object.prototype.toString;var k=Array.isArray||function(y){return v.call(y)==="[object Array]"};function b(y){return typeof y==="function"}function e(y){return y.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&")}var d={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;","/":"&#x2F;"};function m(y){return String(y).replace(/[&<>"'\/]/g,function(z){return d[z]})}function q(y){if(!k(y)||y.length!==2){throw new Error("Invalid tags: "+y)}return[new RegExp(e(y[0])+"\\s*"),new RegExp("\\s*"+e(y[1]))]}var f=/\s*/;var l=/\s+/;var u=/\s*=/;var n=/\s*\}/;var s=/#|\^|\/|>|\{|&|=|!/;function x(O,E){E=E||a.tags;O=O||"";if(typeof E==="string"){E=E.split(l)}var I=q(E);var A=new t(O);var G=[];var F=[];var D=[];var P=false;var N=false;function M(){if(P&&!N){while(D.length){delete F[D.pop()]}}else{D=[]}P=false;N=false}var B,z,H,J,C,y;while(!A.eos()){B=A.pos;H=A.scanUntil(I[0]);if(H){for(var K=0,L=H.length;K<L;++K){J=H.charAt(K);if(h(J)){D.push(F.length)}else{N=true}F.push(["text",J,B,B+1]);B+=1;if(J==="\n"){M()}}}if(!A.scan(I[0])){break}P=true;z=A.scan(s)||"name";A.scan(f);if(z==="="){H=A.scanUntil(u);A.scan(u);A.scanUntil(I[1])}else{if(z==="{"){H=A.scanUntil(new RegExp("\\s*"+e("}"+E[1])));A.scan(n);A.scanUntil(I[1]);z="&"}else{H=A.scanUntil(I[1])}}if(!A.scan(I[1])){throw new Error("Unclosed tag at "+A.pos)}C=[z,H,B,A.pos];F.push(C);if(z==="#"||z==="^"){G.push(C)}else{if(z==="/"){y=G.pop();if(!y){throw new Error('Unopened section "'+H+'" at '+B)}if(y[1]!==H){throw new Error('Unclosed section "'+y[1]+'" at '+B)}}else{if(z==="name"||z==="{"||z==="&"){N=true}else{if(z==="="){I=q(E=H.split(l))}}}}}y=G.pop();if(y){throw new Error('Unclosed section "'+y[1]+'" at '+A.pos)}return w(c(F))}function c(D){var A=[];var C,z;for(var B=0,y=D.length;B<y;++B){C=D[B];if(C){if(C[0]==="text"&&z&&z[0]==="text"){z[1]+=C[1];z[3]=C[3]}else{A.push(C);z=C}}}return A}function w(D){var F=[];var C=F;var E=[];var A,B;for(var z=0,y=D.length;z<y;++z){A=D[z];switch(A[0]){case"#":case"^":C.push(A);E.push(A);C=A[4]=[];break;case"/":B=E.pop();B[5]=A[2];C=E.length>0?E[E.length-1][4]:F;break;default:C.push(A)}}return F}function t(y){this.string=y;this.tail=y;this.pos=0}t.prototype.eos=function(){return this.tail===""};t.prototype.scan=function(A){var z=this.tail.match(A);if(z&&z.index===0){var y=z[0];this.tail=this.tail.substring(y.length);this.pos+=y.length;return y}return""};t.prototype.scanUntil=function(A){var z=this.tail.search(A),y;switch(z){case -1:y=this.tail;this.tail="";break;case 0:y="";break;default:y=this.tail.substring(0,z);this.tail=this.tail.substring(z)}this.pos+=y.length;return y};function p(z,y){this.view=z==null?{}:z;this.cache={".":this.view};this.parent=y}p.prototype.push=function(y){return new p(y,this)};p.prototype.lookup=function(y){var B;if(y in this.cache){B=this.cache[y]}else{var A=this;while(A){if(y.indexOf(".")>0){B=A.view;var C=y.split("."),z=0;while(B!=null&&z<C.length){B=B[C[z++]]}}else{B=A.view[y]}if(B!=null){break}A=A.parent}this.cache[y]=B}if(b(B)){B=B.call(this.view)}return B};function o(){this.cache={}}o.prototype.clearCache=function(){this.cache={}};o.prototype.parse=function(A,z){var y=this.cache;var B=y[A];if(B==null){B=y[A]=x(A,z)}return B};o.prototype.render=function(B,y,A){var C=this.parse(B);var z=(y instanceof p)?y:new p(y);return this.renderTokens(C,z,A,B)};o.prototype.renderTokens=function(G,y,E,I){var C="";var K=this;function z(L){return K.render(L,y,E)}var A,H;for(var D=0,F=G.length;D<F;++D){A=G[D];switch(A[0]){case"#":H=y.lookup(A[1]);if(!H){continue}if(k(H)){for(var B=0,J=H.length;B<J;++B){C+=this.renderTokens(A[4],y.push(H[B]),E,I)}}else{if(typeof H==="object"||typeof H==="string"){C+=this.renderTokens(A[4],y.push(H),E,I)}else{if(b(H)){if(typeof I!=="string"){throw new Error("Cannot use higher-order sections without the original template")}H=H.call(y.view,I.slice(A[3],A[5]),z);if(H!=null){C+=H}}else{C+=this.renderTokens(A[4],y,E,I)}}}break;case"^":H=y.lookup(A[1]);if(!H||(k(H)&&H.length===0)){C+=this.renderTokens(A[4],y,E,I)}break;case">":if(!E){continue}H=b(E)?E(A[1]):E[A[1]];if(H!=null){C+=this.renderTokens(this.parse(H),y,E,H)}break;case"&":H=y.lookup(A[1]);if(H!=null){C+=H}break;case"name":H=y.lookup(A[1]);if(H!=null){C+=a.escape(H)}break;case"text":C+=A[1];break}}return C};a.name="mustache.js";a.version="0.8.1";a.tags=["{{","}}"];var i=new o();a.clearCache=function(){return i.clearCache()};a.parse=function(z,y){return i.parse(z,y)};a.render=function(A,y,z){return i.render(A,y,z)};a.to_html=function(B,z,A,C){var y=a.render(B,z,A);if(b(C)){C(y)}else{return y}};a.escape=m;a.Scanner=t;a.Context=p;a.Writer=o}));
