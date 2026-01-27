<template>
  <div class="quiz-create">
    <div class="container mt-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create New Quiz</h1>
        <router-link to="/" class="btn btn-outline-primary">
          ← Back to Home
        </router-link>
      </div>
      <div class="card">
        <div class="card-body">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label for="title" class="form-label">Title *</label>
              <input
                type="text"
                class="form-control"
                id="title"
                v-model="form.title"
                required
                placeholder="Enter quiz title"
              />
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea
                class="form-control"
                id="description"
                v-model="form.description"
                rows="4"
                placeholder="Enter quiz description..."
              ></textarea>
              <div class="form-text">
                Optional: Add a description for your quiz
              </div>
            </div>

            <!-- Image Upload -->
            <div class="mb-3">
              <label for="image" class="form-label">Quiz Image</label>
              <input
                type="file"
                class="form-control"
                id="image"
                accept="image/*"
                @change="handleImageChange"
              />
              <div class="form-text">
                Accepted formats: JPEG, PNG, JPG, GIF, WEBP (Max 2MB)
              </div>
              <div v-if="imagePreview" class="mt-2">
                <img
                  :src="imagePreview"
                  alt="Preview"
                  class="img-thumbnail"
                  style="max-width: 200px; max-height: 200px;"
                />
                <button
                  type="button"
                  class="btn btn-sm btn-danger ms-2"
                  @click="clearImage"
                >
                  Remove
                </button>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="category" class="form-label">Category</label>
                <input
                  type="text"
                  class="form-control"
                  id="category"
                  v-model="form.category"
                  placeholder="e.g., Science, History"
                />
              </div>

              <div class="col-md-4 mb-3">
                <label for="difficulty" class="form-label">Difficulty</label>
                <select class="form-select" id="difficulty" v-model="form.difficulty">
                  <option value="">Select difficulty</option>
                  <option value="easy">Easy</option>
                  <option value="medium">Medium</option>
                  <option value="hard">Hard</option>
                </select>
              </div>

              <div class="col-md-4 mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input
                  type="number"
                  class="form-control"
                  id="duration"
                  v-model.number="form.duration"
                  min="1"
                  placeholder="Optional"
                />
              </div>
            </div>

            <div class="mb-3 form-check">
              <input
                type="checkbox"
                class="form-check-input"
                id="is_active"
                v-model="form.is_active"
              />
              <label class="form-check-label" for="is_active">
                Active (visible to users)
              </label>
            </div>

            <div v-if="error" class="alert alert-danger">
              {{ error }}
            </div>

            <div class="d-flex justify-content-between">
              <router-link to="/quizzes" class="btn btn-secondary">
                Cancel
              </router-link>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                {{ loading ? 'Creating...' : 'Create Quiz' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Questions Section -->
      <div v-if="createdQuizId" class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            Add Questions ({{ questions.length }}/20)
            <span v-if="questions.length >= 20" class="badge bg-warning ms-2">
              Maximum reached
            </span>
          </h5>
          <button
            v-if="questions.length < 20"
            class="btn btn-sm btn-success"
            @click="showQuestionForm = !showQuestionForm"
          >
            {{ showQuestionForm ? 'Cancel' : '+ Add Question' }}
          </button>
          <span v-else class="text-muted small">
            Maximum of 20 questions reached
          </span>
        </div>
        <div class="card-body">
          <!-- Question Form -->
          <div v-if="showQuestionForm && questions.length < 20" class="mb-4">
            <QuestionForm
              :quiz-id="createdQuizId"
              :question="editingQuestion"
              @saved="handleQuestionSaved"
              @cancelled="cancelQuestionEdit"
            />
          </div>

          <!-- Questions List -->
          <div v-if="questions.length > 0" class="mb-3">
            <h6>Added Questions:</h6>
            <div class="list-group">
              <div
                v-for="(question, index) in questions"
                :key="question.id || index"
                class="list-group-item d-flex justify-content-between align-items-start"
              >
                <div class="ms-2 me-auto">
                  <div class="fw-bold">
                    {{ index + 1 }}. {{ question.text }}
                  </div>
                  <small class="text-muted">
                    Type: {{ question.type }} | Points: {{ question.points }}
                    <span v-if="question.options && question.options.length > 0">
                      | {{ question.options.length }} option(s)
                    </span>
                  </small>
                </div>
                <div class="btn-group">
                  <button
                    class="btn btn-sm btn-outline-primary"
                    @click="editQuestion(question)"
                  >
                    Edit
                  </button>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    @click="removeQuestion(index)"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="alert alert-info">
            No questions added yet. Add at least one question to make your quiz functional.
          </div>

          <!-- Final Actions -->
          <div class="mt-4 d-flex justify-content-between">
            <router-link to="/quizzes" class="btn btn-secondary">
              Back to Quizzes
            </router-link>
            <div>
              <button
                class="btn btn-outline-primary me-2"
                @click="goToQuizDetail"
              >
                View Quiz Details
              </button>
              <button
                class="btn btn-success"
                @click="finishCreating"
                :disabled="questions.length === 0"
                :title="questions.length === 0 ? 'Add at least one question' : ''"
              >
                {{ questions.length === 0 ? 'Add Questions First' : 'Finish Creating Quiz' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import QuestionForm from '../components/QuestionForm.vue'

const router = useRouter()

const form = ref({
  title: '',
  description: '',
  category: '',
  difficulty: '',
  duration: null,
  is_active: true,
})

const loading = ref(false)
const error = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)
const createdQuizId = ref(null)
const questions = ref([])
const showQuestionForm = ref(false)
const editingQuestion = ref(null)

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      error.value = 'Image size must be less than 2MB'
      return
    }

    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
    if (!validTypes.includes(file.type)) {
      error.value = 'Invalid image format. Please use JPEG, PNG, JPG, GIF, or WEBP'
      return
    }

    imageFile.value = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    error.value = null
  }
}

const clearImage = () => {
  imageFile.value = null
  imagePreview.value = null
  const fileInput = document.getElementById('image')
  if (fileInput) {
    fileInput.value = ''
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    // Create FormData for file upload
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description || '')
    formData.append('category', form.value.category || '')
    formData.append('difficulty', form.value.difficulty || '')
    if (form.value.duration) {
      formData.append('duration', form.value.duration)
    }
    formData.append('is_active', form.value.is_active ? '1' : '0')

    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const response = await api.post('/quizzes', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    // Spremi ID kreiranog kviza i prikaži formu za pitanja
    createdQuizId.value = response.data.quiz.id
    showQuestionForm.value = true
    
    // Fetch existing questions if any
    await fetchQuestions()
  } catch (err) {
    console.error('Error creating quiz:', err)
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err.response?.data?.message || 'Failed to create quiz. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const fetchQuestions = async () => {
  if (!createdQuizId.value) return
  
  try {
    const response = await api.get(`/questions?quiz_id=${createdQuizId.value}`)
    questions.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching questions:', error)
  }
}

const handleQuestionSaved = async (question) => {
  showQuestionForm.value = false
  editingQuestion.value = null
  await fetchQuestions()
}

const editQuestion = (question) => {
  editingQuestion.value = question
  showQuestionForm.value = true
  // Scroll to form
  setTimeout(() => {
    const form = document.querySelector('.question-form')
    if (form) {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }, 100)
}

const removeQuestion = async (index) => {
  const question = questions.value[index]
  if (!question.id) {
    // Ako nema ID, samo ukloni iz liste (još nije spremljeno)
    questions.value.splice(index, 1)
    return
  }

  if (!confirm('Are you sure you want to remove this question?')) return

  try {
    await api.delete(`/questions/${question.id}`)
    await fetchQuestions()
  } catch (error) {
    console.error('Error deleting question:', error)
    alert('Failed to delete question. Please try again.')
  }
}

const cancelQuestionEdit = () => {
  editingQuestion.value = null
  showQuestionForm.value = false
}

const goToQuizDetail = () => {
  router.push({
    name: 'QuizDetail',
    params: { id: createdQuizId.value },
  })
}

const finishCreating = () => {
  router.push({
    name: 'QuizDetail',
    params: { id: createdQuizId.value },
  })
}
</script>

<style scoped>
.quiz-create {
  min-height: 70vh;
}
</style>
