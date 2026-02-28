import { Controller } from '@hotwired/stimulus';
import { Chart, BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend);

export default class extends Controller {
    static values = {
        url: String,
    };

    connect() {
        this.loadChart();
    }

    disconnect() {
        if (this.chart) {
            this.chart.destroy();
        }
    }

    async loadChart() {
        try {
            const response = await fetch(this.urlValue);
            const data = await response.json();

            if (!data || data.length === 0) {
                this.element.innerHTML = '<p class="text-muted text-center small">No data available</p>';
                return;
            }

            const labels = [];
            for (let i = 1; i <= 29; i++) {
                labels.push(String(i));
            }
            labels.push('30+');

            const canvas = document.createElement('canvas');
            this.element.appendChild(canvas);

            this.chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Records',
                            data: data,
                            backgroundColor: '#e06500',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        } catch {
            this.element.innerHTML = '<p class="text-danger text-center small">Error loading chart</p>';
        }
    }
}
