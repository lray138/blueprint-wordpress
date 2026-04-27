<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$heading}
{$list_items}<!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//footer/section.php
-->";
};

# src: webpack/src/blueprint/partials/footer/section.ejs