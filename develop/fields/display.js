/**
 * Display location map
 */
function initMap() {
	'use strict';

	$('.location-map').each(function() {
		var google = window.google;
		var data = $(this).data();
		var position = { lat: data.latitude, lng: data.longitude };
		var map = new google.maps.Map(this, {
			zoom: data.zoom || 16,
			center: position,
			tilt: 45,
			mapTypeId: data.mapType
		});

		var infowindow = new google.maps.InfoWindow({
			content: ((data.place) ? '<h4>' + data.place + '</h4>' : '') + data.address
		});

		var marker = new google.maps.Marker({
			map: map,
			position: position,
			title: data.place
		});

		marker.addListener('click', function() {
			if (data.place) {
				infowindow.open(map, marker);
			}
		});

		$(this).width(data.width).height(data.height);
	});
}
