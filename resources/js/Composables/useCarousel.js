import { ref, onMounted, onUnmounted } from 'vue';

export function useCarousel(items, interval = 5000) {
    const current = ref(0);
    let timer = null;

    const start = () => {
        if (items.value.length <= 1) return;
        timer = setInterval(() => {
            current.value = (current.value + 1) % items.value.length;
        }, interval);
    };

    onMounted(start);
    onUnmounted(() => clearInterval(timer));

    return { current };
}