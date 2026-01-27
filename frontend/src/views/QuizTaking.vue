<template>
  <div class="quiz-taking">
    <div class="container mt-4">
      <!-- Header -->
      <div v-if="quiz" class="d-flex justify-content-between align-items-center mb-4">
        <div class="flex-grow-1">
          <h2 class="mb-2">{{ quiz.title }}</h2>
          <p v-if="quiz.description" class="text-muted mb-0" style="line-height: 1.6;">
            {{ stripHtml(quiz.description) }}
          </p>
        </div>
        <div class="text-end">
          <div v-if="timer > 0" class="timer-badge">
            <span
              class="badge fs-6"
              :class="{
                'bg-danger': timer < 60,
                'bg-warning text-dark': timer >= 60 && timer < 300,
                'bg-success': timer >= 300,
              }"
            >
              ⏱️ Time: {{ formatTime(timer) }}
            </span>
          </div>
          <div v-else class="timer-badge">
            <span class="badge bg-danger fs-6">Time's Up!</span>
          </div>
        </div>
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
            role="progressbar"
          >
            {{ Math.round(progressPercentage) }}%
          </div>
        </div>
        <div v-if="allQuestionsAnswered && !isSubmitted" class="alert alert-success mt-2 mb-0">
          <strong>All questions answered!</strong> Submitting quiz...
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Question -->
      <div v-else-if="currentQuestion && !allQuestionsAnswered">
        <QuestionCard
          :question="currentQuestion"
          :question-number="currentQuestionIndex + 1"
          :total-questions="questions.length"
          :is-submitted="isSubmitted"
          :answer-feedback="answerFeedback"
          @answer-selected="handleAnswerSelected"
        />

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4">
          <button
            class="btn btn-secondary"
            @click="previousQuestion"
            :disabled="currentQuestionIndex === 0 || submitting"
          >
            ← Previous
          </button>
          <div>
            <button
              v-if="currentQuestionIndex < questions.length - 1 && !allQuestionsAnswered"
              class="btn btn-primary"
              @click="nextQuestion"
            >
              Next Question →
            </button>
            <button
              v-else-if="!allQuestionsAnswered"
              class="btn btn-warning"
              @click="submitQuiz"
              :disabled="submitting || answeredCount < questions.length"
              :title="answeredCount < questions.length ? 'Please answer all questions' : ''"
            >
              {{ submitting ? 'Submitting...' : 'Submit Quiz (Not all answered)' }}
            </button>
            <button
              v-else
              class="btn btn-success"
              @click="submitQuiz"
              :disabled="submitting"
            >
              {{ submitting ? 'Submitting...' : 'Submit Quiz ✓' }}
            </button>
          </div>
        </div>
      </div>

      <!-- All Questions Answered Message -->
      <div v-if="allQuestionsAnswered && !showResults && !submitting" class="alert alert-info mt-3">
        <strong>Great job!</strong> You've answered all questions. The quiz will be submitted automatically, or you can click "Submit Quiz" to finish now.
      </div>

      <!-- Results -->
      <div v-if="showResults" class="results mt-5 quiz-results">
        <div class="card">
          <div class="card-header bg-success text-white">
            <h4 class="mb-0">Quiz Completed!</h4>
          </div>
          <div class="card-body">
            <div class="results-summary">
              <div class="row text-center mb-4">
                <div class="col-md-4">
                  <div class="score-display">
                    <h3>{{ results.score || 0 }}</h3>
                    <p class="text-muted">Points Earned</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="score-display">
                    <h3>{{ results.total_points || 0 }}</h3>
                    <p class="text-muted">Total Points</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="score-display">
                    <h3>{{ results.percentage ? Math.round(results.percentage) : 0 }}%</h3>
                    <p class="text-muted">Score</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detailed Results -->
            <div v-if="detailedResults" class="detailed-results mt-4">
              <h5 class="mb-3">Question Review</h5>
              <div
                v-for="(result, index) in detailedResults"
                :key="index"
                class="question-item mb-3"
              >
                <div class="question-text">
                  {{ index + 1 }}. {{ result.question_text }}
                </div>
                <div class="question-options mt-2">
                  <div
                    v-for="option in result.options"
                    :key="option.id"
                    class="option-item"
                    :class="{
                      'correct-answer': option.is_correct,
                      'incorrect-answer': result.user_answer_id === option.id && !option.is_correct,
                      'user-answer': result.user_answer_id === option.id,
                    }"
                  >
                    {{ option.text }}
                    <span v-if="option.is_correct" class="badge bg-success ms-2">Correct</span>
                    <span
                      v-if="result.user_answer_id === option.id && !option.is_correct"
                      class="badge bg-danger ms-2"
                    >
                      Your Answer
                    </span>
                  </div>
                </div>
                <div class="mt-2">
                  <small class="text-muted">
                    Points: {{ result.points_earned || 0 }} / {{ result.points || 0 }}
                  </small>
                </div>
              </div>
            </div>

            <div class="text-center mt-4 no-print">
              <router-link to="/quizzes" class="btn btn-primary me-2">
                Back to Quizzes
              </router-link>
              <button class="btn btn-outline-primary me-2" @click="loadDetailedResults">
                {{ detailedResults ? 'Hide' : 'Show' }} Detailed Results
              </button>
              <button class="btn btn-success" @click="printResults">
                🖨️ Print Results
              </button>
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
const detailedResults = ref(null)
const timer = ref(0)
const timerInterval = ref(null)
const isSubmitted = ref(false)
const answerFeedback = ref(null)

const currentQuestion = computed(() => {
  return questions.value[currentQuestionIndex.value] || null
})

const progressPercentage = computed(() => {
  return questions.value.length > 0
    ? ((currentQuestionIndex.value + 1) / questions.value.length) * 100
    : 0
})

const fetchQuiz = async () => {
  try {
    // Provjeri da li je external quiz
    const isExternal = route.query.external === 'true' || route.params.id.startsWith('trivia_')
    
    if (isExternal) {
      // Dohvati external quiz
      const response = await api.get(`/external/premade-quizzes/${route.params.id}`)
      if (response.data.success && response.data.quiz) {
        quiz.value = response.data.quiz
        questions.value = response.data.quiz.questions || []
        
        // Za external quiz, ne kreiraj attempt u bazi, koristi in-memory
        attempt.value = {
          id: 'external_' + Date.now(),
          is_external: true,
        }
        
        // Postavi timer ako postoji duration
        if (quiz.value.duration) {
          timer.value = quiz.value.duration * 60
          startTimer()
        }
      } else {
        throw new Error('Failed to load external quiz')
      }
    } else {
      // Lokalni quiz
      const response = await api.get(`/quizzes/${route.params.id}`)
      quiz.value = response.data
      questions.value = response.data.questions || []
    }
  } catch (error) {
    console.error('Error fetching quiz:', error)
    router.push({ name: 'QuizList' })
  } finally {
    loading.value = false
  }
}

const startQuiz = async () => {
  // Ako je external quiz, attempt je već kreiran u fetchQuiz
  if (attempt.value?.is_external) {
    return
  }

  try {
    const response = await api.post(`/quizzes/${route.params.id}/start`, {
      quiz_id: route.params.id,
    })
    attempt.value = response.data.attempt

    // Postavi timer ako postoji duration
    if (quiz.value.duration) {
      timer.value = quiz.value.duration * 60 // pretvori u sekunde
      startTimer()
    }
  } catch (error) {
    console.error('Error starting quiz:', error)
    if (error.response?.data?.attempt) {
      // Korisnik već ima aktivan pokušaj
      attempt.value = error.response.data.attempt
      loadExistingAnswers()
    }
  }
}

const startTimer = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
  
  timerInterval.value = setInterval(() => {
    if (timer.value > 0) {
      timer.value--
    } else {
      // Vrijeme je isteklo, automatski submit
      clearInterval(timerInterval.value)
      submitQuiz()
    }
  }, 1000)
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const stripHtml = (html) => {
  if (!html) return ''
  const tempDiv = document.createElement('div')
  tempDiv.innerHTML = html
  return tempDiv.textContent || tempDiv.innerText || ''
}

const handleAnswerSelected = async (answer) => {
  answers.value[answer.question_id] = answer
  // Automatski submit odgovora (AJAX)
  await submitAnswer(answer)
  
  // Provjeri da li su sva pitanja odgovorena
  if (allQuestionsAnswered.value && !isSubmitted.value) {
    // Automatski submit kviza kada su sva pitanja odgovorena
    setTimeout(() => {
      submitQuiz()
    }, 1000) // Kratka pauza da korisnik vidi feedback
  }
}

const submitAnswer = async (answer) => {
  if (!attempt.value) return

  // Za external quiz, samo spremi lokalno
  if (attempt.value.is_external) {
    // Pronađi točan odgovor
    const question = questions.value.find(q => q.id === answer.question_id)
    if (question) {
      const correctOption = question.options.find(opt => opt.is_correct)
      const isCorrect = correctOption && answer.option_id === correctOption.id
      
      answerFeedback.value = {
        is_correct: isCorrect,
        points_earned: isCorrect ? question.points : 0,
      }
    }
    return
  }

  try {
    const response = await api.post('/attempts/answer', {
      attempt_id: attempt.value.id,
      question_id: answer.question_id,
      option_id: answer.option_id,
      answer_text: answer.answer_text,
    })

    // Ažuriraj feedback
    if (currentQuestion.value) {
      answerFeedback.value = {
        is_correct: response.data.is_correct,
        points_earned: response.data.points_earned,
      }
    }
  } catch (error) {
    console.error('Error submitting answer:', error)
  }
}

const submitQuiz = async () => {
  if (!attempt.value) return

  submitting.value = true

  try {
    // Za external quiz, izračunaj rezultate lokalno
    if (attempt.value.is_external) {
      let totalPoints = 0
      let earnedPoints = 0

      questions.value.forEach((question) => {
        totalPoints += question.points
        const userAnswer = answers.value[question.id]
        if (userAnswer) {
          const selectedOption = question.options.find(opt => opt.id === userAnswer.option_id)
          if (selectedOption && selectedOption.is_correct) {
            earnedPoints += question.points
          }
        }
      })

      results.value = {
        score: earnedPoints,
        total_points: totalPoints,
        percentage: totalPoints > 0 ? ((earnedPoints / totalPoints) * 100).toFixed(2) : 0,
        is_external: true,
      }
      showResults.value = true
      isSubmitted.value = true
    } else {
      // Za lokalne kvizove, koristi backend
      const response = await api.post(`/attempts/${attempt.value.id}/submit`)
      results.value = response.data.attempt
      showResults.value = true
      isSubmitted.value = true
    }

    // Zaustavi timer
    if (timerInterval.value) {
      clearInterval(timerInterval.value)
    }
  } catch (error) {
    console.error('Error submitting quiz:', error)
    alert('Error submitting quiz. Please try again.')
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

const loadExistingAnswers = () => {
  if (attempt.value?.userAnswers) {
    attempt.value.userAnswers.forEach((answer) => {
      answers.value[answer.question_id] = {
        question_id: answer.question_id,
        option_id: answer.option_id,
        answer_text: answer.answer_text,
      }
    })
  }
}

const loadDetailedResults = async () => {
  if (detailedResults.value) {
    detailedResults.value = null
    return
  }

  try {
    const response = await api.get(`/attempts/${attempt.value.id}/results`)
    detailedResults.value = response.data.questions || response.data.attempt?.quiz?.questions || []
  } catch (error) {
    console.error('Error loading detailed results:', error)
    alert('Failed to load detailed results.')
  }
}

const printResults = () => {
  window.print()
}

const viewResults = () => {
  router.push({
    name: 'QuizResults',
    params: { attemptId: attempt.value.id },
  })
}

onMounted(async () => {
  await fetchQuiz()
  await startQuiz()
})

onUnmounted(() => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
  }
})
</script>

<style scoped>
.quiz-taking {
  min-height: 70vh;
}

.timer-badge {
  font-size: 1.2rem;
}

.progress-bar {
  transition: width 0.3s ease;
}
</style>
