<?php

/**
 * Clone Carbon Page (Theme feature)
 * - Adds "Clone (Carbon)" action for Pages in wp-admin
 * - Clones page content + ALL postmeta (Carbon Fields lives in postmeta)
 */

if (!defined('ABSPATH')) exit;

/**
 * Clone a post/page including ALL postmeta rows (Carbon Fields safe),
 * plus featured image, template, and taxonomy terms.
 *
 * @return int|WP_Error New post ID or WP_Error
 */
function clone_page_with_carbon(int $post_id, array $args = []) {
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('clone_not_found', 'Post not found.');
    }

    $defaults = [
        'post_status' => 'draft',
        'post_title'  => $post->post_title . ' (Copy)',
        'post_author' => get_current_user_id() ?: (int)$post->post_author,
        'copy_terms'  => true,
        'copy_meta'   => true,
        'copy_thumbnail' => true,
        'exclude_meta' => [
            '_edit_lock',
            '_edit_last',
            '_wp_old_slug',
        ],
    ];

    $args = array_merge($defaults, $args);

    // Insert cloned post
    $new_id = wp_insert_post([
        'post_type'      => $post->post_type,
        'post_status'    => $args['post_status'],
        'post_title'     => $args['post_title'],
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_author'    => (int)$args['post_author'],
        'post_parent'    => (int)$post->post_parent,
        'menu_order'     => (int)$post->menu_order,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
    ], true);

    if (is_wp_error($new_id)) {
        return $new_id;
    }

    // Copy page template
    $template = get_page_template_slug($post_id);
    if ($template) {
        update_post_meta($new_id, '_wp_page_template', $template);
    }

    // Copy featured image
    if (!empty($args['copy_thumbnail'])) {
        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            set_post_thumbnail($new_id, $thumb_id);
        }
    }

    // Copy taxonomy terms
    if (!empty($args['copy_terms'])) {
        $taxes = get_object_taxonomies($post->post_type);
        foreach ($taxes as $tax) {
            $terms = wp_get_object_terms($post_id, $tax, ['fields' => 'ids']);
            if (!is_wp_error($terms)) {
                wp_set_object_terms($new_id, $terms, $tax, false);
            }
        }
    }

    // Copy ALL meta rows (Carbon Fields data is stored here)
    if (!empty($args['copy_meta'])) {
        global $wpdb;

        $exclude = array_flip($args['exclude_meta']);

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value
                 FROM {$wpdb->postmeta}
                 WHERE post_id = %d",
                $post_id
            ),
            ARRAY_A
        );

        foreach ($rows as $row) {
            $key = $row['meta_key'];

            if (isset($exclude[$key])) {
                continue;
            }

            // Preserve multi-row keys (important for complex fields)
            add_post_meta($new_id, $key, maybe_unserialize($row['meta_value']));
        }
    }

    return (int)$new_id;
}

/**
 * Add a "Clone (Carbon)" action link in Pages list.
 */
add_filter('page_row_actions', function(array $actions, WP_Post $post) {
    if (!current_user_can('edit_pages')) return $actions;

    // Only show for 'page' post type (page_row_actions should already be pages, but just in case)
    if ($post->post_type !== 'page') return $actions;

    $url = wp_nonce_url(
        admin_url('admin-post.php?action=clone_carbon_page&post_id=' . (int)$post->ID),
        'clone_carbon_page_' . (int)$post->ID
    );

    $actions['clone_carbon_page'] = '<a href="' . esc_url($url) . '">Clone (Carbon)</a>';

    return $actions;
}, 10, 2);

/**
 * Handle the admin action.
 */
add_action('admin_post_clone_carbon_page', function() {
    if (!current_user_can('edit_pages')) {
        wp_die('You do not have permission to clone pages.');
    }

    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    if (!$post_id) {
        wp_die('Missing post_id.');
    }

    check_admin_referer('clone_carbon_page_' . $post_id);

    $new_id = clone_page_with_carbon($post_id);

    if (is_wp_error($new_id)) {
        wp_die(esc_html($new_id->get_error_message()));
    }

    wp_safe_redirect(get_edit_post_link((int)$new_id, ''));
    exit;
});
