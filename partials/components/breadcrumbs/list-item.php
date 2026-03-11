<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"breadcrumb-item active\" aria-current=\"page\">Data</li>";
};

# src: webpack/src/blueprint/partials/components/breadcrumbs/list-item.ejs