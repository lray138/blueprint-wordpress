<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<img src=\"{$src}\" {$attributes}/>";
};

# src: 