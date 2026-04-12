<?php

use function lray138\G2\dump;

$id = get_the_ID();
$page_title = get_the_title();

if (wp_attachment_is_image($id)) {
    $img_html = wp_get_attachment_image(
        $id,
        'full',
        false,
        [
            'class'    => 'photo-viewport-img rounded shadow',
            'decoding' => 'async',
        ]
    );

    $caption      = get_the_title();
    $caption_html = $caption !== ''
        ? '<figcaption class="mt-2 text-secondary small text-center">' . esc_html($caption) . '</figcaption>'
        : '';

    $figure_html = '<figure class="photo-viewport-figure mb-0 text-center">'
        . '<div class="photo-viewport-stage">' . $img_html . '</div>'
        . $caption_html
        . '</figure>';

    $inner = tryPartial('/blueprint/partials/wraps/section-one-col.php', [
        'attrs' => [
            'class_names' => 'mt-0',
        ],
        'content' => $figure_html,
    ])->getOrElse('');

    $wrapped = '<main id="photo-page-main" class="photo-page-main flex-grow-1 d-flex flex-column min-h-0 w-100">'
        . $inner
        . '</main>';

    $content = renderPageContent($id, [
        'site_name' => '',
        'site_url'  => get_stylesheet_directory_uri(),
        'content'   => $wrapped,
    ]);
} else {
    $src_row = wp_get_attachment_url($id);
    $src_esc = $src_row ? esc_url($src_row) : '';
    $link_html = $src_esc !== ''
        ? '<p><a href="' . $src_esc . '">' . esc_html($src_esc) . '</a></p>'
        : '';

    $content = renderPageContent($id, [
        'site_name' => '',
        'site_url'  => get_stylesheet_directory_uri(),
        'content'   => tryPartial('/blueprint/partials/wraps/section-one-col.php', [
            'attrs' => [
                'class_names' => 'mb-3',
            ],
            'content' => $link_html,
        ])->getOrElse(''),
    ]);
}

require_once get_template_directory() . '/index.php';
