;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var phpbb = window.phpbb || {};
		var Grid;

		var $container = $('#sitemaker-content-tiles');
		var $loadAnchor = $('#tile-load-more');

		// layout grid after each image loads
		$container.imagesLoaded().progress(function() {
			/* global AwesomeGrid */
			Grid = new AwesomeGrid('#sitemaker-content-tiles')
				.grid(1) 
				.mobile($container.data('mobile-columns'))
				.tablet($container.data('tablet-columns'))
				.desktop($container.data('desktop-columns'));
		});

		phpbb.ajaxify({
			selector: '#tile-load-more',
			refresh: false,
			callback: 'blitze.content.load_more'
		});

		phpbb.addAjaxCallback('blitze.content.load_more', function(response) {
			var respObj = $(response);
			var items = respObj.find('#sitemaker-content-grid .grid-item');
			var nextUrl = respObj.find('#tile-load-more').attr('href');

			$container.append(items);
			items.imagesLoaded(function() {
				Grid.apply();
			});

			if (nextUrl !== undefined) {
				$loadAnchor.attr('href', nextUrl);
			} else {
				$loadAnchor.parent().hide();
			}
		});
	});
})(jQuery, window, document);