<?php

/*
Plugin Name: Memorizer
Plugin URI:
Description: Masking a part of content by [memorizer] shortcode, and unmask it by click. You can make word books, quizzes and exams by using this plugin.
Version: 1.0.1
Author: PRESSMAN
Author URI: https://www.pressman.ne.jp/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Memorizer {

	private static $instance;
	private $dom_count = 0;
	private $original_text_list = [];
	const MASK = '●●●';

	private function __construct() {
		add_filter( 'mce_external_plugins', [ $this, 'add_original_tinymce_button_plugin' ] );
		add_filter( 'mce_buttons', [ $this, 'add_original_tinymce_button' ] );
		add_shortcode( 'memorizer', [ $this, 'masking_over_func' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'my_scripts_method' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'custom_add_quicktags' ] );
		add_action( 'wp_footer', [ $this, 'add_footer' ] );

		// care for excerpt
		add_filter( 'the_excerpt', [ $this, 'do_shortcode_for_excerpt' ], 11 );
		add_filter( 'get_the_excerpt', [
			$this,
			'excerpt_care_before'
		], 9 );
		add_filter( 'get_the_excerpt', [ $this, 'excerpt_care_after' ], 999 );
	}

	//-------------------------------------------

	/**
	 * In excerpt, make short code available.
	 */
	public function do_shortcode_for_excerpt( $text ) {
		return wp_strip_all_tags( do_shortcode( $text ) );
	}

	public function excerpt_care_before( $text ) {
		if ( '' === $text ) {
			add_filter( 'strip_shortcodes_tagnames', [ $this, 'evade_strip_shortcode' ] );
		}

		return $text;
	}

	public function excerpt_care_after( $text ) {
		if ( has_filter( 'strip_shortcodes_tagnames', [ $this, 'evade_strip_shortcode' ] ) ) {
			remove_filter( 'strip_shortcodes_tagnames', [ $this, 'evade_strip_shortcode' ] );
		}

		return $text;
	}

	public function evade_strip_shortcode( $tags ) {
		if ( in_array( 'memorizer', $tags ) ) {
			$tags = array_values( array_diff( $tags, [ 'memorizer' ] ) );
		}

		return $tags;
	}

	// -------------------------------------------

	/**
	 * Get instances
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
	}

	/**
	 * Added a button for entering a short code for masking text on the visual tab of the post entry screen
	 *
	 * @param array $plugin_array
	 *
	 * @return array $plugin_array
	 */
	public function add_original_tinymce_button_plugin( $plugin_array ) {
		$plugin_array['original_tinymce_button_plugin'] = plugin_dir_url( __FILE__ ) . '/assets/js/masking-shortcode-icon-btm.js';

		return $plugin_array;
	}

	/**
	 * Add button to tinymce
	 *
	 * @param array $buttons
	 *
	 * @return array $buttons
	 */
	public function add_original_tinymce_button( $buttons ) {
		$buttons[] = 'recommended';

		return $buttons;
	}

	/**
	 * Shortcode : Replace the short code with html
	 *
	 * @param array $atts
	 * @param null $content
	 *
	 * @return string
	 */
	public function masking_over_func( $atts, $content = null ) {
		$hash                              = md5( $this->dom_count );
		$this->original_text_list[ $hash ] = $content;
		$html                              = '<span class="masked-text" data-memorizer-key="' . $hash . '" >'
		                                     . '<span class="mask">' . self::MASK . '</span>'
		                                     . '<span class="original"></span>'
		                                     . '</span>';
		$this->dom_count ++;

		return $html;
	}

	/**
	 * Load css, js
	 */
	public function my_scripts_method() {
		global $post;

		if ( isset( $post ) && isset( $post->post_content ) && is_singular() && has_shortcode( $post->post_content, 'memorizer' ) ) {
			wp_enqueue_style( 'memorizer' . '-style', plugin_dir_url( __FILE__ ) . '/assets/css/style.css' );
			wp_enqueue_script( 'memorizer' . '-script', plugin_dir_url( __FILE__ ) . '/assets/js/masking-all-toggle-click.js', [ 'jquery' ], false, true );
		}
	}

	/**
	 * Added a button for entering a short code for masking characters on the text tab of the post entry screen
	 */
	public function custom_add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
			echo '<script type="text/javascript">'
			     . "QTags.addButton('masking', 'memorizer', '[memorizer]', '[/memorizer]', '', 'Mask the text.', 1);"
			     . '</script>';
		}
	}

	/**
	 * Show wp_footer button to toggle display / non-display of all masking alternately
	 */
	public function add_footer() {
		global $post;

		if ( isset( $post ) && isset( $post->post_content ) && is_singular() && has_shortcode( $post->post_content, 'memorizer' ) ) {
			echo '<a class="all-toggle-radius"></a>';
			if ( ! empty( $this->original_text_list ) ) {
				echo '<script type="text/javascript"> var memorizer =' . json_encode( $this->original_text_list ) . '</script>';
			}
		}
	}
}

WP_Memorizer::get_instance();