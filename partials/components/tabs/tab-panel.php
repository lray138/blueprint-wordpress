<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"tab-pane fade position-relative {$class_names}\" id=\"{$id}\" role=\"tabpanel\" aria-labelledby=\"{$aria_labelledby}\">{$content}</div>";
};

# src: 