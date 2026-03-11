<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$heading}
{$list_items}";
};

# src: webpack/src/blueprint/partials/footer/section.ejs