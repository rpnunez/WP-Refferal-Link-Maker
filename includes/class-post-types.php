<?php
/**
 * Register custom post types
 *
 * @package    WP_Referral_Link_Maker
 * @subpackage WP_Referral_Link_Maker/includes
 */

/**
 * Register custom post types for the plugin.
 *
 * This class defines the custom post types used by the plugin:
 * - Referral Link Group
 * - Referral Link Maker
 */
class WP_Referral_Link_Maker_Post_Types {

    /**
     * Register custom post types.
     */
    public static function register_post_types() {
        self::register_referral_link_group();
        self::register_referral_link_maker();
    }

    /**
     * Register the Referral Link Group post type.
     *
     * Used to categorize referral links.
     */
    private static function register_referral_link_group() {
        $labels = array(
            'name'                  => _x( 'Referral Link Groups', 'Post Type General Name', 'wp-referral-link-maker' ),
            'singular_name'         => _x( 'Referral Link Group', 'Post Type Singular Name', 'wp-referral-link-maker' ),
            'menu_name'             => __( 'Link Groups', 'wp-referral-link-maker' ),
            'name_admin_bar'        => __( 'Referral Link Group', 'wp-referral-link-maker' ),
            'archives'              => __( 'Group Archives', 'wp-referral-link-maker' ),
            'attributes'            => __( 'Group Attributes', 'wp-referral-link-maker' ),
            'parent_item_colon'     => __( 'Parent Group:', 'wp-referral-link-maker' ),
            'all_items'             => __( 'All Groups', 'wp-referral-link-maker' ),
            'add_new_item'          => __( 'Add New Group', 'wp-referral-link-maker' ),
            'add_new'               => __( 'Add New', 'wp-referral-link-maker' ),
            'new_item'              => __( 'New Group', 'wp-referral-link-maker' ),
            'edit_item'             => __( 'Edit Group', 'wp-referral-link-maker' ),
            'update_item'           => __( 'Update Group', 'wp-referral-link-maker' ),
            'view_item'             => __( 'View Group', 'wp-referral-link-maker' ),
            'view_items'            => __( 'View Groups', 'wp-referral-link-maker' ),
            'search_items'          => __( 'Search Group', 'wp-referral-link-maker' ),
            'not_found'             => __( 'Not found', 'wp-referral-link-maker' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wp-referral-link-maker' ),
        );

        $args = array(
            'label'                 => __( 'Referral Link Group', 'wp-referral-link-maker' ),
            'description'           => __( 'Categorize referral links', 'wp-referral-link-maker' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'wp-referral-link-maker',
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type( 'ref_link_group', $args );
    }

    /**
     * Register the Referral Link Maker post type.
     *
     * Used to define/link AI automation settings.
     */
    private static function register_referral_link_maker() {
        $labels = array(
            'name'                  => _x( 'Referral Links', 'Post Type General Name', 'wp-referral-link-maker' ),
            'singular_name'         => _x( 'Referral Link', 'Post Type Singular Name', 'wp-referral-link-maker' ),
            'menu_name'             => __( 'Referral Links', 'wp-referral-link-maker' ),
            'name_admin_bar'        => __( 'Referral Link', 'wp-referral-link-maker' ),
            'archives'              => __( 'Link Archives', 'wp-referral-link-maker' ),
            'attributes'            => __( 'Link Attributes', 'wp-referral-link-maker' ),
            'parent_item_colon'     => __( 'Parent Link:', 'wp-referral-link-maker' ),
            'all_items'             => __( 'All Links', 'wp-referral-link-maker' ),
            'add_new_item'          => __( 'Add New Link', 'wp-referral-link-maker' ),
            'add_new'               => __( 'Add New', 'wp-referral-link-maker' ),
            'new_item'              => __( 'New Link', 'wp-referral-link-maker' ),
            'edit_item'             => __( 'Edit Link', 'wp-referral-link-maker' ),
            'update_item'           => __( 'Update Link', 'wp-referral-link-maker' ),
            'view_item'             => __( 'View Link', 'wp-referral-link-maker' ),
            'view_items'            => __( 'View Links', 'wp-referral-link-maker' ),
            'search_items'          => __( 'Search Link', 'wp-referral-link-maker' ),
            'not_found'             => __( 'Not found', 'wp-referral-link-maker' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wp-referral-link-maker' ),
        );

        $args = array(
            'label'                 => __( 'Referral Link', 'wp-referral-link-maker' ),
            'description'           => __( 'Define and manage referral links with AI automation', 'wp-referral-link-maker' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'custom-fields' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'wp-referral-link-maker',
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );

        register_post_type( 'ref_link_maker', $args );
    }
}
