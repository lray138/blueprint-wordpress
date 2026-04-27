<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<button {$attributes}>{$content}</button><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/button/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/button/index.ejs