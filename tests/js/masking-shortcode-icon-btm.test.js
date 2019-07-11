'use strict';

describe( 'maskingShortcodeIconBtm', () => {
	beforeAll( () => {
		global.tynyMCE = global.tinymce = require( 'tinymce/tinymce' );
		require( '../../assets/js/masking-shortcode-icon-btm' );
		$.ready();
	} );

	it( 'should be create tinymce button', () => {
		expect( 1 ).toBe( 1 );
		expect( tinymce.plugins.original_tinymce_button ).toBeDefined();
		let url = '/js/hoge/js/fuga.js';
		const ed = {
			addButton: function ( arg1, arg2 ) {
				expect( arg1 ).toBe( 'recommended' );
				expect( arg2.title ).toBe( 'Mark the text.' );
				expect( arg2.image ).toBe( '/hoge/fuga.js/images/masking-icon.png' );
				expect( arg2.cmd ).toBe( 'recommended_cmd' );
			},
			addCommand: function ( arg1, arg2 ) {
				expect( arg1 ).toBe( 'recommended_cmd' );
				arg2();
			},
			execCommand: function ( arg1, arg2, arg3 ) {
				expect(arg1).toBe('mceInsertContent');
				expect(arg2).toBe(0);
				expect(arg3).toBe('[memorizer][/memorizer]');
			}
		};

		tinymce.plugins.original_tinymce_button.prototype.init( ed, url );

		expect(tinymce.plugins.original_tinymce_button.prototype.createControl(1, 2)).toBeNull();
	} );

	it("should be add tinymce button", () => {
		expect(tinymce.PluginManager.get('original_tinymce_button_plugin')).toBeDefined();
		expect(tinymce.PluginManager.get('original_tinymce_button_plugin')).toBe(tinymce.plugins.original_tinymce_button);
	});
} );
