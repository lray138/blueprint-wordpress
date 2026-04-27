<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"card {$card_attrs}\">{$content}</div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/card/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/card/index.ejs