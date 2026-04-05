<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useCarousel } from '@/Composables/useCarousel';

const page = usePage();

const placeholders = [
    { content: "Complementary shipping on all orders over ₱3000 ⛟" },
    { content: "New Arrivals: The '26 Batch order is now live 📢" },
    { content: "Refer a friend and receive ₱500 towards your next purchase 💸" }
];

const announcements = computed(() => {
    const backendData = page.props.announcements;
    return (backendData && backendData.length > 0) ? backendData : placeholders;
});

const { current } = useCarousel(announcements, 5000);
</script>

<template>
    <div class="w-full bg-zinc-100 h-10 overflow-hidden flex items-center justify-center">
        <div class="relative flex items-center justify-center">
            <Transition name="slide-up" mode="out-in">
                <p :key="current" class="text-xs font-normal tracking-normal text-center px-6">
                    {{ announcements[current].content }}
                </p>
            </Transition>
        </div>
    </div>
</template>