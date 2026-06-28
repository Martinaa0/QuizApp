<template>
  <div class="game-page">
    <div class="game-inner">
      <!-- Loading -->
      <div v-if="loading" style="text-align:center; padding:80px 0;">
        <div style="width:40px; height:40px; border:3px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
      </div>

      <div v-else>
        <!-- Top row -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="live-dot"></span>
            <span style="font-weight:700; font-size:15px; color:var(--muted2);">
              Uživo · Pitanje {{ currentQuestionIndex + 1 }} / {{ questions.length }}
            </span>
          </div>
        </div>

        <!-- Progress -->
        <div class="qa-progress" style="margin-bottom:24px;">
          <div class="qa-progress-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>

        <!-- Question area (left) + Leaderboard (always visible) -->
        <div class="game-layout">
          <div class="game-main">
            <div v-if="currentQuestion && !showResults">
              <QuestionCard
                :question="currentQuestion"
                :question-number="currentQuestionIndex + 1"
                :total-questions="questions.length"
                :is-submitted="showResults"
                :answer-feedback="answerFeedback"
                @answer-selected="handleAnswerSelected"
              />
              <div style="display:flex; justify-content:space-between; margin-top:20px;">
                <button class="btn-outline-mint" @click="previousQuestion" :disabled="currentQuestionIndex === 0">← Prethodno</button>
                <div style="display:flex; gap:10px;">
                  <button v-if="currentQuestionIndex < questions.length - 1" class="btn-mint" @click="nextQuestion">Dalje →</button>
                  <button v-else class="btn-mint" @click="completeGame" :disabled="answeredCount < questions.length">Završi igru</button>
                </div>
              </div>
            </div>

            <!-- Final results -->
            <div v-if="showResults" class="results-panel qa-card" style="padding:28px;">
              <h2 style="font-size:22px; margin-bottom:8px;">Igra završena!</h2>
              <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:32px; color:var(--accent); margin-bottom:4px;">
                {{ myScore }} / {{ totalPoints }}
              </div>
              <div style="font-size:14px; color:var(--faint); margin-bottom:20px;">{{ myPercentage }}% rezultat</div>
              <div style="display:flex; gap:10px;">
                <router-link to="/lobby" class="btn-mint">Natrag u predvorje</router-link>
                <router-link to="/quizzes" class="btn-outline-mint">Pregledaj kvizove</router-link>
              </div>
            </div>
          </div>

          <!-- Leaderboard sidebar -->
          <div class="leaderboard-panel">
            <h2 style="font-size:17px; margin-bottom:14px;">Ljestvica uživo</h2>
            <div class="lb-list">
              <div
                v-for="(player, idx) in leaderboard"
                :key="player.user_id"
                class="lb-row"
                :class="{ 'is-you': player.user_id === currentUserId }"
              >
                <div class="lb-rank" :style="{ color: rankColor(idx) }">{{ idx + 1 }}</div>
                <div class="avatar" :style="{ width:'40px', height:'40px', background: avatarColor(player.user_name), fontSize:'13px', color:'#fff' }">
                  {{ initials(player.user_name) }}
                </div>
                <div style="flex:1; min-width:0;">
                  <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-weight:700; font-size:14px;">{{ player.user_name }}</span>
                    <span v-if="player.user_id === currentUserId" class="pill pill-sm pill-student" style="font-size:10px; padding:2px 7px;">Vi</span>
                  </div>
                  <div class="lb-bar-track">
                    <div class="lb-bar-fill" :style="{ width: (player.percentage || 0) + '%' }"></div>
                  </div>
                </div>
                <div style="text-align:right;">
                  <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:16px;">{{ player.score }}</div>
                  <div style="font-size:11px; color:var(--faint);">{{ player.answers_count || 0 }} / {{ questions.length }}</div>
                </div>
              </div>
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
const lobbyId = ref(route.params.lobbyId)
const quiz = ref(null)
const questions = ref([])
const gameSession = ref(null)
const leaderboard = ref([])
const currentQuestionIndex = ref(0)
const answers = ref({})
const loading = ref(true)
const submitting = ref(false)
const showResults = ref(false)
const finalLeaderboard = ref([])
const answerFeedback = ref(null)
const pollInterval = ref(null)
const currentUserId = ref(null)

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || null)
const answeredCount = computed(() => Object.keys(answers.value).length)
const progressPercentage = computed(() => questions.value.length ? (answeredCount.value / questions.value.length) * 100 : 0)
const myScore = computed(() => gameSession.value?.score || 0)
const totalPoints = computed(() => gameSession.value?.total_points || 0)
const myPercentage = computed(() => totalPoints.value ? ((myScore.value / totalPoints.value) * 100).toFixed(0) : 0)

const avatarColors = ['#78c2ad', '#f3969a', '#5b7fd6', '#f5b740', '#56cc9d', '#ff7851']
const avatarColor = (name) => avatarColors[(name || '').length % avatarColors.length]
const initials = (name) => (name || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
const rankColor = (i) => {
  if (i === 0) return '#f5b740'
  if (i === 1) return '#b8c2c9'
  if (i === 2) return '#cd8b53'
  return '#aab8b3'
}

const fetchGameState = async () => {
  try {
    const r = await api.get(`/lobbies/${lobbyId.value}/game-state`)
    const d = r.data
    quiz.value = d.lobby.quiz
    questions.value = d.lobby.quiz.questions || []
    gameSession.value = d.game_session
    leaderboard.value = d.leaderboard || []
    if (d.game_session.answers) answers.value = d.game_session.answers
    const userStr = localStorage.getItem('user')
    if (userStr) currentUserId.value = JSON.parse(userStr).id
    if (d.game_session.status === 'completed' || d.lobby.status === 'completed') {
      showResults.value = true
      finalLeaderboard.value = d.leaderboard || []
      stopPolling()
    }
  } catch (e) {
    if (e.response?.status === 404) router.push({ name: 'Lobby' })
  } finally { loading.value = false }
}

const handleAnswerSelected = async (answer) => {
  if (submitting.value) return
  submitting.value = true; answerFeedback.value = null
  try {
    const r = await api.post(`/lobbies/${lobbyId.value}/submit-answer`, {
      question_id: answer.question_id,
      option_id: answer.option_id,
      answer_text: answer.answer_text,
    })
    answers.value[answer.question_id] = {
      option_id: answer.option_id,
      answer_text: answer.answer_text,
      is_correct: r.data.is_correct,
      points_earned: r.data.points_earned,
    }
    answerFeedback.value = { is_correct: r.data.is_correct, points_earned: r.data.points_earned }
    gameSession.value = r.data.game_session
    await fetchGameState()
  } catch {} finally { submitting.value = false }
}

const nextQuestion = () => { if (currentQuestionIndex.value < questions.value.length - 1) { currentQuestionIndex.value++; answerFeedback.value = null } }
const previousQuestion = () => { if (currentQuestionIndex.value > 0) { currentQuestionIndex.value--; answerFeedback.value = null } }

const completeGame = async () => {
  if (!confirm('Završiti igru? Nakon toga ne možete mijenjati odgovore.')) return
  submitting.value = true
  try {
    const r = await api.post(`/lobbies/${lobbyId.value}/complete`)
    showResults.value = true
    finalLeaderboard.value = r.data.leaderboard || []
    gameSession.value = r.data.game_session
    stopPolling()
  } catch {} finally { submitting.value = false }
}

const startPolling = () => { stopPolling(); pollInterval.value = setInterval(fetchGameState, 2000) }
const stopPolling = () => { if (pollInterval.value) { clearInterval(pollInterval.value); pollInterval.value = null } }

onMounted(async () => { await fetchGameState(); startPolling() })
onUnmounted(stopPolling)
</script>

<style scoped>
.game-page { min-height: 70vh; }
.game-inner { max-width: 1060px; margin: 0 auto; padding: 26px 28px 64px; }
.game-layout { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
.game-main { min-width: 0; }

.live-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--danger);
  box-shadow: 0 0 0 3px rgba(255,120,81,.25);
}

.leaderboard-panel {
  position: sticky;
  top: 80px;
}
.lb-list { display: flex; flex-direction: column; gap: 11px; }
.lb-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 12px;
  transition: background .15s;
}
.lb-row.is-you {
  background: var(--mint-soft2);
  border-color: var(--accent);
}
.lb-rank {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 16px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}
.lb-bar-track {
  height: 4px;
  background: var(--line2);
  border-radius: 99px;
  margin-top: 6px;
  overflow: hidden;
}
.lb-bar-fill {
  height: 100%;
  background: var(--accent);
  border-radius: 99px;
  transition: width .3s;
}

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 880px) {
  .game-layout { grid-template-columns: 1fr; }
  .leaderboard-panel { position: static; }
}
</style>
