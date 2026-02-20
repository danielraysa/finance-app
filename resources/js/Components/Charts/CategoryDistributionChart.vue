<script setup>
import { Chart, registerables } from 'chart.js';
import { ref, watch, onMounted } from 'vue';

Chart.register(...registerables);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        default: 'Category Distribution',
    },
});

const chartContainer = ref(null);
const chart = ref(null);

const getChartData = () => {
    if (!props.data || props.data.length === 0) {
        return { labels: [], datasets: [] };
    }

    const labels = props.data.map(item => item.category_name);
    const actualAmounts = props.data.map(item => item.actual_amount);
    const colors = props.data.map(item => item.category_color);

    return {
        labels,
        datasets: [
            {
                label: 'Actual Amount',
                data: actualAmounts,
                backgroundColor: colors,
                borderColor: colors.map(color => color + '80'),
                borderWidth: 2,
            },
        ],
    };
};

const createChart = () => {
    if (!chartContainer.value) return;

    if (chart.value) {
        chart.value.destroy();
    }

    const ctx = chartContainer.value.getContext('2d');
    chart.value = new Chart(ctx, {
        type: 'doughnut',
        data: getChartData(),
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.parsed;
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0,
                            }).format(value);
                        },
                    },
                },
            },
        },
    });
};

onMounted(() => {
    createChart();
});

// Watch for changes to data
watch(() => props.data, () => {
    createChart();
}, { deep: true });
</script>

<template>
    <div class="w-full h-full" style="position: relative; min-height: 300px; max-height: 450px;">
        <canvas ref="chartContainer"></canvas>
    </div>
</template>
