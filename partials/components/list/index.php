<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<{$list_type} {$attributes}>{$list_items}</{$list_type}>";
};

# src: webpack/src/blueprint/partials/components/list/index.ejs