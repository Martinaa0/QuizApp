import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Bootstrap i Bootswatch
import 'bootswatch/dist/minty/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// jQuery (jQuery UI će se učitati dinamički gdje je potrebno)
import $ from 'jquery'
window.$ = window.jQuery = $

// Custom styles
import './assets/styles/main.css'
import './assets/styles/print.css'

// TinyMCE (lazy load se može koristiti u komponenti)

const app = createApp(App)
app.use(router)
app.mount('#app')
