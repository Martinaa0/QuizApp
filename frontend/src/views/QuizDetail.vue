<template>
  <div class="quiz-detail">
    <div class="container mt-5">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Loading quiz...</p>
      </div>
      <div v-else-if="quiz">
        <div class="card">
          <div v-if="quizImageUrl" class="card-img-top-container" style="max-height: 400px; overflow: hidden;">
            <img :src="quizImageUrl" class="card-img-top" :alt="quiz.title" style="width: 100%; height: auto; object-fit: cover;" />
          </div>
          <div class="card-body">
            <h1 class="card-title">{{ quiz.title }}</h1>
            <div class="card-text" v-if="quiz.description" v-html="quiz.description"></div>
            <p v-else class="card-text text-muted">No description provided.</p>
            <div class="mb-3">
              <span
                class="badge"
                :class="{
                  'bg-success': quiz.difficulty === 'easy',
                  'bg-warning': quiz.difficulty === 'medium',
                  'bg-danger': quiz.difficulty === 'hard',
                }"
              >
                {{ quiz.difficulty }}
              </span>
              <span v-if="quiz.category" class="badge bg-info ms-2">
                {{ quiz.category }}
              </span>
              <span v-if="quiz.duration" class="badge bg-secondary ms-2">
                {{ quiz.duration }} minutes
              </span>
              <span v-if="quiz.questions && quiz.questions.length > 0" class="badge bg-primary ms-2">
                {{ quiz.questions.length }} question(s)
              </span>
            </div>
            <div class="mt-4">
              <!-- Questions List -->
              <div v-if="quiz.questions && quiz.questions.length > 0" class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4>Questions ({{ quiz.questions.length }})</h4>
                  <div v-if="isAuthenticated && canEdit" class="text-muted small">
                    <i class="bi bi-arrows-move"></i> Drag to reorder questions
                  </div>
                </div>
                <div id="questions-list" class="list-group sortable-questions">
                  <div
                    v-for="(question, index) in quiz.questions"
                    :key="question.id"
                    :data-question-id="question.id"
                    class="list-group-item d-flex justify-content-between align-items-start sortable-item"
                    :class="{ 'sortable-handle': isAuthenticated && canEdit }"
                  >
                    <div class="ms-2 me-auto flex-grow-1">
                      <div class="d-flex align-items-center">
                        <span v-if="isAuthenticated && canEdit" class="drag-handle me-2" style="cursor: move;">
                          <i class="bi bi-grip-vertical text-muted"></i>
                        </span>
                        <div>
                          <div class="fw-bold">
                            {{ index + 1 }}. <span v-html="question.text"></span>
                          </div>
                          <small class="text-muted">
                            Type: {{ question.type }} | Points: {{ question.points }}
                            <span v-if="question.options && question.options.length > 0">
                              | {{ question.options.length }} option(s)
                            </span>
                          </small>
                        </div>
                      </div>
                    </div>
                    <div v-if="isAuthenticated && canEdit" class="btn-group ms-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        @click="editQuestion(question)"
                      >
                        Edit
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger"
                        @click="deleteQuestion(question.id)"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Question Form (only for authenticated users who can edit) -->
              <div v-if="isAuthenticated && canEdit" class="mt-4">
                <QuestionForm
                  :quiz-id="quiz.id"
                  :question="editingQuestion"
                  @saved="handleQuestionSaved"
                  @cancelled="editingQuestion = null"
                />
              </div>

              <!-- Start Quiz Button -->
              <div class="mt-4">
                <div v-if="quiz.questions && quiz.questions.length === 0" class="alert alert-warning">
                  This quiz has no questions yet.
                  <span v-if="isAuthenticated && canEdit">
                    Add questions above to get started.
                  </span>
                </div>
              <div v-else>
                <div v-if="!isAuthenticated" class="alert alert-info">
                  Please <router-link to="/login">login</router-link> to start this quiz.
                </div>
                <button
                  v-else
                  @click="startQuiz"
                  class="btn btn-primary btn-lg"
                  :disabled="loadingQuiz"
                >
                  {{ loadingQuiz ? 'Loading...' : 'Start Quiz' }}
                </button>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <div class="alert alert-danger">
          <h4>Quiz not found</h4>
          <p>The quiz you're looking for doesn't exist.</p>
          <router-link to="/quizzes" class="btn btn-primary">
            Back to Quizzes
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import QuestionForm from '../components/QuestionForm.vue'

const route = useRoute()
const router = useRouter()
const quiz = ref(null)
const loading = ref(true)
const loadingQuiz = ref(false)
const editingQuestion = ref(null)
const currentUser = ref(null)
let sortableInstance = null

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('auth_token')
})

const canEdit = computed(() => {
  if (!isAuthenticated.value || !currentUser.value || !quiz.value) return false
  return (
    currentUser.value.id === quiz.value.created_by ||
    currentUser.value.user_type === 'admin' ||
    currentUser.value.user_type === 'teacher'
  )
})

const quizImageUrl = computed(() => {
  if (!quiz.value?.image) return null
  
  // Ako je već puna URL, vrati direktno
  if (quiz.value.image.startsWith('http')) {
    return quiz.value.image
  }
  
  // Inače, konstruiraj URL prema Laravel storage
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${quiz.value.image}`
})

const fetchQuiz = async () => {
  try {
    const response = await api.get(`/quizzes/${route.params.id}`)
    quiz.value = response.data
    
    // Ako je external quiz, dohvati pitanja
    if (quiz.value.is_external && (!quiz.value.questions || quiz.value.questions.length === 0)) {
      try {
        const questionsResponse = await api.get(`/external/premade-quizzes/${route.params.id}`)
        if (questionsResponse.data.success && questionsResponse.data.quiz) {
          quiz.value = questionsResponse.data.quiz
        }
      } catch (err) {
        console.error('Error fetching external quiz questions:', err)
      }
    }
  } catch (error) {
    console.error('Error fetching quiz:', error)
  } finally {
    loading.value = false
  }
}

const startQuiz = async () => {
  if (!isAuthenticated.value) {
    router.push({ name: 'Login' })
    return
  }

  // Za external quiz, kreiraj privremeni pokušaj
  if (quiz.value.is_external) {
    loadingQuiz.value = true
    try {
      // Za external quiz, direktno preusmjeri na taking sa pitanjima
      router.push({
        name: 'QuizTaking',
        params: { id: quiz.value.id },
        query: { external: 'true' },
      })
    } catch (error) {
      console.error('Error starting external quiz:', error)
      alert('Failed to start quiz. Please try again.')
    } finally {
      loadingQuiz.value = false
    }
  } else {
    // Za lokalne kvizove, koristi postojeći flow
    router.push({
      name: 'QuizTaking',
      params: { id: quiz.value.id },
    })
  }
}

const fetchCurrentUser = async () => {
  if (!isAuthenticated.value) return
  try {
    const response = await api.get('/user')
    currentUser.value = response.data
  } catch (error) {
    console.error('Error fetching user:', error)
  }
}

const editQuestion = (question) => {
  editingQuestion.value = question
  // Scroll to form
  setTimeout(() => {
    const form = document.querySelector('.question-form')
    if (form) {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }, 100)
}

const deleteQuestion = async (questionId) => {
  if (!confirm('Are you sure you want to delete this question?')) return

  try {
    await api.delete(`/questions/${questionId}`)
    await fetchQuiz() // Refresh quiz data
  } catch (error) {
    console.error('Error deleting question:', error)
    alert('Failed to delete question. Please try again.')
  }
}

const handleQuestionSaved = async () => {
  editingQuestion.value = null
  await fetchQuiz() // Refresh quiz data
  // Re-initialize sortable after adding/editing question
  setTimeout(() => {
    initializeSortable()
  }, 100)
}

const initializeSortable = async () => {
  if (!isAuthenticated.value || !canEdit.value) return

  // Destroy existing instance if any
  if (sortableInstance) {
    sortableInstance.destroy()
    sortableInstance = null
  }

  nextTick(async () => {
    try {
      const { default: Sortable } = await import('sortablejs')
      
      const questionsList = document.getElementById('questions-list')
      if (!questionsList) return

      sortableInstance = new Sortable(questionsList, {
        handle: '.drag-handle, .sortable-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: async function (evt) {
          await updateQuestionOrder()
        },
      })
    } catch (error) {
      console.error('Error initializing Sortable:', error)
    }
  })
}

const updateQuestionOrder = async () => {
  if (!isAuthenticated.value || !canEdit.value) return

  const questionsList = document.getElementById('questions-list')
  if (!questionsList) return

  const newOrder = []
  const items = questionsList.querySelectorAll('.sortable-item')
  items.forEach((item, index) => {
    const questionId = item.getAttribute('data-question-id')
    if (questionId) {
      newOrder.push({ id: questionId, order: index })
    }
  })

  try {
    await api.post(`/quizzes/${quiz.value.id}/questions/reorder`, {
      order: newOrder,
    })
    
    // Refresh quiz to get updated order
    await fetchQuiz()
    // Re-initialize sortable after refresh
    setTimeout(() => {
      initializeSortable()
    }, 100)
  } catch (error) {
    console.error('Error updating question order:', error)
    alert('Failed to update question order. Please try again.')
    // Refresh to restore original order
    await fetchQuiz()
    setTimeout(() => {
      initializeSortable()
    }, 100)
  }
}

onMounted(async () => {
  await fetchCurrentUser()
  await fetchQuiz()
  initializeSortable()
})

onUnmounted(() => {
  if (sortableInstance) {
    sortableInstance.destroy()
    sortableInstance = null
  }
})
</script>

<style scoped>
.quiz-detail {
  min-height: 60vh;
}
</style>
