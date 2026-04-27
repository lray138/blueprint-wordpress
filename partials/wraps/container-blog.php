<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"container {$class_names}\"><div class=\"row justify-content-center\"><div class=\"col-12 col-md-10 col-lg-8 col-xl-7\">{$content}</div></div></div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//wraps/container-blog.php
-->";
};

# src: webpack/src/blueprint/partials/wraps/container-blog.ejs