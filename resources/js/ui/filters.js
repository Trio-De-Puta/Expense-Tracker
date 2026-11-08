export function initRangePresets() {
    document.querySelectorAll('[data-range]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');

            if (! form) {
                return;
            }

            const from = form.querySelector('[name="from"]');
            const to = form.querySelector('[name="to"]');

            if (from) {
                from.value = button.dataset.from || '';
            }

            if (to) {
                to.value = button.dataset.to || '';
            }

            form.submit();
        });
    });
}
