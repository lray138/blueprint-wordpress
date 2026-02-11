<?php
# start use
use function lray138\g2\dump;
use lray138\g2\{Lst, Kvm};
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<h{$level} {$attributes}>{$text}</h{$level}>";
};

# src: 