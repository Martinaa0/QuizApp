<template>
  <div class="browse-page">
    <div class="browse-inner">
      <!-- Header -->
      <div class="browse-header">
        <div>
          <h1 class="browse-h1">Pregledaj kvizove</h1>
          <p class="browse-sub">{{ filteredQuizzes.length }} kvizova · pronađite jedan za rješavanje ili preradu</p>
        </div>
        <router-link v-if="isAuthenticated" to="/quizzes/create" class="btn-mint">+ Novi kviz</router-link>
      </div>

      <!-- Filters -->
      <QuizFilters
        :search="searchQuery"
        :filters="filters"
        :categories="categories"
        :filtered-count="filteredQuizzes.length"
        @update:search="onSearchUpdate"
        @update:filters="onFiltersUpdate"
      />

      <!-- Loading -->
      <div v-if="loading" style="text-align:center; padding:60px 0;">
        <div style="width:40px; height:40px; border:3px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
        <p style="margin-top:16px; color:var(--muted); font-weight:700;">Učitavanje kvizova...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="filteredQuizzes.length === 0" style="text-align:center; padding:60px 0;">
        <p style="color:var(--muted); font-size:16px;">Nema pronađenih kvizova.</p>
        <p v-if="hasActiveFilters" style="color:var(--faint); font-size:14px;">Pokušajte prilagoditi filtere.</p>
      </div>

      <!-- Grid -->
      <div v-else class="quiz-grid three-col-grid">
        <QuizCard v-for="quiz in filteredQuizzes" :key="quiz.id" :quiz="quiz" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import QuizCard from '../components/QuizCard.vue'
import QuizFilters from '../components/QuizFilters.vue'

const allQuizzes = ref([])
const loading = ref(true)
const searchQuery = ref('')
const filters = ref({ category: '', difficulty: '', is_active: '' })

const isAuthenticated = computed(() => !!localStorage.getItem('auth_token'))
const hasActiveFilters = computed(() => searchQuery.value || filters.value.category || filters.value.difficulty || filters.value.is_active)

const categories = computed(() => {
  const cats = allQuizzes.value.map(q => q.category).filter(c => c && c.trim())
  return [...new Set(cats)].sort()
})

// Client-side filtering
const filteredQuizzes = computed(() => {
  let result = allQuizzes.value

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(quiz =>
      (quiz.title || '').toLowerCase().includes(q) ||
      (quiz.category || '').toLowerCase().includes(q)
    )
  }

  if (filters.value.category) {
    const cat = filters.value.category.toLowerCase()
    result = result.filter(quiz => (quiz.category || '').toLowerCase() === cat)
  }

  if (filters.value.difficulty) {
    result = result.filter(quiz => (quiz.difficulty || '').toLowerCase() === filters.value.difficulty.toLowerCase())
  }

  if (filters.value.is_active !== '') {
    const active = filters.value.is_active === 'true'
    result = result.filter(quiz => quiz.is_active === active || quiz.is_active === (active ? 1 : 0))
  }

  return result
})

const onSearchUpdate = (val) => { searchQuery.value = val }
const onFiltersUpdate = (val) => { filters.value = { ...val } }

const fetchQuizzes = async () => {
  loading.value = true
  try {
    const response = await api.get('/quizzes')
    allQuizzes.value = response.data.data || response.data || []
  } catch {
    allQuizzes.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchQuizzes)
</script>

<style scoped>
.browse-page { min-height: 60vh; }
.browse-inner {
  max-width: 1160px;
  margin: 0 auto;
  padding: 30px 28px 64px;
}
.browse-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}
.browse-h1 {
  font-size: 30px;
  margin-bottom: 6px;
}
.browse-sub {
  color: var(--muted);
  font-size: 15px;
  margin: 0;
}
.quiz-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 880px) {
  .quiz-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .quiz-grid { grid-template-columns: 1fr; }
  .browse-header { flex-direction: column; align-items: stretch; }
}
</style>
