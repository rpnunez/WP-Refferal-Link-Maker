<?php
/**
 * Fired during plugin activation
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class WP_Referral_Link_Maker_Activator {

    /**
     * Activate the plugin.
     *
     * Register custom post types and flush rewrite rules.
     */
    public static function activate() {
        // Register custom post types
        self::register_custom_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        self::set_default_options();
        
        // Schedule cron events
        self::schedule_cron_events();
    }
    
    /**
     * Register custom post types.
     */
    private static function register_custom_post_types() {
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-post-types.php';
        WP_Referral_Link_Maker_Post_Types::register_post_types();
    }
    
    /**
     * Set default options.
     */
    private static function set_default_options() {
        $defaults = array(
            'api_key' => '',
            'ai_engine_enabled' => false,
            'auto_update_enabled' => false,
            'cron_interval' => 'daily',
            'post_status_after_edit' => 'pending',
        );
        
        add_option( 'wp_referral_link_maker_settings', $defaults );
    }
    
    /**
     * Schedule cron events.
     */
    private static function schedule_cron_events() {
        if ( ! wp_next_scheduled( 'wp_referral_link_maker_process_posts' ) ) {
            wp_schedule_event( time(), 'daily', 'wp_referral_link_maker_process_posts' );
        }
    }
}
