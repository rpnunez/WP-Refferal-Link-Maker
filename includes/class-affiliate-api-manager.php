<?php
/**
 * Affiliate API Manager
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Manage all affiliate network API integrations.
 *
 * This class provides a unified interface for managing multiple affiliate
 * network API connectors and handling import operations.
 */
class WP_Referral_Link_Maker_Affiliate_API_Manager {

    /**
     * Available API connectors
     *
     * @var array
     */
    private $connectors = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_connectors();
    }

    /**
     * Load all API connectors
     */
    private function load_connectors() {
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-base.php';
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-amazon.php';
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-shareasale.php';
    }

    /**
     * Get connector for specified network
     *
     * @param string $network Network name (amazon, shareasale).
     * @return WP_Referral_Link_Maker_Affiliate_API_Base|null Connector instance or null.
     */
    public function get_connector( $network ) {
        $credentials = $this->get_network_credentials( $network );

        if ( empty( $credentials ) ) {
            return null;
        }

        switch ( $network ) {
            case 'amazon':
                return new WP_Referral_Link_Maker_Affiliate_API_Amazon( $credentials );
            
            case 'shareasale':
                return new WP_Referral_Link_Maker_Affiliate_API_ShareASale( $credentials );
            
            default:
                return null;
        }
    }

    /**
     * Get credentials for a network
     *
     * @param string $network Network name.
     * @return array Credentials array.
     */
    private function get_network_credentials( $network ) {
        $settings = get_option( 'wp_referral_link_maker_affiliate_networks', array() );

        if ( ! isset( $settings[ $network ] ) ) {
            return array();
        }

        return $settings[ $network ];
    }

    /**
     * Test connection for a network
     *
     * @param string $network Network name.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function test_connection( $network ) {
        $connector = $this->get_connector( $network );

        if ( ! $connector ) {
            return new WP_Error( 'no_connector', __( 'Invalid network or missing credentials.', 'wp-referral-link-maker' ) );
        }

        if ( $connector->test_connection() ) {
            return true;
        }

        return new WP_Error( 'connection_failed', $connector->get_last_error() );
    }

    /**
     * Fetch links from a network
     *
     * @param string $network Network name.
     * @param array  $args    Fetch arguments.
     * @return array|WP_Error Array of links or error.
     */
    public function fetch_links( $network, $args = array() ) {
        $connector = $this->get_connector( $network );

        if ( ! $connector ) {
            return new WP_Error( 'no_connector', __( 'Invalid network or missing credentials.', 'wp-referral-link-maker' ) );
        }

        return $connector->fetch_links( $args );
    }

    /**
     * Import links from a network
     *
     * @param string $network Network name.
     * @param array  $args    Fetch arguments.
     * @return array|WP_Error Import results or error.
     */
    public function import_links( $network, $args = array() ) {
        $links = $this->fetch_links( $network, $args );

        if ( is_wp_error( $links ) ) {
            return $links;
        }

        $connector = $this->get_connector( $network );
        
        if ( ! $connector ) {
            return new WP_Error( 'no_connector', __( 'Invalid network or missing credentials.', 'wp-referral-link-maker' ) );
        }

        return $connector->import_links( $links );
    }

    /**
     * Get list of available networks
     *
     * @return array Array of network names and labels.
     */
    public function get_available_networks() {
        return array(
            'amazon'     => __( 'Amazon Associates', 'wp-referral-link-maker' ),
            'shareasale' => __( 'ShareASale', 'wp-referral-link-maker' ),
        );
    }

    /**
     * Get network status (configured or not)
     *
     * @param string $network Network name.
     * @return bool True if configured, false otherwise.
     */
    public function is_network_configured( $network ) {
        $credentials = $this->get_network_credentials( $network );
        
        switch ( $network ) {
            case 'amazon':
                return ! empty( $credentials['access_key'] ) &&
                       ! empty( $credentials['secret_key'] ) &&
                       ! empty( $credentials['associate_tag'] );
            
            case 'shareasale':
                return ! empty( $credentials['affiliate_id'] ) &&
                       ! empty( $credentials['api_token'] ) &&
                       ! empty( $credentials['api_secret'] );
            
            default:
                return false;
        }
    }
}
