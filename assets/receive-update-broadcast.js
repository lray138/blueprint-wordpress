(function () {
    const KEY = 'wp-live';
  
    const onMsg = (msg) => {
      if (!msg || msg.type !== 'post_saved') return;
  
      const viewingId = Number(document.body?.dataset?.postId || 0);
      if (!viewingId) return;
  
      if (viewingId !== Number(msg.postId)) return;
  
      location.reload();
    };
  
    if ('BroadcastChannel' in window) {
      const ch = new BroadcastChannel(KEY);
      ch.addEventListener('message', (e) => onMsg(e.data));
    } else {
      window.addEventListener('storage', (e) => {
        if (e.key !== KEY || !e.newValue) return;
        onMsg(JSON.parse(e.newValue));
      });
    }
  })();
  