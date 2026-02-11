<?php
// Date-based pages as actual WP Pages:
// /section/2025
// /section/2025/03
// /section/2025/03/child-page
//
// Put in functions.php or an included file. Then flush permalinks once.

add_action('init', function () {

    // Most specific first:
    // /section/YYYY/MM/child
    add_rewrite_rule(
        '^([a-z0-9-]+)/([0-9]{4})/([0-9]{1,2})/(.+)/?$',
        'index.php?pagename=$matches[1]/$matches[2]/$matches[3]/$matches[4]',
        'top'
    );

    // /section/YYYY/MM
    add_rewrite_rule(
        '^([a-z0-9-]+)/([0-9]{4})/([0-9]{1,2})/?$',
        'index.php?pagename=$matches[1]/$matches[2]/$matches[3]',
        'top'
    );

    // /section/YYYY
    add_rewrite_rule(
        '^([a-z0-9-]+)/([0-9]{4})/?$',
        'index.php?pagename=$matches[1]/$matches[2]',
        'top'
    );
}, 0);

// Prevent WP from “correcting” your numeric paths via canonical redirects
add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    if (preg_match('~/(?:[a-z0-9-]+)/[0-9]{4}(?:/[0-9]{1,2})?(?:/.+)?/?$~i', $requested_url)) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

// Preserve numeric slugs like 2025, 03 under parents (no -2/-3)
add_filter('wp_unique_post_slug', function (
    $slug,
    $post_ID,
    $post_status,
    $post_type,
    $post_parent,
    $original_slug
) {
    if ($post_type !== 'page') return $slug;
    if ((int)$post_parent === 0) return $slug;

    if (preg_match('/^\d+$/', (string)$original_slug)) {
        return (string)$original_slug;
    }
    return $slug;
}, 10, 6);
