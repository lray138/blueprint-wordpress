<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<label {$attributes}>{$content}</label>";
};

# src: webpack/src/blueprint/partials/elements/label/index.ejs