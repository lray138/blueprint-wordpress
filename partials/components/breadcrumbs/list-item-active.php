<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"breadcrumb-item active\" aria-current=\"page\">{$page_label}</li>";
};

# src: 