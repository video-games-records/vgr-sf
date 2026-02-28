import { Controller } from '@hotwired/stimulus';
import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend);

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

            if (!data.dates || data.dates.length === 0) {
                this.element.innerHTML = '<p class="text-muted text-center small">No data available</p>';
                return;
            }

            const canvas = document.createElement('canvas');
            this.element.appendChild(canvas);

            this.chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: 'Platinum',
                            data: data.rank0,
                            borderColor: '#b3e9ff',
                            backgroundColor: '#b3e9ff',
                            tension: 0.3,
                            pointRadius: 0,
                        },
                        {
                            label: 'Gold',
                            data: data.rank1,
                            borderColor: '#ffd900',
                            backgroundColor: '#ffd900',
                            tension: 0.3,
                            pointRadius: 0,
                        },
                        {
                            label: 'Silver',
                            data: data.rank2,
                            borderColor: '#bfbfbf',
                            backgroundColor: '#bfbfbf',
                            tension: 0.3,
                            pointRadius: 0,
                        },
                        {
                            label: 'Bronze',
                            data: data.rank3,
                            borderColor: '#cd8032',
                            backgroundColor: '#cd8032',
                            tension: 0.3,
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
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
