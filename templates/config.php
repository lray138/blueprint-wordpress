<?php
/**
 * Template Name: Config
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

$page_title = get_the_title();
$content = "";

//$content .= "<div class=\"container\">" . concatPageSections(Lst::of(carbon_get_post_meta( get_the_ID(), 'universal_page_sections' ))) . "</div>";
//$partials = Lst::of(carbon_get_post_meta( get_the_ID(), 'partial_page_partials' ));
$partials = Lst::of(carbon_get_post_meta( get_the_ID(), 'partials' ));

// so like, this also gets to the "Why" of the classes and LISO ... (Loose in, Strict Out) bcause the 
// overhead and then...  I think assume once it gets in the system it's going to 99.% of time be wrapped
// but overhead .. it decreasees readbility in my opinion.
$section = getPartialCallable(Str::of("section"))->get();

$partial_attrs = [];
$partial_class = carbon_get_post_meta( get_the_ID(), 'class' );

if(!empty($partial_class)) {
    $partial_attrs['class'] = $partial_class;
}

$partial_style = carbon_get_post_meta( get_the_ID(), 'style' );

$partial_attributes['style'] = [];

if(!empty($partial_style)) {
    $partial_attributes['style'][] = $partial_style;
}

$bg_img = carbon_get_post_meta( get_the_ID(), 'bg_img' );
if(!empty($bg_img)) {
    $partial_attributes['style'][] = "background-image: url(" . wp_get_attachment_image_src($bg_img, 'full')[0] . ")";
}

$partial_attrs['style'] = implode(';', $partial_attributes['style']);

$content .= "<div class=\"container mt-4\">";
$content .= "<h1 class=\"mb-3\">$page_title</h1>";
$content .= $section([
    'content' => concatPartials($partials),
    'fluid' => '',
    'container_attributes' => 'container',
    'attributes' => render_attrs($partial_attrs),
]);
$content .= "</div>";

include(get_template_directory() . '/index.php');