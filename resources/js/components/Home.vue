<script>
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';
import * as THREE from 'three';

export default {
  setup() {
    // Reactive references for charts
    const revenueChart = ref(null);
    const lineChart = ref(null);
    const donutChart = ref(null);
    const gaugeChart = ref(null);

    // Animated top buyers references
    const topBuyersAnimated = ref(null);
    const animatedCounter = ref(0);
    const currentBuyerIndex = ref(0);

    // Current date formatted
    const currentDate = ref(
      new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    );

    // Sample data - replace with your actual API or store data
    const recentInvoices = ref([
      { id: 1, number: 'INV-001', buyer: 'Acme Corp', amount: 1950.0, date: new Date('2023-05-12'), status: 'paid' },
      { id: 2, number: 'INV-002', buyer: 'Globex Inc', amount: 2340.5, date: new Date('2023-05-15'), status: 'pending' },
      { id: 3, number: 'INV-003', buyer: 'Wayne Enterprises', amount: 1200.0, date: new Date('2023-05-18'), status: 'paid' },
      { id: 4, number: 'INV-004', buyer: 'Stark Industries', amount: 3500.75, date: new Date('2023-05-20'), status: 'overdue' }
    ]);

    const topBuyers = ref([
      { name: 'Acme Corporation', total: 15400, percentage: 70, color: '#4361ee' },
      { name: 'Globex Inc', total: 10200, percentage: 55, color: '#32ca99' },
      { name: 'Wayne Enterprises', total: 8700, percentage: 40, color: '#ffa15b' }
    ]);

    const statsCards = ref([
      { label: 'Total Revenue', value: '$24,500', icon: 'fas fa-dollar-sign', color: '#4361ee' },
      { label: 'Invoices', value: '34', icon: 'fas fa-file-invoice', color: '#32ca99' },
      { label: 'Clients', value: '12', icon: 'fas fa-users', color: '#ffa15b' },
      { label: 'Due Amount', value: '$5,240', icon: 'fas fa-clock', color: '#f7677b' }
    ]);

    // Methods
    const formatMoney = (amount) => {
      return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString();
    };

    const getStatus = (id) => {
      const invoice = recentInvoices.value.find((inv) => inv.id === id);
      return invoice ? invoice.status : '';
    };

    const getStatusClass = (status) => {
      return {
        'status-paid': status === 'paid',
        'status-pending': status === 'pending',
        'status-overdue': status === 'overdue'
      };
    };

    const getStatusColor = (status) => {
      switch (status) {
        case 'paid':
          return '#32ca99';
        case 'pending':
          return '#ffa15b';
        case 'overdue':
          return '#f7677b';
        default:
          return '#6c757d';
      }
    };

    const getBuyerInitials = (name) => {
      return name?.split(' ').map((n) => n[0]).join('').toUpperCase() || '';
    };

    // Counter animation for Top Buyer widget
    const animateCounter = (targetValue) => {
      const duration = 1500; // animation duration in ms
      const startTime = Date.now();
      const startValue = animatedCounter.value;

      function updateCounter() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease out cubic function for smooth animation
        const easeOut = 1 - Math.pow(1 - progress, 3);

        animatedCounter.value = Math.floor(
          startValue + (targetValue - startValue) * easeOut
        );

        if (progress < 1) {
          requestAnimationFrame(updateCounter);
        } else {
          animatedCounter.value = targetValue;
        }
      }

      updateCounter();
    };

    const rotateBuyerData = () => {
      const nextIndex = (currentBuyerIndex.value + 1) % topBuyers.value.length;
      currentBuyerIndex.value = nextIndex;
      animateCounter(topBuyers.value[nextIndex].percentage);
    };

    // --------------- CHART INITIALIZATIONS ---------------

    // Performance Toggle
    const showWeekly = ref(true);

    const togglePerformance = () => {
      chartAnimation();
      setTimeout(() => {
        showWeekly.value = !showWeekly.value;
        updateRevenueChart();
      }, 300); // Wait for the animation to complete (adjust as needed)
    };

    const chartAnimation = () => {
      const chartContainer = revenueChart.value.closest('.dashboard-card');
      chartContainer.classList.add('chart-animation');
      setTimeout(() => {
        chartContainer.classList.remove('chart-animation');
      }, 500); // Adjust the timeout to match your CSS animation duration
    };

    const updateRevenueChart = () => {
      if (revenueChart.value) {
        const ctx = revenueChart.value.getContext('2d');
        const chartData = {
          type: 'bar',
          data: {
            labels: showWeekly.value ? ['Week 1', 'Week 2', 'Week 3', 'Week 4'] : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
              {
                label: 'Current Period',
                data: showWeekly.value ? [12000, 19000, 15000, 21000] : [22000, 29000, 25000, 31000, 28000, 35000, 30000, 27000, 33000, 29000, 36000, 32000],
                backgroundColor: '#4361ee'
              },
              {
                label: 'Comparison Period',
                data: showWeekly.value ? [10000, 18000, 14000, 20000] : [20000, 28000, 24000, 29000, 26000, 33000, 28000, 25000, 31000, 27000, 34000, 30000],
                backgroundColor: 'rgba(128,128,128,0.4)' // subtle gray with some transparency
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: '#f0f0f0'
                }
              },
              x: {
                grid: {
                  display: false
                }
              }
            }
          }
        };

        // Destroy the existing chart instance if it exists
        if (window.myRevenueChart) {
          window.myRevenueChart.destroy();
        }

        // Create a new chart instance and store it in the window object
        window.myRevenueChart = new Chart(ctx, chartData);
      }
    };

    onMounted(() => {
      updateRevenueChart();

      // ---------------- LINE CHART (Invoice Trend) ----------------
      if (lineChart.value) {
        new Chart(lineChart.value.getContext('2d'), {
          type: 'line',
          data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
              {
                label: 'Invoices',
                data: [12, 19, 15, 21, 16, 23, 18],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                tension: 0.4,
                fill: true
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: '#f0f0f0'
                }
              },
              x: {
                grid: {
                  display: false
                }
              }
            }
          }
        });
      }

      // ---------------- DONUT CHART (Proportions among Representatives) ----------------
      // Colors: various shades of blue and green for clear differentiation
      if (donutChart.value) {
        const ctx = donutChart.value.getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: topBuyers.value.map((buyer) => buyer.name),
            datasets: [
              {
                data: topBuyers.value.map((buyer) => buyer.total),
                backgroundColor: ['#4361ee', '#32ca99', '#ffa15b'],
                hoverOffset: 4
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%', // makes it a donut
            plugins: {
              legend: {
                display: true,
                position: 'bottom'
              }
            }
          }
        });
      }

      // ---------------- GAUGE CHART (Sales Target) ----------------
      // Semi-circular gauge with gradient shades of blue and green
      if (gaugeChart.value) {
        const ctx = gaugeChart.value.getContext('2d');
        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, 150);
        gradient.addColorStop(0, '#4361ee');
        gradient.addColorStop(1, '#32ca99');

        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Achieved', 'Remaining'],
            datasets: [
              {
                data: [70, 30], // 70% achieved, 30% remaining
                backgroundColor: [gradient, 'rgba(0,0,0,0.1)'], // gradient vs. subtle gray
                borderWidth: 0
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            rotation: -90, // start angle for semi-circle
            circumference: 180, // 180 = semi-circle
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                enabled: false
              }
            }
          }
        });
      }

      // ---------------- Animate the "Top Buyers" rotating data ----------------
      animateCounter(topBuyers.value[0].percentage);
      setInterval(rotateBuyerData, 2000);
    });

    return {
      currentDate,
      revenueChart,
      lineChart,
      donutChart,
      gaugeChart,
      topBuyersAnimated,
      recentInvoices,
      topBuyers,
      statsCards,
      currentBuyerIndex,
      animatedCounter,
      formatMoney,
      formatDate,
      getStatus,
      getStatusClass,
      getStatusColor,
      getBuyerInitials,
      showWeekly,
      togglePerformance
    };
  }
};
</script>

<template>
  <div class="dashboard">
    <div class="container">

      <!-- Stats Cards Row -->
      <div class="stats-row">
        <div class="stat-card" v-for="(stat, index) in statsCards" :key="index">
          <div class="stat-icon" :style="{ color: stat.color }">
            <i :class="stat.icon"></i>
          </div>
          <div class="stat-content">
            <h3>{{ stat.value }}</h3>
            <p>{{ stat.label }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="dashboard-grid">

        <!-- 1) Bar Chart: (Comparative Performance) -->
        <div class="dashboard-card revenue-chart translucent-card">
          <div class="card-header">
            <h3>
              Weekly Performance
              <button @click="togglePerformance" class="performance-toggle">
                Show {{ showWeekly ? 'Monthly' : 'Weekly' }}
              </button>
            </h3>
          </div>
          <div class="chart-container">
            <canvas ref="revenueChart"></canvas>
          </div>
        </div>

        <!-- 2) Animated Top Buyers -->
        <div class="dashboard-card top-buyers-animated">
          <div class="animated-buyers-container">
            <div class="animated-content">
              <br>
              <h3 class="animated-title">Top Buyer</h3>
              <div class="animated-item" v-if="topBuyers.length > 0">
                <div
                  class="buyer-avatar"
                  :style="{ backgroundColor: topBuyers[currentBuyerIndex].color, width: '55px', height: '55px', fontSize: '1.2rem' }"
                >
                  {{ getBuyerInitials(topBuyers[currentBuyerIndex].name) }}
                </div>
                <div class="buyer-name">{{ topBuyers[currentBuyerIndex].name }}</div>
                <div class="buyer-percentage">
                  <div class="animated-counter">{{ animatedCounter }}%</div>
                </div>
                <div class="progress-bar" style="height: 12px;">
                  <div
                    class="progress"
                    :style="{
                      width: animatedCounter + '%',
                      backgroundColor: topBuyers[currentBuyerIndex].color
                    }"
                  ></div>
                </div>
                <div class="buyer-total-container">
                  <div class="buyer-total-badge">
                    <span class="currency-symbol">$</span>
                    <span class="amount">{{ topBuyers[currentBuyerIndex].total.toLocaleString() }}</span>
                  </div>
                  <div class="buyer-total-label" style="margin-left: -20px;">Total Revenue</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3) Line Chart: (Invoice Trend) -->
        <div class="dashboard-card trend-chart translucent-card">
          <div class="card-header">
            <h3>Invoice Trend</h3>
          </div>
          <div class="chart-container">
            <canvas ref="lineChart"></canvas>
          </div>
        </div>

        <!-- 4) Top Buyers List -->
        <div class="dashboard-card top-buyers translucent-card">
          <div class="card-header">
            <h3>Top Buyers</h3>
          </div>
          <div class="buyers-list">
            <div class="buyer-item" v-for="(buyer, index) in topBuyers" :key="index">
              <div class="buyer-info">
                <div class="buyer-avatar" :style="{ backgroundColor: buyer.color }">
                  {{ getBuyerInitials(buyer.name) }}
                </div>
                <div class="buyer-details">
                  <div class="buyer-name">{{ buyer.name }}</div>
                  <div class="buyer-total">${{ buyer.total.toLocaleString() }}</div>
                </div>
              </div>
              <div class="progress-container">
                <div class="progress-bar">
                  <div
                    class="progress"
                    :style="{ width: buyer.percentage + '%', backgroundColor: buyer.color }"
                  ></div>
                </div>
                <div class="progress-value">{{ buyer.percentage }}%</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 5) Donut Chart: (Proportions among Representatives) -->
        <div class="dashboard-card donut-chart translucent-card">
          <div class="card-header">
            <h3>Buyers Distribution</h3>
          </div>
          <div class="chart-container">
            <canvas ref="donutChart"></canvas>
          </div>
        </div>

        <!-- 6) Gauge Chart: (Sales Target) -->
        <div class="dashboard-card gauge-chart translucent-card">
          <div class="card-header">
            <h3>Sales Target</h3>
          </div>
          <div class="chart-container gauge-chart-container">
            <canvas ref="gaugeChart"></canvas>
            <!-- Text overlay for gauge percentage (optional) -->
            <div class="gauge-percentage" style="margin-top: 130px;">
              70% Achieved
            </div>
          </div>
        </div>

        <!-- 7) Recent Invoices Table -->
        <div class="dashboard-card invoices-card">
          <div class="card-header">
            <h3>Recent Invoices</h3>
          </div>
          <div class="table-container">
            <table class="invoice-table">
              <thead>
                <tr>
                  <th>Invoice #</th>
                  <th>Buyer</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="invoice in recentInvoices" :key="invoice.id">
                  <td>{{ invoice.number }}</td>
                  <td>{{ invoice.buyer }}</td>
                  <td>${{ formatMoney(invoice.amount) }}</td>
                  <td>{{ formatDate(invoice.date) }}</td>
                  <td>
                    <span
                      class="status-badge"
                      :class="getStatusClass(invoice.status)"
                      :style="{ backgroundColor: getStatusColor(invoice.status) }"
                    >
                      {{ invoice.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

/* Base Styles */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.dashboard {
  min-height: 100vh;
  background-color: #ffffff;
  color: #333;
  font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
  padding: 2rem 0;
}

.container {
  max-width: 2500px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Translucent card for subtle distinction (transparency effects) */
.translucent-card {
  background-color: rgba(255, 255, 255, 0.7);
}

/* Stats Row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: linear-gradient(207deg, var(--primary-primary-050, #E0EDFF) 67.38%, var(--primary-primary-100, #92BFFF) 124.39%);
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  font-size: 1.25rem;
}

.stat-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
}

.stat-content p {
  font-size: 0.875rem;
  color: #6c757d;
  margin: 0;
}

/* Dashboard Grid Layout */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-auto-rows: minmax(280px, auto);
  gap: 1.5rem;
}

.dashboard-card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.card-header {
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  position: relative;
  display: inline-block;
}

.card-header h3::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -6px;
  width: 30px;
  height: 3px;
  background-color: #4361ee;
  border-radius: 3px;
}

/* Chart Container */
.chart-container {
  flex: 1;
  position: relative;
  min-height: 200px;
}

/* Animated Top Buyers */
.top-buyers-animated {
  grid-column: span 4;
  padding: 0;
  overflow: hidden;
}

.animated-buyers-container {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 280px;
  background: linear-gradient(108deg, #0D264B 1.55%, #1F5AB1 129.02%);
  box-shadow: 0px 0px 13.3px 0px rgba(0, 0, 0, 0.22);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
}

.animated-content {
  position: relative;
  z-index: 10;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.animated-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 2rem;
  text-align: center;
}

.animated-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
  animation: fadeIn 0.5s ease;
}

.buyer-name {
  font-size: 1.25rem;
  font-weight: 500;
  margin-bottom: 1rem;
  text-align: center;
}

.buyer-percentage {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.animated-counter {
  position: relative;
  display: inline-block;
  animation: pulseAnimation 1s ease-in-out;
  text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.5);
  transform-style: preserve-3d;
  perspective: 1000px;
}

.animated-counter::before {
  content: '';
  position: absolute;
  top: -5px;
  left: -5px;
  right: -5px;
  bottom: -5px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  transform: translateZ(-10px);
  filter: blur(5px);
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  overflow: hidden;
  margin-top: 1rem;
}

.progress {
  height: 100%;
  border-radius: 4px;
  transition: width 1s ease-in-out;
}

.buyer-total {
  font-size: 0.875rem;
  margin-top: 1rem;
}

/* Top Buyers List */
.top-buyers {
  grid-column: span 4;
}

.buyers-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.buyer-item {
  padding: 0.5rem 0;
}

.buyer-info {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.buyer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  margin-right: 1rem;
}

.buyer-details {
  flex: 1;
}

.buyer-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.buyer-total {
  font-size: 0.875rem;
  color: #6c757d;
}

.progress-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background-color: #f0f0f0;
  border-radius: 3px;
  overflow: hidden;
}

.progress-value {
  font-size: 0.875rem;
  font-weight: 600;
  min-width: 40px;
  text-align: right;
}

/* Donut Chart */
.donut-chart {
  grid-column: span 6;
}

/* Gauge Chart */
.gauge-chart {
  grid-column: span 6;
  position: relative;
}

.gauge-chart-container {
  position: relative;
}

.gauge-percentage {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
}

/* Recent Invoices Table */
.invoices-card {
  grid-column: span 12;
}

.table-container {
  width: 100%;
  overflow-x: auto;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
}

.invoice-table th,
.invoice-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #f0f0f0;
}

.invoice-table th {
  font-weight: 600;
  color: #6c757d;
  font-size: 0.875rem;
}

.invoice-table tr:hover td {
  background-color: #f8f9fa;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  text-transform: capitalize;
}

/* Grid Layout for Cards (Specific Columns) */
.revenue-chart {
  grid-column: span 8;
}

.trend-chart {
  grid-column: span 8;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulseAnimation {
  0% {
    transform: scale(0.8) translateZ(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2) translateZ(20px);
  }
  100% {
    transform: scale(1) translateZ(0);
    opacity: 1;
  }
}

@keyframes chartAnimation {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(0.95);
    opacity: 0.7;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.chart-animation {
  animation: chartAnimation 0.5s ease;
}

/* Responsive Adjustments */
@media (max-width: 1200px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
  .revenue-chart,
  .trend-chart {
    grid-column: span 7;
  }
  .top-buyers-animated,
  .top-buyers {
    grid-column: span 5;
  }
  .donut-chart,
  .gauge-chart {
    grid-column: span 6;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }
  .stats-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  .dashboard-grid {
    gap: 1rem;
  }
  .revenue-chart,
  .top-buyers-animated,
  .trend-chart,
  .top-buyers,
  .donut-chart,
  .gauge-chart,
  .invoices-card {
    grid-column: span 12;
  }
}

/* Toggle Button Style */
.performance-toggle {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 5px 10px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 12px;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.performance-toggle:hover {
  background-color: #3e8e41;
}
</style>