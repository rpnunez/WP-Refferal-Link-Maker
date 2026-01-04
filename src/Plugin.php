<?php
/**
 * The core plugin class
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Plugin {

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version = NRE_VERSION;
        $this->plugin_name = 'wp-referral-link-maker';

        $this->define_admin_hooks();
        $this->define_post_type_hooks();
        $this->define_meta_box_hooks();
        $this->define_cron_hooks();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

        add_action( 'admin_menu', array( $plugin_admin, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );

        // Register settings functionality
        $plugin_settings = new Settings( $this->get_plugin_name(), $this->get_version() );

        add_action( 'admin_menu', array( $plugin_settings, 'add_settings_submenu' ), 20 );
        add_action( 'admin_init', array( $plugin_settings, 'register_settings' ) );
    }

    /**
     * Register all of the hooks related to custom post types.
     */
    private function define_post_type_hooks() {
        $post_types = new PostTypes();

        add_action( 'init', array( $post_types, 'register_post_types' ) );
    }

    /**
     * Register all of the hooks related to meta boxes.
     */
    private function define_meta_box_hooks() {
        new MetaBoxes();
    }

    /**
     * Register all of the hooks related to cron jobs.
     */
    private function define_cron_hooks() {
        $cron = new Cron();

        add_action( 'wp_referral_link_maker_process_posts', array( $cron, 'process_posts' ) );
        add_filter( 'cron_schedules', array( $cron, 'add_custom_cron_intervals' ) );
    }

    /**
     * The name of the plugin used to uniquely identify it.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
