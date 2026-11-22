<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    dark: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'What are you looking for?'
    },
    modelValue: {
        type: String,
        default: undefined
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const internalQuery = ref(props.modelValue ?? '');

watch(
    () => props.modelValue,
    (val) => {
        if (val !== undefined) {
            internalQuery.value = val;
        }
    }
);

const onInput = (event) => {
    internalQuery.value = event.target.value;
    emit('update:modelValue', internalQuery.value);
};

const handleSubmit = () => {
    const query = internalQuery.value.trim();
    emit('submit', query);

    if (props.modelValue === undefined) {
        router.get(
            route('storefront.index'),
            { search: query || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
                },
            }
        );
    }
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="relative group w-full md:w-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-colors duration-300" :class="[
                dark
                    ? 'text-white group-focus-within:text-white'
                    : 'text-zinc-400 group-focus-within:text-zinc-900'
            ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
        </div>

        <input
            type="text"
            :value="internalQuery"
            @input="onInput"
            :placeholder="placeholder"
            class="border border-solid rounded-none outline-none focus:outline-none focus:ring-0 transition-all duration-500
                   w-full py-4 pl-11 pr-4 text-sm
                   md:w-80 md:py-3 md:pl-9 md:text-xs md:focus:w-[380px]"
            :class="[
                dark
                    ? 'bg-transparent !border-white/70 !text-white placeholder:!text-white/60 focus:!border-white'
                    : 'bg-white !border-zinc-300 !text-zinc-900 placeholder:!text-zinc-400 focus:!border-zinc-900'
            ]"
        />
    </form>
</template>