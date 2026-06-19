<?php
declare( strict_types=1 );

namespace GutSlider\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles loading of Google Fonts used in slider blocks.
 *
 * Collects font family attributes from rendered blocks and enqueues
 * the corresponding Google Fonts stylesheets.
 *
 * @package GutSlider\Assets
 * @since   3.0.0
 */
final class LoadFonts {

	/**
	 * System fonts that do not need to be loaded from Google Fonts.
	 *
	 * @since 3.0.0
	 * @var array<int, string>
	 */
	private const SYSTEM_FONTS = array(
		'Arial',
		'Tahoma',
		'Verdana',
		'Helvetica',
		'Times New Roman',
		'Trebuchet MS',
		'Georgia',
	);

	/**
	 * Collected font families from rendered blocks.
	 *
	 * @since 3.0.0
	 * @var array<int, string>
	 */
	private static array $all_fonts = array();

	/**
	 * Constructor.
	 *
	 * Registers WordPress hooks for font loading.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'fonts_loader' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'fonts_loader' ), 10 );
		add_action( 'gutsliders_render_block', array( $this, 'font_generator' ) );
	}

	/**
	 * Extract font families from a rendered block's attributes.
	 *
	 * Scans block attributes for font family values and triggers
	 * the font loader to enqueue any new fonts found.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string, mixed> $block The block data array.
	 * @return void
	 */
	public function font_generator( array $block ): void {
		if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
			$attributes = $block['attrs'];

			foreach ( $attributes as $key => $value ) {
				if ( ! empty( $value )
					&& is_string( $value )
					&& strpos( $key, 'gutsliders_' ) === 0
					&& strpos( $key, 'FontFamily' ) !== false
				) {
					self::$all_fonts[] = sanitize_text_field( $value );
				}
			}

			$this->fonts_loader();
		}
	}

	/**
	 * Enqueue Google Fonts stylesheets for collected font families.
	 *
	 * Filters out system fonts and duplicates, then registers and
	 * enqueues individual Google Fonts CSS files.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function fonts_loader(): void {
		if ( ! is_array( self::$all_fonts ) || count( self::$all_fonts ) === 0 ) {
			return;
		}

		$fonts = array_filter( array_unique( self::$all_fonts ) );

		if ( empty( $fonts ) ) {
			return;
		}

		$gfonts_attr = ':100,200,300,400,500,600,700,800,900';

		foreach ( $fonts as $font ) {
			if ( in_array( $font, self::SYSTEM_FONTS, true ) || empty( $font ) ) {
				continue;
			}

			$font_family = str_replace( ' ', '+', trim( $font ) ) . $gfonts_attr;
			$query_args  = array( 'family' => $font_family );
			$font_handle = 'gutsliders-font-' . sanitize_title( $font );

			wp_register_style(
				$font_handle,
				add_query_arg( $query_args, '//fonts.googleapis.com/css' ),
				array(),
				GUTSLIDER_VERSION,
				'all'
			);

			wp_enqueue_style( $font_handle );
		}
	}
}
