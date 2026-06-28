<template>
  <div class="take-page">
    <div class="take-inner">
      <!-- Loading -->
      <div v-if="loading" style="text-align:center; padding:80px 0;">
        <div style="width:40px; height:40px; border:3px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
      </div>

      <!-- Quiz -->
      <div v-else-if="quiz && !showResults">
        <!-- Top row -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div style="font-size:15px; font-weight:700; color:var(--muted2);">
            Pitanje {{ currentQuestionIndex + 1 }} od {{ questions.length }}
          </div>
          <div class="timer-pill" :class="timerClass">
            ◷ {{ formatTime(timer) }}
          </div>
        </div>

        <!-- Progress bar -->
        <div class="qa-progress" style="margin-bottom:24px;">
          <div class="qa-progress-fill" :style="{ width: progressPct + '%' }"></div>
        </div>

        <!-- Question card -->
        <QuestionCard
          v-if="currentQuestion"
          :question="currentQuestion"
          :question-number="currentQuestionIndex + 1"
          :total-questions="questions.length"
          :is-submitted="isSubmitted"
          :answer-feedback="answerFeedback"
          @answer-selected="handleAnswerSelected"
        />

        <!-- Nav buttons -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
          <button
            class="btn-outline-mint"
            @click="previousQuestion"
            :disabled="currentQuestionIndex === 0"
            style="opacity: currentQuestionIndex === 0 ? 0.5 : 1"
          >← Prethodno</button>
          <div style="display:flex; gap:10px;">
            <button class="btn-outline-mint" @click="skipQuestion" v-if="currentQuestionIndex < questions.length - 1">Preskoči</button>
            <button
              v-if="currentQuestionIndex < questions.length - 1"
              class="btn-mint"
              @click="nextQuestion"
            >Dalje →</button>
            <button
              v-else
              class="btn-mint"
              @click="submitQuiz"
              :disabled="submitting"
            >{{ submitting ? 'Slanje...' : 'Pošalji ✓' }}</button>
          </div>
        </div>

        <!-- Question dots -->
        <div class="question-dots">
          <button
            v-for="(q, i) in questions"
            :key="q.id"
            class="q-dot"
            :class="{
              current: i === currentQuestionIndex,
              answered: answers[q.id] && i !== currentQuestionIndex,
            }"
            @click="goToQuestion(i)"
          >{{ i + 1 }}</button>
        </div>
      </div>

      <!-- Results -->
      <div v-if="showResults" class="results-section fade-in">
        <!-- Hero panel -->
        <div class="results-hero">
          <div class="score-ring-wrap">
            <div class="score-ring" :style="ringStyle">
              <div class="score-ring-inner">
                <div class="score-pct">{{ scorePct }}%</div>
                <div class="score-label eyebrow">REZULTAT</div>
              </div>
            </div>
          </div>
          <div class="results-hero-right">
            <div class="eyebrow" style="color:var(--dark-muted); margin-bottom:10px;">KVIZ ZAVRŠEN</div>
            <h1 style="font-size:28px; color:var(--dark-text); margin-bottom:10px;">Odlično odrađeno!</h1>
            <p style="color:var(--dark-muted2); font-size:15px; line-height:1.5; margin-bottom:20px;">
              Ostvarili ste {{ correctCount }} od {{ questions.length }} na {{ quiz.title }}.
            </p>
            <div class="results-stats">
              <div class="r-stat"><div class="r-stat-num" style="color:#56cc9d;">{{ correctCount }}</div><div class="r-stat-label">Točno</div></div>
              <div class="r-stat"><div class="r-stat-num" style="color:#ff9a7f;">{{ missedCount }}</div><div class="r-stat-label">Promašeno</div></div>
              <div class="r-stat"><div class="r-stat-num" style="color:var(--dark-text);">{{ earnedPoints }}</div><div class="r-stat-label">Bodovi</div></div>
            </div>
          </div>
        </div>

        <!-- Answer review -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin:28px 0 16px;">
          <h2 style="font-size:19px;">Pregled odgovora</h2>
          <div style="display:flex; gap:10px;">
            <button class="btn-outline-mint" style="font-size:13px; padding:8px 14px;" @click="printResults">⎙ Ispis</button>
            <router-link to="/quizzes" class="btn-mint" style="font-size:13px; padding:8px 14px;">Više kvizova</router-link>
          </div>
        </div>
        <div class="review-list">
          <div
            v-for="(q, i) in questions"
            :key="q.id"
            class="review-row"
            :class="isCorrect(q) ? 'correct' : 'incorrect'"
          >
            <div class="review-mark" :class="isCorrect(q) ? 'correct' : 'incorrect'">
              {{ isCorrect(q) ? '✓' : '✕' }}
            </div>
            <div style="flex:1; min-width:0;">
              <div style="font-weight:700; font-size:14px;" v-html="q.text"></div>
              <div style="font-size:13px; color:var(--faint); margin-top:4px;">
                Vaš odgovor: {{ getUserAnswerText(q) }}
              </div>
            </div>
            <div style="font-weight:700; font-size:14px;" :style="{ color: isCorrect(q) ? 'var(--success-deep)' : 'var(--faint)' }">
              +{{ isCorrect(q) ? q.points : 0 }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import QuestionCard from '../components/QuestionCard.vue'

const route = useRoute()
const router = useRouter()

const quiz = ref(null)
const attempt = ref(null)
const questions = ref([])
const currentQuestionIndex = ref(0)
const answers = ref({})
const loading = ref(true)
const submitting = ref(false)
const showResults = ref(false)
const results = ref(null)
const timer = ref(0)
const timerInterval = ref(null)
const isSubmitted = ref(false)
const answerFeedback = ref(null)

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || null)
const progressPct = computed(() => questions.value.length ? ((currentQuestionIndex.value + 1) / questions.value.length) * 100 : 0)
const answeredCount = computed(() => Object.keys(answers.value).length)
const allQuestionsAnswered = computed(() => answeredCount.value >= questions.value.length)

const timerClass = computed(() => {
  if (timer.value >= 300) return 'timer-green'
  if (timer.value >= 60) return 'timer-amber'
  return 'timer-red'
})

// Results computations
const correctCount = computed(() => {
  return questions.value.filter(q => isCorrect(q)).length
})
const missedCount = computed(() => questions.value.length - correctCount.value)
const earnedPoints = computed(() => {
  return questions.value.reduce((sum, q) => sum + (isCorrect(q) ? q.points : 0), 0)
})
const totalPoints = computed(() => questions.value.reduce((sum, q) => sum + q.points, 0))
const scorePct = computed(() => totalPoints.value ? Math.round((earnedPoints.value / totalPoints.value) * 100) : 0)
const ringStyle = computed(() => ({
  background: `conic-gradient(var(--accent) 0% ${scorePct.value}%, rgba(255,255,255,.14) ${scorePct.value}% 100%)`
}))

const isCorrect = (q) => {
  const a = answers.value[q.id]
  if (!a) return false
  if (a.option_id) {
    const correct = q.options?.find(o => o.is_correct)
    return correct && a.option_id === correct.id
  }
  return false
}

const getUserAnswerText = (q) => {
  const a = answers.value[q.id]
  if (!a) return 'Bez odgovora'
  if (a.option_id) {
    const opt = q.options?.find(o => o.id === a.option_id)
    return opt?.text || 'Bez odgovora'
  }
  return a.answer_text || 'Bez odgovora'
}

const formatTime = (seconds) => {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${s.toString().padStart(2, '0')}`
}

const fetchQuiz = async () => {
  try {
    const isExternal = route.query.external === 'true' || route.params.id.startsWith?.('trivia_')
    if (isExternal) {
      const r = await api.get(`/external/premade-quizzes/${route.params.id}`)
      if (r.data.success && r.data.quiz) {
        quiz.value = r.data.quiz
        questions.value = r.data.quiz.questions || []
        attempt.value = { id: 'external_' + Date.now(), is_external: true }
        if (quiz.value.duration) { timer.value = quiz.value.duration * 60; startTimer() }
      }
    } else {
      const r = await api.get(`/quizzes/${route.params.id}`)
      quiz.value = r.data
      questions.value = r.data.questions || []
    }
  } catch {
    router.push({ name: 'QuizList' })
  } finally {
    loading.value = false
  }
}

const startQuiz = async () => {
  if (attempt.value?.is_external) return
  try {
    const r = await api.post(`/quizzes/${route.params.id}/start`, { quiz_id: route.params.id })
    attempt.value = r.data.attempt
    if (quiz.value.duration) { timer.value = quiz.value.duration * 60; startTimer() }
  } catch (e) {
    if (e.response?.data?.attempt) { attempt.value = e.response.data.attempt }
  }
}

const startTimer = () => {
  if (timerInterval.value) clearInterval(timerInterval.value)
  timerInterval.value = setInterval(() => {
    if (timer.value > 0) timer.value--
    else { clearInterval(timerInterval.value); submitQuiz() }
  }, 1000)
}

const handleAnswerSelected = async (answer) => {
  answers.value[answer.question_id] = answer
  if (attempt.value?.is_external) {
    const q = questions.value.find(q => q.id === answer.question_id)
    if (q) {
      const correct = q.options?.find(o => o.is_correct)
      const ok = correct && answer.option_id === correct.id
      answerFeedback.value = { is_correct: ok, points_earned: ok ? q.points : 0 }
    }
    return
  }
  if (!attempt.value) return
  try {
    const r = await api.post('/attempts/answer', {
      attempt_id: attempt.value.id,
      question_id: answer.question_id,
      option_id: answer.option_id,
      answer_text: answer.answer_text,
    })
    answerFeedback.value = { is_correct: r.data.is_correct, points_earned: r.data.points_earned }
  } catch {}
}

const submitQuiz = async () => {
  if (!attempt.value) return
  submitting.value = true
  try {
    if (attempt.value.is_external) {
      // Score computed locally
    } else {
      const r = await api.post(`/attempts/${attempt.value.id}/submit`)
      results.value = r.data.attempt
    }
    showResults.value = true
    isSubmitted.value = true
    if (timerInterval.value) clearInterval(timerInterval.value)
  } catch {
    alert('Greška pri slanju kviza.')
  } finally {
    submitting.value = false
  }
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < questions.length - 1) { currentQuestionIndex.value++; answerFeedback.value = null }
}
const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) { currentQuestionIndex.value--; answerFeedback.value = null }
}
const skipQuestion = () => nextQuestion()
const goToQuestion = (i) => { currentQuestionIndex.value = i; answerFeedback.value = null }
const printResults = () => window.print()

onMounted(async () => { await fetchQuiz(); await startQuiz() })
onUnmounted(() => { if (timerInterval.value) clearInterval(timerInterval.value) })
</script>

<style scoped>
.take-page { min-height: 70vh; }
.take-inner { max-width: 760px; margin: 0 auto; padding: 26px 24px 64px; }

.timer-pill {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 15px;
  padding: 7px 14px;
  border-radius: 999px;
  font-variant-numeric: tabular-nums;
}
.timer-green { background: #e7f5ee; color: #3aa17e; }
.timer-amber { background: #fbf2dd; color: var(--warning-text); }
.timer-red { background: #ffe9e2; color: var(--danger); }

.question-dots {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 24px;
  justify-content: center;
}
.q-dot {
  width: 30px; height: 30px;
  border-radius: 8px;
  border: none;
  font-family: 'Lato', sans-serif;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all .15s;
  background: var(--line3);
  color: var(--faint2);
}
.q-dot.current { background: var(--accent); color: #fff; }
.q-dot.answered { background: #d6efe5; color: var(--success-deep); }

/* Results */
.results-hero {
  background: linear-gradient(135deg, var(--dark), var(--dark2));
  border-radius: 20px;
  padding: 34px;
  display: flex;
  gap: 34px;
  align-items: center;
  color: var(--dark-text);
}
.score-ring-wrap { flex-shrink: 0; }
.score-ring {
  width: 128px; height: 128px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.score-ring-inner {
  width: 96px; height: 96px;
  border-radius: 50%;
  background: var(--dark-inner);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.score-pct {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 28px;
  color: var(--dark-text);
}
.score-label { color: var(--dark-muted); margin-top: 2px; }
.results-hero-right { flex: 1; }
.results-stats { display: flex; gap: 24px; }
.r-stat-num {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 22px;
}
.r-stat-label {
  font-size: 12px;
  color: var(--dark-muted);
  font-weight: 700;
  margin-top: 2px;
}

.review-list { display: flex; flex-direction: column; gap: 8px; }
.review-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: var(--surface);
  border-radius: 12px;
  border-left: 4px solid;
}
.review-row.correct { border-left-color: var(--success); }
.review-row.incorrect { border-left-color: var(--danger); }
.review-mark {
  width: 30px; height: 30px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.review-mark.correct { background: #e7f5ee; color: var(--success-deep); }
.review-mark.incorrect { background: #ffe9e2; color: var(--danger-text); }

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .results-hero { flex-direction: column; text-align: center; }
  .results-stats { justify-content: center; }
}
</style>
