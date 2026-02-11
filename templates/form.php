<?php
/**
 * Template Name: Form
 */

 use function lray138\g2\dump;
 use lray138\G2\{Kvm, Str, Lst, Num, Maybe};
 
 $page_title = get_the_title();
 $content = "";

 $element_count = -1;

 $elements = Lst::of(carbon_get_post_meta( get_the_ID(), 'form_page_items' ))
    ->wrapMap(function(Kvm $s) use (&$element_count) {
        return handleSectionPartial(
            $s->set( 'index', $element_count )
                ->set( 'id', Str::of("s")->append($element_count) ) 
        );
    })
    ->join("");
 
 $page_content = getPartialCallable(Str::of("form"))
    ->map(fn($callable) => $callable([
        "attributes" => "",
        "content" => $elements
    ]))
    ->getOrElse($elements);

 $content = "<div class=\"container\">" . (include(get_template_directory() . '/partials/header.php'))() . "</div>";
 $content .= "<div class=\"container\">" .  $page_content  . "</div>";
 $content .= "<div class=\"container mt-auto\">" . (include(get_template_directory() . '/partials/footer.php'))() . "</div>";
 include(get_template_directory() . '/index.php');