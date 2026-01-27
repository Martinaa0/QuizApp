<template>
  <form @submit.prevent="handleSubmit">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Name *</label>
        <input
          type="text"
          class="form-control"
          id="name"
          v-model="form.name"
          required
          placeholder="Enter full name"
        />
      </div>

      <div class="col-md-6 mb-3">
        <label for="username" class="form-label">Username *</label>
        <input
          type="text"
          class="form-control"
          id="username"
          v-model="form.username"
          required
          placeholder="Enter username"
        />
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Email *</label>
        <input
          type="email"
          class="form-control"
          id="email"
          v-model="form.email"
          required
          placeholder="Enter email address"
        />
      </div>

      <div class="col-md-6 mb-3">
        <label for="user_type" class="form-label">User Type *</label>
        <select class="form-select" id="user_type" v-model="form.user_type" required>
          <option value="student">Student</option>
          <option value="teacher">Teacher</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">
        {{ editing ? 'New Password (leave empty to keep current)' : 'Password *' }}
      </label>
      <input
        type="password"
        class="form-control"
        id="password"
        v-model="form.password"
        :required="!editing"
        :placeholder="editing ? 'Enter new password (optional)' : 'Enter password (min 8 characters)'"
      />
      <div class="form-text">
        {{ editing ? 'Leave empty if you don\'t want to change the password.' : 'Minimum 8 characters required.' }}
      </div>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div class="d-flex justify-content-end">
      <button type="button" class="btn btn-secondary me-2" @click="cancel">
        Cancel
      </button>
      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Saving...' : (editing ? 'Update User' : 'Create User') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['saved', 'cancelled'])

const form = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  user_type: 'student',
})

const loading = ref(false)
const error = ref(null)

const editing = computed(() => !!props.user)

watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      form.value = {
        name: newUser.name || '',
        username: newUser.username || '',
        email: newUser.email || '',
        password: '',
        user_type: newUser.user_type || 'student',
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

const resetForm = () => {
  form.value = {
    name: '',
    username: '',
    email: '',
    password: '',
    user_type: 'student',
  }
  error.value = null
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    const data = { ...form.value }

    // Ako editamo i password je prazan, ne šalji ga
    if (editing.value && !data.password) {
      delete data.password
    }

    let response
    if (editing.value) {
      response = await api.put(`/admin/users/${props.user.id}`, data)
    } else {
      response = await api.post('/admin/users', data)
    }

    emit('saved', response.data.user)
    resetForm()
  } catch (err) {
    console.error('Error saving user:', err)
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err.response?.data?.message || 'Failed to save user. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const cancel = () => {
  resetForm()
  emit('cancelled')
}
</script>

<style scoped>
/* Add any specific styles here */
</style>
