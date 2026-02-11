<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div id=\"{$carousel_id}\" class=\"carousel slide\" data-bs-ride=\"{$ride}\">{$indicators}<div class=\"carousel-inner\">{$items}</div>{$controls}</div>";
};

# src: 