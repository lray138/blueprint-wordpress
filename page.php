<?php 

// default page
use lray138\G2\{Str};

$page_title = get_the_title();

// var_dump(get_stylesheet_directory_uri());
// var_dump(get_template_directory_uri());
// var_dump(get_site_url());



$content = renderPageContent(get_the_ID(), [
    "site_url" => get_site_url() . "/"
]);

include(get_template_directory() . '/index.php');