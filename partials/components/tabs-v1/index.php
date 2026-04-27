<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"tabs\"><ul class=\"nav nav-tabs\" id=\"myTab\" role=\"tablist\">{$nav_items}</ul><div class=\"tab-content\" id=\"myTabContent\">{$tab_content}</div></div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/tabs-v1/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/tabs-v1/index.ejs