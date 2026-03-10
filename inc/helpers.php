<?php

use lray138\G2\{Kvm, Str, Lst, Num, Maybe, Nil, Result\Ok, Result\Err};
use function lray138\g2\{wrap, dump};

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
    $element = getPartialCallable($section->prop('_type'))->get();

    $attrs_id = $section->mprop('_type')->get() . "_attrs";

    $attributes = $section->mprop($attrs_id)
        ->map(fn($x) => renderAttributes($x))
        ->getOrElse('');

    $inner_wrappers = getInnerWrappers($section->prop($attrs_id));

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

    $inner_wrap_callable = $section
        ->mprop($attrs_id)
        ->bind(fn($x) => 
            $x->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "blog_wrap_wide")
              ->map(fn($x) => [
                "callable" => "blogWrapWide"
              ])
              ->mhead()
              ->bind(fn(Lst $x) => Kvm::of($x)->mprop("callable"))
        )
        ->getOrElse($section->mprop($attrs_id)
        ->bind(fn(Lst $x) => 
            $x->filter(fn($x) => $x["_type"] == "wrap" && $x["wrap_type"] == "blog_wrap")
              ->map(fn($x) => [
                "callable" => "blogWrap"
              ])
              ->mhead()
              ->bind(fn($x) => Kvm::of($x)->mprop("callable"))
        )
        ->getOrElse(''));

    $data = $section->mprop('data')
        ->map(fn($x) => $x->get())
        ->getOrElse([]);

    // brittle code to auto wrap blog
    if(isset($data["page_template"]) && $data["page_template"] == "templates/blog-page.php" && empty($inner_wrap_callable)) { 
        $inner_wrap_callable = "blogWrap";
    }

    $partials = Str::of($element([
        "attributes" => $attributes,
        "inner_wrap_start" => $inner_wrap_start,
        "inner_wrap_end" => $inner_wrap_end,
        "inner_wrap_callable" => $inner_wrap_callable,
        "content" => concatSectionPartials($section),
    ]));

    $outer_wrappers = getOuterWrappers($section->prop($attrs_id));
    if($outer_wrappers->count()->get() > 0) {
        return $outer_wrappers
            ->reduce(function($c, $x) {
                $attrs = isset($x["attrs"]) ? " " . $x["attrs"] : "";
                $el = $x["element"];
                return $c->wrap("<{$el}{$attrs}>", "</{$el}>");
            }, $partials);
    }

    return $partials;
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

    $out = Str::of( '' );

    $section
        ->prop($section->prop('complex_field')->get())
        ->forEach( function( Kvm $p, Num $n ) use ( &$out, $section ) {
            $out = $out->append(handleSectionPartial(
                $p->set( 'index', $n )
                    ->set( 'id', $section->prop('id')->append("_p$n"))
            ));
        });

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

function getPartialPath(Str $type) {

    $callable_path  = $type->contains("/")
        ->fold(
            fn() => $type->append("/index.php"), 
            fn() => $type->append(".php")
        );

    if($callable_path == "header/index.php") {
        $callable_path = Str::of("header.php");
    }

    if($callable_path->contains("docs")->isTrue()) {
        $callable = dirname(__DIR__) . "/partials/{$callable_path}";
    
        if(file_exists($callable)) {
            return Str::of($callable);
        }

        return Str::of($callable);
    }

    $callable = dirname(__DIR__) . "/partials/components/{$callable_path}";
    
    if(file_exists($callable)) {
        return Str::of($callable);
    }

    $callable = dirname(__DIR__) . "/partials/patterns/{$callable_path}";
    
    if(file_exists($callable)) {
        return Str::of($callable);
    }

    $callable = dirname(__DIR__) . "/partials/elements/{$callable_path}";
    
    if(file_exists($callable)) {
        return Str::of($callable);
    }

    $callable = dirname(__DIR__) . "/partials/{$callable_path}";
    
    if(file_exists($callable)) {
        return Str::of($callable);
    }

    return Nil::unit();
}

function tryGetPartialPath($type) {

    $path = getPartialPath(Str::of($type));
    return $path instanceof Str 
        ? Ok::of($path)
        : Err::of("Partial not found")
        ;
}

// the "_type" i.e. partial name
function getPartialCallable(Str $type): Maybe {

    $callable_path  = $type->contains("/")
        ->fold(
            fn() => $type->append("/index.php"), 
            fn() => $type->append(".php")
        );

    if($callable_path == "header/index.php") {
        $callable_path = Str::of("header.php");
    }

    if($callable_path->contains("docs")->isTrue()) {
        $callable = dirname(__DIR__) . "/partials/{$callable_path}";
    
        if(file_exists($callable)) {
            return Maybe::just((include $callable));
        }

        return Maybe::nothing();
    }

    $callable = dirname(__DIR__) . "/partials/components/{$callable_path}";
    
    if(file_exists($callable)) {
        return Maybe::just((include $callable));
    }

    $callable = dirname(__DIR__) . "/partials/patterns/{$callable_path}";
    
    if(file_exists($callable)) {
        return Maybe::just((include $callable));
    }

    $callable = dirname(__DIR__) . "/partials/elements/{$callable_path}";
    
    if(file_exists($callable)) {
        return Maybe::just((include $callable));
    }

    $callable = dirname(__DIR__) . "/partials/{$callable_path}";
    
    if(file_exists($callable)) {
        return Maybe::just((include $callable));
    }

    return Maybe::nothing();

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


function handleSectionPartial(Kvm $partial): Str {    

    if($partial->prop('_type') === 'file') {
        $partial = $partial->set('_type', Str::of('file'));
    }

    $callable = $partial
        ->prop('_type')
        //->map("ucfirst")
        ->prepend("handle_");

    // this was original demo method and the ultimate override
    if(function_exists($callable->get())) {
        $content = Str::of($callable->get()($partial));
        
        return $partial->mprop("bp_id")
            ->map(function($bp_id) {
                $x = bp_get_page_by_bp_id($bp_id);
                $x = get_edit_post_link($x) . "&bp_edit=$bp_id";
                return $x; 
            })
            ->map(fn($x) => Str::of("<div data-bp-edit-url=\"$x\">$content</div>"))
            ->getOrElse($content);
    }
    
    $controller = $partial->prop('_type')->wrap("handle_", "_data")->get();
    if(function_exists($controller)) {
        $partial = $controller($partial); // partial is already a Kvm
    }
    
    // this does become a little 'esoteric' when read in reverse
    return getPartialCallable($partial->prop('_type'))
        ->map(fn($callable) => Str::of($callable($partial->get())))
        ->getOrElse(Str::of("Unknown partial type: {$partial->prop('_type')}"));
}

function concatPageSections( Lst $sections, $data = [] ): Str {
    $out = Str::of( '' );

    extract($data);
    $type = $type ?? 'partials';

    $sections->forEach( function( Kvm $s, Num $n ) use ( &$out, $type, $data ) {
        $out = $out->append( handlePageSection(
            $s->set( 'index', $n )
                ->set( 'id', Str::of("s")->append($n) ) 
                ->set( 'complex_field', $type )
                ->set( 'data', $data )
        ) );
    } );

    return $out;
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

function getDefaultPageContent($t): Str { 
    $content = get_the_content();
    $content = apply_filters('the_content', $content);
    $content = tryPartial("wrap/container", [
        "content" => $content
    ])->getOrElse($content);
    return Str::of($content);
}
function pinTopHeader($config_items): bool {
    foreach($config_items as $config_item) {
        if($config_item["_type"] === "header") {
            foreach($config_item["header_attrs"] as $header_item) {
                if($header_item["_type"] == "pin_top" && $header_item["pin_top"] == true) {
                    return true;
                }
            }
        }
    }
    return false;
}

function getHeaderClassExtras($config_items): string {
    foreach($config_items as $config_item) {
        if($config_item["_type"] === "header") {
            foreach($config_item["header_attrs"] as $header_item) {
                if($header_item["_type"] == "class" && !empty($header_item["class"]) ) {
                    return $header_item["class"];
                }
            }
        }
    }
    return "";
}

function renderPageContent($page_id) {

    // check head options

    // check header option
    $config_items = carbon_get_post_meta($page_id, 'page_config_items');

    // check footer options

    $slug = get_page_template_slug($page_id);

    if(empty($slug)) {
        $slug = "default-page";
    }

    $config_page = get_config_page($slug);

    if(is_null($config_page)) {
        return "";
    }

    // get the configuration page and then concat sections
    return Lst::of(carbon_get_post_meta(get_config_page($slug)->ID, "template_sections"))
        ->map(function($section) use ($config_items, $page_id, $slug) {

            switch($section["_type"]) {
                case "partial_path":
                    $data = [];

                    // returns a Maybe
                    if($section["partial_path"] == "header") {
                        if(pinTopHeader($config_items)) {
                            $data["section_class_extras"] = "pin-top";
                        }

                        $header_class_extras = getHeaderClassExtras($config_items);
                        if(!empty($header_class_extras)) {
                            $data["header_class_extras"] = $header_class_extras;
                        }
                    }

                    // looks like we are assuming 
                    $c = getPartialCallable(Str::of($section["partial_path"]))
                        ->get();

                    $out = Str::of($c(array_merge($data, [
                        "data-bp-edit-url" => tryGetPartialPath($section["partial_path"])
                            ->map(fn(Str $path) => $path->prepend('vscode://file'))
                            ->getOrElse(Str::of(''))
                    ])));

                    // if($section["bp_edit"]) {
                    //     $out = tryGetPartialPath($section["partial_path"])
                    //         ->map(fn(Str $path) => $path->prepend('vscode://file'))
                    //         ->map(fn(Str $path) => $out->wrap(
                    //                 "<div class=\"t\" data-bp-edit-url='$path'>",
                    //                 "</div>"
                    //             )
                    //         )
                    //         ->getOrElse('');
                    // }

                    return $out;
                    break;
                case "page_content":

                    $sections = carbon_get_post_meta( $page_id, $section["field_id"] );

                    $sections = Lst::of($sections);
                    return concatPageSections($sections, [
                        "page_template" => $slug,
                    ]);
               
                    break;
                case "partial_page":
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