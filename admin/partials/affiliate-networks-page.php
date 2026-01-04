<?php
/**
 * Provide affiliate networks settings page view
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

$affiliate_settings = get_option( 'wp_referral_link_maker_affiliate_networks', array() );
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Affiliate Network Settings', 'wp-referral-link-maker' ); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'wp_referral_link_maker_affiliate_networks' );
        ?>

        <h2><?php esc_html_e( 'Amazon Associates API', 'wp-referral-link-maker' ); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="amazon_access_key"><?php esc_html_e( 'Access Key', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="text" id="amazon_access_key" name="wp_referral_link_maker_affiliate_networks[amazon][access_key]" value="<?php echo esc_attr( isset( $affiliate_settings['amazon']['access_key'] ) ? $affiliate_settings['amazon']['access_key'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your Amazon Product Advertising API Access Key.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="amazon_secret_key"><?php esc_html_e( 'Secret Key', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="password" id="amazon_secret_key" name="wp_referral_link_maker_affiliate_networks[amazon][secret_key]" value="<?php echo esc_attr( isset( $affiliate_settings['amazon']['secret_key'] ) ? $affiliate_settings['amazon']['secret_key'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your Amazon Product Advertising API Secret Key.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="amazon_associate_tag"><?php esc_html_e( 'Associate Tag', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="text" id="amazon_associate_tag" name="wp_referral_link_maker_affiliate_networks[amazon][associate_tag]" value="<?php echo esc_attr( isset( $affiliate_settings['amazon']['associate_tag'] ) ? $affiliate_settings['amazon']['associate_tag'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your Amazon Associates tracking ID/tag.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="amazon_region"><?php esc_html_e( 'Region', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <?php $current_region = isset( $affiliate_settings['amazon']['region'] ) ? $affiliate_settings['amazon']['region'] : 'us-east-1'; ?>
                    <select id="amazon_region" name="wp_referral_link_maker_affiliate_networks[amazon][region]">
                        <option value="us-east-1" <?php selected( $current_region, 'us-east-1' ); ?>><?php esc_html_e( 'US East (US)', 'wp-referral-link-maker' ); ?></option>
                        <option value="us-west-2" <?php selected( $current_region, 'us-west-2' ); ?>><?php esc_html_e( 'US West (US)', 'wp-referral-link-maker' ); ?></option>
                        <option value="eu-west-1" <?php selected( $current_region, 'eu-west-1' ); ?>><?php esc_html_e( 'EU West (UK)', 'wp-referral-link-maker' ); ?></option>
                        <option value="eu-central-1" <?php selected( $current_region, 'eu-central-1' ); ?>><?php esc_html_e( 'EU Central (Germany)', 'wp-referral-link-maker' ); ?></option>
                        <option value="ap-northeast-1" <?php selected( $current_region, 'ap-northeast-1' ); ?>><?php esc_html_e( 'Asia Pacific (Japan)', 'wp-referral-link-maker' ); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select the AWS region for your Amazon API.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'ShareASale API', 'wp-referral-link-maker' ); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="shareasale_affiliate_id"><?php esc_html_e( 'Affiliate ID', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="text" id="shareasale_affiliate_id" name="wp_referral_link_maker_affiliate_networks[shareasale][affiliate_id]" value="<?php echo esc_attr( isset( $affiliate_settings['shareasale']['affiliate_id'] ) ? $affiliate_settings['shareasale']['affiliate_id'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your ShareASale Affiliate ID.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shareasale_api_token"><?php esc_html_e( 'API Token', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="text" id="shareasale_api_token" name="wp_referral_link_maker_affiliate_networks[shareasale][api_token]" value="<?php echo esc_attr( isset( $affiliate_settings['shareasale']['api_token'] ) ? $affiliate_settings['shareasale']['api_token'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your ShareASale API Token.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shareasale_api_secret"><?php esc_html_e( 'API Secret', 'wp-referral-link-maker' ); ?></label>
                </th>
                <td>
                    <input type="password" id="shareasale_api_secret" name="wp_referral_link_maker_affiliate_networks[shareasale][api_secret]" value="<?php echo esc_attr( isset( $affiliate_settings['shareasale']['api_secret'] ) ? $affiliate_settings['shareasale']['api_secret'] : '' ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your ShareASale API Secret Key.', 'wp-referral-link-maker' ); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button( __( 'Save Affiliate Network Settings', 'wp-referral-link-maker' ) ); ?>
    </form>

    <div class="wp-rlm-info-box" style="margin-top: 30px;">
        <h3><?php esc_html_e( 'How to Get API Credentials', 'wp-referral-link-maker' ); ?></h3>
        
        <h4><?php esc_html_e( 'Amazon Product Advertising API', 'wp-referral-link-maker' ); ?></h4>
        <ol>
            <li><?php esc_html_e( 'Sign up for Amazon Associates program at affiliate-program.amazon.com', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Apply for Product Advertising API access', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Get your Access Key and Secret Key from the AWS Console', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Find your Associate Tag in your Amazon Associates account', 'wp-referral-link-maker' ); ?></li>
        </ol>
        <p>
            <a href="https://affiliate-program.amazon.com/help/node/topic/G201825840" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Amazon API Documentation', 'wp-referral-link-maker' ); ?>
            </a>
        </p>

        <h4><?php esc_html_e( 'ShareASale API', 'wp-referral-link-maker' ); ?></h4>
        <ol>
            <li><?php esc_html_e( 'Sign up for ShareASale affiliate program at shareasale.com', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Log in to your affiliate account', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Navigate to Tools > API in your dashboard', 'wp-referral-link-maker' ); ?></li>
            <li><?php esc_html_e( 'Generate your API Token and API Secret', 'wp-referral-link-maker' ); ?></li>
        </ol>
        <p>
            <a href="https://account.shareasale.com/a-apicodev.cfm" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'ShareASale API Documentation', 'wp-referral-link-maker' ); ?>
            </a>
        </p>
    </div>
</div>
