;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var phpbb = window.phpbb || {};

		var container = $('#sitemaker-content-grid');
		var loadAnchor = $('#tile-load-more');

		// initialize Masonry after all images have loaded
		container.imagesLoaded(function() {
			container.masonry({
				isAnimated: true,
				itemSelector: '.item'
			});
		});

		phpbb.ajaxify({
			selector: '#tile-load-more',
			refresh: false,
			callback: 'blitze.content.load_more'
		});

		phpbb.addAjaxCallback('blitze.content.load_more', function(response) {
			var respObj = $(response);
			var items = respObj.find('#sitemaker-content-grid .item');
			var nextUrl = respObj.find('#tile-load-more').attr('href');

			container.append(items);
			items.imagesLoaded(function() {
				container.masonry('appended', items);
			});

			if (nextUrl !== undefined) {
				loadAnchor.attr('href', nextUrl);
			} else {
				loadAnchor.parent().hide();
			}
		});

		$('body').on('layoutChanged', function() {
			container.masonry();
		});
	});
})(jQuery, window, document);