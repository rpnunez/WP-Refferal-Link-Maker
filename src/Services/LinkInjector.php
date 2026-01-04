<?php
/**
 * Link Injection Service
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\Services;

/**
 * Handle referral link injection into content.
 *
 * This class provides methods for injecting referral links into post content
 * using keyword matching and replacement strategies.
 */
class LinkInjector {

    /**
     * Apply referral links to post content.
     *
     * This is a fallback method used when AI Engine is not available.
     *
     * @param string $content Original post content.
     * @param array  $links   Array of referral link objects.
     * @return string Modified content with referral links.
     */
    public function apply_referral_links( $content, $links ) {
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
