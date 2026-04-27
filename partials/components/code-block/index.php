<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<pre class=\"{$pre_class}\"><code class=\"{$code_class}\">{$body}</code></pre><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/code-block/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/code-block/index.ejs