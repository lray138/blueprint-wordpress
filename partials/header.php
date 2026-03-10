<?php
# start use
use lray138\g2\Lst;
use function lray138\g2\dump;
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
    
    // die;
    # end data processing
	return "<nav class=\"navbar navbar-expand-lg py-0\" aria-label=\"Offcanvas navbar large\" data-bp-edit-url=\"/wp-admin/post.php?post=123&action=edit\"><div class=\"{$container_class} border-bottom py-3 mb-3\"><div class=\"col-md-3 mb-2 mb-md-0\">{$site_name_anchor}</div><button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"offcanvas\" data-bs-target=\"#offcanvasNavbar2\" aria-controls=\"offcanvasNavbar2\" aria-label=\"Toggle navigation\"><span class=\"navbar-toggler-icon\"></span></button><div class=\"offcanvas offcanvas-end\" tabindex=\"-1\" id=\"offcanvasNavbar2\" aria-labelledby=\"offcanvasNavbar2Label\"><div class=\"offcanvas-header\">{$site_name_anchor}<!-- <h5 class=\"offcanvas-title\" id=\"offcanvasNavbar2Label\">Blueprint</h5> --><button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"offcanvas\" aria-label=\"Close\"></button></div><div class=\"offcanvas-body d-flex flex-wrap align-items-center text-center\"><ul class=\"nav list-group list-group-sm col-12 col-md-auto mb-2 mb-md-0 flex-grow-1 pe-3 \" data-bp-edit-url=\"/wp-admin/post.php?post=123&action=edit\"><!-- align-items-center justify-content-center justify-content-md-between --><li class=\"nav-item\"><a class=\"nav-link\" href=\"{$base_url}/manifesto/\">Manifesto</a></li><li class=\"nav-item\"><a class=\"nav-link active\" aria-current=\"page\" href=\"{$base_url}/showcase/\">Showcase</a></li><li class=\"nav-item\"><a class=\"nav-link\" href=\"{$base_url}/blog/\">Blog</a></li><!-- <li class=\"nav-item dropdown\">
                        <a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"> Dropdown </a>
                        <ul class=\"dropdown-menu start-50 translate-middle-x\">
                            <li>
                                <a class=\"dropdown-item\" href=\"#\">Action</a>
                            </li>
                            <li>
                                <a class=\"dropdown-item\" href=\"#\">Another action</a>
                            </li>
                            <li>
                                <hr class=\"dropdown-divider\">
                            </li>
                            <li>
                                <a class=\"dropdown-item\" href=\"#\">Something else here</a>
                            </li>
                        </ul>
                    </li> --></ul><div class=\"col-md-3 text-end mx-auto\">{$flex_end_content}</div></div></div></div></nav>";
};

# src: 