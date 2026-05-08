<?php
/**
 * Template Name: Gallery Page
 */
use lray138\G2\Result;

$page_title = get_the_title($page_id);

// Field name is `gallery` (2nd arg to Field::make). Container id is `gallery_page` (2nd arg to Container::make).
// If you omit the container id, Carbon may not resolve the field across multiple post_meta containers on the same template.
$gallery_raw = carbon_get_post_meta(get_the_ID(), 'gallery');

$attachment_ids = bp_gallery_field_attachment_ids($gallery_raw);

$attachment_posts = [];
foreach ($attachment_ids as $aid) {
    $post = get_post($aid);
    if ($post && $post->post_type === 'attachment') {
        $attachment_posts[] = $post;
    }
}

$items_html = '';
foreach ($attachment_posts as $post) {
    $items_html .= tryPartial('/bp/components/muuri/muuri-item.php', bp_muuri_item_vars_from_attachment($post))
        ->getOrElse('partial not found');
}


$test = tryPartial("/blueprint/partials/patterns/page-header/index.php", [
    "class_names" => "text-center",
    "heading" => tryQueriedObjectId()->map('get_the_title')->getOrElse(''),
])
    ->getOrElse('a');

$grid_html = $test . '<div id="gallery" class="muuri-grid muuri-grid--max-cols-3 muuri" data-muuri-grid="true">'
    . $items_html
    . '</div>';

$content = tryPartial('/bp/wraps/section-one-col', [
    'content' => $grid_html,
    'container_class' => 'container px-0',
    'section_attributes' => 'class="mb-5"',
    'col_1_class_add' => 'class="col-12 col-md-6"',
])
    ->getOrElse('');

$content = renderPageContent($page_id, [
    'content' => $content,
]);

include get_template_directory() . '/index.php';