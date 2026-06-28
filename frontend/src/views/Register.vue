<template>
  <div class="register-page">
    <div class="register-card qa-card elevated">
      <div class="register-header">
        <router-link to="/" style="text-decoration:none; display:flex; align-items:center; gap:9px;">
          <div style="width:30px; height:30px; border-radius:9px; background:var(--accent); display:flex; align-items:center; justify-content:center; color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700;">Q</div>
          <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:18px; color:var(--ink);">Kreirajte svoj račun</span>
        </router-link>
      </div>
      <p class="register-sub">Zauvijek besplatno za studente. Nije potrebna kartica.</p>

      <form @submit.prevent="handleRegister">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
          <div>
            <label class="qa-label">Puno ime</label>
            <input type="text" class="qa-input" v-model="form.name" placeholder="Alex Morgan" required />
          </div>
          <div>
            <label class="qa-label">Korisničko ime</label>
            <input type="text" class="qa-input" v-model="form.username" placeholder="@alexm" required />
          </div>
        </div>

        <label class="qa-label">Email</label>
        <input type="email" class="qa-input" style="margin-bottom:14px;" v-model="form.email" placeholder="alex@primjer.com" required />

        <label class="qa-label">Ja sam…</label>
        <div class="role-picker">
          <button
            type="button"
            class="role-option"
            :class="{ active: form.user_type === 'student' }"
            @click="form.user_type = 'student'"
          >Student</button>
          <button
            type="button"
            class="role-option"
            :class="{ active: form.user_type === 'teacher' }"
            @click="form.user_type = 'teacher'"
          >Profesor</button>
        </div>

        <label class="qa-label">Lozinka</label>
        <input type="password" class="qa-input" style="margin-bottom:6px;" v-model="form.password" placeholder="••••••••" required />
        <input type="password" class="qa-input" style="margin-bottom:22px;" v-model="form.password_confirmation" placeholder="Potvrdite lozinku" required />

        <div v-if="error" style="background:#ffe9e2; color:var(--danger-text); padding:12px 14px; border-radius:10px; font-size:14px; font-weight:700; margin-bottom:16px;">
          {{ error }}
        </div>

        <button type="submit" class="btn-mint" style="width:100%; padding:14px; font-size:15px; border-radius:12px;" :disabled="loading">
          {{ loading ? 'Kreiranje...' : 'Kreiraj račun' }}
        </button>
      </form>

      <p style="text-align:center; margin-top:22px; font-size:14px; color:var(--muted);">
        Već imate račun? <router-link to="/login" style="color:var(--accent); font-weight:700;">Prijava</router-link>
      </p>
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
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    window.dispatchEvent(new Event('user-updated'))
    router.push({ name: 'QuizList' })
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(', ')
    } else {
      error.value = err.response?.data?.message || 'Registracija nije uspjela.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  background: var(--page-bg-alt);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
}
.register-card {
  width: 100%;
  max-width: 460px;
  padding: 38px;
  border-radius: 20px;
}
.register-header { margin-bottom: 24px; }
.register-sub {
  color: var(--muted);
  font-size: 15px;
  margin: 0 0 26px;
}
.role-picker {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 16px;
}
.role-option {
  border: 1px solid var(--input-border);
  border-radius: 12px;
  padding: 13px;
  text-align: center;
  font-weight: 700;
  color: var(--muted);
  background: var(--surface);
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  font-size: 15px;
  transition: all .15s;
}
.role-option.active {
  border: 2px solid var(--accent);
  background: var(--mint-soft2);
  color: #2f6b5c;
}
</style>
