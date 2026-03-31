<?php
/**
 * Plugin Name: GF Tooltip Lite
 * Plugin URI:  https://github.com/JRMaddren/GFTooltipLite.git
 * Description: Adds hover tooltips to Gravity Forms field labels. Configure per-field inside the form editor.
 * Version:     1.0.0
 * Author:      Jesse M
 * License:     GPL-2.0+
 * Text Domain: gf-tooltip-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GFTL_VERSION', '1.0.0' );
define( 'GFTL_DIR', plugin_dir_path( __FILE__ ) );
define( 'GFTL_URL', plugin_dir_url( __FILE__ ) );

/**
 * check Gravity Forms is active
 */
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'GFForms' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>'
                . esc_html__( 'GF Tooltip Lite requires Gravity Forms to be installed and active.', 'gf-tooltip-lite' )
                . '</p></div>';
        } );
        return;
    }

    // load all feature files
    require_once GFTL_DIR . 'includes/field-setting.php';
    require_once GFTL_DIR . 'includes/frontend.php';
} );
