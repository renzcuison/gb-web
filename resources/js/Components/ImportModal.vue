<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import {
    ImsUploadIcon,
    ImsSpreadsheetIcon,
    ImsStatusDangerIcon,
    ImsLoaderIcon,
} from '@/Components/Icons/ImsIcons.vue'
import { Button } from '@/Components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog'

const props = defineProps({
    open: Boolean,
})

const emit = defineEmits(['update:open'])

const fileInput = ref(null)
const dragActive = ref(false)
const fileError = ref('')

const form = useForm({
    file: null,
    notes: '',
})

const selectedFileName = computed(() => form.file?.name || null)
const selectedFileSize = computed(() => {
    if (!form.file) return null
    const bytes = form.file.size
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`
})

const allowedTypes = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'text/csv',
]

function validateAndSetFile(file) {
    fileError.value = ''
    if (!file) return

    const ext = file.name.split('.').pop().toLowerCase()
    if (!['xlsx', 'xls', 'csv'].includes(ext) && !allowedTypes.includes(file.type)) {
        fileError.value = 'Please select an Excel (.xlsx, .xls) or CSV (.csv) file.'
        return
    }

    if (file.size > 10 * 1024 * 1024) {
        fileError.value = 'File exceeds the 10 MB maximum size.'
        return
    }

    form.file = file
}

function onFileChange(event) {
    validateAndSetFile(event.target.files?.[0])
}

function onDrop(event) {
    dragActive.value = false
    validateAndSetFile(event.dataTransfer?.files?.[0])
}

function clearFile() {
    form.file = null
    fileError.value = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

function submit() {
    if (!form.file) {
        fileError.value = 'Please choose a spreadsheet to import.'
        return
    }

    form.post(route('inventory.import'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            clearFile()
            form.reset()
            emit('update:open', false)
            router.reload({ preserveScroll: true })
        },
    })
}

function close() {
    if (form.processing) return
    clearFile()
    form.clearErrors()
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Import Inventory</DialogTitle>
                <DialogDescription>
                    Upload an Excel (.xlsx, .xls) or CSV file containing your product catalog and opening stock balances.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div
                    class="relative rounded-lg border-2 border-dashed p-6 text-center transition-colors cursor-pointer"
                    :class="[
                        dragActive
                            ? 'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-900'
                            : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700',
                    ]"
                    @click="fileInput?.click()"
                    @dragover.prevent="dragActive = true"
                    @dragleave.prevent="dragActive = false"
                    @drop.prevent="onDrop"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="hidden"
                        @change="onFileChange"
                    />

                    <div v-if="!selectedFileName" class="flex flex-col items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <ImsUploadIcon class="h-5 w-5 text-zinc-600 dark:text-zinc-400" />
                        </div>
                        <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            Click to upload or drag and drop
                        </div>
                        <p class="text-xs text-zinc-500">
                            XLSX, XLS, or CSV up to 10 MB
                        </p>
                    </div>

                    <div v-else class="flex items-center justify-between gap-3 text-left">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                                <ImsSpreadsheetIcon class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ selectedFileName }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ selectedFileSize }}
                                </p>
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click.stop="clearFile"
                            :disabled="form.processing"
                        >
                            Change
                        </Button>
                    </div>
                </div>

                <div
                    v-if="fileError || form.errors.file"
                    class="flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300"
                >
                    <ImsStatusDangerIcon class="h-4 w-4 shrink-0 mt-0.5" />
                    <span>{{ fileError || form.errors.file }}</span>
                </div>

                <div class="space-y-1.5">
                    <label for="import-notes" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                        Import Notes <span class="text-zinc-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="import-notes"
                        v-model="form.notes"
                        rows="2"
                        placeholder="e.g., March restock shipment, Perfume stocktake..."
                        :disabled="form.processing"
                        class="flex min-h-[64px] w-full rounded-md border border-zinc-200 dark:border-zinc-800 bg-transparent px-3 py-2 text-xs text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-400 disabled:cursor-not-allowed disabled:opacity-50"
                    />
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="close"
                        :disabled="form.processing"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="!form.file || form.processing"
                    >
                        <ImsLoaderIcon v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Importing...' : 'Start Import' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>