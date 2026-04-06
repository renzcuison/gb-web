<script setup>
defineProps({
    direction: {
        type: String,
        required: true,
        validator: (value) => ['left', 'right'].includes(value)
    }
});
defineEmits(['click']);
</script>

<template>
    <button
        @click="$emit('click')"
        type="button"
        class="p-2 border border-zinc-200 hover:bg-zinc-50 transition-colors rounded-full group outline-none"
        :aria-label="`Scroll ${direction}`"
    >
        <svg v-if="direction === 'left'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-600 group-hover:text-black">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-600 group-hover:text-black">
            <path d="m9 18 6-6-6-6"/>
        </svg>
    </button>
</template>