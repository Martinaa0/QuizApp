<template>
  <div class="col-md-4 mb-4">
    <div class="card h-100 shadow-sm quiz-card">
      <div v-if="quizImageUrl && !quiz.is_external" class="card-img-top-container">
        <img :src="quizImageUrl" class="card-img-top" :alt="quiz.title" />
      </div>
      <div v-if="quiz.is_external" class="card-img-top-container bg-primary text-white text-center p-4 d-flex flex-column justify-content-center">
        <div style="font-size: 3rem;">📚</div>
        <div class="small mt-2">External Quiz</div>
      </div>
      <div class="card-body d-flex flex-column">
        <div class="mb-2">
          <span
            class="badge"
            :class="{
              'bg-success': quiz.difficulty === 'easy',
              'bg-warning text-dark': quiz.difficulty === 'medium',
              'bg-danger': quiz.difficulty === 'hard',
            }"
          >
            {{ quiz.difficulty }}
          </span>
          <span v-if="quiz.category" class="badge bg-info ms-2">
            {{ quiz.category }}
          </span>
          <span v-if="quiz.duration" class="badge bg-secondary ms-2">
            {{ quiz.duration }} min
          </span>
          <span v-if="quiz.is_external" class="badge bg-primary ms-2">
            🌐 External
          </span>
        </div>
        <h5 class="card-title">{{ quiz.title }}</h5>
        <p class="card-text flex-grow-1 text-muted" style="line-height: 1.6; min-height: 3em;">
          {{ truncateDescription(quiz.description) }}
        </p>
        <div class="mt-auto">
          <div v-if="quiz.is_external" class="text-muted small mb-2">
            Source: Open Trivia Database
          </div>
          <div v-else-if="quiz.creator" class="text-muted small mb-2">
            Created by: {{ quiz.creator?.name || 'Unknown' }}
          </div>
          <div class="text-muted small mb-2">
            {{ quiz.questions?.length || quiz.question_count || quiz.questions_count || 0 }} question(s)
          </div>
          <router-link
            :to="{ name: 'QuizDetail', params: { id: quiz.id } }"
            class="btn btn-primary w-100"
          >
            {{ quiz.is_external ? 'Start Quiz' : 'View Quiz' }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  quiz: {
    type: Object,
    required: true,
  },
})

const quizImageUrl = computed(() => {
  if (!props.quiz.image) return null
  
  // Ako je već puna URL, vrati direktno
  if (props.quiz.image.startsWith('http')) {
    return props.quiz.image
  }
  
  // Inače, konstruiraj URL prema Laravel storage
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${props.quiz.image}`
})

const truncateDescription = (description) => {
  if (!description) return 'No description available.'
  
  // Ukloni HTML tagove
  const tempDiv = document.createElement('div')
  tempDiv.innerHTML = description
  const plainText = tempDiv.textContent || tempDiv.innerText || ''
  
  // Skrati tekst ako je predugačak
  return plainText.length > 150
    ? plainText.substring(0, 150).trim() + '...'
    : plainText.trim()
}
</script>

<style scoped>
.card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
}

.card-img-top-container {
  height: 200px;
  overflow: hidden;
  background-color: #f8f9fa;
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-body {
  min-height: 200px;
}
</style>
