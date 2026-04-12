<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>

        <Head title="Email Verification" />

        <div class="mb-10 flex flex-col space-y-1 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl uppercase tracking-wide text-zinc-900 font-bold leading-none">
                VERIFY EMAIL
            </h2>
            <p class="text-[10px] uppercase tracking-[0.4em] text-zinc-400 font-bold leading-relaxed">
                Confirm your GreatBuy account
            </p>
        </div>

        <div class="mb-8 text-[13px] text-zinc-600 font-medium leading-relaxed tracking-tight">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link
            we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <div v-if="verificationLinkSent" class="mb-8 text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-6 items-center">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-zinc-100 text-zinc-900 rounded-none font-bold uppercase tracking-[0.4em] text-[10px] shadow-none border-none outline-none transition-none transform-none hover:bg-zinc-100 active:bg-zinc-100"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link :href="route('logout')" method="post" as="button"
                    class="text-[13px] text-zinc-900 font-medium border-b border-black pb-0.5 hover:text-zinc-500 hover:border-zinc-500 transition-colors duration-200">
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>