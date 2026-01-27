<template>
  <div class="question-card mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Question {{ questionNumber }} of {{ totalQuestions }}</h5>
        <span class="badge bg-primary">{{ question.points }} point(s)</span>
      </div>
      <div class="card-body">
        <h6 class="card-title mb-3" v-html="question.text"></h6>

        <!-- Multiple Choice / True False -->
        <div v-if="question.type === 'multiple_choice' || question.type === 'true_false'">
          <div
            v-for="option in question.options"
            :key="option.id"
            class="form-check mb-2"
          >
            <input
              class="form-check-input"
              type="radio"
              :name="`question-${question.id}`"
              :id="`option-${option.id}`"
              :value="option.id"
              :checked="selectedOption === option.id"
              @change="selectOption(option.id)"
              :disabled="isSubmitted"
            />
            <label
              class="form-check-label"
              :for="`option-${option.id}`"
              :class="{
                'text-success': isSubmitted && option.is_correct,
                'text-danger': isSubmitted && selectedOption === option.id && !option.is_correct,
              }"
            >
              {{ option.text }}
            </label>
          </div>
        </div>

        <!-- Short Answer -->
        <div v-else-if="question.type === 'short_answer'">
          <input
            type="text"
            class="form-control"
            v-model="answerText"
            @input="updateAnswer"
            :disabled="isSubmitted"
            placeholder="Enter your answer"
          />
        </div>

        <!-- Feedback nakon submita -->
        <div v-if="isSubmitted && answerFeedback" class="mt-3">
          <div
            class="alert"
            :class="answerFeedback.is_correct ? 'alert-success' : 'alert-danger'"
          >
            <strong v-if="answerFeedback.is_correct">Correct!</strong>
            <strong v-else>Incorrect.</strong>
            <span v-if="answerFeedback.points_earned > 0">
              You earned {{ answerFeedback.points_earned }} point(s).
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  questionNumber: {
    type: Number,
    required: true,
  },
  totalQuestions: {
    type: Number,
    required: true,
  },
  isSubmitted: {
    type: Boolean,
    default: false,
  },
  answerFeedback: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['answer-selected'])

const selectedOption = ref(null)
const answerText = ref('')

const selectOption = (optionId) => {
  selectedOption.value = optionId
  emit('answer-selected', {
    question_id: props.question.id,
    option_id: optionId,
  })
}

const updateAnswer = () => {
  emit('answer-selected', {
    question_id: props.question.id,
    answer_text: answerText.value,
  })
}

// Ako već postoji odgovor, postavi ga
watch(
  () => props.answerFeedback,
  (feedback) => {
    if (feedback) {
      if (feedback.option_id) {
        selectedOption.value = feedback.option_id
      }
      if (feedback.answer_text) {
        answerText.value = feedback.answer_text
      }
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.question-card {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
</style>
