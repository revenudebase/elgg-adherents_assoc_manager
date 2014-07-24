elgg.provide('elgg.eaam.map');


elgg.eaam.map.init = function() {
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
		elgg.eaam.map.addAdherent(adh);
	});

	map.addLayer(layer);
};



elgg.eaam.map.addAdherent = function(adh) {
	var marker,
		ville = dataFrance[adh.location];

	ville = elgg.isUndefined(ville) ? {lat: 45, long: '-3'} : ville[0];
	marker = L.marker(new L.LatLng(ville.lat, ville.long));
	marker.bindPopup(elgg.handlebars('map-popup-user')(adh));
	layer.addLayer(marker);
};
