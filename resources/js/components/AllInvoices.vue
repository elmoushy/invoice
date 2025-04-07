<template>
  <div class="invoices-dashboard">
    <h2 class="dashboard-title">Invoices</h2>
    
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
      currentPage: 1
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
    }
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
}

.invoices-dashboard {
  font-family: 'Poppins', sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  color: var(--dark);
}

.dashboard-title {
  font-size: 1.8rem;
  font-weight: 600;
  margin-bottom: 2rem;
  color: var(--dark);
  animation: fadeIn 0.8s ease;
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
</style>
