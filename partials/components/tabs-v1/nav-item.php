<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"nav-item\" role=\"presentation\"><button class=\"nav-link {$active}\" id=\"{$id}-tab\" data-bs-toggle=\"tab\" data-bs-target=\"#{$id}\" type=\"button\" role=\"tab\" aria-controls=\"{$id}\" aria-selected=\"{$is_selected}\">{$text}</button></li><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/tabs-v1/nav-item.php
-->";
};

# src: webpack/src/blueprint/partials/components/tabs-v1/nav-item.ejs