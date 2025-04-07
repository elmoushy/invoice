<template>
    <div class="scroll-container">
      <div class="indicator-wrapper">
        <div
          class="indicator"
          :class="{
            'first': isActive('invoice-details'),
            'second': isActive('seller-details'),
            'third': isActive('buyer-details'),
            'fourth': isActive('document-totals')
          }"
        ></div>
      </div>
      <slot></slot>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TheVerticalScrollBar',
    computed: {
      currentRoute() {
        return this.$route.path;
      }
    },
    methods: {
      isActive(section) {
        return this.currentRoute.includes(section);
      }
    }
  }
  </script>
  
  <style scoped>
  .scroll-container {
    flex: 1;
    overflow-y: auto;
    height: 100vh;
    background-color: #f5f7fa;
    width: 100%;
    position: relative;
  }
  
  .indicator-wrapper {
    position: absolute;
    left: -10px; /* Adjust to match the side menu */
    top: 20px;
    height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .indicator {
    width: 8px;
    height: 8px;
    background-color: transparent;
    border: 2px dashed #2979ff;
    border-radius: 50%;
    transition: all 0.3s ease-in-out;
  }
  
  .first { transform: translateY(0); }
  .second { transform: translateY(50px); }
  .third { transform: translateY(100px); }
  .fourth { transform: translateY(150px); }
  </style>