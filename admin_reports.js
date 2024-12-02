const chartData = <?php echo $vehicle_data; ?>;


const ctx = document.getElementById('vehicle-chart').getContext('2d');
const vehicleChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Number of Rentals',
            data: chartData.values,
            backgroundColor: 'rgba(75, 192, 192, 0.5)', // Bar color
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});