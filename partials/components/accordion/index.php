<?php
# start use
use function lray138\g2\dump;
use lray138\g2\{Lst, Kvm};
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    $count = 0;
    $items = Lst::of($items)->map(function(array $item) use (&$count,$data) {
        $item["parent_id"] = $data["id"];
        $item["target_id"] =  $data["id"] . "_i" . $count;
        $item["always_open"] = $data["always_open"];
        $count++;
        return (include __DIR__ . "/item.php")($item);
    })->join('');
    $classNames = "accordion";

    if(isset($data["flush"]) && $data["flush"]) {
        $classNames .= " accordion-flush";
    }
    # end data processing
	return "<div class=\"{$classNames}\" id=\"{$id}\" {$attributes}>{$items}</div>";
};

# src: webpack/src/blueprint/partials/components/accordion/index.ejs