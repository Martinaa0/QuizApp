<template>
  <div class="multiplayer-game">
    <div class="container mt-4">
      <!-- Header with Leaderboard -->
      <div class="row">
        <div class="col-md-8">
          <div v-if="quiz" class="mb-4">
            <h2>{{ quiz.title }}</h2>
            <p v-if="quiz.description" class="text-muted">
              {{ stripHtml(quiz.description) }}
            </p>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-muted">
                Progress: {{ answeredCount }} / {{ questions.length }} answered
              </span>
              <span class="small text-muted">
                Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
              </span>
            </div>
            <div class="progress" style="height: 25px;">
              <div
                class="progress-bar"
                :class="{
                  'bg-success': allQuestionsAnswered,
                  'bg-primary': !allQuestionsAnswered,
                }"
                :style="{ width: progressPercentage + '%' }"
              >
                {{ Math.round(progressPercentage) }}%
              </div>
            </div>
          </div>

          <!-- Question -->
          <div v-if="currentQuestion && !showResults">
            <QuestionCard
              :question="currentQuestion"
              :question-number="currentQuestionIndex + 1"
              :total-questions="questions.length"
              :is-submitted="showResults"
              :answer-feedback="answerFeedback"
              @answer-selected="handleAnswerSelected"
            />

            <!-- Navigation -->
            <div class="d-flex justify-content-between mt-4">
              <button
                class="btn btn-secondary"
                @click="previousQuestion"
                :disabled="currentQuestionIndex === 0"
              >
                ← Previous
              </button>
              <div>
                <button
                  v-if="currentQuestionIndex < questions.length - 1"
                  class="btn btn-primary"
                  @click="nextQuestion"
                >
                  Next Question →
                </button>
                <button
                  v-else
                  class="btn btn-success"
                  @click="completeGame"
                  :disabled="answeredCount < questions.length"
                >
                  Complete Game
                </button>
              </div>
            </div>
          </div>

          <!-- Results -->
          <div v-if="showResults" class="card">
            <div class="card-header bg-success text-white">
              <h4 class="mb-0">Game Completed!</h4>
            </div>
            <div class="card-body">
              <div class="text-center mb-4">
                <h3>Your Score: {{ myScore }} / {{ totalPoints }}</h3>
                <h4>Percentage: {{ myPercentage }}%</h4>
              </div>
              <h5>Final Leaderboard:</h5>
              <div class="list-group">
                <div
                  v-for="(player, index) in finalLeaderboard"
                  :key="player.user_id"
                  class="list-group-item d-flex justify-content-between align-items-center"
                  :class="{
                    'bg-warning': player.user_id === currentUserId,
                    'bg-light': index === 0 && player.user_id !== currentUserId,
                  }"
                >
                  <div>
                    <strong>#{{ index + 1 }} {{ player.user_name }}</strong>
                    <span v-if="player.user_id === currentUserId" class="badge bg-primary ms-2">
                      You
                    </span>
                  </div>
                  <div>
                    <span class="badge bg-success">
                      {{ player.score }} / {{ player.total_points }} ({{ player.percentage }}%)
                    </span>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <router-link to="/lobby" class="btn btn-primary">
                  Back to Lobby
                </router-link>
                <router-link to="/quizzes" class="btn btn-secondary ms-2">
                  Browse Quizzes
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Live Leaderboard Sidebar -->
        <div class="col-md-4">
          <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
              <h5 class="mb-0">Live Leaderboard</h5>
            </div>
            <div class="card-body">
              <div v-if="leaderboard.length === 0" class="text-muted text-center">
                No players yet
              </div>
              <div v-else class="list-group list-group-flush">
                <div
                  v-for="(player, index) in leaderboard"
                  :key="player.user_id"
                  class="list-group-item d-flex justify-content-between align-items-center px-0"
                  :class="{
                    'bg-warning': player.user_id === currentUserId,
                    'bg-light': index === 0 && player.user_id !== currentUserId,
                  }"
                >
                  <div>
                    <strong>#{{ index + 1 }} {{ player.user_name }}</strong>
                    <span v-if="player.user_id === currentUserId" class="badge bg-primary ms-1">
                      You
                    </span>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold">{{ player.score }} pts</div>
                    <small class="text-muted">{{ player.answers_count }}/{{ questions.length }}</small>
                  </div>
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

const currentQuestion = computed(() => {
  return questions.value[currentQuestionIndex.value] || null
})

const answeredCount = computed(() => {
  return Object.keys(answers.value).length
})

const allQuestionsAnswered = computed(() => {
  return answeredCount.value >= questions.value.length
})

const progressPercentage = computed(() => {
  return questions.value.length > 0
    ? (answeredCount.value / questions.value.length) * 100
    : 0
})

const myScore = computed(() => {
  return gameSession.value?.score || 0
})

const totalPoints = computed(() => {
  return gameSession.value?.total_points || 0
})

const myPercentage = computed(() => {
  return totalPoints.value > 0
    ? ((myScore.value / totalPoints.value) * 100).toFixed(2)
    : 0
})

const stripHtml = (html) => {
  if (!html) return ''
  const tempDiv = document.createElement('div')
  tempDiv.innerHTML = html
  return tempDiv.textContent || tempDiv.innerText || ''
}

const fetchGameState = async () => {
  try {
    const response = await api.get(`/lobbies/${lobbyId.value}/game-state`)
    const data = response.data

    quiz.value = data.lobby.quiz
    questions.value = data.lobby.quiz.questions || []
    gameSession.value = data.game_session
    leaderboard.value = data.leaderboard || []

    // Učitaj odgovore iz game session
    if (data.game_session.answers) {
      answers.value = data.game_session.answers
    }

    // Postavi current user ID
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const user = JSON.parse(userStr)
      currentUserId.value = user.id
    }

    // Ako je igra završena, prikaži rezultate
    if (data.game_session.status === 'completed' || data.lobby.status === 'completed') {
      showResults.value = true
      finalLeaderboard.value = data.leaderboard || []
      stopPolling()
    }
  } catch (error) {
    console.error('Error fetching game state:', error)
    if (error.response?.status === 404) {
      router.push({ name: 'Lobby' })
    }
  } finally {
    loading.value = false
  }
}

const handleAnswerSelected = async (answer) => {
  if (submitting.value || isSubmitted.value) return

  submitting.value = true
  answerFeedback.value = null

  try {
    const response = await api.post(`/lobbies/${lobbyId.value}/submit-answer`, {
      question_id: answer.question_id,
      option_id: answer.option_id,
      answer_text: answer.answer_text,
    })

    // Ažuriraj lokalne odgovore
    answers.value[answer.question_id] = {
      option_id: answer.option_id,
      answer_text: answer.answer_text,
      is_correct: response.data.is_correct,
      points_earned: response.data.points_earned,
    }

    answerFeedback.value = {
      is_correct: response.data.is_correct,
      points_earned: response.data.points_earned,
    }

    // Ažuriraj game session
    gameSession.value = response.data.game_session

    // Osvježi leaderboard
    await fetchGameState()
  } catch (error) {
    console.error('Error submitting answer:', error)
    alert('Failed to submit answer. Please try again.')
  } finally {
    submitting.value = false
  }
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
    answerFeedback.value = null
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
    answerFeedback.value = null
  }
}

const completeGame = async () => {
  if (!confirm('Are you sure you want to complete the game? You cannot change your answers after submitting.')) {
    return
  }

  submitting.value = true

  try {
    const response = await api.post(`/lobbies/${lobbyId.value}/complete`)
    
    showResults.value = true
    finalLeaderboard.value = response.data.leaderboard || []
    gameSession.value = response.data.game_session
    
    stopPolling()
  } catch (error) {
    console.error('Error completing game:', error)
    alert('Failed to complete game. Please try again.')
  } finally {
    submitting.value = false
  }
}

const startPolling = () => {
  stopPolling()
  pollInterval.value = setInterval(fetchGameState, 2000) // Poll every 2 seconds
}

const stopPolling = () => {
  if (pollInterval.value) {
    clearInterval(pollInterval.value)
    pollInterval.value = null
  }
}

onMounted(async () => {
  await fetchGameState()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.multiplayer-game {
  min-height: 70vh;
}
</style>
