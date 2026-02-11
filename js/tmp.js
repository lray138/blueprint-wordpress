document.addEventListener('DOMContentLoaded', () => {
    
    setTimeout(() => {
        console.log('DOMContentLoaded');
        const el = document.querySelector('.edit-prev-assoc-page');
        
        if (!el) {
            console.log('No edit-prev-assoc-page found');
            return;
        }

        
        const fieldEl = el.closest('.cf-field')?.previousElementSibling;
        if (!fieldEl) return;

        const input = fieldEl.querySelector('.cf-association__option input[type="hidden"]');
        if (!input || !input.value) return;

        const value = input.value.split(':').pop();

        const base = window.location.pathname.split('/wp-admin')[0];

        el.href = `${base}/wp-admin/post.php?post=${value}&action=edit`;

    }, 1000);
  });