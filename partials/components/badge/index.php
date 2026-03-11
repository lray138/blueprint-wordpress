<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<span {$attributes}>{$text}</span>";
};

# src: webpack/src/blueprint/partials/components/badge/index.ejs