<?php 

// adds functionality for "new page" parent dropdown to default to current page

add_action('admin_enqueue_scripts', function () {
   
   $screen = function_exists('get_current_screen') ? get_current_screen() : null;
   if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'page') return;

   $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
   if (!$post_id) return;

   $new_url_json = wp_json_encode(
       admin_url('post-new.php?post_type=page&parent_id=' . $post_id)
   );

   $js = "document.addEventListener('DOMContentLoaded', function () {

       var btn = document.querySelector('a.page-title-action');
       if (!btn) return;

       console.log('found');

       if (btn.href.indexOf('post-new.php') !== -1 && btn.href.indexOf('post_type=page') !== -1) {
           btn.href = {$new_url_json};
           btn.textContent = 'Add Page'; // optional
       }
   });";

   // Attach to a core script that's present in wp-admin
   wp_add_inline_script('jquery-core', $js);
});


add_action('admin_enqueue_scripts', function () {
   // Only on Add New Page screen
   if (($GLOBALS['pagenow'] ?? '') !== 'post-new.php') return;

   $screen = function_exists('get_current_screen') ? get_current_screen() : null;
   if (!$screen || $screen->post_type !== 'page') return;

   $parent_id = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : 0;
   if (!$parent_id) return;

   $parent_id_json   = wp_json_encode($parent_id);
   $template_json    = wp_json_encode('templates/universal.php');

   $js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
 var parentId = {$parent_id_json};
 var template = {$template_json};

 // ---- Parent ----
 var parentSelect =
   document.getElementById('parent_id') ||
   document.querySelector('select[name="parent_id"]');

 if (parentSelect) {
   parentSelect.value = String(parentId);
   parentSelect.dispatchEvent(new Event('change', { bubbles: true }));
 }

 // ---- Template ----
 var templateSelect =
   document.getElementById('page_template') ||
   document.querySelector('select[name="page_template"]');

 if (templateSelect) {
   templateSelect.value = template;
   templateSelect.dispatchEvent(new Event('change', { bubbles: true }));
 }
});
JS;

   wp_add_inline_script('jquery-core', $js);
});
