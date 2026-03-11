<?php
# start use
use function lray138\G2\dump;
# end use

return function($data = []) { 
	# start data processing
    extract($data);

    $tab_items = $tab_content;
    # end data processing
	return "<!-- Tabs Navigation --><ul class=\"nav nav-tabs mb-0\" id=\"accordionTabs\" role=\"tablist\">{$nav_items}</ul><!-- Tab Content --><div class=\"tab-content border-start border-end border-bottom rounded-bottom rounded-top-end p-3\" id=\"accordionTabContent\">{$tab_items}</div>";
};

# src: webpack/src/blueprint/partials/components/tabs/index.ejs