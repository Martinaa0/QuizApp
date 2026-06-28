<template>
  <router-link :to="{ name: 'QuizDetail', params: { id: quiz.id } }" class="quiz-card-link">
    <div class="quiz-card qa-card hoverable">
      <!-- Cover band -->
      <div class="card-cover" :class="catClass">
        <div class="cover-emoji">{{ catEmoji }}</div>
        <div class="cover-diff pill pill-sm" :class="diffClass">{{ quiz.difficulty || 'medium' }}</div>
      </div>
      <!-- Body -->
      <div class="card-body">
        <div class="card-eyebrow eyebrow" :style="{ color: 'var(--cat-color, var(--muted))' }">{{ quiz.category || 'General' }}</div>
        <div class="card-title">{{ quiz.title }}</div>
        <div class="card-footer-row">
          <span class="card-meta">◷ {{ quiz.duration || '?' }} min</span>
          <span class="card-meta">❓ {{ quiz.questions?.length || quiz.question_count || quiz.questions_count || 0 }} Q</span>
          <span class="card-author">{{ authorName }}</span>
        </div>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  quiz: { type: Object, required: true },
})

const catMap = {
  geography: { class: 'cat-geography', emoji: '🌍' },
  'computer science': { class: 'cat-computer-science', emoji: '💻' },
  science: { class: 'cat-science', emoji: '🔬' },
  history: { class: 'cat-history', emoji: '📜' },
  sports: { class: 'cat-sports', emoji: '⚽' },
  'general knowledge': { class: 'cat-general-knowledge', emoji: '🧠' },
}

const catKey = computed(() => (props.quiz.category || '').toLowerCase())
const catClass = computed(() => catMap[catKey.value]?.class || 'cat-general-knowledge')
const catEmoji = computed(() => catMap[catKey.value]?.emoji || '📚')

const diffClass = computed(() => {
  const d = (props.quiz.difficulty || '').toLowerCase()
  if (d === 'easy') return 'pill-easy'
  if (d === 'hard') return 'pill-hard'
  return 'pill-medium'
})

const authorName = computed(() => {
  if (props.quiz.creator?.name) {
    const parts = props.quiz.creator.name.split(' ')
    return parts.length > 1 ? `${parts[0][0]}. ${parts.slice(1).join(' ')}` : parts[0]
  }
  return props.quiz.is_external ? 'Trivia DB' : ''
})
</script>

<style scoped>
.quiz-card-link {
  text-decoration: none;
  color: inherit;
}
.quiz-card {
  overflow: hidden;
  border-radius: var(--radius);
}

.card-cover {
  height: 118px;
  background: var(--cat-grad, linear-gradient(135deg, #c3b6f7, #a596e0));
  position: relative;
  display: flex;
  align-items: flex-end;
  padding: 14px 16px;
}
.cover-emoji {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: rgba(255,255,255,.22);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
}
.cover-diff {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(255,255,255,.92) !important;
  border: none !important;
}

.card-body {
  padding: 16px 18px 18px;
}
.card-eyebrow {
  margin-bottom: 6px;
}
.card-title {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 17px;
  color: var(--ink);
  margin-bottom: 14px;
  line-height: 1.3;
}
.card-footer-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding-top: 12px;
  border-top: 1px solid var(--line);
  font-size: 13px;
  color: var(--faint);
  font-weight: 700;
}
.card-author {
  margin-left: auto;
  color: var(--muted2);
}
</style>
