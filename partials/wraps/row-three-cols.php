<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"row g-3\"><div class=\"col-12 col-md-4\">{$col_1_content}</div><div class=\"col-12 col-md-4\">{$col_2_content}</div><div class=\"col-12 col-md-4\">{$col_3_content}</div></div>";
};

# src: 