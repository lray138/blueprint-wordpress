<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<blockquote class=\"blockquote text-center\"><!-- Brand Image --><!-- <div class=\"img-fluid mb-5 mb-md-7 mx-auto\" style=\"max-width: 120px; color: #CB2027;\"></div> --><p class=\"mb-4 mb-md-4\">“{$text}”</p><div class=\"mb-0 blockquote-footer\"><!-- Avatar --><!-- <div class=\"avatar me-3\">
            <img src=\"assets/img/avatars/avatar-1.jpg\" class=\"avatar-img rounded-circle\" alt=\"...\">
        </div>  --><span class=\"quote-source h6 text-uppercase mb-2\">{$source}</span></div></blockquote>";
};

# src: webpack/src/blueprint/partials/components/quote/index.ejs