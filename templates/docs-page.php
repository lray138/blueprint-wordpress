<?php
/**
 * Template Name: Docs Page
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num, Maybe};


$page_title = get_the_title();
$content = "";

// WordPress is forgiving and rareley throws errors (random note I rememered) 
function getPageChildren($page_id): Lst {
    return Lst::of(get_pages(['parent' => $page_id]));
}

$header_class_extras = Lst::of(carbon_get_post_meta( get_the_ID(), 'header_options' ))
    ->mhead()
    ->map(function (array $data) {
        return $data["header_class_extras"];
    })
    ->getOrElse("");

$content = "<div class=\"container-fluid\">" . (include(get_template_directory() . '/partials/header.php'))([
    "header_class_extras" => $header_class_extras
]) . "</div>";

$footer_class_extras = Lst::of(carbon_get_post_meta( get_the_ID(), 'footer_options' ))
    ->mhead()
    ->map(function (array $data) {
        return $data["footer_class_extras"];
    })
    ->getOrElse("");

$page_id = get_the_ID();
$content = renderPageContent($page_id);

include(get_template_directory() . '/index.php');
die;

$content = getDocsMainContent(get_the_id());

// $content .= (include(get_template_directory() . '/partials/footer.php'))([
//     "footer_class_extras" => $footer_class_extras
// ]);

$footer = handle_partial_page_id(Kvm::of(["page_id" => 1422]));
$content .= $footer;

include(get_template_directory() . '/index.php');