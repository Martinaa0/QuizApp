<template>
  <div class="home-page">
    <div class="container mt-5">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 mb-4">Welcome to Quiz App!</h1>
          <p class="lead mb-4">
            Test your knowledge with our interactive quiz platform. 
            Create quizzes, take challenges, and track your progress.
          </p>
          <div class="d-flex justify-content-center gap-3 flex-wrap">
            <router-link to="/quizzes" class="btn btn-primary btn-lg">
              Browse Quizzes
            </router-link>
            <router-link
              v-if="!isAuthenticated"
              to="/register"
              class="btn btn-outline-primary btn-lg"
            >
              Get Started
            </router-link>
            <router-link
              v-if="isAuthenticated"
              to="/quizzes/create"
              class="btn btn-success btn-lg"
            >
              Create Quiz
            </router-link>
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <div class="col-md-4 mb-4">
          <div class="card text-center h-100">
            <div class="card-body">
              <div class="mb-3" style="font-size: 3rem;">📚</div>
              <h5 class="card-title">Learn & Test</h5>
              <p class="card-text">
                Take quizzes on various topics and test your knowledge.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card text-center h-100">
            <div class="card-body">
              <div class="mb-3" style="font-size: 3rem;">✏️</div>
              <h5 class="card-title">Create Quizzes</h5>
              <p class="card-text">
                Create your own quizzes with multiple question types.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card text-center h-100">
            <div class="card-body">
              <div class="mb-3" style="font-size: 3rem;">📊</div>
              <h5 class="card-title">Track Progress</h5>
              <p class="card-text">
                Monitor your performance and see your improvement over time.
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('auth_token')
})
</script>

<style scoped>
.home-page {
  min-height: 70vh;
  padding: 2rem 0;
}

.display-4 {
  color: var(--primary-color, #78c2ad);
  font-weight: 700;
}

.lead {
  font-size: 1.25rem;
  color: #6c757d;
}
</style>
