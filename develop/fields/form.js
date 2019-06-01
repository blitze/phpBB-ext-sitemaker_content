// Responsive Filemanager callback
function responsive_filemanager_callback(field_id) {
	'use strict';
	/* global $ */
	$('#' + field_id).trigger('change');
}

/*
 * Initialize Google Map with marker
 */
var google = window.google;
var $mapFields = {};

function getInfoWindowContent(title, address) {
	'use strict';

	return (title ? '<h4>' + title + '</h4>' : '') + address;
}

function updateLocationInfo(fieldName, place, infowindow) {
	'use strict';

	$mapFields[fieldName].address.val(place.formatted_address);
	$mapFields[fieldName].latitude.val(place.geometry.location.lat);
	$mapFields[fieldName].longitude.val(place.geometry.location.lng);
	$mapFields[fieldName].place.val(place.name);

	infowindow.setContent(getInfoWindowContent(place.name, place.formatted_address));
}

function initAutocomplete(fieldName, map, marker, infowindow) {
	'use strict';

	$mapFields[fieldName] = {
		'map': $('#map-' + fieldName),
		'search': $('#search-' + fieldName),
		'address': $('#' + fieldName + '-address'),
		'latitude': $('#' + fieldName + '-latitude'),
		'longitude': $('#' + fieldName + '-longitude'),
		'place': $('#' + fieldName + '-place'),
		'mapType': $('#' + fieldName + '-map-type'),
		'zoom': $('#' + fieldName + '-zoom')
	};

	marker.setDraggable(true);

	var geocoder = new google.maps.Geocoder();
	var input = $mapFields[fieldName].search[0];
	var autocomplete = new google.maps.places.Autocomplete(input);

	// Listen for the event fired when the user selects a prediction and retrieve
	// more details for that place.
	autocomplete.addListener('place_changed', function() {
		infowindow.close();
		marker.setVisible(false);
		var place = autocomplete.getPlace();
		if (!place.geometry) {
			// User entered the name of a Place that was not suggested and
			// pressed the Enter key, or the Place Details request failed.
			window.alert("No details available for input: '" + place.name + "'");
			return;
		}

		// If the place has a geometry, then present it on a map.
		if (place.geometry.viewport) {
			map.fitBounds(place.geometry.viewport);
		} else {
			map.setCenter(place.geometry.location);
		}
		marker.setPosition(place.geometry.location);
		marker.setVisible(true);

		updateLocationInfo(fieldName, place, infowindow);
		infowindow.open(map, marker);
	});

	// event to capture zoom level
	google.maps.event.addListener(map, 'zoom_changed', function() {
		$mapFields[fieldName].zoom.val(map.getZoom());
	});

	// event to capture map type
	google.maps.event.addListener(map, 'maptypeid_changed', function() {
		$mapFields[fieldName].mapType.val(map.getMapTypeId());
	});

	//Add listener to marker for reverse geocoding
	google.maps.event.addListener(marker, "dragend", function() {
		var marker = this;
		var latlng = this.getPosition();
		map.panTo(latlng);
		geocoder.geocode({ 'location': latlng }, function(results, status) {
			if (status === google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					map.setCenter(results[0].geometry.location);
					marker.setPosition(results[0].geometry.location);

					var request = {
						placeId: results[0].place_id
					};

					var service = new google.maps.places.PlacesService(map);
					service.getDetails(request, function(place, status) {
						if (status === google.maps.places.PlacesServiceStatus.OK) {
							console.log(place);
							updateLocationInfo(fieldName, place, infowindow);
						}
					});
				} else {
					window.alert('No results found');
				}
			} else {
				window.alert('Geocoder failed due to: ' + status);
			}
		});
	});
}

function initMap() {
	'use strict';

	$('.location-map').each(function() {
		var fieldName = $(this).attr('id').substring(4);
		var data = $(this).data();

		var latlng = new google.maps.LatLng(data.latitude, data.longitude);
		var map = new google.maps.Map(this, {
			zoom: data.zoom || 16,
			center: latlng,
			tilt: 45,
			mapTypeId: data.mapType,
			mapTypeControl: true,
			mapTypeControlOptions: {
				mapTypeIds: data.mapTypes.split(',')
			}
		});

		var marker = new google.maps.Marker({
			map: map,
			title: data.place,
			position: latlng
		});

		var infowindow = new google.maps.InfoWindow();

		if (data.address) {
			infowindow.setContent(getInfoWindowContent(data.place, data.address));
			setTimeout(function() {
				infowindow.open(map, marker);
			}, 500);
		}

		marker.addListener('click', function() {
			if ($mapFields[fieldName].address.val()) {
				infowindow.open(map, marker);
			}
		});

		$(this).width(data.width).height(data.height);

		if (data.autocomplete) {
			initAutocomplete(fieldName, map, marker, infowindow);
		}
	});
}

(function($, window, document) {
	'use strict';

	/**
	 * Resize datepicker input field to appropriate size for picker type
	 */
	function resizeDatePickerInput($element) {
		var width = $element.val().length || 15;
		var adj = $element.data('range') || $element.data('multiple-dates') || 1;
		$element.parent().width(width + (adj * 6) + '%');
	}

	$(document).ready(function() {

		/**
		 * Color Picker
		 */
		if (window.tinycolor) {
			var cache = {};
			$('.colorpicker').each(function() {
				var options = $(this).data();

				if (options.palette) {
					options.showPalette = true;
					if (cache[options.palette]) {
						options.palette = cache[options.palette];
					} else {
						var rows = options.palette.replace(/ /g, '').split("\n");

						var palette = [];
						$.each(rows, function(i, str) {
							palette.push(str.trim().split(','));
						});

						cache[options.palette] = palette;
						options.palette = palette;
					}
				}

				$(this).spectrum($.extend(options, {
					hideAfterPaletteSelect: true,
					allowEmpty:true,
    				showInitial: true,
    				showInput: true,
					showButtons: false,
					preferredFormat: 'hex'
				}));
			});

			// overwrite prosilver's fieldset field1 margin-bottom: 3px
			$('.sp-replacer div').css('marginBottom', 0);
		}

		/**
		 * Datetime picker
		 */
		$('.datetimepicker').each(function() {
			var options = $(this).data();
			var current = $(this).val();

			options = $.extend(options, {
				minDate: (options.minDate) ? new Date(options.minDate) : '',
				maxDate: (options.maxDate) ? new Date(options.maxDate) : '',
				language: window.dpLang || {},
				onSelect: function(fd, d, picker) {
					resizeDatePickerInput($(picker.el));
				}
			});

			var dp = $(this).datepicker(options).data('datepicker');
			resizeDatePickerInput($(this));

			if (current) {
				var selected = [];
				$.each(current.split(','), function(i, dateStr) {
					selected.push(new Date(dateStr));
				});
				dp.selectDate(selected);
			}
		});

		/**
		 * Range picker
		 */
		if (typeof $.fn.ionRangeSlider !== 'undefined') {
			$('.rangepicker').ionRangeSlider();
		}

		/**
		 * Image field
		 */
		$('.image-field').each(function() {
			$(this).change(function() {
				var imgSrc = $(this).val();
				var fieldId = $(this).attr('name');

				$('#preview-' + fieldId).html(imgSrc.length ? '<img src=' + imgSrc + ' />' : '');
			})
			.next()
			.fancybox({
				'width': 900,
				'height': 600,
				'autoScale': false,
				'type': 'iframe'
			});
		});
	});
})(jQuery, window, document);
