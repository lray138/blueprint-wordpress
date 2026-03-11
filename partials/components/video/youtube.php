<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube.com/embed/{$video_id}\" title=\"YouTube video\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen=\"\"></iframe></div>";
};

# src: webpack/src/blueprint/partials/components/video/youtube.ejs