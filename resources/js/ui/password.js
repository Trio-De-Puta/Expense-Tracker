export function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);

            if (! input) {
                return;
            }

            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

            button.querySelector('[data-eye]')?.classList.toggle('hidden', show);
            button.querySelector('[data-eye-off]')?.classList.toggle('hidden', ! show);

            input.focus({ preventScroll: true });
        });
    });
}
