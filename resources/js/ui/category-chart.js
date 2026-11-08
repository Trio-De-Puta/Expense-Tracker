import { ArcElement, Chart, DoughnutController, Legend, Tooltip } from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

export function initCategoryChart() {
    const canvas = document.querySelector('[data-category-chart]');
    const dataElement = document.getElementById('category-chart-data');

    if (!canvas || !dataElement) {
        return;
    }

    let rows;

    try {
        rows = JSON.parse(dataElement.textContent);
    } catch {
        return;
    }

    if (!Array.isArray(rows) || rows.length === 0) {
        return;
    }

    const styles = getComputedStyle(document.documentElement);
    const color = (token) => styles.getPropertyValue(`--color-${token}`).trim() || '#5f665f';
    const surface = styles.getPropertyValue('--color-surface').trim() || '#ffffff';
    const ink = styles.getPropertyValue('--color-ink').trim() || '#1f2621';
    const fontFamily = styles.getPropertyValue('--font-sans').trim() || 'Instrument Sans, sans-serif';
    const amounts = rows.map((row) => Number(row.amount) || 0);
    const total = amounts.reduce((sum, amount) => sum + amount, 0);
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const formatAmount = (amount) =>
        new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount);

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: rows.map((row) => row.label),
            datasets: [
                {
                    data: amounts,
                    backgroundColor: rows.map((row) => color(row.color)),
                    borderColor: surface,
                    borderWidth: 2,
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            animation: prefersReducedMotion ? false : { duration: 650 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        color: ink,
                        font: {
                            family: fontFamily,
                            size: 12,
                        },
                        generateLabels(chart) {
                            const dataset = chart.data.datasets[0];

                            return chart.data.labels.map((label, index) => ({
                                text: `${label} ₱${formatAmount(dataset.data[index])}`,
                                fillStyle: dataset.backgroundColor[index],
                                strokeStyle: dataset.backgroundColor[index],
                                lineWidth: 0,
                                hidden: !chart.getDataVisibility(index),
                                index,
                            }));
                        },
                    },
                },
                tooltip: {
                    backgroundColor: ink,
                    titleFont: {
                        family: fontFamily,
                        size: 12,
                    },
                    bodyFont: {
                        family: fontFamily,
                        size: 12,
                    },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label(context) {
                            const amount = Number(context.raw) || 0;
                            const percent = total > 0 ? Math.round((amount / total) * 100) : 0;

                            return ` ₱${formatAmount(amount)} (${percent}%)`;
                        },
                    },
                },
            },
        },
    });
}
