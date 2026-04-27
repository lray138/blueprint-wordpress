<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<p {$attributes}>{$text}</p><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/text/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/text/index.ejs