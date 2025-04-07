<template>
  <div class="page-wrapper">
    <div class="main-layout">
      <TheNav />
      <div class="content-wrapper">
        <div class="invoice-card">
          <!-- Invoice Header -->
          <div class="card-header">
            <div class="header-content">
              <i class="fas fa-file-invoice header-icon"></i>
              <h1 v-if="isEditMode">Edit Invoice #{{ currentInvoiceId }}</h1>
              <h1 v-else>Create New Invoice</h1>
            </div>
          </div>

          <!-- Modern Action Bar -->
          <div class="action-bar">
            <div class="dropdown-container">
              <div class="select-wrapper">
                <!--
                  <select v-model="selectedCase" @change="handleCaseChange" class="modern-select">
                    <option v-for="n in 15" :key="n" :value="n">Case {{ n }}</option>
                  </select>
                  <div class="select-arrow">
                    <i class="fas fa-chevron-down"></i>
                  </div>
                -->
              </div>
            </div>
            <div class="action-buttons">
              <div v-if="isLastStep" class="draft-save" @click="handleSubmit('draft')">
                <span>{{ isEditMode ? 'UPDATE AS DRAFT' : 'SAVE AS A DRAFT' }}</span>
              </div>
              <button class="next-button" @click="handleNextOrSubmit">
                <span v-if="!isLastStep">NEXT</span>
                <span v-else>{{ isEditMode ? 'UPDATE' : 'SAVE' }}</span>
              </button>
            </div>
          </div>

          <!-- Render the child route once data is loaded -->
          <template v-if="!loading">
            <router-view></router-view>
          </template>
          <!-- Loading Spinner -->
          <div v-else style="padding: 2rem;">
            <h2>Loading invoice data...</h2>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TheNav from './TheNav.vue'
import { useInvoiceStore } from '../invoice'
import Swal from 'sweetalert2'
import axios from 'axios'

export default {
  name: 'CreateInvoiceLayout',
  components: { TheNav },

  data() {
    return {
      invoiceStore: null,
      isEditMode: false,
      currentInvoiceId: null,
      loading: false,
      selectedCase: 1 // Default to Case 1
    }
  },

  computed: {
    // Determine if we are on the last step in the wizard
    isLastStep() {
      const menuItems = this.getWizardMenuItems()
      const currentIndex = menuItems.findIndex(item =>
        this.$route.path.includes(item.route)
      )
      return currentIndex === menuItems.length - 1
    },
    invoiceData() {
      return this.invoiceStore.invoiceData
    }
  },

  async created() {
    this.invoiceStore = useInvoiceStore()
    this.currentInvoiceId = this.$route.params.id || null
    this.isEditMode = !!this.currentInvoiceId

    // In edit mode, load the invoice data once
    if (this.isEditMode) {
      this.loading = true
      try {
        await this.invoiceStore.loadInvoiceForEdit(this.currentInvoiceId)
      } catch (error) {
        console.error('Error loading invoice for edit:', error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Could not load the invoice data.'
        }).then(() => {
          this.$router.push({ name: 'AllInvoices' })
        })
      } finally {
        this.loading = false
      }
    }
  },

  watch: {
    // When the selected case changes (and not in edit mode),
    // navigate to the first step of the new case.
    selectedCase(newVal) {
      if (!this.isEditMode) {
        const menuItems = this.getWizardMenuItems()
        this.$router.push(menuItems[0].path)
      }
    }
  },

  beforeRouteUpdate: async function(to, from, next) {
    // If invoice ID changes in edit mode, reset and load new invoice data
    if (this.isEditMode && to.params.id && to.params.id !== this.currentInvoiceId) {
      this.loading = true
      try {
        this.invoiceStore.resetInvoiceData()
        this.currentInvoiceId = to.params.id
        await this.invoiceStore.loadInvoiceForEdit(to.params.id)
        this.loading = false
        next()
      } catch (err) {
        console.error('Error re-loading invoice in beforeRouteUpdate:', err)
        this.loading = false
        next({ name: 'AllInvoices' })
      }
    } else {
      next()
    }
  },

  methods: {
    // Build wizard menu items dynamically based on the selected case
    getWizardMenuItems() {
      let basePath
      if (this.isEditMode) {
        basePath = `/invoice/${this.currentInvoiceId}/edit`
      } else {
        // For Case 1 use the original path; for others use a modified base path
        basePath = this.selectedCase === 1 ? '/create-invoice' : `/create-invoice${this.selectedCase}`
      }
      // For cases other than 1, add a prefix for the child routes (e.g., case2_invoice-details)
      const prefix = this.selectedCase === 1 ? '' : `case${this.selectedCase}_`
      return [
        { text: 'Invoice Details', route: 'invoice-details', path: `${basePath}/${prefix}invoice-details` },
        { text: 'Seller Details',  route: 'seller-details',  path: `${basePath}/${prefix}seller-details` },
        { text: 'Buyer Details',   route: 'buyer-details',   path: `${basePath}/${prefix}buyer-details` },
        { text: 'Document Totals', route: 'document-totals', path: `${basePath}/${prefix}document-totals` }
      ]
    },

    handleCaseChange() {
      // Optional: additional logic when case is changed
      console.log('Selected case:', this.selectedCase)
    },

    handleNextOrSubmit() {
      if (!this.isLastStep) {
        this.nextStep()
      } else {
        this.handleSubmit('final')
      }
    },

    nextStep() {
      const menuItems = this.getWizardMenuItems()
      const currentIndex = menuItems.findIndex(item =>
        this.$route.path.includes(item.route)
      )
      if (currentIndex < menuItems.length - 1) {
        const nextItem = menuItems[currentIndex + 1]
        this.$router.push(nextItem.path)
      }
    },

    handleSubmit(mode) {
      // 1) Clone the invoice data
      const payload = JSON.parse(JSON.stringify(this.invoiceData))

      // 2) Ensure invoice_lines is an array
      if (!Array.isArray(payload.invoice_lines)) {
        payload.invoice_lines = []
      }

      // 3) Build tax_breakdowns from invoice_lines
      const taxMap = {}
      payload.invoice_lines.forEach(line => {
        const code = line.invoiced_item_tax_category_code
        if (!code) return
        if (!taxMap[code]) {
          taxMap[code] = {
            tax_category_code: code,
            tax_category_rate: parseFloat(line.invoiced_item_tax_rate) || 0,
            taxable_amount: 0
          }
        }
        taxMap[code].taxable_amount += parseFloat(line.invoice_line_net_amount) || 0
      })
      payload.tax_breakdowns = Object.values(taxMap).map(item => ({
        tax_category_code: item.tax_category_code,
        tax_category_rate: item.tax_category_rate,
        taxable_amount: item.taxable_amount,
        tax_amount: (item.taxable_amount * item.tax_category_rate) / 100
      }))

      // 4) Compute totals
      const totalLineNet = payload.invoice_lines.reduce((sum, line) => {
        return sum + (parseFloat(line.invoice_line_net_amount) || 0)
      }, 0)
      const totalTax = payload.tax_breakdowns.reduce((sum, tb) => {
        return sum + (parseFloat(tb.tax_amount) || 0)
      }, 0)
      payload.invoice_total_line_net_amount = totalLineNet
      payload.invoice_total_tax_amount = totalTax
      payload.invoice_total_with_tax = totalLineNet + totalTax

      const paid = parseFloat(payload.paid_amount) || 0
      const rounding = parseFloat(payload.rounding_amount) || 0
      payload.invoice_due_for_payment = payload.invoice_total_with_tax - paid + rounding

      // 5) Build payment_details if missing
      if (
        !payload.payment_details ||
        !Array.isArray(payload.payment_details) ||
        payload.payment_details.length === 0
      ) {
        payload.payment_details = [
          {
            payment_means_type_code: payload.payment_means_type_code || '',
            payment_account_identifier: payload.payment_account_identifier || '',
            paid_amount: parseFloat(payload.paid_amount) || 0,
            rounding_amount: parseFloat(payload.rounding_amount) || 0,
            payment_date: payload.payment_date || ''
          }
        ]
      }

      // 6) Remove top-level payment fields
      delete payload.payment_means_type_code
      delete payload.payment_account_identifier
      delete payload.paid_amount
      delete payload.rounding_amount
      delete payload.payment_date

      // Must have at least one tax category
      if (!payload.tax_breakdowns || payload.tax_breakdowns.length === 0) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Please add at least one invoice line with a valid tax category.'
        })
        return
      }

      // 7) Create vs. update logic
      if (this.isEditMode) {
        axios
          .put(`/api/invoice/${this.currentInvoiceId}`, payload)
          .then(() => {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Invoice updated successfully!'
            }).then(() => {
              this.$router.push({
                name: 'InvoicePreview',
                params: { id: this.currentInvoiceId }
              })
            })
          })
          .catch(error => {
            let errorMessage = 'An error occurred while updating the invoice.'
            if (error.response?.data?.message) {
              errorMessage = error.response.data.message
            }
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMessage
            })
          })
      } else {
        axios
          .post('/api/invoice', payload)
          .then(response => {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Invoice created successfully!'
            }).then(() => {
              const newInvoiceId = response.data.data.invoice_id
              this.$router.push({ name: 'InvoicePreview', params: { id: newInvoiceId } })
            })
          })
          .catch(error => {
            let errorMessage = 'An error occurred while saving the invoice.'
            if (error.response?.data?.message) {
              errorMessage = error.response.data.message
            }
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorMessage
            })
          })
      }
    }
  }
}
</script>

<style scoped>
.page-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f5f7fa;
}
.main-layout {
  display: flex;
  flex: 1;
}
.content-wrapper {
  padding: 20px;
  width: 100%;
}
.invoice-card {
  background-color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  padding-bottom: 20px;
}
.card-header {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  background-color: #f8f9fc;
}
.header-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}
.header-icon {
  font-size: 30px;
  color: #333;
  margin-bottom: 10px;
}
.card-header h1 {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}
.action-bar {
  background-color: #2979ff;
  display: flex;
  border-radius: 15px;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
}
.dropdown-container select {
  padding: 6px 10px;
  font-size: 14px;
}
.draft-save {
  color: white;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
}
.next-button {
  background-color: white;
  color: #2979ff;
  border: none;
  padding: 8px 20px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}
.next-button:hover {
  background-color: #f0f0f0;
}
.action-bar {
  background: linear-gradient(135deg, #2979ff, #1565c0);
  display: flex;
  border-radius: 12px;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  margin: 0 16px;
  box-shadow: 0 4px 12px rgba(41, 121, 255, 0.2);
}
.select-wrapper {
  position: relative;
  width: 160px;
}
.modern-select {
  appearance: none;
  width: 100%;
  padding: 10px 16px;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}
.modern-select:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
}
.modern-select:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}
.modern-select option {
  background-color: #2979ff;
  color: white;
}
.select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  pointer-events: none;
  font-size: 12px;
}
.action-buttons {
  display: flex;
  align-items: center;
  gap: 20px;
}
.draft-save {
  color: white;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  opacity: 0.9;
  transition: opacity 0.2s;
  padding: 8px 16px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}
.draft-save:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.1);
}
.next-button {
  background-color: white;
  color: #2979ff;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  letter-spacing: 0.5px;
}
.next-button:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}
</style>
