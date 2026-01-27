<template>
  <div class="login">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h2 class="card-title text-center mb-4">Login</h2>
              <form @submit.prevent="handleLogin">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="form.email"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    v-model="form.password"
                    required
                  />
                </div>
                <div v-if="error" class="alert alert-danger" role="alert">
                  {{ error }}
                </div>
                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                  {{ loading ? 'Logging in...' : 'Login' }}
                </button>
              </form>
              <div class="text-center mt-3">
                <p>
                  Don't have an account?
                  <router-link to="/register">Register here</router-link>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const form = ref({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null

  // Provjeri da li su polja popunjena
  if (!form.value.email || !form.value.password) {
    error.value = 'Please fill in all fields.'
    loading.value = false
    return
  }

  try {
    console.log('Sending login request with:', { email: form.value.email, password: '***' })
    const response = await api.post('/login', {
      email: form.value.email.trim(),
      password: form.value.password,
    })
    const { token, user } = response.data

    // Spremi token i korisnika
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    
    // Emit event za App.vue da osvježi user data
    window.dispatchEvent(new Event('user-updated'))

    // Preusmjeri na home
    router.push({ name: 'Home' })
  } catch (err) {
    console.error('Login error:', err)
    console.error('Error response:', err.response?.data)
    console.error('Request payload:', form.value)
    
    if (err.response?.data?.errors) {
      // Prikaži validacijske greške
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Login failed. Please check your credentials and try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login {
  min-height: 60vh;
  display: flex;
  align-items: center;
}
</style>
