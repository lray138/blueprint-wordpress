<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"feature\">{$content}</div>";
};

# src: webpack/src/blueprint/partials/components/icon-feature/index.ejs