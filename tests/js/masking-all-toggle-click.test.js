'use strict';

describe( 'maskingAllToggleClick', () => {
	let $foo, $bar, $baz, $all;

	// onetime set up
	beforeAll(() => {
		document.body.innerHTML = `
			<span class="masked-text" data-memorizer-key="foo">
				<span class="mask">***</span>
				<span class="original"></span>
			</span>
			<br>
			<span class="masked-text" data-memorizer-key="bar">
				<span class="mask">***</span>
				<span class="original"></span>
			</span>
			<br>
			<span class="masked-text" data-memorizer-key="baz">
				<span class="mask">***</span>
				<span class="original"></span>
			</span>
			<br>
			<a href="#" class="all-toggle-radius">all</a>
		`;

		global.memorizer = {
			foo: 'FOO',
			bar: 'BAR',
			baz: 'BAZ'
		};

		require( '../../assets/js/masking-all-toggle-click' );
		$.ready();
		// console.log( document.body.innerHTML );

		$foo = $( '[data-memorizer-key="foo"]' );
		$bar = $( '[data-memorizer-key="bar"]' );
		$baz = $( '[data-memorizer-key="baz"]' );
		$all = $( '.all-toggle-radius' );
	});

	// set up
	beforeEach(() => {
		$foo.removeClass('show');
		$bar.removeClass('show');
		$baz.removeClass('show');
	} );

	// tear down
	afterEach( () => {
	} );

	it( 'should be set to the original value at load time', () => {
		expect( $foo.children( '.original' ).text() ).toBe( 'FOO' );
		expect( $bar.children( '.original' ).text() ).toBe( 'BAR' );
		expect( $baz.children( '.original' ).text() ).toBe( 'BAZ' );
	} );

	it( 'should switch display when click an element', () => {
		$foo.click();
		expect( $foo.hasClass( 'show' ) ).toBe( true );
		expect( $bar.hasClass( 'show' ) ).toBe( false );
		expect( $baz.hasClass( 'show' ) ).toBe( false );

		$bar.click();
		expect( $foo.hasClass( 'show' ) ).toBe( true );
		expect( $bar.hasClass( 'show' ) ).toBe( true );
		expect( $baz.hasClass( 'show' ) ).toBe( false );

		$foo.click();
		expect( $foo.hasClass( 'show' ) ).toBe( false );
		expect( $bar.hasClass( 'show' ) ).toBe( true );
		expect( $baz.hasClass( 'show' ) ).toBe( false );
	} );

	test( "should switch the display when click 'All'", () => {
		$bar.click();
		$all.click();
		expect( $foo.hasClass( 'show' ) ).toBe( true );
		expect( $bar.hasClass( 'show' ) ).toBe( false );
		expect( $baz.hasClass( 'show' ) ).toBe( true );

		$all.click();
		expect( $foo.hasClass( 'show' ) ).toBe( false );
		expect( $bar.hasClass( 'show' ) ).toBe( true );
		expect( $baz.hasClass( 'show' ) ).toBe( false );
	} );
} );
