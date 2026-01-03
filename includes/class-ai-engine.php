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

        // Build AI prompt
        $prompt = $this->build_ai_prompt( $content, $links_info );

        // Query AI Engine
        try {
            global $mwai;
            $response = $mwai->simpleTextQuery( $prompt );

            if ( empty( $response ) ) {
                return new WP_Error( 'ai_engine_empty_response', __( 'AI Engine returned an empty response.', 'wp-referral-link-maker' ) );
            }

            // Extract the modified content from AI response
            $modified_content = $this->extract_content_from_response( $response, $content );

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
                $link['url'],
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
            $prompt .= sprintf( "6. Use HTML anchor tags with rel=\"%s\" attribute\n", esc_attr( $link_rel ) );
        } else {
            $prompt .= "6. Use HTML anchor tags without rel attribute\n";
        }
        
        $prompt .= "7. Return ONLY the modified HTML content, no explanations\n";
        $prompt .= "8. Preserve all existing HTML formatting and structure\n\n";
        $prompt .= "REFERRAL LINKS TO INSERT:\n";
        $prompt .= $links_description . "\n";
        $prompt .= "ORIGINAL CONTENT:\n";
        $prompt .= $content . "\n\n";
        $prompt .= "MODIFIED CONTENT (return only the HTML with links inserted):";

        return $prompt;
    }

    /**
     * Extract content from AI response.
     *
     * @param string $response AI response.
     * @param string $original Original content as fallback.
     * @return string Extracted content.
     */
    private function extract_content_from_response( $response, $original ) {
        // The AI should return the content directly
        // Trim any extra whitespace or formatting
        $response = trim( $response );

        // If response is empty or significantly shorter than original, return original
        $min_length = intval( strlen( $original ) * self::MIN_RESPONSE_LENGTH_RATIO );
        if ( empty( $response ) || strlen( $response ) < $min_length ) {
            return $original;
        }

        return $response;
    }
}
