;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var field = $('.datetimepicker').attr('type', 'text');

		field.each(function(idx, item) {
			var picker = $(item);
			var options = {
				mask: true
			};
			var settings = picker.data();
			$.each(settings, function(key, val) {
				options[key] = val;
			});

			picker.datetimepicker(options);
		});
	});
})(jQuery, window, document);