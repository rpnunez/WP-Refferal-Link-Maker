<?php
/**
 * Provide an analytics page view for the plugin
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

    <div class="wp-rlm-analytics">
        <h2><?php esc_html_e( 'Overall Statistics', 'wp-referral-link-maker' ); ?></h2>

        <div class="wp-rlm-stats">
            <div class="wp-rlm-stat-box">
                <div class="wp-rlm-stat-number"><?php echo esc_html( $analytics_data['total_clicks'] ); ?></div>
                <div class="wp-rlm-stat-label"><?php esc_html_e( 'Total Clicks', 'wp-referral-link-maker' ); ?></div>
            </div>

            <div class="wp-rlm-stat-box">
                <div class="wp-rlm-stat-number"><?php echo esc_html( $analytics_data['unique_users'] ); ?></div>
                <div class="wp-rlm-stat-label"><?php esc_html_e( 'Unique Visitors', 'wp-referral-link-maker' ); ?></div>
            </div>
        </div>

        <h2><?php esc_html_e( 'Top Performing Links', 'wp-referral-link-maker' ); ?></h2>

        <?php if ( ! empty( $analytics_data['clicks_by_link'] ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Referral Link', 'wp-referral-link-maker' ); ?></th>
                        <th><?php esc_html_e( 'Keyword', 'wp-referral-link-maker' ); ?></th>
                        <th><?php esc_html_e( 'URL', 'wp-referral-link-maker' ); ?></th>
                        <th><?php esc_html_e( 'Click Count', 'wp-referral-link-maker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $analytics_data['clicks_by_link'] as $link_data ) : ?>
                        <?php
                        $link_post = get_post( $link_data->referral_link_id );
                        $keyword = get_post_meta( $link_data->referral_link_id, '_ref_link_keyword', true );
                        $url = get_post_meta( $link_data->referral_link_id, '_ref_link_url', true );
                        ?>
                        <tr>
                            <td>
                                <?php if ( $link_post ) : ?>
                                    <a href="<?php echo esc_url( get_edit_post_link( $link_data->referral_link_id ) ); ?>">
                                        <?php echo esc_html( $link_post->post_title ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php esc_html_e( 'Unknown Link', 'wp-referral-link-maker' ); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $keyword ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html( wp_trim_words( $url, 8, '...' ) ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $link_data->click_count ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'No click data available yet.', 'wp-referral-link-maker' ); ?></p>
        <?php endif; ?>

        <h2><?php esc_html_e( 'Clicks Over Time (Last 30 Days)', 'wp-referral-link-maker' ); ?></h2>

        <?php if ( ! empty( $analytics_data['clicks_by_date'] ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'wp-referral-link-maker' ); ?></th>
                        <th><?php esc_html_e( 'Click Count', 'wp-referral-link-maker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $analytics_data['clicks_by_date'] as $date_data ) : ?>
                        <tr>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date_data->date ) ) ); ?></td>
                            <td><?php echo esc_html( $date_data->click_count ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'No click data available for the last 30 days.', 'wp-referral-link-maker' ); ?></p>
        <?php endif; ?>

        <h2><?php esc_html_e( 'Individual Link Analytics', 'wp-referral-link-maker' ); ?></h2>

        <form method="get" action="">
            <input type="hidden" name="page" value="wp-referral-link-maker-analytics" />
            <?php
            $selected_link = isset( $_GET['link_id'] ) ? absint( $_GET['link_id'] ) : 0;
            
            // Validate that the selected link exists and is a referral link
            if ( $selected_link > 0 ) {
                $link_post = get_post( $selected_link );
                if ( ! $link_post || $link_post->post_type !== 'ref_link_maker' || $link_post->post_status !== 'publish' ) {
                    $selected_link = 0; // Reset if invalid
                }
            }
            
            $referral_links = get_posts( array(
                'post_type'      => 'ref_link_maker',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            ?>
            <select name="link_id" id="link_id">
                <option value=""><?php esc_html_e( 'Select a link', 'wp-referral-link-maker' ); ?></option>
                <?php foreach ( $referral_links as $link ) : ?>
                    <option value="<?php echo esc_attr( $link->ID ); ?>" <?php selected( $selected_link, $link->ID ); ?>>
                        <?php echo esc_html( $link->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php submit_button( __( 'View Analytics', 'wp-referral-link-maker' ), 'secondary', 'submit', false ); ?>
        </form>

        <?php if ( $selected_link ) : ?>
            <?php
            $link_analytics = WP_Referral_Link_Maker_Analytics::get_link_analytics( $selected_link );
            $link_post = get_post( $selected_link );
            ?>

            <h3><?php echo esc_html( sprintf( __( 'Analytics for: %s', 'wp-referral-link-maker' ), $link_post->post_title ) ); ?></h3>

            <div class="wp-rlm-stats">
                <div class="wp-rlm-stat-box">
                    <div class="wp-rlm-stat-number"><?php echo esc_html( $link_analytics['total_clicks'] ); ?></div>
                    <div class="wp-rlm-stat-label"><?php esc_html_e( 'Total Clicks', 'wp-referral-link-maker' ); ?></div>
                </div>

                <div class="wp-rlm-stat-box">
                    <div class="wp-rlm-stat-number"><?php echo esc_html( $link_analytics['unique_users'] ); ?></div>
                    <div class="wp-rlm-stat-label"><?php esc_html_e( 'Unique Visitors', 'wp-referral-link-maker' ); ?></div>
                </div>
            </div>

            <?php if ( ! empty( $link_analytics['recent_clicks'] ) ) : ?>
                <h4><?php esc_html_e( 'Recent Clicks', 'wp-referral-link-maker' ); ?></h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date & Time', 'wp-referral-link-maker' ); ?></th>
                            <th><?php esc_html_e( 'Post', 'wp-referral-link-maker' ); ?></th>
                            <th><?php esc_html_e( 'User IP', 'wp-referral-link-maker' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $link_analytics['recent_clicks'] as $click ) : ?>
                            <tr>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $click->click_time ) ) ); ?></td>
                                <td>
                                    <?php if ( $click->post_id ) : ?>
                                        <a href="<?php echo esc_url( get_edit_post_link( $click->post_id ) ); ?>">
                                            <?php echo esc_html( get_the_title( $click->post_id ) ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php esc_html_e( 'N/A', 'wp-referral-link-maker' ); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $click->user_ip ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'No clicks recorded for this link yet.', 'wp-referral-link-maker' ); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
