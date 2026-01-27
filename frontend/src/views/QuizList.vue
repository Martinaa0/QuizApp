<template>
  <div class="quiz-list">
    <div class="container mt-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Quizzes</h1>
        <router-link
          v-if="isAuthenticated"
          to="/quizzes/create"
          class="btn btn-success"
        >
          Create Quiz
        </router-link>
      </div>

      <!-- Filters Component -->
      <QuizFilters
        v-model:search="searchQuery"
        v-model:filters="filters"
        :categories="categories"
        :filtered-count="quizzes.length"
        @update:search="handleSearchChange"
        @update:filters="handleFilterChange"
      />

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Loading quizzes...</p>
      </div>

      <div v-else-if="quizzes.length === 0" class="text-center py-5">
        <div class="alert alert-info">
          <h4>No quizzes found</h4>
          <p v-if="hasActiveFilters">
            Try adjusting your search or filters.
          </p>
          <p v-else>
            Be the first to create a quiz!
          </p>
          <router-link
            v-if="isAuthenticated"
            to="/quizzes/create"
            class="btn btn-primary"
          >
            Create Your First Quiz
          </router-link>
        </div>
      </div>

      <div v-else class="row">
        <QuizCard
          v-for="quiz in quizzes"
          :key="quiz.id"
          :quiz="quiz"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '../services/api'
import QuizCard from '../components/QuizCard.vue'
import QuizFilters from '../components/QuizFilters.vue'

const quizzes = ref([])
const allQuizzes = ref([]) // Store all quizzes for category extraction
const loading = ref(true)
const searchQuery = ref('')
const filters = ref({
  category: '',
  difficulty: '',
  is_active: '',
})

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('auth_token')
})

const hasActiveFilters = computed(() => {
  return (
    searchQuery.value ||
    filters.value.category ||
    filters.value.difficulty ||
    filters.value.is_active
  )
})

// Extract unique categories from all quizzes
const categories = computed(() => {
  const cats = allQuizzes.value
    .map((q) => q.category)
    .filter((cat) => cat && cat.trim() !== '')
  return [...new Set(cats)].sort()
})

const buildQueryParams = () => {
  const params = new URLSearchParams()

  if (searchQuery.value) {
    params.append('search', searchQuery.value)
  }

  if (filters.value.category) {
    params.append('category', filters.value.category)
  }

  if (filters.value.difficulty) {
    params.append('difficulty', filters.value.difficulty)
  }

  if (filters.value.is_active !== '') {
    params.append('is_active', filters.value.is_active)
  }

  return params.toString()
}

const fetchQuizzes = async () => {
  loading.value = true
  try {
    const queryString = buildQueryParams()
    const url = queryString ? `/quizzes?${queryString}` : '/quizzes'
    const response = await api.get(url)

    const fetchedQuizzes = response.data.data || response.data || []
    quizzes.value = fetchedQuizzes

    // Store all quizzes for category extraction (only on initial load)
    if (!hasActiveFilters.value && allQuizzes.value.length === 0) {
      allQuizzes.value = fetchedQuizzes
    }
  } catch (error) {
    console.error('Error fetching quizzes:', error)
    console.error('Error details:', error.response?.data)
    quizzes.value = []
  } finally {
    loading.value = false
  }
}

const handleSearchChange = () => {
  fetchQuizzes()
}

const handleFilterChange = () => {
  fetchQuizzes()
}

// Watch for filter changes
watch([searchQuery, filters], () => {
  fetchQuizzes()
}, { deep: true })

onMounted(() => {
  fetchQuizzes()
})
</script>

<style scoped>
.quiz-list {
  min-height: 60vh;
}
</style>
