<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Upload, FileSpreadsheet, AlertCircle, Loader2 } from 'lucide-vue-next'
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
    open: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:open'])

const fileInput = ref(null)

const form = useForm({
    file: null
})

const handleFileChange = (e) => {
    form.file = e.target.files[0] || null
}

const submitImport = () => {
    if (!form.file) return

    form.post(route('products.import'), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}

const closeModal = () => {
    form.reset()
    form.clearErrors()
    if (fileInput.value) {
        fileInput.value.value = ''
    }
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent
            class="sm:max-w-[480px] dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-100 data-[state=open]:animate-none data-[state=closed]:animate-none duration-0">
            <DialogHeader>
                <DialogTitle class="dark:text-zinc-100 flex items-center gap-2">
                    <FileSpreadsheet class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <span>Import Products</span>
                </DialogTitle>
                <DialogDescription class="dark:text-zinc-400">
                    Upload an Excel spreadsheet (.xlsx, .xls) or CSV file containing your product catalog.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitImport" class="space-y-4 my-2">
                <!-- Dropzone / Selection Box -->
                <div>
                    <label for="excel-file-input"
                        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer bg-slate-50 dark:bg-zinc-800/50 hover:bg-slate-100 dark:hover:bg-zinc-800 border-slate-200 dark:border-zinc-700 transition">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                            <Upload class="w-8 h-8 mb-2 text-slate-400 dark:text-zinc-500" />
                            <p class="text-xs text-slate-600 dark:text-zinc-300">
                                <span v-if="!form.file" class="font-semibold">Click to browse or drag & drop</span>
                                <span v-else class="font-bold text-emerald-600 dark:text-emerald-400">{{ form.file.name
                                }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-1">XLSX, XLS, CSV up to 10MB</p>
                        </div>
                        <input id="excel-file-input" ref="fileInput" type="file" class="hidden" accept=".xlsx,.xls,.csv"
                            @change="handleFileChange" />
                    </label>

                    <!-- Upload Progress Bar -->
                    <div v-if="form.progress" class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 mt-3">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-300"
                            :style="{ width: `${form.progress.percentage}%` }"></div>
                    </div>

                    <!-- General Error Message -->
                    <p v-if="typeof form.errors.file === 'string'"
                        class="text-xs font-medium text-rose-600 dark:text-rose-400 mt-2">
                        {{ form.errors.file }}
                    </p>

                    <!-- Row Validation Failure Summary -->
                    <div v-if="Array.isArray(form.errors.file)"
                        class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/30 rounded-lg border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300 max-h-36 overflow-y-auto space-y-1">
                        <div class="flex items-center gap-1.5 font-bold mb-1">
                            <AlertCircle class="h-3.5 w-3.5" />
                            <span>Import Validation Issues:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                            <li v-for="(err, idx) in form.errors.file" :key="idx">{{ err }}</li>
                        </ul>
                    </div>
                </div>

                <DialogFooter class="pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <Button type="button" variant="ghost" @click="closeModal" :disabled="form.processing"
                        class="dark:text-zinc-400 dark:hover:text-zinc-100">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="!form.file || form.processing"
                        class="gap-2 dark:bg-zinc-100 dark:text-zinc-900">
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <span v-if="form.processing">Importing...</span>
                        <span v-else>Upload & Import</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>