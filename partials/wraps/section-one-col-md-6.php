<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<section><div class=\"container\"><div class=\"row\"><div class=\"col-12 col-md-6 {$class_names}\">{$content}</div></div></div></section>";
};

# src: 