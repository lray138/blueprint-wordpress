<?php
# start use
use lray138\g2\Lst;
use function lray138\g2\dump;
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $section_1 = "";

    $section_1 = Lst::of(carbon_get_post_meta('277', 'partials'));
    $section_1 = concatPartials($section_1);

    $section_2 = $section_1;
    $section_3 = $section_2;

    $section_4 = Lst::of(carbon_get_post_meta('1238', 'form_page_items'));
    $section_4 = concatPartials($section_4);

    $section_5 = Lst::of(carbon_get_post_meta('326', 'partials'));
    $section_5 = concatPartials($section_5);
    $section_5_outer_wrap = carbon_get_post_meta('326', 'outer_wrap');
   

    // I just messed this up... 
    if($section_5_outer_wrap !== null) {
        $outer_wrap = render_attrs(reduce_attrs($section_5_outer_wrap));
        if(!empty($outer_wrap)) {
            $section_5 = "<div {$outer_wrap}>{$section_5}</div>";
        } else {
            $section_5 = "<div>{$section_5}</div>";
        }
    } else {
        $section_5 = "<div>{$section_5}</div>";
    }

    $footer_class_extras = $data['footer_class_extras'] ?? '';
    
    # end data processing
	return "<footer class=\"mt-auto py-5 pb-3 mt-3 {$footer_class_extras}\"><div class=\"container\"><div class=\"row\"><div class=\"col-6 col-md-2 mb-3\">{$section_1}</div><div class=\"col-6 col-md-2 mb-3\">{$section_2}</div><div class=\"col-6 col-md-2 mb-3\">{$section_3}</div><div class=\"col-md-5 offset-md-0 mb-3\">{$section_4}</div><div class=\"d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top pb-0 mb-0\">{$section_5}</div></div></div></footer>";
};

# src: 