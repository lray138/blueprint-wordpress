<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<h3 class=\"mb-2\">{$title}</h3><p class=\"small mb-2\"><a href=\"{$editUrl}\">edit</a></p><p class=\"text-muted mb-3\">{$intro}</p><div class=\"table-responsive mb-4\"><table class=\"{$tableClass}\"><thead><tr><th scope=\"col\">Property</th><th scope=\"col\">Type</th><th scope=\"col\">Description</th><th scope=\"col\">Required</th><th scope=\"col\">Default</th></tr></thead><tbody><tr><td><strong>{$prop.name}</strong></td><td><code>{$prop.type}</code></td><td>{$prop.descriptionHtml}</td><td>{$requiredCell(prop.required)}</td><td>-<code>{$def.value}</code>{$def.value}</td></tr></tbody></table></div><p class=\"small text-muted mb-0\">{$caption}</p><!-- dist:
/Users/lray/Sites/babygirlatl/wp-content/themes/blueprint/partials//docs/component-props-table.php
-->";
};

# src: webpack/src/blueprint/partials/docs/component-props-table.ejs