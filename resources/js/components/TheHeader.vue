<template>
      <!-- Header -->
      <nav class="navbar navbar-dark custom-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
          <!-- Centered Title -->
          <div class="d-flex align-items-center header-title" style="margin-left: 10px;">
        <i class="fas fa-file-invoice-dollar me-2 icon text-white"></i> 
        <span class="navbar-brand mb-0 h1">E-Invoice</span>
          </div>
      
          <!-- Navigation Links -->
          <div class="d-flex">
        <ul class="navbar-nav d-flex flex-row align-items-center">
          <li>
            <button class="nav-link btn btn-link text-white logout-btn" @click="logout">
          <span>Logout</span>
          <i class="fas fa-sign-out-alt ms-1"></i>
            </button>
          </li>
            </ul>
          </div>
        </div>
      </nav>
  
      <!-- Sidebar -->
      <div :class="['sidebar', { 'collapsed': isSidebarCollapsed }]">
        <ul class="nav flex-column">
          <li v-for="item in menuItems" :key="item.name" class="nav-item">
            <!-- If the item has children, display a clickable div that toggles the submenu -->
            <div v-if="item.children" class="nav-link" @click="toggleSubmenu(item)">
              <i :class="item.icon"></i>
              <span>{{ item.name }}</span>
              <!-- Arrow icon -->
              <i class="fas" :class="item.expanded ? 'fa-chevron-up' : 'fa-chevron-down'" style="margin-left: auto;"></i>
            </div>
            <!-- If no children, use a router-link -->
            <router-link v-else class="nav-link" :to="item.route">
              <i :class="item.icon"></i>
              <span>{{ item.name }}</span>
            </router-link>
            <!-- Submenu -->
            <transition name="submenu-fade">
              <ul v-if="item.children && item.expanded" class="submenu">
                <li v-for="child in item.children" :key="child.name" class="nav-item">
                  <router-link class="nav-link" :to="child.route">
                    <span>{{ child.name }}</span>
                  </router-link>
                </li>
              </ul>
            </transition>
          </li>
        </ul>
  
        <!-- Sidebar Toggle Button -->
        <button 
          class="sidebar-toggle-button"
          :class="{ 'is-active': !isSidebarCollapsed }"
          @click="toggleSidebar"
        >
          <i class="fas fa-angle-right"></i>
        </button>
      </div>
  
      <!-- Main Content -->
      <div class="main-content">
        <router-view></router-view>
      </div>
  </template>
  
  <script>
  export default {
    name: 'TheHeader',
    data() {
      return {
        isSidebarCollapsed: false,
        menuItems: [
          { name: 'Notifications', icon: 'fas fa-bell', route: '/notifications' },
          { name: 'Dashboard', icon: 'fas fa-chart-bar', route: '/' },
          { 
            name: 'Invoice', 
            icon: 'fas fa-file-alt',
            expanded: false,
            children: [
              { name: 'Create Invoice', icon: '', route: '/redirect' },
              { name: 'Invoices', icon: '', route: '/all_invoice' }
            ]
          },
          { name: 'Sales', icon: 'fas fa-dollar-sign', route: '/sales' },
          { name: 'Purchases', icon: 'fas fa-shopping-bag', route: '/purchases' },
        ],
      };
    },
    methods: {
      toggleSidebar() {
        this.isSidebarCollapsed = !this.isSidebarCollapsed;
      },
      toggleSubmenu(item) {
        item.expanded = !item.expanded;
      },
      logout() {
        localStorage.removeItem('auth_token');
        this.$router.push('/login');
      },
    },
  };
  </script>
  
  <style scoped>
  /* Header */
  .custom-header {
    background-color: #112240;
    height: 60px;
    display: flex;
    align-items: center;
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.6s ease-out;
  }
  
  
  .header-title {
    animation: fadeIn 0.8s ease-out;
  }
  
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
  
  /* Sidebar */
  .sidebar {
    width: 240px;
    background: linear-gradient(145deg, #112240 0%, #335d92 100%);
    height: 100vh;
    padding: 20px 15px;
    position: fixed;
    top: 60px;
    left: 0;
    transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    overflow-y: auto;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.15);
    z-index: 10;
  }
  
  .sidebar.collapsed {
    width: 60px;
    padding: 20px 10px;
  }
  
  /* Navigation */
  .nav-item {
    margin-bottom: 12px;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
  }
  
  .nav-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(3px);
  }
  
  .nav-link {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 14px;
    border-radius: 6px;
    transition: all 0.3s ease;
    position: relative;
  }
  
  .nav-link i {
    margin-right: 12px;
    font-size: 18px;
    min-width: 22px;
    text-align: center;
    transition: all 0.3s ease;
  }
  
  .nav-link.router-link-active {
    background-color: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .nav-link.router-link-active i {
    color: #ffffff;
    transform: scale(1.1);
  }
  
  .nav-link.router-link-active::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: #ffffff;
    border-radius: 0 2px 2px 0;
  }
  
  /* Sidebar collapsed: Hide text */
  .sidebar.collapsed .nav-link span {
    opacity: 0;
    width: 0;
    white-space: nowrap;
    transition: opacity 0.2s ease, width 0.2s ease;
  }
  
  .sidebar:not(.collapsed) .nav-link span {
    opacity: 1;
    width: auto;
    transition: opacity 0.4s ease 0.1s;
  }
  
  /* Sidebar Toggle Button */
  .sidebar-toggle-button {
    position: absolute;
    bottom: 120px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    background-color: #0a192f;
    color: white;
    border: none;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
  }
  
  .sidebar-toggle-button i {
    font-size: 20px;
    transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
  }
  
  .sidebar-toggle-button.is-active i {
    transform: rotate(180deg);
  }
  
  .sidebar-toggle-button:hover {
    background-color: #112240;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
    transform: translateX(-50%) scale(1.05);
  }
  
  /* Submenu Styles */
  .submenu {
    list-style: none;
    padding-left: 20px;
    transition: all 0.4s ease;
  }
  
  .submenu .nav-item {
    margin-bottom: 8px;
  }
  
  .submenu .nav-link {
    padding: 10px;
    font-size: 14px;
  }
  
  /* Transition for submenu using Vue's transition */
  .submenu-fade-enter-active, .submenu-fade-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
  }
  .submenu-fade-enter, .submenu-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
  }
  
  /* Main Content */
  .main-content {
    margin-left: 240px;
    padding: 60px 0px;
    transition: margin-left 0.5s cubic-bezier(0.25, 1, 0.5, 1);
  }
  
  .sidebar.collapsed ~ .main-content {
    margin-left: 60px;
  }
  
  /* Additional Animations */
  /* Logout Button Hover */
  .logout-btn {
    transition: background 0.3s ease, transform 0.3s ease;
  }
  .logout-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.05);
  }
  
  /* Header Icon Hover */
  .custom-header .icon {
    transition: transform 0.3s ease;
  }
  .custom-header .icon:hover {
    transform: rotate(20deg);
  }
  </style>
  