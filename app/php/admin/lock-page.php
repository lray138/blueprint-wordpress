<?php
/**
 * Plugin Name: Page Lock (Hard Lock + Break Lock)
 * Description: Adds a Lock / Break Lock control for Pages using post meta _locked=1.
 * Version: 1.0.1
 */

if (!defined('ABSPATH')) exit;

/* ---------------------------------------------------------
 * Helpers
 * --------------------------------------------------------- */
function pl_is_locked_post($post_id): bool {
    return (bool) get_post_meta($post_id, '_locked', true);
}

/* ---------------------------------------------------------
 * Page Lock Meta Box
 * --------------------------------------------------------- */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'pl_page_lock_meta',
        'Page Lock',
        function ($post) {
            $locked = pl_is_locked_post($post->ID);

            if ($locked) {
                echo '<p><strong>🔒 This page is locked.</strong></p>';

                $url = wp_nonce_url(
                    admin_url('admin-post.php?action=pl_break_page_lock&post_id=' . (int) $post->ID),
                    'pl_break_page_lock_' . (int) $post->ID
                );

                echo '<p>
                    <a class="button button-primary" href="' . esc_url($url) . '">
                        🔓 Break Lock
                    </a>
                </p>';
                echo '<p class="description">Unlocks immediately. No save required.</p>';
            } else {
                wp_nonce_field('pl_page_lock_nonce', 'pl_page_lock_nonce');

                echo '<label style="display:flex;gap:8px;align-items:center;">';
                echo '<input type="checkbox" name="_locked" value="1">';
                echo '<strong>Lock this page (read-only)</strong>';
                echo '</label>';
                echo '<p class="description">Check and click Update to lock.</p>';
            }
        },
        'page',
        'side',
        'high'
    );
});

/* ---------------------------------------------------------
 * Save lock state (only applies when not locked yet)
 * --------------------------------------------------------- */
add_action('save_post_page', function ($post_id) {
    if (!isset($_POST['pl_page_lock_nonce']) ||
        !wp_verify_nonce($_POST['pl_page_lock_nonce'], 'pl_page_lock_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_page', $post_id)) return;

    if (!empty($_POST['_locked'])) {
        update_post_meta($post_id, '_locked', '1');
    } else {
        delete_post_meta($post_id, '_locked');
    }
});

/* ---------------------------------------------------------
 * Break Lock handler (no save required)
 * --------------------------------------------------------- */
add_action('admin_post_pl_break_page_lock', function () {
    $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
    if (!$post_id) wp_die('Missing post_id', 400);

    if (!current_user_can('edit_page', $post_id)) wp_die('Forbidden', 403);

    check_admin_referer('pl_break_page_lock_' . $post_id);

    delete_post_meta($post_id, '_locked');

    wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
    exit;
});

/* ---------------------------------------------------------
 * Hard Lock Enforcement
 * --------------------------------------------------------- */

/* Block edit/delete capabilities */
add_filter('user_has_cap', function ($allcaps, $caps, $args) {
    $requested = $args[0] ?? '';
    $post_id   = isset($args[2]) ? (int) $args[2] : 0;
    if (!$post_id) return $allcaps;

    if (!in_array($requested, ['edit_post','delete_post','edit_page','delete_page'], true)) {
        return $allcaps;
    }

    if (pl_is_locked_post($post_id)) {
        $allcaps[$requested] = false;
    }

    return $allcaps;
}, 10, 3);

/* Block saving */
add_filter('wp_insert_post_data', function ($data, $postarr) {
    $post_id = (int) ($postarr['ID'] ?? 0);
    if ($post_id && pl_is_locked_post($post_id)) {
        wp_die(
            'This page is locked. Use "Break Lock" in the Page Lock panel.',
            403
        );
    }
    return $data;
}, 10, 2);

/* Block REST updates (Block Editor) */
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if (!in_array($request->get_method(), ['POST','PUT','PATCH'], true)) {
        return $result;
    }

    if (!preg_match('#/wp/v2/(posts|pages)/(\d+)$#', $request->get_route(), $m)) {
        return $result;
    }

    $post_id = (int) $m[2];
    if ($post_id && pl_is_locked_post($post_id)) {
        return new WP_Error(
            'locked_post',
            'This page is locked. Use "Break Lock" to unlock.',
            ['status' => 403]
        );
    }

    return $result;
}, 10, 3);

/* Hide Publish / Update buttons on locked pages */
add_action('admin_head-post.php', function () {
    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (!$post_id || !pl_is_locked_post($post_id)) return;

    echo '<style>
        #publish,
        #save-post,
        #minor-publishing-actions,
        #major-publishing-actions,
        .editor-post-publish-button,
        .editor-post-save-draft,
        .editor-post-publish-panel__toggle {
            display:none !important;
        }
    </style>';
});
