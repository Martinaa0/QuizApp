<template>
  <div class="question-card fade-in">
    <div class="qc-card qa-card elevated" style="padding:30px; border-radius:18px;">
      <!-- Top row -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <span class="pill pill-sm pill-student">{{ formatType(question.type) }}</span>
        <span style="font-size:13px; font-weight:700; color:var(--accent);">+{{ question.points }} bodova</span>
      </div>

      <!-- Question text -->
      <h2 style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:25px; line-height:1.3; margin-bottom:20px;" v-html="question.text"></h2>

      <!-- Multiple Choice / True False options -->
      <div v-if="question.type === 'multiple_choice' || question.type === 'true_false'" class="options-grid">
        <button
          v-for="(option, idx) in question.options"
          :key="option.id"
          class="option-btn"
          :class="{ selected: selectedOption === option.id }"
          @click="selectOption(option.id)"
          :disabled="isSubmitted"
        >
          <div class="option-key">{{ keys[idx] }}</div>
          <span class="option-label">{{ option.text }}</span>
          <span v-if="selectedOption === option.id" class="option-dot">●</span>
        </button>
      </div>

      <!-- Short Answer -->
      <div v-else-if="question.type === 'short_answer'">
        <input
          type="text"
          class="qa-input"
          v-model="answerText"
          @input="updateAnswer"
          :disabled="isSubmitted"
          placeholder="Upišite svoj odgovor…"
        />
      </div>

      <!-- Feedback -->
      <div v-if="answerFeedback" class="feedback-box" :class="answerFeedback.is_correct ? 'correct' : 'incorrect'">
        <strong>{{ answerFeedback.is_correct ? 'Točno!' : 'Netočno.' }}</strong>
        <span v-if="answerFeedback.points_earned > 0"> +{{ answerFeedback.points_earned }} pts</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  question: { type: Object, required: true },
  questionNumber: { type: Number, required: true },
  totalQuestions: { type: Number, required: true },
  isSubmitted: { type: Boolean, default: false },
  answerFeedback: { type: Object, default: null },
})

const emit = defineEmits(['answer-selected'])
const selectedOption = ref(null)
const answerText = ref('')
const keys = ['A', 'B', 'C', 'D', 'E', 'F']

const formatType = (t) => {
  if (t === 'multiple_choice') return 'Višestruki izbor'
  if (t === 'true_false') return 'Točno / Netočno'
  return 'Kratki odgovor'
}

const selectOption = (optionId) => {
  selectedOption.value = optionId
  emit('answer-selected', { question_id: props.question.id, option_id: optionId })
}

const updateAnswer = () => {
  emit('answer-selected', { question_id: props.question.id, answer_text: answerText.value })
}

// Reset on question change
watch(() => props.question.id, () => {
  selectedOption.value = null
  answerText.value = ''
})

watch(() => props.answerFeedback, (f) => {
  if (f?.option_id) selectedOption.value = f.option_id
  if (f?.answer_text) answerText.value = f.answer_text
}, { immediate: true })
</script>

<style scoped>
.options-grid {
  display: grid;
  gap: 12px;
}
.option-btn {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: var(--surface);
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: var(--body-text);
  text-align: left;
  transition: all .15s;
  width: 100%;
}
.option-btn:hover:not(:disabled) {
  border-color: var(--accent);
  background: var(--mint-soft2);
}
.option-btn.selected {
  border: 2px solid var(--accent);
  background: var(--mint-soft2);
  color: #2f6b5c;
}
.option-btn:disabled { cursor: default; opacity: .8; }

.option-key {
  width: 28px; height: 28px;
  border-radius: 8px;
  background: var(--page-bg);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: var(--muted2);
  flex-shrink: 0;
}
.option-btn.selected .option-key {
  background: var(--accent);
  color: #fff;
}
.option-label { flex: 1; }
.option-dot {
  color: var(--accent);
  font-size: 14px;
}

.feedback-box {
  margin-top: 18px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
}
.feedback-box.correct { background: #e7f5ee; color: var(--success-deep); }
.feedback-box.incorrect { background: #ffe9e2; color: var(--danger-text); }
</style>
