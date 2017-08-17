;(function($, window, document) {
	'use strict';

	var resizeInput = function(element) {
		element.parent().width(element.val().length + '%');
	};

	$(document).ready(function() {

		// Color Picker
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
					preferredFormat: 'hex',
					showButtons: false
				}));
			});

			// overwrite prosilver's fieldset field1 margin-bottom: 3px
			$('.sp-replacer div').css('marginBottom', 0)
		}

		// Datetime picker
		$('.datetimepicker').each(function() {
			var options = $(this).data();
			var current = $(this).val();

			options = $.extend(options, {
				minDate: (options.minDate) ? new Date(options.minDate) : '',
				maxDate: (options.maxDate) ? new Date(options.maxDate) : '',
				language: window.dpLang || {},
				onSelect: function (fd, d, picker) {
					resizeInput($(picker.el));
				}
			});

			var dp = $(this).datepicker(options).data('datepicker');
			resizeInput($(this));

			if (current) {
				var selected = [];
				$.each(current.split(','), function(i, dateStr) {
					selected.push(new Date(dateStr))
				});
				dp.selectDate(selected);
			}
		});

		// Range picker
		$('.rangepicker').ionRangeSlider();

		// image field
		$('.image-field').change(function(e) {
			var imgSrc = $(this).val();

			$(this).next().children('.img-ui').html(imgSrc.length ? '<img src=' + imgSrc + ' />' : '');
		})
	});
})(jQuery, window, document);