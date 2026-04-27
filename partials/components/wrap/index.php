<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$wrap_start}
    {$content}
{$wrap_end}<!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/wrap/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/wrap/index.ejs