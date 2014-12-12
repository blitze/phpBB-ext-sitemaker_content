;(function($, window, document, undefined) {
	'use strict';

	$(document).ready(function() {
		var slug = $('#topic-slug');
		$('#topic-subject').on('blur keyup change click', function(e) {
			var title = $(this).val().toLowerCase()
				.replace(/[^\w ]+/g, '')
				.replace(/ +/g, '-');
			slug.val(title);
		});
	});
})(jQuery, window, document);
