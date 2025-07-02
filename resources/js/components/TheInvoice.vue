<template>
  <div>
    <!-- Action Buttons -->
    <div class="actionbox_position">
      <div class="action-item" @click="downloadPDF">
        <!-- SVG for DOWNLOAD -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7 10 12 15 17 10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <span>DOWNLOAD &nbsp;</span>
      </div>
      <div class="action-item" @click="sendPDF">
        <!-- SVG for SEND -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
        <span>SEND &nbsp;</span>
      </div>
      <div class="action-item" @click="printInvoice">
        <!-- SVG for PRINT -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>PRINT &nbsp;</span>
      </div>
    </div>

    <!-- Invoice Container -->
    <div class="invoice-container" ref="invoiceContainer" v-if="invoiceData">
      <div class="invoice-header">
        <div class="header-logo">
          <!-- Logo if available -->
        </div>
        <div class="header-meta">
          <div class="meta-item">
            <div class="meta-label">Invoice Number</div>
            <div class="meta-value">{{ invoiceData.invoice_number }}</div>
          </div>
          <div class="meta-item">
            <div class="meta-label">Date</div>
            <div class="meta-value">{{ invoiceData.invoice_issue_date }}</div>
          </div>
        </div>
        <div class="header-details">
          <h1 class="arabic">فاتورة ضريبية</h1>
          <h1>Tax Invoice</h1>
        </div>
      </div>

      <div class="invoice-content">
        <div class="invoice-row">
          <div class="info-section">
            <div class="section-header">
              <span>Bill to {{ invoiceData?.buyer?.buyer_name }}</span>
              <span class="arabic">العميل</span>
            </div>
            <div class="row">
              <div class="label">Country</div>
              <div class="value">
                <span>{{ invoiceData?.buyer?.country_code }}</span>
                <span class="value-arabic">البلد</span>
              </div>
            </div>
            <hr/>
            <div class="row">
              <div class="label">VAT Number</div>
              <div class="value">
                <span>{{ invoiceData?.buyer?.buyer_tax_identifier }}</span>
                <span class="value-arabic">رقم التسجيل الضريبي</span>
              </div>
            </div>
          </div>

          <div class="invoice-details">
            <div class="row detail-row">
              <div class="label">Invoice Number</div>
              <div class="value">
                <span>{{ invoiceData.invoice_number }}</span>
                <span class="value-arabic">رقم الفاتورة</span>
              </div>
            </div>
            <div class="row detail-row">
              <div class="label">Date</div>
              <div class="value">
                <span>{{ invoiceData.invoice_issue_date }}</span>
                <span class="value-arabic">التاريخ</span>
              </div>
            </div>
            <div class="row detail-row">
              <div class="label">Due Date</div>
              <div class="value">
                <span>{{ invoiceData.payment_due_date || 'N/A' }}</span>
                <span class="value-arabic">تاريخ الاستحقاق</span>
              </div>
            </div>
          </div>
        </div>

        <div class="invoice-table" v-if="invoiceData.lines">
          <div class="table-header">
            <div class="table-cell arabic">وصف المنتج<br />Item Description</div>
            <div class="table-cell">الكمية<br />Quantity</div>
            <div class="table-cell">السعر<br />Price</div>
            <div class="table-cell">القيمة المضافة<br />VAT</div>
            <div class="table-cell arabic">المجموع<br />Total</div>
          </div>
          <div class="table-row" v-for="line in invoiceData.lines" :key="line.line_id">
            <div class="table-cell arabic">{{ line.item_description }}</div>
            <div class="table-cell">{{ line.invoiced_quantity }}</div>
            <div class="table-cell">{{ line.item_net_price }}</div>
            <div class="table-cell">{{ line.vat_line_amount }}</div>
            <div class="table-cell">
              {{ calculateLineTotal(line.invoiced_quantity, line.item_net_price, line.vat_line_amount) }}
            </div>
          </div>
        </div>

        <div class="invoice-summary">
          <div class="summary-row">
            <div class="summary-value">{{ invoiceData.invoice_total_line_net_amount }}</div>
            <div class="summary-label arabic">المجموع الفرعي<br />Subtotal</div>
          </div>
          <div class="summary-row">
            <div class="summary-value">{{ invoiceData.invoice_total_tax_amount }}</div>
            <div class="summary-label arabic">إجمالي الضريبة<br />Total VAT</div>
          </div>
          <div class="summary-row total">
            <div class="summary-value">{{ invoiceData.invoice_total_with_tax }}</div>
            <div class="summary-label arabic">الإجمالي<br />Total</div>
          </div>
        </div>

        <div class="invoice-due">
          <div class="due-row">
            <div class="due-value">{{ invoiceData.invoice_due_for_payment }}</div>
            <div class="due-label arabic">الرصيد المستحق<br />Total Due</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      Loading...
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import jsPDF from "jspdf"
import html2canvas from "html2canvas"

export default {
  name: "InvoiceComponent",
  data() {
    return {
      invoiceData: null
    }
  },
  async created() {
    try {
      // Get invoice id from route parameters
      const invoiceId = this.$route.params.id
      const response = await axios.get(`/api/invoice/${invoiceId}`)
      this.invoiceData = response.data.data
    } catch (error) {
      console.error('Error fetching invoice:', error)
    }
  },
  methods: {
    calculateLineTotal(quantity, price, vatAmount) {
      // Convert to numbers with fallback to 0
      const qty = parseFloat(quantity) || 0;
      const unitPrice = parseFloat(price) || 0;
      const vat = parseFloat(vatAmount) || 0;
      // Calculate: (Quantity * Price) + VAT
      return ((qty * unitPrice) + vat).toFixed(2);
    },
    async downloadPDF() {
      try {
        window.scrollTo(0, 0)
        const originalElement = this.$refs.invoiceContainer
        const clone = originalElement.cloneNode(true)
        clone.style.setProperty("width", "900px", "important")
        clone.style.position = "absolute"
        clone.style.top = "-9999px"
        document.body.appendChild(clone)

        const canvas = await html2canvas(clone, { scrollX: 0, scrollY: 0 })
        document.body.removeChild(clone)

        const imgData = canvas.toDataURL("image/png")
        const pdf = new jsPDF("p", "pt", "a4")
        const pageWidth = pdf.internal.pageSize.getWidth()
        const pageHeight = pdf.internal.pageSize.getHeight()
        const imgWidth = canvas.width
        const imgHeight = canvas.height
        const ratio = imgWidth / imgHeight
        let pdfWidth = pageWidth
        let pdfHeight = pageWidth / ratio

        if (pdfHeight > pageHeight) {
          pdfHeight = pageHeight
          pdfWidth = pageHeight * ratio
        }

        pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight, "", "FAST")
        pdf.save("invoice.pdf")
      } catch (error) {
        console.error("Error generating PDF:", error)
      }
    },
    sendPDF() {
      window.location.href = "mailto:?subject=Your Invoice&body=Please find your invoice attached."
    },
    printInvoice() {
      const invoiceHTML = this.$refs.invoiceContainer.innerHTML
      const printWindow = window.open("", "_blank", "width=900,height=650")
      const styles = Array.from(
        document.querySelectorAll("style, link[rel='stylesheet']")
      )
        .map((style) => style.outerHTML)
        .join("")

      printWindow.document.write(`
        <html>
          <head>
            <title>Print Invoice</title>
            ${styles}
          </head>
          <body>
            <div class="invoice-container">
              ${invoiceHTML}
            </div>
          </body>
        </html>
      `)
      printWindow.document.close()
      printWindow.focus()
      printWindow.onload = function () {
        printWindow.print()
        printWindow.close()
      }
    }
  }
}
</script>

  <style scoped>
  /* Invoice Container */
  .invoice-container {
    max-width: 1400px;
    margin: 0 auto !important; /* or margin: 0 !important; */
    font-family: 'Inter', 'SF Pro Display', 'Segoe UI', 'Roboto', sans-serif;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.3s ease;
  }


  /* Invoice Header */
  .invoice-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 25px 35px;
    background: linear-gradient(135deg, #ffffff, #f1f5f9);
    border-bottom: 2px solid #e2e8f0;
  }

  .header-logo img {
    max-height: 60px;
    margin-right: 20px;
  }

  .header-details {
    flex-grow: 1;
    text-align: center;
  }

  .header-details h1 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #1e293b;
    text-transform: uppercase;
  }

  .header-details h1.arabic {
    font-size: 20px;
    color: #334155;
    margin-bottom: 5px;
  }

.header-meta {
    text-align: left;
    line-height: 1.2;
}

  .meta-item {
    margin-bottom: 5px;
  }

  .meta-label {
    font-size: 12px;
    color: #64748b;
  }

  .meta-value {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
  }

  /* Action Buttons */
  .actionbox_position {
    display: flex;
    justify-content: flex-end;
    padding: 20px;
    gap: 12px;
  }

  .action-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #444;
    background-color: #f8f9fa;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  }

  .action-item:hover {
    background-color: #eff6ff;
    color: #1a56db;
    transform: translateY(-1px);
  }

  /* Main Invoice Content */
  .invoice-content {
    padding: 35px;
    background-color: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    margin: 20px 35px;
    position: relative;
  }

  .invoice-content:before {
    content: "";
    position: absolute;
    top: 20px;
    right: 20px;
    bottom: 20px;
    left: 20px;
    border: 1px solid rgba(0, 0, 0, 0.03);
    border-radius: 8px;
    pointer-events: none;
  }

  /* Invoice Rows (two-column layout) */
  .invoice-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
  }

  /* Company Block */
  .company-block {
    background-color: #fff;
    border: 1px solid #f0f2f5;
    border-radius: 6px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .company-block .company-name {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
  }

  .value-arabic {
    font-family: "Tajawal", "Roboto", sans-serif;
    font-size: 13px;
    color: #64748b;
    text-align: right;
    margin-top: 2px;
  }

  /* Company Info */
  .company-info {
    background-color: #fff;
    border: 1px solid #f0f2f5;
    border-radius: 6px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    text-align: right;
  }

  .label-secondary {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
  }

  .value-secondary {
    font-size: 15px;
    margin-bottom: 6px;
    color: #1a202c;
  }

  .label-tertiary {
    font-size: 12px;
    color: #64748b;
  }

  /* Info Section (Bill To) */
  .info-section {
    background-color: #f9fafc;
    border: 1px solid #eaedf2;
    border-radius: 6px;
    padding: 15px;
  }

  .info-section .section-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eaedf2;
  }

  /* Invoice Details */
  .invoice-details {
    background-color: #f9fafc;
    border: 1px solid #eaedf2;
    border-radius: 6px;
    padding: 15px;
  }

  .detail-row {
    display: flex;
    flex-direction: column;
    padding: 8px 0;
    border-bottom: 1px solid #f0f2f5;
  }

  .detail-row:last-child {
    border-bottom: none;
  }

  .label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
    font-weight: 500;
  }

  .value {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  /* Responsive columns */
  @media (max-width: 640px) {
    .invoice-row {
      grid-template-columns: 1fr;
    }
  }

  /* Invoice Table */
  .invoice-table {
    width: 100%;
    margin-bottom: 25px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }

  .table-header {
    display: flex;
    background-color: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    text-align: center;
  }

  .table-cell {
    flex: 1;
    padding: 14px 10px;
    font-size: 13px;
    border-right: 1px solid #e2e8f0;
    color: #4a5568;
  }

  .table-header .table-cell {
    color: #2d3748;
    font-weight: 600;
  }

  .table-cell:last-child {
    border-right: none;
  }

  .table-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease-in-out;
    font-size: 14px;
  }

  .table-row:last-child {
    border-bottom: none;
  }

  .table-row:hover {
    background-color: #f9fafb;
  }

  /* Invoice Summary */
  .invoice-summary {
    margin-top: 25px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 20px;
    background-color: #f8fafc;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 4px 0;
  }

  .summary-row.total {
    font-weight: 700;
    border-top: 1px solid #e2e8f0;
    padding-top: 12px;
    margin-top: 5px;
  }

  .summary-label {
    text-align: right;
    color: #4a5568;
    font-weight: 500;
  }

  .summary-value {
    text-align: left;
    font-weight: 500;
    color: #2d3748;
  }

  /* Invoice Due */
  .invoice-due {
    margin-top: 25px;
    text-align: right;
    background-color: #ebf4ff;
    border: 1px solid #c3dafe;
    border-radius: 6px;
    padding: 18px;
  }

  .due-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
  }

  .due-value {
    font-size: 20px;
    font-weight: 700;
    text-align: left;
    color: #1a56db;
  }

  .due-label {
    text-align: right;
    color: #4a5568;
  }

  .currency {
    font-size: 12px;
    color: #718096;
  }

  .total {
    font-weight: 600;
    border-top: 1px solid #e2e8f0;
    padding: 10px 0;
    color: #1a202c;
  }

  /* Print-friendly styles */
  @media print {
    .actionbox_position {
      display: none;
    }
    .invoice-container {
      box-shadow: none;
      border: none;
    }
  }
  </style>
