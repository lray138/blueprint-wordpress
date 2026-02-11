// sticky publish button
(function () {
    const ADMIN_BAR = document.getElementById('wpadminbar');
    const getTopOffset = () => (ADMIN_BAR ? ADMIN_BAR.offsetHeight : 0);
  
    let placeholder = null;
    let isDocked = false;
  
    // --- NEW: throttle + mutation guard
    let rafScheduled = false;
    let selfMutating = 0; // counter > boolean (safer if nested)
  
    const scheduleTick = () => {
      if (rafScheduled) return;
      rafScheduled = true;
      requestAnimationFrame(() => {
        rafScheduled = false;
        tick();
      });
    };
  
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
  
      selfMutating++;
  
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
  
      selfMutating--;
    };
  
    const undock = (el) => {
      if (!isDocked) return;
  
      selfMutating++;
  
      el.classList.remove('sticky-major-publishing-actions');
      el.style.width = '';
      el.style.left = '';
      el.style.removeProperty('--sticky-top');
  
      if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
      placeholder = null;
  
      isDocked = false;
  
      selfMutating--;
    };
  
    const tick = () => {
      const actions = document.querySelector('#submitdiv #major-publishing-actions');
      const submitdiv = document.getElementById('submitdiv');
      if (!actions || !submitdiv) return;
  
      const boxRect = submitdiv.getBoundingClientRect();
      const topLine = getTopOffset() + 8;
  
      const boxOutOfViewAbove = boxRect.bottom <= topLine;
      if (boxOutOfViewAbove) dock(actions);
      else undock(actions);
  
      if (isDocked) {
        const col = document.getElementById('postbox-container-1') || actions.parentElement;
        const rect = col.getBoundingClientRect();
        actions.style.width = rect.width + 'px';
        actions.style.left = rect.left + 'px';
        actions.style.setProperty('--sticky-top', `${getTopOffset()}px`);
      }
    };
  
    const boot = () => {
      // --- NEW: observe less + don't tick on our own mutations
      const root = document.getElementById('poststuff') || document.body;
  
      const observer = new MutationObserver(() => {
        if (selfMutating > 0) return;
        scheduleTick();
      });
  
      observer.observe(root, { childList: true, subtree: true });
  
      window.addEventListener('scroll', scheduleTick, { passive: true });
      window.addEventListener('resize', scheduleTick);
  
      scheduleTick();
    };
  
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', boot);
    } else {
      boot();
    }
  })();
  

  // scroll back 
  (function () {
    const KEY = 'wp-admin-scroll-pos';
  
    // 1. Capture scroll before save
    const captureScroll = () => {
      sessionStorage.setItem(KEY, String(window.scrollY || 0));
    };
  
    // Hook all common save paths
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!form || form.id !== 'post') return;
      captureScroll();
    });
  
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('#publish, #save-post, #save-post-top');
      if (!btn) return;
      captureScroll();
    });
  
    // 2. Restore scroll after reload
    const restoreScroll = () => {
      const y = sessionStorage.getItem(KEY);
      if (!y) return;
  
      sessionStorage.removeItem(KEY);
  
      // Let WP finish layout first (metaboxes, admin bar, etc.)
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          window.scrollTo(0, parseInt(y, 10));
        });
      });
    };
  
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', restoreScroll);
    } else {
      restoreScroll();
    }
  })();
  

  ///


  // broadcast the save 

  (function () {

    const KEY_CH = 'wp-live';
    const KEY_PENDING = 'wp-live-pending-save';
    const ch = ('BroadcastChannel' in window) ? new BroadcastChannel(KEY_CH) : null;
  
    const send = (payload) => {
      if (ch) ch.postMessage(payload);
      else localStorage.setItem(KEY_CH, JSON.stringify({ ...payload, t: Date.now() }));
    };
  
    const getPostId = () => Number(document.getElementById('post_ID')?.value || 0);
  
    // Mark intent to save BEFORE reload
    const markPending = () => {
      const postId = getPostId();
      if (!postId) return;
      sessionStorage.setItem(KEY_PENDING, JSON.stringify({ postId, t: Date.now() }));
    };
  
    document.addEventListener('submit', (e) => {
      if (e.target?.id !== 'post') return;
      markPending();
    });
  
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('#publish, #save-post, #save-post-top');
      if (!btn) return;
      markPending();
    });
  
    // After reload: only broadcast if we had a pending save AND we see a success notice
    const pendingRaw = sessionStorage.getItem(KEY_PENDING);
    if (!pendingRaw) return;
  
    let pending = null;
    try { pending = JSON.parse(pendingRaw); } catch {}
  
    const fresh = pending && (Date.now() - (pending.t || 0) < 2 * 60 * 1000);
    if (!fresh) {
      sessionStorage.removeItem(KEY_PENDING);
      return;
    }
  
    const savedNotice =
      document.querySelector('#message.updated') ||
      document.querySelector('.notice.notice-success') ||
      document.querySelector('.updated.notice');
  
    if (!savedNotice) return;
  
    sessionStorage.removeItem(KEY_PENDING);
    send({ type: 'post_saved', postId: Number(pending.postId) });
  })();
  