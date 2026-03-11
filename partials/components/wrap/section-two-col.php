<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<section><div class=\"container\"><div class=\"row\"><div class=\"col-12 col-md-6\">{$col_1_content}</div><div class=\"col-12 col-md-6\">{$col_2_content}
                {$col_2_content2}</div></div></div></section>";
};

# src: webpack/src/blueprint/partials/components/wrap/section-two-col.ejs