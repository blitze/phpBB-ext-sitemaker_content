(function($, window, document, undefined) {
	'use strict';

	var swipers = {};

	function initSwiper(bid) {
		var elementId = '#swiper-' + bid;
		var $element = $(elementId);
		var options = $element.data();

		if (!options) {
			return;
		}

		if (options.autoplay) {
			options.autoplay = {
				delay: options.autoplay
			};
		}

		if (options.navigation) {
			options.navigation = {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev'
			};
		}

		if (options.pagination) {
			options.pagination = {
				el: '.swiper-pagination',
				type: options.pagination,
				clickable: true
			};
		}
		options.keyboard = { enabled: true };
		options.loopedSlides = options.slidesPerView + 5;

		swipers[bid] = {
			'top': new Swiper(elementId, options),
			'bottom': ''
		};

		if (options.thumbs) {
			swipers[bid]['bottom'] = new Swiper('#thumbs-' + bid, {
				spaceBetween: 10,
				centeredSlides: true,
				slidesPerView: 'auto',
				touchRatio: 0.2,
				slideToClickedSlide: true,
				loop: options.loop,
				loopedSlides: options.loopedSlides
			});
			swipers[bid]['top'].controller.control = swipers[bid]['bottom'];
			swipers[bid]['bottom'].controller.control = swipers[bid]['top'];
		}
	}

	$(document).ready(function() {
		$('.swiper').each(function() {
			var bid = $(this).attr('id').substring(7);
			initSwiper(bid);
		});
		$('body')
			.on('blitze_sitemaker_renderBlock_before', function(e, data) {
				if (swipers[data.bid]) {
					swipers[data.bid].top.destroy();
					swipers[data.bid].bottom ? swipers[data.bid].bottom.destroy() : '';
				}
			})
			.on('blitze_sitemaker_renderBlock_after', function(e, data) {
				if (data.name === "blitze.content.block.swiper") {
					initSwiper(data.bid);
				}
			});
	});
})(jQuery, window, document);
