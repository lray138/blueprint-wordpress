<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "{$section_heading}<div class=\"{$collapse_class}\" id=\"{$collapse_id}\">{$list}</div><style>
    me .section-heading {
        text-decoration: none;
        color: inherit;
        display: inline-block;
        padding-bottom: .25rem;
    }

    me .section-heading:hover {
        text-decoration: underline;
    }

    me .section-toggle {
        width: 1.25rem;
        height: 1.25rem;
        line-height: 1.25rem;
        text-decoration: none;
        color: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        align-self: center;
        font-size: 1rem;
        line-height: 1;
    }

    me .section-toggle::before {
        content: \"+\";
        font-weight: 700;
        font-size: 1rem;
        line-height: 1;
        display: block;
    }

    me .section-toggle[aria-expanded=\"true\"]::before {
        content: \"–\";
    }

    me ul li a {
        text-decoration: none;
        color: var(--wp--preset--color--base);
    }
</style>";
};

# src: 