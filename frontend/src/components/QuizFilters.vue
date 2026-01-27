<template>
  <div class="quiz-filters card mb-4">
    <div class="card-body">
      <!-- Search Bar -->
      <div class="mb-3">
        <label for="search" class="form-label">Search Quizzes</label>
          <div class="input-group">
          <span class="input-group-text">🔍</span>
          <input
            type="text"
            class="form-control"
            id="search"
            v-model="localSearch"
            @input="onSearchChange"
            placeholder="Search by title..."
          />
          <button
            v-if="localSearch"
            class="btn btn-outline-secondary"
            type="button"
            @click="clearSearch"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="row g-3">
        <div class="col-md-4">
          <label for="category" class="form-label">Category</label>
          <select
            class="form-select"
            id="category"
            v-model="localFilters.category"
            @change="onFilterChange"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="difficulty" class="form-label">Difficulty</label>
          <select
            class="form-select"
            id="difficulty"
            v-model="localFilters.difficulty"
            @change="onFilterChange"
          >
            <option value="">All Difficulties</option>
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="status" class="form-label">Status</label>
          <select
            class="form-select"
            id="status"
            v-model="localFilters.is_active"
            @change="onFilterChange"
          >
            <option value="">All Status</option>
            <option value="true">Active</option>
            <option value="false">Inactive</option>
          </select>
        </div>
      </div>

      <!-- Clear All Filters -->
      <div class="mt-3">
        <button
          class="btn btn-outline-secondary btn-sm"
          @click="clearAllFilters"
          :disabled="!hasActiveFilters"
        >
          Clear All Filters
        </button>
        <span v-if="hasActiveFilters" class="ms-2 text-muted small">
          {{ filteredCount }} quiz(es) found
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  search: {
    type: String,
    default: '',
  },
  filters: {
    type: Object,
    default: () => ({
      category: '',
      difficulty: '',
      is_active: '',
    }),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  filteredCount: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['update:search', 'update:filters'])

const localSearch = ref(props.search)
const localFilters = ref({ ...props.filters })

let searchTimeout = null

const onSearchChange = () => {
  // Debounce search input
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    emit('update:search', localSearch.value)
  }, 500) // Wait 500ms after user stops typing
}

const clearSearch = () => {
  localSearch.value = ''
  emit('update:search', '')
}

const onFilterChange = () => {
  emit('update:filters', { ...localFilters.value })
}

const clearAllFilters = () => {
  localSearch.value = ''
  localFilters.value = {
    category: '',
    difficulty: '',
    is_active: '',
  }
  emit('update:search', '')
  emit('update:filters', { ...localFilters.value })
}

const hasActiveFilters = computed(() => {
  return (
    localSearch.value ||
    localFilters.value.category ||
    localFilters.value.difficulty ||
    localFilters.value.is_active
  )
})

// Sync props with local values
watch(
  () => props.search,
  (newVal) => {
    localSearch.value = newVal
  }
)

watch(
  () => props.filters,
  (newVal) => {
    localFilters.value = { ...newVal }
  },
  { deep: true }
)
</script>

<style scoped>
.quiz-filters {
  background-color: #f8f9fa;
}

.input-group-text {
  background-color: white;
}
</style>
