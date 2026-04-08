<?php

use lray138\G2\{Kvm, Str, Lst, Num, Maybe, Either, Nil, Result\Ok, Result\Err, Result};
use function lray138\g2\{wrap, dump, apply, curryN, flipN};

function getOuterWrappers(Lst $attrs): Lst {
    return $attrs
        ->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "outer")
        //->filter(fn($x) => $x["wrap_type"] == "outer")
        ->map(function($x) {
            $attrs = reduce_attrs($x["wrap_attrs"]);
            $element = isset($attrs["element"]) ? $attrs["element"] : "div";
            unset($attrs["element"]);
            return [
                "element" => $element,
                "attrs" => render_attrs($attrs)
            ];
        })
        ;
}

function getInnerWrappers(Lst $attrs): Lst {
    $out = $attrs
        //->filter(fn($x) => $x["_type"] == "inner_wrap")
        ->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "inner")
        ->map(function($x) {
            $attrs = reduce_attrs($x["wrap_attrs"]);
            $element = isset($attrs["element"]) ? $attrs["element"] : "div";
            unset($attrs["element"]);
            return [
                "element" => $element,
                "attrs" => render_attrs($attrs)
            ];
        })
        ;

    return $out;
}

// handle page section
function handlePageSection( Kvm $section ): Str {
    // here $element is $section 

    $element = tryPartialCallable($section->prop('_type'))
        ->getOrElse(include dirname(__DIR__) . '/partials/elements/section/index.php');

    $tryAttrsId = fn() => $section->tryProp('_type')
        ->map(fn(Str $t) => $t->append("_attrs"));

    $tryAttributes = fn() => $tryAttrsId()
        ->bind(fn(Str $t) => $section->tryProp($t));

    $attributes = $tryAttributes()
        ->map(fn(Lst $x) => renderAttributes($x))
        ->getOrElse(Str::of(''));

    $inner_wrappers = $tryAttributes()
        ->map(fn(Lst $x) => getInnerWrappers($x))
        ->getOrElse(Lst::of([]));

    if($inner_wrappers->count()->get() > 0) {
        $inner_wrap_start = $inner_wrappers
            ->map(fn ($w) => "<{$w['element']} {$w['attrs']}>")
            ->join('');

        $inner_wrap_end = Lst::of(array_reverse($inner_wrappers->get()))
            ->map(fn ($w) => "</{$w['element']}>")
            ->join('');
    } else {
        $inner_wrap_start = '';
        $inner_wrap_end = '';
    }

    $inner_wrap_callable = "";
    // $inner_wrap_callable = $section
    //     ->mprop($attrs_id)
    //     ->bind(fn($x) => 
    //         $x->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "blog_wrap_wide")
    //           ->map(fn($x) => [
    //             "callable" => "blogWrapWide"
    //           ])
    //           ->mhead()
    //           ->bind(fn(Lst $x) => Kvm::of($x)->mprop("callable"))
    //     )
    //     ->getOrElse($section->mprop($attrs_id)
    //     ->bind(fn(Lst $x) => 
    //         $x->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "blog_wrap")
    //           ->map(fn($x) => [
    //             "callable" => "blogWrap"
    //           ])
    //           ->mhead()
    //           ->bind(fn($x) => Kvm::of($x)->mprop("callable"))
    //     )
    //     ->getOrElse(''));

    // this is where we need docs to point to and .. I'm not even sure what this
    // does... 
    $data = $section->mprop('data')
        ->map(fn($x) => $x->get())
        ->getOrElse([]);

    // brittle code to auto wrap blog
    if(isset($data["page_template"]) && $data["page_template"] == "templates/blog-page.php" && empty($inner_wrap_callable)) { 
        $inner_wrap_callable = "blogWrap";
    }

    // element is the partial callable we should try 'ap' here
    $content = Str::of($element([
        "attributes" => $attributes,
        "inner_wrap_start" => $inner_wrap_start,
        "inner_wrap_end" => $inner_wrap_end,
        "inner_wrap_callable" => $inner_wrap_callable,
        "content" => concatSectionPartials($section),
    ]));

    //$outer_wrappers = getOuterWrappers($section->prop($attrs_id));
    $outer_wrappers = $tryAttrsId()
        ->bind(fn(Str $t) => $section->tryProp($t))
        ->map(fn(Lst $x) => getOuterWrappers($x))
        ->getOrElse(Lst::of([]));

    if($outer_wrappers->count()->get() > 0) {
        return $outer_wrappers
            ->reduce(function($c, $x) {
                $attrs = isset($x["attrs"]) ? " " . $x["attrs"] : "";
                $el = $x["element"];
                return $c->wrap("<{$el}{$attrs}>", "</{$el}>");
            }, $content);
    }

    return $content;
}

function handle_file(Kvm $partial): Str {
    $file_id = $partial->prop("code_file")->get();

    $file_path = get_attached_file($file_id);
    $contents = file_get_contents($file_path);

    if($partial->prop("escape")->isTrue()) {
        $contents = "<pre class=\"mb-0 bg-light p-3 rounded\"><code>" . htmlspecialchars($contents, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code></pre>";
    } else {
        $contents = "<div class=\"px-1 p-3 rounded\">" . $contents . "</div>";
    }

    return Str::of($contents);   
}

function concatSectionPartials( Kvm $section ): Str {
    // complex_field is the key of the partial to use
    $out = $section->mprop('complex_field')
        ->bind(fn(Str $key) => $section->mprop($key))
        ->map(fn(Lst $partials) => 
            $partials->reduce(function(array $acc, array $partial) use ($section) {
                $partial = Kvm::of($partial);
                return [$acc[0]->append(handleSectionPartial(
                    $partial->set( 'index', $acc[1] )
                            ->set( 'id', $section->prop('id')->append("_p$acc[1]"))
                )), $acc[1] + 1];
            }, [Str::of(''), 0])[0]
        )
        ->getOrElse(Str::of(''));

    return $out;
}

function concatPartials(Lst $partials): Str {
    $out = Str::of( '' );

    $partials->forEach( function( Kvm $p, Num $n ) use ( &$out ) {
        $out = $out->append(handleSectionPartial(
            $p->set( 'index', $n )
              ->set( 'id', Str::of("p")->append($n))
        ));
    });

    return $out;
}

/**
 * Ordered filesystem candidates for a partial slug (bare name, path, or .php file).
 * Handles: heading → components/heading/index.php, components/gallery-card.php as-is, header aliases, etc.
 *
 * @return list<string>
 */
function bp_build_partial_path_candidates(string $partials_root, string $rel): array {
    $partials_root = rtrim($partials_root, '/');
    $rel = trim(str_replace('\\', '/', $rel), '/');
    $seen = [];
    $out = [];
    $add = static function (string $path) use (&$seen, &$out): void {
        if ($path === '' || isset($seen[$path])) {
            return;
        }
        $seen[$path] = true;
        $out[] = $path;
    };

    if ($rel === '') {
        return [];
    }

    $middles = ['components', 'patterns', 'elements'];

    $stem = preg_match('/\.php$/i', $rel) ? substr($rel, 0, -4) : $rel;
    $stem = trim($stem, '/');

    // Legacy: header partial is partials/header.php not partials/header/index.php
    if ($rel === 'header/index.php' || $stem === 'header/index' || $stem === 'header') {
        $add($partials_root . '/header.php');
    }

    // Exact path under partials root (e.g. components/gallery-card.php)
    $add($partials_root . '/' . $rel);

    $stem_has_middle = false;
    foreach ($middles as $m) {
        if (strncmp($stem, $m . '/', strlen($m) + 1) === 0) {
            $stem_has_middle = true;
            $add($partials_root . '/' . $stem . '.php');
            $add($partials_root . '/' . $stem . '/index.php');
            break;
        }
    }

    if (! $stem_has_middle) {
        $add($partials_root . '/' . $stem . '.php');
        $add($partials_root . '/' . $stem . '/index.php');

        foreach ($middles as $m) {
            $add($partials_root . '/' . $m . '/' . $stem . '.php');
            $add($partials_root . '/' . $m . '/' . $stem . '/index.php');
        }
    }

    return $out;
}

function getPartialPath(Str $type) {

    $type_str = $type->get();
    $partials_root = dirname(__DIR__) . '/partials';

    if (strpos($type_str, '/site/') === 0) {
        $partials_root = WP_CONTENT_DIR . '/site/partials';
        $type = Str::of(ltrim(substr($type_str, strlen('/site/')), '/'));
    } elseif (strpos($type_str, '/bp/') === 0) {
        $type = Str::of(ltrim(substr($type_str, strlen('/bp/')), '/'));
    }

    $rel = ltrim($type->get(), '/');
    if (strncmp($rel, 'partials/', strlen('partials/')) === 0) {
        $rel = substr($rel, strlen('partials/'));
    } elseif ($rel === 'partials') {
        $rel = '';
    }
    $rel = ltrim($rel, '/');

    if ($rel === '') {
        return Nil::unit();
    }

    foreach (bp_build_partial_path_candidates($partials_root, $rel) as $candidate) {
        if (file_exists($candidate)) {
            return Str::of($candidate);
        }
    }

    return Nil::unit();
}

function tryPartialPath($type) {
    $path = getPartialPath($type instanceof Str ? $type : Str::of($type));

    return $path instanceof Str
        ? Ok::of($path)
        : Err::of("Partial not found");
}

function ensurePhp(Str $t): Str
{
    return $t->endsWith(".php")
        ->match(
            fn() => $t->concat(Str::of(".php")),
            fn() => $t
        );
}

const ensurePhp = __NAMESPACE__ . '\\ensurePhp';

// the "_type" i.e. partial name
function tryPartialCallable(Str $type): Result {
    return tryPartialPath($type)
        ->map(fn(Str $x) => include($x->get()));
}

function bp_get_page_by_bp_id($bp_id) {
    global $wpdb;

    $like = '%' . $wpdb->esc_like($bp_id) . '%';

    $post_id = $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT pm.post_id
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p
                ON p.ID = pm.post_id
            WHERE pm.meta_value LIKE %s
              AND p.post_type NOT IN ('revision', 'attachment')
              AND p.post_status NOT IN ('auto-draft', 'inherit')
            LIMIT 1
            ",
            $like
        )
    );

    return $post_id ? (int) $post_id : null;
}


// there are hardcode functions and then there are the partials 
// coming from the webpack set up, so if the handle_ function exists, use it. 
// otherwise check for a data mutator and call it then try to use a partial
function handleSectionPartial(Kvm $partial): Str {    

    // not sure why this is happening?
    if($partial->prop('_type') === 'file') {
        $partial = $partial->set('_type', Str::of('file'));
    }

    // this is an example where prop is always going to exist, but 
    // as comprehension grows and being comfortable working in the Monadic context 
    // I think mprop would be the default... however... Maybe not. hah, nah maybe not was cursor autocomplete.
    return $partial
        ->tryProp('_type')
        ->map(fn(Str $t) => $t->prepend("handle_"))
        ->pipe(fn(Result $r): Either
            => $r->map(function(Str $callable) use ($partial) {
                // this was original demo method and the ultimate override
                if(function_exists($callable->get())) {

                    $content = Str::of($callable->get()($partial));
                    
                    $val = $partial->mprop("bp_id")
                        ->bind(function(Str $bp_id) {
                            if(empty($bp_id->get())) {
                                return Maybe::nothing();
                            }

                            $x = bp_get_page_by_bp_id($bp_id);
                            $x = get_edit_post_link($x) . "&bp_edit=$bp_id";
                            return Maybe::just($x); 
                        })
                        ->map(fn($x) => Str::of("<div data-bp-edit-url=\"$x\">$content</div>"))
                        ->getOrElse($content);

                    return Either::left($val);
                }

                return Either::right('asdf');
            })
            ->getOrElse(Either::left(''))
        )
        ->map(function() use ($partial) {
          
            $controller = $partial->prop('_type')->wrap("handle_", "_data")->get();
    
            if(function_exists($controller)) {
                $partial = $controller($partial); // partial is already a Kvm
            }
            
            // this does become a little 'esoteric' when read in reverse
            return tryPartialCallable($partial->prop('_type'))
                ->map(fn($callable) => Str::of($callable($partial->get())))
                ->getOrElse(Str::of("Unknown partial type: {$partial->prop('_type')}"));
        })
        ->fold(
            fn($x) => $x,
            fn($x) => $x
        );

}

function handleSectionPartial_old(Kvm $partial): Str {    

    // not sure why this is happening?
    if($partial->prop('_type') === 'file') {
        $partial = $partial->set('_type', Str::of('file'));
    }

    // this is an example where prop is always going to exist, but 
    // as comprehension grows and being comfortable working in the Monadic context 
    // I think mprop would be the default... however... Maybe not. hah, nah maybe not was cursor autocomplete.
    $callable = $partial
        ->prop('_type')
        ->prepend("handle_");

    // this was original demo method and the ultimate override
    if(function_exists($callable->get())) {
        $content = Str::of($callable->get()($partial));
        
        return $partial->mprop("bp_id")
            ->bind(function(Str $bp_id) {
                if(empty($bp_id->get())) {
                    return Maybe::nothing();
                }

                $x = bp_get_page_by_bp_id($bp_id);
                $x = get_edit_post_link($x) . "&bp_edit=$bp_id";
                return Maybe::just($x); 
            })
            ->map(fn($x) => Str::of("<div data-bp-edit-url=\"$x\">$content</div>"))
            ->getOrElse($content);
    }
    
    $controller = $partial->prop('_type')->wrap("handle_", "_data")->get();
    
    if(function_exists($controller)) {
        $partial = $controller($partial); // partial is already a Kvm
    }
    
    // this does become a little 'esoteric' when read in reverse
    return tryPartialCallable($partial->prop('_type'))
        ->map(fn($callable) => Str::of($callable($partial->get())))
        ->getOrElse(Str::of("Unknown partial type: {$partial->prop('_type')}"));
}

function concatPageSections(Lst $sections, $data = [] ): Str {
    $out = Str::of( '' );

    extract($data);
    $type = $type ?? 'partials';

    $out = $sections->reduce(function(array $acc, array $x) use (&$out, $type, $data) {
        $x = Kvm::of($x);
        return [$acc[0]->append(handlePageSection(
            $x->set( 'index', $acc[1] )
                ->set( 'id', Str::of("s")->append($acc[1]) ) 
                ->set( 'complex_field', $type )
                ->set( 'data', $data )
        )), $acc[1] + 1];
    }, [Str::of(''), 0]);

    return Str::of($out[0]);
}

function zip_type_value(array $item): ?array
{
    if (!isset($item['_type'])) {
        return null;
    }

    $type = $item['_type'];

    if (!array_key_exists($type, $item)) {
        return null;
    }

    return [$type, $item[$type]];
}

function zip_type_values(array $items): array
{
    return array_values(array_filter(array_map(
        'zip_type_value',
        $items
    )));
}

function reduce_attrs(array $items): array
{
    return array_reduce($items, function (array $acc, array $item): array {
        $type = $item['_type'] ?? null;

        if($type == "data-") {
            $acc["data-{$item["attr"]}"] = $item["val"];
            return $acc;
        }
 
        if (!$type || !isset($item[$type])) {
            return $acc;
        }

        $acc[$type] = $item[$type]; // last one wins
        return $acc;
    }, []);
}

function render_attrs(array $attrs): string
{
    return implode(' ', array_map(
        fn($k, $v) =>
            sprintf('%s="%s"', $k, htmlspecialchars((string)$v, ENT_QUOTES)),
        array_keys($attrs),
        $attrs
    ));
}

function renderAttributes(Lst $attrs): Str
{
    return Str::of(render_attrs(handleAttributesField($attrs)->get()));
}

function get_config_page(string $template): ?WP_Post
{
    $slug = basename($template, '.php');

    $q = new WP_Query([
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'name'           => $slug,
        'meta_query'     => [
            [
                'key'   => '_wp_page_template',
                'value' => 'templates/template-config.php',
            ],
        ],
    ]);

    return $q->have_posts() ? $q->posts[0] : null;
}

function tryConfigPage(string $template): Result
{
    $slug = basename($template, '.php');

    $q = new WP_Query([
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'name'           => $slug,
        'meta_query'     => [
            [
                'key'   => '_wp_page_template',
                'value' => 'templates/template-config.php',
            ],
        ],
    ]);

    return $q->have_posts() ? Result::ok($q->posts[0]) : Result::err("Config Page not found");
}

function getDefaultPageContent($t): Str { 
    $content = get_the_content();
    $content = apply_filters('the_content', $content);
    $content = tryPartial("wrap/container", [
        "content" => $content
    ])->getOrElse($content);
    return Str::of($content);
}

function pinTopHeader($config_items): bool {
    if (empty($config_items) || ! is_array($config_items)) {
        return false;
    }

    foreach ($config_items as $config_item) {
        if (! is_array($config_item)) {
            continue;
        }

        $type = $config_item['_type'] ?? '';

        // Footer layout also exposes pin_top / header extras (see getPageConfigFields).
        if ($type === 'footer_items' && ! empty($config_item['pin_top'])) {
            return true;
        }

        $attr_lists = [];
        if ($type === 'page_config_header_items' && isset($config_item['header_config_attrs'])) {
            $attr_lists[] = $config_item['header_config_attrs'];
        }
        if ($type === 'header' && isset($config_item['header_attrs'])) {
            $attr_lists[] = $config_item['header_attrs'];
        }
        if ($type === 'header_items' && isset($config_item['header_attrs'])) {
            $attr_lists[] = $config_item['header_attrs'];
        }

        foreach ($attr_lists as $header_attrs) {
            if (! is_array($header_attrs)) {
                continue;
            }
            foreach ($header_attrs as $header_item) {
                if (($header_item['_type'] ?? '') === 'pin_top' && ! empty($header_item['pin_top'])) {
                    return true;
                }
            }
        }
    }

    return false;
}

function getHeaderClassExtras($config_items): string {
    if (empty($config_items) || ! is_array($config_items)) {
        return '';
    }

    foreach ($config_items as $config_item) {
        if (! is_array($config_item)) {
            continue;
        }

        $type = $config_item['_type'] ?? '';

        if ($type === 'footer_items' && ! empty($config_item['header_class_extras'])) {
            return (string) $config_item['header_class_extras'];
        }

        $attr_lists = [];
        if ($type === 'page_config_header_items' && isset($config_item['header_config_attrs'])) {
            $attr_lists[] = $config_item['header_config_attrs'];
        }
        if ($type === 'header' && isset($config_item['header_attrs'])) {
            $attr_lists[] = $config_item['header_attrs'];
        }

        foreach ($attr_lists as $header_attrs) {
            if (! is_array($header_attrs)) {
                continue;
            }
            foreach ($header_attrs as $header_item) {
                if (($header_item['_type'] ?? '') === 'class' && ! empty($header_item['class'])) {
                    return (string) $header_item['class'];
                }
            }
        }
    }

    return '';
}

/**
 * Data for blueprint muuri-item.php partial from an attachment post.
 */
function bp_muuri_item_vars_from_attachment(WP_Post $post): array
{
    $full = wp_get_attachment_image_url($post->ID, 'full');
    $thumb = $full;
    if (! $full) {
        $full = wp_get_attachment_url($post->ID);
    }
    if (! $thumb) {
        $thumb = $full ?: '';
    }

    $caption = wp_get_attachment_caption($post->ID);
    $figcaption_html = $caption !== ''
        ? '<figcaption class="small text-secondary mt-1 px-1">' . esc_html($caption) . '</figcaption>'
        : '';

    $alt_text = (string) get_post_meta($post->ID, '_wp_attachment_image_alt', true);
    if ($alt_text === '') {
        $alt_text = $post->post_title;
    }

    return [
        'itemId' => (string) $post->ID,
        'title' => $post->post_title,
        'href' => get_attachment_link($post->ID),
        'fullSrc' => $full ?: '',
        'thumbSrc' => $thumb,
        'altText' => $alt_text,
        'figcaptionHtml' => $figcaption_html,
        'bpGalleryOpts' => wp_json_encode(['loop' => true, 'gallery' => '#gallery']),
    ];
}

/**
 * Attachment IDs from Carbon media_gallery / gallery field value (handles int or row arrays).
 *
 * @param mixed $raw Value from carbon_get_post_meta( $id, 'gallery' ).
 * @return int[]
 */
function bp_gallery_field_attachment_ids($raw): array
{
    if (! is_array($raw)) {
        return [];
    }

    $out = [];
    foreach ($raw as $row) {
        if (is_numeric($row)) {
            $out[] = (int) $row;
            continue;
        }
        if (is_array($row)) {
            $id = $row['id'] ?? $row['attachment_id'] ?? null;
            if (is_numeric($id)) {
                $out[] = (int) $id;
            }
        }
    }

    return array_values(array_filter(array_unique($out)));
}


function renderPageContent($page_id, $data = []) {
    // check head options

    // check header option
    $config_items = carbon_get_post_meta($page_id, 'page_config_items');

    // check footer options
    $slug = get_page_template_slug($page_id);
    
    if(is_attachment($page_id) || empty($slug)) {
        $slug = "default-page";
    }

    // each page must have a configuration 
    // we do not default so it will return a blank page otherwise
    return tryConfigPage($slug)
        // get template sections from config page
        ->bind(function(WP_Post $p) {
            $r = carbon_get_post_meta($p->ID, "template_sections");
            return is_null($r) 
                ? Result::err("Template sections not found") 
                : Result::ok(Lst::of($r));            
        })
        ->map(fn(Lst $sections) => handleTemplateSections($sections, $config_items, $page_id, $slug, $data))
        ->getOrElse("");
}

function tryCarbonPostMeta(...$args): Result
{
    $f = function($field_id, $page_id) {
        $r = carbon_get_post_meta($page_id, $field_id);
        return is_null($r) 
            ? Result::err("Carbon post meta not found") 
            : Result::ok(Lst::of($r));
    };

    return call_user_func_array(curryN(2, $f), $args);
}

function handleTemplateSections(Lst $sections, $config_items, $page_id, $slug, $data = []) {
    return $sections
        ->map(function($section) use ($config_items, $page_id, $slug, $data) {

            switch($section["_type"]) {
                case "partial_path":
                    $data = [];

                    // returns a Maybe
                    if(in_array($section["partial_path"], ["header", "header/original", "/site/header.php"])) {
                        $nav_parts = [];
                        if (pinTopHeader($config_items)) {
                            $nav_parts[] = 'pin-top';
                        }
                        $header_class_extras = getHeaderClassExtras($config_items);
                        if ($header_class_extras !== '') {
                            foreach (preg_split('/\s+/', trim($header_class_extras), -1, PREG_SPLIT_NO_EMPTY) as $c) {
                                $nav_parts[] = $c;
                            }
                        }
                        if (! empty($nav_parts)) {
                            $data['section_extra_classes'] = implode(' ', array_unique($nav_parts));
                        }
                    }

                    // looks like we are assuming 
                    $out = tryPartialCallable(Str::of($section["partial_path"]))
                        ->map(apply($data))
                        ->getOrElse("partial path not found");

                    // $out = Str::of($c(array_merge($data, [
                        // "data-bp-edit-url" => tryGetPartialPath($section["partial_path"])
                        //     ->map(fn(Str $path) => $path->prepend('vscode://file'))
                        //     ->getOrElse(Str::of(''))
                    // ])));

                    // if($section["bp_edit"] && !empty($section["bp_edit"])) {
                    //     $out = tryGetPartialPath($section["partial_path"])
                    //         ->map(fn(Str $path) => $path->prepend('vscode://file'))
                    //         ->map(fn(Str $path) => $out->wrap(
                    //                 "<div class=\"t\" data-bp-edit-url='$path'>",
                    //                 "</div>"
                    //             )
                    //         )
                    //         ->getOrElse('');
                    // }

                    return Str::of($out);

                    break;
                case "page_content":
                    // "field_id" is the outer unique ID for each page type, e.g. "universal_page_sections"
                    // and contains the page sections (array)

                    if(isset($data["content"])) {
                        return Str::of($data["content"]);
                    }

                    return tryCarbonPostMeta($section["field_id"], $page_id)
                        ->map(flipN(2, 'concatPageSections')([
                            "page_template" => $slug
                        ]))
                        ->getOrElse("");

                    break;
                case "partial_page":
                    //dump($section);
                    $id = $section["partial_pages"][0]["id"];
                    // this is where it should be LISO and wrap inside if needed... 
                    return handle_partial_page_id(Kvm::of(["page_id" => $id]));
                    break;
                case "callable":
                    $callable = $section["callable"];
                    if(function_exists($callable)) {
                        return $callable($page_id);
                    }
                    break;
                default:
                    die("Missconfig");
                die;
            }
            
        })
        ->join('')
        ->get();
}

function getDocsSidebarNav($docs_homepage_id): Str {

    return Lst::of(get_pages([
        'parent' => $docs_homepage_id,
        'sort_column' => 'menu_order',
    ]))->map(function ($page) {
        $children = getPageChildren($page->ID)
            ->map(fn($child) => tryPartial('list-item', [
                'attributes' => '',
                'content' => tryPartial('anchor', [
                    "href" => "#",
                    "text" => $child->post_title,
                    "attributes" => 'href="' . get_permalink($child) . '"',
                ])->getOrElse(""),
            ])->getOrElse(""))
            ->join('')
            // this is failing because I'm returning the Maybe?
            ;

        $children = tryPartial('list', [
            "list_type" => "ul",
            "attributes" => "class=\"list mb-6 ps-0 list-unstyled\"",
            "list_items" => $children,
        ])->getOrElse(Str::of(""));
    
        $anchor = tryPartial('anchor', [
            "attributes" => 'class="section-heading" href="' . get_permalink($page) . '"',
            "href" => get_permalink($page),
            "text" => tryPartial('heading', [
                "level" => 6,
                "attributes" => 'class="text-uppercase fw-bold"',
                "text" => $page->post_title,
            ])->getOrElse(""),
        ])->getOrElse("");
    
        return tryPartial("docs/sidebar/section", [
            "anchor" => $anchor,
            "list" => $children
        ])->getOrElse(Str::of("?"));
    })->join('');
}

function getDocsMainContent($current_page_id) {
    
    $breadcrumbs = (include(get_template_directory() . '/partials/components/breadcrumbs/index.php'))([
        "current_page_id" => $current_page_id,
    ]);

    $sections = Lst::of(carbon_get_post_meta( get_the_ID(), 'docs_page_sections' ));

    $content = (include(get_template_directory() . '/partials/docs/main.php'))([
        "sidebar" => (include(get_template_directory() . '/partials/docs/sidebar.php'))([
            "sections" => getDocsSidebarNav(195)
        ]),
        "content" => 
            $breadcrumbs . concatPageSections($sections),
        "jump_to_sidebar" => tryPartial('docs/jump-to', [])->getOrElse("")
    ]);

    return $content;
}

/**
 * Add "link-secondary" to nav anchor classes when the anchor points to $currentPageId.
 *
 * Mutates and returns the same array structure.
 */
function markCurrentNavLink(array $list, int $currentPageId, string $activeClass = 'link-secondary'): array
{
    if (!isset($list['items']) || !is_array($list['items'])) {
        return $list;
    }

    foreach ($list['items'] as &$item) {
        if (!is_array($item)) continue;

        // item["content"] is an array of components; we care about "_type" === "anchor"
        if (!isset($item['content']) || !is_array($item['content'])) continue;

        foreach ($item['content'] as &$component) {
            if (!is_array($component) || ($component['_type'] ?? null) !== 'anchor') continue;

            // Try to read the internal page id (as shown in your dump)
            $linkedId = $component['link'][0]['page'][0]['id'] ?? null;
            if ($linkedId === null) continue;

            // Compare as ints to handle string ids like "195"
            if ((int)$linkedId !== $currentPageId) continue;

            // Ensure anchor_attrs exists
            if (!isset($component['anchor_attrs']) || !is_array($component['anchor_attrs'])) {
                $component['anchor_attrs'] = [];
            }

            // Find existing class attr (Carbon stores attrs as a list of ["_type" => "class", "class" => "..."])
            $classIndex = null;
            foreach ($component['anchor_attrs'] as $i => $attr) {
                if (is_array($attr) && ($attr['_type'] ?? null) === 'class') {
                    $classIndex = $i;
                    break;
                }
            }

            if ($classIndex === null) {
                // No class attr yet — add one
                $component['anchor_attrs'][] = [
                    '_type' => 'class',
                    'class' => $activeClass,
                ];
            } else {
                $existing = (string)($component['anchor_attrs'][$classIndex]['class'] ?? '');

                // Append only if not already present (safe whitespace check)
                if (!preg_match('/(^|\s)' . preg_quote($activeClass, '/') . '(\s|$)/', $existing)) {
                    $component['anchor_attrs'][$classIndex]['class'] =
                        trim($existing . ' ' . $activeClass);
                }
            }
        }
        unset($component);
    }
    
    unset($item);
    return $list;
}



function headerNavUpdateCurrentLink(Kvm $partial) {

    $partial = $partial->get();

    $partial = markCurrentNavLink($partial, get_the_ID());

    return Kvm::of($partial);
}


function is_image($post): bool {
    return wp_attachment_is_image($post);
}

/**
 * Get direct child pages.
 *
 * Usage:
 * getPageChildren([
 *   'page_id' => 234,
 *   'query'   => [ 'post_status' => 'publish' ]
 * ]);
 */
function getChildren(array $data = []): Lst
{
    $page_id = isset($data['page_id']) ? (int) $data['page_id'] : 0;
    if (!$page_id) return [];

    $defaults = [
        'post_type'      => 'page',
        'post_parent'    => $page_id,
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => [
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ],
        'no_found_rows'  => true,
    ];

    $query_args = array_merge(
        $defaults,
        $data['query'] ?? []
    );

    $q = new WP_Query($query_args);

    return Lst::of($q->posts); // array<WP_Post>
}

function getBlogPosts(array $data = []): Lst
{
    // Optional inputs
    $limit  = isset($data['limit']) ? (int) $data['limit'] : 10;
    $paged  = isset($data['paged']) ? max(1, (int) $data['paged']) : 1;
    $status = $data['post_status'] ?? 'publish';

    // Default template
    $template = $data['template'] ?? 'templates/blog-page.php';

    $defaults = [
        'post_type'      => 'page',
        'post_status'    => $status,

        // ✅ Page template filter
        'meta_query'     => [
            [
                'key'   => '_wp_page_template',
                'value' => $template,
            ],
        ],

        'posts_per_page' => $limit,
        'paged'          => $paged,

        // Sensible page ordering
        'orderby'        => [
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ],

        // Performance
        'no_found_rows'  => true,
    ];

    // Merge user overrides (Blueprint-style)
    $query_args = array_merge(
        $defaults,
        $data['query'] ?? []
    );

    $q = new WP_Query($query_args);

    return Lst::of($q->posts); // array<WP_Post>
}


/**
 * Get ALL descendants (flattened list).
 */
function getDescendants(array $data = []): Lst
{
    $page_id = isset($data['page_id']) ? (int) $data['page_id'] : 0;
    if (!$page_id) return [];

    $descendants = [];

    $children = getPageChildren($data);

    foreach ($children as $child) {
        $descendants[] = $child;

        $descendants = array_merge(
            $descendants,
            getPageDescendants([
                ...$data,
                'page_id' => $child->ID,
            ])
        );
    }

    return Lst::of($descendants);
}

/**
 * Get a nested page tree.
 *
 * Returns:
 * [
 *   [
 *     'page'     => WP_Post,
 *     'children' => [ ... ]
 *   ],
 *   ...
 * ]
 */
function getDescendantsTree(array $data = []): array
{
    $page_id = isset($data['page_id']) ? (int) $data['page_id'] : 0;
    if (!$page_id) return [];

    $tree = [];

    $children = getChildren($data);

    foreach ($children as $child) {
        $tree[] = [
            'page'     => $child,
            'children' => getDescendantsTree([
                ...$data,
                'page_id' => $child->ID,
            ]),
        ];
    }

    return Lst::of($tree);
}

function getPageLink(WP_Post $page): Str {

    $text = $page->post_title;

    if(empty($text)) {
        $text = "Untitled";
    }

    return Str::of("<a href='" . get_permalink($page) . "'>" . $text . "</a>");
}

function blogWrapWide($content): Str {
    return tryPartial("wrap/container-blog-wide", [
        "content" => $content
    ])
        ->map(fn($x) => Str::of($x))
        ->getOrElse("issue with wrap");
}

function blogWrap($content): Str {
    return tryPartial("wrap/container-blog", [
        "content" => $content
    ])
        ->map(fn($x) => Str::of($x))
        ->getOrElse(Str::of("issue with wrap"));
}