import './bootstrap';

import { initAmountFields } from './ui/amount';
import { initCategoryChart } from './ui/category-chart';
import { initDeleteConfirmation } from './ui/delete';
import { initRangePresets } from './ui/filters';
import { initGreeting } from './ui/greeting';
import { initIncomeSchedule } from './ui/income-schedule';
import { initPasswordToggles } from './ui/password';
import { initQuickAddTabs } from './ui/quick-add-tabs';
import { initSheets } from './ui/sheet';
import { initToasts } from './ui/toast';

document.addEventListener('DOMContentLoaded', () => {
    initGreeting();
    initCategoryChart();
    initToasts();
    initQuickAddTabs();
    initIncomeSchedule();
    initSheets();
    initAmountFields();
    initRangePresets();
    initPasswordToggles();
    initDeleteConfirmation();
});
