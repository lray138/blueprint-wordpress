<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<a {$attributes}>{$text}</a>";
};

# src: webpack/src/blueprint/partials/components/anchor/index.ejs