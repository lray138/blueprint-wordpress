<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<button type=\"submit\" class=\"btn btn-primary btn-lg d-inline-flex align-items-center gap-2\"><span class=\"label\">{$label}</span><span class=\"spinner-border spinner-border-sm htmx-indicator\"></span></button><style>
.htmx-indicator {
  display: none;
}

.htmx-indicator.htmx-request  {
  display: inline-block;
}

.htmx-request .label {
  opacity: 0.6;
}
</style>";
};

# src: webpack/src/blueprint/partials/components/button/htmx-submit.ejs