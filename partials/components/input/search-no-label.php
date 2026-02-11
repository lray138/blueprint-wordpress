<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"input-group\"><span class=\"input-group-text pe-0 border-0\"><i class=\"bi bi-search\" id=\"{$input_id}\"></i></span><input class=\"form-control ps-2 border-0\" name=\"{$input_name}\" type=\"search\" placeholder=\"{$input_placeholder}\" aria-label=\"{$input_aria_label}\" aria-describedby=\"{$input_id}\"/></div>";
};

# src: 