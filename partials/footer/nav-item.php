<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"nav-item mb-2\"><a href=\"{$href}\" class=\"nav-link p-0 text-body-secondary\">{$text}</a></li><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//footer/nav-item.php
-->";
};

# src: webpack/src/blueprint/partials/footer/nav-item.ejs