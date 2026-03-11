<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"tabs\"><ul class=\"nav nav-tabs\" id=\"myTab\" role=\"tablist\">{$nav_items}</ul><div class=\"tab-content\" id=\"myTabContent\">{$tab_content}</div></div>";
};

# src: webpack/src/blueprint/partials/components/tabs-v1/index.ejs