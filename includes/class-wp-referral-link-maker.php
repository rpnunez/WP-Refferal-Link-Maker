<?php
/**
 * The core plugin class
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class WP_Referral_Link_Maker {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

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
        $this->version = WP_REFERRAL_LINK_MAKER_VERSION;
        $this->plugin_name = 'wp-referral-link-maker';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_post_type_hooks();
        $this->define_cron_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-loader.php';
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-post-types.php';
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-cron.php';
        require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'admin/class-admin.php';

        $this->loader = new WP_Referral_Link_Maker_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Referral_Link_Maker_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    }

    /**
     * Register all of the hooks related to custom post types.
     */
    private function define_post_type_hooks() {
        $post_types = new WP_Referral_Link_Maker_Post_Types();

        $this->loader->add_action( 'init', $post_types, 'register_post_types' );
    }

    /**
     * Register all of the hooks related to cron jobs.
     */
    private function define_cron_hooks() {
        $cron = new WP_Referral_Link_Maker_Cron();

        $this->loader->add_action( 'wp_referral_link_maker_process_posts', $cron, 'process_posts' );
        $this->loader->add_filter( 'cron_schedules', $cron, 'add_custom_cron_intervals' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
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
