<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"px-3 {$class_names}\"><div class=\"col-lg-6 mx-auto\"><h1 class=\"display-5 fw-bold text-body-emphasis mb-3\">{$heading}</h1>{$content}</div></div>";
};

# src: 