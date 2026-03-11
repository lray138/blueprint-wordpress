<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$embed}";
};

# src: webpack/src/blueprint/partials/components/video/index.ejs