<?php
declare( strict_types=1 );

namespace GutSlider\Style;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates and enqueues dynamic CSS for rendered slider blocks.
 *
 * Collects block-level styles during rendering, combines and minifies
 * them into a single CSS file, and enqueues it for the current page.
 *
 * @package GutSlider\Style
 * @since   3.0.0
 */
final class DynamicStyle {

	/**
	 * Collected CSS styles from rendered blocks.
	 *
	 * @since 3.0.0
	 * @var array<int, string>
	 */
	private array $styles = array();

	/**
	 * Absolute path to the CSS upload directory.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	private string $upload_dir;

	/**
	 * Public URL to the CSS upload directory.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	private string $upload_url;

	/**
	 * Constructor.
	 *
	 * Sets up the upload directory and registers rendering hooks.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_filter( 'render_block', array( $this, 'collect_block_styles' ), 10, 2 );

		if ( wp_is_block_theme() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'generate_and_enqueue_combined_css' ) );
		} else {
			add_action( 'wp_footer', array( $this, 'generate_and_enqueue_combined_css' ) );
		}

		$upload_dir       = wp_upload_dir();
		$this->upload_dir = $upload_dir['basedir'] . '/gutslider-styles/';
		$this->upload_url = $upload_dir['baseurl'] . '/gutslider-styles/';

		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}
	}

	/**
	 * Collect CSS styles from a rendered block.
	 *
	 * Inspects the block's attributes for inline styles and stores
	 * them for later combination into a single CSS file.
	 *
	 * @since 3.0.0
	 *
	 * @param string               $block_content The rendered block HTML.
	 * @param array<string, mixed> $block         The block data array.
	 * @return string The unmodified block content.
	 */
	public function collect_block_styles( string $block_content, array $block ): string {
		if ( isset( $block['blockName'] ) && str_contains( $block['blockName'], 'gutsliders/' ) ) {
			do_action( 'gutsliders_render_block', $block );

			if ( isset( $block['attrs']['blockStyle'] ) ) {
				$style = $block['attrs']['blockStyle'];

				if ( is_array( $style ) && ! empty( $style ) ) {
					$style = implode( ' ', $style );
				}

				if ( is_string( $style ) && '' !== $style ) {
					$this->styles[] = wp_strip_all_tags( $style );
				}
			}
		}

		return $block_content;
	}

	/**
	 * Generate and enqueue the combined CSS file.
	 *
	 * Combines all collected styles, minifies the result, writes it
	 * to a file in the uploads directory, and enqueues it.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function generate_and_enqueue_combined_css(): void {
		if ( empty( $this->styles ) ) {
			return;
		}

		$combined_css = implode( "\n", $this->styles );
		$minified_css = $this->minify_css( $combined_css );

		$post_id       = get_the_ID();
		$file_suffix   = $post_id ? (string) $post_id : 'archive-' . md5( wp_json_encode( array_keys( $this->styles ) ) );
		$css_file_name = 'gutslider-styles-' . $file_suffix . '.min.css';
		$css_file_path = $this->upload_dir . $css_file_name;
		$css_file_url  = $this->upload_url . $css_file_name;

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$existing_content = $wp_filesystem->exists( $css_file_path )
			? $wp_filesystem->get_contents( $css_file_path )
			: '';

		if ( $existing_content !== $minified_css ) {
			$wp_filesystem->put_contents( $css_file_path, $minified_css, FS_CHMOD_FILE );
		}

		if ( file_exists( $css_file_path ) && is_readable( $css_file_path ) ) {
			$version = (string) filemtime( $css_file_path );
		} else {
			$version = (string) time();
		}

		if ( file_exists( $css_file_path ) ) {
			wp_enqueue_style(
				'gutslider-combined-styles',
				$css_file_url,
				array(),
				$version
			);
		}
	}

	/**
	 * Minify a CSS string.
	 *
	 * Removes comments, unnecessary whitespace, and extra spaces
	 * around colons to reduce file size.
	 *
	 * @since 3.0.0
	 *
	 * @param string $css The raw CSS string.
	 * @return string The minified CSS string.
	 */
	private function minify_css( string $css ): string {
		// Remove comments.
		$css = (string) preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove space after colons.
		$css = str_replace( ': ', ':', $css );

		// Remove whitespace.
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );

		return $css;
	}
}
