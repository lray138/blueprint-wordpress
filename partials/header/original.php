<?php
# start use
use lray138\G2\{Kvm, Lst};
use function lray138\G2\dump;
# end use

return function($data = []) { 
	# start data processing
    extract($data);

    $nav_links = "";
    $partials = Lst::of(carbon_get_post_meta('226', 'partials'));


    $items = $partials->ehead()->get()["items"];
    $new_items = [];
    foreach($items as $item) {
        // this is why the $path is actually a cool method

        if(!isset($item["content"][0]["link"][0])) continue;
        $id = $item["content"][0]["link"][0]["page"][0]["id"];

        if($id == get_the_ID()) {
            $t = &$item["content"][0];

            foreach($t["anchor_attrs"] as &$attr) {
                
                if($attr["_type"] == "class") {
                    $attr["class"] = $attr["class"] . " text-secondary";
                    break;
                }
            }

            //$item["content"][0]["class"] = $item["content"][0]["class"] . " link-secondary";
        }
        $new_items[] = $item;
    }

    $partials_tmp = $partials->get();
    $partials_tmp[0]["items"] = $new_items;
    $partials = Lst::of($partials_tmp);

    $nav_links = concatPartials($partials);
    
    $partials = Lst::of(carbon_get_post_meta('251', 'partials'));
    $site_name_anchor = concatPartials($partials);

    $flex_end_content = "";

    $partials = Lst::of(carbon_get_post_meta('256', 'partials'));
    $flex_end_content = concatPartials($partials);


    // this just popped up Wed Jan 14 at 14:02
    $header_class_extras = $data['header_class_extras'] ?? '';
    $section_extra_classes = $data['section_class_extras'] ?? '';

    $container_class = "";
    $base_url = "";

    $section_attrs = "";
    if(isset($data["data-bp-edit-url"])) {
        $section_attrs = 'data-bp-edit-url="' . $data["data-bp-edit-url"] . '"';
    }

    # end data processing
	return "<section class=\"header {$section_extra_classes}\" {$section_attrs}><div class=\"container\"><header class=\"d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-3 border-bottom {$header_class_extras}\"><div class=\"col-md-3 mb-2 mb-md-0\">{$site_name_anchor}</div>{$nav_links}<div class=\"col-md-3 text-end d-flex align-items-center justify-content-end gap-2\">{$flex_end_content}</div></header></div></section>";
};

# src: 