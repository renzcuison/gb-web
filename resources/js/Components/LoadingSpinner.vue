<script setup>
import { computed } from 'vue';

const props = defineProps({
    size: {
        type: String,
        default: 'sm', 
    },
    label: {
        type: String,
        default: 'Loading',
    },
});

const sizeClasses = computed(() => ({
    xs: 'w-3.5 h-3.5',
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
}[props.size] || 'w-4 h-4'));
</script>

<template>
    <span
        role="status"
        :aria-label="label"
        class="inline-flex items-center justify-center shrink-0">
        <svg
            :class="['animate-spin', sizeClasses]"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <circle
                cx="12"
                cy="12"
                r="9.5"
                stroke="currentColor"
                stroke-width="1.25"
                class="opacity-20"
            />
            <path
                d="M12 2.5A9.5 9.5 0 0 1 21.5 12"
                stroke="currentColor"
                stroke-width="1.25"
                stroke-linecap="butt"
            />
        </svg>
        <span class="sr-only">{{ label }}</span>
    </span>
</template>