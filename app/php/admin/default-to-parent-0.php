<?php

add_action('admin_init', function () {
    if (!is_admin()) return;

    global $pagenow;
    // Only the Pages list
    if ($pagenow !== 'edit.php') return;
    if (($_GET['post_type'] ?? '') !== 'page') return;

    // If parent_id already exists, respect it
    if (isset($_GET['post_parent'])) return;

    // Redirect to top-level pages
    wp_redirect(add_query_arg('post_parent', 0));
    exit;
});

