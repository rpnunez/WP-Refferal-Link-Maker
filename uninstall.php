<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package    WP_Referral_Link_Maker
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Delete plugin options
 */
function wp_rlm_delete_plugin_options() {
    delete_option( 'wp_referral_link_maker_settings' );
    delete_option( 'wp_referral_link_maker_global_values' );
}

/**
 * Delete custom post types and their meta
 */
function wp_rlm_delete_custom_posts() {
    global $wpdb;

    // Get all referral link posts
    $ref_links = get_posts( array(
        'post_type'      => 'ref_link_maker',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ) );

    // Delete referral links
    foreach ( $ref_links as $post ) {
        wp_delete_post( $post->ID, true );
    }

    // Get all referral link group posts
    $ref_groups = get_posts( array(
        'post_type'      => 'ref_link_group',
        'posts_per_page' => -1,
        'post_status'    => 'any',
    ) );

    // Delete referral link groups
    foreach ( $ref_groups as $post ) {
        wp_delete_post( $post->ID, true );
    }

    // Clean up any remaining meta from regular posts
    $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wp_rlm_%'" );
}

/**
 * Clear scheduled cron events
 */
function wp_rlm_clear_scheduled_events() {
    $timestamp = wp_next_scheduled( 'wp_referral_link_maker_process_posts' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'wp_referral_link_maker_process_posts' );
    }
}

// Only delete data if user has confirmed they want to remove all data
// This can be controlled via a setting in a future version
// For now, we'll clean up everything on uninstall

wp_rlm_delete_plugin_options();
wp_rlm_delete_custom_posts();
wp_rlm_clear_scheduled_events();

// Flush rewrite rules
flush_rewrite_rules();
