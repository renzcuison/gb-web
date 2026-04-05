<script setup>
defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
});

defineEmits(['toggle']);
</script>

<template>
    <button @click="$emit('toggle')" :class="[
        'p-2 -ml-2 text-black focus:outline-none flex items-center justify-center transition-all duration-300 z-50',
        isOpen ? 'bg-white' : 'bg-transparent'
    ]" aria-label="Toggle Menu">
        <svg v-if="!isOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 12H21" stroke="currentColor" stroke-width="1" stroke-linecap="square" />
            <path d="M3 6H21" stroke="currentColor" stroke-width="1" stroke-linecap="square" />
            <path d="M3 18H21" stroke="currentColor" stroke-width="1" stroke-linecap="square" />
        </svg>

        <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="1" stroke-linecap="square" />
        </svg>
    </button>
</template>