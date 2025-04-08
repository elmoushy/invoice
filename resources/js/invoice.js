import { defineStore } from 'pinia'
import axios from 'axios'

export const useInvoiceStore = defineStore('invoice', {
  // Enable persistence using localStorage; ensure you have installed pinia-plugin-persistedstate
  persist: {
    enabled: true,
    strategies: [
      { storage: localStorage }
    ]
  },

  state: () => ({
    invoiceData: {
      invoice_issue_date: '',
      invoice_type_code: '',
      invoice_currency_code: '',
      payment_currency_code: '',
      tax_registration_identifier: '',
      invoice_total_line_net_amount: 0,
      invoice_total_tax_amount: 0,
      invoice_total_with_tax: 0,
      invoice_due_for_payment: 0,
      payment_due_date: '',
      business_process_type: '',
      specification_identifier: '',
      currency_exchange_rate: null,
      invoice_total_tax_amount_acc_currency: 0,
      status: '',
      seller_id: null,
      buyer_id: null,

      // Payment fields
      payment_means_type_code: '',
      payment_account_identifier: '',
      payment_account_name: '',
      payment_date: '',
      paid_amount: '',
      rounding_amount: '',
      payment_card_primary_account_number: '',
      expiry_date: '',
      cvv: '',

      // Additional fields
      invoice_transaction_type_code: '00000000',
      tax_scheme_code: '',
      scheme_identifier_electronic_address: '',
      credit_transfer: '',
      invoiced_quantity_unit_code: '',

      // New fields for Principal ID and Transaction Types
      principal_id: '',
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

      // New Credit Note Fields
      creditNoteRefInvoice: '',           // Should be numeric (unsigned big integer)
      creditNoteRefInvoice_number: '',      // String field for invoice number
      correction_method: '',
      reason_for_credit_note: '',

      // Summary Invoice Date Range Fields
      summary_invoice_start_date: '',
      summary_invoice_end_date: '',

      // Delivery-related fields
      deliver_to_address_line_1: '',
      deliver_to_address_line_2: '',
      deliver_to_address_line_3: '',
      deliver_to_post_code: '',
      deliver_to_country_code: '',
      deliver_to_country_subdivision: '',
      deliver_to_city: '',
      deliver_to_party_name: '',
      deliver_to_location_identifier: '',
      location_scheme_identifier: '',
      actual_delivery_date: '',

      // Arrays
      invoice_lines: [],
      tax_breakdowns: [],
      payment_details: []
    },
    invoiceLoaded: false
  }),

  actions: {
    async loadInvoiceForEdit(id) {
      if (this.invoiceLoaded) return

      try {
        const resp = await axios.get(`/api/invoice/${id}`)
        const serverData = resp.data.data

        // Get invoice lines and payments from the server.
        const invoiceLines = serverData.lines || []
        const paymentDetails = serverData.payments || []

        // Create a transformed object from the server data.
        // We merge in the new Credit Note fields using the null‑coalescing operator.
        const transformedData = {
          ...serverData,
          invoice_lines: invoiceLines,
          payment_details: paymentDetails,
          principal_id: serverData.principal_id || '',
          transactionTypes: serverData.transactionTypes || [
            { label: 'Free Trade Zone', selected: false },
            { label: 'Deemed Supply', selected: false },
            { label: 'Margin Scheme', selected: false },
            { label: 'Summary Invoice', selected: false },
            { label: 'Continuous Supply', selected: false },
            { label: 'Disclosed Agent Billing', selected: false },
            { label: 'Supply through E-commerce', selected: false },
            { label: 'Exports', selected: false }
          ],          
          summary_invoice_start_date: serverData.summary_invoice_start_date || '',
          summary_invoice_end_date: serverData.summary_invoice_end_date || '',
          deliver_to_address_line_1: serverData.deliver_to_address_line_1 || '',
          deliver_to_address_line_2: serverData.deliver_to_address_line_2 || '',
          deliver_to_address_line_3: serverData.deliver_to_address_line_3 || '',
          deliver_to_post_code: serverData.deliver_to_post_code || '',
          deliver_to_country_code: serverData.deliver_to_country_code || '',
          deliver_to_country_subdivision: serverData.deliver_to_country_subdivision || '',
          deliver_to_city: serverData.deliver_to_city || '',
          deliver_to_party_name: serverData.deliver_to_party_name || '',
          deliver_to_location_identifier: serverData.deliver_to_location_identifier || '',
          location_scheme_identifier: serverData.scheme_identifier || '',
          actual_delivery_date: serverData.actual_delivery_date || '',
          // Load the new fields, preserving existing values if the server field is null.
          creditNoteRefInvoice: serverData.creditNoteRefInvoice ?? this.invoiceData.creditNoteRefInvoice,
          creditNoteRefInvoice_number: serverData.creditNoteRefInvoice_number ?? this.invoiceData.creditNoteRefInvoice_number,
          correction_method: serverData.correction_method ?? this.invoiceData.correction_method,
          reason_for_credit_note: serverData.reason_for_credit_note ?? this.invoiceData.reason_for_credit_note,
        };

        // Map payment details if any are present.
        if (paymentDetails.length > 0) {
          const firstPayment = paymentDetails[0];
          let typeCode = firstPayment.payment_means_type_code;
          if (typeCode === 'cash') {
            typeCode = '97';
          }
          transformedData.payment_means_type_code = typeCode || '';
          transformedData.payment_date = firstPayment.payment_date || '';
          transformedData.payment_account_identifier = firstPayment.payment_account_identifier || '';
          transformedData.payment_account_name = firstPayment.payment_account_name || '';
          transformedData.paid_amount = firstPayment.paid_amount || '';
          transformedData.rounding_amount = firstPayment.rounding_amount || '';
          transformedData.payment_card_primary_account_number = firstPayment.payment_card_primary_account_number || '';
          transformedData.expiry_date = firstPayment.expiry_date || '';
          transformedData.cvv = '';
        }

        // Merge the transformed data with the existing store
        this.invoiceData = { ...this.invoiceData, ...transformedData };

        this.invoiceLoaded = true;
      } catch (err) {
        console.error('Error fetching invoice for edit:', err);
        throw err;
      }
    },

    updateField(field, value) {
      this.invoiceData[field] = value;
    },

    resetInvoiceData() {
      this.invoiceData = {
        invoice_issue_date: '',
        invoice_type_code: '',
        invoice_currency_code: '',
        payment_currency_code: '',
        tax_registration_identifier: '',
        invoice_total_line_net_amount: 0,
        invoice_total_tax_amount: 0,
        invoice_total_with_tax: 0,
        invoice_due_for_payment: 0,
        payment_due_date: '',
        business_process_type: '',
        specification_identifier: '',
        currency_exchange_rate: null,
        invoice_total_tax_amount_acc_currency: 0,
        status: '',
        seller_id: null,
        buyer_id: null,

        payment_means_type_code: '',
        payment_account_identifier: '',
        payment_account_name: '',
        payment_date: '',
        paid_amount: '',
        rounding_amount: '',
        payment_card_primary_account_number: '',
        expiry_date: '',
        cvv: '',

        invoice_transaction_type_code: '00000000',
        tax_scheme_code: '',
        scheme_identifier_electronic_address: '',
        credit_transfer: '',
        invoiced_quantity_unit_code: '',

        principal_id: '',
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

        // Credit Note fields
        creditNoteRefInvoice: '',
        creditNoteRefInvoice_number: '',
        correction_method: '',
        reason_for_credit_note: '',

        summary_invoice_start_date: '',
        summary_invoice_end_date: '',

        deliver_to_address_line_1: '',
        deliver_to_address_line_2: '',
        deliver_to_address_line_3: '',
        deliver_to_post_code: '',
        deliver_to_country_code: '',
        deliver_to_country_subdivision: '',
        deliver_to_city: '',
        deliver_to_party_name: '',
        deliver_to_location_identifier: '',
        location_scheme_identifier: '',
        actual_delivery_date: '',

        invoice_lines: [],
        tax_breakdowns: [],
        payment_details: [],
      };
      this.invoiceLoaded = false;
    },
  },
});
