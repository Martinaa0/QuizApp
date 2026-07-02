<template>
  <div class="admin-login-page">
    <div class="admin-login-card qa-card elevated">
      <div style="text-align:center; margin-bottom:28px;">
        <div style="width:48px; height:48px; border-radius:14px; background:var(--dark); display:inline-flex; align-items:center; justify-content:center; color:var(--dark-text); font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:24px; margin-bottom:14px;">Q</div>
        <h1 style="font-size:24px; margin-bottom:6px;">Admin portal</h1>
        <p style="color:var(--faint); font-size:13px;">Pristup samo za administratore</p>
      </div>

      <form @submit.prevent="handleLogin">
        <label class="qa-label">Email</label>
        <input type="email" class="qa-input" style="margin-bottom:14px;" v-model="form.email" required autocomplete="email" />

        <label class="qa-label">Lozinka</label>
        <input type="password" class="qa-input" style="margin-bottom:18px;" v-model="form.password" required autocomplete="current-password" />

        <div v-if="error" style="background:#ffe9e2; color:var(--danger-text); padding:12px 14px; border-radius:10px; font-size:14px; font-weight:700; margin-bottom:16px;">{{ error }}</div>

        <button type="submit" class="btn-mint" style="width:100%; padding:14px; font-size:15px; border-radius:12px; background:var(--dark);" :disabled="loading">
          {{ loading ? 'Autentifikacija...' : 'Prijava kao Admin' }}
        </button>
      </form>

      <div style="text-align:center; margin-top:22px;">
        <router-link to="/" style="font-size:13px; color:var(--faint); font-weight:700;">← Natrag na početnu</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null
  if (!form.value.email || !form.value.password) { error.value = 'Molimo ispunite sva polja.'; loading.value = false; return }
  try {
    const r = await api.post('/admin/login', { email: form.value.email.trim(), password: form.value.password })
    const { token, user } = r.data
    if (!['admin', 'super_admin'].includes(user.user_type)) { error.value = 'Pristup odbijen. Samo za administratore.'; loading.value = false; return }
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    window.dispatchEvent(new Event('user-updated'))
    router.push({ name: 'UserManagement' })
  } catch (err) {
    if (err.response?.data?.errors) error.value = Object.values(err.response.data.errors).flat().join(', ')
    else if (err.response?.data?.message) error.value = err.response.data.message
    else error.value = 'Prijava nije uspjela.'
  } finally { loading.value = false }
}
</script>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
  background: var(--dark);
}
.admin-login-card {
  width: 100%;
  max-width: 400px;
  padding: 38px;
  border-radius: 20px;
}
</style>
