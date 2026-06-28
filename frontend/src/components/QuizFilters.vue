<template>
  <div class="filter-bar qa-card" style="padding:14px; margin-bottom:24px;">
    <div class="filter-row">
      <!-- Search -->
      <div class="filter-search">
        <span class="search-icon">⌕</span>
        <input
          type="text"
          class="search-input"
          v-model="localSearch"
          @input="onSearchChange"
          placeholder="Pretraži po naslovu ili temi…"
        />
      </div>

      <!-- Category dropdown -->
      <div class="filter-dropdown" style="position:relative;" @click.stop>
        <button class="dropdown-trigger" @click="toggleMenu('category')">
          {{ localFilters.category || 'Kategorija' }}
          <span class="dropdown-arrow">▾</span>
        </button>
        <div v-if="openMenu === 'category'" class="dropdown-menu-custom">
          <div class="dropdown-item" :class="{ active: !localFilters.category }" @click="selectCategory('')">Sve</div>
          <div
            v-for="cat in allCategories"
            :key="cat"
            class="dropdown-item"
            :class="{ active: localFilters.category === cat }"
            @click="selectCategory(cat)"
          >{{ cat }}</div>
        </div>
      </div>

      <!-- Difficulty chips -->
      <div class="diff-chips">
        <button
          v-for="d in ['easy', 'medium', 'hard']"
          :key="d"
          class="diff-chip pill pill-sm"
          :class="[diffPillClass(d), { active: localFilters.difficulty === d }]"
          @click="toggleDiff(d)"
        >{{ d }}</button>
      </div>

      <!-- Clear -->
      <button
        v-if="hasActiveFilters"
        class="btn-danger-text"
        @click="clearAllFilters"
      >✕ Očisti</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  search: { type: String, default: '' },
  filters: { type: Object, default: () => ({ category: '', difficulty: '', is_active: '' }) },
  categories: { type: Array, default: () => [] },
  filteredCount: { type: Number, default: 0 },
})

const emit = defineEmits(['update:search', 'update:filters'])

const localSearch = ref(props.search)
const localFilters = ref({ ...props.filters })
const openMenu = ref(null)
let searchTimeout = null

const allCategories = computed(() => {
  const defaults = ['Geography', 'Computer Science', 'Science', 'History', 'Sports', 'General Knowledge']
  const merged = new Set([...defaults, ...props.categories])
  return [...merged].sort()
})

const hasActiveFilters = computed(() => localSearch.value || localFilters.value.category || localFilters.value.difficulty || localFilters.value.is_active)

const onSearchChange = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => emit('update:search', localSearch.value), 500)
}

const toggleMenu = (menu) => {
  openMenu.value = openMenu.value === menu ? null : menu
}

const selectCategory = (cat) => {
  localFilters.value.category = cat
  openMenu.value = null
  emit('update:filters', { ...localFilters.value })
}

const toggleDiff = (d) => {
  localFilters.value.difficulty = localFilters.value.difficulty === d ? '' : d
  emit('update:filters', { ...localFilters.value })
}

const diffPillClass = (d) => {
  if (d === 'easy') return 'pill-easy'
  if (d === 'hard') return 'pill-hard'
  return 'pill-medium'
}

const clearAllFilters = () => {
  localSearch.value = ''
  localFilters.value = { category: '', difficulty: '', is_active: '' }
  emit('update:search', '')
  emit('update:filters', { ...localFilters.value })
}

watch(() => props.search, (v) => { localSearch.value = v })
watch(() => props.filters, (v) => { localFilters.value = { ...v } }, { deep: true })

// Close dropdown on outside click
const onOutsideClick = (e) => {
  if (!e.target.closest('.filter-dropdown')) openMenu.value = null
}
onMounted(() => document.addEventListener('click', onOutsideClick))
onUnmounted(() => document.removeEventListener('click', onOutsideClick))
</script>

<style scoped>
.filter-bar { border-radius: 14px; }
.filter-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.filter-search {
  flex: 1;
  min-width: 200px;
  display: flex;
  align-items: center;
  gap: 9px;
  background: var(--page-bg);
  border-radius: 10px;
  padding: 11px 13px;
}
.search-icon { color: var(--faint2); font-size: 16px; }
.search-input {
  border: none;
  outline: none;
  background: transparent;
  flex: 1;
  min-width: 0;
  font-family: 'Lato', sans-serif;
  font-size: 14px;
  color: var(--body-text);
}
.search-input::placeholder { color: var(--faint2); }

.dropdown-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--line);
  background: var(--surface);
  border-radius: 10px;
  padding: 10px 14px;
  font-family: 'Lato', sans-serif;
  font-size: 13.5px;
  font-weight: 700;
  color: var(--body-text);
  cursor: pointer;
  white-space: nowrap;
}
.dropdown-arrow { color: var(--faint); font-size: 12px; }

.dropdown-menu-custom {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  min-width: 200px;
  background: var(--surface);
  border: 1px solid var(--line);
  border-radius: 12px;
  box-shadow: var(--shadow-dropdown);
  padding: 6px;
  z-index: 50;
}
.dropdown-item {
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  color: var(--body-text);
  cursor: pointer;
  transition: background .1s;
}
.dropdown-item:hover { background: var(--page-bg); }
.dropdown-item.active { background: var(--mint-soft); color: #2f6b5c; }

.diff-chips { display: flex; gap: 6px; }
.diff-chip {
  cursor: pointer;
  border: none;
  font-family: 'Lato', sans-serif;
  transition: all .15s;
  text-transform: capitalize;
}
.diff-chip.active.pill-easy { background: var(--success-deep); color: #fff; border-color: var(--success-deep); }
.diff-chip.active.pill-medium { background: var(--warning-deep); color: #fff; border-color: var(--warning-deep); }
.diff-chip.active.pill-hard { background: var(--danger-text); color: #fff; border-color: var(--danger-text); }
</style>
