<?php
/**
 * Template Name: Project
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

$page_title = get_the_title();
$content = "";

$sections = Lst::of(carbon_get_post_meta( get_the_ID(), 'universal_page_sections' ));

$header_class_extras = Lst::of(carbon_get_post_meta( get_the_ID(), 'header_options' ))
    ->mhead()
    ->map(function (array $data) {
        return $data["header_class_extras"];
    })
    ->getOrElse("");

if(isset(carbon_get_post_meta( get_the_ID(), 'header_options' )[0]["pin_top"])  
    && carbon_get_post_meta( get_the_ID(), 'header_options' )[0]["pin_top"] === true) {
    $section_class_extras = " pin-top";
} else {
    $section_class_extras = "";
}

$content = "<section class=\"{$section_class_extras}\"><div class=\"container\">" . (include(get_template_directory() . '/partials/header.php'))([
    "header_class_extras" => $header_class_extras
]) . "</div></section>";

// stince It's still not 2nd nature, I don't want to be forced into the function chain
// which is why head would be the wrapped value and Null otherwise... which would lead to Null vs not a value ... 
// hah.. which gets you back to dunno.. 
$footer_class_extras = Lst::of(carbon_get_post_meta( get_the_ID(), 'footer_options' ))
    ->mhead()
    ->map(function (array $data) {
        return $data["footer_class_extras"];
    })
    ->getOrElse("");

$content .= "" . concatPageSections($sections) . "";
$content .= (include(get_template_directory() . '/partials/footer.php'))([
    "footer_class_extras" => $footer_class_extras
]);

include(get_template_directory() . '/index.php');
