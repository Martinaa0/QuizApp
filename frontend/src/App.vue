<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('auth_token')
})

const user = computed(() => {
  const userStr = localStorage.getItem('user')
  return userStr ? JSON.parse(userStr) : null
})

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token')
    if (token) {
      await fetch('http://localhost:8000/api/logout', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      })
    }
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    router.push({ name: 'Home' })
  }
}
</script>

<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <router-link class="navbar-brand" to="/">Quiz App</router-link>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/">Home</router-link>
            </li>
                  <li class="nav-item">
                    <router-link class="nav-link" to="/quizzes">Quizzes</router-link>
                  </li>
                  <li class="nav-item" v-if="user?.user_type === 'admin'">
                    <router-link class="nav-link" to="/admin/users">User Management</router-link>
                  </li>
                </ul>
          <ul class="navbar-nav">
            <li v-if="isAuthenticated" class="nav-item dropdown">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
              >
                {{ user?.name || 'User' }}
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a>
                </li>
              </ul>
            </li>
            <li v-else class="nav-item">
              <router-link class="nav-link" to="/login">Login</router-link>
            </li>
            <li v-if="!isAuthenticated" class="nav-item">
              <router-link class="nav-link" to="/register">Register</router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      <router-view />
    </main>
    
    <footer class="mt-5">
      <div class="container text-center">
        <p class="mb-0">&copy; 2026 Quiz App. All rights reserved.</p>
      </div>
    </footer>
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
  padding-bottom: 2rem;
}

.navbar-brand {
  font-weight: 700;
  font-size: 1.5rem;
}

.navbar-dark {
  background-color: #343a40 !important;
}
</style>
