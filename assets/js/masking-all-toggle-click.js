(function ($) {

	$(function () {
		if (typeof memorizer !== 'undefined') {
			var memorizer_dom = $('.masked-text');
			var toggle = 'show';

			/**
			 * Set up answers
			 */
			memorizer_dom.each(function (i, el) {
				var key = $(el).data('memorizer-key');
				$('span[data-memorizer-key="' + key + '"] .original').html(memorizer[key]);
			});

			/**
			 * Switching masking clicked with mouse alternately between display and non-display
			 */
			memorizer_dom.on('click', function () {
				$(this).toggleClass(toggle);
			});

			/**
			 * Switching all masking alternately between display and non-display
			 */
			$('.all-toggle-radius').on('click', function (e) {
				e.preventDefault();
				memorizer_dom.toggleClass(toggle);
			});
		}
	});

}(jQuery));