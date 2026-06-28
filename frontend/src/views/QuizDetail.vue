<template>
  <div class="detail-page">
    <div class="detail-inner">
      <!-- Loading -->
      <div v-if="loading" style="text-align:center; padding:80px 0;">
        <div style="width:40px; height:40px; border:3px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto;"></div>
      </div>

      <div v-else-if="quiz">
        <!-- Breadcrumb -->
        <div class="detail-breadcrumb">
          <router-link to="/quizzes" style="color:var(--faint); text-decoration:none;">Pregled</router-link>
          <span style="color:var(--faint); margin:0 8px;">/</span>
          <span style="color:var(--faint);">{{ quiz.category || 'Općenito' }}</span>
          <span style="color:var(--faint); margin:0 8px;">/</span>
          <span style="color:var(--body-text); font-weight:700;">{{ quiz.title }}</span>
        </div>

        <div class="detail-grid two-col-layout">
          <!-- Left -->
          <div class="detail-left">
            <!-- Hero block -->
            <div class="detail-hero" :class="catClass">
              <div style="font-size:60px;">{{ catEmoji }}</div>
            </div>

            <!-- Badges -->
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
              <span class="pill pill-sm" :class="catPillClass">{{ quiz.category || 'Općenito' }}</span>
              <span class="pill pill-sm" :class="diffPillClass">{{ quiz.difficulty || 'medium' }}</span>
              <span class="pill pill-sm" style="background:var(--page-bg); color:var(--muted); border:1px solid var(--line);">◷ {{ quiz.duration || '?' }} min</span>
            </div>

            <h1 style="font-size:32px; margin-bottom:14px; line-height:1.25;">{{ quiz.title }}</h1>

            <div v-if="quiz.description" class="detail-desc" v-html="quiz.description"></div>
            <p v-else style="color:var(--muted); font-size:15px; line-height:1.6;">Opis nije naveden.</p>

            <!-- Stat tiles -->
            <div class="stat-tiles">
              <div class="stat-tile">
                <div class="stat-tile-num">{{ quiz.questions?.length || 0 }}</div>
                <div class="stat-tile-label">Pitanja</div>
              </div>
              <div class="stat-tile">
                <div class="stat-tile-num">{{ totalPoints }}</div>
                <div class="stat-tile-label">Ukupno bodova</div>
              </div>
              <div class="stat-tile">
                <div class="stat-tile-num">—</div>
                <div class="stat-tile-label">Igranja</div>
              </div>
            </div>

            <!-- Questions list (editable) -->
            <div v-if="isAuthenticated && canEdit && quiz.questions?.length" class="questions-section">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:19px;">Pitanja · {{ quiz.questions.length }}</h2>
                <span style="font-size:12px; color:var(--faint); font-weight:700;">⤢ Povucite za promjenu redoslijeda</span>
              </div>
              <div id="questions-list" class="questions-list">
                <div
                  v-for="(q, i) in quiz.questions"
                  :key="q.id"
                  :data-question-id="q.id"
                  class="question-row sortable-item"
                >
                  <span class="drag-handle" style="color:var(--faint2); cursor:move;">⠿</span>
                  <div class="q-num-tile">{{ i + 1 }}</div>
                  <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:14px;" v-html="q.text"></div>
                    <div style="font-size:12.5px; color:var(--faint); margin-top:2px;">
                      {{ q.options?.length || 0 }} opcija · {{ q.points }} bod.
                    </div>
                  </div>
                  <span class="pill pill-sm" :class="typePillClass(q.type)">{{ formatType(q.type) }}</span>
                  <div style="display:flex; gap:4px; margin-left:8px;">
                    <button class="btn-ghost" style="font-size:12px; padding:4px 8px;" @click="editQuestion(q)">Uredi</button>
                    <button class="btn-danger-text" style="font-size:12px; padding:4px 8px;" @click="deleteQuestion(q.id)">Obriši</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Question Form -->
            <div v-if="isAuthenticated && canEdit" style="margin-top:20px;">
              <QuestionForm
                :quiz-id="quiz.id"
                :question="editingQuestion"
                @saved="handleQuestionSaved"
                @cancelled="editingQuestion = null"
              />
            </div>
          </div>

          <!-- Right sidebar -->
          <div class="detail-sidebar">
            <div class="sidebar-card qa-card" style="padding:24px; position:sticky; top:80px;">
              <!-- Author -->
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                <div class="avatar" style="width:40px; height:40px; background:var(--accent2); color:#5a1f24; font-size:14px;">
                  {{ creatorInitials }}
                </div>
                <div>
                  <div style="font-weight:700; font-size:14px;">{{ quiz.creator?.name || 'Nepoznato' }}</div>
                  <div style="font-size:12.5px; color:var(--faint);">{{ quiz.creator?.user_type || 'Korisnik' }}</div>
                </div>
              </div>

              <!-- Facts box -->
              <div class="facts-box">
                <div class="fact-row"><span>Pitanja</span><span style="font-weight:700;">{{ quiz.questions?.length || 0 }}</span></div>
                <div class="fact-row"><span>Vremensko ograničenje</span><span style="font-weight:700;">{{ quiz.duration || '—' }} min</span></div>
                <div class="fact-row"><span>Ukupno bodova</span><span style="font-weight:700;">{{ totalPoints }}</span></div>
              </div>

              <!-- Actions -->
              <button
                v-if="isAuthenticated && quiz.questions?.length"
                class="btn-mint glow"
                style="width:100%; padding:14px; font-size:15px; border-radius:12px; margin-bottom:10px;"
                @click="startQuiz"
                :disabled="loadingQuiz"
              >
                {{ loadingQuiz ? 'Učitavanje...' : 'Započni kviz →' }}
              </button>
              <router-link
                v-if="isAuthenticated"
                to="/lobby"
                class="btn-outline-mint"
                style="width:100%; padding:13px; font-size:14px; border-radius:12px; text-align:center;"
              >Igraj s prijateljima</router-link>

              <div v-if="!isAuthenticated" style="margin-top:8px;">
                <router-link to="/login" class="btn-mint" style="width:100%; padding:14px; font-size:15px; border-radius:12px; text-align:center;">
                  Prijavite se za igranje
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Not found -->
      <div v-else style="text-align:center; padding:80px 0;">
        <p style="font-size:16px; color:var(--muted);">Kviz nije pronađen.</p>
        <router-link to="/quizzes" class="btn-mint" style="margin-top:16px;">Natrag na kvizove</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import QuestionForm from '../components/QuestionForm.vue'

const route = useRoute()
const router = useRouter()
const quiz = ref(null)
const loading = ref(true)
const loadingQuiz = ref(false)
const editingQuestion = ref(null)
const currentUser = ref(null)
let sortableInstance = null

const isAuthenticated = computed(() => !!localStorage.getItem('auth_token'))
const canEdit = computed(() => {
  if (!isAuthenticated.value || !currentUser.value || !quiz.value) return false
  return currentUser.value.id === quiz.value.created_by || currentUser.value.user_type === 'admin' || currentUser.value.user_type === 'teacher'
})

const totalPoints = computed(() => quiz.value?.questions?.reduce((sum, q) => sum + (q.points || 0), 0) || 0)

const creatorInitials = computed(() => {
  const name = quiz.value?.creator?.name || '?'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const catMap = {
  geography: { class: 'cat-geography', emoji: '🌍', pill: 'pill-student' },
  'computer science': { class: 'cat-computer-science', emoji: '💻', pill: 'pill-admin' },
  science: { class: 'cat-science', emoji: '🔬', pill: 'pill-teacher' },
  history: { class: 'cat-history', emoji: '📜', pill: 'pill-medium' },
  sports: { class: 'cat-sports', emoji: '⚽', pill: 'pill-medium' },
  'general knowledge': { class: 'cat-general-knowledge', emoji: '🧠', pill: 'pill-admin' },
}
const catKey = computed(() => (quiz.value?.category || '').toLowerCase())
const catClass = computed(() => catMap[catKey.value]?.class || 'cat-general-knowledge')
const catEmoji = computed(() => catMap[catKey.value]?.emoji || '📚')
const catPillClass = computed(() => catMap[catKey.value]?.pill || 'pill-student')
const diffPillClass = computed(() => {
  const d = (quiz.value?.difficulty || '').toLowerCase()
  if (d === 'easy') return 'pill-easy'
  if (d === 'hard') return 'pill-hard'
  return 'pill-medium'
})

const formatType = (t) => {
  if (t === 'multiple_choice') return 'Višestruki izbor'
  if (t === 'true_false') return 'Točno/Netočno'
  return 'Kratki odgovor'
}
const typePillClass = (t) => {
  if (t === 'true_false') return 'pill-medium'
  if (t === 'short_answer') return 'pill-teacher'
  return 'pill-student'
}

const fetchQuiz = async () => {
  try {
    const response = await api.get(`/quizzes/${route.params.id}`)
    quiz.value = response.data
    if (quiz.value.is_external && (!quiz.value.questions || !quiz.value.questions.length)) {
      try {
        const qr = await api.get(`/external/premade-quizzes/${route.params.id}`)
        if (qr.data.success && qr.data.quiz) quiz.value = qr.data.quiz
      } catch {}
    }
  } catch {
    quiz.value = null
  } finally {
    loading.value = false
  }
}

const startQuiz = () => {
  loadingQuiz.value = true
  const query = quiz.value.is_external ? { external: 'true' } : {}
  router.push({ name: 'QuizTaking', params: { id: quiz.value.id }, query })
}

const fetchCurrentUser = async () => {
  if (!isAuthenticated.value) return
  try {
    const r = await api.get('/user')
    currentUser.value = r.data
  } catch {}
}

const editQuestion = (q) => { editingQuestion.value = q }
const deleteQuestion = async (id) => {
  if (!confirm('Obrisati ovo pitanje?')) return
  try { await api.delete(`/questions/${id}`); await fetchQuiz() } catch {}
}
const handleQuestionSaved = async () => {
  editingQuestion.value = null
  await fetchQuiz()
  setTimeout(initializeSortable, 100)
}

const initializeSortable = async () => {
  if (!isAuthenticated.value || !canEdit.value) return
  if (sortableInstance) { sortableInstance.destroy(); sortableInstance = null }
  nextTick(async () => {
    try {
      const { default: Sortable } = await import('sortablejs')
      const el = document.getElementById('questions-list')
      if (!el) return
      sortableInstance = new Sortable(el, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: async () => {
          const items = el.querySelectorAll('.sortable-item')
          const order = []
          items.forEach((item, i) => {
            const id = item.getAttribute('data-question-id')
            if (id) order.push({ id, order: i })
          })
          try {
            await api.post(`/quizzes/${quiz.value.id}/questions/reorder`, { order })
            await fetchQuiz()
            setTimeout(initializeSortable, 100)
          } catch {}
        },
      })
    } catch {}
  })
}

onMounted(async () => {
  await fetchCurrentUser()
  await fetchQuiz()
  initializeSortable()
})
onUnmounted(() => { if (sortableInstance) sortableInstance.destroy() })
</script>

<style scoped>
.detail-page { min-height: 60vh; }
.detail-inner {
  max-width: 1020px;
  margin: 0 auto;
  padding: 26px 28px 64px;
}
.detail-breadcrumb {
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 22px;
}
.detail-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 28px;
  align-items: start;
}
.detail-hero {
  height: 220px;
  border-radius: var(--radius);
  background: var(--cat-grad, linear-gradient(135deg,#c3b6f7,#a596e0));
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}
.detail-desc {
  font-size: 15px;
  line-height: 1.7;
  color: var(--body-text);
  margin-bottom: 24px;
}
.stat-tiles {
  display: flex;
  gap: 14px;
  margin-bottom: 28px;
}
.stat-tile {
  flex: 1;
  background: var(--page-bg);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}
.stat-tile-num {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 24px;
  color: var(--ink);
}
.stat-tile-label {
  font-size: 12px;
  color: var(--faint);
  font-weight: 700;
  margin-top: 4px;
}
.facts-box {
  background: var(--page-bg);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
}
.fact-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14px;
  color: var(--muted);
}
.fact-row + .fact-row { border-top: 1px solid var(--line); }

.questions-section { margin-top: 8px; }
.questions-list { display: flex; flex-direction: column; gap: 6px; }
.question-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 10px;
}
.q-num-tile {
  width: 26px; height: 26px;
  border-radius: 7px;
  background: var(--mint-soft);
  color: var(--success-deep);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  font-size: 12px;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 880px) {
  .detail-grid { grid-template-columns: 1fr; }
  .detail-sidebar { order: -1; }
}
</style>
