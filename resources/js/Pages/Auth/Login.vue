<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// Function to redirect to social provider
const socialLogin = (provider) => {
    window.location.href = `/auth/${provider}/redirect`;
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div class="mb-10 flex flex-col space-y-1 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                LOG IN
            </h2>
            <p class="text-[10px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-relaxed">
                Access your GreatBuy account
            </p>
        </div>

        <div v-if="status" class="mb-6 text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="relative group">
                <input id="email" type="email" v-model="form.email" required autofocus placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="email"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Email Address *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.email" />
            </div>

            <div class="relative group">
                <input id="password" type="password" v-model="form.password" required placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="password"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Password *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center group cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember"
                        class="border-zinc-300 text-zinc-900 focus:ring-0 rounded-none w-3.5 h-3.5" />
                    <span
                        class="ms-2.5 text-[13px] text-zinc-900 font-medium border-b border-transparent group-hover:border-black pb-0.5 transition-colors duration-200">
                        Keep me logged in
                    </span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 hover:text-zinc-500 hover:border-zinc-500 transition-colors duration-200">
                    Forgot Password?
                </Link>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-zinc-100 text-zinc-900 rounded-none font-bold uppercase tracking-[0.4em] text-[10px] shadow-none border-none outline-none transition-none transform-none hover:bg-zinc-100 active:bg-zinc-100"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Sign In
                </PrimaryButton>
            </div>

            <div class="pt-6 flex flex-col items-center space-y-4">
                <p class="text-[9px] uppercase tracking-[0.3em] text-zinc-400 font-bold">Or continue with</p>

                <div class="flex space-x-4 w-full">
                    <button @click.prevent="socialLogin('google')" type="button"
                        class="flex-1 flex justify-center items-center py-3 border border-zinc-200 hover:border-zinc-900 transition-colors duration-300 rounded-none bg-white">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="currentColor"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="currentColor"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="currentColor"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                    </button>
                    <button @click.prevent="socialLogin('facebook')" type="button"
                        class="flex-1 flex justify-center items-center py-3 border border-zinc-200 hover:border-zinc-900 transition-colors duration-300 rounded-none bg-white">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="text-center pt-10 border-t border-zinc-100">
                <p class="text-[10px] text-zinc-400 uppercase tracking-[0.4em] font-bold mb-4">
                    New to GreatBuy Originals?
                </p>
                <Link :href="route('register')"
                    class="inline-block text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 hover:text-zinc-500 hover:border-zinc-500 transition-colors duration-200">
                    Create Account
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>