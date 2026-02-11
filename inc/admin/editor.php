<?php

// Disable the classic editor when editing the universal page template.
add_action( 'admin_init', 'blueprint_admin_disable_editor_for_universal_page' );
function blueprint_admin_disable_editor_for_universal_page() {
    if ( ! is_admin() ) {
        return;
    }

    $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
    if ( ! $post_id ) {
        return;
    }

    $template = get_page_template_slug( $post_id );

    if ( in_array( $template, [
        'templates/universal-page.php', 
        'templates/partial-page.php',
        'templates/docs-page.php'
        ] ) ) {
        remove_post_type_support( 'page', 'editor' );
    }
}

// Disable the block editor for posts, and post types
add_filter( 'use_block_editor_for_post', 'blueprint_admin_disable_gutenberg', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'blueprint_admin_disable_gutenberg', 10, 2 );
function blueprint_admin_disable_gutenberg( $use_block_editor, $post ) {
    return false;
}

// Remove Gutenberg block CSS on the front end.
add_action( 'wp_enqueue_scripts', 'blueprint_admin_remove_block_css', 100 );
function blueprint_admin_remove_block_css() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' );
}


/// 

/**
 * Get the post ID being edited in wp-admin (works for edit screen + CF ajax).
 */
function crb_get_current_admin_post_id(): int {
    // Most reliable in edit.php/post.php
    if (isset($_GET['post'])) {
        return (int) $_GET['post'];
    }

    // Often present during AJAX requests
    if (isset($_POST['post_id'])) {
        return (int) $_POST['post_id'];
    }
    if (isset($_POST['post_ID'])) {
        return (int) $_POST['post_ID'];
    }

    // Fallback if $post is set
    $post = get_post();
    return $post ? (int) $post->ID : 0;
}

add_filter(
    'carbon_fields_association_field_options_partial_pages_post_page',
    function (array $query_arguments) {

        $current_id = crb_get_current_admin_post_id();
        
        if ($current_id) {  
            $query_arguments['post__not_in'] = [$current_id];
            //$query_arguments['post__in'] = [491];
        }

        // Optional: restrict templates if needed
        $query_arguments['meta_query'] = [
            [
                'key'     => '_wp_page_template',
                'value'   => [
                    'templates/partial.php',
                    'templates/form.php',
                ],
                'compare' => 'IN',
            ],
        ];

        // Order pages alphabetically by title
        // $query_arguments['orderby'] = 'title';
        // $query_arguments['order']   = 'DESC';

          return $query_arguments;

    },
    10,
    1
);
