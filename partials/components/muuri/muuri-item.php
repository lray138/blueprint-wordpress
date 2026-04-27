<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"muuri-item\" data-item-id=\"{$itemId}\"><div class=\"muuri-item-content\"><figure class=\"position-relative mb-0 muuri-item-figure\"><a href=\"{$fullSrc}\" class=\"muuri-item-lightbox d-block text-decoration-none\" data-bp=\"{$fullSrc}\" data-bigpicture='{$bpGalleryOpts}' {$page_data_attrs}><img class=\"muuri-item-img w-100 rounded\" src=\"{$thumbSrc}\" alt=\"{$altText}\" loading=\"lazy\" decoding=\"async\"/></a>{$figcaptionHtml}</figure></div></div><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//components/muuri/muuri-item.php
-->";
};

# src: webpack/src/blueprint/partials/components/muuri/muuri-item.ejs