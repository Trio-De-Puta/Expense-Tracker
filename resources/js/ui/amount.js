export function initAmountFields() {
    document.querySelectorAll('.amount-field').forEach((field) => {
        const input = field.querySelector('input[name]');
        const warning = field.querySelector('[data-amount-warning]');

        if (! input || ! warning) {
            return;
        }

        const check = () => {
            const value = input.value.trim();
            const amount = Number(value);
            const invalid = value !== '' && (! Number.isFinite(amount) || amount <= 0);

            warning.classList.toggle('hidden', ! invalid);
        };

        input.addEventListener('input', check);
        check();
    });
}
