<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"{$class_name}\">{$content}</div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//example.php
-->";
};

# src: webpack/src/blueprint/partials/example.ejs