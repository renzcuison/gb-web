<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    isActive: Boolean,
    item: Object,
    isVisible: Boolean
});

const emit = defineEmits(['close']);

const arrivalProducts = [
    { id: 1, name: 'Minimalist Runner', price: '₱8000', image: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=600&auto=format&fit=crop' },
    { id: 2, name: 'Textured Oversized Tee', price: '₱345', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop' },
    { id: 3, name: 'Canvas Tote', price: '₱790', image: 'https://images.unsplash.com/photo-1544816155-12df9643f363?q=80&w=600&auto=format&fit=crop' },
];
</script>

<template>
    <Transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-[1200px]" leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 max-h-[1200px]" leave-to-class="opacity-0 max-h-0">
        <div v-if="isActive" class="mt-6 flex flex-col space-y-6 overflow-hidden">
            <div v-if="item.slug === 'new-arrivals'" class="relative w-screen -ml-8 pb-2">
                <div class="flex overflow-x-auto gap-4 px-8 snap-x snap-mandatory scroll-pl-8 no-scrollbar transition-all duration-1000 ease-out"
                    :style="{
                        transitionDelay: '200ms',
                        transform: isVisible ? 'translateY(0)' : 'translateY(20px)',
                        opacity: isVisible ? 1 : 0
                    }">
                    <div v-for="product in arrivalProducts" :key="product.id"
                        class="flex-none w-[72vw] snap-start flex flex-col space-y-3">
                        <div class="aspect-[16/9] w-full bg-zinc-100 overflow-hidden">
                            <img :src="product.image" :alt="product.name"
                                class="w-full h-full object-cover transition-all duration-500" />
                        </div>
                        <div class="flex flex-col space-y-1">
                            <span class="text-xs font-normal text-black">{{ product.name }}</span>
                            <span class="text-xs text-zinc-400">{{ product.price }}</span>
                        </div>
                    </div>
                    <div class="flex-none w-4"></div>
                </div>
            </div>

            <Link v-for="(child, index) in item.children" :key="child" href="#" @click="emit('close')"
                class="text-sm font-normal text-zinc-400 transition-all duration-1000 ease-out" :style="{
                    transitionDelay: `${(index * 50) + 300}ms`,
                    transform: isVisible ? 'translateY(0)' : 'translateY(15px)',
                    opacity: isVisible ? 1 : 0
                }">
                {{ child }}
            </Link>
        </div>
    </Transition>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>