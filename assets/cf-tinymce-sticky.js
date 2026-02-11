(function () {
    const ready = () =>
      document.querySelector('.cf-field .mce-toolbar-grp');
  
    const inject = () => {
      if (document.getElementById('cf-tinymce-sticky-css')) return;
  
      const css = `
        .cf-field .mce-toolbar-grp,
        .cf-field .mce-top-part {
          position: sticky;
          top: 32px;
          z-index: 9999;
          background: #fff;
        }
      `;
  
      const style = document.createElement('style');
      style.id = 'cf-tinymce-sticky-css';
      style.textContent = css;
      document.head.appendChild(style);
    };
  
    const observer = new MutationObserver(() => {
      if (ready()) {
        inject();
        observer.disconnect();
      }
    });
  
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  })();
  

  (function () {
  const ADMIN_BAR = document.getElementById('wpadminbar');
  const getTopOffset = () => (ADMIN_BAR ? ADMIN_BAR.offsetHeight : 0);

  let placeholder = null;
  let isDocked = false;

  const ensureStyles = () => {
    if (document.getElementById('sticky-major-publishing-css')) return;

    const css = `
      .sticky-major-publishing-actions {
        position: fixed !important;
        top: var(--sticky-top, 32px);
        z-index: 100000;
        background: #fff;
        padding: 10px 12px;
        box-shadow: 0 6px 10px rgba(0,0,0,.08);
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 6px;
      }
    `;

    const style = document.createElement('style');
    style.id = 'sticky-major-publishing-css';
    style.textContent = css;
    document.head.appendChild(style);
  };

  const dock = (el) => {
    if (isDocked) return;
    ensureStyles();

    // Create placeholder so layout doesn't jump when we "pop" it out
    placeholder = document.createElement('div');
    placeholder.id = 'sticky-major-publishing-placeholder';
    placeholder.style.height = el.offsetHeight + 'px';
    el.parentNode.insertBefore(placeholder, el);

    // Dock styling
    const top = getTopOffset();
    el.style.setProperty('--sticky-top', `${top}px`);
    el.classList.add('sticky-major-publishing-actions');

    // Match width + left with its original column
    const col = document.getElementById('postbox-container-1') || el.parentElement;
    const rect = col.getBoundingClientRect();
    el.style.width = rect.width + 'px';
    el.style.left = rect.left + 'px';

    isDocked = true;
  };

  const undock = (el) => {
    if (!isDocked) return;

    el.classList.remove('sticky-major-publishing-actions');
    el.style.width = '';
    el.style.left = '';
    el.style.removeProperty('--sticky-top');

    if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
    placeholder = null;

    isDocked = false;
  };

  const tick = () => {
    const actions = document.querySelector('#submitdiv #major-publishing-actions');
    const submitdiv = document.getElementById('submitdiv');
    if (!actions || !submitdiv) return;

    // If the entire publish box is above the viewport, dock the actions.
    const boxRect = submitdiv.getBoundingClientRect();
    const topLine = getTopOffset() + 8;

    const boxOutOfViewAbove = boxRect.bottom <= topLine;
    if (boxOutOfViewAbove) dock(actions);
    else undock(actions);

    // If docked, keep it aligned on resize/scroll (side metabox column can shift)
    if (isDocked) {
      const col = document.getElementById('postbox-container-1') || actions.parentElement;
      const rect = col.getBoundingClientRect();
      actions.style.width = rect.width + 'px';
      actions.style.left = rect.left + 'px';
      actions.style.setProperty('--sticky-top', `${getTopOffset()}px`);
    }
  };

  // Run after DOM changes (metaboxes, screen options, etc.)
  const boot = () => {
    const observer = new MutationObserver(() => tick());
    observer.observe(document.body, { childList: true, subtree: true });

    window.addEventListener('scroll', tick, { passive: true });
    window.addEventListener('resize', tick);

    tick();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
