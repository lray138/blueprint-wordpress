<?php 

// default page
use lray138\G2\{Str};

$page_title = get_the_title();

$content = renderPageContent(get_the_ID());

include(get_template_directory() . '/index.php');