<?php
/**
 * Class WP_Memorizer_Test
 *
 * @package Memorizer
 */

class WP_Memorizer_Test extends WP_UnitTestCase {
	/** @var WP_Memorizer */
	private $instance;

	public function setUp() {
		parent::setUp();
		$ref  = new ReflectionClass( WP_Memorizer::class );
		$prop = $ref->getProperty( 'instance' );
		$prop->setAccessible( true );
		$this->instance = $prop->getValue();

		$prop = $ref->getProperty( 'dom_count' );
		$prop->setAccessible( true );
		$prop->setValue( $this->instance, 0 );
		$prop = $ref->getProperty( 'original_text_list' );
		$prop->setAccessible( true );
		$prop->setValue( $this->instance, [] );
	}

	public function tearDown() {
		parent::tearDown();
	}

	############################################################################

	/**
	 * @covers WP_Memorizer::__construct
	 * @throws ReflectionException
	 */
	public function test____construct() {


		$this->assertNotFalse( has_filter( 'mce_external_plugins', [ $this->instance, 'add_original_tinymce_button_plugin' ] ) );
		$this->assertNotFalse( has_filter( 'mce_buttons', [ $this->instance, 'add_original_tinymce_button' ] ) );
		$this->assertEquals( 11, has_filter( 'the_excerpt', [ $this->instance, 'do_shortcode_for_excerpt' ] ) );
		$this->assertEquals( 9, has_filter( 'get_the_excerpt', [ $this->instance, 'excerpt_care_before' ] ) );
		$this->assertEquals( 999, has_filter( 'get_the_excerpt', [ $this->instance, 'excerpt_care_after' ] ) );

		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', [ $this->instance, 'my_scripts_method' ] ) );
		$this->assertNotFalse( has_action( 'admin_print_footer_scripts', [ $this->instance, 'custom_add_quicktags' ] ) );
		$this->assertNotFalse( has_action( 'wp_footer', [ $this->instance, 'add_footer' ] ) );

		$this->assertTrue( shortcode_exists( 'memorizer' ) );
	}

	/**
	 * @covers WP_Memorizer::do_shortcode_for_excerpt
	 */
	public function test__do_shortcode_for_excerpt() {
		$html     = '<div>foo [memorizer]bar[/memorizer] baz</div>';
		$expected = 'foo ●●● baz';
		$actual   = $this->instance->do_shortcode_for_excerpt( $html );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @covers WP_Memorizer::excerpt_care_before
	 * @covers WP_Memorizer::excerpt_care_after
	 */
	public function test__excerpt_care() {
		// empty string
		$this->assertEmpty( $this->instance->excerpt_care_before( '' ) );
		$this->assertNotFalse( has_filter( 'strip_shortcodes_tagnames', [ $this->instance, 'evade_strip_shortcode' ] ) );
		$this->assertEquals( 'foo', $this->instance->excerpt_care_after( 'foo' ) );
		$this->assertFalse( has_filter( 'strip_shortcodes_tagnames', [ $this->instance, 'evade_strip_shortcode' ] ) );

		// not empty string
		$this->assertEquals( 'bar', $this->instance->excerpt_care_before( 'bar' ) );
		$this->assertFalse( has_filter( 'strip_shortcodes_tagnames', [ $this->instance, 'evade_strip_shortcode' ] ) );
		$this->assertEquals( 'baz', $this->instance->excerpt_care_after( 'baz' ) );
		$this->assertFalse( has_filter( 'strip_shortcodes_tagnames', [ $this->instance, 'evade_strip_shortcode' ] ) );
	}

	/**
	 * @covers WP_Memorizer::evade_strip_shortcode
	 */
	public function test__evade_strip_shortcode() {
		$tags     = [ 'foo', 'memorizer', 'bar', 'baz' ];
		$expected = [ 'foo', 'bar', 'baz' ];
		$actual   = $this->instance->evade_strip_shortcode( $tags );
		$this->assertEquals( $expected, $actual );
	}

	// /**
	//  * @covers WP_Memorizer::get_instance
	//  * @throws ReflectionException
	//  */
	// public function test__get_instance() {
	// 	$ref  = new ReflectionClass( $this->instance );
	// 	$prop = $ref->getProperty( 'instance' );
	// 	$prop->setAccessible( true );
	// 	$prop->setValue( $this->instance, null );
	//
	// 	// $prop = $ref->getProperty( 'instance' );
	// 	// $prop->setAccessible( true );
	// 	// var_dump( $prop->getValue() ); // -> NULL
	//
	// 	WP_Memorizer::get_instance();
	// 	$prop = $ref->getProperty( 'instance' );
	// 	$prop->setAccessible( true );
	// 	$instance = $prop->getValue();
	// 	$this->assertInstanceOf( WP_Memorizer::class, $instance );
	//
	// 	WP_Memorizer::get_instance();
	// 	$prop = $ref->getProperty( 'instance' );
	// 	$prop->setAccessible( true );
	// 	$this->assertSame($instance, $prop->getValue());
	// }

	/**
	 * @covers WP_Memorizer::add_original_tinymce_button_plugin
	 */
	public function test__add_original_tinymce_button_plugin() {
		$actual = $this->instance->add_original_tinymce_button_plugin( [ 'foo' => 'FOO' ] );
		$this->assertArrayHasKey( 'original_tinymce_button_plugin', $actual );
		$this->assertEquals( plugin_dir_url( dirname( __DIR__ ) ) . '/assets/js/masking-shortcode-icon-btm.js', $actual['original_tinymce_button_plugin'] );
	}

	/**
	 * @covers WP_Memorizer::add_original_tinymce_button
	 */
	public function test__add_original_tinymce_button() {
		$this->assertContains( 'recommended', $this->instance->add_original_tinymce_button( [ 'foo', 'bar' ] ) );
	}

	/**
	 * @covers WP_Memorizer::masking_over_func
	 * @throws ReflectionException
	 */
	public function test__masking_over_func() {
		$hash     = "cfcd208495d565ef66e7dff9f98764da"; // md5(0)
		$expected = ''
		            . '<span class="masked-text" data-memorizer-key="cfcd208495d565ef66e7dff9f98764da" >'
		            . '<span class="mask">●●●</span>'
		            . '<span class="original"></span>'
		            . '</span>';
		$actual   = $this->instance->masking_over_func( null, 'foobar' );

		$ref  = new ReflectionClass( $this->instance );
		$prop = $ref->getProperty( 'dom_count' );
		$prop->setAccessible( true );
		$dom_count = $prop->getValue( $this->instance );

		$prop = $ref->getProperty( 'original_text_list' );
		$prop->setAccessible( true );
		$original_text_list = $prop->getValue( $this->instance );

		$this->assertEquals( 1, $dom_count );
		$this->assertArrayHasKey( $hash, $original_text_list );
		$this->assertEquals( 'foobar', $original_text_list[ $hash ] );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @covers WP_Memorizer::custom_add_quicktags
	 */
	public function test__custom_add_quicktags() {
		wp_enqueue_script( 'quicktags' );
		$expected = ''
		            . '<script type="text/javascript">'
		            . "QTags.addButton('masking', 'memorizer', '[memorizer]', '[/memorizer]', '', 'Mask the text.', 1);"
		            . '</script>';
		ob_start();
		$this->instance->custom_add_quicktags();
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );
	}

	public function test__add_footer() {
		// No short code
		$post_id = $this->factory->post->create();
		$this->go_to( "?p={$post_id}" );
		ob_start();
		$this->instance->add_footer();
		$actual = ob_get_clean();
		$this->assertEmpty( $actual );

		// No original text
		$post_id = $this->factory->post->create( [ 'post_content' => '[memorizer]foo[/memorizer]' ] );
		$this->go_to( "?p={$post_id}" );
		$expected = '<a class="all-toggle-radius"></a>';
		ob_start();
		$this->instance->add_footer();
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );

		// Normal
		$ref  = new ReflectionClass( $this->instance );
		$prop = $ref->getProperty( 'original_text_list' );
		$prop->setAccessible( true );
		$prop->setValue( $this->instance, [ 'FOO' => 'foo' ] );

		$post_id = $this->factory->post->create( [ 'post_content' => '[memorizer]foo[/memorizer]' ] );
		$this->go_to( "?p={$post_id}" );
		$expected = ''
		            . '<a class="all-toggle-radius"></a>'
		            . '<script type="text/javascript"> var memorizer ={"FOO":"foo"}</script>';
		ob_start();
		$this->instance->add_footer();
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );
	}
}
