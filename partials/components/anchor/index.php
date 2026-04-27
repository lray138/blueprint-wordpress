<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<a {$attributes}>{$text}</a><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/anchor/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/anchor/index.ejs