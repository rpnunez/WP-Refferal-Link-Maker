<?php
/**
 * Cron job handling
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

use NunezReferralEngine\Services\AIEngine;

/**
 * Handle cron jobs for automated processing.
 *
 * This class defines cron schedules and processes posts with AI automation.
 */
class Cron {

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
        $posts = $this->get_posts_to_process();

        if ( empty( $posts ) ) {
            return;
        }

        // Process each post
        foreach ( $posts as $post ) {
            $this->process_single_post( $post );
        }
    }

    /**
     * Get posts that need processing.
     *
     * @return array Array of post objects.
     */
    private function get_posts_to_process() {
        // Get posts marked for AI processing
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'meta_query'     => array(
                array(
                    'key'     => '_wp_rlm_process_ai',
                    'value'   => '1',
                    'compare' => '='
                ),
                array(
                    'key'     => '_wp_rlm_processed',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        $query = new WP_Query( $args );
        return $query->posts;
    }

    /**
     * Process a single post with AI automation.
     *
     * @param WP_Post $post Post object to process.
     */
    private function process_single_post( $post ) {
        // Get plugin settings
        $settings = get_option( 'wp_referral_link_maker_settings', array() );

        // Get referral links
        $referral_links = $this->get_referral_links();

        if ( empty( $referral_links ) ) {
            return;
        }

        // Try using AI Engine for intelligent link insertion
        $updated_content = $this->get_ai_processed_content( $post->post_content, $referral_links, $post->ID );
        
        // If AI processing failed, use fallback
        if ( is_null( $updated_content ) ) {
            $updated_content = $this->apply_referral_links( $post->post_content, $referral_links );
        }

        // Update post with new content and set to pending
        $post_status = ! empty( $settings['post_status_after_edit'] ) ? $settings['post_status_after_edit'] : 'pending';

        $post_data = array(
            'ID'           => $post->ID,
            'post_content' => $updated_content,
            'post_status'  => $post_status,
        );

        wp_update_post( $post_data );

        // Mark as processed
        update_post_meta( $post->ID, '_wp_rlm_processed', current_time( 'mysql' ) );
        delete_post_meta( $post->ID, '_wp_rlm_process_ai' );

        // Log the action
        do_action( 'wp_referral_link_maker_post_processed', $post->ID );
    }

    /**
     * Get AI processed content with referral links.
     *
     * @param string $content         Original post content.
     * @param array  $referral_links  Array of referral link objects.
     * @param int    $post_id         Post ID for logging purposes.
     * @return string|null Processed content or null if AI processing failed.
     */
    private function get_ai_processed_content( $content, $referral_links, $post_id = 0 ) {
        $ai_engine = new AIEngine();

        if ( ! $ai_engine->is_available() ) {
            // Log that AI Engine is not available
            error_log( sprintf( 'WP Referral Link Maker: AI Engine not available for post %d. Using fallback method.', $post_id ) );
            return null;
        }

        $updated_content = $ai_engine->insert_referral_links( $content, $referral_links );
        
        // Check if AI Engine returned an error
        if ( is_wp_error( $updated_content ) ) {
            // Log the error for troubleshooting
            error_log( sprintf( 'WP Referral Link Maker: AI Engine error for post %d: %s. Using fallback method.', $post_id, $updated_content->get_error_message() ) );
            
            // Store failure information in post meta
            update_post_meta( $post_id, '_wp_rlm_ai_failure', array(
                'time' => current_time( 'mysql' ),
                'error' => $updated_content->get_error_code(),
                'message' => $updated_content->get_error_message(),
            ) );
            
            return null;
        }

        // Clear any previous failure meta
        delete_post_meta( $post_id, '_wp_rlm_ai_failure' );

        return $updated_content;
    }

    /**
     * Get active referral links.
     *
     * @return array Array of referral link objects.
     */
    private function get_referral_links() {
        $args = array(
            'post_type'      => 'ref_link_maker',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );
        return $query->posts;
    }

    /**
     * Apply referral links to post content.
     *
     * This is a fallback method used when AI Engine is not available.
     *
     * @param string $content Original post content.
     * @param array  $links   Array of referral link objects.
     * @return string Modified content with referral links.
     */
    private function apply_referral_links( $content, $links ) {
        // Ensure $links is iterable before processing to avoid fatal errors
        if ( ! is_array( $links ) ) {
            return $content;
        }
        
        // Simple keyword replacement fallback
        
        // Get link attributes from settings
        $settings = get_option( 'wp_referral_link_maker_settings', array() );
        $link_rel = isset( $settings['link_rel_attribute'] ) ? $settings['link_rel_attribute'] : 'nofollow';
        
        // Validate and sanitize the rel attribute
        $link_rel = wp_referral_link_maker_sanitize_rel_attribute( $link_rel );

        foreach ( $links as $link ) {
            $keyword = get_post_meta( $link->ID, '_ref_link_keyword', true );
            $url = get_post_meta( $link->ID, '_ref_link_url', true );

            if ( empty( $keyword ) || empty( $url ) ) {
                continue;
            }

            // Build link HTML with rel attribute if configured
            if ( ! empty( $link_rel ) ) {
                $link_html = sprintf( '<a href="%s" rel="%s">%s</a>', esc_url( $url ), esc_attr( $link_rel ), esc_html( $keyword ) );
            } else {
                $link_html = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $keyword ) );
            }
            
            // Replace first occurrence of keyword with link
            $content = preg_replace(
                '/\b' . preg_quote( $keyword, '/' ) . '\b/',
                $link_html,
                $content,
                1
            );
        }

        return $content;
    }
}
