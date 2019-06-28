/* global $ */

/**
 * Display location map
 */
function initMap() { // jshint ignore:line
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

/**
 * social share
 */
function initSocialShare() { // jshint ignore:line
	'use strict';

	$('.social-share').each(function() {
		var options = $(this).data();
		options.shares = options.shares.split(',');

		$(this).jsSocials(options);
	});
}

$(document).ready(function() {
	'use strict';

	if (window.jsSocials) {
		initSocialShare();
	}

	/**
	 * Range picker
	 */
	if (typeof $.fn.ionRangeSlider !== 'undefined') {
		$('.rangepicker').ionRangeSlider();
	}
});
