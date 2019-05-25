(function($, window, document, undefined) {
	"use strict";

	$(document).ready(function() {
		var $grid = $('#sitemaker-content-tiles').masonry({
			itemSelector: '.grid-item',
			columnWidth: '.tile',
			stamp: '.stamp',
			stagger: 30,
			negativeMargin: 100,
			percentPosition: true,
			containerStyle: null
		});

		var $ias = $.ias({
			container: '#sitemaker-content-tiles',
			item: ".grid-item",
			pagination: "#pagination",
			next: ".next a",
			delay: 1200,
		});

		$ias.on('render', function(items) {
			$(items).css({ opacity: 0 });
		});

		$ias.on('rendered', function(items) {
			var $items = $(items).imagesLoaded(function() {
				$grid.masonry('appended', $items);
			});
		});

		$ias.extension(new window.IASSpinnerExtension({
			html: '<div class="ias-btn align-center"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>'
		}));

		if ($grid.data('offset') > 0) {
			$ias.extension(new window.IASTriggerExtension({
				offset: $grid.data('offset'),
				text: $grid.data('load-more-lang'),
				html: '<div class="ias-btn align-center sm-badge primary-color"><a class="info">{text}</a></div>'
			}));
		}

		$ias.extension(new window.IASNoneLeftExtension({
			text: $grid.data('no-more-lang'),
			html: '<div class="ias-btn align-center"><strong>{text}</strong></div>'
		}));
	});
})(jQuery, window, document);
