<?php 

require_once get_template_directory() . '/app/php/admin/new-page-auto-child.php';
require_once get_template_directory() . '/app/php/admin/default-to-parent-0.php';
require_once get_template_directory() . '/app/php/admin/lock-page.php';

// add below 
function getPageConfig(int $current_page_id): ?WP_Post
{
    // Normalize revision/autosave IDs to the real page ID
    $real_id = wp_is_post_revision($current_page_id) ?: $current_page_id;
    $real_id = wp_is_post_autosave($real_id) ?: $real_id;

    $page = get_post($real_id);
    if (!$page instanceof WP_Post || $page->post_type !== 'page') {
        return null;
    }

    while (true) {
        // 1) Look for a CHILD config page under the current page
        $config = get_posts([
            'post_type'      => 'page',
            'post_parent'    => (int) $page->ID,   // <-- child of current page
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'meta_query'     => [
                [
                    'key'   => '_wp_page_template',
                    'value' => 'templates/config.php',
                ],
            ],
        ]);

        if (!empty($config)) {
            return $config[0];
        }

        // 2) Move up
        $parent_id = (int) wp_get_post_parent_id($page->ID);
        if ($parent_id === 0) {
            return null;
        }

        $page = get_post($parent_id);
        if (!$page instanceof WP_Post) {
            return null;
        }
    }
}

add_action('admin_enqueue_scripts', function () {
    // Only on Edit Page screen: post.php?action=edit&post=ID
    if (($GLOBALS['pagenow'] ?? '') !== 'post.php') return;

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'page') return;

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (!$post_id) return;

    // Verify it has at least 1 child page
    $has_children = (bool) get_posts([
        'post_type'      => 'page',
        'post_parent'    => $post_id,
        'post_status'    => 'any',          // include drafts/private children too
        'fields'         => 'ids',
        'posts_per_page' => 1,              // existence check
        'no_found_rows'  => true,
    ]);

    if (!$has_children) return;

    $view_children_url = add_query_arg([
        'post_type'   => 'page',
        'post_parent' => $post_id,
    ], admin_url('edit.php'));

    $view_children_url_json = wp_json_encode($view_children_url);

    $js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
  const addNew = document.querySelector('.wrap .page-title-action');
  if (!addNew) return;

  const btn = document.createElement('a');
  btn.className = addNew.className;
  btn.href = {$view_children_url_json};
  btn.textContent = 'View Children';

  const copy = document.createElement('a');
  copy.className = addNew.className;
  copy.href = '#';
  copy.textContent = 'Copy';

  copy.addEventListener('click', function (e) {
    e.preventDefault();
    const url = {$view_children_url_json};
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url);
      return;
    }
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
  });

  addNew.insertAdjacentElement('afterend', btn);
  btn.insertAdjacentElement('afterend', copy);
});
JS;

    wp_add_inline_script('jquery-core', $js);
});



add_action('pre_get_posts', function (WP_Query $q) {
    if (!is_admin() || !$q->is_main_query()) return;

    // Only on Pages list screen: edit.php?post_type=page
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-page') return;

    // Read the desired parent filter from the URL
    $parent_id = isset($_GET['post_parent']) ? (int) $_GET['post_parent'] : 0;

    // Only apply when explicitly present
    if (!isset($_GET['post_parent'])) return;

    // Apply the filter: show only direct children of that parent
    $q->set('post_parent', $parent_id);

    // Optional: make the list less weird when filtering
    $q->set('orderby', 'menu_order title');
    $q->set('order', 'ASC');
});


add_action('admin_enqueue_scripts', function () {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit.php') return;

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-page') return;

    $parent_id = isset($_GET['post_parent']) ? (int) $_GET['post_parent'] : 0;
    if (!$parent_id) return;

    $parent = get_post($parent_id);
    if (!$parent || $parent->post_type !== 'page') return;

    // 🔁 ADMIN edit URL instead of frontend permalink
    $edit_url = get_edit_post_link($parent_id, '');
    if (!$edit_url) return;

    // 1) Hidden anchor we can move
    add_action('admin_footer', function () use ($edit_url) {
        printf(
            '<a id="pw-edit-parent-page"
                href="%s"
                class="page-title-action"
                style="display:none">
                Edit Page
            </a>',
            esc_url($edit_url)
        );
    });

    // 2) Move next to "Add New"
    $js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
  var link = document.getElementById('pw-edit-parent-page');
  if (!link) return;

  var wrap = document.querySelector('.wrap');
  if (!wrap) return;

  var addNew = wrap.querySelector('a.page-title-action');
  if (!addNew) return;

  addNew.insertAdjacentElement('afterend', link);
  link.style.display = '';
});
JS;

    wp_add_inline_script('jquery-core', $js);
});






/**
 * Add "Children" row action for hierarchical post types (Pages),
 * only when the item has direct children.
 */
add_filter('page_row_actions', function (array $actions, \WP_Post $post) {

    // Only for Pages (change if you want other hierarchical post types)
    if ($post->post_type !== 'page') {
        return $actions;
    }

    // Only when it has children
    $has_children = get_posts([
        'post_type'              => 'page',
        'post_parent'            => $post->ID,
        'post_status'            => 'any',
        'fields'                 => 'ids',
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    if (empty($has_children)) {
        return $actions;
    }

    // Build link to list filtered by this page as parent
    $url = add_query_arg([
        'post_type'   => 'page',
        'post_parent' => $post->ID,   // WP uses post_parent on edit.php to filter children
    ], admin_url('edit.php'));

    $actions['children'] = sprintf(
        '<a href="%s">%s</a>',
        esc_url($url),
        esc_html__('Children', 'textdomain')
    );

    // Optional: position it right after "View" if present
    $ordered = [];
    foreach ($actions as $key => $html) {
        $ordered[$key] = $html;
        if ($key === 'view') {
            // if we inserted at end, this keeps it near View
        }
    }

    return $actions; // or return $ordered;
}, 10, 2);
