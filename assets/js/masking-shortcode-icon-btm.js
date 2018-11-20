(function ($) {

	$(function () {
		/**
		 * Added a button for entering a short code for masking text on the visual tab of the post entry screen
		 */
		tinymce.create('tinymce.plugins.original_tinymce_button', {
			init: function (ed, url) {
				url = url.replace(/\/js/g, "");

				ed.addButton('recommended', {
					title: 'Mark the text.',
					image: url + '/images/masking-icon.png',
					cmd: 'recommended_cmd'
				});
				ed.addCommand('recommended_cmd', function () {
					var return_text = '[memorizer][/memorizer]';
					ed.execCommand('mceInsertContent', 0, return_text);
				});
			},
			createControl: function (n, cm) {
				return null;
			},
		});
		tinymce.PluginManager.add('original_tinymce_button_plugin', tinymce.plugins.original_tinymce_button);
	});

}(jQuery));