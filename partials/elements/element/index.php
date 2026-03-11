<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$content}{$element}>";
};

# src: webpack/src/blueprint/partials/elements/element/index.ejs