<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<p {$attributes}>{$text}</p>";
};

# src: 