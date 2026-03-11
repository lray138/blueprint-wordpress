<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"page-header {$class_names}\"><h2 class=\"display-2\">{$heading}</h2><p class=\"lead\">{$text}</p></div>";
};

# src: webpack/src/blueprint/partials/patterns/page-header/index.ejs