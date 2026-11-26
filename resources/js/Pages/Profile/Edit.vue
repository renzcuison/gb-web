<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ShieldCheck, KeyRound, Trash2, X, MailCheck, Camera } from 'lucide-vue-next'

const props = defineProps({
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: null },
})

const page = usePage()
const user = computed(() => page.props.auth?.user || {})

const fileInputRef = ref(null)
const previewUrl = ref(null)

const profileForm = useForm({
    _method: 'patch',
    name: user.value.name || '',
    email: user.value.email || '',
    avatar: null,
    remove_avatar: false,
})

const displayedAvatar = computed(() => {
    if (previewUrl.value) return previewUrl.value
    if (profileForm.remove_avatar) return null
    return user.value.avatar_url || null
})

const userInitials = computed(() => {
    const name = (profileForm.name || user.value.name || 'A').trim()
    return name
        .split(/\s+/)
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
})

const avatarBgColor = computed(() => user.value.resolved_avatar_color || '#4f46e5')

const triggerFileSelect = () => {
    fileInputRef.value?.click()
}

const onFileChange = (event) => {
    const file = event.target.files?.[0]
    if (!file) return

    profileForm.avatar = file
    profileForm.remove_avatar = false
    previewUrl.value = URL.createObjectURL(file)
}

const removePhoto = () => {
    profileForm.avatar = null
    profileForm.remove_avatar = true
    previewUrl.value = null
    if (fileInputRef.value) {
        fileInputRef.value.value = ''
    }
}

const submitProfile = () => {
    profileForm.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            profileForm.avatar = null
            profileForm.remove_avatar = false
            previewUrl.value = null
            if (fileInputRef.value) {
                fileInputRef.value.value = ''
            }
        },
    })
}

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation')
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password')
            }
        },
    })
}

const isDeleteModalOpen = ref(false)
const deleteForm = useForm({
    password: '',
})

const openDeleteModal = () => {
    deleteForm.reset()
    deleteForm.clearErrors()
    isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false
    deleteForm.reset()
    deleteForm.clearErrors()
}

const submitDeleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
        onFinish: () => deleteForm.reset(),
    })
}

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Intl.DateTimeFormat('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(iso))
}
</script>

<template>

    <Head title="Profile Settings" />

    <AdminLayout>
        <div class="w-full space-y-5">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
                    Profile Settings
                </h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                    Manage your personal account details, profile photo, and security credentials.
                </p>
            </div>

            <div
                class="grid grid-cols-2 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200/80 dark:divide-zinc-800 bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3">
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">Role</div>
                    <div class="mt-1 text-xs font-semibold capitalize text-emerald-600 dark:text-emerald-400">
                        {{ user.role || 'Member' }}
                    </div>
                </div>

                <div class="px-4 py-3">
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">Sign-in Method</div>
                    <div class="mt-1 text-xs font-medium capitalize text-slate-900 dark:text-zinc-100">
                        {{ user.provider || 'Email & Password' }}
                    </div>
                </div>

                <div class="px-4 py-3">
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">Verification</div>
                    <div class="mt-1 text-xs font-medium"
                        :class="user.email_verified_at ? 'text-slate-900 dark:text-zinc-100' : 'text-amber-600 dark:text-amber-400'">
                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                    </div>
                </div>

                <div class="px-4 py-3">
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">Member Since</div>
                    <div class="mt-1 font-mono text-xs font-medium text-slate-900 dark:text-zinc-100">
                        {{ formatDate(user.created_at) }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitProfile"
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
                <div
                    class="px-5 py-4 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                            Account Identity
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Update your profile photo, display name, and primary email address.
                        </p>
                    </div>
                    <ShieldCheck class="h-4 w-4 text-slate-400 dark:text-zinc-500" />
                </div>

                <div class="p-5 space-y-5">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="relative group cursor-pointer" @click="triggerFileSelect">
                            <img v-if="displayedAvatar" :src="displayedAvatar" :alt="user.name"
                                class="h-14 w-14 rounded-2xl object-cover border border-slate-200 dark:border-zinc-800" />
                            <div v-else :style="{ backgroundColor: avatarBgColor }"
                                class="h-14 w-14 rounded-2xl text-white font-bold text-base flex items-center justify-center shrink-0 select-none">
                                {{ userInitials }}
                            </div>

                            <div
                                class="absolute inset-0 rounded-2xl bg-slate-950/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                <Camera class="h-4 w-4" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <input ref="fileInputRef" type="file" accept="image/png,image/jpeg,image/webp"
                                    class="hidden" @change="onFileChange" />

                                <button type="button" @click="triggerFileSelect"
                                    class="rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-800/70 transition-colors">
                                    Change Photo
                                </button>

                                <button v-if="displayedAvatar" type="button" @click="removePhoto"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                                    Remove
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500">
                                JPG, PNG, or WebP up to 2MB. Defaults to your initials badge if no photo is set.
                            </p>
                            <p v-if="profileForm.errors.avatar" class="text-[11px] text-rose-600 dark:text-rose-400">
                                {{ profileForm.errors.avatar }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name"
                                class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Full Name
                            </label>
                            <input id="name" v-model="profileForm.name" type="text" required autocomplete="name"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p v-if="profileForm.errors.name" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ profileForm.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label for="email"
                                class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Email Address
                            </label>
                            <input id="email" v-model="profileForm.email" type="email" required autocomplete="username"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p v-if="profileForm.errors.email"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ profileForm.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div v-if="mustVerifyEmail && user.email_verified_at === null"
                        class="rounded-lg bg-amber-500/10 px-3.5 py-2.5 text-xs text-amber-700 dark:text-amber-300 flex items-center justify-between gap-3">
                        <span class="flex items-center gap-2">
                            <MailCheck class="h-4 w-4 shrink-0" />
                            Your email address is unverified.
                        </span>
                        <Link :href="route('verification.send')" method="post" as="button"
                            class="font-semibold underline hover:opacity-80 shrink-0">
                            Resend Link
                        </Link>
                    </div>

                    <div v-if="status === 'verification-link-sent'"
                        class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <div
                    class="px-5 py-3 bg-slate-50/60 dark:bg-zinc-950/40 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-end gap-3">
                    <span v-if="profileForm.recentlySuccessful"
                        class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        Saved.
                    </span>
                    <button type="submit" :disabled="profileForm.processing"
                        class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50">
                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <form @submit.prevent="submitPassword"
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-hidden">
                <div
                    class="px-5 py-4 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                            Password &amp; Security
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Use a strong, unique password to protect administrative access.
                        </p>
                    </div>
                    <KeyRound class="h-4 w-4 text-slate-400 dark:text-zinc-500" />
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="current_password"
                                class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Current Password
                            </label>
                            <input id="current_password" v-model="passwordForm.current_password" type="password"
                                autocomplete="current-password"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p v-if="passwordForm.errors.current_password"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div>
                            <label for="password"
                                class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                New Password
                            </label>
                            <input id="password" v-model="passwordForm.password" type="password"
                                autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p v-if="passwordForm.errors.password"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-xs font-medium text-slate-700 dark:text-zinc-300 mb-1.5">
                                Confirm Password
                            </label>
                            <input id="password_confirmation" v-model="passwordForm.password_confirmation"
                                type="password" autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                            <p v-if="passwordForm.errors.password_confirmation"
                                class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                                {{ passwordForm.errors.password_confirmation }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="px-5 py-3 bg-slate-50/60 dark:bg-zinc-950/40 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-end gap-3">
                    <span v-if="passwordForm.recentlySuccessful"
                        class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        Password updated.
                    </span>
                    <button type="submit" :disabled="passwordForm.processing"
                        class="rounded-lg bg-slate-900 dark:bg-zinc-100 px-4 py-1.5 text-xs font-medium text-white dark:text-zinc-900 hover:bg-slate-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50">
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>
                </div>
            </form>

            <div
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                        Delete Account
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Permanently remove your account and revoke all active sessions. This cannot be undone.
                    </p>
                </div>

                <button type="button" @click="openDeleteModal"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50/60 dark:bg-rose-950/30 px-3.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-100/80 dark:hover:bg-rose-950/60 transition-colors">
                    <Trash2 class="h-3.5 w-3.5" />
                    Delete Account
                </button>
            </div>
        </div>

        <div v-if="isDeleteModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-xs p-4"
            @click.self="closeDeleteModal">
            <div
                class="w-full max-w-md rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
                <div
                    class="flex items-center justify-between border-b border-slate-200/80 dark:border-zinc-800 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                        Confirm Account Deletion
                    </h2>
                    <button type="button" @click="closeDeleteModal"
                        class="rounded-md p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <form @submit.prevent="submitDeleteAccount" class="p-5 space-y-4">
                    <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">
                        Enter your current password to permanently delete <span class="font-mono font-semibold">{{
                            user.email }}</span>.
                    </p>

                    <div>
                        <input v-model="deleteForm.password" type="password" placeholder="Current password" required
                            class="w-full rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-slate-900 dark:text-zinc-100 focus:border-slate-400 dark:focus:border-zinc-600 focus:outline-none" />
                        <p v-if="deleteForm.errors.password" class="mt-1 text-[11px] text-rose-600 dark:text-rose-400">
                            {{ deleteForm.errors.password }}
                        </p>
                    </div>

                    <div
                        class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" @click="closeDeleteModal"
                            class="rounded-lg border border-slate-200 dark:border-zinc-800 px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-800">
                            Cancel
                        </button>
                        <button type="submit" :disabled="deleteForm.processing"
                            class="rounded-lg bg-rose-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-rose-700 transition-colors disabled:opacity-50">
                            {{ deleteForm.processing ? 'Deleting...' : 'Delete Permanently' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>