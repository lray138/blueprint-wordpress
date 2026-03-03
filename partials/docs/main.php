<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<main><div class=\"container-fluid\"><div class=\"row\">{$sidebar}<div class=\"col-12 col-lg-6 col-xl-8 py-7 py-lg-9 px-lg-7\" id=\"doc-content\">{$content}</div><!-- Right \"Jump to\" Sidebar -->{$jump_to_sidebar}</div></div></main>";
};

# src: 