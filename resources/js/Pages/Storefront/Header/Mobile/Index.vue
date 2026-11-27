<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import SearchBar from '@/Components/SearchBar.vue';
import Hamburger from '@/Pages/Storefront/Header/Mobile/Hamburger.vue';
import MobileMenuTray from './Tray.vue';
import { ImsChevronDownIcon, ImsUserIcon } from '@/Components/Icons/ImsIcons.vue';

const props = defineProps({
    isOpen: Boolean,
    navItems: Array,
});

const emit = defineEmits(['close']);
const activeDropdown = ref('new-arrivals');
const animateIn = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        animateIn.value = true;
    });
});

const toggleDropdown = (slug) => {
    activeDropdown.value = activeDropdown.value === slug ? null : slug;
};
</script>

<template>
    <div class="fixed inset-0 z-[100] bg-white md:hidden flex flex-col">
        <div class="px-6 shrink-0">
            <div class="flex items-center justify-between h-16">
                <Logo />
                <Hamburger :is-open="true" @toggle="emit('close')" class="-mr-2" />
            </div>
            <div class="pb-4">
                <SearchBar class="w-full" @submit="emit('close')" />
            </div>
        </div>

        <nav class="flex flex-col p-8 space-y-6 overflow-y-auto">
            <div v-for="(item, index) in navItems" :key="item.slug">
                <button @click="toggleDropdown(item.slug)"
                    class="flex items-center justify-between w-full text-sm font-normal tracking-normal text-left transition-all duration-700 ease-out"
                    :style="{
                        transitionDelay: `${index * 60}ms`,
                        transform: animateIn ? 'translateY(0)' : 'translateY(20px)',
                        opacity: animateIn ? 1 : 0
                    }">
                    {{ item.name }}
                    <ImsChevronDownIcon
                        :class="['w-4 h-4 text-zinc-400 transition-transform duration-200', activeDropdown === item.slug ? 'rotate-180' : '']"
                    />
                </button>

                <MobileMenuTray :is-active="activeDropdown === item.slug" :item="item" :is-visible="animateIn"
                    @close="emit('close')" />
            </div>
        </nav>

        <div class="mt-auto p-8 transition-all duration-700 ease-out" :style="{
            transitionDelay: `${(navItems.length + 1) * 60}ms`,
            transform: animateIn ? 'translateY(0)' : 'translateY(20px)',
            opacity: animateIn ? 1 : 0
        }">
            <Link
                :href="$page.props.auth?.user ? route('dashboard') : route('login')"
                @click="emit('close')"
                class="flex items-center space-x-3 text-sm font-normal tracking-normal text-zinc-400 hover:text-black transition-colors group">
                <ImsUserIcon class="w-[18px] h-[18px]" />
                <span>{{ $page.props.auth?.user ? 'Dashboard' : 'Sign in' }}</span>
            </Link>
        </div>
    </div>
</template>