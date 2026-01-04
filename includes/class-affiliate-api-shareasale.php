<?php
/**
 * ShareASale API connector
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-base.php';

/**
 * ShareASale API integration.
 *
 * This class handles integration with ShareASale's API
 * to fetch and manage ShareASale affiliate links.
 */
class WP_Referral_Link_Maker_Affiliate_API_ShareASale extends WP_Referral_Link_Maker_Affiliate_API_Base {

    /**
     * API endpoint base URL
     *
     * @var string
     */
    private $api_endpoint = 'https://api.shareasale.com/';

    /**
     * API version
     *
     * @var string
     */
    private $api_version = '2.8';

    /**
     * Constructor
     *
     * @param array $credentials API credentials (affiliate_id, api_token, api_secret).
     */
    public function __construct( $credentials = array() ) {
        parent::__construct( $credentials );
        $this->api_name = 'shareasale';
    }

    /**
     * Test API connection
     *
     * @return bool True if connection successful, false otherwise.
     */
    public function test_connection() {
        if ( ! $this->validate_credentials() ) {
            $this->set_error( __( 'Missing required ShareASale API credentials.', 'wp-referral-link-maker' ) );
            return false;
        }

        // Test with getMerchantStatus endpoint
        $result = $this->make_api_request( 'getMerchantStatus', array() );
        
        return ! is_wp_error( $result );
    }

    /**
     * Fetch affiliate links from ShareASale
     *
     * @param array $args Optional arguments (merchant_id, limit).
     * @return array|WP_Error Array of links or WP_Error on failure.
     */
    public function fetch_links( $args = array() ) {
        if ( ! $this->validate_credentials() ) {
            return new WP_Error( 'missing_credentials', __( 'ShareASale API credentials not configured.', 'wp-referral-link-maker' ) );
        }

        $defaults = array(
            'merchant_id' => '',
            'limit'       => 50,
        );

        $args = wp_parse_args( $args, $defaults );

        return $this->get_merchant_links( $args );
    }

    /**
     * Get merchant links from ShareASale
     *
     * @param array $args Request arguments.
     * @return array|WP_Error Array of links or error.
     */
    private function get_merchant_links( $args ) {
        $params = array(
            'merchantId' => $args['merchant_id'],
            'limit'      => $args['limit'],
        );

        // If no specific merchant, get links from all active merchants
        if ( empty( $args['merchant_id'] ) ) {
            return $this->get_links_from_all_merchants( $args['limit'] );
        }

        $result = $this->make_api_request( 'getLinks', $params );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $this->parse_shareasale_links( $result );
    }

    /**
     * Get links from all active merchants
     *
     * @param int $limit Maximum links to return.
     * @return array|WP_Error Array of links or error.
     */
    private function get_links_from_all_merchants( $limit ) {
        // First, get list of active merchants
        $merchants_result = $this->make_api_request( 'getMerchantStatus', array() );

        if ( is_wp_error( $merchants_result ) ) {
            return $merchants_result;
        }

        if ( empty( $merchants_result ) || ! is_array( $merchants_result ) ) {
            return new WP_Error( 'no_merchants', __( 'No active merchants found.', 'wp-referral-link-maker' ) );
        }

        $all_links = array();
        $merchant_count = count( $merchants_result );
        $links_per_merchant = max( 1, intval( $limit / $merchant_count ) );

        // Fetch links from each merchant
        foreach ( $merchants_result as $merchant ) {
            if ( ! isset( $merchant['merchantId'] ) || $merchant['status'] !== 'approved' ) {
                continue;
            }

            $merchant_links = $this->get_merchant_links( array(
                'merchant_id' => $merchant['merchantId'],
                'limit'       => $links_per_merchant,
            ) );

            if ( ! is_wp_error( $merchant_links ) ) {
                $all_links = array_merge( $all_links, $merchant_links );
            }

            if ( count( $all_links ) >= $limit ) {
                break;
            }
        }

        return array_slice( $all_links, 0, $limit );
    }

    /**
     * Parse ShareASale API response into our link format
     *
     * @param array $data API response data.
     * @return array Array of parsed links.
     */
    private function parse_shareasale_links( $data ) {
        $links = array();

        if ( empty( $data ) || ! is_array( $data ) ) {
            return $links;
        }

        foreach ( $data as $item ) {
            $title = isset( $item['linkName'] ) ? $item['linkName'] : '';
            $url = isset( $item['linkUrl'] ) ? $item['linkUrl'] : '';
            $link_id = isset( $item['linkId'] ) ? $item['linkId'] : '';

            if ( empty( $title ) || empty( $url ) ) {
                continue;
            }

            // Add affiliate ID to URL if not present
            $url = $this->add_affiliate_id_to_url( $url );

            $links[] = array(
                'title'       => $title,
                'keyword'     => $title,
                'url'         => $url,
                'external_id' => $link_id,
                'description' => isset( $item['description'] ) ? $item['description'] : sprintf( __( 'ShareASale link: %s', 'wp-referral-link-maker' ), $title ),
                'priority'    => 10,
                'max_insertions' => 2,
            );
        }

        return $links;
    }

    /**
     * Make API request to ShareASale
     *
     * @param string $action API action/endpoint.
     * @param array  $params Request parameters.
     * @return array|WP_Error Response data or error.
     */
    private function make_api_request( $action, $params ) {
        $affiliate_id = $this->credentials['affiliate_id'];
        $api_token = $this->credentials['api_token'];
        $api_secret = $this->credentials['api_secret'];

        // Build API URL
        $url = $this->api_endpoint . $this->api_version . '/' . $action;

        // Generate timestamp
        $timestamp = gmdate( 'D, d M Y H:i:s T' );

        // Generate authentication signature
        $sig_string = $api_token . ':' . $timestamp . ':' . $action . ':' . $api_secret;
        $sig = hash( 'sha256', $sig_string );

        // Build headers
        $headers = array(
            'x-ShareASale-Date'        => $timestamp,
            'x-ShareASale-Authentication' => $sig,
            'x-ShareASale-Affiliate'   => $affiliate_id,
            'x-ShareASale-APIToken'    => $api_token,
            'x-ShareASale-APIVersion'  => $this->api_version,
        );

        // Add parameters to URL
        if ( ! empty( $params ) ) {
            $url = add_query_arg( $params, $url );
        }

        $response = wp_remote_get(
            $url,
            array(
                'headers' => $headers,
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            $this->set_error( $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            $body = wp_remote_retrieve_body( $response );
            $this->set_error( sprintf( 'ShareASale API error (code %d): %s', $code, $body ) );
            return new WP_Error( 'api_error', $this->last_error );
        }

        $body = wp_remote_retrieve_body( $response );
        
        // Try to parse as JSON
        $data = json_decode( $body, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            return $data;
        }

        // If not JSON, try parsing as CSV or text
        return $this->parse_response_format( $body );
    }

    /**
     * Parse different response formats from ShareASale
     *
     * @param string $body Response body.
     * @return array Parsed data.
     */
    private function parse_response_format( $body ) {
        // ShareASale often returns pipe-delimited data
        $lines = explode( "\n", trim( $body ) );
        
        if ( empty( $lines ) ) {
            return array();
        }

        // First line might be headers
        $headers = str_getcsv( $lines[0], '|' );
        $data = array();

        for ( $i = 1; $i < count( $lines ); $i++ ) {
            if ( empty( trim( $lines[ $i ] ) ) ) {
                continue;
            }

            $values = str_getcsv( $lines[ $i ], '|' );
            
            if ( count( $values ) === count( $headers ) ) {
                $data[] = array_combine( $headers, $values );
            }
        }

        return $data;
    }

    /**
     * Add affiliate ID to ShareASale URL
     *
     * @param string $url Original URL.
     * @return string URL with affiliate ID.
     */
    private function add_affiliate_id_to_url( $url ) {
        $affiliate_id = $this->credentials['affiliate_id'];
        
        // If URL already contains affiliate ID, return as-is
        if ( strpos( $url, 'afftrack=' ) !== false || strpos( $url, 'affiliateId=' ) !== false ) {
            return $url;
        }

        // Add affiliate ID parameter
        return add_query_arg( 'afftrack', $affiliate_id, $url );
    }

    /**
     * Validate credentials
     *
     * @return bool True if valid, false otherwise.
     */
    private function validate_credentials() {
        return ! empty( $this->credentials['affiliate_id'] ) &&
               ! empty( $this->credentials['api_token'] ) &&
               ! empty( $this->credentials['api_secret'] );
    }
}
