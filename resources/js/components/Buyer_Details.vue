<template>
  <div class="container">
    <!-- Buyers List Section -->
    <div class="section buyers-list animate-in">
      <h3><span class="icon">🛒</span> Buyers List</h3>

      <!-- Existing Buyers Dropdown -->
      <div class="select-row">
        <label class="form-label">Select Buyer</label>
        <select class="form-select" v-model="invoiceData.buyer_id" required>
          <option value="" disabled>Select Buyer</option>
          <option
            v-for="buyer in buyers"
            :key="buyer.buyer_id"
            :value="buyer.buyer_id"
          >
            {{ buyer.buyer_name }} - {{ buyer.buyer_tax_identifier }}
          </option>
        </select>
      </div>

      <!-- "Add New Buyer" Button -->
      <div class="button-row">
        <button class="btn" @click="toggleForm">
          {{ showForm ? 'Hide Form' : 'Add New Buyer' }}
        </button>
      </div>

      <!-- Animated New Buyer Form -->
      <transition name="fade">
        <div v-if="showForm" class="new-buyer-form">
          <h4>Create a New Buyer</h4>
          <form @submit.prevent="createBuyer" novalidate>
            <div class="form-grid">
              <!-- Buyer Name -->
              <div class="form-group">
                <label class="form-label">Buyer Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.buyer_name"
                  required
                />
              </div>

              <!-- Scheme Identifier -->
              <div class="form-group">
                <label class="form-label">Scheme Identifier</label>
                <select
                  class="form-control"
                  v-model="newBuyer.scheme_identifier"
                  @change="handleSchemeChange"
                >
                  <option value="" disabled>Select Scheme Identifier</option>
                  <option value="0235">0235 → Trade License</option>
                  <option value="EID">EID → Emirates ID</option>
                  <option value="PAS">PAS → Passport</option>
                  <option value="CD">CD → Cabinet Decision</option>
                </select>
              </div>

              <!-- Passport Issuing Country Code -->
              <div
                class="form-group"
                v-if="newBuyer.scheme_identifier === 'PAS'"
              >
                <label class="form-label">
                  Passport Issuing Country Code <span class="required-badge">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.buyer_passport_issuing_country"
                  required
                />
                <span class="help-text">Required for Passport scheme</span>
              </div>

              <!-- Buyer Legal Registration Identifier -->
              <div class="form-group">
                <label class="form-label">
                  Buyer Legal Registration Identifier
                  <span
                    v-if="newBuyer.scheme_identifier === '0235'"
                    class="required-badge"
                  >
                    *
                  </span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.legal_identifier"
                  :required="newBuyer.scheme_identifier === '0235'"
                />
                <span v-if="newBuyer.scheme_identifier === '0235'" class="help-text">
                  Required for Trade License
                </span>
                <span v-else-if="newBuyer.scheme_identifier" class="help-text">
                  Optional for this scheme type
                </span>
              </div>

              <!-- Buyer Tax Identifier -->
              <div class="form-group">
                <label class="form-label">
                  Buyer Tax Identifier
                  <span v-if="!newBuyer.legal_identifier" class="required-badge">
                    *
                  </span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.buyer_tax_identifier"
                  pattern="^$|^1[0-9]{12}03$"
                  title="If provided, must be exactly 15 digits, starting with 1 and ending with 03"
                  :required="!newBuyer.legal_identifier"
                />
                <span v-if="!newBuyer.legal_identifier" class="help-text">
                  Required when Legal Registration Identifier is not provided. Must be
                  exactly 15 digits, starting with 1 and ending with 03
                </span>
                <span v-else class="help-text">
                  Optional. If provided, must be exactly 15 digits, starting with 1
                  and ending with 03
                </span>
              </div>

              <!-- Buyer Legal Registration Type (auto-populated) -->
              <div class="form-group">
                <label class="form-label">Buyer Legal Registration Type</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.buyer_legal_registration_type"
                  readonly
                  :placeholder="newBuyer.scheme_identifier ? 'Select scheme identifier first' : ''"
                />
              </div>

              <!-- Authority Code (auto-populated) -->
              <div class="form-group">
                <label class="form-label">Authority Code</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.authority_code"
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
                  v-model="newBuyer.scheme_identifier_electronic_address"
                >
                  <option value="" disabled>Select Electronic Address Scheme</option>
                  <option value="0088">0088 → Peppol Electronic Address</option>
                  <option value="EM">EM → Email Address</option>
                  <option value="VAT">VAT → VAT Number as Identification</option>
                  <option value="GLN">GLN → Global Location Number (GS1)</option>
                  <option value="DUNS">DUNS → DUNS Number for Companies</option>
                </select>
              </div>

              <!-- Electronic Address -->
              <div class="form-group">
                <label class="form-label">Electronic Address</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.electronic_address"
                  :pattern="electronicAddressPattern"
                  :title="electronicAddressTitle"
                  :required="isElectronicAddressRequired"
                />
              </div>

              <!-- Address Fields -->
              <div class="form-group">
                <label class="form-label">Address Line 1</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.address_line1"
                />
              </div>
              <div class="form-group">
                <label class="form-label">City</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.city"
                />
              </div>
              <div class="form-group">
                <label class="form-label">Country Code</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.country_code"
                />
              </div>
              <div class="form-group">
                <label class="form-label">Country Subdivision</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="newBuyer.country_subdivision"
                />
              </div>
            </div>

            <!-- Form Buttons -->
            <div class="button-group">
              <button type="submit" class="btn">Save Buyer</button>
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
  name: "BuyerManagement",
  data() {
    return {
      buyers: [],
      showForm: false,
      // Data for creating a new buyer
      newBuyer: {
        buyer_name: "",
        buyer_tax_identifier: "",
        legal_identifier: "",
        electronic_address: "",
        address_line1: "",
        city: "",
        country_code: "",
        country_subdivision: "",
        buyer_legal_registration_type: "",
        authority_code: "",
        buyer_passport_issuing_country: "",
        scheme_identifier: "",
        scheme_identifier_electronic_address: ""
      }
    };
  },
  computed: {
    // Validate the Electronic Address based on the selected scheme
    electronicAddressPattern() {
      switch (this.newBuyer.scheme_identifier_electronic_address) {
        case "0088":
          return "^8888:\\d{13}$"; // Format: 8888: followed by exactly 13 digits
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
    electronicAddressTitle() {
      switch (this.newBuyer.scheme_identifier_electronic_address) {
        case "0088":
          return "Format must be 8888: followed by exactly 13 digits (e.g. 8888:1234567891234).";
        case "EM":
          return "Must be a valid email address with no spaces.";
        case "VAT":
          return "15 alphanumeric chars, starting with 1 and ending with 03 (e.g. 1000000000003).";
        case "GLN":
          return "Must be exactly 13 numeric digits.";
        case "DUNS":
          return "Must be exactly 9 numeric digits.";
        default:
          return "";
      }
    },
    isElectronicAddressRequired() {
      return this.newBuyer.scheme_identifier_electronic_address !== "";
    }
  },
  created() {
    // Load the list of buyers (this API call is specific to the buyer list and remains)
    this.fetchBuyers();
  },
  methods: {
    fetchBuyers() {
      axios
        .get("/api/buyers")
        .then(response => {
          // Adjust this based on your API response structure
          this.buyers = response.data.data.data;
        })
        .catch(error => {
          console.error("Error fetching buyers:", error);
        });
    },
    toggleForm() {
      this.showForm = !this.showForm;
    },
    cancelForm() {
      this.showForm = false;
      this.resetNewBuyer();
    },
    resetNewBuyer() {
      this.newBuyer = {
        buyer_name: "",
        buyer_tax_identifier: "",
        legal_identifier: "",
        electronic_address: "",
        address_line1: "",
        city: "",
        country_code: "",
        country_subdivision: "",
        buyer_legal_registration_type: "",
        authority_code: "",
        buyer_passport_issuing_country: "",
        scheme_identifier: "",
        scheme_identifier_electronic_address: ""
      };
    },
    handleSchemeChange() {
      if (this.newBuyer.scheme_identifier === "0235") {
        this.newBuyer.buyer_legal_registration_type = "Trade License";
      } else if (this.newBuyer.scheme_identifier === "EID") {
        this.newBuyer.buyer_legal_registration_type = "Emirates ID";
      } else if (this.newBuyer.scheme_identifier === "PAS") {
        this.newBuyer.buyer_legal_registration_type = "Passport";
      } else if (this.newBuyer.scheme_identifier === "CD") {
        this.newBuyer.buyer_legal_registration_type = "Cabinet Decision Number";
      } else {
        this.newBuyer.buyer_legal_registration_type = "";
      }
      this.updateAuthorityCode();
    },
    updateAuthorityCode() {
      switch (this.newBuyer.buyer_legal_registration_type) {
        case "Trade License":
          this.newBuyer.authority_code = "Department of Economic Development (DED)";
          break;
        case "Emirates ID":
          this.newBuyer.authority_code = "Federal Authority for Identity and Citizenship (FAIC)";
          break;
        case "Passport":
          this.newBuyer.authority_code = "UAE Immigration Authorities";
          break;
        case "Cabinet Decision Number":
          this.newBuyer.authority_code = "UAE Government or specific ministry responsible for the regulation";
          break;
        default:
          this.newBuyer.authority_code = "";
      }
    },
    createBuyer() {
      axios
        .post("/api/buyer", this.newBuyer)
        .then(response => {
          const successText = response.data.message || "Buyer created successfully!";
          Swal.fire({
            icon: "success",
            title: "Success",
            text: successText
          });

          // Get the new buyer ID from the response
          const newBuyerId = response.data.data.buyer_id;

          // Set the buyer_id in the shared invoice data from Pinia
          this.invoiceStore.invoiceData.buyer_id = newBuyerId;

          // Refresh the buyers list
          this.fetchBuyers();

          // Hide the form and reset its fields
          this.showForm = false;
          this.resetNewBuyer();
        })
        .catch(error => {
          console.error("Error creating buyer:", error);
          if (error.response && error.response.data) {
            const errData = error.response.data;
            let errorHtml = "";
            if (errData.message) {
              errorHtml += `<p><strong>${errData.message}</strong></p>`;
            }
            if (errData.errors) {
              errorHtml += "<ul>";
              for (let field in errData.errors) {
                errData.errors[field].forEach(msg => {
                  errorHtml += `<li>${msg}</li>`;
                });
              }
              errorHtml += "</ul>";
            }
            Swal.fire({
              icon: "error",
              title: "Validation Error",
              html: errorHtml || "Please check the inputs."
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
    // Access the shared invoice store (for setting invoiceData.buyer_id)
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

.buyers-list::before {
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
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
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

.new-buyer-form {
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background-color: #fafafa;
}

.new-buyer-form h4 {
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
