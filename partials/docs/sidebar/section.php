<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$anchor}
{$list}<style>
    me a.section-heading {
        text-decoration: none;
    }

    me ul li a {
        text-decoration: none;
        color: var(--wp--preset--color--base);
    }
</style>";
};

# src: 