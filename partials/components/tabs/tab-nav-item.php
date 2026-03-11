<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<li class=\"nav-item\" role=\"presentation\"><button class=\"nav-link {$button_class_names}\" id=\"{$button_id}\" data-bs-toggle=\"tab\" data-bs-target=\"#{$id}\" type=\"button\" role=\"tab\" aria-controls=\"{$id}\" aria-selected=\"<%= selected ? \'true\' : \'false\' %>\">{$text}</button></li>";
};

# src: webpack/src/blueprint/partials/components/tabs/tab-nav-item.ejs