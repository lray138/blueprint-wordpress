<?php
/**
 * Attachment page permalinks by type + date:
 *   image/*  → photos/YYYY/MM/slug/
 *   video/*  → videos/YYYY/MM/slug/
 *   other    → media/YYYY/MM/slug/
 *
 * Loaded from functions.php. After changes: Settings → Permalinks → Save (or theme switch).
 *
 * Override the folder for an attachment:
 *   add_filter('blueprint_attachment_permalink_base', function ($base, WP_Post $post) {
 *       if ($post->post_mime_type === 'application/pdf') {
 *           return 'documents';
 *       }
 *       return $base; // photos | videos | media
 *   }, 10, 2);
 * (If you add a custom base, extend the rewrite regex below to include it.)
 *
 * Change the date segment:
 *   add_filter('blueprint_attachment_permalink_timestamp', ...)
 *
 * Default date: EXIF capture time when available (images), else attachment post_date.
 */

/**
 * EXIF / image_meta capture timestamp for permalink YYYY/MM, or 0 if unavailable.
 */
function blueprint_attachment_image_created_timestamp(WP_Post $post): int
{
    if ($post->post_type !== 'attachment') {
        return 0;
    }
    if (strpos((string) $post->post_mime_type, 'image/') !== 0) {
        return 0;
    }

    $id = (int) $post->ID;
    $meta = wp_get_attachment_metadata($id);
    if (is_array($meta) && ! empty($meta['image_meta']['created_timestamp'])) {
        $ts = (int) $meta['image_meta']['created_timestamp'];
        if ($ts > 0) {
            return $ts;
        }
    }

    $file = get_attached_file($id);
    if (! is_string($file) || $file === '' || ! is_readable($file)) {
        return 0;
    }

    if (! function_exists('wp_read_image_metadata')) {
        if (defined('ABSPATH') && is_readable(ABSPATH . 'wp-includes/media.php')) {
            require_once ABSPATH . 'wp-includes/media.php';
        }
        if (! function_exists('wp_read_image_metadata')) {
            return 0;
        }
    }

    $read = wp_read_image_metadata($file);
    if (! is_array($read) || empty($read['image_meta']['created_timestamp'])) {
        return 0;
    }

    $ts = (int) $read['image_meta']['created_timestamp'];

    return $ts > 0 ? $ts : 0;
}

add_filter('blueprint_attachment_permalink_timestamp', static function (int $ts, WP_Post $post): int {
    $exif_ts = blueprint_attachment_image_created_timestamp($post);
    if ($exif_ts <= 0) {
        return $ts;
    }
    // Ignore obviously wrong EXIF (future or ancient garbage).
    if ($exif_ts > time() + DAY_IN_SECONDS) {
        return $ts;
    }
    if ($exif_ts < strtotime('1970-01-02')) {
        return $ts;
    }

    return $exif_ts;
}, 10, 2);

/**
 * Top-level segment for this attachment: photos, videos, or media.
 */
function blueprint_attachment_permalink_base(WP_Post $post): string
{
    if ($post->post_type !== 'attachment') {
        return 'media';
    }

    $mime = isset($post->post_mime_type) ? (string) $post->post_mime_type : '';

    if (strpos($mime, 'image/') === 0) {
        $base = 'photos';
    } elseif (strpos($mime, 'video/') === 0) {
        $base = 'videos';
    } else {
        $base = 'media';
    }

    return (string) apply_filters('blueprint_attachment_permalink_base', $base, $post);
}

/**
 * Relative path (no leading/trailing slash) for the canonical attachment page URL, or null if not applicable.
 */
function blueprint_attachment_media_canonical_path(WP_Post $post): ?string
{
    if ($post->post_type !== 'attachment') {
        return null;
    }

    $slug = $post->post_name ?: (string) $post->ID;
    if ($slug === '') {
        return null;
    }

    $ts = strtotime($post->post_date);
    if ($ts === false) {
        $ts = time();
    }

    /** @var int $ts */
    $ts = (int) apply_filters('blueprint_attachment_permalink_timestamp', $ts, $post);

    $y = function_exists('wp_date') ? wp_date('Y', $ts) : gmdate('Y', $ts);
    $m = function_exists('wp_date') ? wp_date('m', $ts) : gmdate('m', $ts);

    $base = blueprint_attachment_permalink_base($post);

    return sprintf('%s/%s/%s/%s', $base, $y, $m, $slug);
}

/**
 * Request path after the site’s home path (e.g. photos/2025/04/foo).
 */
function blueprint_request_path_relative_to_home(): string
{
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = wp_parse_url($uri, PHP_URL_PATH);
    if (! is_string($path)) {
        return '';
    }

    $path = trim($path, '/');
    $home = wp_parse_url(home_url(), PHP_URL_PATH);
    $home = is_string($home) ? trim($home, '/') : '';

    if ($home !== '' && strpos($path, $home) === 0) {
        $path = trim(substr($path, strlen($home)), '/');
    }

    return $path;
}

add_filter('attachment_link', static function ($link, $post_id) {
    $post = get_post($post_id);
    if (! $post instanceof WP_Post) {
        return $link;
    }

    $rel = blueprint_attachment_media_canonical_path($post);
    if ($rel === null) {
        return $link;
    }

    return home_url(user_trailingslashit($rel));
}, 10, 2);

/**
 * Legacy short URLs and wrong bucket (e.g. old media/… for an image) → canonical path.
 */
add_action('template_redirect', static function () {
    if (! is_attachment()) {
        return;
    }

    if (is_admin() || wp_doing_ajax() || is_preview() || is_embed() || is_feed()) {
        return;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    $post = get_queried_object();
    if (! $post instanceof WP_Post) {
        return;
    }

    $canonical = blueprint_attachment_media_canonical_path($post);
    if ($canonical === null) {
        return;
    }

    $requested = blueprint_request_path_relative_to_home();

    $canonical_cmp = strtolower($canonical);
    $requested_cmp = strtolower($requested);

    if ($requested_cmp === $canonical_cmp) {
        return;
    }

    $target = home_url(user_trailingslashit($canonical));
    $query  = wp_parse_url(isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '', PHP_URL_QUERY);
    if (is_string($query) && $query !== '') {
        $target = $target . (strpos($target, '?') !== false ? '&' : '?') . $query;
    }

    wp_safe_redirect($target, 301);
    exit;
}, 1);

/**
 * One rule: photos | videos | media (+ legacy uploads) + year + month + slug → attachment.
 */
add_filter('rewrite_rules_array', static function (array $rules): array {
    $attachment_dated = [
        '^(uploads|photos|videos|media)/([0-9]{4})/([0-9]{1,2})/([^/]+)/?$' => 'index.php?attachment=$matches[4]&post_type=attachment',
    ];

    return $attachment_dated + $rules;
}, 1);

add_action('init', static function () {
    if (get_option('blueprint_attachment_rewrite_rules_ver') === '4') {
        return;
    }
    flush_rewrite_rules(false);
    update_option('blueprint_attachment_rewrite_rules_ver', '4');
}, 1000);

add_action('after_switch_theme', static function () {
    delete_option('blueprint_attachment_rewrite_rules_ver');
    delete_option('blueprint_attachment_date_perm_media_prefix');
    delete_option('blueprint_attachment_date_perm_inited');
    flush_rewrite_rules(false);
});
