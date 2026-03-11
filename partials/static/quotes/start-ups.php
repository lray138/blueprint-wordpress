<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$quote}";
};

# src: webpack/src/blueprint/partials/static/quotes/start-ups.ejs