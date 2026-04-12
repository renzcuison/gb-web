<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Confirm Password" />

        <div class="mb-10 flex flex-col space-y-1 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                CONFIRM PASSWORD
            </h2>
            <p class="text-[10px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-relaxed">
                Secure area access
            </p>
        </div>

        <div class="mb-8 text-[13px] text-zinc-600 font-medium leading-relaxed tracking-tight">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="relative group">
                <input id="password" type="password" v-model="form.password" required autofocus placeholder=" "
                    class="peer block w-full border border-zinc-200 text-zinc-900 focus:border-zinc-900 focus:ring-0 transition-all duration-500 rounded-none px-4 pt-6 pb-2 bg-transparent text-[14px] font-normal tracking-tight leading-snug outline-none shadow-none" />
                <label for="password"
                    class="absolute left-4 top-[0.85rem] text-zinc-400 text-[14px] font-normal tracking-tight pointer-events-none transition-all duration-300 peer-focus:top-1 peer-[:not(:placeholder-shown)]:top-1">
                    Password *
                </label>
                <InputError class="mt-2 text-[10px]" :message="form.errors.password" />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-zinc-100 text-zinc-900 rounded-none font-bold uppercase tracking-[0.4em] text-[10px] shadow-none border-none outline-none transition-none transform-none hover:bg-zinc-100 active:bg-zinc-100"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>