;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var phpbb = window.phpbb || {};

		$('.color_palette').each(function() {
			var el = $(this);
			var	orientation	= el.data('orientation');
			var height		= el.data('height');
			var width		= el.data('width');

			// Insert the palette HTML into the container.
			el.html(phpbb.colorPalette(orientation, width, height));

		}).filter(':gt(0)').on('click', 'a', function() {
			// Attach event handler when a palette cell is clicked.
			var color = $(this).attr('data-color');
			window.bbfontstyle('[color=#' + color + ']', '[/color]');
		});

		var tocItems = $('#preview-detail-panel .toc a, #preview-detail-panel .pagination a').click(function(e) {
			var url = $(this).attr('href').match(/\/(\d+)$/);
			var page = (url !== null) ? url[1] : 1;
			$('.pages').hide();
			$('#page-' + page).show();
			tocItems.removeClass('current');
			if (page > 1) {
				tocItems.filter('[href$="' + page + '"]').addClass('current');
			} else {
				tocItems.filter('.first').addClass('current');
			}
			e.preventDefault();
		});
	});
})(jQuery, window, document);
