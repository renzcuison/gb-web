<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/Components/ui/dialog'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    tags: Object
})

const presetColors = [
    '#64748b',
    '#ef4444',
    '#f97316',
    '#eab308',
    '#10b981',
    '#06b6d4',
    '#3b82f6',
    '#8b5cf6',
]

const isModalOpen = ref(false)
const editingTag = ref(null)

const form = useForm({
    name: '',
    color_code: '#3b82f6'
})

const openModal = (tag = null) => {
    editingTag.value = tag
    if (tag) {
        form.name = tag.name
        form.color_code = tag.color_code || '#64748b'
    } else {
        form.reset()
        form.color_code = '#3b82f6'
    }
    form.clearErrors()
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    editingTag.value = null
    form.reset()
}

const handleSubmit = () => {
    if (editingTag.value) {
        form.put(route('tags.update', editingTag.value.id), {
            onSuccess: () => closeModal()
        })
    } else {
        form.post(route('tags.store'), {
            onSuccess: () => closeModal()
        })
    }
}

const deleteTag = (tag) => {
    if (tag.products_count > 0) {
        alert(`Cannot delete "${tag.name}" because it is assigned to ${tag.products_count} product(s).`)
        return
    }

    if (confirm(`Are you sure you want to delete the tag "${tag.name}"?`)) {
        form.delete(route('tags.destroy', tag.id))
    }
}
</script>

<template>
    <div class="w-full space-y-5">

        <Head title="Tags" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Tags
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Manage merchandising tags for storefront filtering and highlights.
                </p>
            </div>

            <Button size="sm" @click="openModal()"
                class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 gap-1.5 px-3 shadow-none w-fit">
                <Plus class="h-3.5 w-3.5" />
                <span>Add Tag</span>
            </Button>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow
                            class="border-b border-zinc-200 dark:border-zinc-800 hover:bg-transparent bg-zinc-50/60 dark:bg-zinc-900">
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Tag</TableHead>
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Slug</TableHead>
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Color
                            </TableHead>
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Products
                            </TableHead>
                            <TableHead class="text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">
                                Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tag in tags.data" :key="tag.id"
                            class="border-b border-zinc-100 dark:border-zinc-800/70 hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <TableCell class="py-2.5">
                                <div
                                    class="inline-flex items-center gap-2 text-xs font-medium text-zinc-900 dark:text-zinc-100">
                                    <span class="h-2 w-2 rounded-full shrink-0"
                                        :style="{ backgroundColor: tag.color_code || '#64748b' }" />
                                    <span>{{ tag.name }}</span>
                                </div>
                            </TableCell>

                            <TableCell class="py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                {{ tag.slug }}
                            </TableCell>

                            <TableCell class="py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400 uppercase">
                                {{ tag.color_code || '#64748B' }}
                            </TableCell>

                            <TableCell class="py-2.5 font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                {{ tag.products_count }}
                            </TableCell>

                            <TableCell class="py-2.5 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <Button variant="ghost" size="icon" @click="openModal(tag)"
                                        class="h-7 w-7 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteTag(tag)"
                                        class="h-7 w-7 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="!tags.data || tags.data.length === 0">
                            <TableCell colspan="5" class="h-28 text-center text-xs text-zinc-400 dark:text-zinc-500">
                                No tags found. Click "Add Tag" to create one.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="(tags?.total ?? 0) > 0"
                class="flex items-center justify-between px-4 py-2.5 border-t border-zinc-200 dark:border-zinc-800 text-[11px] text-zinc-500 dark:text-zinc-400">
                <span class="font-mono">
                    {{ tags.from }}–{{ tags.to }} of {{ tags.total }}
                </span>

                <div v-if="(tags?.last_page ?? 1) > 1" class="flex items-center gap-4">
                    <Link v-if="tags.prev_page_url" :href="tags.prev_page_url" preserve-scroll preserve-state
                        class="hover:text-zinc-900 dark:hover:text-zinc-100">
                        Previous
                    </Link>
                    <span v-else class="opacity-40">Previous</span>

                    <span class="font-mono">{{ tags.current_page }} / {{ tags.last_page }}</span>

                    <Link v-if="tags.next_page_url" :href="tags.next_page_url" preserve-scroll preserve-state
                        class="hover:text-zinc-900 dark:hover:text-zinc-100">
                        Next
                    </Link>
                    <span v-else class="opacity-40">Next</span>
                </div>
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="closeModal">
            <DialogContent
                class="sm:max-w-[380px] p-5 bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-none">
                <DialogHeader class="pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <DialogTitle class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ editingTag ? 'Edit Tag' : 'Create Tag' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="space-y-3.5 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Tag Name <span class="text-rose-500">*</span>
                        </label>
                        <Input v-model="form.name" placeholder="e.g., Bestseller"
                            class="h-8 text-xs bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 focus-visible:ring-1 shadow-none" />
                        <p v-if="form.errors.name" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Color
                        </label>
                        <div class="flex items-center gap-1.5 mb-2">
                            <button v-for="color in presetColors" :key="color" type="button"
                                @click="form.color_code = color" class="h-5 w-5 rounded-full border"
                                :class="form.color_code?.toLowerCase() === color ? 'ring-2 ring-offset-2 ring-zinc-900 dark:ring-zinc-100 dark:ring-offset-zinc-900 border-transparent' : 'border-black/10'"
                                :style="{ backgroundColor: color }" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="color" v-model="form.color_code"
                                class="h-8 w-10 cursor-pointer rounded border border-zinc-200 dark:border-zinc-800 bg-transparent p-0.5" />
                            <Input v-model="form.color_code" maxlength="7" placeholder="#3b82f6"
                                class="h-8 font-mono text-xs uppercase bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-none" />
                        </div>
                        <p v-if="form.errors.color_code" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.color_code }}
                        </p>
                    </div>

                    <DialogFooter class="pt-3 border-t border-zinc-100 dark:border-zinc-800 gap-2">
                        <Button type="button" variant="ghost" size="sm" @click="closeModal"
                            class="h-8 text-xs font-medium text-zinc-600 dark:text-zinc-400 shadow-none">
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing"
                            class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-none">
                            {{ editingTag ? 'Save' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>