<template>
  <div class="question-form">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ editingQuestion ? 'Edit Question' : 'Add New Question' }}</h5>
        <button
          v-if="editingQuestion"
          class="btn btn-sm btn-secondary"
          @click="cancelEdit"
        >
          Cancel
        </button>
      </div>
      <div class="card-body">
        <form @submit.prevent="handleSubmit">
          <div class="mb-3">
            <label for="question-text" class="form-label">Question Text *</label>
            <RichTextEditor
              v-model="form.text"
              :height="200"
              placeholder="Enter your question..."
            />
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="question-type" class="form-label">Question Type *</label>
              <select
                class="form-select"
                id="question-type"
                v-model="form.type"
                @change="onTypeChange"
                required
              >
                <option value="multiple_choice">Multiple Choice</option>
                <option value="true_false">True/False</option>
                <option value="short_answer">Short Answer</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="question-points" class="form-label">Points *</label>
              <input
                type="number"
                class="form-control"
                id="question-points"
                v-model.number="form.points"
                min="1"
                required
                placeholder="1"
              />
            </div>
            <div class="col-md-3">
              <label for="question-order" class="form-label">Order</label>
              <input
                type="number"
                class="form-control"
                id="question-order"
                v-model.number="form.order"
                min="0"
                placeholder="Optional"
              />
            </div>
          </div>

          <!-- Options for Multiple Choice and True/False -->
          <div v-if="form.type === 'multiple_choice' || form.type === 'true_false'" class="mb-3">
            <label class="form-label">Options *</label>
            <div
              v-for="(option, index) in form.options"
              :key="index"
              class="input-group mb-2"
            >
              <div class="input-group-text">
                <input
                  class="form-check-input mt-0"
                  type="radio"
                  :name="`correct-option-${editingQuestion?.id || 'new'}`"
                  :checked="option.is_correct"
                  @change="setCorrectOption(index)"
                />
              </div>
              <input
                type="text"
                class="form-control"
                v-model="option.text"
                :placeholder="form.type === 'true_false' ? (index === 0 ? 'True' : 'False') : `Option ${index + 1}`"
                required
              />
              <button
                v-if="form.type === 'multiple_choice' && form.options.length > 2"
                class="btn btn-outline-danger"
                type="button"
                @click="removeOption(index)"
              >
                Remove
              </button>
            </div>
            <button
              v-if="form.type === 'multiple_choice'"
              class="btn btn-sm btn-outline-primary"
              type="button"
              @click="addOption"
            >
              + Add Option
            </button>
            <div v-if="form.type === 'true_false'" class="form-text">
              True/False questions automatically have 2 options.
            </div>
          </div>

          <!-- Correct Answer for Short Answer -->
          <div v-if="form.type === 'short_answer'" class="mb-3">
            <label for="correct-answer" class="form-label">Correct Answer *</label>
            <input
              type="text"
              class="form-control"
              id="correct-answer"
              v-model="form.correct_answer"
              required
              placeholder="Enter the correct answer..."
            />
            <div class="form-text">
              This will be used to check if the user's answer is correct.
            </div>
          </div>

          <div v-if="error" class="alert alert-danger">
            {{ error }}
          </div>

          <div class="d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-secondary me-2"
              @click="resetForm"
            >
              Reset
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'Saving...' : (editingQuestion ? 'Update Question' : 'Add Question') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '../services/api'
import RichTextEditor from './RichTextEditor.vue'

const props = defineProps({
  quizId: {
    type: [String, Number],
    required: true,
  },
  question: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['saved', 'cancelled'])

const form = ref({
  text: '',
  type: 'multiple_choice',
  points: 1,
  order: null,
  options: [
    { text: '', is_correct: false },
    { text: '', is_correct: false },
  ],
  correct_answer: '',
})

const loading = ref(false)
const error = ref(null)
const editingQuestion = ref(null)

const onTypeChange = () => {
  if (props.question?.type === form.value.type) return

  if (form.value.type === 'true_false') {
    form.value.options = [
      { text: 'True', is_correct: false },
      { text: 'False', is_correct: false },
    ]
  } else if (form.value.type === 'multiple_choice') {
    if (form.value.options.length < 2) {
      form.value.options = [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
      ]
    }
  }
}

const addOption = () => {
  form.value.options.push({ text: '', is_correct: false })
}

const removeOption = (index) => {
  if (form.value.options.length > 2) {
    form.value.options.splice(index, 1)
  }
}

const setCorrectOption = (index) => {
  form.value.options.forEach((opt, i) => {
    opt.is_correct = i === index
  })
}

const resetForm = () => {
  form.value = {
    text: '',
    type: 'multiple_choice',
    points: 1,
    order: null,
    options: [
      { text: '', is_correct: false },
      { text: '', is_correct: false },
    ],
    correct_answer: '',
  }
  editingQuestion.value = null
  error.value = null
}

const cancelEdit = () => {
  resetForm()
  emit('cancelled')
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    // Prvo kreiraj pitanje
    const questionData = {
      quiz_id: props.quizId,
      text: form.value.text,
      type: form.value.type,
      points: form.value.points,
      order: form.value.order,
    }

    let questionResponse
    if (editingQuestion.value) {
      // Update existing question
      questionResponse = await api.put(`/questions/${editingQuestion.value.id}`, questionData)
    } else {
      // Create new question
      questionResponse = await api.post('/questions', questionData)
    }

    const question = questionResponse.data.question || questionResponse.data

    // Zatim kreiraj/update opcije
    if (editingQuestion.value) {
      // Ako editamo, prvo obriši stare opcije
      if (editingQuestion.value.options && editingQuestion.value.options.length > 0) {
        for (const oldOption of editingQuestion.value.options) {
          try {
            await api.delete(`/options/${oldOption.id}`)
          } catch (err) {
            console.error('Error deleting old option:', err)
          }
        }
      }
    }

    // Kreiraj nove opcije
    if (form.value.type === 'multiple_choice' || form.value.type === 'true_false') {
      // Za multiple choice i true/false, kreiraj opcije
      for (let i = 0; i < form.value.options.length; i++) {
        const option = form.value.options[i]
        if (option.text.trim()) {
          await api.post('/options', {
            question_id: question.id,
            text: option.text,
            is_correct: option.is_correct,
            order: i,
          })
        }
      }
    } else if (form.value.type === 'short_answer') {
      // Za short answer, kreiraj opciju s correct answer
      if (form.value.correct_answer.trim()) {
        await api.post('/options', {
          question_id: question.id,
          text: form.value.correct_answer,
          is_correct: true,
          order: 0,
        })
      }
    }

    resetForm()
    emit('saved', question)
  } catch (err) {
    console.error('Error saving question:', err)
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err.response?.data?.message || 'Failed to save question. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Watch for question prop changes (for editing)
watch(
  () => props.question,
  (newQuestion) => {
    if (newQuestion) {
      editingQuestion.value = newQuestion
      form.value = {
        text: newQuestion.text || '',
        type: newQuestion.type || 'multiple_choice',
        points: newQuestion.points || 1,
        order: newQuestion.order || null,
        options: newQuestion.options?.map((opt) => ({
          text: opt.text,
          is_correct: opt.is_correct,
        })) || [
          { text: '', is_correct: false },
          { text: '', is_correct: false },
        ],
        correct_answer: newQuestion.options?.find((opt) => opt.is_correct)?.text || '',
      }

      if (form.value.type === 'true_false' && form.value.options.length !== 2) {
        form.value.options = [
          { text: 'True', is_correct: false },
          { text: 'False', is_correct: false },
        ]
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.question-form {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
