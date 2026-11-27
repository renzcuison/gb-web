<script setup>
import { ImsChevronDownIcon } from '@/Components/Icons/ImsIcons.vue';

defineProps({
    title: { type: String, required: true },
    open: { type: Boolean, default: false },
});

defineEmits(['toggle']);
</script>

<template>
    <div class="space-y-3">
        <button
            type="button"
            @click="$emit('toggle')"
            class="w-full flex items-center justify-between text-left cursor-pointer"
        >
            <span class="text-sm font-medium text-zinc-900">
                {{ title }}
            </span>
            <ImsChevronDownIcon
                :class="[
                    'w-4 h-4 text-zinc-400 transition-transform duration-200',
                    open ? 'rotate-180' : '',
                ]"
            />
        </button>

        <div v-show="open" class="pt-0.5">
            <slot />
        </div>
    </div>
</template>