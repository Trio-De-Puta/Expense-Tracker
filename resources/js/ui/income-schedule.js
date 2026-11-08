function daysInMonth(year, month) {
    return new Date(year, month + 1, 0).getDate();
}

function resolveDate(year, month, day) {
    const lastDay = daysInMonth(year, month);
    const normalized = Number(day) === 0 ? lastDay : Math.min(Number(day), lastDay);

    return new Date(year, month, normalized);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function latestPaydayThisMonth(payDays) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const candidates = payDays
        .map((day) => resolveDate(today.getFullYear(), today.getMonth(), day))
        .filter((date) => date <= today)
        .sort((a, b) => b - a);

    return candidates[0] ?? today;
}

function initForm(form) {
    const frequency = form.querySelector('[data-income-frequency]');
    const primaryWrap = form.querySelector('[data-payday="primary"]');
    const secondaryWrap = form.querySelector('[data-payday="secondary"]');
    const primary = primaryWrap?.querySelector('select');
    const secondary = secondaryWrap?.querySelector('select');
    const date = form.querySelector('[data-income-date]');

    if (! frequency || ! primary || ! secondary) {
        return;
    }

    const showsPrimary = () => ['monthly', 'bi_monthly'].includes(frequency.value);

    const syncVisibility = () => {
        primaryWrap.classList.toggle('hidden', ! showsPrimary());
        secondaryWrap.classList.toggle('hidden', frequency.value !== 'bi_monthly');
    };

    const fillDate = () => {
        if (! date || ! showsPrimary() || date.dataset.userEdited === '1') {
            return;
        }

        const payDays = [primary.value];

        if (frequency.value === 'bi_monthly') {
            payDays.push(secondary.value);
        }

        date.value = formatDate(latestPaydayThisMonth(payDays));
    };

    syncVisibility();

    if (form.dataset.autofillDate === '1') {
        fillDate();
    }

    frequency.addEventListener('change', () => {
        if (frequency.value === 'bi_monthly' && primary.value === secondary.value) {
            primary.value = '15';

            if (primary.value === secondary.value) {
                secondary.value = '0';
            }
        }

        syncVisibility();
        fillDate();
    });

    [primary, secondary].forEach((select) => {
        select.addEventListener('change', fillDate);
    });

    date?.addEventListener('input', () => {
        date.dataset.userEdited = '1';
    });
}

export function initIncomeSchedule() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.querySelector('[data-income-frequency]') && form.querySelector('[data-income-date]')) {
            initForm(form);
        }
    });
}
