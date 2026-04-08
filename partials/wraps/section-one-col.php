<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $container_class = $container_class ?? "container";
    $section_attributes = "";
    $col_1_class_add = "";
    # end data processing
	return "<section {$section_attributes}><div class=\"{$container_class}\"><div class=\"row\"><div class=\"col-12 {$col_1_class_add}\">{$content}</div></div></div></section>";
};

# src: webpack/src/blueprint/partials/wraps/section-one-col.ejs