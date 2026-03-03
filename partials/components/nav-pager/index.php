<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"nav-pager\">{$prev}
   {$next}</div><style>

    me .nav-pager {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        justify-content: space-between;
    }

    me a.nav-pager-item {
        text-decoration: none;
        border: 1px solid #cccccc;
        border-radius: 0.25em;
        padding: 0.5rem 1rem;
        color: black;
    }

    me .nav-pager-item .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    me .nav-pager-item .card-text {
        font-size: 1rem;
        color: var(--bs-body-color);
    }

    me a.nav-pager-item:hover {
        background-color: var(--bs-body-bg);
        border-color: var(--bs-border-color);
        box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
    }
    
</style>";
};

# src: 