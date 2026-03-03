<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div style=\"background: url({$bg_img_src}); background-size: cover; min-height: 100vh\" class=\"{$class_names}\">{$content}</div>";
};

# src: 