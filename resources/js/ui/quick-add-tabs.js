const activeClasses = ['bg-surface', 'text-ink', 'shadow-card'];
const inactiveClasses = ['text-muted', 'hover:text-ink'];

function setTab(sheet, name) {
    const tabs = sheet.querySelectorAll('[data-quick-add-tab]');
    const panels = sheet.querySelectorAll('[data-quick-add-panel]');

    tabs.forEach((tab) => {
        const active = tab.dataset.quickAddTab === name;

        tab.setAttribute('aria-selected', active ? 'true' : 'false');

        activeClasses.forEach((className) => tab.classList.toggle(className, active));
        inactiveClasses.forEach((className) => tab.classList.toggle(className, ! active));

        if (active) {
            tab.removeAttribute('tabindex');
        } else {
            tab.setAttribute('tabindex', '-1');
        }
    });

    panels.forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.quickAddPanel !== name);
    });

    sheet.dataset.activeTab = name;
}

export function initQuickAddTabs() {
    const sheet = document.getElementById('quick-add');

    if (! sheet) {
        return;
    }

    const initial = sheet.dataset.initialTab === 'income' ? 'income' : 'expense';
    setTab(sheet, initial);

    sheet.querySelectorAll('[data-quick-add-tab]').forEach((tab) => {
        tab.addEventListener('click', () => setTab(sheet, tab.dataset.quickAddTab));
    });

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-sheet-open="quick-add"]');

        if (opener?.dataset.sheetTab) {
            setTab(sheet, opener.dataset.sheetTab);
        }
    });
}
