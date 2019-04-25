(function($, window, document, undefined) {
	"use strict";

	$(document).ready(function() {
		var phpbb = window.phpbb || {};
		var Grid;

		var $container = $("#sitemaker-content-tiles");
		var $loadAnchor = $("#tile-load-more").on("click", function() {
			$loadAnchor.children("i").show();
		});

		// layout grid after each image loads
		$container
			.imagesLoaded(function() {
			/* global AwesomeGrid */
			var options = $container.data("editMode") ? { context: "self" } : {};
			Grid = new AwesomeGrid("#sitemaker-content-tiles", options)
				.grid(1)
				.mobile($container.data("mobile-columns"))
				.tablet($container.data("tablet-columns"))
				.desktop($container.data("desktop-columns"));
			})
			.always(function() {
				$loadAnchor.children("i").hide();
			});

		phpbb.addAjaxCallback("blitze.content.load_more", function(response) {
			var $respObj = $(response);
			var $items = $respObj.find(".tile");
			var nextUrl = $respObj.find("#tile-load-more").attr("href");

			$items
				.imagesLoaded(function() {
					$container.append($items);
					Grid.apply();
	
					if (nextUrl !== undefined) {
						$loadAnchor.attr("href", nextUrl);
					} else {
						$loadAnchor.parent().hide();
					}
				})
				.always(function() {
					$loadAnchor.children("i").hide();
				});
		});
	});
})(jQuery, window, document);
