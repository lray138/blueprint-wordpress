<?php 

use function lray138\G2\dump;

$content = "test";

$img_src = wp_get_attachment_image_src(get_the_ID(), 'full')[0];

$alt_txt = get_post_meta(get_the_ID(), '_wp_attachment_image_alt', true);
$page_title = get_the_title();

$content = renderPageContent(get_the_ID(), [
    "site_name" => "",
    "site_url" => get_stylesheet_directory_uri(),
    "content" => tryPartial("/blueprint/partials/wraps/section-one-col.php", [
        "attrs" => [
            "class_names" => "mb-3",
        ],
        "content" => "<img src='{$img_src}' alt='{$alt_txt}' class='img-fluid'>"
    ])->getOrElse(""),
]);

require_once get_template_directory() . '/index.php';