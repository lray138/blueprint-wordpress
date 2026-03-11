<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$content}";
};

# src: webpack/src/blueprint/partials/patterns/info-stack/examples/standard.ejs