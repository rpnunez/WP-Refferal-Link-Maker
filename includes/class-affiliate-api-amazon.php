<?php
/**
 * Amazon Product Advertising API connector
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-base.php';

/**
 * Amazon Product Advertising API integration.
 *
 * This class handles integration with Amazon's Product Advertising API
 * to fetch and manage Amazon affiliate links.
 */
class WP_Referral_Link_Maker_Affiliate_API_Amazon extends WP_Referral_Link_Maker_Affiliate_API_Base {

    /**
     * API endpoint base URL
     *
     * @var string
     */
    private $api_endpoint = 'https://webservices.amazon.com/paapi5/';

    /**
     * Constructor
     *
     * @param array $credentials API credentials (access_key, secret_key, associate_tag, region).
     */
    public function __construct( $credentials = array() ) {
        parent::__construct( $credentials );
        $this->api_name = 'amazon';
    }

    /**
     * Test API connection
     *
     * @return bool True if connection successful, false otherwise.
     */
    public function test_connection() {
        if ( ! $this->validate_credentials() ) {
            $this->set_error( __( 'Missing required Amazon API credentials.', 'wp-referral-link-maker' ) );
            return false;
        }

        // For Amazon PA API 5.0, we can test by making a simple search request
        $test_result = $this->search_products( array( 'keywords' => 'test', 'limit' => 1 ) );
        
        return ! is_wp_error( $test_result );
    }

    /**
     * Fetch affiliate links from Amazon
     *
     * @param array $args Optional arguments (keywords, category, limit).
     * @return array|WP_Error Array of links or WP_Error on failure.
     */
    public function fetch_links( $args = array() ) {
        if ( ! $this->validate_credentials() ) {
            return new WP_Error( 'missing_credentials', __( 'Amazon API credentials not configured.', 'wp-referral-link-maker' ) );
        }

        $defaults = array(
            'keywords' => '',
            'limit'    => 10,
        );

        $args = wp_parse_args( $args, $defaults );

        if ( empty( $args['keywords'] ) ) {
            return new WP_Error( 'missing_keywords', __( 'Keywords are required for Amazon search.', 'wp-referral-link-maker' ) );
        }

        return $this->search_products( $args );
    }

    /**
     * Search for products on Amazon
     *
     * @param array $args Search arguments.
     * @return array|WP_Error Array of products or error.
     */
    private function search_products( $args ) {
        $access_key = $this->credentials['access_key'];
        $secret_key = $this->credentials['secret_key'];
        $associate_tag = $this->credentials['associate_tag'];
        $region = isset( $this->credentials['region'] ) ? $this->credentials['region'] : 'us-east-1';

        // Build the request payload
        $payload = array(
            'Keywords'       => $args['keywords'],
            'Resources'      => array( 'ItemInfo.Title', 'Offers.Listings.Price' ),
            'ItemCount'      => min( $args['limit'], 10 ),
            'PartnerTag'     => $associate_tag,
            'PartnerType'    => 'Associates',
            'Marketplace'    => $this->get_marketplace_url( $region ),
        );

        // Sign and make the request
        $endpoint = $this->api_endpoint . 'searchitems';
        $headers = $this->generate_request_headers( $endpoint, $payload, $region );

        $response = wp_remote_post(
            $endpoint,
            array(
                'headers' => $headers,
                'body'    => wp_json_encode( $payload ),
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
            $this->set_error( sprintf( 'Amazon API error (code %d): %s', $code, $body ) );
            return new WP_Error( 'api_error', $this->last_error );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $this->parse_amazon_response( $data );
    }

    /**
     * Parse Amazon API response into our link format
     *
     * @param array $data API response data.
     * @return array Array of parsed links.
     */
    private function parse_amazon_response( $data ) {
        $links = array();

        if ( ! isset( $data['SearchResult']['Items'] ) ) {
            return $links;
        }

        foreach ( $data['SearchResult']['Items'] as $item ) {
            $title = isset( $item['ItemInfo']['Title']['DisplayValue'] ) ? $item['ItemInfo']['Title']['DisplayValue'] : '';
            $asin = isset( $item['ASIN'] ) ? $item['ASIN'] : '';

            if ( empty( $title ) || empty( $asin ) ) {
                continue;
            }

            // Build affiliate link
            $associate_tag = $this->credentials['associate_tag'];
            $url = sprintf( 'https://www.amazon.com/dp/%s?tag=%s', $asin, $associate_tag );

            $links[] = array(
                'title'       => $title,
                'keyword'     => $title,
                'url'         => $url,
                'external_id' => $asin,
                'description' => sprintf( 'Amazon product: %s (ASIN: %s)', $title, $asin ),
                'priority'    => 10,
                'max_insertions' => 2,
            );
        }

        return $links;
    }

    /**
     * Generate request headers for Amazon PA API 5.0
     *
     * @param string $endpoint Request endpoint.
     * @param array  $payload Request payload.
     * @param string $region AWS region.
     * @return array Headers array.
     */
    private function generate_request_headers( $endpoint, $payload, $region ) {
        $access_key = $this->credentials['access_key'];
        $secret_key = $this->credentials['secret_key'];
        
        $timestamp = gmdate( 'Ymd\THis\Z' );
        $date = gmdate( 'Ymd' );

        // For simplicity, we're using basic authentication
        // In production, implement AWS Signature Version 4
        return array(
            'Content-Type'   => 'application/json',
            'X-Amz-Target'   => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
            'Content-Encoding' => 'amz-1.0',
            'X-Amz-Date'     => $timestamp,
            'Authorization'  => $this->generate_aws_signature( $endpoint, $payload, $timestamp, $date, $region ),
        );
    }

    /**
     * Generate AWS Signature Version 4
     *
     * @param string $endpoint Request endpoint.
     * @param array  $payload Request payload.
     * @param string $timestamp Request timestamp.
     * @param string $date Request date.
     * @param string $region AWS region.
     * @return string Authorization header value.
     */
    private function generate_aws_signature( $endpoint, $payload, $timestamp, $date, $region ) {
        // This is a simplified implementation
        // For production use, implement full AWS Signature Version 4
        // See: https://docs.aws.amazon.com/general/latest/gr/signature-version-4.html
        
        $access_key = $this->credentials['access_key'];
        $secret_key = $this->credentials['secret_key'];

        return sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s/%s/ProductAdvertisingAPI/aws4_request',
            $access_key,
            $date,
            $region
        );
    }

    /**
     * Get marketplace URL for region
     *
     * @param string $region AWS region.
     * @return string Marketplace URL.
     */
    private function get_marketplace_url( $region ) {
        $marketplaces = array(
            'us-east-1' => 'www.amazon.com',
            'eu-west-1' => 'www.amazon.co.uk',
            'eu-central-1' => 'www.amazon.de',
            'us-west-2' => 'www.amazon.com',
            'ap-northeast-1' => 'www.amazon.co.jp',
        );

        return isset( $marketplaces[ $region ] ) ? $marketplaces[ $region ] : 'www.amazon.com';
    }

    /**
     * Validate credentials
     *
     * @return bool True if valid, false otherwise.
     */
    private function validate_credentials() {
        return ! empty( $this->credentials['access_key'] ) &&
               ! empty( $this->credentials['secret_key'] ) &&
               ! empty( $this->credentials['associate_tag'] );
    }
}
