<?php
/**
 * Base class for affiliate network API connectors
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Abstract base class for affiliate network API integrations.
 *
 * This class provides a common interface for different affiliate network APIs
 * and handles common functionality like caching and error handling.
 */
abstract class WP_Referral_Link_Maker_Affiliate_API_Base {

    /**
     * API name identifier
     *
     * @var string
     */
    protected $api_name;

    /**
     * API credentials
     *
     * @var array
     */
    protected $credentials;

    /**
     * Last error message
     *
     * @var string
     */
    protected $last_error = '';

    /**
     * Constructor
     *
     * @param array $credentials API credentials.
     */
    public function __construct( $credentials = array() ) {
        $this->credentials = $credentials;
    }

    /**
     * Test API connection
     *
     * @return bool True if connection successful, false otherwise.
     */
    abstract public function test_connection();

    /**
     * Fetch affiliate links from the API
     *
     * @param array $args Optional arguments for filtering/pagination.
     * @return array|WP_Error Array of links or WP_Error on failure.
     */
    abstract public function fetch_links( $args = array() );

    /**
     * Import links into WordPress
     *
     * @param array $links Array of link data to import.
     * @return array Array with 'success' count and 'failed' count.
     */
    public function import_links( $links ) {
        $imported = array(
            'success' => 0,
            'failed'  => 0,
            'skipped' => 0,
        );

        foreach ( $links as $link_data ) {
            $result = $this->import_single_link( $link_data );
            
            if ( $result === true ) {
                $imported['success']++;
            } elseif ( $result === 'skipped' ) {
                $imported['skipped']++;
            } else {
                $imported['failed']++;
            }
        }

        return $imported;
    }

    /**
     * Import a single link
     *
     * @param array $link_data Link data.
     * @return bool|string True on success, 'skipped' if exists, false on failure.
     */
    protected function import_single_link( $link_data ) {
        // Check if link already exists
        if ( $this->link_exists( $link_data['url'] ) ) {
            return 'skipped';
        }

        // Create post
        $post_data = array(
            'post_title'  => sanitize_text_field( $link_data['title'] ),
            'post_type'   => 'ref_link_maker',
            'post_status' => 'publish',
            'post_content' => isset( $link_data['description'] ) ? sanitize_textarea_field( $link_data['description'] ) : '',
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        // Save meta data
        update_post_meta( $post_id, '_ref_link_keyword', sanitize_text_field( $link_data['keyword'] ) );
        update_post_meta( $post_id, '_ref_link_url', esc_url_raw( $link_data['url'] ) );
        update_post_meta( $post_id, '_ref_link_source', sanitize_text_field( $this->api_name ) );
        update_post_meta( $post_id, '_ref_link_priority', isset( $link_data['priority'] ) ? absint( $link_data['priority'] ) : 10 );
        update_post_meta( $post_id, '_ref_link_max_insertions', isset( $link_data['max_insertions'] ) ? absint( $link_data['max_insertions'] ) : 3 );

        // Save external ID if provided
        if ( isset( $link_data['external_id'] ) ) {
            update_post_meta( $post_id, '_ref_link_external_id', sanitize_text_field( $link_data['external_id'] ) );
        }

        return true;
    }

    /**
     * Check if a link already exists
     *
     * @param string $url Link URL.
     * @return bool True if exists, false otherwise.
     */
    protected function link_exists( $url ) {
        global $wpdb;
        
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_ref_link_url' 
                AND meta_value = %s 
                LIMIT 1",
                $url
            )
        );

        return ! empty( $existing );
    }

    /**
     * Get last error message
     *
     * @return string Error message.
     */
    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Set error message
     *
     * @param string $message Error message.
     */
    protected function set_error( $message ) {
        $this->last_error = $message;
    }

    /**
     * Make HTTP request with error handling
     *
     * @param string $url Request URL.
     * @param array  $args Request arguments.
     * @return array|WP_Error Response or error.
     */
    protected function make_request( $url, $args = array() ) {
        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            $this->set_error( $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            $this->set_error( sprintf( 'API returned status code %d', $code ) );
            return new WP_Error( 'api_error', $this->last_error );
        }

        $body = wp_remote_retrieve_body( $response );
        return json_decode( $body, true );
    }

    /**
     * Get API name
     *
     * @return string API name.
     */
    public function get_api_name() {
        return $this->api_name;
    }
}
