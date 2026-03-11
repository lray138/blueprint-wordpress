<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"card {$card_attrs}\">{$content}</div>";
};

# src: webpack/src/blueprint/partials/components/card/index.ejs