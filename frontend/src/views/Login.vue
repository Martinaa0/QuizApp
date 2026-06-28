<template>
  <div class="login-page">
    <!-- Left: Form -->
    <div class="login-form-side">
      <div class="login-form-inner">
        <router-link to="/" class="login-brand">
          <div class="brand-icon-sm">Q</div>
          <span>QuizApp</span>
        </router-link>

        <h1 class="login-h1">Dobrodošli natrag</h1>
        <p class="login-sub">Prijavite se i nastavite kreirati i igrati.</p>

        <form @submit.prevent="handleLogin">
          <label class="qa-label">Email ili korisničko ime</label>
          <div style="position:relative; margin-bottom:16px;">
            <span class="input-affix">@</span>
            <input
              type="text"
              class="qa-input"
              style="padding-left:38px;"
              v-model="form.email"
              placeholder="vi@primjer.com"
              required
            />
          </div>

          <label class="qa-label">Lozinka</label>
          <div style="position:relative; margin-bottom:10px;">
            <span class="input-affix">•</span>
            <input
              type="password"
              class="qa-input"
              style="padding-left:38px;"
              v-model="form.password"
              placeholder="••••••••"
              required
            />
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
            <label style="font-size:13px; color:var(--muted); cursor:pointer; display:flex; align-items:center; gap:6px;">
              <input type="checkbox" v-model="rememberMe" style="accent-color:var(--accent);" /> Zapamti me
            </label>
            <span style="font-size:13px; font-weight:700; color:var(--accent); cursor:pointer;">Zaboravljena lozinka?</span>
          </div>

          <div v-if="error" class="login-error">{{ error }}</div>

          <button type="submit" class="btn-mint" style="width:100%; padding:14px; font-size:15px; border-radius:12px;" :disabled="loading">
            {{ loading ? 'Prijava u tijeku...' : 'Prijava' }}
          </button>
        </form>

        <p class="login-footer">
          Novi ste ovdje? <router-link to="/register" style="color:var(--accent); font-weight:700;">Kreirajte račun</router-link>
        </p>
        <p style="text-align:center; margin-top:10px; font-size:12.5px; color:var(--faint2);">
          Admin? <router-link to="/admin/login" style="font-weight:700; color:var(--muted);">Admin portal →</router-link>
        </p>
      </div>
    </div>

    <!-- Right: Brand panel -->
    <div class="login-brand-side">
      <div class="brand-circle-1"></div>
      <div class="brand-circle-2"></div>
      <div class="brand-content">
        <div class="brand-quote">"Najbrži način koji sam pronašla za ispitivanje razreda od 30 učenika."</div>
        <p class="brand-body">Ljestvice uživo, trenutno bodovanje i nula pripreme. Učenici samo upišu 6-znakovni kod i već su unutra.</p>
        <div class="brand-attribution">
          <div class="avatar" style="width:42px; height:42px; background:var(--accent2); color:#5a1f24; font-size:14px;">RT</div>
          <div>
            <div style="font-weight:700;">Rosa Tan</div>
            <div style="color:var(--dark-muted); font-size:13px;">Profesorica · Lincoln High</div>
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
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref(null)
const rememberMe = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = null

  if (!form.value.email || !form.value.password) {
    error.value = 'Molimo ispunite sva polja.'
    loading.value = false
    return
  }

  try {
    const response = await api.post('/login', {
      email: form.value.email.trim(),
      password: form.value.password,
    })
    const { token, user } = response.data
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user', JSON.stringify(user))
    window.dispatchEvent(new Event('user-updated'))
    router.push({ name: 'QuizList' })
  } catch (err) {
    if (err.response?.status === 403 && err.response?.data?.message?.includes('admin')) {
      error.value = 'Admin računi moraju koristiti admin portal za prijavu.'
    } else if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Prijava nije uspjela. Provjerite svoje podatke.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.login-form-side {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: var(--surface);
}
.login-form-inner {
  width: 100%;
  max-width: 380px;
}
.login-brand {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-bottom: 34px;
  text-decoration: none;
}
.login-brand .brand-icon-sm {
  width: 30px; height: 30px; border-radius: 9px;
  background: var(--accent);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
}
.login-brand span {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 18px;
  color: var(--ink);
}
.login-h1 {
  font-size: 30px;
  margin-bottom: 8px;
}
.login-sub {
  color: var(--muted);
  font-size: 15px;
  margin: 0 0 28px;
}
.input-affix {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--faint2);
  font-size: 16px;
}
.login-error {
  background: #ffe9e2;
  color: var(--danger-text);
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 16px;
}
.login-footer {
  text-align: center;
  margin-top: 26px;
  font-size: 14px;
  color: var(--muted);
}

/* Brand panel */
.login-brand-side {
  background: var(--dark);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
  overflow: hidden;
}
.brand-circle-1 {
  position: absolute;
  top: -80px; right: -80px;
  width: 280px; height: 280px;
  border-radius: 50%;
  background: rgba(120,194,173,.16);
}
.brand-circle-2 {
  position: absolute;
  bottom: -60px; left: -40px;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: rgba(243,150,154,.12);
}
.brand-content {
  position: relative;
  color: var(--dark-text);
  max-width: 360px;
}
.brand-quote {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 30px;
  line-height: 1.25;
  margin-bottom: 18px;
}
.brand-body {
  color: var(--dark-muted);
  font-size: 15px;
  line-height: 1.6;
}
.brand-attribution {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 26px;
}

@media (max-width: 880px) {
  .login-page { grid-template-columns: 1fr; }
  .login-brand-side { display: none; }
}
</style>
