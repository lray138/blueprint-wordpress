<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<pre class=\"{$pre_class}\"><code class=\"{$code_class}\">{$body}</code></pre>";
};

# src: webpack/src/blueprint/partials/components/code-block/index.ejs