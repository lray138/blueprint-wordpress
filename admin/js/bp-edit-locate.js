document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const bpId = params.get('bp_edit');

    if (!bpId) {
        console.log('Blueprint: no bp_edit parameter found');
        return;
    }

    console.log('Blueprint: looking for field with id:', bpId);

    function wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function findBpInput(bpId) {
        const containers = document.querySelectorAll('.postbox-container');

        for (const container of containers) {
            const input = container.querySelector(
                `input[value="${CSS.escape(bpId)}"]`
            );

            if (input) {
                return input;
            }
        }

        return null;
    }

    function openGroup(group) {
        if (!group || !group.classList.contains('cf-complex__group')) {
            return;
        }

        console.log('Blueprint: opening group', group);

        group.classList.remove(
            'cf-complex__group--collapsed',
            '-collapsed',
            'collapsed'
        );

        const body = group.querySelector(':scope > .cf-complex__group-body');
        if (body) {
            body.hidden = false;
            body.removeAttribute('hidden');
            body.style.display = '';
        }

        const collapseText = group.querySelector(':scope > .cf-complex__group-actions .cf-complex__group-action-text:last-child');
        const collapseIcon = group.querySelector(':scope > .cf-complex__group-actions .cf-complex__group-action-icon.dashicons-arrow-down');

        if (collapseIcon) {
            collapseIcon.classList.remove('dashicons-arrow-down');
            collapseIcon.classList.add('dashicons-arrow-up');
        }

        if (collapseText && collapseText.textContent.trim() === 'Collapse') {
            collapseText.textContent = 'Collapse';
        }
    }

    function openGroups(input) {
        let node = input.closest('.cf-complex__group');

        while (node) {
            openGroup(node);

            const parentPostbox = node.parentElement?.closest('.postbox-container');
            if (parentPostbox && parentPostbox.contains(node) && node.parentElement?.classList.contains('postbox-container')) {
                break;
            }

            node = node.parentElement?.closest('.cf-complex__group') || null;
        }
    }

    async function locate() {
        let input = null;

        for (let i = 0; i < 20; i++) {
            input = findBpInput(bpId);

            if (input) {
                console.log('Blueprint: input found!', input);
                break;
            }

            await wait(200);
        }

        if (!input) {
            console.warn('Blueprint: field not found:', bpId);
            return;
        }

        openGroups(input);

        const target =
            input.closest('.cf-complex__group') ||
            input.closest('.cf-field') ||
            input;

        console.log('Blueprint: scrolling to', target);

        setTimeout(() => {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 200);
    }

    locate();
});