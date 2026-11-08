import { showToast } from './toast';

const pendingForms = new WeakSet();

const focusableSelector = [
    'button:not([disabled])',
    'a[href]',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

let activeForm = null;
let lastFocused = null;

const dialogElement = () => document.querySelector('[data-confirm-dialog]');

function closeConfirmDialog() {
    const dialog = dialogElement();

    if (! dialog || dialog.classList.contains('hidden')) {
        return;
    }

    dialog.classList.add('hidden');

    if (! document.querySelector('[data-sheet].is-open')) {
        document.body.classList.remove('overflow-hidden');
    }

    if (lastFocused && typeof lastFocused.focus === 'function') {
        lastFocused.focus({ preventScroll: true });
    }

    activeForm = null;
    lastFocused = null;
}

function submitWithUndo(form) {
    pendingForms.add(form);

    let cancelled = false;

    showToast('Transaction deleted.', {
        action: 'Undo',
        duration: 5000,
        onAction: () => {
            cancelled = true;
            pendingForms.delete(form);
            showToast('Transaction kept.');
        },
        onExpire: () => {
            if (cancelled) {
                return;
            }

            form.submit();
        },
    });
}

function openConfirmDialog(form) {
    const dialog = dialogElement();

    if (! dialog) {
        if (window.confirm('Delete this transaction?')) {
            submitWithUndo(form);
        }

        return;
    }

    activeForm = form;
    lastFocused = document.activeElement;

    const label = form.dataset.deleteLabel;
    const message = dialog.querySelector('[data-confirm-message]');

    if (message) {
        message.textContent = label
            ? `“${label}” will be deleted. You can undo it for a few seconds.`
            : 'This transaction will be deleted. You can undo it for a few seconds.';
    }

    dialog.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    requestAnimationFrame(() => {
        dialog.querySelector('[data-confirm-accept]')?.focus({ preventScroll: true });
    });
}

export function initDeleteConfirmation() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-delete-form]');

        if (! form) {
            return;
        }

        event.preventDefault();

        if (pendingForms.has(form)) {
            return;
        }

        openConfirmDialog(form);
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-cancel]')) {
            event.preventDefault();
            closeConfirmDialog();

            return;
        }

        if (event.target.closest('[data-confirm-accept]')) {
            event.preventDefault();

            if (! activeForm) {
                return;
            }

            const form = activeForm;
            closeConfirmDialog();
            submitWithUndo(form);
        }
    });

    document.addEventListener('keydown', (event) => {
        const dialog = dialogElement();

        if (! dialog || dialog.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeConfirmDialog();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const panel = dialog.querySelector('[data-confirm-panel]');
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
}
