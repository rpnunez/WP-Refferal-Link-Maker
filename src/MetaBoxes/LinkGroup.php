<?php
/**
 * Meta boxes for Referral Link Group post type
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\MetaBoxes;

/**
 * Handle meta boxes for Referral Link Group post type.
 */
class LinkGroup {

    /**
     * Initialize meta boxes.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
    }

    /**
     * Add meta boxes.
     */
    public function add_meta_boxes() {
        // Meta box for Referral Link Group
        add_meta_box(
            'wp_rlm_group_settings',
            __( 'Group Settings', 'wp-referral-link-maker' ),
            array( $this, 'render_group_settings_meta_box' ),
            'ref_link_group',
            'normal',
            'high'
        );
    }

    /**
     * Render group settings meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public function render_group_settings_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'wp_rlm_group_settings_nonce', 'wp_rlm_group_settings_nonce' );

        // Get saved values
        $color = get_post_meta( $post->ID, '_ref_group_color', true );
        $icon = get_post_meta( $post->ID, '_ref_group_icon', true );

        ?>
        <div class="wp-rlm-meta-box">
            <div class="form-field">
                <label for="ref_group_color"><?php esc_html_e( 'Group Color', 'wp-referral-link-maker' ); ?></label>
                <input type="text" id="ref_group_color" name="ref_group_color" value="<?php echo esc_attr( $color ? $color : '#0073aa' ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Hex color code for visual identification (e.g., #0073aa).', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_group_icon"><?php esc_html_e( 'Group Icon', 'wp-referral-link-maker' ); ?></label>
                <input type="text" id="ref_group_icon" name="ref_group_icon" value="<?php echo esc_attr( $icon ); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e( 'Dashicon class name (e.g., dashicons-star-filled).', 'wp-referral-link-maker' ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta box data.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_meta_boxes( $post_id, $post ) {
        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save Referral Link Group meta
        if ( $post->post_type === 'ref_link_group' ) {
            $this->save_group_settings( $post_id );
        }
    }

    /**
     * Save group settings.
     *
     * @param int $post_id Post ID.
     */
    private function save_group_settings( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['wp_rlm_group_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wp_rlm_group_settings_nonce'], 'wp_rlm_group_settings_nonce' ) ) {
            return;
        }

        // Save color
        if ( isset( $_POST['ref_group_color'] ) ) {
            $color = sanitize_hex_color( $_POST['ref_group_color'] );
            if ( $color ) {
                update_post_meta( $post_id, '_ref_group_color', $color );
            }
        }

        // Save icon
        if ( isset( $_POST['ref_group_icon'] ) ) {
            update_post_meta( $post_id, '_ref_group_icon', sanitize_text_field( $_POST['ref_group_icon'] ) );
        }
    }
}
