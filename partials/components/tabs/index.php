<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<!-- Tabs Navigation --><ul class=\"nav nav-tabs mb-0\" id=\"accordionTabs\" role=\"tablist\">{$nav_items}</ul><!-- Tab Content --><div class=\"tab-content border-start border-end border-bottom rounded-bottom rounded-top-end p-3\" id=\"accordionTabContent\">{$tab_items}</div>";
};

# src: 