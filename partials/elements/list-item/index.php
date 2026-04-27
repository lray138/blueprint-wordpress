<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li {$attributes}>{$content}</li><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//elements/list-item/index.php
-->";
};

# src: webpack/src/blueprint/partials/elements/list-item/index.ejs