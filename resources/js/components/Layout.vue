<template>
    <div class="d-flex flex-column min-vh-100">
      <!-- Conditionally show nav & header -->
      <TheHeader v-if="showNavHeader" />
  
      <!-- <main class="flex-fill container my-4">
        <router-view />
      </main> -->
    </div>
  </template>
  
  <script>
  import { useRoute } from 'vue-router'
  import { computed } from 'vue'
  import TheNav from './TheNav.vue'
  import TheHeader from './TheHeader.vue'
  
  export default {
    name: 'Layout',
    components: { TheNav, TheHeader },
    setup() {
      const route = useRoute();
      
      // Use computed to make it reactive to route changes
      const showNavHeader = computed(() => {
        return route.name !== 'Login' && route.name !== undefined;
      });
      
      return { showNavHeader };
    }
  }
  </script>
  