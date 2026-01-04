<?php
/**
 * AI Engine Integration
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

/**
 * Handle integration with Meow Apps AI Engine plugin.
 *
 * This class provides methods to interact with the AI Engine plugin
 * for intelligent referral link insertion.
 */
class AIEngine {

    /**
     * Minimum response length threshold as percentage of original content.
     *
     * @var float
     */
    const MIN_RESPONSE_LENGTH_RATIO = 0.5;

    /**
     * Chunk size for splitting content (characters).
     *
     * @var int
     */
    const CHUNK_SIZE = 2000;

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
            return new \WP_Error( 'ai_engine_unavailable', __( 'AI Engine plugin is not available.', 'wp-referral-link-maker' ) );
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

        return $this->process_content_in_chunks( $content, $links_info );
    }

    /**
     * Process content in chunks to handle long posts and improve reliability.
     *
     * @param string $content    Original post content.
     * @param array  $links_info Array of link information.
     * @return string|WP_Error Modified content or error.
     */
    private function process_content_in_chunks( $content, $links_info ) {
        // Split content into chunks respecting HTML structure
        $chunks = $this->split_content_smartly( $content );
        $modified_chunks = array();
        $errors = array();

        foreach ( $chunks as $chunk ) {
            // If chunk is too short or just whitespace, skip processing
            if ( strlen( trim( $chunk ) ) < 50 ) {
                $modified_chunks[] = $chunk;
                continue;
            }

            // Process the chunk
            $modified_chunk = $this->process_single_chunk( $chunk, $links_info );

            if ( is_wp_error( $modified_chunk ) ) {
                // Log error but keep original content to avoid breaking the post
                $errors[] = $modified_chunk->get_error_message();
                $modified_chunks[] = $chunk;
            } else {
                $modified_chunks[] = $modified_chunk;
            }
        }

        return implode( '', $modified_chunks );
    }

    /**
     * Process a single chunk of content using AI.
     *
     * @param string $chunk      Content chunk.
     * @param array  $links_info Link info.
     * @return string|WP_Error   Modified chunk or error.
     */
    private function process_single_chunk( $chunk, $links_info ) {
        // Build instructions and message
        $instructions = $this->build_ai_instructions( $links_info );
        $message = "Here is the content section to process:\n\n" . $this->escape_prompt_content( $chunk );

        try {
            global $mwai_core;

            // Verify $mwai_core is available
            if ( ! isset( $mwai_core ) || ! is_object( $mwai_core ) ) {
                return new \WP_Error( 'ai_engine_core_missing', 'AI Engine core is not available' );
            }

            // Use Meow_MWAI_Query_Text for better control
            if ( class_exists( 'Meow_MWAI_Query_Text' ) ) {
                $query = new Meow_MWAI_Query_Text( $message );
                $query->set_instructions( $instructions );
                $query->set_temperature( 0.2 ); // Smart default: Low temp for precision

                // Run the query
                $reply = $mwai_core->run_query( $query );
                
                // Verify reply is valid
                if ( ! is_object( $reply ) || ! property_exists( $reply, 'result' ) ) {
                    return new \WP_Error( 'ai_engine_invalid_reply', 'AI Engine returned an invalid reply' );
                }
                
                $response = $reply->result;
            } else {
                // Fallback (though unlikely if checked is_available)
                return new \WP_Error( 'ai_engine_class_missing', 'Meow_MWAI_Query_Text class missing' );
            }

            if ( empty( $response ) ) {
                return new \WP_Error( 'ai_engine_empty_response', __( 'AI Engine returned an empty response.', 'wp-referral-link-maker' ) );
            }

            // Validate and extract
            $modified_content = $this->extract_content_from_response( $response, $chunk );

            if ( is_wp_error( $modified_content ) ) {
                return $modified_content;
            }

            return $this->sanitize_ai_response( $modified_content );

        } catch ( \Exception $e ) {
            return new \WP_Error( 'ai_engine_error', $e->getMessage() );
        }
    }

    /**
     * Split content into chunks based on paragraphs or newlines.
     *
     * @param string $content Full content.
     * @return array Chunks.
     */
    private function split_content_smartly( $content ) {
        $chunks = array();
        $current_chunk = '';

        // Split by closing paragraph tag or double newline to preserve block structure
        // Using a positive lookbehind to keep the delimiter in the previous chunk is tricky with split
        // So we split and re-append.

        // Regex split: keep delimiters
        // Split by </p> or \n\n
        $parts = preg_split( '/(<\/p>|\n\n)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        foreach ( $parts as $part ) {
            // Append part to current chunk
            $current_chunk .= $part;

            // If current chunk exceeds size AND we are at a safe boundary (end of p or newline)
            // The split logic above puts the delimiter as a separate part usually?
            // Actually, PREG_SPLIT_DELIM_CAPTURE captures the delimiter.
            // So we might get ["Text...", "</p>", "Next...", "\n\n"]

            // If the part is just a delimiter, strictly speaking we are at a boundary.
            // But we want to check length accumulated so far.

            if ( strlen( $current_chunk ) >= self::CHUNK_SIZE ) {
                // Only break if we just added a delimiter to ensure valid HTML fragments
                if ( preg_match( '/(<\/p>|\n\n)$/i', $current_chunk ) ) {
                    $chunks[] = $current_chunk;
                    $current_chunk = '';
                }
            }
        }

        if ( ! empty( $current_chunk ) ) {
            $chunks[] = $current_chunk;
        }

        return $chunks;
    }

    /**
     * Build AI instructions (System Prompt).
     *
     * @param array $links_info Array of link information.
     * @return string Instructions.
     */
    private function build_ai_instructions( $links_info ) {
        $settings = get_option( 'wp_referral_link_maker_settings', array() );
        $link_rel = isset( $settings['link_rel_attribute'] ) ? $settings['link_rel_attribute'] : 'nofollow';
        $global_context = isset( $settings['global_ai_context'] ) ? $settings['global_ai_context'] : '';
        
        // Validate rel
        $link_rel = wp_referral_link_maker_sanitize_rel_attribute( $link_rel );

        $links_description = '';
        foreach ( $links_info as $link ) {
            $links_description .= sprintf(
                "- Keyword: '%s', URL: '%s'",
                $link['keyword'],
                esc_url_raw( $link['url'] )
            );
            if ( ! empty( $link['context'] ) ) {
                $links_description .= sprintf( " (Context: %s)", $link['context'] );
            }
            $links_description .= "\n";
        }

        $instructions = "You are a specialized content editor for a WordPress blog.\n";
        if ( ! empty( $global_context ) ) {
            $instructions .= "BLOG CONTEXT: " . $global_context . "\n";
        }
        
        $instructions .= "\nTASK: Insert the provided referral links into the text naturally. Do not rewrite the entire text, only modify sentences to insert links where relevant.\n";
        $instructions .= "RULES:\n";
        $instructions .= "1. Maintain the original HTML structure exactly.\n";
        $instructions .= "2. Return ONLY the HTML for the section provided.\n";
        $instructions .= "3. Do not add markdown code blocks (```html).\n";
        if ( ! empty( $link_rel ) ) {
            $instructions .= sprintf( "4. Use <a href='...' rel='%s'>keyword</a>.\n", esc_attr( $link_rel ) );
        } else {
            $instructions .= "4. Use standard <a href='...'> tags.\n";
        }
        
        $instructions .= "\nLINKS TO INSERT:\n" . $links_description;

        return $instructions;
    }

    /**
     * Escape content for AI prompt to prevent prompt injection.
     *
     * @param string $content Content to escape.
     * @return string Escaped content.
     */
    private function escape_prompt_content( $content ) {
        // Simple escaping to prevent confusion with instructions
        return str_replace( array( 'INSTRUCTIONS:', 'SYSTEM:', 'USER:' ), '', $content );
    }

    /**
     * Sanitize AI response to prevent malicious content.
     *
     * @param string $content Content from AI response.
     * @return string Sanitized content.
     */
    private function sanitize_ai_response( $content ) {
        $allowed_html = wp_kses_allowed_html( 'post' );
        if ( isset( $allowed_html['a'] ) && is_array( $allowed_html['a'] ) ) {
            $allowed_html['a']['rel'] = true;
        }
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
        $response = trim( $response );

        // Remove markdown code blocks if AI added them despite instructions
        $response = preg_replace( '/^```html\s*|\s*```$/', '', $response );

        if ( empty( $response ) ) {
            return new \WP_Error( 'ai_engine_empty', 'Empty response' );
        }

        // Basic safety check: length shouldn't vary wildly for a simple link insertion task
        // But chunking makes this check safer as we compare smaller bits
        $len_ratio = strlen( strip_tags( $response ) ) / ( strlen( strip_tags( $original ) ) + 1 );
        
        if ( $len_ratio < self::MIN_RESPONSE_LENGTH_RATIO ) {
            return new \WP_Error( 'ai_engine_short', 'Response too short compared to original' );
        }

        return $response;
    }
}
