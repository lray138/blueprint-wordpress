<?php
# start use
use function lray138\G2\dump;
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $inner_wrap_start = isset($inner_wrap_start) ? $inner_wrap_start : '';
    $inner_wrap_end = isset($inner_wrap_end) ? $inner_wrap_end : '';

    if(isset($inner_wrap_callable)) {
        
        if(method_exists($inner_wrap_callable, "get")) {
            $inner_wrap_callable = $inner_wrap_callable->get();
        }

        

        if(!empty($inner_wrap_callable)) {
            $content = $inner_wrap_callable($content);
        }
    }
    # end data processing
	return "<section {$attributes}>{$inner_wrap_start}
    {$content}
    {$inner_wrap_end}</section>";
};

# src: 