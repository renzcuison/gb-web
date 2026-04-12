<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Reset Password" />

        <div class="mb-10 flex flex-col space-y-1 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                RESET PASSWORD
            </h2>
            <p class="text-[10px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-relaxed">
                Update your account security
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="relative group opacity-60">
                <input id="email" type="email" v-model="form.email" required readonly placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:ring-0 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none cursor-not-allowed" />
                <label for="email"
                    class="absolute left-4 top-1 text-zinc-400 text-[8px] uppercase tracking-[0.2em] font-bold">
                    Email Address
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.email" />
            </div>

            <div class="relative group">
                <input id="password" type="password" v-model="form.password" required autofocus placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="password"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    New Password *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.password" />
            </div>

            <div class="relative group">
                <input id="password_confirmation" type="password" v-model="form.password_confirmation" required
                    placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="password_confirmation"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Confirm New Password *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-zinc-100 text-zinc-900 rounded-none font-bold uppercase tracking-[0.4em] text-[10px] shadow-none border-none outline-none transition-none transform-none hover:bg-zinc-100 active:bg-zinc-100"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>