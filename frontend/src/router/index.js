import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import QuizList from '../views/QuizList.vue'
import QuizDetail from '../views/QuizDetail.vue'
import QuizTaking from '../views/QuizTaking.vue'
import UserManagement from '../views/Admin/UserManagement.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true },
  },
  {
    path: '/quizzes',
    name: 'QuizList',
    component: QuizList,
  },
  {
    path: '/quizzes/create',
    name: 'QuizCreate',
    component: () => import('../views/QuizCreate.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/quizzes/:id',
    name: 'QuizDetail',
    component: QuizDetail,
  },
  {
    path: '/quizzes/:id/take',
    name: 'QuizTaking',
    component: QuizTaking,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/users',
    name: 'UserManagement',
    component: UserManagement,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard za zaštićene rute
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const isAuthenticated = !!token

  // Ako ruta zahtijeva gosta (login/register) i korisnik je već prijavljen
  if (to.meta.requiresGuest && isAuthenticated) {
    next({ name: 'Home' })
    return
  }

  // Ako ruta zahtijeva autentifikaciju
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
    return
  }

  // Ako ruta zahtijeva admin pristup
  if (to.meta.requiresAdmin && isAuthenticated) {
    try {
      const userStr = localStorage.getItem('user')
      const user = userStr ? JSON.parse(userStr) : null
      
      if (!user || user.user_type !== 'admin') {
        // Fetch current user to check role
        const response = await fetch('http://localhost:8000/api/user', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        })
        
        if (response.ok) {
          const currentUser = await response.json()
          if (currentUser.user_type !== 'admin') {
            alert('You do not have permission to access this page.')
            next({ name: 'QuizList' })
            return
          }
        } else {
          next({ name: 'Login' })
          return
        }
      }
    } catch (error) {
      console.error('Error checking admin access:', error)
      next({ name: 'Login' })
      return
    }
  }

  next()
})

export default router
