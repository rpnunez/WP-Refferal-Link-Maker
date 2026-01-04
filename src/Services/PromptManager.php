<?php
/**
 * Prompt Management for AI Engine
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\Services;

/**
 * Manage AI prompt building and content escaping.
 *
 * This class handles the construction of AI instructions and
 * preparation of content for AI processing.
 */
class PromptManager {

    /**
     * Build AI instructions (System Prompt).
     *
     * @param array $links_info Array of link information.
     * @return string Instructions.
     */
    public function build_ai_instructions( $links_info ) {
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
    public function escape_prompt_content( $content ) {
        // Simple escaping to prevent confusion with instructions
        return str_replace( array( 'INSTRUCTIONS:', 'SYSTEM:', 'USER:' ), '', $content );
    }
}
