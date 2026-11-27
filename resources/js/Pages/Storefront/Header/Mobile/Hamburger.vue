<script setup>
import { ImsMenuIcon, ImsCloseIcon } from '@/Components/Icons/ImsIcons.vue';

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
        <ImsMenuIcon v-if="!isOpen" class="w-6 h-6" />
        <ImsCloseIcon v-else class="w-6 h-6" />
    </button>
</template>