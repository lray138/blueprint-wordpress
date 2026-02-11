<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"container\"><div class=\"row justify-content-center\"><div class=\"col-12 col-md-10 col-lg-8 col-xl-7\">{$content}</div></div></div>";
};

# src: 