<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"tab-pane fade {$active_class}\" id=\"{$id}\" role=\"tabpanel\" aria-labelledby=\"{$id}-tab\">{$content}</div>";
};

# src: 