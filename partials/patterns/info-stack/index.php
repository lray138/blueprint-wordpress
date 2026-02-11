<?php
# start use
use lray138\G2\{Kvm, Str, Lst, Num};
use function lray138\g2\dump;
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div data-pattern=\"info-stack\">{$content}</div>";
};

# src: 