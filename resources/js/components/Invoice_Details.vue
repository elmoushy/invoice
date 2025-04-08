<template>
  <div class="container">
    <div class="sections-grid">
      <div class="section invoice-details animate-in">
        <h3><span class="icon">🧾</span> Invoice Details</h3>
        <form>
          <!-- Invoice Transaction Types -->
          <div class="form-row full-width">
            <label class="form-label">Invoice Transaction Types</label>
            <div class="transaction-types-grid">
              <div
                v-for="(option, index) in transactionTypes"
                :key="index"
                class="transaction-type-item"
              >
                <label class="toggle-switch">
                  <input
                    type="checkbox"
                    v-model="option.selected"
                    :disabled="isRestrictedInvoiceType && restrictedTransactionTypes.includes(option.label)"
                    @change="updateInvoiceTransactionTypeCode"
                  />
                  <span class="toggle-slider"></span>
                  <span class="toggle-label">{{ option.label }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="invoice-details-grid">
            <!-- Row 1: Issue date & Invoice type -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Invoice Issue Date</label>
                <input
                  type="date"
                  v-model="invoiceData.invoice_issue_date"
                  class="form-control"
                />
              </div>
              <div class="form-row">
                <label class="form-label">Invoice Type Code</label>
                <select
                  v-model="invoiceData.invoice_type_code"
                  class="form-select"
                  @change="updateBusinessProcessType"
                >
                  <option value="" disabled>Select Invoice Type</option>
                  <option value="380">380 - Standard Invoice</option>
                  <option value="381">381 - Credit Note</option>
                  <option value="384">384 - Corrected Invoice</option>
                  <option value="386">386 - Prepayment Invoice</option>
                  <option value="396">396 - Factored Invoice</option>
                  <option value="Out of scope of tax">Out of scope of tax</option>
                </select>
              </div>
            </div>

            <!-- Credit Note fields if invoice_type_code === '381' -->
            <div class="form-row-group" v-if="invoiceData.invoice_type_code === '381'">
              <div class="form-row">
                <label class="form-label">Select Invoice to Credit Note</label>
                <select
                  class="form-select"
                  v-model="invoiceData.creditNoteRefInvoice"
                  @change="
                    invoiceData.creditNoteRefInvoice_number =
                      lastInvoices.find(i => i.invoice_id === invoiceData.creditNoteRefInvoice)?.invoice_number;
                    onSelectCreditNoteRefInvoice();
                  "
                >
                  <option value="" disabled>Select Invoice</option>
                  <option
                    v-for="item in lastInvoices"
                    :key="item.invoice_id"
                    :value="item.invoice_id"
                  >
                    {{ item.invoice_number }} (ID: {{ item.invoice_id }})
                  </option>
                </select>
              </div>

              <div class="form-row">
                <label class="form-label">test Invoice to Credit Note</label>
                <input
                  type="text"
                  v-model="invoiceData.creditNoteRefInvoice"
                  class="form-control"
                  placeholder="Enter reason for credit note"
                  @change="onSelectCreditNoteRefInvoice"
                />
              </div>

              <div class="form-row">
                <label class="form-label">Correction Method</label>
                <select v-model="invoiceData.correction_method" class="form-select">
                  <option value="" disabled>Select Correction Method</option>
                  <option value="Full">Full</option>
                  <option value="Partial">Partial</option>
                </select>
              </div>
              <div class="form-row">
                <label class="form-label">
                  Reason for Credit Note <span class="required-marker">*</span>
                </label>
                <input
                  type="text"
                  v-model="invoiceData.reason_for_credit_note"
                  class="form-control"
                  placeholder="Enter reason for credit note"
                  required
                />
              </div>
            </div>

            <!-- Row 2: Currency fields -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Invoice Currency Code</label>
                <select
                  v-model="invoiceData.invoice_currency_code"
                  class="form-select"
                  @change="fetchExchangeRate"
                >
                  <option value="" disabled>Select Currency</option>
                  <option v-for="currency in currencies" :key="currency" :value="currency">
                    {{ currency }}
                  </option>
                </select>
              </div>
              <transition name="slide-fade">
                <div
                  class="form-row"
                  v-if="
                    invoiceData.invoice_currency_code &&
                    invoiceData.invoice_currency_code !== 'AED'
                  "
                >
                  <label class="form-label">Tax accounting currency</label>
                  <div class="readonly-field">
                    <input
                      type="text"
                      value="AED"
                      class="form-control readonly"
                      readonly
                      @input="invoiceData.payment_currency_code = 'AED'"
                    />
                    <span class="readonly-badge">Required by law</span>
                  </div>
                  <input type="hidden" v-model="invoiceData.payment_currency_code" />
                </div>
              </transition>
            </div>

            <!-- Currency Exchange Rate -->
            <transition name="slide-fade">
              <div
                class="form-row-group"
                v-if="
                  invoiceData.invoice_currency_code &&
                  invoiceData.invoice_currency_code !== 'AED'
                "
              >
                <div class="form-row full-width">
                  <label class="form-label">Currency Exchange Rate</label>
                  <div class="exchange-rate-info">
                    <span class="exchange-rate-formula">
                      3.6 {{ invoiceData.exchange_rate }} AED =
                      1 {{ invoiceData.invoice_currency_code }}
                    </span>
                    <span
                      class="exchange-rate-badge"
                      :class="{'is-loading': isLoadingRate}"
                    >
                      {{ isLoadingRate ? 'Updating...' : (' Last updated: ' + lastUpdated) }}
                    </span>
                  </div>
                </div>
              </div>
            </transition>

            <!-- Row 3: Specification + Business Process -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Specification Identifier</label>
                <div class="readonly-field">
                  <input
                    type="text"
                    class="form-control readonly"
                    readonly
                    :value="invoiceData.specification_identifier"
                  />
                  <span class="readonly-badge">Fixed value</span>
                  <input
                    type="hidden"
                    v-model="invoiceData.specification_identifier"
                    value="urn:peppol:printbilling-1@ae-1"
                  />
                </div>
              </div>
              <div class="form-row">
                <label class="form-label">Business Process Type</label>
                <div class="readonly-field">
                  <input
                    type="text"
                    v-model="invoiceData.business_process_type"
                    class="form-control readonly"
                    readonly
                    placeholder="Will be set based on invoice type"
                  />
                  <span
                    class="readonly-badge"
                    v-if="invoiceData.invoice_type_code"
                  >Auto-generated</span>
                </div>
              </div>
            </div>

            <!-- Summary Invoice date range fields -->
            <transition name="slide-fade">
              <div class="form-row-group" v-if="transactionTypes[3].selected">
                <div class="form-row">
                  <label class="form-label">Summary Invoice Start Date</label>
                  <input
                    type="date"
                    v-model="invoiceData.summary_invoice_start_date"
                    class="form-control"
                  />
                </div>
                <div class="form-row">
                  <label class="form-label">Summary Invoice End Date</label>
                  <input
                    type="date"
                    v-model="invoiceData.summary_invoice_end_date"
                    class="form-control"
                  />
                </div>
              </div>
            </transition>

            <!-- Continuous Supply Fields -->
            <transition name="slide-fade">
              <div class="form-row-group" v-if="transactionTypes[4].selected">
                <div class="form-row">
                  <label class="form-label">Contract Reference</label>
                  <input
                    type="text"
                    v-model="invoiceData.contract_reference"
                    class="form-control"
                    placeholder="Optional"
                  />
                  <small class="form-text">Optional field if needed</small>
                </div>
                <div class="form-row">
                  <label class="form-label">Contract Value</label>
                  <input
                    type="number"
                    v-model="invoiceData.contract_value"
                    class="form-control"
                    placeholder="Optional"
                    step="0.01"
                  />
                  <small class="form-text">Optional field if needed</small>
                </div>
              </div>
            </transition>

            <transition name="slide-fade">
              <div class="form-row-group" v-if="transactionTypes[4].selected">
                <div class="form-row">
                  <label class="form-label">Frequency of Billing</label>
                  <select
                    v-model="invoiceData.billing_frequency"
                    class="form-select"
                  >
                    <option value="" disabled>Select Frequency</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="others">Others</option>
                  </select>
                </div>
                <div class="form-row">
                  <label class="form-label">
                    Invoice Note
                    <span
                      v-if="invoiceData.billing_frequency === 'others'"
                      class="required-marker"
                    >
                      *
                    </span>
                  </label>
                  <input
                    type="text"
                    v-model="invoiceData.invoice_note"
                    class="form-control"
                    :placeholder="
                      invoiceData.billing_frequency === 'others'
                        ? 'Required'
                        : 'Optional'
                    "
                    :required="invoiceData.billing_frequency === 'others'"
                  />
                  <small
                    class="form-text"
                    :class="{ 'text-required': invoiceData.billing_frequency === 'others' }"
                  >
                    {{ invoiceData.billing_frequency === 'others' ? 'Required field' : 'Optional field' }}
                  </small>
                </div>
              </div>
            </transition>

            <!-- Free Trade Zone Fields -->
            <transition name="slide-fade">
              <div class="form-row-group" v-if="transactionTypes[0].selected">
                <div class="form-row full-width">
                  <label class="form-label">
                    Beneficiary ID <span class="required-marker">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="invoiceData.beneficiary_id"
                    class="form-control"
                    placeholder="Enter Beneficiary ID"
                    required
                  />
                  <small class="form-text text-required"
                    >Required for Free Trade Zone transactions</small
                  >
                </div>
              </div>
            </transition>
          </div>
        </form>
      </div>

      <!-- E-commerce Fields -->
      <div class="section transaction-e-commerce animate-in" v-if="transactionTypes[6].selected">
        <h3><span class="icon">🧾</span> e-commerce</h3>
        <form>
          <div class="invoice-details-grid">
            <!-- Row 1: Address line1 + toggle -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Deliver to address line 1</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_address_line_1"
                  class="form-control"
                />
                <div class="address-toggle" @click="showAddressLine2 = !showAddressLine2">
                  <span class="toggle-icon">{{ showAddressLine2 ? '−' : '+' }}</span>
                  <span class="toggle-text">
                    {{
                      showAddressLine2
                        ? 'Hide additional address lines'
                        : 'Add more address details'
                    }}
                  </span>
                </div>
              </div>
              <div class="form-row" v-show="showAddressLine2">
                <label class="form-label">Deliver to address line 2</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_address_line_2"
                  class="form-control"
                />
              </div>
            </div>

            <!-- Row 2: Address line3 + country code (if toggled) -->
            <div class="form-row-group" v-show="showAddressLine2">
              <div class="form-row">
                <label class="form-label">Deliver to address line 3</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_address_line_3"
                  class="form-control"
                />
              </div>
              <div class="form-row">
                <label class="form-label">Deliver to country code</label>
                <select
                  v-model="invoiceData.deliver_to_country_code"
                  class="form-select"
                >
                  <option value="" disabled>Select Country</option>
                  <option
                    v-for="country in countries"
                    :key="country.code"
                    :value="country.code"
                  >
                    {{ country.code }} - {{ country.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- If hide toggles, show single row with country code -->
            <div class="form-row-group" v-show="!showAddressLine2">
              <div class="form-row full-width">
                <label class="form-label">Deliver to country code</label>
                <select
                  v-model="invoiceData.deliver_to_country_code"
                  class="form-select"
                >
                  <option value="" disabled>Select Country</option>
                  <option
                    v-for="country in countries"
                    :key="country.code"
                    :value="country.code"
                  >
                    {{ country.code }} - {{ country.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Row 3 -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Deliver to country subdivision</label>
                <select
                  v-model="invoiceData.deliver_to_country_subdivision"
                  class="form-select"
                >
                  <option value="" disabled>Select Subdivision</option>
                  <option
                    v-for="region in selectedSubdivisions"
                    :key="region.code"
                    :value="region.code"
                  >
                    {{ region.code }} - {{ region.name }}
                  </option>
                </select>
              </div>
              <div class="form-row">
                <label class="form-label">Deliver to post code</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_post_code"
                  class="form-control"
                />
              </div>
            </div>

            <!-- Row 4 -->
            <div class="form-row-group">
              <div class="form-row">
                <label class="form-label">Deliver to city</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_city"
                  class="form-control"
                />
              </div>
              <div class="form-row">
                <label class="form-label">Deliver to party name</label>
                <input
                  type="text"
                  v-model="invoiceData.deliver_to_party_name"
                  class="form-control"
                />
              </div>
            </div>

            <!-- Row 6: Actual delivery date -->
            <div class="form-row-group">
              <div class="form-row full-width">
                <label class="form-label">Actual delivery date</label>
                <input
                  type="date"
                  v-model="invoiceData.actual_delivery_date"
                  class="form-control"
                />
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import axios from 'axios'
import { useInvoiceStore } from '../invoice'
import {
  emirates,
  saudiRegions,
  usStates,
  ukSubdivisions,
  franceRegions,
  germanStates,
  indianSubdivisions,
  chinaSubdivisions,
  japanPrefectures,
  countries
} from './subdivisions.js'

export default {
  name: 'InvoiceDetails',

  data() {
    return {
      isEditMode: false,
      currentInvoiceId: null,
      transactionTypes: [
        { label: 'Free Trade Zone', selected: false },
        { label: 'Deemed Supply', selected: false },
        { label: 'Margin Scheme', selected: false },
        { label: 'Summary Invoice', selected: false },
        { label: 'Continuous Supply', selected: false },
        { label: 'Disclosed Agent Billing', selected: false },
        { label: 'Supply through E-commerce', selected: false },
        { label: 'Exports', selected: false }
      ],
      showAddressLine2: false,
      showAddressLine3: false
    }
  },

  watch: {
    // Sync local toggles with store code whenever store code changes
    'invoiceData.invoice_transaction_type_code': {
      immediate: true,
      handler(newCode) {
        this.loadSelectedTransactionTypes()
      }
    }
  },

  setup() {
    const invoiceStore = useInvoiceStore()
    const { invoiceData } = storeToRefs(invoiceStore)

    // For currency exchange rate
    const isLoadingRate = ref(false)
    const lastUpdated = ref('')
    const currencies = ['AED', 'USD', 'EUR']

    // Last invoices for credit note
    const lastInvoices = ref([])

    // Fetch the list of "last invoices" from backend
    const fetchLastInvoices = async () => {
      console.log('Fetching last invoices from /api/listinvoice/cases...')
      try {
        const response = await axios.get('/api/listinvoice/cases')
        console.log('Response from /api/listinvoice/cases:', response.data)
        lastInvoices.value = response.data?.data || []
      } catch (err) {
        console.error('Error fetching last invoices:', err)
      }
    }

    // Called whenever user selects an invoice in the credit note dropdown
    const onSelectCreditNoteRefInvoice = async () => {
      if (!invoiceData.value.creditNoteRefInvoice) return
      console.log('User picked invoice to credit:', invoiceData.value.creditNoteRefInvoice)
      try {
        const resp = await axios.get(`/api/invoice/${invoiceData.value.creditNoteRefInvoice}`)
        console.log('Fetched old invoice data:', resp.data)
        if (resp.data?.data) {
          patchInvoiceDataFromExisting(resp.data.data)
        }
      } catch (err) {
        console.error('Error loading referenced invoice:', err)
      }
    }

    // Merge old invoice data into current invoice store
    const patchInvoiceDataFromExisting = (oldData) => {
      const { invoice_type_code, lines, payments, buyer, seller, ...rest } = oldData
      
      const preservedFields = {
        creditNoteRefInvoice: invoiceData.value.creditNoteRefInvoice,
        creditNoteRefInvoice_number: invoiceData.value.creditNoteRefInvoice_number
      }
      
      // Merge
      invoiceData.value = { ...invoiceData.value, ...rest, ...preservedFields }
      // Overwrite lines, payments
      invoiceData.value.invoice_lines = lines || []
      invoiceData.value.payment_details = payments || []
      // If buyer_id is missing, use nested buyer
      if (!invoiceData.value.buyer_id && buyer && buyer.buyer_id) {
        invoiceData.value.buyer_id = buyer.buyer_id
      }
      // Similarly for seller_id
      if (!invoiceData.value.seller_id && seller && seller.seller_id) {
        invoiceData.value.seller_id = seller.seller_id
      }
      // If we have top-level payment from the first payment detail
      if (payments && payments.length) {
        const firstPayment = payments[0]
        let typeCode = firstPayment.payment_means_type_code
        if (typeCode === 'cash') {
          typeCode = '97'
        }
        invoiceData.value.payment_means_type_code = typeCode || ''
        invoiceData.value.payment_date = firstPayment.payment_date || ''
        invoiceData.value.payment_account_identifier = firstPayment.payment_account_identifier || ''
        invoiceData.value.payment_account_name = firstPayment.payment_account_name || ''
        invoiceData.value.paid_amount = firstPayment.paid_amount || ''
        invoiceData.value.rounding_amount = firstPayment.rounding_amount || ''
        invoiceData.value.payment_card_primary_account_number =
          firstPayment.payment_card_primary_account_number || ''
        invoiceData.value.expiry_date = firstPayment.expiry_date || ''
        invoiceData.value.cvv = ''
      }
      // Force credit note business process
      invoiceData.value.business_process_type = 'urn:peppol:bis:creditnote-1'
    }

    // Watch the invoice_type_code to call fetchLastInvoices if it becomes '381'
    watch(
      () => invoiceData.value.invoice_type_code,
      (newVal, oldVal) => {
        console.log('invoice_type_code changed from', oldVal, 'to', newVal)
        if (newVal === '381') {
          fetchLastInvoices()
        } else {
          // Stop clearing creditNoteRefInvoice so it persists
          lastInvoices.value = []
        }
      }
    )

    // Are we restricted?
    const isRestrictedInvoiceType = computed(() => {
      return (
        invoiceData.value.invoice_type_code === '381' ||
        invoiceData.value.invoice_type_code === 'Out of scope of tax'
      )
    })

    const restrictedTransactionTypes = ['Deemed Supply', 'Margin Scheme', 'Summary Invoice']

    // Called if the user changes the currency
    const fetchExchangeRate = async () => {
      if (!invoiceData.value.invoice_currency_code) return
      try {
        isLoadingRate.value = true
        const response = await axios.get('/api/exchange-rate', {
          params: { currency: invoiceData.value.invoice_currency_code }
        })
        invoiceStore.invoiceData.exchange_rate = response.data.rate
        lastUpdated.value = new Date().toLocaleDateString()
      } catch (error) {
        console.error('Error fetching exchange rate:', error)
      } finally {
        isLoadingRate.value = false
      }
    }

    // Return subdivisions based on deliver_to_country_code
    const selectedSubdivisions = computed(() => {
      switch (invoiceData.value.deliver_to_country_code) {
        case 'AE': return emirates
        case 'SA': return saudiRegions
        case 'US': return usStates
        case 'GB': return ukSubdivisions
        case 'FR': return franceRegions
        case 'DE': return germanStates
        case 'IN': return indianSubdivisions
        case 'CN': return chinaSubdivisions
        case 'JP': return japanPrefectures
        default: return []
      }
    })

    // On mount, if invoice_type_code is already '381', fetch last invoices
    onMounted(() => {
      if (invoiceData.value.invoice_type_code === '381') {
        fetchLastInvoices()
      }
    })

    return {
      invoiceData,
      invoiceStore, // Return invoiceStore so it's available in component methods
      lastInvoices,
      isLoadingRate,
      lastUpdated,
      currencies,
      countries,
      isRestrictedInvoiceType,
      restrictedTransactionTypes,
      fetchExchangeRate,
      selectedSubdivisions,
      onSelectCreditNoteRefInvoice
    }
  },

  created() {
    this.currentInvoiceId = this.$route.params.id || null
    this.isEditMode = !!this.currentInvoiceId

    // Ensure specification_identifier
    if (!this.invoiceData.specification_identifier) {
      this.invoiceData.specification_identifier = 'urn:peppol:printbilling-1@ae-1'
    }
  },

  mounted() {
    this.loadSelectedTransactionTypes()
  },

  methods: {
    async loadInvoiceForEdit(id) {
      try {
        const resp = await axios.get(`/api/invoice/${id}`)
        this.$patchInvoiceData(resp.data.data)
      } catch (err) {
        console.error('Error loading invoice:', err)
      }
    },

    $patchInvoiceData(newData) {
      this.invoiceStore.invoiceData = newData
      this.loadSelectedTransactionTypes()
      if (!this.invoiceData.specification_identifier) {
        this.invoiceData.specification_identifier = 'urn:peppol:printbilling-1@ae-1'
      }
    },

    updateBusinessProcessType() {
      switch (this.invoiceData.invoice_type_code) {
        case '380':
          this.invoiceData.business_process_type = 'urn:peppol:bis:billing-1'
          break
        case '381':
          this.invoiceData.business_process_type = 'urn:peppol:bis:creditnote-1'
          break
        case '384':
          this.invoiceData.business_process_type = 'urn:peppol:bis:correctedinvoice-1'
          break
        case '386':
          this.invoiceData.business_process_type = 'urn:peppol:bis:prepaymentinvoice-1'
          break
        case '396':
          this.invoiceData.business_process_type = 'urn:peppol:bis:factoredinvoice-1'
          break
        default:
          this.invoiceData.business_process_type = ''
      }

      // If restricted, unselect disallowed toggles
      if (
        this.invoiceData.invoice_type_code === '381' ||
        this.invoiceData.invoice_type_code === 'Out of scope of tax'
      ) {
        this.transactionTypes.forEach((option) => {
          if (['Deemed Supply', 'Margin Scheme', 'Summary Invoice'].includes(option.label)) {
            option.selected = false
          }
        })
        this.updateInvoiceTransactionTypeCode()
      }
    },

    updateInvoiceTransactionTypeCode() {
      const code = this.transactionTypes.map((option) => (option.selected ? '1' : '0')).join('')
      this.invoiceData.invoice_transaction_type_code = code
      // Update the store using the invoiceStore reference
      if (this.invoiceStore) {
        this.invoiceStore.invoiceData.transactionTypes = JSON.parse(JSON.stringify(this.transactionTypes))
      }
    },

    loadSelectedTransactionTypes() {
      const { invoice_transaction_type_code } = this.invoiceData
      if (!invoice_transaction_type_code || invoice_transaction_type_code.length !== 8) {
        this.transactionTypes.forEach(opt => { opt.selected = false })
        return
      }
      for (let i = 0; i < 8; i++) {
        this.transactionTypes[i].selected = (invoice_transaction_type_code[i] === '1')
      }
    }
  }
}
</script>

<style scoped>
.container {
  max-width: 100%;
  padding: 20px;
  font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
  color: #374151;
  background: #f3f4f6;
  border-radius: 12px;
}

.address-toggle {
  display: flex;
  align-items: center;
  margin-top: 8px;
  cursor: pointer;
  color: #4f46e5;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
}
.address-toggle:hover {
  color: #6366f1;
}
.toggle-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  margin-right: 8px;
  background-color: #eff6ff;
  border-radius: 50%;
  font-size: 16px;
  line-height: 1;
  transition: all 0.2s ease;
}
.address-toggle:hover .toggle-icon {
  background-color: #dbeafe;
  transform: scale(1.1);
}
.toggle-text {
  font-size: 0.85rem;
}

.address-toggle:hover .toggle-text {
  color: #4f46e5;
}
.address-toggle .toggle-icon {
  background-color: #e5e7eb;
  color: #111827;
}

.section {
  margin-bottom: 25px;
  padding: 24px;
  border-radius: 12px;
  background: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.transaction-e-commerce::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #d2e710, #cfff4a);
}
.invoice-details::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #4f46e5, #8b5cf6);
}

h3 {
  margin-bottom: 20px;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  display: flex;
  align-items: center;
}

.icon {
  margin-right: 10px;
  font-size: 1.3rem;
  display: inline-block;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.form-row-group {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.form-row {
  flex: 1;
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
}

.full-width {
  width: 100%;
}

.form-label {
  margin-bottom: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
}

.form-control,
.form-select {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background-color: #fff;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.form-control:focus,
.form-select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.form-control:hover,
.form-select:hover {
  border-color: #9ca3af;
}

.animate-in {
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
  transform: translateY(20px);
}

.transaction-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
}

.transaction-type-item {
  background-color: #f9fafb;
  border-radius: 10px;
  padding: 12px;
  transition: all 0.2s ease;
}

.transaction-type-item:hover {
  background-color: #f3f4f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.toggle-switch {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

.toggle-slider {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  background-color: #e5e7eb;
  border-radius: 24px;
  margin-right: 12px;
  transition: 0.3s ease-in-out;
}

.toggle-slider:before {
  content: "";
  position: absolute;
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: 0.3s ease-in-out;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
  background-color: #4f46e5;
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

.toggle-label {
  font-size: 0.95rem;
  font-weight: 500;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
