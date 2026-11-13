<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import {
    Search,
    Package,
    Tag,
    Building2,
    ChevronDown,
    X,
    ArrowRight,
    Loader2
} from 'lucide-vue-next'

const searchQuery = ref('')
const selectedCategory = ref('All')
const isSearchFocused = ref(false)
const isCategoryDropdownOpen = ref(false)
const isLoading = ref(false)
const searchResults = ref([])

const containerRef = ref(null)
let debounceTimer = null

const searchCategories = [
    { name: 'All', icon: Search },
    { name: 'Products', icon: Package },
    { name: 'Categories', icon: Tag },
    { name: 'Brands', icon: Building2 },
]

const activeCategoryIcon = computed(() => {
    const cat = searchCategories.find(c => c.name === selectedCategory.value)
    return cat ? cat.icon : Search
})

const selectCategory = (categoryName) => {
    selectedCategory.value = categoryName
    isCategoryDropdownOpen.value = false
    fetchSearchResults()
}

const fetchSearchResults = () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = []
        return
    }

    isLoading.value = true

    axios.get('/api/search', {
        params: {
            q: searchQuery.value,
            category: selectedCategory.value
        }
    })
        .then(response => {
            searchResults.value = response.data
        })
        .catch(() => {
            searchResults.value = []
        })
        .finally(() => {
            isLoading.value = false
        })
}

watch(searchQuery, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        fetchSearchResults()
    }, 300)
})

const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isCategoryDropdownOpen.value = false
        isSearchFocused.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

const getCategoryIcon = (type) => {
    switch (type) {
        case 'Products': return Package
        case 'Categories': return Tag
        case 'Brands': return Building2
        default: return Search
    }
}
</script>

<template>
    <div ref="containerRef" class="relative flex items-center">
        <div
            class="flex items-center h-9 bg-slate-100 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700/80 rounded-lg text-xs transition-all focus-within:ring-2 focus-within:ring-slate-400 dark:focus-within:ring-zinc-600">
            <div class="relative border-r border-slate-200 dark:border-zinc-700">
                <button type="button" @click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
                    class="flex items-center gap-1.5 px-2.5 py-1.5 font-medium text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white transition">
                    <component :is="activeCategoryIcon" class="h-3.5 w-3.5 text-slate-500 dark:text-zinc-400" />
                    <span>{{ selectedCategory }}</span>
                    <ChevronDown class="h-3 w-3 opacity-60 ml-0.5" />
                </button>

                <div v-if="isCategoryDropdownOpen"
                    class="absolute top-full left-0 mt-1.5 w-36 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg shadow-lg py-1 z-50 text-xs">
                    <button v-for="cat in searchCategories" :key="cat.name" @click="selectCategory(cat.name)"
                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left hover:bg-slate-100 dark:hover:bg-zinc-800/80 text-slate-700 dark:text-zinc-300 transition"
                        :class="{ 'font-bold bg-slate-50 dark:bg-zinc-800/50': selectedCategory === cat.name }">
                        <component :is="cat.icon" class="h-3.5 w-3.5 text-slate-400" />
                        <span>{{ cat.name }}</span>
                    </button>
                </div>
            </div>

            <div class="relative flex items-center pl-2.5 pr-2 w-48 sm:w-64 md:w-80">
                <Search v-if="!isLoading" class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 shrink-0 mr-2" />
                <Loader2 v-else class="h-3.5 w-3.5 text-slate-400 dark:text-zinc-500 shrink-0 mr-2 animate-spin" />

                <input v-model="searchQuery" type="text" @focus="isSearchFocused = true"
                    :placeholder="`Search ${selectedCategory === 'All' ? 'products, SKU, brands...' : selectedCategory.toLowerCase() + '...'}`"
                    class="w-full bg-transparent border-none p-0 text-xs text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-0" />

                <button v-if="searchQuery" @click="searchQuery = ''; searchResults = []"
                    class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200">
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>

        <div v-if="isSearchFocused && searchQuery.trim()"
            class="absolute left-0 top-full mt-2 w-full min-w-[320px] sm:min-w-[420px] bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 py-2 max-h-80 overflow-y-auto">
            <div
                class="px-3 py-1.5 border-b border-slate-100 dark:border-zinc-800 text-[10px] uppercase font-bold text-slate-400 dark:text-zinc-500 flex justify-between items-center">
                <span>Results for "{{ searchQuery }}"</span>
                <span>Category: {{ selectedCategory }}</span>
            </div>

            <div v-if="searchResults.length > 0" class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                <Link v-for="result in searchResults" :key="result.id" :href="result.href"
                    @click="isSearchFocused = false"
                    class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition group">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-1.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700">
                            <component :is="getCategoryIcon(result.type)" class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-zinc-200">{{ result.title }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-zinc-500">{{ result.subtitle }}</p>
                        </div>
                    </div>
                    <ArrowRight class="h-3.5 w-3.5 text-slate-400 opacity-0 group-hover:opacity-100 transition" />
                </Link>
            </div>

            <div v-else-if="!isLoading" class="px-4 py-8 text-center text-xs text-slate-400 dark:text-zinc-500">
                No matching results found for "{{ searchQuery }}".
            </div>
        </div>
    </div>
</template>