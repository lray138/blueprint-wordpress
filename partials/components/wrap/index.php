<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$wrap_start}
    {$content}
{$wrap_end}";
};

# src: webpack/src/blueprint/partials/components/wrap/index.ejs