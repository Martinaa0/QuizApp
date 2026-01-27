<template>
  <div class="register">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h2 class="card-title text-center mb-4">Register</h2>
              <form @submit.prevent="handleRegister">
                <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    v-model="form.name"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input
                    type="text"
                    class="form-control"
                    id="username"
                    v-model="form.username"
                    required
                  />
                </div>
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
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="user_type" class="form-label">User Type</label>
                  <select class="form-select" id="user_type" v-model="form.user_type">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div v-if="error" class="alert alert-danger" role="alert">
                  {{ error }}
                </div>
                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                  {{ loading ? 'Registering...' : 'Register' }}
                </button>
              </form>
              <div class="text-center mt-3">
                <p>
                  Already have an account?
                  <router-link to="/login">Login here</router-link>
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
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  user_type: 'student',
})

const loading = ref(false)
const error = ref(null)

const handleRegister = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.post('/register', form.value)
    const { token, user } = response.data

    // Spremi token i korisnika
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    
    // Emit event za App.vue da osvježi user data
    window.dispatchEvent(new Event('user-updated'))

    // Preusmjeri na home
    router.push({ name: 'Home' })
  } catch (err) {
    if (err.response?.data?.errors) {
      // Prikaži validacijske greške
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err.response?.data?.message || 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register {
  min-height: 60vh;
  display: flex;
  align-items: center;
}
</style>
