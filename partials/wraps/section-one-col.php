<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<section {$section_attributes}><div class=\"container\"><div class=\"row\"><div class=\"col-12 {$col_1_class_add}\">{$content}</div></div></div></section>";
};

# src: webpack/src/blueprint/partials/wraps/section-one-col.ejs