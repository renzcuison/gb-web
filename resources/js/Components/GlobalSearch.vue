<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import {
  ImsSearchIcon,
  ImsInventoryIcon,
  ImsTagIcon,
  ImsBrandIcon,
  ImsChevronDownIcon,
  ImsCloseIcon,
  ImsChevronRightIcon,
} from '@/Components/Icons/ImsIcons.vue'

const searchQuery = ref('')
const selectedCategory = ref('All')
const isSearchFocused = ref(false)
const isCategoryDropdownOpen = ref(false)
const isLoading = ref(false)
const searchResults = ref([])
const activeIndex = ref(-1)

const containerRef = ref(null)
const inputRef = ref(null)
let debounceTimer = null

const searchCategories = [
  { name: 'All', icon: ImsSearchIcon },
  { name: 'Products', icon: ImsInventoryIcon },
  { name: 'Categories', icon: ImsTagIcon },
  { name: 'Brands', icon: ImsBrandIcon },
]

const activeCategoryIcon = computed(() => {
  const cat = searchCategories.find((c) => c.name === selectedCategory.value)
  return cat ? cat.icon : ImsSearchIcon
})

const selectCategory = (categoryName) => {
  selectedCategory.value = categoryName
  isCategoryDropdownOpen.value = false
  fetchSearchResults()
}

const fetchSearchResults = () => {
  const q = searchQuery.value.trim()
  if (q.length < 2) {
    searchResults.value = []
    activeIndex.value = -1
    return
  }

  isLoading.value = true

  axios
    .get('/api/search', {
      params: {
        q,
        category: selectedCategory.value,
      },
    })
    .then((response) => {
      searchResults.value = response.data
      activeIndex.value = response.data.length > 0 ? 0 : -1
    })
    .catch(() => {
      searchResults.value = []
      activeIndex.value = -1
    })
    .finally(() => {
      isLoading.value = false
    })
}

watch(searchQuery, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchSearchResults()
  }, 220)
})

const handleKeydown = (event) => {
  if (!isSearchFocused.value || searchResults.value.length === 0) {
    if (event.key === 'Escape') {
      isSearchFocused.value = false
      inputRef.value?.blur()
    }
    return
  }

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    activeIndex.value = (activeIndex.value + 1) % searchResults.value.length
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    activeIndex.value = (activeIndex.value - 1 + searchResults.value.length) % searchResults.value.length
  } else if (event.key === 'Enter' && activeIndex.value >= 0) {
    event.preventDefault()
    const selected = searchResults.value[activeIndex.value]
    if (selected?.href) {
      isSearchFocused.value = false
      router.visit(selected.href)
    }
  } else if (event.key === 'Escape') {
    isSearchFocused.value = false
    inputRef.value?.blur()
  }
}

const handleGlobalShortcut = (event) => {
  const tag = document.activeElement?.tagName
  const isTyping = tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement?.isContentEditable

  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault()
    inputRef.value?.focus()
    isSearchFocused.value = true
  } else if (event.key === '/' && !isTyping) {
    event.preventDefault()
    inputRef.value?.focus()
    isSearchFocused.value = true
  }
}

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    isCategoryDropdownOpen.value = false
    isSearchFocused.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleGlobalShortcut)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleGlobalShortcut)
})

const getCategoryIcon = (type) => {
  switch (type) {
    case 'Products':
      return ImsInventoryIcon
    case 'Categories':
      return ImsTagIcon
    case 'Brands':
      return ImsBrandIcon
    default:
      return ImsSearchIcon
  }
}
</script>

<template>
  <div ref="containerRef" class="relative flex items-center [&_*]:!transition-none [&_*]:!animate-none">
    <div
      class="flex items-center h-9 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700/80 rounded-md text-[13px] focus-within:border-slate-400 dark:focus-within:border-zinc-500"
    >
      <div class="relative border-r border-slate-200 dark:border-zinc-700">
        <button
          type="button"
          @click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
          class="flex items-center gap-1.5 px-2.5 py-1.5 font-medium text-slate-700 dark:text-zinc-200 hover:text-slate-900 dark:hover:text-white"
        >
          <component :is="activeCategoryIcon" class="h-3.5 w-3.5 text-slate-900 dark:text-zinc-100" />
          <span>{{ selectedCategory }}</span>
          <ImsChevronDownIcon class="h-3 w-3 text-slate-900 dark:text-zinc-100 ml-0.5" />
        </button>

        <div
          v-if="isCategoryDropdownOpen"
          class="absolute top-full left-0 mt-1.5 w-40 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-md shadow-lg p-1.5 space-y-0.5 z-50 text-[13px]"
        >
          <button
            v-for="cat in searchCategories"
            :key="cat.name"
            @click="selectCategory(cat.name)"
            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-left hover:bg-slate-100 dark:hover:bg-zinc-800/80 text-slate-700 dark:text-zinc-300"
            :class="{ 'font-semibold bg-slate-200 text-slate-900 dark:bg-zinc-800 dark:text-zinc-100': selectedCategory === cat.name }"
          >
            <component :is="cat.icon" class="h-3.5 w-3.5 text-slate-900 dark:text-zinc-100" />
            <span>{{ cat.name }}</span>
          </button>
        </div>
      </div>

      <div class="relative flex items-center pl-2.5 pr-2 w-48 sm:w-64 md:w-80">
        <ImsSearchIcon class="h-3.5 w-3.5 shrink-0 mr-2 text-slate-900 dark:text-zinc-100" />

        <input
          ref="inputRef"
          v-model="searchQuery"
          type="text"
          @focus="isSearchFocused = true"
          @keydown="handleKeydown"
          :placeholder="`Search ${selectedCategory === 'All' ? 'products, SKU, brands...' : selectedCategory.toLowerCase() + '...'}`"
          class="w-full bg-transparent border-none p-0 text-[13px] text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-0"
        />

        <button
          v-if="searchQuery"
          @click="searchQuery = ''; searchResults = []"
          aria-label="Clear search"
          class="p-0.5 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200"
        >
          <ImsCloseIcon class="h-3 w-3" />
        </button>
        <kbd
          v-else-if="!isSearchFocused"
          class="hidden sm:inline-block font-mono text-xs text-slate-400 dark:text-zinc-500 px-1.5 py-0.5 rounded border border-slate-200 dark:border-zinc-700"
        >
          /
        </kbd>
      </div>
    </div>

    <div
      v-if="isSearchFocused && searchQuery.trim().length >= 2"
      class="absolute left-0 top-full mt-2 w-full min-w-[320px] sm:min-w-[420px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-md shadow-lg z-50 overflow-hidden"
    >
      <div v-if="searchResults.length > 0" class="max-h-80 overflow-y-auto p-1.5 space-y-0.5">
        <Link
          v-for="(result, idx) in searchResults"
          :key="result.id"
          :href="result.href"
          @click="isSearchFocused = false"
          @mouseenter="activeIndex = idx"
          :class="[
            'flex items-center justify-between px-3 py-2 rounded-md group',
            activeIndex === idx ? 'bg-slate-100 dark:bg-zinc-800/80' : 'hover:bg-slate-50 dark:hover:bg-zinc-800/50',
          ]"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="p-1.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 shrink-0">
              <component :is="getCategoryIcon(result.type)" class="h-4 w-4" />
            </div>
            <div class="min-w-0">
              <p class="text-[13px] font-medium text-slate-900 dark:text-zinc-100 truncate">
                {{ result.title }}
              </p>
              <p class="text-xs text-slate-500 dark:text-zinc-400 truncate">
                {{ result.subtitle }}
              </p>
            </div>
          </div>
          <ImsChevronRightIcon
            :class="[
              'h-3.5 w-3.5 text-slate-900 dark:text-zinc-100 shrink-0',
              activeIndex === idx ? 'opacity-100' : 'opacity-0 group-hover:opacity-100',
            ]"
          />
        </Link>
      </div>

      <div v-else-if="!isLoading" class="px-4 py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
        No matching results found for "{{ searchQuery }}".
      </div>
    </div>
  </div>
</template>