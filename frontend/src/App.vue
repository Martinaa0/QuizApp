<script setup>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from './services/api'

const router = useRouter()
const route = useRoute()
const authToken = ref(localStorage.getItem('auth_token'))
const userData = ref(localStorage.getItem('user'))
const mobileMenuOpen = ref(false)

window.addEventListener('storage', () => {
  authToken.value = localStorage.getItem('auth_token')
  userData.value = localStorage.getItem('user')
})
window.addEventListener('user-updated', () => {
  authToken.value = localStorage.getItem('auth_token')
  userData.value = localStorage.getItem('user')
})

const isAuthenticated = computed(() => !!authToken.value)
const user = computed(() => userData.value ? JSON.parse(userData.value) : null)
const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const showAppBar = computed(() => {
  const guestRoutes = ['Home', 'Login', 'Register', 'AdminLogin']
  return !guestRoutes.includes(route.name)
})

const handleLogout = async () => {
  try { await api.post('/logout') } catch {}
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  authToken.value = null
  userData.value = null
  window.dispatchEvent(new Event('user-updated'))
  router.push({ name: 'Home' })
}
</script>

<template>
  <div id="app">
    <!-- Dark top app bar (logged-in screens) -->
    <header v-if="showAppBar" class="app-bar">
      <div class="app-bar-inner">
        <router-link to="/" class="app-bar-brand">
          <div class="brand-icon">Q</div>
          <span class="brand-text">QuizApp</span>
        </router-link>

        <nav class="app-bar-nav" :class="{ open: mobileMenuOpen }">
          <router-link to="/quizzes" class="nav-pill" :class="{ active: route.name === 'QuizList' }">Pregled</router-link>
          <router-link v-if="isAuthenticated" to="/quizzes/create" class="nav-pill" :class="{ active: route.name === 'QuizCreate' }">Kreiraj</router-link>
          <router-link v-if="isAuthenticated" to="/lobby" class="nav-pill" :class="{ active: route.name === 'Lobby' || route.name === 'MultiplayerGame' }">Multiplayer</router-link>
          <router-link v-if="user?.user_type === 'admin'" to="/admin/users" class="nav-pill" :class="{ active: route.name === 'UserManagement' }">Admin</router-link>
        </nav>

        <div class="app-bar-spacer"></div>

        <div class="app-bar-right">
          <div v-if="isAuthenticated" class="app-bar-user" @click="handleLogout" title="Odjava">
            <div class="user-avatar" :style="{ background: 'var(--accent2)' }">{{ userInitials }}</div>
          </div>
          <router-link v-else to="/login" class="nav-pill">Prijava</router-link>
        </div>

        <button class="mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen">
          <span></span><span></span><span></span>
        </button>
      </div>
    </header>

    <main>
      <router-view />
    </main>
  </div>
</template>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
main {
  flex: 1;
}

/* ===== App Bar ===== */
.app-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  height: 60px;
  background: var(--dark);
}
.app-bar-inner {
  height: 100%;
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 0 26px;
  max-width: 100%;
}
.app-bar-brand {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  flex-shrink: 0;
}
.brand-icon {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #13241f;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 15px;
}
.brand-text {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 600;
  font-size: 16px;
  color: var(--dark-text);
}
.app-bar-nav {
  display: flex;
  gap: 4px;
  margin-left: 8px;
}
.nav-pill {
  font-size: 13.5px;
  font-weight: 700;
  padding: 7px 12px;
  border-radius: 8px;
  color: #a9c4bd;
  text-decoration: none;
  transition: background .15s, color .15s;
  white-space: nowrap;
}
.nav-pill:hover {
  color: #fff;
  background: rgba(255,255,255,.08);
}
.nav-pill.active {
  background: rgba(255,255,255,.12);
  color: #fff;
}
.app-bar-spacer { flex: 1; }
.app-bar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}
.app-bar-user {
  cursor: pointer;
}
.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 13px;
  color: #5a1f24;
}
.mobile-toggle {
  display: none;
  flex-direction: column;
  gap: 4px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
}
.mobile-toggle span {
  width: 20px;
  height: 2px;
  background: var(--dark-text);
  border-radius: 2px;
}

@media (max-width: 680px) {
  .app-bar-nav {
    display: none;
    position: absolute;
    top: 60px;
    left: 0;
    right: 0;
    background: var(--dark);
    flex-direction: column;
    padding: 12px 26px 18px;
    gap: 2px;
    border-bottom: 1px solid rgba(255,255,255,.08);
  }
  .app-bar-nav.open { display: flex; }
  .mobile-toggle { display: flex; }
}
</style>
