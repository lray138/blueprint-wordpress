<?php

add_action('admin_init', function () {
    if (!is_admin()) return;

    global $pagenow;
    // Only the Pages list
    if ($pagenow !== 'edit.php') return;
    if (($_GET['post_type'] ?? '') !== 'page') return;

    // If parent_id already exists, respect it
    if (isset($_GET['post_parent'])) return;

    // Show every page in the hierarchy (no post_parent filter), e.g. for picking pages in meta boxes
    if (isset($_GET['all'])) return;

    // List search should not be forced through top-level-only; pre_get_posts clears parent when `s` is set
    if (isset($_GET['s']) && trim((string) $_GET['s']) !== '') return;

    // Redirect to top-level pages
    wp_redirect(add_query_arg('post_parent', 0));
    exit;
});

