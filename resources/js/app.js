import './bootstrap'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
import BootstrapVue3 from 'bootstrap-vue-3'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

// Import Bootstrap and BootstrapVue3 CSS files
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)
app.use(BootstrapVue3)
app.mount('#app')
