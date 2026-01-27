<template>
  <div class="lobby-page">
    <div class="container mt-5">
      <!-- Create Lobby Section -->
      <div v-if="!currentLobby" class="row">
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Create Lobby</h5>
            </div>
            <div class="card-body">
              <form @submit.prevent="createLobby">
                <div class="mb-3">
                  <label for="quiz-select" class="form-label">Select Quiz *</label>
                  <select
                    id="quiz-select"
                    class="form-select"
                    v-model="selectedQuizId"
                    required
                  >
                    <option value="">Choose a quiz...</option>
                    <option
                      v-for="quiz in availableQuizzes"
                      :key="quiz.id"
                      :value="quiz.id"
                    >
                      {{ quiz.title }} ({{ quiz.questions?.length || 0 }} questions)
                    </option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="max-players" class="form-label">Max Players</label>
                  <input
                    type="number"
                    id="max-players"
                    class="form-control"
                    v-model.number="maxPlayers"
                    min="2"
                    max="20"
                  />
                  <div class="form-text">Default: 10 players</div>
                </div>
                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                  {{ loading ? 'Creating...' : 'Create Lobby' }}
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Join Lobby Section -->
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Join Lobby</h5>
            </div>
            <div class="card-body">
              <form @submit.prevent="joinLobby">
                <div class="mb-3">
                  <label for="lobby-code" class="form-label">Lobby Code</label>
                  <input
                    type="text"
                    id="lobby-code"
                    class="form-control text-uppercase"
                    v-model="lobbyCode"
                    placeholder="Enter 6-letter code"
                    maxlength="6"
                    required
                  />
                </div>
                <button type="submit" class="btn btn-success w-100" :disabled="loading">
                  {{ loading ? 'Joining...' : 'Join Lobby' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Lobby Room -->
      <div v-if="currentLobby" class="lobby-room">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h4 class="mb-0">{{ currentLobby.quiz?.title }}</h4>
              <small class="text-muted">Lobby Code: <strong>{{ currentLobby.code }}</strong></small>
            </div>
            <div>
              <span class="badge bg-primary me-2">
                {{ currentLobby.current_players }} / {{ currentLobby.max_players }} Players
              </span>
              <span
                class="badge"
                :class="{
                  'bg-success': currentLobby.status === 'waiting',
                  'bg-warning': currentLobby.status === 'starting',
                  'bg-info': currentLobby.status === 'in_progress',
                  'bg-secondary': currentLobby.status === 'completed',
                }"
              >
                {{ currentLobby.status }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <!-- Players List -->
            <div class="mb-4">
              <h5>Players</h5>
              <div class="list-group">
                <div
                  v-for="session in currentLobby.game_sessions"
                  :key="session.id"
                  class="list-group-item d-flex justify-content-between align-items-center"
                  :class="{ 'bg-light': session.user_id === currentLobby.host_id }"
                >
                  <div>
                    <strong>{{ session.user?.name }}</strong>
                    <span v-if="session.user_id === currentLobby.host_id" class="badge bg-primary ms-2">
                      Host
                    </span>
                  </div>
                  <div v-if="currentLobby.status === 'in_progress'">
                    <span class="badge bg-success">
                      Score: {{ session.score || 0 }} / {{ session.total_points || 0 }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2">
              <button
                v-if="isHost && currentLobby.status === 'waiting' && currentLobby.current_players >= 2"
                class="btn btn-success"
                @click="startGame"
                :disabled="loading"
              >
                Start Game
              </button>
              <button
                v-if="currentLobby.status === 'waiting'"
                class="btn btn-danger"
                @click="leaveLobby"
                :disabled="loading"
              >
                Leave Lobby
              </button>
              <button
                v-if="currentLobby.status === 'in_progress'"
                class="btn btn-primary"
                @click="goToGame"
              >
                Go to Game
              </button>
            </div>

            <!-- Share Code -->
            <div class="mt-4 p-3 bg-light rounded">
              <p class="mb-2"><strong>Share this code with friends:</strong></p>
              <div class="input-group">
                <input
                  type="text"
                  class="form-control text-center fw-bold"
                  :value="currentLobby.code"
                  readonly
                  id="lobby-code-input"
                />
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="copyCode"
                >
                  Copy
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="alert alert-danger mt-3" role="alert">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const currentLobby = ref(null)
const availableQuizzes = ref([])
const selectedQuizId = ref('')
const maxPlayers = ref(10)
const lobbyCode = ref('')
const loading = ref(false)
const error = ref(null)
const pollInterval = ref(null)

const isHost = ref(false)

const fetchQuizzes = async () => {
  try {
    const response = await api.get('/quizzes')
    availableQuizzes.value = (response.data.data || response.data || []).filter(
      (quiz) => quiz.questions && quiz.questions.length > 0 && !quiz.is_external
    )
  } catch (err) {
    console.error('Error fetching quizzes:', err)
  }
}

const createLobby = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.post('/lobbies', {
      quiz_id: selectedQuizId.value,
      max_players: maxPlayers.value,
    })

    currentLobby.value = response.data.lobby
    isHost.value = true
    startPolling()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create lobby'
    console.error('Error creating lobby:', err)
  } finally {
    loading.value = false
  }
}

const joinLobby = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.post(`/lobbies/join/${lobbyCode.value.toUpperCase()}`)
    currentLobby.value = response.data.lobby
    isHost.value = false
    startPolling()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to join lobby'
    console.error('Error joining lobby:', err)
  } finally {
    loading.value = false
  }
}

const startGame = async () => {
  if (!isHost.value) return

  loading.value = true
  error.value = null

  try {
    const response = await api.post(`/lobbies/${currentLobby.value.id}/start`)
    currentLobby.value = response.data.lobby

    // Preusmjeri na igru nakon kratke pauze
    setTimeout(() => {
      router.push({
        name: 'MultiplayerGame',
        params: { lobbyId: currentLobby.value.id },
      })
    }, 1000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to start game'
    console.error('Error starting game:', err)
  } finally {
    loading.value = false
  }
}

const leaveLobby = async () => {
  if (!confirm('Are you sure you want to leave this lobby?')) return

  loading.value = true
  error.value = null

  try {
    await api.post(`/lobbies/${currentLobby.value.id}/leave`)
    currentLobby.value = null
    isHost.value = false
    stopPolling()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to leave lobby'
    console.error('Error leaving lobby:', err)
  } finally {
    loading.value = false
  }
}

const goToGame = () => {
  router.push({
    name: 'MultiplayerGame',
    params: { lobbyId: currentLobby.value.id },
  })
}

const copyCode = () => {
  const input = document.getElementById('lobby-code-input')
  input.select()
  document.execCommand('copy')
  alert('Code copied to clipboard!')
}

const pollLobby = async () => {
  if (!currentLobby.value) return

  try {
    const response = await api.get(`/lobbies/${currentLobby.value.id}`)
    currentLobby.value = response.data

    // Ako je igra počela, preusmjeri
    if (currentLobby.value.status === 'in_progress') {
      router.push({
        name: 'MultiplayerGame',
        params: { lobbyId: currentLobby.value.id },
      })
    }
  } catch (err) {
    console.error('Error polling lobby:', err)
  }
}

const startPolling = () => {
  stopPolling()
  pollInterval.value = setInterval(pollLobby, 2000) // Poll every 2 seconds
}

const stopPolling = () => {
  if (pollInterval.value) {
    clearInterval(pollInterval.value)
    pollInterval.value = null
  }
}

onMounted(() => {
  fetchQuizzes()
})

onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.lobby-page {
  min-height: 70vh;
}

.lobby-room {
  max-width: 800px;
  margin: 0 auto;
}
</style>
