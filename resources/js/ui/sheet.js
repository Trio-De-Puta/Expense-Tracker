const openSheets = [];
let lastFocused = null;

const focusableSelector = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

export function openSheet(sheet) {
    if (! sheet) {
        return;
    }

    lastFocused = document.activeElement;

    sheet.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    requestAnimationFrame(() => {
        sheet.classList.add('is-open');

        const panel = sheet.querySelector('[data-sheet-panel]');
        const scope = sheet.querySelector('[data-quick-add-panel]:not(.hidden)') ?? panel;
        const firstField = scope?.querySelector('input:not([type="hidden"]):not([type="radio"]), select, textarea');
        const target = firstField ?? scope?.querySelector(focusableSelector);

        target?.focus({ preventScroll: true });
    });

    if (! openSheets.includes(sheet)) {
        openSheets.push(sheet);
    }
}

export function closeSheet(sheet) {
    if (! sheet) {
        return;
    }

    sheet.classList.remove('is-open');

    const index = openSheets.indexOf(sheet);

    if (index !== -1) {
        openSheets.splice(index, 1);
    }

    window.setTimeout(() => {
        sheet.classList.add('hidden');

        if (openSheets.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }, 200);

    if (lastFocused && typeof lastFocused.focus === 'function') {
        lastFocused.focus({ preventScroll: true });
    }
}

export function initSheets() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-sheet-open]');

        if (opener) {
            event.preventDefault();
            openSheet(document.getElementById(opener.dataset.sheetOpen));

            return;
        }

        if (event.target.closest('[data-sheet-close]') || event.target.closest('[data-sheet-backdrop]')) {
            closeSheet(event.target.closest('[data-sheet]'));
        }
    });

    document.addEventListener('keydown', (event) => {
        const sheet = openSheets[openSheets.length - 1];

        if (! sheet) {
            if (event.key === 'n' && ! event.metaKey && ! event.ctrlKey && ! event.altKey) {
                const active = document.activeElement;
                const tag = active?.tagName;
                const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag) || active?.isContentEditable;
                const quickAdd = document.getElementById('quick-add');

                if (! typing && quickAdd) {
                    event.preventDefault();
                    openSheet(quickAdd);
                }
            }

            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeSheet(sheet);

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const panel = sheet.querySelector('[data-sheet-panel]');
        const focusable = Array.from(panel?.querySelectorAll(focusableSelector) ?? [])
            .filter((element) => element.getClientRects().length > 0);

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.querySelectorAll('[data-sheet][data-open-on-load="1"]').forEach((sheet) => openSheet(sheet));
}
