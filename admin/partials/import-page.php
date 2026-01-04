<?php
/**
 * Provide an import page view for the plugin
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once WP_REFERRAL_LINK_MAKER_PLUGIN_DIR . 'includes/class-affiliate-api-manager.php';
$manager = new WP_Referral_Link_Maker_Affiliate_API_Manager();
$networks = $manager->get_available_networks();
$affiliate_settings = get_option( 'wp_referral_link_maker_affiliate_networks', array() );
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( isset( $import_result ) && ! is_wp_error( $import_result ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                printf(
                    esc_html__( 'Import completed: %d links imported successfully, %d skipped (already exist), %d failed.', 'wp-referral-link-maker' ),
                    intval( $import_result['success'] ),
                    intval( $import_result['skipped'] ),
                    intval( $import_result['failed'] )
                );
                ?>
            </p>
        </div>
    <?php elseif ( isset( $import_result ) && is_wp_error( $import_result ) ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $import_result->get_error_message() ); ?></p>
        </div>
    <?php endif; ?>

    <div class="wp-rlm-import">
        <p><?php esc_html_e( 'Import affiliate links directly from your affiliate network accounts. Make sure you have configured your API credentials in the Settings page first.', 'wp-referral-link-maker' ); ?></p>

        <?php foreach ( $networks as $network_key => $network_name ) : ?>
            <?php $is_configured = $manager->is_network_configured( $network_key ); ?>
            
            <div class="wp-rlm-import-network postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">
                        <?php echo esc_html( $network_name ); ?>
                        <?php if ( ! $is_configured ) : ?>
                            <span class="wp-rlm-not-configured" style="color: #d63638; font-weight: normal; font-size: 13px;">
                                (<?php esc_html_e( 'Not Configured', 'wp-referral-link-maker' ); ?>)
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="inside">
                    <?php if ( ! $is_configured ) : ?>
                        <p>
                            <?php
                            printf(
                                /* translators: %s: Network name */
                                esc_html__( 'Please configure your %s API credentials in the Settings page before importing.', 'wp-referral-link-maker' ),
                                esc_html( $network_name )
                            );
                            ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-referral-link-maker-settings' ) ); ?>" class="button button-secondary">
                                <?php esc_html_e( 'Configure Settings', 'wp-referral-link-maker' ); ?>
                            </a>
                        </p>
                    <?php else : ?>
                        <form method="post" action="">
                            <?php wp_nonce_field( 'wp_rlm_import_links' ); ?>
                            <input type="hidden" name="network" value="<?php echo esc_attr( $network_key ); ?>" />

                            <table class="form-table">
                                <?php if ( $network_key === 'amazon' ) : ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="amazon_keywords"><?php esc_html_e( 'Search Keywords', 'wp-referral-link-maker' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" id="amazon_keywords" name="amazon_keywords" class="regular-text" required />
                                            <p class="description"><?php esc_html_e( 'Enter keywords to search for products on Amazon.', 'wp-referral-link-maker' ); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="limit"><?php esc_html_e( 'Number of Links', 'wp-referral-link-maker' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" id="limit" name="limit" value="10" min="1" max="100" class="small-text" />
                                            <p class="description"><?php esc_html_e( 'Maximum number of products to import (1-100).', 'wp-referral-link-maker' ); ?></p>
                                        </td>
                                    </tr>
                                <?php elseif ( $network_key === 'shareasale' ) : ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="merchant_id"><?php esc_html_e( 'Merchant ID', 'wp-referral-link-maker' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" id="merchant_id" name="merchant_id" class="regular-text" />
                                            <p class="description"><?php esc_html_e( 'Leave empty to import from all active merchants, or enter a specific merchant ID.', 'wp-referral-link-maker' ); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="limit"><?php esc_html_e( 'Number of Links', 'wp-referral-link-maker' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" id="limit" name="limit" value="50" min="1" max="500" class="small-text" />
                                            <p class="description"><?php esc_html_e( 'Maximum number of links to import (1-500).', 'wp-referral-link-maker' ); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>

                            <?php submit_button( __( 'Import Links', 'wp-referral-link-maker' ), 'primary', 'wp_rlm_import_links' ); ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="wp-rlm-import-info postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e( 'About Affiliate Network Integration', 'wp-referral-link-maker' ); ?></h2>
            </div>
            <div class="inside">
                <h3><?php esc_html_e( 'Amazon Associates', 'wp-referral-link-maker' ); ?></h3>
                <p><?php esc_html_e( 'Requires Amazon Product Advertising API credentials (Access Key, Secret Key, and Associate Tag).', 'wp-referral-link-maker' ); ?></p>
                <p>
                    <a href="https://affiliate-program.amazon.com/help/node/topic/G201825840" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Learn how to get Amazon API credentials', 'wp-referral-link-maker' ); ?>
                    </a>
                </p>

                <h3><?php esc_html_e( 'ShareASale', 'wp-referral-link-maker' ); ?></h3>
                <p><?php esc_html_e( 'Requires ShareASale API credentials (Affiliate ID, API Token, and API Secret).', 'wp-referral-link-maker' ); ?></p>
                <p>
                    <a href="https://account.shareasale.com/a-apicodev.cfm" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Learn how to get ShareASale API credentials', 'wp-referral-link-maker' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
