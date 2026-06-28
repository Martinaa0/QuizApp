<template>
  <div class="create-page">
    <div class="create-inner">
      <!-- Header -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
        <div>
          <h1 style="font-size:28px; margin-bottom:4px;">Kreiraj kviz</h1>
          <span v-if="createdQuizId" style="font-size:13px; color:var(--faint); font-weight:700;">Skica · spremljeno</span>
        </div>
        <div style="display:flex; gap:10px;">
          <router-link to="/quizzes" class="btn-outline-mint" style="font-size:13.5px; padding:10px 16px;">Odustani</router-link>
          <button
            v-if="!createdQuizId"
            class="btn-mint"
            style="font-size:13.5px; padding:10px 18px;"
            @click="handleSubmit"
            :disabled="loading"
          >{{ loading ? 'Kreiranje...' : 'Objavi' }}</button>
          <button
            v-if="createdQuizId"
            class="btn-mint"
            style="font-size:13.5px; padding:10px 18px;"
            @click="finishCreating"
            :disabled="questions.length === 0"
          >Završi</button>
        </div>
      </div>

      <div class="create-grid two-col-layout">
        <!-- Left column -->
        <div class="create-left">
          <!-- Title + Description -->
          <div class="qa-card" style="padding:24px; margin-bottom:20px;">
            <input
              type="text"
              class="title-input"
              v-model="form.title"
              placeholder="Kviz bez naslova"
              required
            />
            <div style="margin-top:16px;">
              <RichTextEditor
                v-model="form.description"
                :height="180"
                placeholder="Dodajte opis…"
              />
            </div>
          </div>

          <!-- Questions -->
          <div v-if="createdQuizId" class="qa-card" style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h2 style="font-size:19px;">Pitanja · {{ questions.length }}</h2>
              <span style="font-size:12px; color:var(--faint); font-weight:700;">⤢ Povucite za promjenu redoslijeda</span>
            </div>

            <div class="questions-list">
              <div
                v-for="(q, i) in questions"
                :key="q.id || i"
                class="question-row"
              >
                <span style="color:var(--faint2); cursor:move; font-size:16px;">⠿</span>
                <div class="q-num-tile">{{ i + 1 }}</div>
                <div style="flex:1; min-width:0;">
                  <div style="font-weight:700; font-size:14px;" v-html="q.text || 'Bez naslova'"></div>
                  <div style="font-size:12px; color:var(--faint); margin-top:2px;">
                    {{ q.options?.length || 0 }} opcija · {{ q.points }} bod.
                  </div>
                </div>
                <span class="pill pill-sm" :class="typePillClass(q.type)">{{ formatType(q.type) }}</span>
                <button class="btn-ghost" style="font-size:12px; padding:4px 8px;" @click="editQuestion(q)">Uredi</button>
                <button class="btn-danger-text" style="font-size:12px; padding:4px 8px;" @click="removeQuestion(i)">Ukloni</button>
              </div>
            </div>

            <button
              class="add-question-btn"
              @click="showQuestionForm = true"
              v-if="!showQuestionForm && questions.length < 20"
            >+ Dodaj pitanje</button>

            <div v-if="showQuestionForm" style="margin-top:16px;">
              <QuestionForm
                :quiz-id="createdQuizId"
                :question="editingQuestion"
                @saved="handleQuestionSaved"
                @cancelled="cancelQuestionEdit"
              />
            </div>
          </div>
        </div>

        <!-- Right column -->
        <div class="create-right">
          <!-- Cover image -->
          <div class="qa-card" style="padding:20px; margin-bottom:16px;">
            <div class="eyebrow" style="margin-bottom:12px;">NASLOVNA SLIKA</div>
            <div class="drop-zone" @click="$refs.fileInput.click()">
              <img v-if="imagePreview" :src="imagePreview" style="width:100%; height:100%; object-fit:cover; border-radius:10px;" />
              <template v-else>
                <span style="font-size:24px; color:var(--faint2);">↑</span>
                <span style="font-size:12.5px; color:var(--faint); font-family:monospace;">povucite sliku · maks. 2MB</span>
              </template>
            </div>
            <input ref="fileInput" type="file" accept="image/*" @change="handleImageChange" style="display:none;" />
            <button v-if="imagePreview" class="btn-danger-text" style="margin-top:8px; font-size:12px;" @click="clearImage">Ukloni</button>
          </div>

          <!-- Settings -->
          <div class="qa-card" style="padding:20px;">
            <div class="eyebrow" style="margin-bottom:14px;">POSTAVKE</div>

            <label class="qa-label">Kategorija</label>
            <select class="qa-select" style="margin-bottom:14px;" v-model="form.category">
              <option value="">Odaberite kategoriju</option>
              <option value="General Knowledge">Opće znanje</option>
              <option value="Science">Znanost</option>
              <option value="History">Povijest</option>
              <option value="Geography">Geografija</option>
              <option value="Sports">Sport</option>
              <option value="Computer Science">Računarstvo</option>
            </select>

            <label class="qa-label">Težina</label>
            <div class="diff-segment">
              <button
                v-for="d in ['easy', 'medium', 'hard']"
                :key="d"
                :class="{ active: form.difficulty === d }"
                @click="form.difficulty = d"
              >{{ diffLabel(d) }}</button>
            </div>

            <label class="qa-label" style="margin-top:14px;">Vremensko ograničenje (min)</label>
            <input type="number" class="qa-input" v-model.number="form.duration" min="1" placeholder="15" />
          </div>
        </div>
      </div>

      <div v-if="error" style="background:#ffe9e2; color:var(--danger-text); padding:12px 14px; border-radius:10px; font-weight:700; margin-top:16px;">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import QuestionForm from '../components/QuestionForm.vue'
import RichTextEditor from '../components/RichTextEditor.vue'

const router = useRouter()
const form = ref({ title: '', description: '', category: '', difficulty: 'medium', duration: null, is_active: true })
const loading = ref(false)
const error = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)
const createdQuizId = ref(null)
const questions = ref([])
const showQuestionForm = ref(false)
const editingQuestion = ref(null)

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
const diffLabel = (d) => {
  if (d === 'easy') return 'Lagano'
  if (d === 'medium') return 'Srednje'
  if (d === 'hard') return 'Teško'
  return d
}

const handleImageChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) { error.value = 'Slika mora biti manja od 2MB'; return }
  imageFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => { imagePreview.value = ev.target.result }
  reader.readAsDataURL(file)
  error.value = null
}
const clearImage = () => { imageFile.value = null; imagePreview.value = null }

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  try {
    const fd = new FormData()
    fd.append('title', form.value.title)
    fd.append('description', form.value.description || '')
    fd.append('category', form.value.category || '')
    fd.append('difficulty', form.value.difficulty || '')
    if (form.value.duration) fd.append('duration', form.value.duration)
    fd.append('is_active', form.value.is_active ? '1' : '0')
    if (imageFile.value) fd.append('image', imageFile.value)

    const r = await api.post('/quizzes', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    createdQuizId.value = r.data.quiz.id
    showQuestionForm.value = true
    await fetchQuestions()
  } catch (err) {
    if (err.response?.data?.errors) error.value = Object.values(err.response.data.errors).flat().join(', ')
    else error.value = err.response?.data?.message || 'Kreiranje kviza nije uspjelo.'
  } finally { loading.value = false }
}

const fetchQuestions = async () => {
  if (!createdQuizId.value) return
  try {
    const r = await api.get(`/questions?quiz_id=${createdQuizId.value}`)
    questions.value = r.data.data || r.data || []
  } catch {}
}

const handleQuestionSaved = async () => {
  showQuestionForm.value = false
  editingQuestion.value = null
  await fetchQuestions()
}
const editQuestion = (q) => { editingQuestion.value = q; showQuestionForm.value = true }
const removeQuestion = async (i) => {
  const q = questions.value[i]
  if (!q.id) { questions.value.splice(i, 1); return }
  if (!confirm('Ukloniti ovo pitanje?')) return
  try { await api.delete(`/questions/${q.id}`); await fetchQuestions() } catch {}
}
const cancelQuestionEdit = () => { editingQuestion.value = null; showQuestionForm.value = false }
const finishCreating = () => { router.push({ name: 'QuizDetail', params: { id: createdQuizId.value } }) }
</script>

<style scoped>
.create-page { min-height: 70vh; }
.create-inner { max-width: 1080px; margin: 0 auto; padding: 26px 28px 64px; }
.create-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
.title-input {
  width: 100%;
  border: none;
  border-bottom: 2px solid var(--line);
  padding: 8px 0;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 22px;
  color: var(--ink);
  outline: none;
  background: transparent;
}
.title-input::placeholder { color: var(--faint2); }
.title-input:focus { border-bottom-color: var(--accent); }

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
  font-weight: 700; font-size: 12px; flex-shrink: 0;
}

.add-question-btn {
  width: 100%;
  padding: 14px;
  margin-top: 12px;
  border: 2px dashed var(--line);
  border-radius: 12px;
  background: transparent;
  color: var(--faint);
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  transition: border-color .15s, color .15s;
}
.add-question-btn:hover { border-color: var(--accent); color: var(--accent); }

.drop-zone {
  width: 100%;
  height: 120px;
  border: 2px dashed var(--line);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  background: repeating-linear-gradient(135deg, transparent, transparent 8px, rgba(231,239,235,.4) 8px, rgba(231,239,235,.4) 16px);
  transition: border-color .15s;
  overflow: hidden;
}
.drop-zone:hover { border-color: var(--accent); }

.diff-segment {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0;
  border: 1px solid var(--line);
  border-radius: 10px;
  overflow: hidden;
}
.diff-segment button {
  padding: 10px;
  border: none;
  background: var(--surface);
  font-family: 'Lato', sans-serif;
  font-weight: 700;
  font-size: 13px;
  color: var(--muted);
  cursor: pointer;
  transition: all .15s;
}
.diff-segment button + button { border-left: 1px solid var(--line); }
.diff-segment button.active {
  background: var(--accent);
  color: #fff;
}

@media (max-width: 880px) {
  .create-grid { grid-template-columns: 1fr; }
}
</style>
