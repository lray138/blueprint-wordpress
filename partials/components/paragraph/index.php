<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $content = $text;
    # end data processing
	return "<p {$attributes}>{$content}</p><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/paragraph/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/paragraph/index.ejs