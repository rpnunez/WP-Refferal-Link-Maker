<?php
/**
 * Fired during plugin deactivation
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Deactivator {

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
}
