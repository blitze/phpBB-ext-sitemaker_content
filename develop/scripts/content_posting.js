;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		$('.color_palette').each(function() {
			var el = $(this);
			var	orientation	= el.data('orientation');
			var height		= el.data('height');
			var width		= el.data('width');
			var target		= el.data('target');
			var bbcode		= el.data('bbcode');

			// Insert the palette HTML into the container.
			el.html(phpbb.colorPalette(orientation, width, height));

		}).filter(':gt(0)').on('click', 'a', function(e) {
			// Attach event handler when a palette cell is clicked.
			var color = $(this).attr('data-color');
			bbfontstyle('[color=#' + color + ']', '[/color]');
		});
	});
})(jQuery, window, document);
