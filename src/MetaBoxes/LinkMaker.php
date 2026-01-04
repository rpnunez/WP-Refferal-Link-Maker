<?php
/**
 * Meta boxes for Referral Link Maker post type
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\MetaBoxes;

/**
 * Handle meta boxes for Referral Link Maker post type.
 */
class LinkMaker {

    /**
     * Register meta box hooks.
     */
    public static function register() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 10, 2 );
    }

    /**
     * Add meta boxes.
     */
    public static function add_meta_boxes() {
        // Meta box for Referral Link Maker
        add_meta_box(
            'wp_rlm_link_details',
            __( 'Referral Link Details', 'wp-referral-link-maker' ),
            array( __CLASS__, 'render_link_details_meta_box' ),
            'ref_link_maker',
            'normal',
            'high'
        );

        // Meta box for AI Settings
        add_meta_box(
            'wp_rlm_ai_settings',
            __( 'AI Automation Settings', 'wp-referral-link-maker' ),
            array( __CLASS__, 'render_ai_settings_meta_box' ),
            'ref_link_maker',
            'side',
            'default'
        );
    }

    /**
     * Render link details meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public static function render_link_details_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'wp_rlm_link_details_nonce', 'wp_rlm_link_details_nonce' );

        // Get saved values
        $keyword = get_post_meta( $post->ID, '_ref_link_keyword', true );
        $url = get_post_meta( $post->ID, '_ref_link_url', true );
        $group_id = get_post_meta( $post->ID, '_ref_link_group', true );
        $priority = get_post_meta( $post->ID, '_ref_link_priority', true );
        $max_insertions = get_post_meta( $post->ID, '_ref_link_max_insertions', true );

        // Get available groups
        $groups = get_posts( array(
            'post_type'      => 'ref_link_group',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );

        ?>
        <div class="wp-rlm-meta-box">
            <div class="form-field">
                <label for="ref_link_keyword"><?php esc_html_e( 'Keyword', 'wp-referral-link-maker' ); ?></label>
                <input type="text" id="ref_link_keyword" name="ref_link_keyword" value="<?php echo esc_attr( $keyword ); ?>" class="widefat" />
                <p class="description"><?php esc_html_e( 'The keyword or phrase to be linked in posts.', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_link_url"><?php esc_html_e( 'Referral URL', 'wp-referral-link-maker' ); ?></label>
                <input type="url" id="ref_link_url" name="ref_link_url" value="<?php echo esc_attr( $url ); ?>" class="widefat" />
                <p class="description"><?php esc_html_e( 'The full referral URL including tracking parameters.', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_link_group"><?php esc_html_e( 'Link Group', 'wp-referral-link-maker' ); ?></label>
                <select id="ref_link_group" name="ref_link_group" class="widefat">
                    <option value=""><?php esc_html_e( 'Select a group', 'wp-referral-link-maker' ); ?></option>
                    <?php foreach ( $groups as $group ) : ?>
                        <option value="<?php echo esc_attr( $group->ID ); ?>" <?php selected( $group_id, $group->ID ); ?>>
                            <?php echo esc_html( $group->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'Organize this link into a group for easier management.', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_link_priority"><?php esc_html_e( 'Priority', 'wp-referral-link-maker' ); ?></label>
                <input type="number" id="ref_link_priority" name="ref_link_priority" value="<?php echo esc_attr( $priority ? $priority : '10' ); ?>" class="small-text" min="0" max="100" />
                <p class="description"><?php esc_html_e( 'Higher priority links are inserted first (0-100).', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_link_max_insertions"><?php esc_html_e( 'Max Insertions Per Post', 'wp-referral-link-maker' ); ?></label>
                <input type="number" id="ref_link_max_insertions" name="ref_link_max_insertions" value="<?php echo esc_attr( $max_insertions ? $max_insertions : '3' ); ?>" class="small-text" min="1" max="10" />
                <p class="description"><?php esc_html_e( 'Maximum number of times to insert this link in a single post.', 'wp-referral-link-maker' ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render AI settings meta box.
     *
     * @param WP_Post $post Current post object.
     */
    public static function render_ai_settings_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'wp_rlm_ai_settings_nonce', 'wp_rlm_ai_settings_nonce' );

        // Get saved values
        $ai_enabled = get_post_meta( $post->ID, '_ref_link_ai_enabled', true );
        $ai_context = get_post_meta( $post->ID, '_ref_link_ai_context', true );

        ?>
        <div class="wp-rlm-meta-box">
            <div class="form-field">
                <label>
                    <input type="checkbox" name="ref_link_ai_enabled" value="1" <?php checked( $ai_enabled, '1' ); ?> />
                    <?php esc_html_e( 'Enable AI Automation', 'wp-referral-link-maker' ); ?>
                </label>
                <p class="description"><?php esc_html_e( 'Allow AI to automatically insert this link.', 'wp-referral-link-maker' ); ?></p>
            </div>

            <div class="form-field">
                <label for="ref_link_ai_context"><?php esc_html_e( 'AI Context', 'wp-referral-link-maker' ); ?></label>
                <textarea id="ref_link_ai_context" name="ref_link_ai_context" rows="5" class="widefat"><?php echo esc_textarea( $ai_context ); ?></textarea>
                <p class="description"><?php esc_html_e( 'Provide context for AI to understand when to use this link.', 'wp-referral-link-maker' ); ?></p>
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
    public static function save_meta_boxes( $post_id, $post ) {
        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save Referral Link Maker meta
        if ( $post->post_type === 'ref_link_maker' ) {
            self::save_link_details( $post_id );
            self::save_ai_settings( $post_id );
        }
    }

    /**
     * Save link details.
     *
     * @param int $post_id Post ID.
     */
    private static function save_link_details( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['wp_rlm_link_details_nonce'] ) || ! wp_verify_nonce( $_POST['wp_rlm_link_details_nonce'], 'wp_rlm_link_details_nonce' ) ) {
            return;
        }

        // Save keyword
        if ( isset( $_POST['ref_link_keyword'] ) ) {
            update_post_meta( $post_id, '_ref_link_keyword', sanitize_text_field( $_POST['ref_link_keyword'] ) );
        }

        // Save URL
        if ( isset( $_POST['ref_link_url'] ) ) {
            update_post_meta( $post_id, '_ref_link_url', esc_url_raw( $_POST['ref_link_url'] ) );
        }

        // Save group
        if ( isset( $_POST['ref_link_group'] ) ) {
            update_post_meta( $post_id, '_ref_link_group', absint( $_POST['ref_link_group'] ) );
        }

        // Save priority
        if ( isset( $_POST['ref_link_priority'] ) ) {
            update_post_meta( $post_id, '_ref_link_priority', absint( $_POST['ref_link_priority'] ) );
        }

        // Save max insertions
        if ( isset( $_POST['ref_link_max_insertions'] ) ) {
            update_post_meta( $post_id, '_ref_link_max_insertions', absint( $_POST['ref_link_max_insertions'] ) );
        }
    }

    /**
     * Save AI settings.
     *
     * @param int $post_id Post ID.
     */
    private static function save_ai_settings( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['wp_rlm_ai_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wp_rlm_ai_settings_nonce'], 'wp_rlm_ai_settings_nonce' ) ) {
            return;
        }

        // Save AI enabled
        if ( isset( $_POST['ref_link_ai_enabled'] ) ) {
            update_post_meta( $post_id, '_ref_link_ai_enabled', '1' );
        } else {
            delete_post_meta( $post_id, '_ref_link_ai_enabled' );
        }

        // Save AI context
        if ( isset( $_POST['ref_link_ai_context'] ) ) {
            update_post_meta( $post_id, '_ref_link_ai_context', sanitize_textarea_field( $_POST['ref_link_ai_context'] ) );
        }
    }
}
