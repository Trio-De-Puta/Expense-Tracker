const region = () => document.getElementById('toast-region');

export function dismissToast(element) {
    if (! element) {
        return;
    }

    window.clearTimeout(element._toastTimer);

    element.classList.add('transition', 'duration-200', 'translate-y-2', 'opacity-0');
    window.setTimeout(() => element.remove(), 180);
}

export function showToast(message, options = {}) {
    const container = region();

    if (! container) {
        return null;
    }

    const {
        action = null,
        onAction = null,
        duration = 4000,
        onExpire = null,
    } = options;

    const element = document.createElement('div');
    element.className = 'pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-card border border-line bg-surface px-4 py-3 shadow-float translate-y-2 opacity-0 transition duration-200';
    element.setAttribute('role', 'status');

    const icon = document.createElement('span');
    icon.className = 'grid h-8 w-8 shrink-0 place-items-center rounded-full bg-success/10 text-success';
    icon.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i>';

    const text = document.createElement('p');
    text.className = 'flex-1 text-sm font-medium';
    text.textContent = message;

    element.append(icon, text);

    let settled = false;

    const settle = (callback) => {
        if (settled) {
            return;
        }

        settled = true;

        if (callback) {
            callback();
        }
    };

    if (action) {
        const actionButton = document.createElement('button');
        actionButton.type = 'button';
        actionButton.className = 'rounded-control px-2 py-1 text-sm font-semibold text-brand outline-none transition hover:bg-brand-soft focus-visible:ring-2 focus-visible:ring-brand/30';
        actionButton.textContent = action;
        actionButton.addEventListener('click', () => {
            settle(onAction);
            dismissToast(element);
        });
        element.append(actionButton);
    }

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'grid h-8 w-8 shrink-0 place-items-center rounded-control text-muted transition hover:bg-brand-soft hover:text-brand';
    closeButton.setAttribute('aria-label', 'Dismiss notification');
    closeButton.innerHTML = '<i class="bi bi-x-lg text-xs" aria-hidden="true"></i>';
    closeButton.addEventListener('click', () => {
        settle(onExpire);
        dismissToast(element);
    });
    element.append(closeButton);

    container.append(element);

    requestAnimationFrame(() => {
        element.classList.remove('translate-y-2', 'opacity-0');
    });

    element._toastTimer = window.setTimeout(() => {
        settle(onExpire);
        dismissToast(element);
    }, duration);

    return element;
}

export function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((element) => {
        const duration = Number(element.dataset.toastDuration || 4000);

        element.querySelector('[data-toast-dismiss]')?.addEventListener('click', () => {
            dismissToast(element);
        });

        element._toastTimer = window.setTimeout(() => dismissToast(element), duration);
    });
}
