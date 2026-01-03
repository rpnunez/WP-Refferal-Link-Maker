<?php
/**
 * Provide a settings page view for the plugin
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wp_referral_link_maker_settings' );
        do_settings_sections( 'wp-referral-link-maker-settings' );
        submit_button( __( 'Save Settings', 'wp-referral-link-maker' ) );
        ?>
    </form>

    <div class="wp-rlm-info-box">
        <h3><?php esc_html_e( 'About AI Engine Integration', 'wp-referral-link-maker' ); ?></h3>
        <p><?php esc_html_e( 'This plugin integrates with the Meow Apps AI Engine plugin to provide intelligent referral link insertion. Make sure you have the AI Engine plugin installed and activated.', 'wp-referral-link-maker' ); ?></p>
        <p>
            <a href="https://wordpress.org/plugins/ai-engine/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Learn more about AI Engine', 'wp-referral-link-maker' ); ?>
            </a>
        </p>
    </div>
</div>
