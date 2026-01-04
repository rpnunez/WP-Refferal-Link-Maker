<?php
/**
 * Analytics tracking functionality
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Handle analytics tracking for referral links.
 *
 * This class tracks user interactions with referral links.
 */
class WP_Referral_Link_Maker_Analytics {

    /**
     * Initialize analytics tracking.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tracking_script' ) );
        add_action( 'wp_ajax_wp_rlm_track_click', array( $this, 'track_link_click' ) );
        add_action( 'wp_ajax_nopriv_wp_rlm_track_click', array( $this, 'track_link_click' ) );
    }

    /**
     * Enqueue tracking script on frontend.
     */
    public function enqueue_tracking_script() {
        wp_enqueue_script(
            'wp-rlm-analytics',
            WP_REFERRAL_LINK_MAKER_PLUGIN_URL . 'public/js/analytics.js',
            array( 'jquery' ),
            WP_REFERRAL_LINK_MAKER_VERSION,
            true
        );

        wp_localize_script( 'wp-rlm-analytics', 'wpRlmAnalytics', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wp_rlm_analytics_nonce' ),
        ) );
    }

    /**
     * Track link click via AJAX.
     */
    public function track_link_click() {
        check_ajax_referer( 'wp_rlm_analytics_nonce', 'nonce' );

        global $wpdb;

        $referral_link_id = isset( $_POST['referral_link_id'] ) ? absint( $_POST['referral_link_id'] ) : 0;
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $referrer_url = isset( $_POST['referrer_url'] ) ? esc_url_raw( $_POST['referrer_url'] ) : '';

        if ( ! $referral_link_id ) {
            wp_send_json_error( array( 'message' => 'Invalid referral link ID' ) );
            return;
        }

        $table_name = $wpdb->prefix . 'wp_rlm_analytics';

        $data = array(
            'referral_link_id' => $referral_link_id,
            'post_id'          => $post_id,
            'user_id'          => get_current_user_id(),
            'click_time'       => current_time( 'mysql', 1 ), // Use GMT time
            'user_ip'          => $this->get_user_ip(),
            'user_agent'       => $this->get_user_agent(),
            'referrer_url'     => $referrer_url,
        );

        $result = $wpdb->insert( $table_name, $data );

        if ( false === $result ) {
            wp_send_json_error( array( 'message' => 'Failed to track click' ) );
            return;
        }

        wp_send_json_success( array( 'message' => 'Click tracked successfully' ) );
    }

    /**
     * Get user IP address.
     *
     * Note: HTTP headers like X-Forwarded-For can be spoofed by clients.
     * This method validates the IP format but cannot guarantee authenticity.
     * For critical applications, consider only using REMOTE_ADDR.
     *
     * @return string User IP address.
     */
    private function get_user_ip() {
        $ip = '';

        // Check for IP from reverse proxy headers
        // Note: These headers can be spoofed, but we validate the format
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // HTTP_X_FORWARDED_FOR can contain multiple IPs, get the first one
            $ip_list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $ip_list[0] );
        } elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            // REMOTE_ADDR is the most trustworthy as it's set by the server
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Sanitize and validate IP address format
        $ip = sanitize_text_field( $ip );
        
        // Validate as IPv4 or IPv6
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return $ip;
        }

        // Return empty string if invalid IP
        return '';
    }

    /**
     * Get user agent.
     *
     * @return string User agent string.
     */
    private function get_user_agent() {
        if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return '';
        }
        
        // Use wp_kses to strip potentially harmful tags while preserving the full string
        $user_agent = wp_kses( $_SERVER['HTTP_USER_AGENT'], array() );
        
        // Limit length to prevent extremely long strings
        return substr( $user_agent, 0, 500 );
    }

    /**
     * Get analytics data for a specific referral link.
     *
     * @param int $referral_link_id Referral link ID.
     * @return array Analytics data.
     */
    public static function get_link_analytics( $referral_link_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp_rlm_analytics';

        $total_clicks = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE referral_link_id = %d",
            $referral_link_id
        ) );

        $unique_users = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM $table_name WHERE referral_link_id = %d",
            $referral_link_id
        ) );

        $recent_clicks = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE referral_link_id = %d ORDER BY click_time DESC LIMIT 10",
            $referral_link_id
        ) );

        return array(
            'total_clicks'  => intval( $total_clicks ),
            'unique_users'  => intval( $unique_users ),
            'recent_clicks' => $recent_clicks,
        );
    }

    /**
     * Get overall analytics data.
     *
     * @return array Overall analytics data.
     */
    public static function get_overall_analytics() {
        global $wpdb;

        // Table name uses $wpdb->prefix which is a trusted WordPress internal value
        $table_name = $wpdb->prefix . 'wp_rlm_analytics';

        // These queries do not contain any user input, only trusted WordPress values
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total_clicks = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $unique_users = $wpdb->get_var( "SELECT COUNT(DISTINCT user_ip) FROM $table_name" );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $clicks_by_link = $wpdb->get_results(
            "SELECT referral_link_id, COUNT(*) as click_count 
            FROM $table_name 
            GROUP BY referral_link_id 
            ORDER BY click_count DESC 
            LIMIT 10"
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $clicks_by_date = $wpdb->get_results(
            "SELECT DATE(click_time) as date, COUNT(*) as click_count 
            FROM $table_name 
            WHERE click_time >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)
            GROUP BY DATE(click_time) 
            ORDER BY date DESC"
        );

        return array(
            'total_clicks'    => intval( $total_clicks ),
            'unique_users'    => intval( $unique_users ),
            'clicks_by_link'  => $clicks_by_link,
            'clicks_by_date'  => $clicks_by_date,
        );
    }
}
