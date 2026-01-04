<?php
/**
 * Block Editor Integration
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Handle block editor (Gutenberg) integration.
 *
 * This class provides functionality for:
 * - Previewing AI-suggested referral links in the block editor
 * - Suggesting links during manual post creation
 */
class WP_Referral_Link_Maker_Block_Editor {

    /**
     * Initialize block editor integration.
     */
    public function __construct() {
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
        add_action( 'wp_ajax_wp_rlm_suggest_links', array( $this, 'ajax_suggest_links' ) );
        add_action( 'wp_ajax_wp_rlm_preview_links', array( $this, 'ajax_preview_links' ) );
    }

    /**
     * Enqueue block editor assets (JavaScript and CSS).
     */
    public function enqueue_block_editor_assets() {
        // Only enqueue on post edit screens
        $screen = get_current_screen();
        if ( ! $screen || $screen->base !== 'post' ) {
            return;
        }

        // Enqueue block editor JavaScript
        wp_enqueue_script(
            'wp-rlm-block-editor',
            WP_REFERRAL_LINK_MAKER_PLUGIN_URL . 'admin/js/block-editor.js',
            array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
            WP_REFERRAL_LINK_MAKER_VERSION,
            true
        );

        // Enqueue block editor CSS
        wp_enqueue_style(
            'wp-rlm-block-editor',
            WP_REFERRAL_LINK_MAKER_PLUGIN_URL . 'admin/css/block-editor.css',
            array(),
            WP_REFERRAL_LINK_MAKER_VERSION
        );

        // Localize script with data
        wp_localize_script(
            'wp-rlm-block-editor',
            'wpRlmBlockEditor',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wp_rlm_block_editor' ),
                'i18n' => array(
                    'title' => __( 'Referral Links', 'wp-referral-link-maker' ),
                    'suggestLinks' => __( 'Suggest Links', 'wp-referral-link-maker' ),
                    'previewLinks' => __( 'Preview with Links', 'wp-referral-link-maker' ),
                    'loading' => __( 'Loading...', 'wp-referral-link-maker' ),
                    'noLinks' => __( 'No referral links available.', 'wp-referral-link-maker' ),
                    'error' => __( 'An error occurred. Please try again.', 'wp-referral-link-maker' ),
                    'suggestedLinks' => __( 'Suggested Links', 'wp-referral-link-maker' ),
                    'preview' => __( 'Preview', 'wp-referral-link-maker' ),
                    'closePreview' => __( 'Close Preview', 'wp-referral-link-maker' ),
                    'applyLinks' => __( 'Apply Links', 'wp-referral-link-maker' ),
                    'aiNotAvailable' => __( 'AI Engine is not available.', 'wp-referral-link-maker' ),
                    'contentTooLong' => __( 'Content is too long for AI processing.', 'wp-referral-link-maker' ),
                ),
            )
        );
    }

    /**
     * AJAX handler for suggesting links.
     *
     * Returns a list of available referral links that could be inserted into the post.
     */
    public function ajax_suggest_links() {
        // Verify nonce
        check_ajax_referer( 'wp_rlm_block_editor', 'nonce' );

        // Check user permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wp-referral-link-maker' ) ) );
        }

        // Get post content
        $content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
        
        if ( empty( $content ) ) {
            wp_send_json_error( array( 'message' => __( 'No content provided.', 'wp-referral-link-maker' ) ) );
        }

        // Get all active referral links
        $referral_links = $this->get_active_referral_links();

        if ( empty( $referral_links ) ) {
            wp_send_json_error( array( 'message' => __( 'No referral links available.', 'wp-referral-link-maker' ) ) );
        }

        // Analyze content and suggest relevant links
        $suggested_links = $this->analyze_and_suggest_links( $content, $referral_links );

        wp_send_json_success( array( 'links' => $suggested_links ) );
    }

    /**
     * AJAX handler for previewing links in content.
     *
     * Returns the content with AI-suggested referral links inserted.
     */
    public function ajax_preview_links() {
        // Verify nonce
        check_ajax_referer( 'wp_rlm_block_editor', 'nonce' );

        // Check user permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wp-referral-link-maker' ) ) );
        }

        // Get post content
        $content = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
        
        if ( empty( $content ) ) {
            wp_send_json_error( array( 'message' => __( 'No content provided.', 'wp-referral-link-maker' ) ) );
        }

        // Get all active referral links
        $referral_links = $this->get_active_referral_links();

        if ( empty( $referral_links ) ) {
            wp_send_json_error( array( 'message' => __( 'No referral links available.', 'wp-referral-link-maker' ) ) );
        }

        // Process content with AI or fallback
        $processed_content = $this->process_content_with_links( $content, $referral_links );

        if ( is_wp_error( $processed_content ) ) {
            wp_send_json_error( array( 
                'message' => $processed_content->get_error_message(),
                'code' => $processed_content->get_error_code(),
            ) );
        }

        wp_send_json_success( array( 'content' => $processed_content ) );
    }

    /**
     * Get active referral links.
     *
     * @return array Array of referral link objects.
     */
    private function get_active_referral_links() {
        $args = array(
            'post_type'      => 'ref_link_maker',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_ref_link_ai_enabled',
                    'value'   => '1',
                    'compare' => '='
                )
            )
        );

        $query = new WP_Query( $args );
        return $query->posts;
    }

    /**
     * Analyze content and suggest relevant links.
     *
     * @param string $content         Post content.
     * @param array  $referral_links  Array of referral link objects.
     * @return array Array of suggested links with metadata.
     */
    private function analyze_and_suggest_links( $content, $referral_links ) {
        $suggestions = array();

        foreach ( $referral_links as $link ) {
            $keyword = get_post_meta( $link->ID, '_ref_link_keyword', true );
            $url = get_post_meta( $link->ID, '_ref_link_url', true );
            $ai_context = get_post_meta( $link->ID, '_ref_link_ai_context', true );
            $max_insertions = get_post_meta( $link->ID, '_ref_link_max_insertions', true );

            if ( empty( $keyword ) || empty( $url ) ) {
                continue;
            }

            // Check if keyword appears in content (case-insensitive)
            $occurrences = substr_count( strtolower( $content ), strtolower( $keyword ) );

            if ( $occurrences > 0 ) {
                $suggestions[] = array(
                    'id' => $link->ID,
                    'keyword' => $keyword,
                    'url' => $url,
                    'context' => $ai_context,
                    'maxInsertions' => ! empty( $max_insertions ) ? intval( $max_insertions ) : 1,
                    'occurrences' => $occurrences,
                    'title' => $link->post_title,
                );
            }
        }

        // Sort by number of occurrences (descending)
        usort( $suggestions, function( $a, $b ) {
            return $b['occurrences'] - $a['occurrences'];
        });

        return $suggestions;
    }

    /**
     * Process content with referral links.
     *
     * @param string $content         Post content.
     * @param array  $referral_links  Array of referral link objects.
     * @return string|WP_Error Processed content or error.
     */
    private function process_content_with_links( $content, $referral_links ) {
        // Check if AI Engine is available
        $settings = get_option( 'wp_referral_link_maker_settings', array() );
        
        if ( ! empty( $settings['ai_engine_enabled'] ) ) {
            // Try AI Engine processing
            if ( ! class_exists( 'WP_Referral_Link_Maker_AI_Engine' ) ) {
                require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-ai-engine.php';
            }

            $ai_engine = new WP_Referral_Link_Maker_AI_Engine();

            if ( $ai_engine->is_available() ) {
                $processed_content = $ai_engine->insert_referral_links( $content, $referral_links );
                
                // If AI processing succeeded, return it
                if ( ! is_wp_error( $processed_content ) ) {
                    return $processed_content;
                }
                // Otherwise, fall back to simple processing
            }
        }

        // Fallback to simple keyword replacement
        return $this->apply_referral_links_fallback( $content, $referral_links );
    }

    /**
     * Apply referral links to content (fallback method).
     *
     * @param string $content Original post content.
     * @param array  $links   Array of referral link objects.
     * @return string Modified content with referral links.
     */
    private function apply_referral_links_fallback( $content, $links ) {
        // Get link attributes from settings
        $settings = get_option( 'wp_referral_link_maker_settings', array() );
        $link_rel = isset( $settings['link_rel_attribute'] ) ? $settings['link_rel_attribute'] : 'nofollow';
        
        // Validate and sanitize the rel attribute
        $link_rel = wp_referral_link_maker_sanitize_rel_attribute( $link_rel );

        foreach ( $links as $link ) {
            $keyword = get_post_meta( $link->ID, '_ref_link_keyword', true );
            $url = get_post_meta( $link->ID, '_ref_link_url', true );
            $max_insertions = get_post_meta( $link->ID, '_ref_link_max_insertions', true );

            if ( empty( $keyword ) || empty( $url ) ) {
                continue;
            }

            $max = ! empty( $max_insertions ) ? intval( $max_insertions ) : 1;

            // Build link HTML with rel attribute if configured
            if ( ! empty( $link_rel ) ) {
                $link_html = sprintf( '<a href="%s" rel="%s">%s</a>', esc_url( $url ), esc_attr( $link_rel ), esc_html( $keyword ) );
            } else {
                $link_html = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $keyword ) );
            }
            
            // Replace occurrences of keyword with link (up to max)
            $content = preg_replace(
                '/\b' . preg_quote( $keyword, '/' ) . '\b/',
                $link_html,
                $content,
                $max
            );
        }

        return $content;
    }
}
