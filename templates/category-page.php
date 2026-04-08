<?php
/**
 * Template Name: Category Page
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

if (! function_exists('bp_get_category_page_media')) {
    /**
     * Attachment posts in media_category whose term matches this page’s title (name, then slug).
     *
     * @param int|null $page_id Page ID; defaults to current post in the loop.
     * @return WP_Post[]
     */
    function bp_get_category_page_media($page_id = null): Lst
    {
        $page_id = $page_id !== null ? (int) $page_id : (int) get_the_ID();
        if ($page_id < 1) {
            return Lst::of([]);
        }

        $title = get_the_title($page_id);
        if ($title === '') {
            return Lst::of([]);
        }

        $term = get_term_by('name', $title, 'media_category');
        if (! $term || is_wp_error($term)) {
            $term = get_term_by('slug', sanitize_title($title), 'media_category');
        }

        if (! $term || is_wp_error($term)) {
            return Lst::of([]);
        }

        return Lst::of(get_posts([
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => 'media_category',
                    'field'    => 'term_id',
                    'terms'    => [(int) $term->term_id],
                ],
            ],
        ]));
    }
}

$page_id = get_queried_object_id() ?: get_the_ID();
$page_title = get_the_title($page_id);

$content = bp_get_category_page_media($page_id)
    ->map(fn(WP_Post $post) => bp_muuri_item_vars_from_attachment($post))
    ->map(function($x) {
        return tryPartial("/bp/components/muuri/muuri-item.php", $x)
            ->getOrElse('partial not found');
    })
    ->join('')
    ->map(function($x) {
        return "<div id=\"gallery\" class=\"muuri-grid mt-4 mb-4 muuri-grid--max-cols-3 muuri\" data-muuri-grid=\"true\">{$x}</div>";
    })
    ->map(function($x) {
        return tryPartial("/bp/wraps/section-one-col", [
            "content" => $x,
            "container_class" => "container",
            "section_attributes" => "class=\"mb-5\"",
            "col_1_class_add" => "class=\"col-12 col-md-6\"",
        ])
        ->getOrElse('');
    });

$content = renderPageContent($page_id, [
    "content" => $content
]);

include(get_template_directory() . '/index.php');