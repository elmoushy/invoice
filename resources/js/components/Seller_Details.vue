<template>
  <div class="container">
    <!-- Sellers List Section -->
    <div class="section sellers-list animate-in">
      <h3><span class="icon">🏬</span> Sellers List</h3>
      
        <!-- Dropdown for existing sellers -->
        <div class="select-row">
      <label class="form-label">Select Seller</label>
      <select class="form-select" v-model="invoiceData.seller_id" :disabled="invoiceData.invoice_type_code === '381'" required>
        <option value="" disabled>Select Seller</option>
        <option v-for="seller in sellers" :key="seller.seller_id" :value="seller.seller_id">
          {{ seller.seller_name }}
        </option>
      </select>
    </div>

    <!-- Conditionally render the Principal ID (TRN) input -->
    <div v-if="isDisclosedAgentBillingSelected" class="form-group">
      <label class="form-label">Principal ID (TRN)</label>
      <input
        type="text"
        class="form-control"
        v-model="invoiceData.principal_id"
        placeholder="Enter Principal ID (TRN)"
      />
      <span class="help-text">Required for Disclosed Agent Billing</span>
    </div>

    <br/>
      <!-- Button to toggle form for creating a new seller -->
      <div class="button-row">
        <button class="btn" @click="toggleForm">
          {{ showForm ? 'Hide Form' : 'Add New Seller' }}
        </button>
      </div>

      <!-- New Seller Form -->
      <transition name="fade">
        <div v-if="showForm" class="new-seller-form">
          <h4>Create a New Seller</h4>
          <form @submit.prevent="createSeller" novalidate>
              <div class="form-grid">
                <div class="form-group">
                <label class="form-label">Seller Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.seller_name"
                  required
                />
              </div>

              <!-- Scheme Identifier -->
              <div class="form-group">
                <label class="form-label">Scheme Identifier</label>
                <select
                  class="form-control"
                  v-model="newSeller.scheme_identifier"
                  @change="handleSchemeChange"
                >
                  <option value="" disabled>Select Scheme Identifier</option>
                  <option value="0235">0235 → Trade License</option>
                  <option value="EID">EID → Emirates ID</option>
                  <option value="PAS">PAS → Passport</option>
                  <option value="CD">CD → Cabinet Decision</option>
                </select>
              </div>

              <!-- Passport country code if scheme = PAS -->
              <div
                class="form-group"
                v-if="newSeller.scheme_identifier === 'PAS'"
              >
                <label class="form-label">
                  Passport Issuing Country Code <span class="required-badge">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.passport_issuing_country_code"
                  required
                />
                <span class="help-text">Required for Passport scheme</span>
              </div>

              <!-- Seller Legal Registration Identifier -->
              <div class="form-group">
                <label class="form-label">
                  Seller Legal Registration Identifier
                  <span
                    v-if="newSeller.scheme_identifier === '0235'"
                    class="required-badge"
                  >
                    *
                  </span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.legal_identifier"
                  :required="newSeller.scheme_identifier === '0235'"
                />
                <span
                  v-if="newSeller.scheme_identifier === '0235'"
                  class="help-text"
                >
                  Required for Trade License
                </span>
                <span
                  v-else-if="newSeller.scheme_identifier"
                  class="help-text"
                >
                  Optional for this scheme type
                </span>
              </div>

              <!-- Seller Tax Identifier -->
              <div class="form-group">
                <label class="form-label">
                  Seller Tax Identifier
                  <span v-if="!newSeller.legal_identifier" class="required-badge">
                    *
                  </span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.seller_tax_identifier"
                  pattern="^$|^1[0-9]{12}03$"
                  title="If provided, must be exactly 15 digits, starting with 1 and ending with 03"
                  :required="!newSeller.legal_identifier"
                  @input="updateTaxSchemeCode"
                />
                <span v-if="!newSeller.legal_identifier" class="help-text">
                  Required when Legal Registration Identifier is not provided.
                  Must be exactly 15 digits, starting with 1 and ending with 03
                </span>
                <span v-else class="help-text">
                  Optional. If provided, must be exactly 15 digits,
                  starting with 1 and ending with 03
                </span>
              </div>

              <!-- Tax Scheme Code (read-only) -->
              <div
                class="form-group"
                v-if="newSeller.seller_tax_identifier"
              >
                <label class="form-label">Tax Scheme Code</label>
                <input
                  type="text"
                  class="form-control"
                  value="VAT"
                  readonly
                  @input="newSeller.tax_scheme_code = 'VAT'"
                />
              </div>

              <!-- Seller Legal Registration Type -->
              <div class="form-group">
                <label class="form-label">Seller Legal Registration Type</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.seller_legal_registration_type"
                  readonly
                  :placeholder="newSeller.scheme_identifier ? 'Select scheme identifier first' : ''"
                />
              </div>

              <!-- Authority Name -->
              <div class="form-group">
                <label class="form-label">Authority Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.authority_name"
                  readonly
                />
              </div>

              <!-- Scheme Identifier Electronic Address -->
              <div class="form-group">
                <label class="form-label">
                  Scheme Identifier Electronic Address
                </label>
                <select
                  class="form-control"
                  v-model="newSeller.scheme_identifier_electronic_address"
                >
                  <option value="" disabled>Select Electronic Address Scheme</option>
                  <option value="0088">0088 → Peppol Electronic Address</option>
                  <option value="EM">EM → Email Address</option>
                  <option value="VAT">VAT → VAT Number as Identification</option>
                  <option value="GLN">
                    GLN → Global Location Number (GS1)
                  </option>
                  <option value="DUNS">DUNS → DUNS Number for Companies</option>
                </select>
              </div>

              <!-- Electronic Address -->
              <div class="form-group">
                <label class="form-label">Electronic Address</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.electronic_address"
                  :pattern="electronicAddressPattern"
                  :title="electronicAddressTitle"
                  :required="isElectronicAddressRequired"
                />
              </div>

              <!-- Address Line 1 -->
              <div class="form-group">
                <label class="form-label">Address Line 1</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.address_line1"
                />
              </div>

              <!-- City -->
              <div class="form-group">
                <label class="form-label">City</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.city"
                />
              </div>

              <!-- Country Code -->
              <div class="form-group">
                <label class="form-label">Country Code</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.country_code"
                />
              </div>

              <!-- Country Subdivision -->
              <div class="form-group">
                <label class="form-label">Country Subdivision</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newSeller.country_subdivision"
                />
              </div>
            </div>

            <!-- Save / Cancel Buttons -->
            <div class="button-group">
              <button type="submit" class="btn">Save Seller</button>
              <button
                type="button"
                class="btn cancel-btn"
                @click="cancelForm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { useInvoiceStore } from "../invoice";
import { storeToRefs } from "pinia";

export default {
  name: "SellerManagement",

  data() {
    return {
      sellers: [],
      showForm: false,
      // The object used when creating a new seller
      newSeller: {
        seller_name: "",
        seller_tax_identifier: "",
        legal_identifier: "",
        electronic_address: "",
        address_line1: "",
        city: "",
        country_code: "",
        country_subdivision: "",
        seller_legal_registration_type: "",
        authority_name: "",
        passport_issuing_country_code: "",
        scheme_identifier: "",
        scheme_identifier_electronic_address: "",
        tax_scheme_code: ""
      }
    };
  },

  computed: {
    isDisclosedAgentBillingSelected() {
      console.log("Checking disclosed agent billing status...");
      console.log("Transaction types:", this.invoiceData.transactionTypes);
      
      const isSelected = this.invoiceData.transactionTypes &&
        this.invoiceData.transactionTypes.some(
          option => option.label === "Disclosed Agent Billing" && option.selected
        );
      
      console.log("Is disclosed agent billing selected:", isSelected);
      return isSelected;
    },
    // Pattern for "Electronic Address" depending on the chosen scheme_identifier_electronic_address
    electronicAddressPattern() {
      switch (this.newSeller.scheme_identifier_electronic_address) {
        case "0088":
          return "^8888:\\d{13}$";
        case "EM":
          return "^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$";
        case "VAT":
          return "^1[A-Za-z0-9]{12}03$";
        case "GLN":
          return "^\\d{13}$";
        case "DUNS":
          return "^\\d{9}$";
        default:
          return ".*";
      }
    },
    // Title attribute for input validation tooltip
    electronicAddressTitle() {
      switch (this.newSeller.scheme_identifier_electronic_address) {
        case "0088":
          return "Format must be 8888: followed by exactly 13 digits (e.g. 8888:123456789123).";
        case "EM":
          return "Must be a valid email address with no spaces.";
        case "VAT":
          return "15 alphanumeric characters, starting with 1 and ending with 03 (e.g. 1000000000003).";
        case "GLN":
          return "Must be exactly 13 numeric digits (e.g. 123456789123).";
        case "DUNS":
          return "Must be exactly 9 numeric digits (e.g. 123456789).";
        default:
          return "";
      }
    },
    // If a scheme for electronic address is selected, require the field
    isElectronicAddressRequired() {
      return this.newSeller.scheme_identifier_electronic_address !== "";
    }
  },

  created() {
    this.fetchSellers();
  },

  methods: {
    // Fetch existing sellers from the backend
    fetchSellers() {
      axios
        .get("/api/sellers")
        .then((response) => {
          this.sellers = response.data.data.data;
        })
        .catch((error) => {
          console.error("Error fetching sellers:", error);
        });
    },

    toggleForm() {
      this.showForm = !this.showForm;
    },

    cancelForm() {
      this.showForm = false;
      this.resetNewSeller();
    },

    resetNewSeller() {
      this.newSeller = {
        seller_name: "",
        seller_tax_identifier: "",
        legal_identifier: "",
        electronic_address: "",
        address_line1: "",
        city: "",
        country_code: "",
        country_subdivision: "",
        seller_legal_registration_type: "",
        authority_name: "",
        passport_issuing_country_code: "",
        scheme_identifier: "",
        scheme_identifier_electronic_address: "",
        tax_scheme_code: ""
      };
    },

    // Set registration type based on scheme identifier
    handleSchemeChange() {
      if (this.newSeller.scheme_identifier === "0235") {
        this.newSeller.seller_legal_registration_type = "Trade License";
      } else if (this.newSeller.scheme_identifier === "EID") {
        this.newSeller.seller_legal_registration_type = "Emirates ID";
      } else if (this.newSeller.scheme_identifier === "PAS") {
        this.newSeller.seller_legal_registration_type = "Passport";
      } else if (this.newSeller.scheme_identifier === "CD") {
        this.newSeller.seller_legal_registration_type = "Cabinet Decision Number";
      } else {
        this.newSeller.seller_legal_registration_type = "";
      }
      this.updateAuthorityName();
    },

    // Set authority name based on registration type
    updateAuthorityName() {
      switch (this.newSeller.seller_legal_registration_type) {
        case "Trade License":
          this.newSeller.authority_name = "Department of Economic Development (DED)";
          break;
        case "Emirates ID":
          this.newSeller.authority_name = "Federal Authority for Identity and Citizenship (FAIC)";
          break;
        case "Passport":
          this.newSeller.authority_name = "UAE Immigration Authorities";
          break;
        case "Cabinet Decision Number":
          this.newSeller.authority_name = "UAE Government or specific ministry responsible for the regulation";
          break;
        default:
          this.newSeller.authority_name = "";
      }
    },

    // If seller_tax_identifier is entered, set tax_scheme_code = 'VAT'
    updateTaxSchemeCode() {
      if (this.newSeller.seller_tax_identifier) {
        this.newSeller.tax_scheme_code = "VAT";
      }
    },

    // Create a new seller, then set invoiceData.seller_id from the store
    createSeller() {
      axios
        .post("/api/seller", this.newSeller)
        .then((resp) => {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: resp.data.message || "Seller created!"
          });
          // Get the new seller ID from the response
          const newId = resp.data.data.seller_id;
          // Update the shared invoice data in Pinia with the new seller ID
          this.invoiceStore.invoiceData.seller_id = newId;
          // Refresh seller list
          this.fetchSellers();
          // Hide the form and reset
          this.showForm = false;
          this.resetNewSeller();
        })
        .catch((err) => {
          const errData = err.response && err.response.data;
          if (errData) {
            let errorHtml = errData.message ? `<p><strong>${errData.message}</strong></p>` : "";
            if (errData.errors) {
              errorHtml += "<ul>";
              for (let field in errData.errors) {
                errData.errors[field].forEach((msg) => {
                  errorHtml += `<li>${msg}</li>`;
                });
              }
              errorHtml += "</ul>";
            }
            Swal.fire({
              icon: "error",
              title: "Validation Error",
              html: errorHtml || "Please check your inputs."
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An unexpected error occurred."
            });
          }
        });
    }
  },

  setup() {
    // Access the Pinia store to use shared invoice data
    const invoiceStore = useInvoiceStore();
    const { invoiceData } = storeToRefs(invoiceStore);
    return { invoiceData, invoiceStore };
  }
};
</script>

<style scoped>
.container {
  max-width: 100%;
  padding: 20px;
  font-family: "Inter", "Segoe UI", Roboto, sans-serif;
  color: #374151;
  background: #f3f4f6;
  border-radius: 12px;
}

.section {
  margin-bottom: 25px;
  padding: 24px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.section:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.sellers-list::before {
  content: "";
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
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.select-row,
.button-row {
  margin-bottom: 16px;
}

.form-select,
.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background-color: #fff;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.form-select:focus,
.form-control:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.form-control:invalid,
.form-select:invalid {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

.btn {
  padding: 10px 20px;
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn:hover {
  background: #3c3fb1;
}

.cancel-btn {
  background: #ef4444;
  margin-left: 10px;
}

.new-seller-form {
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #fafafa;
}

.new-seller-form h4 {
  margin-bottom: 16px;
  font-size: 1rem;
  font-weight: 600;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  margin-bottom: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4b5563;
}

.help-text {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 4px;
}

.button-group {
  margin-top: 20px;
  display: flex;
  gap: 16px;
}

.required-badge {
  color: #ef4444;
  margin-left: 4px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
