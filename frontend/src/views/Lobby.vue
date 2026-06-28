<template>
  <div class="lobby-page">
    <div class="lobby-inner">
      <!-- Create/Join -->
      <div v-if="!currentLobby" class="lobby-setup">
        <div class="lobby-grid two-col-layout">
          <!-- Create -->
          <div class="qa-card" style="padding:24px;">
            <h2 style="font-size:19px; margin-bottom:4px;">Kreiraj predvorje</h2>
            <p style="font-size:14px; color:var(--faint); margin-bottom:20px;">Odaberite kviz i pozovite prijatelje.</p>
            <form @submit.prevent="createLobby">
              <label class="qa-label">Odaberite kviz</label>
              <select class="qa-select" style="margin-bottom:14px;" v-model="selectedQuizId" required>
                <option value="">Odaberite kviz…</option>
                <option v-for="q in availableQuizzes" :key="q.id" :value="q.id">
                  {{ q.title }} ({{ q.questions?.length || 0 }}P)
                </option>
              </select>
              <label class="qa-label">Maks. igrača</label>
              <input type="number" class="qa-input" style="margin-bottom:18px;" v-model.number="maxPlayers" min="2" max="20" />
              <button type="submit" class="btn-mint" style="width:100%; padding:14px; font-size:15px;" :disabled="loading">
                {{ loading ? 'Kreiranje...' : 'Kreiraj predvorje' }}
              </button>
            </form>
          </div>
          <!-- Join -->
          <div class="qa-card" style="padding:24px;">
            <h2 style="font-size:19px; margin-bottom:4px;">Pridruži se predvorju</h2>
            <p style="font-size:14px; color:var(--faint); margin-bottom:20px;">Unesite 6-znakovni kod.</p>
            <form @submit.prevent="joinLobby">
              <label class="qa-label">Kod predvorja</label>
              <input type="text" class="qa-input code-input" v-model="lobbyCode" placeholder="A B C D E F" maxlength="6" required />
              <button type="submit" class="btn-mint" style="width:100%; padding:14px; font-size:15px; margin-top:18px;" :disabled="loading">
                {{ loading ? 'Pridruživanje...' : 'Pridruži se' }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Lobby Room -->
      <div v-if="currentLobby" class="lobby-room">
        <div class="lobby-room-grid two-col-layout">
          <!-- Left: dark card with code -->
          <div class="lobby-dark-card">
            <div class="eyebrow" style="color:var(--dark-muted); margin-bottom:14px;">KOD ZA PRIDRUŽIVANJE</div>
            <div class="code-tiles">
              <div v-for="(ch, i) in codeChars" :key="i" class="code-tile">{{ ch }}</div>
            </div>
            <button class="copy-btn" @click="copyCode">⧉ Kopiraj poveznicu za poziv</button>
            <div style="height:1px; background:rgba(255,255,255,.1); margin:20px 0;"></div>
            <div style="font-size:14px; color:var(--dark-muted2); line-height:1.6;">
              {{ currentLobby.quiz?.title }} · {{ currentLobby.quiz?.questions?.length || '?' }} pitanja · Domaćin ste vi
            </div>
            <div style="display:flex; gap:24px; margin-top:18px;">
              <div>
                <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:22px; color:var(--dark-text);">{{ currentLobby.current_players }} / {{ currentLobby.max_players }}</div>
                <div style="font-size:12px; color:var(--dark-muted); font-weight:700;">Igrači</div>
              </div>
              <div>
                <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:22px; color:#56cc9d;">{{ currentLobby.status }}</div>
                <div style="font-size:12px; color:var(--dark-muted); font-weight:700;">Status</div>
              </div>
            </div>
          </div>

          <!-- Right: players list -->
          <div>
            <div style="font-size:15px; font-weight:700; color:var(--muted2); margin-bottom:14px;">
              Igrači u predvorju · {{ currentLobby.current_players }} pridruženo
            </div>
            <div class="players-list">
              <div
                v-for="session in currentLobby.game_sessions"
                :key="session.id"
                class="player-row qa-card"
                style="padding:12px 16px; margin-bottom:8px;"
              >
                <div class="avatar" :style="{ width:'38px', height:'38px', background: avatarColor(session.user?.name), fontSize:'13px', color:'#fff' }">
                  {{ initials(session.user?.name) }}
                </div>
                <div style="flex:1; min-width:0;">
                  <div style="font-weight:700; font-size:14px;">{{ session.user?.name }}</div>
                  <div style="font-size:12px; color:var(--faint);">@{{ session.user?.username }}</div>
                </div>
                <span class="pill pill-sm" :class="session.user_id === currentLobby.host_id ? 'pill-student' : 'pill-easy'">
                  {{ session.user_id === currentLobby.host_id ? 'Domaćin' : 'Spreman' }}
                </span>
              </div>
              <div class="waiting-row">Čekanje na više igrača…</div>
            </div>

            <button
              v-if="isHost && currentLobby.status === 'waiting' && currentLobby.current_players >= 2"
              class="btn-mint glow"
              style="width:100%; padding:14px; font-size:15px; margin-top:16px;"
              @click="startGame"
              :disabled="loading"
            >Započni igru · {{ currentLobby.current_players }} igrača</button>

            <div style="display:flex; gap:10px; margin-top:10px;">
              <button v-if="currentLobby.status === 'waiting'" class="btn-outline-mint" style="flex:1;" @click="leaveLobby" :disabled="loading">Napusti predvorje</button>
              <button v-if="currentLobby.status === 'in_progress'" class="btn-mint" style="flex:1;" @click="goToGame">Idi na igru</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="error" style="background:#ffe9e2; color:var(--danger-text); padding:12px 14px; border-radius:10px; font-weight:700; margin-top:16px;">{{ error }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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

const codeChars = computed(() => (currentLobby.value?.code || '').split(''))

const avatarColors = ['#78c2ad', '#f3969a', '#5b7fd6', '#f5b740', '#56cc9d', '#ff7851']
const avatarColor = (name) => avatarColors[(name || '').length % avatarColors.length]
const initials = (name) => (name || '?').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)

const fetchQuizzes = async () => {
  try {
    const r = await api.get('/quizzes')
    availableQuizzes.value = (r.data.data || r.data || []).filter(q => q.questions?.length > 0 && !q.is_external)
  } catch {}
}

const createLobby = async () => {
  loading.value = true; error.value = null
  try {
    const r = await api.post('/lobbies', { quiz_id: selectedQuizId.value, max_players: maxPlayers.value })
    currentLobby.value = r.data.lobby; isHost.value = true; startPolling()
  } catch (e) { error.value = e.response?.data?.message || 'Kreiranje predvorja nije uspjelo' }
  finally { loading.value = false }
}

const joinLobby = async () => {
  loading.value = true; error.value = null
  try {
    const r = await api.post(`/lobbies/join/${lobbyCode.value.toUpperCase()}`)
    currentLobby.value = r.data.lobby; isHost.value = false; startPolling()
  } catch (e) { error.value = e.response?.data?.message || 'Pridruživanje predvorju nije uspjelo' }
  finally { loading.value = false }
}

const startGame = async () => {
  if (!isHost.value) return
  loading.value = true; error.value = null
  try {
    const r = await api.post(`/lobbies/${currentLobby.value.id}/start`)
    currentLobby.value = r.data.lobby
    setTimeout(() => router.push({ name: 'MultiplayerGame', params: { lobbyId: currentLobby.value.id } }), 1000)
  } catch (e) { error.value = e.response?.data?.message || 'Pokretanje nije uspjelo' }
  finally { loading.value = false }
}

const leaveLobby = async () => {
  if (!confirm('Napustiti ovo predvorje?')) return
  loading.value = true
  try { await api.post(`/lobbies/${currentLobby.value.id}/leave`); currentLobby.value = null; isHost.value = false; stopPolling() }
  catch (e) { error.value = e.response?.data?.message || 'Napuštanje nije uspjelo' }
  finally { loading.value = false }
}

const goToGame = () => router.push({ name: 'MultiplayerGame', params: { lobbyId: currentLobby.value.id } })

const copyCode = () => {
  navigator.clipboard?.writeText(currentLobby.value.code).then(() => alert('Kod kopiran!'))
}

const pollLobby = async () => {
  if (!currentLobby.value) return
  try {
    const r = await api.get(`/lobbies/${currentLobby.value.id}`)
    currentLobby.value = r.data
    if (currentLobby.value.status === 'in_progress') {
      router.push({ name: 'MultiplayerGame', params: { lobbyId: currentLobby.value.id } })
    }
  } catch {}
}

const startPolling = () => { stopPolling(); pollInterval.value = setInterval(pollLobby, 2000) }
const stopPolling = () => { if (pollInterval.value) { clearInterval(pollInterval.value); pollInterval.value = null } }

onMounted(fetchQuizzes)
onUnmounted(stopPolling)
</script>

<style scoped>
.lobby-page { min-height: 70vh; }
.lobby-inner { max-width: 980px; margin: 0 auto; padding: 30px 28px 64px; }
.lobby-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.lobby-room-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; align-items: start; }
.code-input {
  text-transform: uppercase;
  text-align: center;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 22px;
  letter-spacing: 8px;
}

.lobby-dark-card {
  background: linear-gradient(135deg, var(--dark), var(--dark2));
  border-radius: 18px;
  padding: 28px;
  color: var(--dark-text);
}
.code-tiles {
  display: flex;
  gap: 8px;
  margin-bottom: 18px;
}
.code-tile {
  width: 46px; height: 58px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 28px;
  color: var(--dark-text);
}
.copy-btn {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 10px;
  background: transparent;
  color: var(--dark-muted);
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  transition: background .15s;
}
.copy-btn:hover { background: rgba(255,255,255,.06); }

.players-list { display: flex; flex-direction: column; }
.player-row { display: flex; align-items: center; gap: 12px; }
.waiting-row {
  padding: 14px;
  border: 2px dashed var(--line);
  border-radius: 12px;
  text-align: center;
  color: var(--faint);
  font-weight: 700;
  font-size: 13px;
}

@media (max-width: 880px) {
  .lobby-grid, .lobby-room-grid { grid-template-columns: 1fr; }
}
</style>
