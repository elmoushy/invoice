<template>
  <div class="invoices-dashboard">
    <h2 class="dashboard-title">
      <span class="typewriter-container">
        <span class="typewriter-text" :style="{ width: typewriterWidth }">
          <span class="holographic-text"  style="background-color: black!important;">{{ displayText }}</span>
        </span>
        <span class="futuristic-cursor" :class="{ 'pulse': showCursor }"></span>
      </span>
    </h2>

    <!-- Search, Filter, and Sort -->
    <div class="search-container">
      <div class="search-input-container">
        <i class="fas fa-search search-icon"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search invoices..."
          class="search-input"
        >
      </div>
      <select v-model="selectedStatus" class="filter-select">
        <option value="all">All</option>
        <option value="paid">Paid</option>
        <option value="sent">Sent</option>
        <option value="draft">Draft</option>
        <option value="overdue">Overdue</option>
      </select>
      <select v-model="sortOption" class="sort-select">
        <option value="date-desc">Sort by Date (Newest)</option>
        <option value="date-asc">Sort by Date (Oldest)</option>
        <option value="amount-desc">Sort by Amount (High to Low)</option>
        <option value="amount-asc">Sort by Amount (Low to High)</option>
      </select>
    </div>

    <transition-group name="invoice-list" tag="div" class="invoices-container">
      <div
        v-for="invoice in mappedInvoices"
        :key="invoice.id"
        class="invoice-card"
        :class="`status-${invoice.status}`"

        @click="goToInvoice(invoice.id)"
      >
        <div class="invoice-content">
          <div class="invoice-header">
            <div class="invoice-number">{{ invoice.number }}</div>
            <div
              :class="['invoice-status', `status-${invoice.status}`]"
              :data-status="invoice.statusLabel"
            >
              {{ invoice.statusLabel }}
            </div>
          </div>

          <div class="invoice-body">
            <div class="invoice-customer">
              <i class="fas fa-user"></i> {{ invoice.customerName }}
            </div>
            <div class="invoice-date">
              <i class="far fa-calendar"></i> {{ invoice.date }}
            </div>
            <div class="invoice-amount">
              <i class="fas fa-tag"></i> {{ invoice.amount }}
              <button
                class="action-btn"
                @click.stop="downloadInvoice(invoice.id)"
                title="Download"
                style="margin-left: auto;"
              >
                <i class="fas fa-download"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition-group>

    <!-- Pagination -->
    <div class="pagination" v-if="invoicesData">
      <button
        class="pagination-btn"
        :disabled="currentPage === 1"
        @click="previousPage"
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <span>{{ currentPage }} / {{ totalPages }}</span>
      <button
        class="pagination-btn"
        :disabled="currentPage === totalPages"
        @click="nextPage"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModernInvoicesDashboard',
  data() {
    return {
      // Will hold the paginated response from the API
      invoicesData: null,
      searchQuery: '',
      selectedStatus: 'all',
      sortOption: 'date-desc',
      currentPage: 1,
      fullText: 'Invoices',
      displayText: '',
      typewriterWidth: '0ch',
      showCursor: true,
      typewriterIndex: 0,
      animationSpeed: 100 // Faster for modern feel
    }
  },
  computed: {
    totalPages() {
      return this.invoicesData ? this.invoicesData.last_page : 1;
    },
    // Map the API fields to what your template expects
    mappedInvoices() {
      if (!this.invoicesData || !this.invoicesData.data) return [];
      return this.invoicesData.data.map(invoice => {
        const status = invoice.status ? invoice.status.toLowerCase() : '';
        const statusLabel = invoice.status
          ? invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1).toLowerCase()
          : '';
        return {
          id: invoice.invoice_id,
          number: invoice.invoice_number,
          // Assuming the buyer information is stored under invoice.buyer
          customerName: invoice.buyer && invoice.buyer.buyer_name ? invoice.buyer.buyer_name : '',
          date: invoice.invoice_issue_date,
          // Combine total with tax and currency code for display
          amount: invoice.invoice_total_with_tax + ' ' + (invoice.invoice_currency_code || ''),
          status: status,
          statusLabel: statusLabel
        }
      });
    }
  },
  methods: {
    fetchInvoices() {
      const params = {
        page: this.currentPage,
      };
      if (this.searchQuery) {
        params.search = this.searchQuery;
      }
      if (this.selectedStatus && this.selectedStatus !== 'all') {
        // Capitalize first letter if required by the API
        params.status = this.selectedStatus.charAt(0).toUpperCase() + this.selectedStatus.slice(1);
      }
      // Map sort option to API query parameters
      if (this.sortOption.startsWith('date')) {
        params.sort_date = this.sortOption.endsWith('asc') ? 'asc' : 'desc';
      } else if (this.sortOption.startsWith('amount')) {
        params.sort_amount = this.sortOption.endsWith('asc') ? 'asc' : 'desc';
      }

      axios.get('/api/invoice_case1_index', { params })
        .then(response => {
          if (response.data.status === 200) {
            this.invoicesData = response.data.data;
          }
        })
        .catch(error => {
          console.error('Error fetching invoices:', error);
        });
    },

    // When the user clicks on an invoice card, navigate to the edit form
    goToInvoice(id) {
      // This route name must match your router configuration
      // e.g. { name: 'EditInvoice', path: '/invoices/:id/edit' }
      this.$router.push({ name: 'EditInvoice', params: { id }})
    },

    downloadInvoice(id) {
      console.log('Download invoice', id);
      // Add your download logic here
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.fetchInvoices();
      }
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.fetchInvoices();
      }
    },
    startTypewriter() {
      const typeSpeed = this.animationSpeed;
      const pauseTime = 3000;
      const deleteSpeed = 200;

      const typeNext = () => {
        if (this.typewriterIndex < this.fullText.length) {
          this.displayText += this.fullText[this.typewriterIndex];
          this.typewriterWidth = `${this.displayText.length}ch`;
          this.typewriterIndex++;
          setTimeout(typeNext, typeSpeed);
        } else {
          // Pause before deleting
          setTimeout(() => {
            this.deleteText();
          }, pauseTime);
        }
      };

      const deleteText = () => {
        if (this.displayText.length > 0) {
          this.displayText = this.displayText.slice(0, -1);
          this.typewriterWidth = `${this.displayText.length}ch`;
          setTimeout(deleteText, deleteSpeed);
        } else {
          this.typewriterIndex = 0;
          setTimeout(typeNext, 800);
        }
      };

      this.deleteText = deleteText;
      typeNext();
    },
  },
  watch: {
    // Whenever search, filter, or sort changes, reset to page 1 and re-fetch
    searchQuery() {
      this.currentPage = 1;
      this.fetchInvoices();
    },
    selectedStatus() {
      this.currentPage = 1;
      this.fetchInvoices();
    },
    sortOption() {
      this.currentPage = 1;
      this.fetchInvoices();
    }
  },
  mounted() {
    this.fetchInvoices();
    this.startTypewriter();

    // Smooth cursor animation
    setInterval(() => {
      this.showCursor = !this.showCursor;
    }, 400);
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

:root {
  --primary: #4361ee;
  --primary-light: #4361ee20;
  --secondary: #FFB22E;
  --success: #10b981;
  --danger: #ef4444;
  --light: #f1f5f9;
  --dark: #334155;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  --transition: all 0.3s ease;
  --neon-blue: #00f3ff;
  --neon-purple: #8a2be2;
  --cyber-green: #39ff14;
}

.invoices-dashboard {
  font-family: 'Poppins', sans-serif;
  max-width: auto;
  margin: 0 auto;
  padding: 2rem;
  color: var(--dark);
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: var(--dark);
  display: flex;
  align-items: center;
  min-height: 3rem;
  font-family: 'Orbitron', 'Poppins', sans-serif;
}

.typewriter-container {
    position: relative;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(138, 43, 226, 0.1));
    padding: 0.5rem 1.5rem;
    border-radius: 15px;
    border: 1px solid rgba(67, 97, 238, 0.2);
    backdrop-filter: blur(10px);
    box-shadow:
        0 8px 32px rgba(67, 97, 238, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    min-width: 200px;
    min-height: 80px;
    max-width: 400px;
    max-height: 80px;
}

.typewriter-text {
  display: inline-block;
  overflow: hidden;
  white-space: nowrap;
  transition: width 0.05s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.holographic-text {
  background: linear-gradient(
    45deg,
    var(--neon-blue),
    var(--neon-purple),
    var(--cyber-green),
    var(--neon-blue)
  );
  background-size: 400% 400%;
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: holographicShift 3s ease-in-out infinite;
  filter: drop-shadow(0 0 10px rgba(0, 243, 255, 0.3));
  position: relative;
}

.holographic-text::before {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  background: linear-gradient(45deg, transparent, rgba(0, 243, 255, 0.1), transparent);
  background-clip: text;
  -webkit-background-clip: text;
  animation: glitchEffect 4s linear infinite;
}

.futuristic-cursor {
  width: 3px;
  height: 2rem;
  background: linear-gradient(180deg, var(--neon-blue), var(--cyber-green));
  margin-left: 5px;
  border-radius: 2px;
  box-shadow:
    0 0 10px var(--neon-blue),
    0 0 20px var(--neon-blue),
    0 0 30px var(--neon-blue);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.futuristic-cursor::before {
  content: '';
  position: absolute;
  top: -5px;
  left: -2px;
  width: 7px;
  height: calc(100% + 10px);
  background: radial-gradient(ellipse, rgba(0, 243, 255, 0.3), transparent);
  border-radius: 50%;
  animation: cursorGlow 2s ease-in-out infinite alternate;
}

.futuristic-cursor.pulse {
  opacity: 0;
  transform: scaleY(0.8);
}

/* Futuristic Animations */
@keyframes holographicShift {
  0%, 100% {
    background-position: 0% 50%;
    filter:
      drop-shadow(0 0 10px rgba(0, 243, 255, 0.3))
      drop-shadow(2px 2px 0px rgba(138, 43, 226, 0.1));
  }
  50% {
    background-position: 100% 50%;
    filter:
      drop-shadow(0 0 15px rgba(57, 255, 20, 0.4))
      drop-shadow(-2px -2px 0px rgba(0, 243, 255, 0.1));
  }
}

@keyframes cursorGlow {
  0% {
    box-shadow:
      0 0 5px var(--neon-blue),
      0 0 10px var(--neon-blue),
      0 0 15px var(--neon-blue);
    transform: scaleY(1);
  }
  100% {
    box-shadow:
      0 0 10px var(--cyber-green),
      0 0 20px var(--cyber-green),
      0 0 30px var(--cyber-green);
    transform: scaleY(1.1);
  }
}

@keyframes glitchEffect {
  0%, 90%, 100% {
    opacity: 0;
    transform: translateX(0);
  }
  95% {
    opacity: 0.1;
    transform: translateX(2px);
  }
}

/* Enhanced container glow */
.typewriter-container::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple), var(--cyber-green));
  border-radius: 17px;
  z-index: -1;
  opacity: 0.3;
  filter: blur(8px);
  animation: borderGlow 3s ease-in-out infinite alternate;
}

@keyframes borderGlow {
  0% {
    opacity: 0.2;
    filter: blur(8px);
  }
  100% {
    opacity: 0.4;
    filter: blur(12px);
  }
}

/* Toolbar */
.toolbar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  animation: slideDown 0.5s ease;
}

.btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.7rem 1.2rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.9rem;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--box-shadow);
}
.btn.primary {
  background-color: var(--primary);
  color: white;
}
.btn.secondary {
  background-color: white;
  color: var(--dark);
  border: 1px solid #e2e8f0;
}
.export-dropdown {
  position: relative;
}
.dropdown-toggle {
  background-color: white;
  color: var(--dark);
  border: 1px solid #e2e8f0;
}
.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 0.5rem 0;
  margin-top: 0.5rem;
  min-width: 120px;
  z-index: 10;
  display: none;
}
.export-dropdown:hover .dropdown-menu {
  display: block;
  animation: fadeIn 0.3s ease;
}
.dropdown-menu a {
  display: block;
  padding: 0.5rem 1rem;
  cursor: pointer;
  transition: var(--transition);
}
.dropdown-menu a:hover {
  background-color: var(--primary-light);
  color: var(--primary);
}

/* Search and filter */
.search-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 2rem;
  animation: slideDown 0.7s ease;
}
.search-input-container {
  position: relative;
  flex: 1;
}
.search-icon {
  position: absolute;
  top: 50%;
  left: 10px;
  transform: translateY(-50%);
  color: #a0aec0;
}
.search-input {
  width: 100%;
  padding: 0.7rem 1rem 0.7rem 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: var(--border-radius);
  transition: var(--transition);
}
.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px var(--primary-light);
}
.filter-select, .sort-select {
  padding: 0.7rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: var(--border-radius);
  transition: var(--transition);
}
.filter-select:focus, .sort-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px var(--primary-light);
}

/* Invoice cards */
.invoices-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.invoice-card {
  display: flex;
  background: white;
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--box-shadow);
  transition: var(--transition);
  position: relative;
  border-left: 2px solid transparent;
  cursor: pointer; /* so user sees it’s clickable */
}
/* Status-specific borders */
.invoice-card.status-sent {
  border-left: 2px solid var(--secondary-secondary-200, #FFB22E);
  background: var(--neutral-neutral-050, #F1F3F5);
}
.invoice-card.status-paid {
  border-left: 2px solid var(--primary-primary-100, #92BFFF);
  background: var(--neutral-neutral-050, #F1F3F5);
}
.invoice-card.status-draft {
  border-left: 2px solid #94a3b8;
}
.invoice-card.status-overdue {
  border-left: 1px solid var(--error-error-200, #FF382E);
  background: var(--neutral-neutral-050, #F1F3F5);
}
.invoice-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
.invoice-content {
  padding: 1.2rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.invoice-number {
  font-weight: 600;
  font-size: 1.1rem;
}
.invoice-status {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  position: relative;
  overflow: hidden;
}
.status-sent {
  background-color: #fff3e0;
  color: #f59e0b;
}
.status-paid {
  background-color: #dcfce7;
  color: var(--success);
}
.status-draft {
  background-color: #f1f5f9;
  color: #64748b;
}
.status-overdue {
  background-color: #fee2e2;
  color: var(--danger);
}
.invoice-body {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  font-size: 0.9rem;
}
.invoice-customer, .invoice-date, .invoice-amount {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
}
.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background-color: var(--light);
  color: var(--dark);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
}
.action-btn:hover {
  background-color: var(--primary);
  color: white;
  transform: scale(1.1);
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideDown {
  from { transform: translateY(-20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.invoice-list-enter-active, .invoice-list-leave-active {
  transition: all 0.5s ease;
}
.invoice-list-enter-from, .invoice-list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
.invoice-list-move {
  transition: transform 0.5s ease;
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}
.pagination-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: var(--border-radius);
  background-color: white;
  color: var(--dark);
  cursor: pointer;
  transition: var(--transition);
}
.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .dashboard-title {
    font-size: 2rem;
  }

  .typewriter-container {
    padding: 0.4rem 1rem;
  }

  .futuristic-cursor {
    height: 1.5rem;
  }
}
</style>
