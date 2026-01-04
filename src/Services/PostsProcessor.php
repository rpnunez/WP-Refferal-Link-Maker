<?php
/**
 * Posts Processing Service
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\Services;

use NunezReferralEngine\AIEngineService;

/**
 * Handle posts processing for automated referral link integration.
 *
 * This class is responsible for retrieving posts that need processing,
 * processing individual posts, and coordinating with AI Engine for content updates.
 */
class PostsProcessor {

    /**
     * Link injector service.
     *
     * @var LinkInjector
     */
    private $link_injector;

    /**
     * Constructor.
     *
     * @param LinkInjector $link_injector Link injector service.
     */
    public function __construct( LinkInjector $link_injector ) {
        $this->link_injector = $link_injector;
    }

    /**
     * Get posts that need processing.
     *
     * @return array Array of post objects.
     */
    public function get_posts_to_process() {
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

        $query = new \WP_Query( $args );
        return $query->posts;
    }

    /**
     * Process a single post with AI automation.
     *
     * @param \WP_Post $post Post object to process.
     */
    public function process_single_post( $post ) {
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
            $updated_content = $this->link_injector->apply_referral_links( $post->post_content, $referral_links );
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
    public function get_ai_processed_content( $content, $referral_links, $post_id = 0 ) {
        $ai_engine = new AIEngineService();

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
    public function get_referral_links() {
        $args = array(
            'post_type'      => 'ref_link_maker',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $query = new \WP_Query( $args );
        return $query->posts;
    }
}
