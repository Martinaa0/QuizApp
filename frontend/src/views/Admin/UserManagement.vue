<template>
  <div class="admin-page">
    <div class="admin-inner">
      <!-- Header -->
      <div style="margin-bottom:24px;">
        <h1 style="font-size:28px; margin-bottom:4px;">Upravljanje korisnicima</h1>
        <p style="font-size:14px; color:var(--faint); margin:0;">Admin nadzorna ploča · upravljanje računima, ulogama i aktivnošću</p>
      </div>

      <!-- Stat cards -->
      <div class="stat-cards">
        <div class="stat-card qa-card" style="padding:18px;">
          <div class="stat-icon" style="background:#e8f4ef; color:#2f6b5c;">👥</div>
          <div class="eyebrow" style="margin-bottom:4px;">UKUPNO KORISNIKA</div>
          <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:26px;">{{ users.length }}</div>
        </div>
        <div class="stat-card qa-card" style="padding:18px;">
          <div class="stat-icon" style="background:#fdeef0; color:#c5566a;">🎓</div>
          <div class="eyebrow" style="margin-bottom:4px;">PROFESORI</div>
          <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:26px;">{{ teacherCount }}</div>
        </div>
        <div class="stat-card qa-card" style="padding:18px;">
          <div class="stat-icon" style="background:#eef2fd; color:#5b7fd6;">📝</div>
          <div class="eyebrow" style="margin-bottom:4px;">KVIZOVI</div>
          <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:26px;">{{ totalQuizzes }}</div>
        </div>
        <div class="stat-card qa-card" style="padding:18px;">
          <div class="stat-icon" style="background:#fbf2dd; color:#9c7212;">📊</div>
          <div class="eyebrow" style="margin-bottom:4px;">POKUŠAJI (7D)</div>
          <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:26px;">{{ totalAttempts }}</div>
        </div>
      </div>

      <!-- Create/Edit Form -->
      <div v-if="showCreateForm || editingUser" class="qa-card" style="padding:24px; margin-bottom:20px;">
        <h2 style="font-size:17px; margin-bottom:16px;">{{ editingUser ? 'Uredi korisnika' : 'Kreiraj novog korisnika' }}</h2>
        <UserForm :user="editingUser" @saved="handleUserSaved" @cancelled="cancelForm" />
      </div>

      <!-- Table card -->
      <div class="qa-card" style="overflow:hidden;">
        <!-- Toolbar -->
        <div style="display:flex; gap:12px; align-items:center; padding:16px 20px; flex-wrap:wrap;">
          <div class="filter-search" style="flex:1; min-width:220px;">
            <span style="color:var(--faint2);">⌕</span>
            <input
              type="text"
              class="search-input"
              v-model="searchQuery"
              @input="debouncedSearch"
              placeholder="Pretraži po imenu, korisničkom imenu ili emailu…"
            />
          </div>
          <div style="position:relative;">
            <button class="dropdown-trigger" @click="roleMenuOpen = !roleMenuOpen">
              {{ userTypeFilter || 'Sve uloge' }}
              <span style="color:var(--faint); font-size:12px;">▾</span>
            </button>
            <div v-if="roleMenuOpen" class="dropdown-menu-custom" style="right:0; left:auto; min-width:160px;">
              <div class="dropdown-item" :class="{ active: !userTypeFilter }" @click="selectRole('')">Sve uloge</div>
              <div class="dropdown-item" :class="{ active: userTypeFilter === 'super_admin' }" @click="selectRole('super_admin')">Super Admin</div>
              <div class="dropdown-item" :class="{ active: userTypeFilter === 'admin' }" @click="selectRole('admin')">Admin</div>
              <div class="dropdown-item" :class="{ active: userTypeFilter === 'teacher' }" @click="selectRole('teacher')">Profesor</div>
              <div class="dropdown-item" :class="{ active: userTypeFilter === 'student' }" @click="selectRole('student')">Student</div>
            </div>
          </div>
          <button class="btn-mint" style="font-size:13.5px; padding:10px 16px;" @click="showCreateForm = true" v-if="!showCreateForm && !editingUser">+ Dodaj korisnika</button>
        </div>

        <!-- Table -->
        <div v-if="loading" style="text-align:center; padding:40px;">
          <div style="width:32px; height:32px; border:3px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
        </div>
        <div v-else-if="users.length === 0" style="text-align:center; padding:40px; color:var(--faint);">Nema pronađenih korisnika.</div>
        <div v-else>
          <div class="user-table">
            <div class="table-header">
              <div style="flex:2.4;">Korisnik</div>
              <div style="flex:1;">Uloga</div>
              <div style="flex:1;">Kvizovi</div>
              <div style="flex:1;">Pokušaji</div>
              <div style="width:80px;"></div>
            </div>
            <div
              v-for="user in users"
              :key="user.id"
              class="table-row"
            >
              <div style="flex:2.4; display:flex; align-items:center; gap:12px; min-width:0;">
                <div class="avatar" :style="{ width:'38px', height:'38px', background: avatarColor(user.name), fontSize:'13px', color:'#fff' }">
                  {{ initials(user.name) }}
                </div>
                <div style="min-width:0;">
                  <div style="font-weight:700; font-size:14px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ user.name }}</div>
                  <div style="font-size:12px; color:var(--faint); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ user.email }}</div>
                </div>
              </div>
              <div style="flex:1;">
                <span class="pill pill-sm" :class="rolePillClass(user.user_type)">{{ user.user_type }}</span>
              </div>
              <div style="flex:1; font-weight:700; font-size:14px;">{{ user.quizzes_count || 0 }}</div>
              <div style="flex:1; font-weight:700; font-size:14px;">{{ user.quiz_attempts_count || 0 }}</div>
              <div style="width:80px; display:flex; gap:6px; justify-content:flex-end;">
                <button class="btn-ghost" style="font-size:12px; padding:4px 8px;" @click="editUser(user)">Uredi</button>
                <button
                  class="btn-danger-text"
                  style="font-size:12px; padding:4px 8px;"
                  @click="deleteUser(user)"
                  :disabled="user.id === currentUserId"
                >Obriši</button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; font-size:13px; color:var(--faint);">
            <span>Prikazano 1–{{ users.length }} od {{ pagination.total || users.length }} korisnika</span>
            <div style="display:flex; gap:4px;">
              <button
                v-for="page in pagination.last_page"
                :key="page"
                class="page-btn"
                :class="{ active: page === pagination.current_page }"
                @click="changePage(page)"
              >{{ page }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import UserForm from '../../components/Admin/UserForm.vue'

const router = useRouter()
const users = ref([])
const loading = ref(true)
const searchQuery = ref('')
const userTypeFilter = ref('')
const showCreateForm = ref(false)
const editingUser = ref(null)
const pagination = ref(null)
const currentUserId = ref(null)
const roleMenuOpen = ref(false)
let searchTimeout = null

const teacherCount = computed(() => users.value.filter(u => u.user_type === 'teacher').length)
const totalQuizzes = computed(() => users.value.reduce((s, u) => s + (u.quizzes_count || 0), 0))
const totalAttempts = computed(() => users.value.reduce((s, u) => s + (u.quiz_attempts_count || 0), 0))

const avatarColors = ['#78c2ad', '#f3969a', '#5b7fd6', '#f5b740', '#56cc9d', '#ff7851']
const avatarColor = (name) => avatarColors[(name || '').length % avatarColors.length]
const initials = (name) => (name || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
const rolePillClass = (t) => {
  if (t === 'super_admin') return 'pill-super-admin'
  if (t === 'admin') return 'pill-admin'
  if (t === 'teacher') return 'pill-teacher'
  return 'pill-student'
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchUsers, 500)
}

const selectRole = (role) => {
  userTypeFilter.value = role
  roleMenuOpen.value = false
  fetchUsers()
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (userTypeFilter.value) params.append('user_type', userTypeFilter.value)
    const qs = params.toString()
    const r = await api.get(qs ? `/admin/users?${qs}` : '/admin/users')
    if (r.data.data) {
      users.value = r.data.data
      pagination.value = { current_page: r.data.current_page, last_page: r.data.last_page, total: r.data.total, prev_page_url: r.data.prev_page_url, next_page_url: r.data.next_page_url }
    } else {
      users.value = r.data
      pagination.value = null
    }
  } catch (e) {
    if (e.response?.status === 403) { router.push('/nemate-pristup') }
    users.value = []
  } finally { loading.value = false }
}

const changePage = (page) => fetchUsers()
const editUser = (user) => { editingUser.value = { ...user }; showCreateForm.value = false }
const deleteUser = async (user) => {
  if (user.id === currentUserId.value) { alert("Ne možete obrisati sami sebe."); return }
  if (!confirm(`Obrisati "${user.name}"?`)) return
  try { await api.delete(`/admin/users/${user.id}`); await fetchUsers() } catch {}
}
const handleUserSaved = async () => { editingUser.value = null; showCreateForm.value = false; await fetchUsers() }
const cancelForm = () => { editingUser.value = null; showCreateForm.value = false }

onMounted(async () => {
  try { const r = await api.get('/user'); currentUserId.value = r.data.id } catch {}
  await fetchUsers()
})
</script>

<style scoped>
.admin-page { min-height: 70vh; }
.admin-inner { max-width: 1160px; margin: 0 auto; padding: 26px 28px 64px; }

.stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-card { display: flex; flex-direction: column; }
.stat-icon {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  margin-bottom: 12px;
}

.filter-search {
  display: flex;
  align-items: center;
  gap: 9px;
  background: var(--page-bg);
  border-radius: 10px;
  padding: 10px 13px;
}
.search-input {
  border: none; outline: none; background: transparent;
  flex: 1; min-width: 0;
  font-family: 'Lato', sans-serif; font-size: 14px; color: var(--body-text);
}
.search-input::placeholder { color: var(--faint2); }

.dropdown-trigger {
  display: flex; align-items: center; gap: 8px;
  border: 1px solid var(--line); background: var(--surface);
  border-radius: 10px; padding: 10px 14px;
  font-family: 'Lato', sans-serif; font-size: 13.5px; font-weight: 700;
  color: var(--body-text); cursor: pointer; white-space: nowrap;
}
.dropdown-menu-custom {
  position: absolute; top: calc(100% + 6px);
  min-width: 160px; background: var(--surface);
  border: 1px solid var(--line); border-radius: 12px;
  box-shadow: var(--shadow-dropdown); padding: 6px; z-index: 50;
}
.dropdown-item {
  padding: 9px 12px; border-radius: 8px;
  font-size: 14px; font-weight: 700; color: var(--body-text);
  cursor: pointer; transition: background .1s;
}
.dropdown-item:hover { background: var(--page-bg); }
.dropdown-item.active { background: var(--mint-soft); color: #2f6b5c; }

.user-table { border-top: 1px solid var(--line); }
.table-header {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  background: #fafcfb;
  font-size: 12px;
  font-weight: 700;
  color: var(--faint);
  text-transform: uppercase;
  letter-spacing: .06em;
}
.table-row {
  display: flex;
  align-items: center;
  padding: 14px 20px;
  border-top: 1px solid var(--line);
  transition: background .1s;
}
.table-row:hover { background: var(--page-bg); }

.page-btn {
  width: 32px; height: 32px;
  border-radius: 8px;
  border: 1px solid var(--line);
  background: var(--surface);
  font-family: 'Lato', sans-serif;
  font-weight: 700;
  font-size: 13px;
  color: var(--muted);
  cursor: pointer;
  transition: all .15s;
}
.page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 880px) {
  .stat-cards { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .stat-cards { grid-template-columns: 1fr; }
  .table-header { display: none; }
  .table-row { flex-wrap: wrap; gap: 8px; }
}
</style>
