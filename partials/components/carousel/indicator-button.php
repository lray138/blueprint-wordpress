<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<button type=\"button\" data-bs-target=\"#{$carousel_id}\" data-bs-slide-to=\"{$index}\" class=\"{$class_name}\" aria-current=\"{$aria_current}\" aria-label=\"Slide <%= index + 1 %>\"></button>";
};

# src: webpack/src/blueprint/partials/components/carousel/indicator-button.ejs