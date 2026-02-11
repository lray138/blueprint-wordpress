function addChildPageLink(parentId) {
    var parentId = {$parent_id_json};
    var sel = document.getElementById('parent_id') || document.querySelector('select[name="parent_id"]');
    if (!sel) return;
    
    // Set it + trigger change (some screens update UI on change)
    sel.value = String(parentId);
    sel.dispatchEvent(new Event('change', { bubbles: true }));
}