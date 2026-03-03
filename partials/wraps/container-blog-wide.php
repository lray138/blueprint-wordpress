<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"container\"><div class=\"row justify-content-center\"><div class=\"col-12 col-lg-10 col-xl-9\">{$content}</div></div></div>";
};

# src: 