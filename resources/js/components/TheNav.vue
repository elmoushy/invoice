<template>
  <aside class="side-menu">
    <div class="menu-container">
      <h2 class="menu-title">Invoice Creation</h2>
      <div class="progress-bar">
        <div class="progress-indicator" :style="{ width: getProgressWidth }"></div>
      </div>
      <ul class="menu-items">
        <li 
          v-for="(item, index) in dynamicMenuItems" 
          :key="index" 
          class="menu-item"
        >
          <router-link
            :to="item.path"
            :class="['menu-link', { active: $route.path.includes(item.route) }]"
          >
            <div class="link-container">
              <div 
                class="icon-wrapper"
                :class="{ active: $route.path.includes(item.route) }"
              >
                <span class="step-number">{{ index + 1 }}</span>
              </div>
              <div class="link-content">
                <span class="link-text">{{ item.text }}</span>
                <span v-if="item.subtext" class="link-subtext">{{ item.subtext }}</span>
              </div>
            </div>
          </router-link>
        </li>
      </ul>
    </div>
  </aside>
</template>

<script>
export default {
  name: 'TheNav',
  computed: {
    // If we have an :id in the URL, we’re in edit mode
    isEditMode() {
      return !!this.$route.params.id
    },
    currentInvoiceId() {
      return this.$route.params.id
    },
    // Dynamically build the base path: /create-invoice vs. /invoice/:id/edit
    basePath() {
      return this.isEditMode
        ? `/invoice/${this.currentInvoiceId}/edit`
        : '/create-invoice'
    },
    // Build the nav items using basePath
    dynamicMenuItems() {
      return [
        {
          text: 'Invoice Details',
          route: 'invoice-details',
          path: `${this.basePath}/invoice-details`
        },
        {
          text: 'Seller Details',
          route: 'seller-details',
          path: `${this.basePath}/seller-details`
        },
        {
          text: 'Buyer Details',
          route: 'buyer-details',
          path: `${this.basePath}/buyer-details`
        },
        {
          text: 'Document Totals',
          subtext: 'Tax Breakdown / Invoice Line',
          route: 'document-totals',
          path: `${this.basePath}/document-totals`
        }
      ]
    },
    // Determine which step is active for the progress bar
    currentStep() {
      const currentPath = this.$route.path
      const index = this.dynamicMenuItems.findIndex(item =>
        currentPath.includes(item.route)
      )
      return index !== -1 ? index : 0
    },
    getProgressWidth() {
      const stepPercentage = (this.currentStep / (this.dynamicMenuItems.length - 1)) * 100
      return `${stepPercentage}%`
    }
  }
}
</script>
  
  <style scoped>
  .side-menu {
    width: 280px;
    min-height: 100vh;
    background-color: #ffffff;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    position: relative;
  }
  
  .menu-container {
    padding: 32px 16px;
  }
  
  .menu-title {
    font-size: 20px;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 24px;
    padding-left: 16px;
  }
  
  .progress-bar {
    height: 4px;
    background-color: #e2e8f0;
    border-radius: 4px;
    margin-bottom: 32px;
    overflow: hidden;
  }
  
  .progress-indicator {
    height: 100%;
    background: linear-gradient(90deg, #4299e1, #667eea);
    border-radius: 4px;
    transition: width 0.3s ease;
  }
  
  .menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .menu-item {
    margin-bottom: 8px;
  }
  
  .menu-link {
    display: block;
    text-decoration: none;
    color: #4a5568;
    border-radius: 8px;
    transition: all 0.2s ease;
    padding: 12px 16px;
  }
  
  .menu-link:hover {
    background-color: #f7fafc;
  }
  
  .menu-link.active {
    background-color: #ebf4ff;
    color: #3182ce;
  }
  
  .link-container {
    display: flex;
    align-items: center;
  }
  
  .icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: #e2e8f0;
    border-radius: 50%;
    margin-right: 12px;
    transition: all 0.2s ease;
  }
  
  .icon-wrapper.active {
    background: linear-gradient(135deg, #4299e1, #667eea);
    color: white;
  }
  
  .step-number {
    font-size: 14px;
    font-weight: 600;
  }
  
  .link-content {
    display: flex;
    flex-direction: column;
  }
  
  .link-text {
    font-size: 14px;
    font-weight: 500;
  }
  
  .link-subtext {
    font-size: 12px;
    color: #718096;
    margin-top: 2px;
  }
  
  .menu-link.active .link-subtext {
    color: #4a5db4;
  }
  </style>
  