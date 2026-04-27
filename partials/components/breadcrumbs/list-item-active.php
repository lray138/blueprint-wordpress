<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"breadcrumb-item active\" aria-current=\"page\">{$page_label}</li><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/breadcrumbs/list-item-active.php
-->";
};

# src: webpack/src/blueprint/partials/components/breadcrumbs/list-item-active.ejs