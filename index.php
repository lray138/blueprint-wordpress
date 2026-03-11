<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/theme.css">
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
</head>
<?php
    $can_edit = is_user_logged_in() && current_user_can('edit_post', get_the_ID());

    $admin_attr = $can_edit
        ? ' data-admin-url="' . esc_url(admin_url()) . '" data-post-id="' . esc_attr(get_the_ID()) . '"'
        : '';
?>
<body <?php body_class('d-flex flex-column min-vh-100' . ($can_edit ? ' bp-edit' : '')); ?><?= $admin_attr; ?>>
    <?php echo $content; ?>
    <?php wp_footer(); ?>
    <script src="<?php echo esc_url(get_template_directory_uri() . '/js/theme.js'); ?>"></script>
</body>
</html> 