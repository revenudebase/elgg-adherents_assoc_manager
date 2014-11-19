elgg.provide('elgg.eaam.map');

/**
 * Set default settings
 */
elgg.eaam.map.settings = {
	centerFrance: [46.763056, 2.424722],
	defaultZoom: 6 - (($(window).width() <= 768) ? 1 : 0), // zoom par défaut,
	adrerentWithoutLocation: {lat: 45, lng: '-3'}
};



/**
 * Initialise map and add adherents markers
 */
map = null;
layer = null;
elgg.eaam.map.init = function() {
	map = L.map('map-adherents').setView(elgg.eaam.map.settings.centerFrance, elgg.eaam.map.settings.defaultZoom);
	L.Icon.Mine = L.Icon.Default.extend({
		options: {
			iconUrl: elgg.get_site_url()+'/mod/elgg-adherents_assoc_manager/graphics/marker-icon-mine.png'
		}
	});

	L.tileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpeg', { // http://leaflet-extras.github.io/leaflet-providers/preview/index.html
		attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/">MapQuest</a> &mdash; Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		subdomains: '1234',
		maxZoom: 18
	}).addTo(map);

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
		ville = adh.location ? elgg.eaam.map.getCity(adh.location) : null, // in case user don't get location
		icon = elgg.get_logged_in_user_guid() == adh.guid ? {icon: new L.Icon.Mine()} : {};

	ville = elgg.isNullOrUndefined(ville) ? elgg.eaam.map.settings.adrerentWithoutLocation : ville;
	marker = L.marker(new L.LatLng(ville.lat, ville.lng), icon);
	marker.bindPopup(elgg.handlebars('map-popup-user')(adh));
	adh.marker = marker; // add marker to map_adherents
	layer.addLayer(marker);
};



/**
 * Return data of a city by postal code. If city doesn't match, we degrade postal code until we match something. Else, it return false.
 * @param  {string} postalCode The postal code to match
 * @return {Object}            data of the city
 */
elgg.eaam.map.getCity = function(postalCode) {
	if (!$.isNumeric(postalCode) || postalCode.length != 5) return false;
	var city = dataFrance[parseInt(postalCode)];
	if (!elgg.isUndefined(city)) return city[0];

	if (postalCode.replace(/0*$/, '').length > 2) {
		return elgg.eaam.map.getCity(postalCode.replace(/[1-9]([^1-9]*)$/,'0'+'$1'));
	} else {
		return false;
	}
};


elgg.eaam.map.showMyMarker = function() {
	var myMarker = map_adherents[elgg.get_logged_in_user_guid()].marker;

	$('#map-adherents').click(); // close popup if any
	layer.zoomToShowLayer(myMarker, function(){
		map.setView(myMarker.getLatLng(), map.getZoom());
		myMarker.openPopup();
	});
};