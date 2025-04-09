<template>
  <div class="container">
    <!-- Invoice Lines Section -->
    <div class="section invoice-line animate-in" style="animation-delay: 0.4s">
      <h3>
        <span class="icon">🧾</span> Invoice Lines
      </h3>
      <!-- Button to add new Invoice Line -->
      <button class="add-line-button" @click="addInvoiceLine" :disabled="invoiceData.invoice_type_code === '381'">
        + Add Invoice Line
      </button>

      <!-- Render multiple invoice lines -->
      <div
        v-for="(line, index) in invoiceData.invoice_lines"
        :key="index"
        class="invoice-line-grid"
      >
        <!-- Identifier -->
        <div class="form-row">
          <label class="form-label">Invoice Line Identifier</label>
          <input
            type="text"
            class="form-control"
            v-model="line.invoice_line_identifier"
            readonly
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Invoiced Quantity -->
        <div class="form-row">
          <label class="form-label">Invoiced Quantity</label>
          <div class="quantity-control" v-if="invoiceData.invoice_type_code === '381'">
            <input
              type="text"
              class="form-control"
              v-model="line.invoiced_quantity"
              placeholder="Invoiced Quantity"
              @input="validateInvoicedQuantity(line, $event)"
            />
            <div class="quantity-arrows">
              <button 
                type="button" 
                class="arrow-btn up"
                @click="incrementQuantity(line)"
                :disabled="isAtMaxQuantity(line)"
              >▲</button>
              <button 
                type="button" 
                class="arrow-btn down"
                @click="decrementQuantity(line)"
                :disabled="isAtMinQuantity(line)"
              >▼</button>
            </div>
          </div>
          <input
            v-else
            type="text"
            class="form-control"
            v-model="line.invoiced_quantity"
            placeholder="Invoiced Quantity"
            @input="validateInvoicedQuantity(line, $event)"
          />
        </div>

        <!-- Unit Measure Code -->
        <div class="form-row">
          <label class="form-label">Unit Measure Code</label>
          <select class="form-select" v-model="line.invoiced_quantity_unit_code" :disabled="invoiceData.invoice_type_code === '381'">
            <option value="" disabled>Select Unit</option>
            <option v-for="unit in units" :key="unit" :value="unit">
              {{ unit }}
            </option>
          </select>
        </div>

        <!-- Item Gross Price -->
        <div class="form-row">
          <label class="form-label">Item Gross Price</label>
          <div class="form-group currency-group">
            <input
              type="text"
              class="form-control"
              v-model="line.item_gross_price"
              placeholder="Item Gross Price"
              @input="line.item_gross_price = line.item_gross_price.replace(/[^0-9.]/g, '')"
              :disabled="invoiceData.invoice_type_code === '381'"
            />
            <span class="currency">AED</span>
          </div>
        </div>

        <!-- Discount Type -->
        <div class="form-row">
          <label class="form-label">Discount Type</label>
          <select class="form-select" v-model="line.discount_type" :disabled="invoiceData.invoice_type_code === '381'">
            <option value="" disabled>Select Discount Type</option>
            <option value="static">Static</option>
            <option value="percentage">Percentage</option>
          </select>
        </div>

        <!-- Discount Value -->
        <div class="form-row">
          <label class="form-label">Discount Value</label>
          <input
            type="text"
            class="form-control"
            v-model="line.discount_value"
            placeholder="Discount Value"
            @input="line.discount_value = line.discount_value.replace(/[^0-9.]/g, '')"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Item Net Price (read-only) -->
        <div class="form-row">
          <label class="form-label">Item Net Price</label>
          <div class="form-group currency-group">
            <input
              type="text"
              class="form-control"
              :value="line.item_net_price"
              readonly
              :disabled="invoiceData.invoice_type_code === '381'"
            />
            <span class="currency">AED</span>
          </div>
        </div>

        <!-- Invoice Line Net Amount (read-only) -->
        <div class="form-row">
          <label class="form-label">Invoice Line Net Amount</label>
          <input
            type="text"
            class="form-control"
            v-model="line.invoice_line_net_amount"
            readonly
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Item Description -->
        <div class="form-row">
          <label class="form-label">Item Description</label>
          <input
            type="text"
            class="form-control"
            v-model="line.item_description"
            placeholder="Item Description"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Item Classification -->
        <div class="form-row">
          <label class="form-label">Item Classification</label>
          <input
            type="text"
            class="form-control"
            v-model="line.item_classification"
            placeholder="Item Classification"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Item Name -->
        <div class="form-row">
          <label class="form-label">Item Name</label>
          <input
            type="text"
            class="form-control"
            v-model="line.item_name"
            placeholder="Item Name"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Item Type -->
        <div class="form-row">
          <label class="form-label">Item Type</label>
          <select class="form-select" v-model="line.item_type" :disabled="invoiceData.invoice_type_code === '381'">
            <option value="" disabled>Select Item Type</option>
            <option value="Goods">Goods</option>
            <option value="Services">Services</option>
          </select>
        </div>

        <!-- Item Price Base Quantity -->
        <div class="form-row">
          <label class="form-label">Item Price Base Quantity</label>
          <input
            type="text"
            class="form-control"
            v-model="line.item_price_base_quantity"
            placeholder="Item Price Base Quantity"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Invoiced Item Tax Rate (read-only) -->
        <div class="form-row">
          <label class="form-label">Invoiced Item Tax Rate</label>
          <input
            type="text"
            class="form-control"
            :value="line.invoiced_item_tax_rate"
            readonly
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- VAT Line Amount (read-only) -->
        <div class="form-row">
          <label class="form-label">VAT Line Amount</label>
          <input
            type="text"
            class="form-control"
            :value="line.vat_line_amount"
            readonly
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Classification Scheme Identifier (Goods) -->
        <div class="form-row" v-if="line.item_type === 'Goods'">
          <label class="form-label">Classification Scheme Identifier</label>
          <input
            type="text"
            class="form-control"
            v-model="line.classification_scheme_identifier"
            placeholder="Classification Scheme Identifier"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- SAC Scheme Identifier (Services) -->
        <div class="form-row" v-if="line.item_type === 'Services'">
          <label class="form-label">SAC Scheme Identifier</label>
          <input
            type="text"
            class="form-control"
            v-model="line.sac_scheme_identifier"
            placeholder="SAC Scheme Identifier"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Tax Category Code -->
        <div class="form-row">
          <label class="form-label">Invoiced Item Tax Category Code</label>
          <select
            class="form-select"
            v-model="line.invoiced_item_tax_category_code"
            @change="
              line.invoiced_item_tax_rate =
          line.invoiced_item_tax_category_code === 'standard_rate'
            ? 5
            : line.invoiced_item_tax_category_code === 'Reverse_Charge'
              ? 5
              : 0
            "
            :disabled="invoiceData.invoice_type_code === '381'"
          >
            <option value="" disabled>Select Tax Category</option>
            <option value="standard_rate">Standard rate (5% VAT)</option>
            <option value="Reverse_Charge">Reverse Charge (5% VAT)</option>
            <option value="zero_rated">Zero-rated (0% VAT)</option>
            <option value="exempt">Exempt (0% VAT)</option>
            <!-- Additional options when Margin Scheme is selected -->
            <option v-if="isMarginSchemeSelected" value="second_hand">
              Second hand goods (0% VAT)
            </option>
            <option v-if="isMarginSchemeSelected" value="works_of_art">
              Works of art (0% VAT)
            </option>
            <option v-if="isMarginSchemeSelected" value="collectors_items">
              Collectors items and antiques (0% VAT)
            </option>
          </select>
          <div
            v-if="line.invoiced_item_tax_category_code === 'Reverse_Charge'"
            class="reverse-charge-alert"
          >
            <i class="alert-icon">ℹ️</i>
            <span
              >Reverse Charge Mechanism applied: VAT will not be collected by
              the seller. Buyer is responsible for VAT reporting</span
            >
          </div>
        </div>

        <!-- Tax exemption reason text -->
        <div class="form-row" v-if="line.invoiced_item_tax_category_code === 'zero_rated'">
          <label class="form-label">Tax Exemption Reason Text</label>
          <input
            type="text"
            class="form-control"
            v-model="line.tax_exemption_reason"
            placeholder="Enter tax exemption reason"
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Tax Exemption Reason Code -->
        <div class="form-row" v-if="line.invoiced_item_tax_category_code === 'zero_rated'">
          <label class="form-label">Tax Exemption Reason Code</label>
          <select class="form-select" v-model="line.tax_exemption_reason_code" :disabled="invoiceData.invoice_type_code === '381'">
            <option value="" disabled>Select Exemption Reason</option>
            <option value="ZRE"
              >ZRE - Zero-Rated Export (Goods/services exported outside the UAE)</option
            >
            <option value="ZRL"
              >ZRL - Zero-Rated Local Supply (Education, healthcare, specific
              food items)</option
            >
            <option value="EXE"
              >EXE - Exempt Supply (Financial services, bare land sales, local
              passenger transport)</option
            >
            <option value="RCM"
              >RCM - Reverse Charge Mechanism (VAT paid by buyer)</option
            >
            <option value="OSR"
              >OSR - Out of Scope Revenue (Transactions outside VAT scope)</option
            >
          </select>
        </div>

        <!-- Item Standard Identifier (for Reverse_Charge) -->
        <div
          class="form-row"
          v-if="line.invoiced_item_tax_category_code === 'Reverse_Charge'"
        >
          <label class="form-label">Item Standard Identifier</label>
          <input
            type="text"
            class="form-control"
            v-model="line.Item_Standard_Identifier"
            placeholder="Item Standard Identifier"
            @input="
              line.Item_Standard_Identifier = line.Item_Standard_Identifier.replace(/[^0-9]/g, '');
            "
            :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>

        <!-- Remove line button -->
        <button class="remove-line-button" @click="removeInvoiceLine(index)">
          Remove
        </button>
      </div>
    </div>

    <!-- Tax Breakdown Section -->
    <div class="section tax-breakdown animate-in" style="animation-delay: 0.2s">
      <h3>
        <span class="icon">💰</span> Tax Breakdown
      </h3>
      <table class="tax-breakdown-table">
        <thead>
          <tr>
            <th>Tax Category Code</th>
            <th>Taxable Amount</th>
            <th>Tax Rate (%)</th>
            <th>Tax Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in computedTaxBreakdownByCategory" :key="i">
            <td>{{ row.tax_category_code }}</td>
            <td>{{ row.taxable_amount.toFixed(2) }}</td>
            <td>{{ row.tax_category_rate.toFixed(2) }}%</td>
            <td>{{ row.tax_amount.toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Document Totals Section -->
    <div class="sections-grid">
      <div class="section document-totals animate-in">
      <h3>
        <span class="icon">📄</span> Document Totals
      </h3>
      <div class="form-row">
        <label class="form-label">Invoice Total Line Net Amount</label>
        <input
        type="text"
        class="form-control"
        :value="invoice_total_line_net_amount"
        :disabled="invoiceData.invoice_type_code === '381'"
        readonly
        />
      </div>
      <div class="form-row">
        <label class="form-label">Invoice Total Tax Amount</label>
        <input
        type="text"
        class="form-control"
        :value="invoice_total_tax_amount"
        :disabled="invoiceData.invoice_type_code === '381'"
        readonly
        />
      </div>
      <div class="form-row">
        <label class="form-label">Invoice Total With Tax</label>
        <input
        type="text"
        class="form-control"
        :value="invoice_total_with_tax"
        :disabled="invoiceData.invoice_type_code === '381'"
        readonly
        />
      </div>
      <div class="form-row">
        <label class="form-label">Paid Amount</label>
        <input
        type="text"
        class="form-control"
        v-model="invoiceData.paid_amount"
        :disabled="invoiceData.invoice_type_code === '381'"
        placeholder="Enter Paid Amount"
        />
      </div>
      <div class="form-row">
        <label class="form-label">Rounding Amount</label>
        <input
        type="text"
        class="form-control"
        v-model="invoiceData.rounding_amount"
        :disabled="invoiceData.invoice_type_code === '381'"
        placeholder="Enter Rounding Amount"
        />
      </div>
      <div class="form-row">
        <label class="form-label">Invoice Due For Payment</label>
        <input
        type="text"
        class="form-control"
        :value="invoice_due_for_payment"
        :disabled="invoiceData.invoice_type_code === '381'"
        readonly
        />
      </div>
      </div>

      <!-- Payment Details Section -->
      <div class="section payment-details animate-in" style="animation-delay: 0.2s">
        <h3>
          <span class="icon">💳</span> Payment Details
        </h3>
        <div class="form-row">
          <label class="form-label">Payment Means Type Code</label>
          <select class="form-select" v-model="invoiceData.payment_means_type_code" :disabled="invoiceData.invoice_type_code === '381'">
        <option value="" disabled>Select Payment Means</option>
        <option value="1">Instrument Not Defined</option>
        <option value="10">Bank Transfer</option>
        <option value="31">Credit Card</option>
        <option value="97">Cash</option>
        <option value="ZZZ">Mutually Defined</option>
          </select>
        </div>

        <!-- If Payment Means = Credit Card -->
        <template v-if="invoiceData.payment_means_type_code === '31'">
          <div class="form-row">
        <label class="form-label">Card Number</label>
        <input
          type="text"
          class="form-control"
          v-model="invoiceData.payment_card_primary_account_number"
          placeholder="Enter Card Number"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
          <div class="form-row">
        <label class="form-label">Cardholder Name</label>
        <input
          type="text"
          class="form-control"
          v-model="invoiceData.payment_account_name"
          placeholder="Enter Cardholder Name"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
          <div class="form-row">
        <label class="form-label">Expiry Date</label>
        <input
          type="text"
          class="form-control"
          v-model="invoiceData.expiry_date"
          placeholder="MM/YY"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
          <div class="form-row">
        <label class="form-label">CVV</label>
        <input
          type="password"
          class="form-control"
          v-model="invoiceData.cvv"
          placeholder="Enter CVV"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
        </template>

        <!-- If Payment Means = Bank Transfer -->
        <template v-if="invoiceData.payment_means_type_code === '10'">
          <div class="form-row">
        <label class="form-label">Bank Account Number</label>
        <input
          type="text"
          class="form-control"
          v-model="invoiceData.payment_account_identifier"
          placeholder="Enter Bank Account Number"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
          <div class="form-row">
        <label class="form-label">Bank Name</label>
        <input
          type="text"
          class="form-control"
          v-model="invoiceData.payment_account_name"
          placeholder="Enter Bank Name"
          :disabled="invoiceData.invoice_type_code === '381'"
        />
          </div>
        </template>

        <!-- Payment Date -->
        <div class="form-row">
          <label class="form-label">Payment Date</label>
          <input
        type="date"
        class="form-control"
        v-model="invoiceData.payment_date"
        placeholder="Payment Date"
        :disabled="invoiceData.invoice_type_code === '381'"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, watchEffect, ref, watch } from 'vue'
import { useInvoiceStore } from '../invoice'
import { storeToRefs } from 'pinia'

export default {
  name: 'DocumentTotals',
  props: {
    // If true, we apply "hard logic" for discount in watchEffect
    isEditMode: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const invoiceStore = useInvoiceStore()
    const { invoiceData } = storeToRefs(invoiceStore)
    // Track original quantities for credit note validation
    const originalInvoicedQuantities = ref({})

    // Ensure invoiceData exists and has an array for invoice_lines.
    if (!invoiceData.value) {
      invoiceData.value = {}
    }
    if (!invoiceData.value.invoice_lines) {
      invoiceData.value.invoice_lines = []
    }

    const units = ['PCS', 'KG', 'L', 'BOX']

    const addInvoiceLine = () => {
      invoiceData.value.invoice_lines.push({
        invoice_line_identifier: '',
        invoiced_quantity: '',
        invoiced_quantity_unit_code: '',
        item_gross_price: 0,
        discount_type: '',
        discount_value: 0,
        item_net_price: 0,
        invoice_line_net_amount: 0,
        item_description: '',
        item_classification: '',
        item_price_base_quantity: 1,
        invoiced_item_tax_rate: '',
        vat_line_amount: 0,
        invoiced_item_tax_category_code: '',
        Item_Standard_Identifier: '',
        item_name: '',
        item_type: '',
        classification_scheme_identifier: '',
        sac_scheme_identifier: '',
        tax_exemption_reason: '',
        tax_exemption_reason_code: ''
      })
    }

    const removeInvoiceLine = (index) => {
      invoiceData.value.invoice_lines.splice(index, 1)
      // Also remove the tracked original quantity for this line
      delete originalInvoicedQuantities.value[index]
    }

    // Track when invoice lines change in credit note mode
    watch(() => invoiceData.value.invoice_lines, (newLines, oldLines) => {
      if (invoiceData.value.invoice_type_code === '381') {
        // Store original quantities if we haven't already
        newLines.forEach((line, index) => {
          if (!originalInvoicedQuantities.value[index] && line.invoiced_quantity) {
            originalInvoicedQuantities.value[index] = parseFloat(line.invoiced_quantity) || 0
          }
        })
      }
    }, { deep: true })

    // Watch for changes in credit note reference to reset tracked quantities
    watch(() => invoiceData.value.creditNoteRefInvoice, () => {
      if (invoiceData.value.invoice_type_code === '381') {
        originalInvoicedQuantities.value = {}
      }
    })

    // Validation function for invoiced quantity
    const validateInvoicedQuantity = (line, event) => {
      // Only apply validation for credit notes
      if (invoiceData.value.invoice_type_code === '381') {
        const lineIndex = invoiceData.value.invoice_lines.indexOf(line)
        const originalQty = originalInvoicedQuantities.value[lineIndex]
        
        // Clean input to ensure it's a valid number
        let value = event.target.value.replace(/[^0-9.]/g, '')
        let numValue = parseFloat(value) || 0
        
        // Ensure it's not less than 1
        if (numValue < 1) {
          numValue = 1
        }
        
        // Ensure it doesn't exceed original quantity if we have one
        if (originalQty !== undefined) {
          if (numValue > originalQty) {
            numValue = originalQty
          }
        }
        
        // Update the value
        line.invoiced_quantity = numValue.toString()
      }
    }

    // Increment quantity with validation
    const incrementQuantity = (line) => {
      if (invoiceData.value.invoice_type_code === '381') {
        const lineIndex = invoiceData.value.invoice_lines.indexOf(line)
        const originalQty = originalInvoicedQuantities.value[lineIndex]
        
        let currentValue = parseFloat(line.invoiced_quantity) || 0
        // Only increment if we're below the original quantity
        if (originalQty !== undefined && currentValue < originalQty) {
          currentValue++
          line.invoiced_quantity = currentValue.toString()
        }
      }
    }
    
    // Decrement quantity with validation
    const decrementQuantity = (line) => {
      if (invoiceData.value.invoice_type_code === '381') {
        let currentValue = parseFloat(line.invoiced_quantity) || 0
        // Only decrement if we're above 1
        if (currentValue > 1) {
          currentValue--
          line.invoiced_quantity = currentValue.toString()
        }
      }
    }
    
    // Check if at maximum allowed quantity
    const isAtMaxQuantity = (line) => {
      if (invoiceData.value.invoice_type_code === '381') {
        const lineIndex = invoiceData.value.invoice_lines.indexOf(line)
        const originalQty = originalInvoicedQuantities.value[lineIndex]
        
        if (originalQty !== undefined) {
          const currentValue = parseFloat(line.invoiced_quantity) || 0
          return currentValue >= originalQty
        }
      }
      return false
    }
    
    // Check if at minimum allowed quantity
    const isAtMinQuantity = (line) => {
      if (invoiceData.value.invoice_type_code === '381') {
        const currentValue = parseFloat(line.invoiced_quantity) || 0
        return currentValue <= 1
      }
      return false
    }

    // Recompute line amounts on data changes.
    watchEffect(() => {
      if (!invoiceData.value.invoice_lines) return

      invoiceData.value.invoice_lines.forEach((line, idx) => {
        // 1) Set invoice line identifier
        line.invoice_line_identifier = String(idx + 1)

        // 2) Gross & discount calculations
        const gross = parseFloat(line.item_gross_price) || 0
        let discountVal = parseFloat(line.discount_value) || 0

        if (props.isEditMode) {
          // If discount type is percentage, clamp between 0-100
          if (line.discount_type === 'percentage') {
            if (discountVal > 100) {
              discountVal = 100
              line.discount_value = '100'
            } else if (discountVal < 0) {
              discountVal = 0
              line.discount_value = '0'
            }
          }
          // If discount type is static, clamp between 0 and gross
          if (line.discount_type === 'static') {
            if (discountVal > gross) {
              discountVal = gross
              line.discount_value = String(gross)
            } else if (discountVal < 0) {
              discountVal = 0
              line.discount_value = '0'
            }
          }
        }

        if (line.discount_type === 'percentage') {
          line.item_net_price = gross - (gross * discountVal / 100)
        } else if (line.discount_type === 'static') {
          line.item_net_price = gross - discountVal
        } else {
          line.item_net_price = gross
        }
        if (line.item_net_price < 0) {
          line.item_net_price = 0
        }

        // 3) Compute invoice_line_net_amount
        const baseQty = parseFloat(line.item_price_base_quantity) || 1
        const qty = parseFloat(line.invoiced_quantity) || 0
        line.invoice_line_net_amount = (line.item_net_price / baseQty) * qty

        // 4) Compute VAT line amount if not reverse charge
        let rate = parseFloat(line.invoiced_item_tax_rate) || 0
        if (line.invoiced_item_tax_category_code === 'Reverse_Charge') {
          line.vat_line_amount = 0
        } else {
          line.vat_line_amount = (line.invoice_line_net_amount * rate) / 100
        }

        // 5) **If invoice_type_code is 381 (Credit Note), negate amounts** 
        // so that net/tax appear as negative values.
        if (invoiceData.value.invoice_type_code === '381') {
          line.invoice_line_net_amount = -Math.abs(line.invoice_line_net_amount)
          line.vat_line_amount = -Math.abs(line.vat_line_amount)
        }
      })
    })

    // Summaries
    const computedTaxBreakdownByCategory = computed(() => {
      const map = {}
      invoiceData.value.invoice_lines.forEach((line) => {
        if (!line.invoiced_item_tax_category_code) return
        const code = line.invoiced_item_tax_category_code
        const rate = parseFloat(line.invoiced_item_tax_rate) || 0
        const taxableAmount = parseFloat(line.invoice_line_net_amount) || 0

        let taxAmount = (taxableAmount * rate) / 100

        if (code === 'Reverse_Charge') {
          taxAmount = 0
        }

        if (!map[code]) {
          map[code] = {
            tax_category_code: code,
            tax_category_rate: rate,
            taxable_amount: 0,
            tax_amount: 0
          }
        }
        map[code].taxable_amount += taxableAmount
        map[code].tax_amount += taxAmount
      })
      return Object.values(map).map(item => ({
        tax_category_code: item.tax_category_code,
        tax_category_rate: item.tax_category_rate,
        taxable_amount: item.taxable_amount,
        tax_amount: item.tax_amount
      }))
    })

    const invoice_total_line_net_amount = computed(() => {
      return invoiceData.value.invoice_lines.reduce((sum, line) => {
        return sum + (parseFloat(line.invoice_line_net_amount) || 0)
      }, 0)
    })

    const invoice_total_tax_amount = computed(() => {
      return computedTaxBreakdownByCategory.value.reduce((sum, row) => {
        return sum + row.tax_amount
      }, 0)
    })

    const invoice_total_with_tax = computed(() => {
      return invoice_total_line_net_amount.value + invoice_total_tax_amount.value
    })

    const invoice_due_for_payment = computed(() => {
      const paid = parseFloat(invoiceData.value.paid_amount) || 0
      const rounding = parseFloat(invoiceData.value.rounding_amount) || 0
      return invoice_total_with_tax.value - paid + rounding
    })

    // Check if 3rd toggle (Margin Scheme) is selected
    const isMarginSchemeSelected = computed(() => {
      return (
        invoiceData.value.transactionTypes &&
        invoiceData.value.transactionTypes[2] &&
        invoiceData.value.transactionTypes[2].selected
      )
    })

    return {
      invoiceData,
      units,
      addInvoiceLine,
      removeInvoiceLine,
      validateInvoicedQuantity,
      incrementQuantity,
      decrementQuantity,
      isAtMaxQuantity,
      isAtMinQuantity,
      computedTaxBreakdownByCategory,
      invoice_total_line_net_amount,
      invoice_total_tax_amount,
      invoice_total_with_tax,
      invoice_due_for_payment,
      isMarginSchemeSelected
    }
  }
}
</script>


<style scoped>
/* Your original styles remain unchanged */
.container {
  max-width: 100%;
  padding: 20px;
  font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
  color: #374151;
  background: #f3f4f6;
  border-radius: 12px;
}
.sections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}
.invoice-line-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e7eb;
}
.section {
  padding: 24px;
  border-radius: 12px;
  background: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}
.invoice-line::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #f59e0b, #ef4444);
}
.tax-breakdown::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #10b981, #3b82f6);
}
.document-totals::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #4f46e5, #8b5cf6);
}
.payment-details::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(to right, #3b82f6, #06b6d4);
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
.form-row {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
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
.currency-group {
  position: relative;
  display: flex;
  align-items: center;
}
.currency-group input {
  padding-right: 40px;
}
.currency {
  position: absolute;
  right: 10px;
  font-weight: bold;
  color: #4b5563;
}
.animate-in {
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
  transform: translateY(20px);
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.add-line-button {
  margin-top: 10px;
  margin-bottom: 20px;
  padding: 10px 16px;
  background-color: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3);
}
.add-line-button:hover {
  background-color: #4338ca;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(79, 70, 229, 0.4);
}
.remove-line-button {
  width: 50% !important;
  padding: 5px 5px;
  background-color: #f3f4f6;
  color: #ef4444;
  border: 1px solid #ef4444;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.2s ease;
  align-self: end;
  margin-top: 10px;
  margin-left: 65px !important;
}
.remove-line-button:hover {
  background-color: #fee2e2;
  color: #dc2626;
}
.tax-breakdown-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
.tax-breakdown-table th,
.tax-breakdown-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}
@media (max-width: 768px) {
  .sections-grid { grid-template-columns: 1fr; }
  .invoice-line-grid { grid-template-columns: 1fr; }
}

/* Add quantity control styling */
.quantity-control {
  position: relative;
  display: flex;
}

.quantity-control input {
  width: calc(100% - 30px);
  padding-right: 35px;
}

.quantity-arrows {
  display: flex;
  flex-direction: column;
  position: absolute;
  right: 0;
  top: 0;
  height: 100%;
  width: 30px;
}

.arrow-btn {
  flex: 1;
  padding: 0;
  margin: 0;
  border: none;
  background-color: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  font-size: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, color 0.2s;
}

.arrow-btn:first-child {
  border-top-right-radius: 8px;
  border-bottom: 1px solid #d1d5db;
}

.arrow-btn:last-child {
  border-bottom-right-radius: 8px;
}

.arrow-btn:hover:not(:disabled) {
  background-color: #e5e7eb;
  color: #374151;
}

.arrow-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>