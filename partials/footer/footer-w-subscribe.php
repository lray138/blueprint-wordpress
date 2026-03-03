<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<footer class=\"mt-auto py-5 pb-3 mt-3 {$footer_class_extras}\"><div class=\"container\"><div class=\"row\"><div class=\"col-6 col-md-2 mb-3\">{$section_1}</div><div class=\"col-6 col-md-2 mb-3\">{$section_2}</div><div class=\"col-6 col-md-2 mb-3\">{$section_3}</div><div class=\"col-md-5 offset-md-0 mb-3\">{$section_4}</div><div class=\"d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top pb-0 mb-0\">{$section_5}</div></div></div></footer>";
};

# src: 