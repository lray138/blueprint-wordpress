<?php
/**
 * Template Name: Universal Page
 */

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

$page_title = get_the_title();

$content = renderPageContent(get_the_ID(), [
    "site_name" => "",
    "site_url" => get_stylesheet_directory_uri()
]);

include(get_template_directory() . '/index.php');