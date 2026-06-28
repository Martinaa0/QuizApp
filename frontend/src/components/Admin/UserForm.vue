<template>
  <form @submit.prevent="handleSubmit">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Ime *</label>
        <input
          type="text"
          class="form-control"
          id="name"
          v-model="form.name"
          required
          placeholder="Unesite puno ime"
        />
      </div>

      <div class="col-md-6 mb-3">
        <label for="username" class="form-label">Korisničko ime *</label>
        <input
          type="text"
          class="form-control"
          id="username"
          v-model="form.username"
          required
          placeholder="Unesite korisničko ime"
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
          placeholder="Unesite email adresu"
        />
      </div>

      <div class="col-md-6 mb-3">
        <label for="user_type" class="form-label">Uloga korisnika *</label>
        <select class="form-select" id="user_type" v-model="form.user_type" required>
          <option value="student">Student</option>
          <option value="teacher">Profesor</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">
        {{ editing ? 'Nova lozinka (ostavite prazno za zadržavanje trenutne)' : 'Lozinka *' }}
      </label>
      <input
        type="password"
        class="form-control"
        id="password"
        v-model="form.password"
        :required="!editing"
        :placeholder="editing ? 'Unesite novu lozinku (neobavezno)' : 'Unesite lozinku (min. 8 znakova)'"
      />
      <div class="form-text">
        {{ editing ? 'Ostavite prazno ako ne želite promijeniti lozinku.' : 'Potrebno je najmanje 8 znakova.' }}
      </div>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div class="d-flex justify-content-end">
      <button type="button" class="btn btn-secondary me-2" @click="cancel">
        Odustani
      </button>
      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Spremanje...' : (editing ? 'Ažuriraj korisnika' : 'Kreiraj korisnika') }}
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
      error.value = err.response?.data?.message || 'Spremanje korisnika nije uspjelo. Pokušajte ponovo.'
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
