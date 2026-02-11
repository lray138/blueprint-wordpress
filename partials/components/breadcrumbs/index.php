<?php
# start use
use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst};
# end use

return function($data = []) { 
	# start data processing
    extract($data);

    $ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
    $ancestors[] = get_the_ID();

    if(count($ancestors) === 1) {
        return "";
    }

    $content = Lst::of($ancestors)
        ->map(fn($ancestor) => get_post($ancestor))
        ->map(function($ancestor) { 

            $attrs = ["class" => "breadcrumb-item"];
            
            if($ancestor->ID === get_the_ID()) {
                $attrs["class"] .= " active";
                $attrs["aria-current"] = "page";
            }

            $attrs["href"] = get_permalink($ancestor);

            $content = $ancestor->ID === get_the_ID() 
                ? $ancestor->post_title
                : tryPartial("anchor", [
                    "text" => $ancestor->post_title,
                    "href" => get_permalink($ancestor),
                    "attributes" => render_attrs($attrs),
                ])->getOrElse("");

            return tryPartial("list-item", [
                "content" => $content,
                "attributes" => "class=\"breadcrumb-item\"",
            ])->getOrElse("");
        })
        ->join("");

    $list = tryPartial("list", [
        "list_type" => "ol",
        "list_items" => $content,
        "attributes" => "class=\"breadcrumb\"",
    ])->getOrElse("");
    
    # end data processing
	return "<nav aria-label=\"breadcrumb\" class=\"container\">{$list}</nav>";
};

# src: 