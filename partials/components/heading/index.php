<?php
# start use
use function lray138\g2\dump;
use lray138\g2\{Lst, Kvm};
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $level = $level ?? 2;
    $attributes = $attributes ?? '';
    $text = $text ?? '';
    # end data processing
	return "<h{$level} {$attributes}>{$text}</h{$level}><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/heading/index.php
-->";
};

# src: webpack/src/blueprint/partials/components/heading/index.ejs