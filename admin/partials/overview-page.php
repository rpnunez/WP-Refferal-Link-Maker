<?php
/**
 * Provide an overview page view for the plugin
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

    <div class="wp-rlm-overview">
        <h2><?php esc_html_e( 'Statistics', 'wp-referral-link-maker' ); ?></h2>

        <div class="wp-rlm-stats">
            <div class="wp-rlm-stat-box">
                <div class="wp-rlm-stat-number"><?php echo esc_html( $stats['link_groups'] ); ?></div>
                <div class="wp-rlm-stat-label"><?php esc_html_e( 'Referral Link Groups', 'wp-referral-link-maker' ); ?></div>
            </div>

            <div class="wp-rlm-stat-box">
                <div class="wp-rlm-stat-number"><?php echo esc_html( $stats['referral_links'] ); ?></div>
                <div class="wp-rlm-stat-label"><?php esc_html_e( 'Referral Links', 'wp-referral-link-maker' ); ?></div>
            </div>

            <div class="wp-rlm-stat-box">
                <div class="wp-rlm-stat-number"><?php echo esc_html( $stats['processed_posts'] ); ?></div>
                <div class="wp-rlm-stat-label"><?php esc_html_e( 'Processed Posts', 'wp-referral-link-maker' ); ?></div>
            </div>
        </div>

        <h2><?php esc_html_e( 'Referral Link Global Values', 'wp-referral-link-maker' ); ?></h2>

        <form method="post" action="">
            <?php wp_nonce_field( 'wp_rlm_global_values' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_group"><?php esc_html_e( 'Default Group', 'wp-referral-link-maker' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $groups = get_posts( array(
                            'post_type'      => 'ref_link_group',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                        ) );
                        ?>
                        <select name="default_group" id="default_group" class="regular-text">
                            <option value=""><?php esc_html_e( 'Select a group', 'wp-referral-link-maker' ); ?></option>
                            <?php foreach ( $groups as $group ) : ?>
                                <option value="<?php echo esc_attr( $group->ID ); ?>" <?php selected( isset( $global_values['default_group'] ) ? $global_values['default_group'] : '', $group->ID ); ?>>
                                    <?php echo esc_html( $group->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default referral link group for new links.', 'wp-referral-link-maker' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="global_link_prefix"><?php esc_html_e( 'Global Link Prefix', 'wp-referral-link-maker' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="global_link_prefix" id="global_link_prefix" value="<?php echo esc_attr( isset( $global_values['global_link_prefix'] ) ? $global_values['global_link_prefix'] : '' ); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Text to prepend to all referral links (e.g., tracking parameters).', 'wp-referral-link-maker' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="global_link_suffix"><?php esc_html_e( 'Global Link Suffix', 'wp-referral-link-maker' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="global_link_suffix" id="global_link_suffix" value="<?php echo esc_attr( isset( $global_values['global_link_suffix'] ) ? $global_values['global_link_suffix'] : '' ); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Text to append to all referral links (e.g., tracking parameters).', 'wp-referral-link-maker' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Global Values', 'wp-referral-link-maker' ), 'primary', 'wp_rlm_save_global_values' ); ?>
        </form>

        <h2><?php esc_html_e( 'Quick Actions', 'wp-referral-link-maker' ); ?></h2>

        <div class="wp-rlm-quick-actions">
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ref_link_group' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Add New Link Group', 'wp-referral-link-maker' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ref_link_maker' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Add New Referral Link', 'wp-referral-link-maker' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-referral-link-maker-settings' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Configure Settings', 'wp-referral-link-maker' ); ?>
            </a>
        </div>
    </div>
</div>
