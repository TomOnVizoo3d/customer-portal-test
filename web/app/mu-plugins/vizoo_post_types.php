<?php

if (!defined('ABSPATH')) {
    exit;
}

function vizoo_add_custom_post_types()
{
    vizoo_add_video_tutorial_post_type();
    vizoo_add_faq_post_type();
}

function vizoo_add_video_tutorial_post_type()
{
    $labels = array(
        'name' => 'Video Tutorials',
        'singular_name' => 'Video Tutorial',
        'menu_name' => 'Video Tutorials',
        'all_items' => 'All Video Tutorials',
        'view_item' => 'View Video Tutorial',
        'add_new_item' => 'Add New Video Tutorial',
        'add_new' => 'Add New',
        'edit_item' => 'Edit Video Tutorial',
        'update_item' => 'Update Video Tutorial',
        'search_items' => 'Search Video Tutorials',
        'not_found' => 'Not Found',
        'not_found_in_trash' => 'Not Found in Trash',
    );

    $args = array(
        'label' => 'videotutorial',
        'description' => 'Video tutorials explaining xTex.',
        'labels' => $labels,
        'supports' => array('title', 'revisions', 'excerpt'),
        'taxonomies' => array('category'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-video-alt3',
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
        'rewrite' => array('slug' => 'tutorials'),
    );

    register_post_type('vizoo_videotutorial', $args);
}

function vizoo_add_faq_post_type()
{
    $labels = array(
        'name' => 'FAQs',
        'singular_name' => 'FAQ',
        'menu_name' => 'FAQs',
        'all_items' => 'All FAQs',
        'view_item' => 'View FAQ',
        'add_new_item' => 'Add New FAQ',
        'add_new' => 'Add New',
        'edit_item' => 'Edit FAQ',
        'update_item' => 'Update FAQ',
        'search_items' => 'Search FAQs',
        'not_found' => 'Not Found',
        'not_found_in_trash' => 'Not Found in Trash',
    );

    $args = array(
        'label' => 'faq',
        'description' => 'FAQ: Frequently asked question.',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'revisions'),
        'taxonomies' => array('category'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-info',
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
        'rewrite' => array('slug' => 'faqs'),
    );

    register_post_type('vizoo_faq', $args);
}

/**
 * Add custom post types after initialization. Using priority 15 to be after category
 * setup, so that the registration of the category taxonomy works correctly.
 */
add_action('init', 'vizoo_add_custom_post_types', /* priority: */ 15);
