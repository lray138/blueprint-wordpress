<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$label_top}<input {$attributes}/>{$label_bottom}";
};

# src: webpack/src/blueprint/partials/elements/input/index.ejs