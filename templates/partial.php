<?php
/**
 * Template Name: Component
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num, Maybe};

$page_title = get_the_title();
$content = "";

//$content .= "<div class=\"container\">" . concatPageSections(Lst::of(carbon_get_post_meta( get_the_ID(), 'universal_page_sections' ))) . "</div>";
//$partials = Lst::of(carbon_get_post_meta( get_the_ID(), 'partial_page_partials' ));
$partials = Lst::of(carbon_get_post_meta( get_the_ID(), 'partials' ));

// so like, this also gets to the "Why" of the classes and LISO ... (Loose in, Strict Out) bcause the 
// overhead and then...  I think assume once it gets in the system it's going to 99.% of time be wrapped
// but overhead .. it decreasees readbility in my opinion.
$section = getPartialCallable(Str::of("section"))->get();


$template_slug = get_page_template_slug(get_the_ID());
$attrs_id = getAttrsFieldNameFromTemplateSlug($template_slug);

$attributes  = carbon_get_post_meta( get_the_ID(), $attrs_id);

$attributes = Maybe::of(Lst::of($attributes))
    ->map(fn($attrs) => renderAttributes($attrs))
    ->getOrElse(Str::of(""));

// $content .= "<div id=\"partial-container\" class=\"container mt-4\">";
// $content .= concatPartials($partials);
// $content .= "</div>";

    $page_id = get_the_ID();
    $selected_page_id = (int) $page_id;

    $template_slug = get_page_template_slug($selected_page_id);
    $complex_field_name = $template_slug === 'templates/form.php' ? 'form_page_items' : 'partials';

    $partials = Lst::of(carbon_get_post_meta($selected_page_id, $complex_field_name));
    $partials = concatPartials($partials);

    $attrs_id = getAttrsFieldNameFromTemplateSlug($template_slug);
    
   // $attributes = Lst::of();
    $attributes = handleAttributesField(Lst::of(carbon_get_post_meta($selected_page_id, $attrs_id)));

    $attributes = render_attrs($attributes->get());

    if(!empty($attributes)) {
        $partials = Str::of("<div {$attributes}>{$partials}</div>");
    }

    $content = $partials;


include(get_template_directory() . '/index.php');