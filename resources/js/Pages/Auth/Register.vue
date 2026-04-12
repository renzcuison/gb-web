<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Register" />

        <div class="mb-10 flex flex-col space-y-1 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                CREATE ACCOUNT
            </h2>
            <p class="text-[10px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-relaxed">
                Join GreatBuy Originals
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="relative group">
                <input id="name" type="text" v-model="form.name" required autofocus placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="name"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Full Name *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.name" />
            </div>

            <div class="relative group">
                <input id="email" type="email" v-model="form.email" required placeholder=" "
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

            <div class="relative group">
                <input id="password_confirmation" type="password" v-model="form.password_confirmation" required
                    placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="password_confirmation"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Confirm Password *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-zinc-100 text-zinc-900 rounded-none font-bold uppercase tracking-[0.4em] text-[10px] shadow-none border-none outline-none transition-none transform-none hover:bg-zinc-100 active:bg-zinc-100"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>

            <div class="text-center pt-10 border-t border-zinc-100">
                <p class="text-[10px] text-zinc-400 uppercase tracking-[0.4em] font-bold mb-4">
                    Already have an account?
                </p>
                <Link :href="route('login')"
                    class="inline-block text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 hover:text-zinc-500 hover:border-zinc-500 transition-colors duration-200">
                    Log In
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>