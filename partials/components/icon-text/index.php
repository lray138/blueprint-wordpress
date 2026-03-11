<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"d-flex align-items-center\"><span class=\"badge py-2 px-0 px-0 {$icon_wrap_class_names}\" style=\"font-size:1.2em; margin-right: .5rem;\">{$icon}</span>{$text}</div>";
};

# src: webpack/src/blueprint/partials/components/icon-text/index.ejs