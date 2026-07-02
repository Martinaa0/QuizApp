import axios from 'axios'

// Kreiraj axios instancu s osnovnom konfiguracijom
const api = axios.create({
  baseURL: 'http://pzi072026.studenti.sum.ba/backend/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Interceptor za dodavanje tokena u zahtjeve
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor za rukovanje greškama
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const requestUrl = error.config?.url || ''
    const isLoginRequest = requestUrl.includes('/login') || requestUrl.includes('/register')
    if (error.response?.status === 401 && !isLoginRequest) {
      // Token je istekao ili nije valjan
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
