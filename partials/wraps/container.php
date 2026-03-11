<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"container\">{$content}</div>";
};

# src: webpack/src/blueprint/partials/wraps/container.ejs