<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ImsGoogleIcon } from '@/Components/Icons/ImsIcons.vue';
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
                        <ImsGoogleIcon class="w-4 h-4" />
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