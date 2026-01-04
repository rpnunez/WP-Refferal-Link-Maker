<?php
/**
 * Cron job handling
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

use NunezReferralEngine\Services\PostsProcessor;
use NunezReferralEngine\Services\LinkInjector;

/**
 * Handle cron jobs for automated processing.
 *
 * This class defines cron schedules and coordinates post processing.
 */
class Cron {

    /**
     * Posts processor service.
     *
     * @var PostsProcessor
     */
    private $posts_processor;

    /**
     * Constructor.
     *
     * @param PostsProcessor|null $posts_processor Optional posts processor service.
     */
    public function __construct( PostsProcessor $posts_processor = null ) {
        if ( null === $posts_processor ) {
            $link_injector = new LinkInjector();
            $posts_processor = new PostsProcessor( $link_injector );
        }
        $this->posts_processor = $posts_processor;
    }

    /**
     * Add custom cron intervals.
     *
     * @param array $schedules Existing cron schedules.
     * @return array Modified cron schedules.
     */
    public function add_custom_cron_intervals( $schedules ) {
        // Add hourly interval
        if ( ! isset( $schedules['hourly'] ) ) {
            $schedules['hourly'] = array(
                'interval' => 3600,
                'display'  => __( 'Once Hourly', 'wp-referral-link-maker' )
            );
        }

        // Add twice daily interval
        $schedules['twice_daily'] = array(
            'interval' => 43200, // 12 hours
            'display'  => __( 'Twice Daily', 'wp-referral-link-maker' )
        );

        // Add weekly interval
        $schedules['weekly'] = array(
            'interval' => 604800, // 7 days
            'display'  => __( 'Once Weekly', 'wp-referral-link-maker' )
        );

        return $schedules;
    }

    /**
     * Process posts with AI automation.
     *
     * This method is triggered by cron and processes posts based on plugin settings.
     */
    public function process_posts() {
        // Get plugin settings
        $settings = get_option( 'wp_referral_link_maker_settings', array() );

        // Check if auto update is enabled
        if ( empty( $settings['auto_update_enabled'] ) ) {
            return;
        }

        // Check if AI Engine is enabled
        if ( empty( $settings['ai_engine_enabled'] ) ) {
            return;
        }

        // Get posts that need processing
        $posts = $this->posts_processor->get_posts_to_process();

        if ( empty( $posts ) ) {
            return;
        }

        // Process each post
        foreach ( $posts as $post ) {
            $this->posts_processor->process_single_post( $post );
        }
    }
}
