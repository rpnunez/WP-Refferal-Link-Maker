<?php
/**
 * Referral Link Group post type
 *
 * @package    NunezReferralEngine
 */

namespace NunezReferralEngine\PostTypes;

/**
 * Register the Referral Link Group post type.
 *
 * Used to categorize referral links.
 */
class LinkGroup {

    /**
     * Register the post type.
     */
    public static function register() {
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
}
