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



/**
 * Add adherent in the map
 * @param {ElggUser} an adherent
 */
elgg.eaam.map.addAdherent = function(adh) {
	var marker,
		ville = adh.location ? elgg.eaam.map.getCity(adh.location) : null; // in case user don't get location

	ville = elgg.isNullOrUndefined(ville) ? {lat: 45, long: '-3'} : ville;
	marker = L.marker(new L.LatLng(ville.lat, ville.long));
	marker.bindPopup(elgg.handlebars('map-popup-user')(adh));
	layer.addLayer(marker);
};



/**
 * Return data of a city by postal code. If city doesn't match, we degrade postal code until we match something. Else, it return false.
 * @param  {string} postalCode The postal code to match
 * @return {Object}            data of the city
 */
elgg.eaam.map.getCity = function(postalCode) {
	var city = dataFrance[parseInt(postalCode)];
	if (!elgg.isUndefined(city)) return city[0];

	if (postalCode.replace(/0*$/, '').length > 2) {
		return elgg.eaam.map.getCity(postalCode.replace(/[1-9]([^1-9]*)$/,'0'+'$1'));
	} else {
		return false;
	}
};