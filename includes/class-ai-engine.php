<?php
/**
 * AI Engine Integration
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Handle integration with Meow Apps AI Engine plugin.
 *
 * This class provides methods to interact with the AI Engine plugin
 * for intelligent referral link insertion.
 */
class WP_Referral_Link_Maker_AI_Engine {

    /**
     * Minimum response length threshold as percentage of original content.
     *
     * @var float
     */
    const MIN_RESPONSE_LENGTH_RATIO = 0.5;

    /**
     * Maximum content length for AI processing (characters).
     * Prevents exceeding AI model token limits.
     *
     * @var int
     */
    const MAX_CONTENT_LENGTH = 15000;

    /**
     * Check if AI Engine plugin is available.
     *
     * @return bool True if AI Engine is active and available.
     */
    public function is_available() {
        global $mwai;
        return isset( $mwai ) && is_object( $mwai );
    }

    /**
     * Intelligently insert referral links into post content using AI.
     *
     * @param string $content         Original post content.
     * @param array  $referral_links  Array of referral link objects.
     * @return string|WP_Error Modified content with referral links or error.
     */
    public function insert_referral_links( $content, $referral_links ) {
        if ( ! $this->is_available() ) {
            return new WP_Error( 'ai_engine_unavailable', __( 'AI Engine plugin is not available.', 'wp-referral-link-maker' ) );
        }

        if ( empty( $referral_links ) ) {
            return $content;
        }

        // Prepare links data for AI context
        $links_info = array();
        foreach ( $referral_links as $link ) {
            $keyword = get_post_meta( $link->ID, '_ref_link_keyword', true );
            $url = get_post_meta( $link->ID, '_ref_link_url', true );
            $ai_context = get_post_meta( $link->ID, '_ref_link_ai_context', true );
            $max_insertions = get_post_meta( $link->ID, '_ref_link_max_insertions', true );
            
            if ( empty( $keyword ) || empty( $url ) ) {
                continue;
            }

            $links_info[] = array(
                'keyword' => $keyword,
                'url' => $url,
                'context' => $ai_context,
                'max_insertions' => ! empty( $max_insertions ) ? intval( $max_insertions ) : 1,
            );
        }

        if ( empty( $links_info ) ) {
            return $content;
        }

        // Validate content length to prevent token limit issues
        if ( strlen( $content ) > self::MAX_CONTENT_LENGTH ) {
            return new WP_Error( 'ai_engine_content_too_long', __( 'Post content exceeds maximum length for AI processing.', 'wp-referral-link-maker' ) );
        }

        // Build AI prompt
        $prompt = $this->build_ai_prompt( $content, $links_info );

        // Query AI Engine
        try {
            global $mwai;
            $response = $mwai->simpleTextQuery( $prompt );

            if ( empty( $response ) ) {
                return new WP_Error( 'ai_engine_empty_response', __( 'AI Engine returned an empty response.', 'wp-referral-link-maker' ) );
            }

            // Validate and extract the modified content from AI response
            $modified_content = $this->extract_content_from_response( $response, $content );
            
            // Check if validation failed
            if ( is_wp_error( $modified_content ) ) {
                return $modified_content;
            }
            
            // Sanitize the AI response to prevent XSS attacks
            $modified_content = $this->sanitize_ai_response( $modified_content );

            return $modified_content;
        } catch ( Exception $e ) {
            return new WP_Error( 'ai_engine_error', sprintf( __( 'AI Engine error: %s', 'wp-referral-link-maker' ), $e->getMessage() ) );
        }
    }

    /**
     * Build AI prompt for intelligent link insertion.
     *
     * @param string $content    Original post content.
     * @param array  $links_info Array of link information.
     * @return string AI prompt.
     */
    private function build_ai_prompt( $content, $links_info ) {
        // Get link attributes from settings
        $settings = get_option( 'wp_referral_link_maker_settings', array() );
        $link_rel = isset( $settings['link_rel_attribute'] ) ? $settings['link_rel_attribute'] : 'nofollow';
        
        $links_description = '';
        foreach ( $links_info as $link ) {
            $links_description .= sprintf(
                "- Keyword: '%s', URL: '%s', Max insertions: %d",
                $link['keyword'],
                esc_url_raw( $link['url'] ),
                $link['max_insertions']
            );
            if ( ! empty( $link['context'] ) ) {
                $links_description .= sprintf( ", Context: %s", $link['context'] );
            }
            $links_description .= "\n";
        }

        $prompt = "You are a content editor tasked with intelligently inserting referral links into blog post content. Your goal is to add links naturally where they make sense contextually.\n\n";
        $prompt .= "INSTRUCTIONS:\n";
        $prompt .= "1. Read the provided content carefully\n";
        $prompt .= "2. For each keyword provided, find natural places to insert the link\n";
        $prompt .= "3. Insert links only where they are contextually relevant\n";
        $prompt .= "4. Do not force links where they don't fit naturally\n";
        $prompt .= "5. Respect the maximum insertion count for each keyword\n";
        
        // Add rel attribute instruction only if configured
        if ( ! empty( $link_rel ) ) {
            $prompt .= sprintf( "6. Use HTML anchor tags with rel=\"%s\" attribute\n", $link_rel );
        } else {
            $prompt .= "6. Use HTML anchor tags without rel attribute\n";
        }
        
        $prompt .= "7. Return ONLY the modified HTML content, no explanations\n";
        $prompt .= "8. Preserve all existing HTML formatting and structure\n\n";
        $prompt .= "REFERRAL LINKS TO INSERT:\n";
        $prompt .= $links_description . "\n";
        $prompt .= "ORIGINAL CONTENT:\n";
        $prompt .= $this->escape_prompt_content( $content ) . "\n\n";
        $prompt .= "MODIFIED CONTENT (return only the HTML with links inserted):";

        return $prompt;
    }

    /**
     * Escape content for AI prompt to prevent prompt injection.
     *
     * @param string $content Content to escape.
     * @return string Escaped content.
     */
    private function escape_prompt_content( $content ) {
        // Replace potential prompt injection keywords with placeholders
        $replacements = array(
            'INSTRUCTIONS:' => '[INSTRUCTIONS-TEXT]',
            'MODIFIED CONTENT:' => '[MODIFIED-CONTENT-TEXT]',
            'ORIGINAL CONTENT:' => '[ORIGINAL-CONTENT-TEXT]',
            'REFERRAL LINKS TO INSERT:' => '[REFERRAL-LINKS-TEXT]',
        );
        
        return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
    }

    /**
     * Sanitize AI response to prevent malicious content.
     *
     * @param string $content Content from AI response.
     * @return string Sanitized content.
     */
    private function sanitize_ai_response( $content ) {
        // Start from WordPress's default allowed HTML for post content
        $allowed_html = wp_kses_allowed_html( 'post' );

        // Ensure that <a> tags always allow the "rel" attribute so that
        // AI-inserted links can use configurations like "sponsored" or
        // combined values such as "nofollow sponsored" without being stripped
        if ( isset( $allowed_html['a'] ) && is_array( $allowed_html['a'] ) ) {
            $allowed_html['a']['rel'] = true;
        }

        // Use wp_kses with the customized allowed HTML to sanitize the content
        return wp_kses( $content, $allowed_html );
    }

    /**
     * Extract content from AI response.
     *
     * @param string $response AI response.
     * @param string $original Original content as fallback.
     * @return string|WP_Error Extracted content or error on invalid AI response.
     */
    private function extract_content_from_response( $response, $original ) {
        // The AI should return the content directly
        // Trim any extra whitespace or formatting
        $response = trim( $response );

        // If response is empty, return an error
        if ( empty( $response ) ) {
            return new WP_Error(
                'ai_engine_invalid_response',
                __( 'AI Engine returned an empty response.', 'wp-referral-link-maker' )
            );
        }

        // Compare stripped text content to handle HTML compression scenarios
        $original_text = wp_strip_all_tags( $original );
        $response_text = wp_strip_all_tags( $response );
        
        $min_text_length = intval( strlen( $original_text ) * self::MIN_RESPONSE_LENGTH_RATIO );
        
        if ( strlen( $response_text ) < $min_text_length ) {
            return new WP_Error(
                'ai_engine_invalid_response',
                __( 'AI Engine returned a response that is too short.', 'wp-referral-link-maker' )
            );
        }

        return $response;
    }
}
