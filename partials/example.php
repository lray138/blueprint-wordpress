<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"{$class_name}\">{$content}</div>";
};

# src: 