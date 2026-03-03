<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<section><div class=\"container\"><div class=\"row\"><div class=\"col-12 col-md-6 {$col_1_class_add}\" {$col_1_attributes}>{$col_1_content}</div><div class=\"col-12 col-md-6  {$col_1_class_add}\">{$col_2_content}</div></div></div></section>";
};

# src: 