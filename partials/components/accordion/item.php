<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $expanded = isset($expanded) ? $expanded : false;
    $button_class = "accordion-button";
    if(isset($expanded) && !$expanded) {
        $button_class .= " collapsed";
    }
    $button_text = $label;
    $content = $content;

    if(isset($always_open) && !$always_open) {
        $extra_attributes = "data-bs-parent=\"#$parent_id\"";
    } else {
        $extra_attributes = "";
    }

    $accordion_collapse_class = "accordion-collapse";
    if(isset($expanded) && $expanded) {
        $accordion_collapse_class .= " collapse show";
    } else {
        $accordion_collapse_class .= " collapse";
    }
    # end data processing
	return "<div class=\"accordion-item\"><h2 class=\"accordion-header\"><button class=\"{$button_class}\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#{$target_id}\" aria-expanded=\"{$expanded}\" aria-controls=\"{$target_id}\">{$button_text}</button></h2><div id=\"{$target_id}\" class=\"{$accordion_collapse_class}\" {$extra_attributes}><div class=\"accordion-body\">{$content}</div></div></div>";
};

# src: webpack/src/blueprint/partials/components/accordion/item.ejs