<?php
# start use
# end use

return function($data = []) { 
	# start data processing
    extract($data);
    # end data processing
	return "<div class=\"col-12 col-lg-3 col-xl-2 px-lg-0 border-end-lg border-secondary\"><div class=\"collapse d-lg-block\" id=\"docsNavCollapse\"><div class=\"py-7 py-lg-9 px-3\"><style>
                me .section-heading {
                    text-decoration: none;
                    color: inherit;
                }

                me .section-heading:hover {
                    text-decoration: underline;
                }

                me ul li a { 
                    text-decoration: none;
                }
            </style>{$sections}<script>
                (function () {
                    const KEY_PREFIX = 'blueprint:docs-nav:';

                    function getKey(collapseId) {
                        return KEY_PREFIX + collapseId;
                    }

                    function setExpanded(btn, expanded) {
                        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    }

                    function setCollapseVisible(el, visible) {
                        el.classList.toggle('show', !!visible);
                    }

                    window.addEventListener('DOMContentLoaded', function () {
                        const toggles = Array.from(document.querySelectorAll('.section-toggle[aria-controls]'));
                        if (!toggles.length) return;

                        // 1) Restore saved state (if present). If no saved state, keep server-rendered defaults.
                        toggles.forEach((btn) => {
                            const collapseId = btn.getAttribute('aria-controls');
                            if (!collapseId) return;
                            const el = document.getElementById(collapseId);
                            if (!el) return;

                            const saved = localStorage.getItem(getKey(collapseId));
                            if (saved == null) return;

                            const visible = saved === 'open';
                            setExpanded(btn, visible);
                            setCollapseVisible(el, visible);
                        });

                        // 2) Track future opens/closes.
                        toggles.forEach((btn) => {
                            const collapseId = btn.getAttribute('aria-controls');
                            if (!collapseId) return;
                            const el = document.getElementById(collapseId);
                            if (!el) return;

                            el.addEventListener('shown.bs.collapse', function () {
                                localStorage.setItem(getKey(collapseId), 'open');
                                setExpanded(btn, true);
                            });
                            el.addEventListener('hidden.bs.collapse', function () {
                                localStorage.setItem(getKey(collapseId), 'closed');
                                setExpanded(btn, false);
                            });
                        });
                    });
                })();
            </script></div></div></div>";
};

# src: webpack/src/blueprint/partials/docs/sidebar.ejs