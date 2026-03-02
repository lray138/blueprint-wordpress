<?php
/**
 * Template Name: Default Page
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

$page_title = get_the_title();
$content = "";
$content = renderPageContent(get_the_ID());

include(get_template_directory() . '/index.php');

die;

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

$header_class_extras = "";

foreach(carbon_get_post_meta( get_the_ID(), 'page_config_items' ) as $item) {
    if($item["_type"] === "header_items") {
        foreach($item["header_attrs"] as $attr) {
            if($attr["_type"] === "pin_top") {
                if($attr["pin_top"] === true) {
                    $section_class_extras = " pin-top";
                }
            }
        }
    }
}

$content = (include(get_template_directory() . '/partials/header.php'))([
    "header_class_extras" => $header_class_extras,
    "section_class_extras" => $section_class_extras
]);

$config = getPageConfig(get_the_ID());

if($config) {
    $test = carbon_get_post_meta($config->ID, 'config_page_items');

    foreach($test as $item) {
        if($item["_type"] === "header") {
            foreach($item["header_config_items"] as $header_config_item) {
                if($header_config_item["_type"] === "partial") {
                    $partial_id = $header_config_item["partial"][0]["id"];
                    $content = handle_partial_page_id(Kvm::of(["page_id" => $partial_id]));
                }
            }
        }
    }
}

// stince It's still not 2nd nature, I don't want to be forced into the function chain
// which is why head would be the wrapped value and Null otherwise... which would lead to Null vs not a value ... 
// hah.. which gets you back to dunno.. 
$footer_class_extras = Lst::of(carbon_get_post_meta( get_the_ID(), 'footer_options' ))
    ->mhead()
    ->map(function (array $data) {
        return $data["footer_class_extras"];
    })
    ->getOrElse("");

//$content .= "" . concatPageSections($sections) . "";
// $content .= (include(get_template_directory() . '/partials/footer.php'))([
//     "footer_class_extras" => $footer_class_extras
// ]);

$footer = handle_partial_page_id(Kvm::of(["page_id" => 1422]));
$content .= $footer;

include(get_template_directory() . '/index.php');

