<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"col-12 col-lg-3 col-xl-2 px-lg-0 border-end-lg border-secondary\"><div class=\"collapse d-lg-block\" id=\"docsNavCollapse\"><div class=\"py-7 py-lg-9 px-3\"><style>
                me a.section-heading {
                    text-decoration: none;
                    color: inherit;
                }

                me a.section-heading:hover {
                    text-decoration: underline;
                }
            </style>{$sections}</div></div></div>";
};

# src: 