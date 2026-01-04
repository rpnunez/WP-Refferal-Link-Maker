<?php
/**
 * Plugin activation and deactivation manager
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

use NunezReferralEngine\PostTypes\LinkGroup;
use NunezReferralEngine\PostTypes\LinkMaker;

/**
 * Handles plugin activation and deactivation.
 *
 * This class defines all code necessary to run during the plugin's
 * activation and deactivation.
 */
class PluginManager {

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
     * Deactivate the plugin.
     *
     * Clear scheduled cron events.
     */
    public static function deactivate() {
        // Clear scheduled cron events
        $timestamp = wp_next_scheduled( 'wp_referral_link_maker_process_posts' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'wp_referral_link_maker_process_posts' );
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Register custom post types.
     */
    private static function register_custom_post_types() {
        LinkGroup::register();
        LinkMaker::register();
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
