import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import Login from '../components/Login.vue'
import CreateInvoiceLayout from '../components/CreateInvoiceLayout.vue'
import InvoiceDetails from '../components/Invoice_Details.vue'
import SellerDetails from '../components/Seller_Details.vue'
import BuyerDetails from '../components/Buyer_Details.vue'
import DocumentTotals from '../components/Document_Totals.vue'
import TheInvoice from '../components/TheInvoice.vue'
import AllInvoices from '../components/AllInvoices.vue'
import redirect from '../components/redirect.vue'
import { cos } from 'three/tsl'


const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: true }
  },
  {
    path: '/redirect',
    name: 'path',
    component: redirect,
    meta: { requiresAuth: true }
  },
  // CREATE NEW INVOICE
  {
    path: '/create-invoice',
    name: 'CreateInvoice',
    component: CreateInvoiceLayout,
    meta: { requiresAuth: true },
      children: [
      { path: '', redirect: 'invoice-details' },
      {
        path: 'invoice-details',
        name: 'InvoiceDetails',
        component: InvoiceDetails
      },
      {
        path: 'seller-details',
        name: 'SellerDetails',
        component: SellerDetails
      },
      {
        path: 'buyer-details',
        name: 'BuyerDetails',
        component: BuyerDetails
      },
      {
        path: 'document-totals',
        name: 'DocumentTotals',
        component: DocumentTotals
      }
        ]
      },
  // EDIT EXISTING INVOICE
  {
    path: '/invoice/:id/edit',
    name: 'EditInvoice',
    component: CreateInvoiceLayout,
    meta: { requiresAuth: true },
    beforeEnter(to, from, next) {
      const { id } = to.params
      if (to.path === `/invoice/${id}/edit`) {
        next(`/invoice/${id}/edit/invoice-details`)
      } else {
        next()
      }
    },
    children: [
      {
        path: 'invoice-details',
        name: 'EditInvoiceDetails',
        component: InvoiceDetails
      },
      {
        path: 'seller-details',
        name: 'EditSellerDetails',
        component: SellerDetails
      },
      {
        path: 'buyer-details',
        name: 'EditBuyerDetails',
        component: BuyerDetails
      },
      {
        path: 'document-totals',
        name: 'EditDocumentTotals',
        component: DocumentTotals
      }
    ]
  },
  // PREVIEW
  {
    path: '/invoice-preview/:id',
    name: 'InvoicePreview',
    component: TheInvoice,
    meta: { requiresAuth: true }
  },
  // INVOICE LIST
  {
    path: '/all_invoice',
    name: 'AllInvoices',
    component: AllInvoices,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Simple auth guard
router.beforeEach((to, from, next) => {
  const isAuth = !!localStorage.getItem('auth_token')
  if (to.meta.requiresAuth && !isAuth) {
    // next('/login')
    next()
  } else {
    next()
  }
})

export default router
