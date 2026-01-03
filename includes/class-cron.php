<?php
/**
 * Cron job handling
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Handle cron jobs for automated processing.
 *
 * This class defines cron schedules and processes posts with AI automation.
 */
class WP_Referral_Link_Maker_Cron {

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
        $updated_content = $this->get_ai_processed_content( $post->post_content, $referral_links );
        
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
     * @return string|null Processed content or null if AI processing failed.
     */
    private function get_ai_processed_content( $content, $referral_links ) {
        // Check if AI Engine class is already loaded
        if ( ! class_exists( 'WP_Referral_Link_Maker_AI_Engine' ) ) {
            require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-ai-engine.php';
        }

        $ai_engine = new WP_Referral_Link_Maker_AI_Engine();

        if ( ! $ai_engine->is_available() ) {
            return null;
        }

        $updated_content = $ai_engine->insert_referral_links( $content, $referral_links );
        
        // Check if AI Engine returned an error
        if ( is_wp_error( $updated_content ) ) {
            return null;
        }

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
        // Simple keyword replacement fallback

        foreach ( $links as $link ) {
            $keyword = get_post_meta( $link->ID, '_ref_link_keyword', true );
            $url = get_post_meta( $link->ID, '_ref_link_url', true );

            if ( empty( $keyword ) || empty( $url ) ) {
                continue;
            }

            // Replace first occurrence of keyword with link
            $link_html = sprintf( '<a href="%s" rel="nofollow">%s</a>', esc_url( $url ), esc_html( $keyword ) );
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
