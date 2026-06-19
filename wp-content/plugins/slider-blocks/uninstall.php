<?php
/**
 * Uninstall script for GutSliderBlocks
 *
 * @package GutSliderBlocks
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load the main plugin file to access the cleanup method.
require_once plugin_dir_path( __FILE__ ) . 'slider-blocks.php';

// Cleanup the plugin data.
GutSlider\Plugin::cleanup();
