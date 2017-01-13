;(function($, window, document) {
	'use strict';

	$(document).ready(function() {
		var phpbb = window.phpbb || {};
		var bbcodeEditors = $('textarea[data-bbcode="true"]').focus(function() {
			window['text_name'] = $(this).attr('name');
		});

		/**
		 * Update the indices used in inline attachment bbcodes. This ensures that the
		 * bbcodes correspond to the correct file after a file is added or removed.
		 * This should be called before the phpbb.plupload,data and phpbb.plupload.ids
		 * arrays are updated, otherwise it will not work correctly.
		 *
		 * @param {string} action	The action that occurred -- either "addition" or "removal"
		 * @param {int} index		The index of the attachment from phpbb.plupload.ids that was affected.
		 */
		phpbb.plupload.updateBbcode = function(action, index) {
			bbcodeEditors.each(function() {
				var	textarea = $(this, phpbb.plupload.form);
				var text = textarea.val();
				var removal = (action === 'removal');

				// Return if the bbcode isn't used at all.
				if (text.indexOf('[attachment=') === -1) {
					return;
				}

				function runUpdate(i) {
					var regex = new RegExp('\\[attachment=' + i + '\\](.*?)\\[\\/attachment\\]', 'g');
					text = text.replace(regex, function updateBbcode(_, fileName) {
						// Remove the bbcode if the file was removed.
						if (removal && index === i) {
							return '';
						}
						var newIndex = i + ((removal) ? -1 : 1);
						return '[attachment=' + newIndex + ']' + fileName + '[/attachment]';
					});
				}

				// Loop forwards when removing and backwards when adding ensures we don't
				// corrupt the bbcode index.
				var i;
				if (removal) {
					for (i = index; i < phpbb.plupload.ids.length; i++) {
						runUpdate(i);
					}
				} else {
					for (i = phpbb.plupload.ids.length - 1; i >= index; i--) {
						runUpdate(i);
					}
				}

				textarea.val(text);
			});
		};

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
