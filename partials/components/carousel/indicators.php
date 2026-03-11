<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"carousel-indicators\">{$buttons}</div>";
};

# src: webpack/src/blueprint/partials/components/carousel/indicators.ejs