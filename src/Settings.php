<?php
/**
 * The settings-specific functionality of the plugin
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine;

/**
 * The settings-specific functionality of the plugin.
 *
 * Defines the plugin settings registration, rendering, and sanitization.
 */
class Settings {

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
     * Register settings hooks.
     */
    public static function register() {
        $instance = new self( 'wp-referral-link-maker', NRE_VERSION );
        add_action( 'admin_menu', array( $instance, 'add_settings_submenu' ), 20 );
        add_action( 'admin_init', array( $instance, 'register_settings' ) );
    }

    /**
     * Add Settings submenu page.
     */
    public function add_settings_submenu() {
        add_submenu_page(
            'wp-referral-link-maker',
            __( 'Settings', 'wp-referral-link-maker' ),
            __( 'Settings', 'wp-referral-link-maker' ),
            'manage_options',
            'wp-referral-link-maker-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Display the Settings page.
     */
    public function display_settings_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        include NRE_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'wp_referral_link_maker_settings',
            'wp_referral_link_maker_settings',
            array( $this, 'sanitize_settings' )
        );

        // Add settings section
        add_settings_section(
            'wp_referral_link_maker_ai_engine',
            __( 'AI Engine Configuration', 'wp-referral-link-maker' ),
            array( $this, 'ai_engine_section_callback' ),
            'wp-referral-link-maker-settings'
        );

        // Add settings fields
        add_settings_field(
            'ai_engine_enabled',
            __( 'Enable AI Engine', 'wp-referral-link-maker' ),
            array( $this, 'ai_engine_enabled_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_ai_engine'
        );

        add_settings_field(
            'api_key',
            __( 'API Key', 'wp-referral-link-maker' ),
            array( $this, 'api_key_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_ai_engine'
        );

        add_settings_field(
            'global_ai_context',
            __( 'Global AI Context', 'wp-referral-link-maker' ),
            array( $this, 'global_ai_context_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_ai_engine'
        );

        // Add automation section
        add_settings_section(
            'wp_referral_link_maker_automation',
            __( 'Automation Settings', 'wp-referral-link-maker' ),
            array( $this, 'automation_section_callback' ),
            'wp-referral-link-maker-settings'
        );

        add_settings_field(
            'auto_update_enabled',
            __( 'Enable Auto Updates', 'wp-referral-link-maker' ),
            array( $this, 'auto_update_enabled_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_automation'
        );

        add_settings_field(
            'cron_interval',
            __( 'Update Interval', 'wp-referral-link-maker' ),
            array( $this, 'cron_interval_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_automation'
        );

        add_settings_field(
            'post_status_after_edit',
            __( 'Post Status After AI Edit', 'wp-referral-link-maker' ),
            array( $this, 'post_status_after_edit_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_automation'
        );

        add_settings_field(
            'link_rel_attribute',
            __( 'Link Rel Attribute', 'wp-referral-link-maker' ),
            array( $this, 'link_rel_attribute_callback' ),
            'wp-referral-link-maker-settings',
            'wp_referral_link_maker_automation'
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        if ( isset( $input['ai_engine_enabled'] ) ) {
            $sanitized['ai_engine_enabled'] = (bool) $input['ai_engine_enabled'];
        }

        if ( isset( $input['api_key'] ) ) {
            $sanitized['api_key'] = sanitize_text_field( $input['api_key'] );
        }

        if ( isset( $input['global_ai_context'] ) ) {
            $sanitized['global_ai_context'] = sanitize_textarea_field( $input['global_ai_context'] );
        }

        if ( isset( $input['auto_update_enabled'] ) ) {
            $sanitized['auto_update_enabled'] = (bool) $input['auto_update_enabled'];
        }

        if ( isset( $input['cron_interval'] ) ) {
            $sanitized['cron_interval'] = sanitize_text_field( $input['cron_interval'] );
        }

        if ( isset( $input['post_status_after_edit'] ) ) {
            $sanitized['post_status_after_edit'] = sanitize_text_field( $input['post_status_after_edit'] );
        }

        if ( isset( $input['link_rel_attribute'] ) ) {
            $sanitized['link_rel_attribute'] = wp_referral_link_maker_sanitize_rel_attribute( $input['link_rel_attribute'] );
        }

        return $sanitized;
    }

    /**
     * AI Engine section callback.
     */
    public function ai_engine_section_callback() {
        echo '<p>' . esc_html__( 'Configure AI Engine plugin integration for automated referral link insertion.', 'wp-referral-link-maker' ) . '</p>';
    }

    /**
     * Automation section callback.
     */
    public function automation_section_callback() {
        echo '<p>' . esc_html__( 'Configure automatic processing of posts with referral links.', 'wp-referral-link-maker' ) . '</p>';
    }

    /**
     * AI Engine enabled field callback.
     */
    public function ai_engine_enabled_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['ai_engine_enabled'] ) ? $settings['ai_engine_enabled'] : false;
        ?>
        <label>
            <input type="checkbox" name="wp_referral_link_maker_settings[ai_engine_enabled]" value="1" <?php checked( $value, true ); ?> />
            <?php esc_html_e( 'Enable AI Engine integration', 'wp-referral-link-maker' ); ?>
        </label>
        <?php
    }

    /**
     * API Key field callback.
     */
    public function api_key_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
        ?>
        <input type="text" name="wp_referral_link_maker_settings[api_key]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'Enter your AI Engine API key.', 'wp-referral-link-maker' ); ?></p>
        <?php
    }

    /**
     * Global AI Context field callback.
     */
    public function global_ai_context_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['global_ai_context'] ) ? $settings['global_ai_context'] : '';
        ?>
        <textarea name="wp_referral_link_maker_settings[global_ai_context]" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Provide global context for the AI (e.g., "This is a web development blog focusing on React and PHP"). This helps the AI understand the overall theme.', 'wp-referral-link-maker' ); ?></p>
        <?php
    }

    /**
     * Auto update enabled field callback.
     */
    public function auto_update_enabled_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['auto_update_enabled'] ) ? $settings['auto_update_enabled'] : false;
        ?>
        <label>
            <input type="checkbox" name="wp_referral_link_maker_settings[auto_update_enabled]" value="1" <?php checked( $value, true ); ?> />
            <?php esc_html_e( 'Automatically process posts with referral links', 'wp-referral-link-maker' ); ?>
        </label>
        <?php
    }

    /**
     * Cron interval field callback.
     */
    public function cron_interval_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['cron_interval'] ) ? $settings['cron_interval'] : 'daily';
        ?>
        <select name="wp_referral_link_maker_settings[cron_interval]">
            <option value="hourly" <?php selected( $value, 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'wp-referral-link-maker' ); ?></option>
            <option value="twice_daily" <?php selected( $value, 'twice_daily' ); ?>><?php esc_html_e( 'Twice Daily', 'wp-referral-link-maker' ); ?></option>
            <option value="daily" <?php selected( $value, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wp-referral-link-maker' ); ?></option>
            <option value="weekly" <?php selected( $value, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wp-referral-link-maker' ); ?></option>
        </select>
        <?php
    }

    /**
     * Post status after edit field callback.
     */
    public function post_status_after_edit_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['post_status_after_edit'] ) ? $settings['post_status_after_edit'] : 'pending';
        ?>
        <select name="wp_referral_link_maker_settings[post_status_after_edit]">
            <option value="pending" <?php selected( $value, 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'wp-referral-link-maker' ); ?></option>
            <option value="draft" <?php selected( $value, 'draft' ); ?>><?php esc_html_e( 'Draft', 'wp-referral-link-maker' ); ?></option>
            <option value="publish" <?php selected( $value, 'publish' ); ?>><?php esc_html_e( 'Published', 'wp-referral-link-maker' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Status to set posts after AI processing.', 'wp-referral-link-maker' ); ?></p>
        <?php
    }

    /**
     * Link rel attribute field callback.
     */
    public function link_rel_attribute_callback() {
        $settings = get_option( 'wp_referral_link_maker_settings' );
        $value = isset( $settings['link_rel_attribute'] ) ? $settings['link_rel_attribute'] : 'nofollow';
        ?>
        <select name="wp_referral_link_maker_settings[link_rel_attribute]">
            <option value="nofollow" <?php selected( $value, 'nofollow' ); ?>><?php esc_html_e( 'nofollow', 'wp-referral-link-maker' ); ?></option>
            <option value="sponsored" <?php selected( $value, 'sponsored' ); ?>><?php esc_html_e( 'sponsored', 'wp-referral-link-maker' ); ?></option>
            <option value="nofollow sponsored" <?php selected( $value, 'nofollow sponsored' ); ?>><?php esc_html_e( 'nofollow sponsored', 'wp-referral-link-maker' ); ?></option>
            <option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'None', 'wp-referral-link-maker' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'The rel attribute to use for referral links (affects SEO).', 'wp-referral-link-maker' ); ?></p>
        <?php
    }
}

