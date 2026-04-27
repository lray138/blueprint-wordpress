<?php

use function lray138\g2\dump;
use lray138\G2\{Kvm, Str, Lst, Num};

$page_title = get_the_title();

$content = renderPageContent(get_the_ID(), [
    "content" => "404",
]);

echo "?";

include(get_template_directory() . '/index.php');