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
        
        // Create analytics table
        self::create_analytics_table();
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
    
    /**
     * Create analytics table.
     */
    private static function create_analytics_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_rlm_analytics';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            referral_link_id bigint(20) NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            click_time datetime NOT NULL,
            user_ip varchar(100) NOT NULL DEFAULT '',
            user_agent text NOT NULL,
            referrer_url text NOT NULL,
            PRIMARY KEY  (id),
            KEY referral_link_id (referral_link_id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY click_time (click_time)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
