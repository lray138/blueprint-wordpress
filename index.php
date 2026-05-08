<?php
    use function lray138\G2\wrap;
    use lray138\G2\{Kvm, Lst, Result, Either};

    $page_title = isset($page_title) ? $page_title : '';
    $bp_html_class = isset($bp_html_class) ? trim((string) $bp_html_class) : '';

    // Main-query ID: after renderPageContent() the global $post may not match this page.
    $bp_context_post_id = (int) get_queried_object_id();
    if ($bp_context_post_id === 0) {
        $bp_context_post_id = (int) get_the_ID();
    }

    $t = tryCarbonPostMeta("page_config_items", $bp_context_post_id)
        ->map(function(Lst $l) {
            return $l->filter(function(array $kvm) {
                return wrap($kvm)->prop("_type")->get() === "html";
            })
            ->head();
        })
        ->map(function($k) {
            if (is_null($k)) return "";
            $t = $k->prop($k->prop("_type")->append("_attrs"));
            return renderAttributes($t);
        })
        ->getOrElse("");

    /**
     * Main query id first, then loop/global post (after renderPageContent() may have moved $post).
     */
    
    function tryPostThumbnailId(int $post_id): Result
    {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    
        return Result::ok(
            $thumbnail_id !== 0
                ? Either::right($thumbnail_id)
                : Either::left('No thumbnail id found')
        );
    }
    
    function tryLargeImageUrl(int $thumbnail_id): Result
    {
        $url = wp_get_attachment_image_url($thumbnail_id, 'large');
    
        return $url !== false
            ? Result::ok($url)
            : Result::err('Could not resolve large image URL');
    }
    
    function getDefaultOgImageUrl(): string
    {
        $uploads = wp_get_upload_dir();
        return $uploads['baseurl'] . '/2026/04/everlasting_og.jpg';
    }
    
    /**
     * Single OG/Twitter image URL: photo attachment page → full file; else featured large or fallback asset.
     */
    function bp_resolve_share_image_url_force(): string
    {
        if (function_exists('bp_is_photo_attachment_viewport') && bp_is_photo_attachment_viewport()) {
            $att_id = (int) get_queried_object_id();
            if ($att_id) {
                $url = wp_get_attachment_image_url($att_id, 'full');
                if ($url !== false && $url !== '') {
                    return $url;
                }
            }
        }

        return tryQueriedObjectId()
            ->bind(fn (int $post_id) => tryPostThumbnailId($post_id))
            ->bind(function ($either) {
                return $either->fold(
                    fn ($_reason) => Result::ok(getDefaultOgImageUrl()),
                    fn (int $thumbnail_id) => tryLargeImageUrl($thumbnail_id)
                );
            })
            ->fold(
                fn ($_err) => getDefaultOgImageUrl(),
                fn (string $url) => $url
            );
    }

    $bp_share_image_url_force = bp_resolve_share_image_url_force();

?>
<!DOCTYPE html>
<html lang="en" <?= $t ?> data-bs-theme="light"<?php echo $bp_html_class !== '' ? ' class="' . esc_attr($bp_html_class) . '"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?></title>
    <?php
    $bp_is_photo_attachment = bp_is_photo_attachment_viewport();

    $bp_share_image_url = trim((string) $bp_share_image_url_force);
    $bp_share_image_w = 0;
    $bp_share_image_h = 0;
    if ($bp_is_photo_attachment) {
        $bp_att_id = (int) get_queried_object_id();
        if ($bp_att_id) {
            $bp_src = wp_get_attachment_image_src($bp_att_id, 'full');
            if (is_array($bp_src) && ! empty($bp_src[0])) {
                $bp_share_image_w = (int) $bp_src[1];
                $bp_share_image_h = (int) $bp_src[2];
            }
        }
    } elseif (is_singular() && has_post_thumbnail()) {
        $bp_thumb_id = (int) get_post_thumbnail_id();
        if ($bp_thumb_id) {
            $bp_src = wp_get_attachment_image_src($bp_thumb_id, 'full');
            if (is_array($bp_src) && ! empty($bp_src[0])) {
                $bp_share_image_w = (int) $bp_src[1];
                $bp_share_image_h = (int) $bp_src[2];
            }
        }
    }


    $bp_normalize_og_image_url = static function (string $url): string {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (! preg_match('#^https?://#i', $url)) {
            $url = home_url('/' . ltrim($url, '/'));
        }
        $url = set_url_scheme($url, is_ssl() ? 'https' : 'http');

        return esc_url($url);
    };

    if ($bp_share_image_url !== '') {
        $bp_share_image_url = $bp_normalize_og_image_url($bp_share_image_url);
    }

    $bp_home_og_description = is_front_page()
        ? 'Photography from Atlanta focused on authentic moments, creative energy, and stories worth remembering.'
        : '';

    $bp_og_canonical_url = '';
    if (is_singular()) {
        $bp_og_canonical_url = get_permalink() ?: '';
    } elseif (is_front_page()) {
        $bp_og_canonical_url = home_url('/');
    }
    if ($bp_og_canonical_url !== '') {
        $bp_og_canonical_url = $bp_normalize_og_image_url($bp_og_canonical_url);
    }

    $bp_og_published = '';
    $bp_og_modified = '';
    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && $post->post_status === 'publish') {
            $bp_ts_pub = get_post_time('U', true, $post);
            $bp_ts_mod = get_post_modified_time('U', true, $post);
            if ($bp_ts_pub) {
                $bp_og_published = gmdate('c', (int) $bp_ts_pub);
            }
            if ($bp_ts_mod) {
                $bp_og_modified = gmdate('c', (int) $bp_ts_mod);
            }
        }
    }
    ?>
    <?php /* Open Graph before wp_head(): many crawlers use the first og:image; plugins often inject another in wp_head(). */ ?>
    <?php if ($bp_og_canonical_url !== '') : ?>
    <meta property="og:url" content="<?php echo esc_url($bp_og_canonical_url); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo esc_attr(wp_strip_all_tags((string) $page_title)); ?>">
    <?php if (is_front_page()) : ?>
    <meta property="og:type" content="website">
    <?php elseif (is_singular('post')) : ?>
    <meta property="og:type" content="article">
    <?php elseif (is_singular()) : ?>
    <meta property="og:type" content="website">
    <?php endif; ?>
    <?php if ($bp_og_published !== '') : ?>
    <meta property="article:published_time" content="<?php echo esc_attr($bp_og_published); ?>">
    <?php endif; ?>
    <?php if ($bp_og_modified !== '') : ?>
    <meta property="article:modified_time" content="<?php echo esc_attr($bp_og_modified); ?>">
    <?php endif; ?>
    <?php if ($bp_home_og_description !== '') : ?>
    <meta property="og:description" content="<?php echo esc_attr($bp_home_og_description); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($bp_home_og_description); ?>">
    <?php endif; ?>
    <?php if ($bp_share_image_url !== '') : ?>
    <meta property="og:image" content="<?php echo esc_url($bp_share_image_url); ?>">
    <?php if ($bp_share_image_w > 0 && $bp_share_image_h > 0) : ?>
    <meta property="og:image:width" content="<?php echo esc_attr((string) $bp_share_image_w); ?>">
    <meta property="og:image:height" content="<?php echo esc_attr((string) $bp_share_image_h); ?>">
        <?php endif; ?>
    <meta property="og:image:alt" content="<?php echo esc_attr(wp_strip_all_tags((string) $page_title)); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($bp_share_image_url); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php endif; ?>
    <?php wp_head(); ?>
    <?php echo bp_photo_viewport_style_tag(); ?>
    <?php
    // Webpack output: npm run build:site-wp → wp-content/site/css|js
    $bp_site_css_path = WP_CONTENT_DIR . '/site/css/theme.css';
    $bp_site_js_path = WP_CONTENT_DIR . '/site/js/theme.js';
    $bp_site_css_url = content_url('site/css/theme.css');
    $bp_site_js_url = content_url('site/js/theme.js');
    $bp_site_css_ver = is_readable($bp_site_css_path) ? (string) filemtime($bp_site_css_path) : '1';
    $bp_site_js_ver = is_readable($bp_site_js_path) ? (string) filemtime($bp_site_js_path) : '1';
    ?>
    <link rel="stylesheet" href="<?php echo esc_url(add_query_arg('ver', $bp_site_css_ver, $bp_site_css_url)); ?>">
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <?php if (is_page('contact')) : ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    
</head>
<?php
    $can_edit = is_user_logged_in() && current_user_can('edit_post', $bp_context_post_id);

    $admin_attr = $can_edit
        ? ' data-admin-url="' . esc_url(admin_url()) . '" data-post-id="' . esc_attr((string) $bp_context_post_id) . '"'
        : '';

    $content = isset($content) ? $content : "";

    $bp_body_class = 'd-flex flex-column min-vh-100';
    if ($bp_is_photo_attachment) {
        $bp_body_class .= ' photo-page-body';
    }
    if ($can_edit) {
        $bp_body_class .= ' bp-edit';
    }
?>
    <body <?php body_class($bp_body_class); ?><?= $admin_attr; ?>>
        <?php echo $content; ?>
        <?php echo bp_photo_viewport_script_tag(); ?>
        <?php wp_footer(); ?>
        <script src="<?php echo esc_url(add_query_arg('ver', $bp_site_js_ver, $bp_site_js_url)); ?>"></script>
    </body>
</html>