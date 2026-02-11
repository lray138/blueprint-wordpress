<?php

use lray138\G2\{Kvm, Str, Lst, Num, Maybe};
use function lray138\g2\dump;

function handle_partial_page(Kvm $partial_page): Str {

    if(!isset($partial_page->prop('partial_pages')->get()[0])) {
        return Str::of("No page");
    }

    $page = $partial_page->prop('partial_pages')->get()[0]["id"];

    return handle_partial_page_id(Kvm::of(["page_id" => $page]));
};

function maybeOuterWrap() {}

function handle_partial_page_id(Kvm $partial_page_id): Str {

    $page_id = $partial_page_id->prop('page_id');
    $selected_page_id = (int) $page_id->get();

    $template_slug = get_page_template_slug($selected_page_id);
    $complex_field_name = $template_slug === 'templates/form.php' ? 'form_page_items' : 'partials';

    $partials = Lst::of(carbon_get_post_meta($selected_page_id, $complex_field_name));
    $partials = concatPartials($partials);

    $attrs_id = getAttrsFieldNameFromTemplateSlug($template_slug);

    // $attributes = Lst::of();
    if(carbon_get_post_meta($selected_page_id, $attrs_id) == NULL) {
        $attributes = [];
    } else {
        $meta = carbon_get_post_meta($selected_page_id, $attrs_id);
        // this caused an issue wher the array index was 1 ?
        // 
        $lst = Lst::of(array_values($meta));
        $attributes = handleAttributesField($lst)->get();
    } 
 
    $partial_element = isset($attributes["element"]) 
        ? $attributes["element"]
        : "div"
        ;
    
    if($template_slug == "templates/form.php") {
        $partial_element = "form";
    }

    unset($attributes["element"]);
    $attributes = render_attrs($attributes);

    if(!empty($attributes)) {
        $partials = Str::of("<{$partial_element} {$attributes}>{$partials}</{$partial_element}>");
    }

    // $outer_wrappers = Lst::of(carbon_get_post_meta($selected_page_id, $attrs_id))
    //     ->filter(fn($x) => $x["_type"] == "outer_wrap")
    //     ->map(function($x) {
    //         $attrs = reduce_attrs($x["outer_wrap_attrs"]);
    //         $element = isset($attrs["element"]) ? $attrs["element"] : "div";
    //         unset($attrs["element"]);
    //         return [
    //             "element" => $element,
    //             "attrs" => render_attrs($attrs)
    //         ];
    //     })
    //     ;

    // if($outer_wrappers->count()->get() > 0) {
    //     return $outer_wrappers
    //         ->reduce(function($c, $x) {
    //             $attrs = isset($x["attrs"]) ? " " . $x["attrs"] : "";
    //             $el = $x["element"];
    //             return $c->wrap("<{$el}{$attrs}>", "</{$el}>");
    //         }, $partials);
    // }

    return $partials;
};

function sanitize_code_for_pre(string $code): string {
    return htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function handle_code(Kvm $code): Str {
    $file = $code->prop('download_file')->get();
    $file = get_attached_file($file);
    $file = file_get_contents($file);
    return Str::of("file-soon");
};

function handle_download_file(Kvm $download_file): Str {
    $file = $download_file->prop('download_file');
    $file = wp_get_attachment_url($file);
    return Str::of("<a href=\"{$file}\">{$file}</a>");
};

// def not pure ? heh
function handle_page_title(Kvm $page_title): Str {
    return Str::of(get_the_title());
};

function handle_field_group(Kvm $field_group): Str {


    $partials = $field_group->prop('items');
    return Str::of("<div class=\"{$field_group->prop('class')}\">" . concatPartials($partials) . "</div>");
};

function handleAttrsField(Kvm $partial): Kvm
{
    return handleAttributesField($partial->mprop('attrs')->getOrElse(Lst::of([])));
}

function handleAttributesField(Lst $attrs): Kvm
{
    $tmp = [];

    $attrs->wrapMap(function (Kvm $a) use (&$tmp) {
           
        $type = $a->prop("_type")->get();

        switch($type) {
            case "bg_img": 
                if(isset($tmp["style"])) {
                    $tmp["style"] = $tmp["style"] . "; background-image: url(" . wp_get_attachment_image_src($a->prop("bg_img")->get(), 'full')[0] . ")";
                } else {
                    $tmp["style"] = "background-image: url(" . wp_get_attachment_image_src($a->prop("bg_img")->get(), 'full')[0] . ")";
                }
                break;
            case "data-":
                $tmp[$a->prop("attr")->prepend("data-")->get()] = $a->prop("val")->get();
                break;
            case "style":
                if(isset($tmp["style"])) {
                    $tmp["style"] = $tmp["style"] . "; " . $a->mprop("style")->getOrElse("");
                } else {
                    $tmp["style"] = $a->mprop("style")->getOrElse("");
                }
                break;
            case "hx-":
                $tmp["hx-" . $a->prop('attr')->get()] = $a->prop('val')->get();
                break;
            case "wrap":
                // the blog wraps don't have attributes/properties
                return $a->mprop("wrap_attrs")
                    ->map(fn(Lst $attrs) => handleAttributesField($attrs))
                    ->getOrElse(Kvm::of([]));
                //return handleAttributesField($a->prop("wrap_attrs"));;
                break;
            case "outer_wrap":
            case "inner_wrap":
                return handleAttributesField($a->prop($a->prop("_type")->get() . "_attrs"));
                break;
            case "element":
                $tmp["element"] = $a->prop("element");
                break;
            case "modifier":
                break;
            default:
                $tmp[$a->prop("_type")->get()] = $a->prop($type)->get();
                break;
        }

    });

    return Kvm::of($tmp);
}

function handle_input_group(Kvm $input_group): Str {
    $partials = $input_group->prop('items');

    $attrs = handleAttrsField($input_group);
    
    $div = (include get_template_directory() . '/partials/elements/div/index.php')([
        "attributes" => "class=\"input-group {$input_group->prop('class')}\"",
        "content" => concatPartials($partials)
    ]);
    return Str::of($div);
};

function handle_row(Kvm $row): Str {
    $partials = $row->prop('columns');
    return Str::of("<div class=\"row {$row->prop('class')}\">" . concatPartials($partials) . "</div>");
};

function handle_columns(Kvm $row): Str {
    $partials = $row->prop('columns');
    return Str::of("<div class=\"{$row->prop('class')}\">" . concatPartials($partials) . "</div>");
};

function handle_column(Kvm $column): Str {

    if(!is_null($column->prop('partials'))) {
        return Str::of("<div class=\"col {$column->prop('class')}\">" . concatPartials($column->prop('partials')) . "</div>");
    }

    if(!is_null($column->prop('items'))) {
        return Str::of("<div class=\"col {$column->prop('class')}\">" . concatPartials($column->prop('items')) . "</div>");
    }

    return Str::of('column asdf');
};

function handle_span(Kvm $span): Str {
    $attributes = [];
    
    // if($span->prop('class') && $span->prop('class')->get() !== "") {
    //     $attributes["class"] = $span->prop('class');
    // }

    // if($span->prop('style') && $span->prop('style')->get() !== "") {
    //     $attributes["style"] = $span->prop('style');
    // }

    // $attributes = array_merge($attributes, handleAttrsField($span)->get());
    // $attributes = render_attrs($attributes);

    $attributes = $span->mprop("span_attrs")
        ->map(fn(Lst $attrs) => renderAttributes($attrs))
        ->getOrElse("");

    $partials = $span->prop('items');
    if(is_null($partials)) {
        if($span->prop('text')->get()) {
            $content = $span->prop('text');
        } else {
            $content = "";
        }
    } else {
        $content = concatPartials($partials);
    }
    
    return Str::of("<span {$attributes}>{$content}</span>");
};

function handle_text(Kvm $text): Str {
    return Str::of($text->prop('text'));
};

function handle_br(Kvm $br): Str {
    return Str::of("<br/>");
};

function handle_partial_callable(Kvm $partial): Str {

    $data = [];

    $args = $partial->mprop("args")
        ->map(function(Lst $l) use (&$data) {
            $l->map(function($x) use (&$data) {
                
                switch($x["_type"]) {
                    case "arg_text":
                        $data[$x["arg_name"]] = $x["arg_value"];
                        break;
                }

            });
        });
    
    return $partial->mprop("partial_callable")
        ->bind(fn($x) => tryPartial($x, $data))
        ->map(fn(string $x) => Str::of($x))
        ->getOrElse(Str::of('partial not found'));
}

function tryPartial($partial, $args = []): Maybe {
    $out = getPartialCallable(Str::of($partial))
        ->map(fn($callable) => Str::of($callable($args)));
    return $out;
}

function handle_query_render(Kvm $partial): Str {
    
    $env = [
        'page_id' => get_the_ID(),
    ];

    $results = $partial->mprop('query_fns')
        ->map(fn (Lst $rows) =>
        $rows->reduce(fn (Lst $acc, array $row) =>
            (!empty($row['fn']) && function_exists($row['fn']))
            ? $acc->concat(($row['fn'])($env))
            : $acc
        , Lst::of([]))
        )
        ->getOrElse(Lst::of([]));
  

    $out = $partial->mprop('pipeline_fns')
        ->map(fn (Lst $rows) =>
          $rows->reduce(function ($value, array $row) use ($env) {
            $fn = trim((string)($row['fn'] ?? ''));

            if ($fn === '' || !function_exists($fn)) {
                return "$fn - NO FUNC";
                //return $value; // no-op
            }

            // pipeline step: (value, env) -> nextValue
            return $fn($value, $env);
            // if you don't want $row, use: return $fn($value, $env);
          }, $results)
        )
        ->getOrElse(Str::of(""));

    return $out;
}