<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { resolveProductImages } from '@/Composables/useProductImage'
import LoadingSpinner from '@/Components/LoadingSpinner.vue'
import { ImsSearchIcon, ImsCloseIcon } from '@/Components/Icons/ImsIcons.vue'

const getSearchThumb = (product) => resolveProductImages(product).primaryImg

const props = defineProps({
  dark: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: 'What are you looking for?',
  },
  modelValue: {
    type: String,
    default: undefined,
  },
  showRecommendations: {
    type: Boolean,
    default: undefined,
  },
})

const emit = defineEmits(['update:modelValue', 'submit'])

const containerRef = ref(null)
const inputRef = ref(null)
const internalQuery = ref(props.modelValue ?? '')
const isOpen = ref(false)
const isLoading = ref(false)
const activeIndex = ref(-1)

const suggestions = ref({
  mode: 'recommendations',
  popular_searches: [],
  trending_products: [],
  taxonomy: [],
  products: [],
})

const recommendationsEnabled = computed(() =>
  props.showRecommendations !== undefined
    ? props.showRecommendations
    : props.modelValue === undefined
)

watch(
  () => props.modelValue,
  (val) => {
    if (val !== undefined) {
      internalQuery.value = val
    }
  }
)

let debounceTimer = null
let abortController = null

const fetchSuggestions = async (q = '') => {
  if (!recommendationsEnabled.value) return

  if (abortController) {
    abortController.abort()
  }
  abortController = new AbortController()
  isLoading.value = true

  try {
    const url = new URL(route('storefront.search.suggestions'), window.location.origin)
    if (q.trim().length >= 2) {
      url.searchParams.set('q', q.trim())
    }

    const response = await fetch(url.toString(), {
      headers: { Accept: 'application/json' },
      signal: abortController.signal,
    })

    if (response.ok) {
      suggestions.value = await response.json()
      activeIndex.value = -1
    }
  } catch (err) {
    if (err.name !== 'AbortError') {
    }
  } finally {
    isLoading.value = false
  }
}

const recordSearchTelemetry = (term) => {
  const clean = term.trim()
  if (clean.length < 3) return

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

  fetch(route('storefront.search.log'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
    body: JSON.stringify({ query: clean }),
    keepalive: true,
  }).catch(() => {})
}

const onFocus = () => {
  if (!recommendationsEnabled.value) return
  isOpen.value = true
  fetchSuggestions(internalQuery.value)
}

const onInput = (event) => {
  internalQuery.value = event.target.value
  emit('update:modelValue', internalQuery.value)

  if (!recommendationsEnabled.value) return

  isOpen.value = true
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchSuggestions(internalQuery.value)
  }, 220)
}

const clearInput = () => {
  internalQuery.value = ''
  emit('update:modelValue', '')
  activeIndex.value = -1
  inputRef.value?.focus()
  if (recommendationsEnabled.value) {
    fetchSuggestions('')
  }
}

const closeDropdown = () => {
  isOpen.value = false
  activeIndex.value = -1
}

const executeSearch = (term) => {
  const query = (term ?? internalQuery.value).trim()
  internalQuery.value = query
  closeDropdown()
  emit('submit', query)

  if (query.length >= 3) {
    recordSearchTelemetry(query)
  }

  if (props.modelValue === undefined) {
    router.get(
      route('storefront.index'),
      { search: query || undefined },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' })
        },
      }
    )
  }
}

const navigateToProduct = (product) => {
  if (internalQuery.value.trim().length >= 3) {
    recordSearchTelemetry(internalQuery.value.trim())
  }
  closeDropdown()
  emit('submit', product.name)
  router.visit(route('products.show', product.slug))
}

const navigateToTaxonomy = (item) => {
  closeDropdown()
  emit('submit', item.label)
  router.get(route('storefront.index'), item.params, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' })
    },
  })
}

const selectableItems = computed(() => {
  if (suggestions.value.mode === 'autocomplete') {
    return [
      ...(suggestions.value.taxonomy || []).map((t) => ({ kind: 'taxonomy', data: t })),
      ...(suggestions.value.products || []).map((p) => ({ kind: 'product', data: p })),
    ]
  }

  return [
    ...(suggestions.value.popular_searches || []).map((s) => ({ kind: 'popular', data: s })),
    ...(suggestions.value.trending_products || []).map((p) => ({ kind: 'product', data: p })),
  ]
})

const onKeydown = (e) => {
  if (!isOpen.value || selectableItems.value.length === 0) {
    if (e.key === 'Escape') closeDropdown()
    return
  }

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    activeIndex.value = (activeIndex.value + 1) % selectableItems.value.length
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    activeIndex.value =
      (activeIndex.value - 1 + selectableItems.value.length) % selectableItems.value.length
  } else if (e.key === 'Enter' && activeIndex.value >= 0) {
    e.preventDefault()
    const chosen = selectableItems.value[activeIndex.value]
    if (chosen.kind === 'popular') executeSearch(chosen.data.query)
    if (chosen.kind === 'taxonomy') navigateToTaxonomy(chosen.data)
    if (chosen.kind === 'product') navigateToProduct(chosen.data)
  } else if (e.key === 'Escape') {
    closeDropdown()
    inputRef.value?.blur()
  }
}

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  clearTimeout(debounceTimer)
  if (abortController) abortController.abort()
})

const formatPrice = (val) => Number(val || 0).toLocaleString('en-PH')
</script>

<template>
  <div ref="containerRef" class="relative w-full md:w-auto">
    <form @submit.prevent="executeSearch()" class="relative group w-full md:w-auto">
      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <ImsSearchIcon
          class="h-4 w-4 transition-colors duration-300"
          :class="[
            dark
              ? 'text-white group-focus-within:text-white'
              : 'text-zinc-400 group-focus-within:text-zinc-900',
          ]"
        />
      </div>

      <input
        ref="inputRef"
        type="text"
        :value="internalQuery"
        @focus="onFocus"
        @input="onInput"
        @keydown="onKeydown"
        :placeholder="placeholder"
        autocomplete="off"
        class="border border-solid rounded-none outline-none focus:outline-none focus:ring-0 transition-all duration-500
               w-full py-4 pl-11 pr-9 text-sm
               md:w-80 md:py-3 md:pl-9 md:pr-8 md:text-xs md:focus:w-[380px]"
        :class="[
          dark
            ? 'bg-transparent !border-white/70 !text-white placeholder:!text-white/60 focus:!border-white'
            : 'bg-white !border-zinc-300 !text-zinc-900 placeholder:!text-zinc-400 focus:!border-zinc-900',
        ]"
      />

      <button
        v-if="internalQuery"
        type="button"
        @click="clearInput"
        aria-label="Clear search"
        class="absolute inset-y-0 right-0 pr-3 flex items-center transition-colors"
        :class="dark ? 'text-white/70 hover:text-white' : 'text-zinc-400 hover:text-zinc-900'">
        <ImsCloseIcon class="h-3.5 w-3.5" />
      </button>
    </form>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1">
      <div
        v-if="recommendationsEnabled && isOpen"
        class="absolute left-0 md:left-auto md:right-0 top-full mt-2 w-full md:w-[560px] lg:w-[680px] bg-white text-zinc-900 border border-zinc-200 p-5 sm:p-6 z-[70]">
        <div
          v-if="isLoading"
          class="absolute top-5 right-5 sm:top-6 sm:right-6 pointer-events-none">
          <LoadingSpinner size="xs" class="text-zinc-400" />
        </div>

        <div v-if="suggestions.mode === 'recommendations'" class="space-y-6">
          <div v-if="suggestions.popular_searches?.length > 0" class="space-y-3">
            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold pr-6">
              Popular Searches
            </span>

            <div class="flex flex-wrap gap-x-5 gap-y-2">
              <button
                v-for="(item, idx) in suggestions.popular_searches"
                :key="item.query"
                type="button"
                @click="executeSearch(item.query)"
                :class="[
                  'text-xs transition-colors inline-flex items-center gap-1.5 py-0.5',
                  activeIndex === idx
                    ? 'text-zinc-900 underline underline-offset-4'
                    : 'text-zinc-600 hover:text-zinc-900',
                ]">
                <span>{{ item.label }}</span>
                <span
                  v-if="item.badge === 'Trending'"
                  class="text-[9px] uppercase tracking-widest text-zinc-400">
                  Hot
                </span>
              </button>
            </div>
          </div>

          <div v-if="suggestions.trending_products?.length > 0" class="space-y-3">
            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold">
              Trending Pieces
            </span>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-1 gap-y-4">
              <button
                v-for="(product, pIdx) in suggestions.trending_products"
                :key="product.id"
                type="button"
                @click="navigateToProduct(product)"
                class="flex flex-col text-left cursor-pointer">
                <div class="aspect-[3/4] w-full bg-zinc-100 overflow-hidden flex items-center justify-center">
                  <img
                    v-if="getSearchThumb(product)"
                    :src="getSearchThumb(product)"
                    :alt="product.name"
                    class="w-full h-full object-cover"
                  />
                  <span
                    v-else
                    class="text-[9px] uppercase tracking-widest text-zinc-400 select-none">
                    No Image
                  </span>
                </div>
                <div class="pt-2.5 pr-1.5 space-y-0.5 w-full">
                  <div class="text-[10px] uppercase tracking-wider text-zinc-400 truncate">
                    {{ product.subtitle }}
                  </div>
                  <div
                    :class="[
                      'text-xs font-normal text-zinc-800 truncate',
                      activeIndex === (suggestions.popular_searches?.length || 0) + pIdx
                        ? 'text-black underline underline-offset-4'
                        : '',
                    ]">
                    {{ product.name }}
                  </div>
                  <div class="text-xs font-bold text-zinc-900 pt-0.5 price-tag">
                    ₱{{ formatPrice(product.price) }}
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div
            v-if="!isLoading && !suggestions.popular_searches?.length && !suggestions.trending_products?.length"
            class="py-4 text-center text-xs text-zinc-400">
            Type to search products, brands, or SKUs.
          </div>
        </div>

        <div v-else class="space-y-6">
          <div v-if="suggestions.taxonomy?.length > 0" class="space-y-2.5">
            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold pr-6">
              Collections &amp; Categories
            </span>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
              <button
                v-for="(tax, tIdx) in suggestions.taxonomy"
                :key="`${tax.type}-${tax.label}`"
                type="button"
                @click="navigateToTaxonomy(tax)"
                :class="[
                  'text-xs transition-colors inline-flex items-center gap-1.5 py-0.5',
                  activeIndex === tIdx
                    ? 'text-zinc-900 underline underline-offset-4'
                    : 'text-zinc-600 hover:text-zinc-900',
                ]">
                <span class="text-[9px] uppercase tracking-wider text-zinc-400">
                  {{ tax.type }}
                </span>
                <span>{{ tax.label }}</span>
              </button>
            </div>
          </div>

          <div v-if="suggestions.products?.length > 0" class="space-y-3">
            <span class="block text-[10px] uppercase tracking-[0.3em] text-zinc-400 font-semibold pr-6">
              Matching Pieces
            </span>
            <div class="space-y-3">
              <button
                v-for="(product, pIdx) in suggestions.products"
                :key="product.id"
                type="button"
                @click="navigateToProduct(product)"
                class="w-full flex items-center justify-between gap-3 text-left cursor-pointer">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-11 h-14 bg-zinc-100 shrink-0 overflow-hidden flex items-center justify-center">
                    <img
                      v-if="getSearchThumb(product)"
                      :src="getSearchThumb(product)"
                      :alt="product.name"
                      class="w-full h-full object-cover"
                    />
                    <span
                      v-else
                      class="text-[7px] uppercase tracking-wider text-zinc-400 text-center leading-tight px-0.5 select-none">
                      No Image
                    </span>
                  </div>
                  <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-zinc-400 truncate">
                      {{ product.subtitle }}
                    </div>
                    <div
                      :class="[
                        'text-xs font-normal text-zinc-800 truncate',
                        activeIndex === (suggestions.taxonomy?.length || 0) + pIdx
                          ? 'text-black underline underline-offset-4'
                          : '',
                      ]">
                      {{ product.name }}
                    </div>
                    <div class="text-[11px] text-zinc-400">
                      {{ product.in_stock ? 'In Stock' : 'Available for Inquiry' }}
                    </div>
                  </div>
                </div>

                <span class="text-xs font-bold text-zinc-900 shrink-0 price-tag">
                  ₱{{ formatPrice(product.price) }}
                </span>
              </button>
            </div>
          </div>

          <div
            v-if="!isLoading && !suggestions.taxonomy?.length && !suggestions.products?.length"
            class="py-3 text-center space-y-1">
            <p class="text-xs text-zinc-600">
              No pieces matched "<span class="font-medium text-zinc-900">{{ internalQuery }}</span>"
            </p>
            <p class="text-[11px] text-zinc-400">
              Try searching by category, brand, or SKU.
            </p>
          </div>

          <div class="pt-1 flex items-center justify-between">
            <span class="text-[11px] text-zinc-400">
              Press <kbd class="font-mono text-zinc-600">Enter</kbd> to filter catalog
            </span>
            <button
              type="button"
              @click="executeSearch()"
              class="text-xs font-medium text-zinc-900 hover:underline underline-offset-4">
              View all results
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>