<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php
    $bp_share_image_url = '';
    $bp_share_image_w = 0;
    $bp_share_image_h = 0;

    if (is_front_page()) {
        // Home: default social image = custom logo, then front-page featured image, then site icon.
        $bp_logo_id = (int) get_theme_mod('custom_logo');
        if ($bp_logo_id) {
            $bp_src = wp_get_attachment_image_src($bp_logo_id, 'full');
            if (is_array($bp_src) && ! empty($bp_src[0])) {
                $bp_share_image_url = $bp_src[0];
                $bp_share_image_w = (int) $bp_src[1];
                $bp_share_image_h = (int) $bp_src[2];
            }
        }
        if ($bp_share_image_url === '' && is_singular() && has_post_thumbnail()) {
            $bp_thumb_id = (int) get_post_thumbnail_id();
            if ($bp_thumb_id) {
                $bp_src = wp_get_attachment_image_src($bp_thumb_id, 'full');
                if (is_array($bp_src) && ! empty($bp_src[0])) {
                    $bp_share_image_url = $bp_src[0];
                    $bp_share_image_w = (int) $bp_src[1];
                    $bp_share_image_h = (int) $bp_src[2];
                }
            }
        }
        if ($bp_share_image_url === '' && function_exists('get_site_icon_url')) {
            $bp_share_image_url = (string) get_site_icon_url(512);
        }
    } elseif (is_singular() && has_post_thumbnail()) {
        $bp_thumb_id = (int) get_post_thumbnail_id();
        if ($bp_thumb_id) {
            $bp_src = wp_get_attachment_image_src($bp_thumb_id, 'full');
            if (is_array($bp_src) && ! empty($bp_src[0])) {
                $bp_share_image_url = $bp_src[0];
                $bp_share_image_w = (int) $bp_src[1];
                $bp_share_image_h = (int) $bp_src[2];
            }
        }
    } elseif (($bp_logo_id = (int) get_theme_mod('custom_logo'))) {
        $bp_src = wp_get_attachment_image_src($bp_logo_id, 'full');
        if (is_array($bp_src) && ! empty($bp_src[0])) {
            $bp_share_image_url = $bp_src[0];
            $bp_share_image_w = (int) $bp_src[1];
            $bp_share_image_h = (int) $bp_src[2];
        }
    } elseif (function_exists('get_site_icon_url')) {
        $bp_share_image_url = (string) get_site_icon_url(512);
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
    ?>
    <?php wp_head(); ?>
    <?php if ($bp_og_canonical_url !== '') : ?>
    <meta property="og:url" content="<?php echo esc_url($bp_og_canonical_url); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo esc_attr(wp_strip_all_tags((string) $page_title)); ?>">
    <?php if (is_front_page()) : ?>
    <meta property="og:type" content="website">
    <?php endif; ?>
    <?php if ($bp_home_og_description !== '') : ?>
    <meta property="og:description" content="<?php echo esc_attr($bp_home_og_description); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($bp_home_og_description); ?>">
    <?php endif; ?>
    <?php if ($bp_share_image_url !== '') : ?>
    <meta property="og:image" content="<?php echo esc_url($bp_share_image_url); ?>">
    <meta name="image" content="<?php echo esc_url($bp_share_image_url); ?>">
        <?php if (strpos($bp_share_image_url, 'https://') === 0) : ?>
    <meta property="og:image:secure_url" content="<?php echo esc_url($bp_share_image_url); ?>">
        <?php endif; ?>
    <meta property="og:image:alt" content="<?php echo esc_attr(wp_strip_all_tags((string) $page_title)); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($bp_share_image_url); ?>">
    <meta name="twitter:card" content="summary_large_image">
        <?php if ($bp_share_image_w > 0 && $bp_share_image_h > 0) : ?>
    <meta property="og:image:width" content="<?php echo esc_attr((string) $bp_share_image_w); ?>">
    <meta property="og:image:height" content="<?php echo esc_attr((string) $bp_share_image_h); ?>">
        <?php endif; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/theme.css">
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <?php if (is_page('contact')) : ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    
</head>
<?php
    $can_edit = is_user_logged_in() && current_user_can('edit_post', get_the_ID());

    $admin_attr = $can_edit
        ? ' data-admin-url="' . esc_url(admin_url()) . '" data-post-id="' . esc_attr(get_the_ID()) . '"'
        : '';

    $content = isset($content) ? $content : "";
?>
<body <?php body_class('d-flex flex-column min-vh-100' . ($can_edit ? ' bp-edit' : '')); ?><?= $admin_attr; ?> style="background-color:rgb(240, 240, 240);">
    <?php echo $content; ?>
    <?php wp_footer(); ?>
    <script src="<?php echo esc_url(get_template_directory_uri() . '/js/theme.js'); ?>"></script>
</body>
</html>