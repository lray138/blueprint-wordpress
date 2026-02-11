<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"{$item_class}\"><img class=\"d-block w-100\" src=\"{$src}\" alt=\"{$alt}\"/></div>";
};

# src: 