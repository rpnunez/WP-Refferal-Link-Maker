<?php
/**
 * Plugin Name: WP Referral Link Maker
 * Plugin URI: https://github.com/rpnunez/WP-Refferal-Link-Maker
 * Description: A WordPress plugin that integrates referral links into existing posts using AI capabilities from Meow Apps AI Engine plugin.
 * Version: 1.0.1
 * Author: rpnunez
 * Author URI: https://github.com/rpnunez
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-referral-link-maker
 * Domain Path: /languages
 *
 * @package NunezReferralEngine
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'NRE_VERSION', '1.0.1' );
define( 'NRE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NRE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Require Composer autoloader.
 */
$autoload_file = NRE_PLUGIN_DIR . 'vendor/autoload.php';
if ( ! file_exists( $autoload_file ) ) {
    wp_die(
        esc_html__( 'WP Referral Link Maker: Composer autoloader not found. The plugin installation may be incomplete or corrupted.', 'wp-referral-link-maker' ),
        esc_html__( 'Plugin Dependency Error', 'wp-referral-link-maker' ),
        array( 'back_link' => true )
    );
}
require_once $autoload_file;

use NunezReferralEngine\Plugin;
use NunezReferralEngine\PluginManager;

/**
 * Get allowed rel attribute values for referral links.
 *
 * @return array Allowed rel attribute values.
 */
function wp_referral_link_maker_get_allowed_rel_values() {
    return array( '', 'nofollow', 'sponsored', 'nofollow sponsored' );
}

/**
 * Validate and sanitize link rel attribute value.
 *
 * @param string $value Value to validate.
 * @return string Sanitized value or empty string if invalid.
 */
function wp_referral_link_maker_sanitize_rel_attribute( $value ) {
    $allowed = wp_referral_link_maker_get_allowed_rel_values();
    $value = sanitize_text_field( $value );
    return in_array( $value, $allowed, true ) ? $value : '';
}

/**
 * The code that runs during plugin activation.
 */
function activate_wp_referral_link_maker() {
    PluginManager::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_referral_link_maker() {
    PluginManager::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_referral_link_maker' );
register_deactivation_hook( __FILE__, 'deactivate_wp_referral_link_maker' );

/**
 * Begins execution of the plugin.
 */
function run_wp_referral_link_maker() {
    new Plugin();
}

run_wp_referral_link_maker();
