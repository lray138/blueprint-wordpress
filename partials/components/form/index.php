<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<form {$attributes}>{$content}</form><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/form/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/form/index.ejs