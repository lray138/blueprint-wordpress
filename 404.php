<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/theme.css">
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body <?php body_class('d-flex flex-column min-vh-100'); ?> >
    404
    <?php wp_footer(); ?>
    <script src="<?php echo get_template_directory_uri(); ?>/js/main.js"></script>
</body>
</html>