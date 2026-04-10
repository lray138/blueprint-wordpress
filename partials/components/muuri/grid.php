<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div id=\"{$galleryDomId}\" class=\"mt-0 mb-0 {$gridClass}\" data-muuri-grid=\"true\">{$itemsHtml}</div>";
};

# src: webpack/src/blueprint/partials/components/muuri/grid.ejs