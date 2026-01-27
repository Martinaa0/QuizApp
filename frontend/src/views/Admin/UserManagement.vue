<template>
  <div class="user-management">
    <div class="container mt-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Management</h1>
        <button
          class="btn btn-success"
          @click="showCreateForm = true"
          v-if="!showCreateForm && !editingUser"
        >
          + Add New User
        </button>
      </div>

      <!-- Create/Edit Form -->
      <div v-if="showCreateForm || editingUser" class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">
            {{ editingUser ? 'Edit User' : 'Create New User' }}
          </h5>
        </div>
        <div class="card-body">
          <UserForm
            :user="editingUser"
            @saved="handleUserSaved"
            @cancelled="cancelForm"
          />
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="search" class="form-label">Search Users</label>
              <div class="input-group">
                <span class="input-group-text">🔍</span>
                <input
                  type="text"
                  class="form-control"
                  id="search"
                  v-model="searchQuery"
                  @input="debouncedSearch"
                  placeholder="Search by name, username, or email..."
                />
                <button
                  v-if="searchQuery"
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="clearSearch"
                >
                  Clear
                </button>
              </div>
            </div>
            <div class="col-md-3">
              <label for="user-type-filter" class="form-label">User Type</label>
              <select
                class="form-select"
                id="user-type-filter"
                v-model="userTypeFilter"
                @change="fetchUsers"
              >
                <option value="">All Types</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-outline-secondary w-100" @click="resetFilters">
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="card-body">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <div v-else-if="users.length === 0" class="text-center py-5">
            <p class="text-muted">No users found.</p>
          </div>

          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Quizzes</th>
                  <th>Attempts</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.username }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span
                      class="badge"
                      :class="{
                        'bg-danger': user.user_type === 'admin',
                        'bg-warning text-dark': user.user_type === 'teacher',
                        'bg-info': user.user_type === 'student',
                      }"
                    >
                      {{ user.user_type }}
                    </span>
                  </td>
                  <td>{{ user.quizzes_count || 0 }}</td>
                  <td>{{ user.quiz_attempts_count || 0 }}</td>
                  <td>{{ formatDate(user.created_at) }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button
                        class="btn btn-outline-primary"
                        @click="editUser(user)"
                        title="Edit"
                      >
                        ✏️
                      </button>
                      <button
                        class="btn btn-outline-danger"
                        @click="deleteUser(user)"
                        :disabled="user.id === currentUserId"
                        :title="user.id === currentUserId ? 'Cannot delete yourself' : 'Delete'"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="mt-4">
            <nav>
              <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                  <button
                    class="page-link"
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                  >
                    Previous
                  </button>
                </li>
                <li
                  v-for="page in pagination.last_page"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === pagination.current_page }"
                >
                  <button class="page-link" @click="changePage(page)">
                    {{ page }}
                  </button>
                </li>
                <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                  <button
                    class="page-link"
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="!pagination.next_page_url"
                  >
                    Next
                  </button>
                </li>
              </ul>
            </nav>
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

let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchUsers()
  }, 500)
}

const clearSearch = () => {
  searchQuery.value = ''
  fetchUsers()
}

const resetFilters = () => {
  searchQuery.value = ''
  userTypeFilter.value = ''
  fetchUsers()
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }
    if (userTypeFilter.value) {
      params.append('user_type', userTypeFilter.value)
    }

    const queryString = params.toString()
    const url = queryString ? `/admin/users?${queryString}` : '/admin/users'
    const response = await api.get(url)

    if (response.data.data) {
      // Paginated response
      users.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        prev_page_url: response.data.prev_page_url,
        next_page_url: response.data.next_page_url,
      }
    } else {
      // Simple array response
      users.value = response.data
      pagination.value = null
    }
  } catch (error) {
    console.error('Error fetching users:', error)
    if (error.response?.status === 403) {
      alert('You do not have permission to access this page.')
      router.push('/quizzes')
    }
    users.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  fetchUsers()
}

const editUser = (user) => {
  editingUser.value = { ...user }
  showCreateForm.value = false
  // Scroll to form
  setTimeout(() => {
    const form = document.querySelector('.user-management')
    if (form) {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }, 100)
}

const deleteUser = async (user) => {
  if (user.id === currentUserId.value) {
    alert('You cannot delete your own account.')
    return
  }

  if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) {
    return
  }

  try {
    await api.delete(`/admin/users/${user.id}`)
    await fetchUsers()
  } catch (error) {
    console.error('Error deleting user:', error)
    alert('Failed to delete user. Please try again.')
  }
}

const handleUserSaved = async () => {
  editingUser.value = null
  showCreateForm.value = false
  await fetchUsers()
}

const cancelForm = () => {
  editingUser.value = null
  showCreateForm.value = false
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const fetchCurrentUser = async () => {
  try {
    const response = await api.get('/user')
    currentUserId.value = response.data.id
  } catch (error) {
    console.error('Error fetching current user:', error)
  }
}

onMounted(async () => {
  await fetchCurrentUser()
  await fetchUsers()
})
</script>

<style scoped>
.user-management {
  min-height: 70vh;
}
</style>
