<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<section class=\"{$class_names}\"><div class=\"container\"><div class=\"row\"><div class=\"col-12\">{$content}</div></div></div></section>";
};

# src: webpack/src/blueprint/partials/components/wrap/section-one-col.ejs