<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin area.
 */
class Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, NRE_PLUGIN_URL . 'admin/css/admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, NRE_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), $this->version, false );
    }

    /**
     * Add admin menu pages.
     */
    public function add_admin_menu() {
        // Add main menu page
        add_menu_page(
            __( 'Referral Link Maker', 'wp-referral-link-maker' ),
            __( 'Referral Links', 'wp-referral-link-maker' ),
            'manage_options',
            'wp-referral-link-maker',
            array( $this, 'display_overview_page' ),
            'dashicons-admin-links',
            30
        );

        // Add Overview submenu (replaces main menu)
        add_submenu_page(
            'wp-referral-link-maker',
            __( 'Overview', 'wp-referral-link-maker' ),
            __( 'Overview', 'wp-referral-link-maker' ),
            'manage_options',
            'wp-referral-link-maker',
            array( $this, 'display_overview_page' )
        );
    }

    /**
     * Display the Overview page.
     */
    public function display_overview_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get global values
        $global_values = get_option( 'wp_referral_link_maker_global_values', array() );

        // Handle form submission
        if ( isset( $_POST['wp_rlm_save_global_values'] ) && check_admin_referer( 'wp_rlm_global_values' ) ) {
            $global_values = array(
                'default_group' => isset( $_POST['default_group'] ) ? sanitize_text_field( $_POST['default_group'] ) : '',
                'global_link_prefix' => isset( $_POST['global_link_prefix'] ) ? sanitize_text_field( $_POST['global_link_prefix'] ) : '',
                'global_link_suffix' => isset( $_POST['global_link_suffix'] ) ? sanitize_text_field( $_POST['global_link_suffix'] ) : '',
            );

            update_option( 'wp_referral_link_maker_global_values', $global_values );

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Global values saved successfully.', 'wp-referral-link-maker' ) . '</p></div>';
        }

        // Get statistics
        $stats = $this->get_statistics();

        include NRE_PLUGIN_DIR . 'admin/partials/overview-page.php';
    }

    /**
     * Get plugin statistics.
     *
     * @return array Statistics data.
     */
    private function get_statistics() {
        // Get counts for custom post types
        $link_groups = wp_count_posts( 'ref_link_group' );
        $referral_links = wp_count_posts( 'ref_link_maker' );

        // Get processed posts count
        global $wpdb;
        $processed_posts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wp_rlm_processed'" );

        return array(
            'link_groups'     => isset( $link_groups->publish ) ? $link_groups->publish : 0,
            'referral_links'  => isset( $referral_links->publish ) ? $referral_links->publish : 0,
            'processed_posts' => intval( $processed_posts ),
        );
    }
}
