<?php 

use function lray138\G2\dump;

$content = "test";

$img_src = wp_get_attachment_image_src(get_the_ID(), 'full')[0];

$alt_txt = get_post_meta(get_the_ID(), '_wp_attachment_image_alt', true);
$page_title = get_the_title();

$content = renderPageContent(get_the_ID(), [
    "site_name" => "",
    "site_url" => get_stylesheet_directory_uri(),
    "content" => tryPartial("/bp/wraps/section-one-col.php", [
        "content" => "<img src='{$img_src}' alt='{$alt_txt}' class='img-fluid'>"
    ])->getOrElse(""),
]);

require_once get_template_directory() . '/index.php';

die;

// <!-- <?php get_header(); ?>

// <main class="container py-5">
//     <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
//         <article <?php post_class(); ?>>
//             <h1><?php the_title(); ?></h1>

//             <div class="mb-4">
//                 <?php echo wp_get_attachment_image(get_the_ID(), 'large'); ?>
//             </div>

//             <div class="mb-4">
//                 <?php the_content(); ?>
//             </div>
//         </article>
//     <?php endwhile; endif; ?>
// </main>

// <?php get_footer(); ?> -->
