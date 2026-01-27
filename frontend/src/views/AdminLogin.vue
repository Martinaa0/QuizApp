<template>
  <div class="admin-login">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <h2 class="card-title mb-2">
                  <i class="bi bi-shield-lock-fill text-danger"></i>
                  Admin Portal
                </h2>
                <p class="text-muted small">Administrator access only</p>
              </div>
              <form @submit.prevent="handleLogin">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="form.email"
                    required
                    autocomplete="email"
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
                    autocomplete="current-password"
                  />
                </div>
                <div v-if="error" class="alert alert-danger" role="alert">
                  {{ error }}
                </div>
                <button type="submit" class="btn btn-danger w-100" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ loading ? 'Authenticating...' : 'Login as Admin' }}
                </button>
              </form>
              <div class="text-center mt-4">
                <router-link to="/" class="text-muted small text-decoration-none">
                  <i class="bi bi-arrow-left"></i> Back to Home
                </router-link>
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
    const response = await api.post('/admin/login', {
      email: form.value.email.trim(),
      password: form.value.password,
    })
    const { token, user } = response.data

    // Provjeri da li je stvarno admin
    if (user.user_type !== 'admin') {
      error.value = 'Access denied. This portal is for administrators only.'
      loading.value = false
      return
    }

    // Spremi token i korisnika
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    
    // Emit event za App.vue da osvježi user data
    window.dispatchEvent(new Event('user-updated'))

    // Preusmjeri na admin dashboard ili home
    router.push({ name: 'UserManagement' })
  } catch (err) {
    console.error('Admin login error:', err)
    
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
.admin-login {
  min-height: 70vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.card {
  border-radius: 15px;
}

.card-title {
  font-weight: 700;
  font-size: 1.75rem;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
  font-weight: 600;
  padding: 0.75rem;
}

.btn-danger:hover {
  background-color: #c82333;
  border-color: #bd2130;
}

.form-control:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
