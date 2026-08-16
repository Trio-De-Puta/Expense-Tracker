const budgetForm = document.querySelector("#budgetForm");
const expenseForm = document.querySelector("#expenseForm");
const expenseList = document.querySelector("#expenseList");

const budgetInput = document.querySelector("#budget");
const amountInput = document.querySelector("#amount");
const descriptionInput = document.querySelector("#description");
const categoryInput = document.querySelector("#category");

const budgetDisplay = document.querySelector("#budgetDisplay");
const totalDisplay = document.querySelector("#totalDisplay");
const remainingDisplay = document.querySelector("#remainingDisplay");
const expenseCount = document.querySelector("#expenseCount");
const currentDate = document.querySelector("#currentDate");

let budget = Number(localStorage.getItem("dailyBudget")) || 0;
let expenses = JSON.parse(localStorage.getItem("expenses")) || [];

currentDate.textContent = new Date().toLocaleDateString("en-PH", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric"
});

function formatMoney(amount) {
    return `₱${amount.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}

function saveData() {
    localStorage.setItem("dailyBudget", budget);
    localStorage.setItem("expenses", JSON.stringify(expenses));
}

function updateSummary() {
    const total = expenses.reduce((sum, expense) => sum + expense.amount, 0);

    budgetDisplay.textContent = formatMoney(budget);
    totalDisplay.textContent = formatMoney(total);
    remainingDisplay.textContent = formatMoney(budget - total);

    expenseCount.textContent =
        `${expenses.length} ${expenses.length === 1 ? "expense" : "expenses"}`;
}

function renderExpenses() {
    if (expenses.length === 0) {
        expenseList.innerHTML = `
            <div class="empty-state">
                No expenses recorded today.
            </div>
        `;

        updateSummary();
        return;
    }

    expenseList.innerHTML = expenses.map(expense => `
        <div class="expense-item">
            <div class="expense-info">
                <strong>${escapeHTML(expense.description)}</strong>
                <span>${escapeHTML(expense.category)}</span>
            </div>

            <div class="expense-amount">
                ${formatMoney(expense.amount)}
            </div>

            <button class="delete-btn" data-id="${expense.id}">
                Delete
            </button>
        </div>
    `).join("");

    updateSummary();
}

function escapeHTML(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

budgetForm.addEventListener("submit", event => {
    event.preventDefault();

    budget = Number(budgetInput.value);

    saveData();
    updateSummary();

    budgetForm.reset();
});

expenseForm.addEventListener("submit", event => {
    event.preventDefault();

    const amount = Number(amountInput.value);
    const description = descriptionInput.value.trim();
    const category = categoryInput.value;

    if (amount <= 0 || !description || !category) {
        return;
    }

    expenses.unshift({
        id: Date.now(),
        amount,
        description,
        category
    });

    saveData();
    renderExpenses();
    expenseForm.reset();
});

expenseList.addEventListener("click", event => {
    const deleteButton = event.target.closest(".delete-btn");

    if (!deleteButton) {
        return;
    }

    const id = Number(deleteButton.dataset.id);

    expenses = expenses.filter(expense => expense.id !== id);

    saveData();
    renderExpenses();
});

budgetInput.value = budget || "";

renderExpenses();