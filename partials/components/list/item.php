<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li{$attributes}>{$content}<!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/list/item.php
--></li{$attributes}>";
};

# src: webpack/src/blueprint/partials/components/list/item.ejs