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