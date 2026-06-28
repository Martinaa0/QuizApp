import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Custom design system styles
import './assets/styles/main.css'
import './assets/styles/print.css'

const app = createApp(App)
app.use(router)
app.mount('#app')
