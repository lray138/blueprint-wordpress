<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div style=\"background: url({$bg_img_src}); background-size: cover; min-height: 100vh\" class=\"{$class_names}\">{$content}</div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/parallax/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/parallax/index.ejs