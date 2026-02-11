<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<button {$attributes}>{$content}</button>";
};

# src: 