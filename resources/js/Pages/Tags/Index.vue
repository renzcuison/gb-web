<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import InventoryNavTabs from '@/Components/InventoryNavTabs.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Plus, Pencil, Trash2, Tag } from 'lucide-vue-next'
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
    '#64748b', // Slate
    '#ef4444', // Rose/Red
    '#f97316', // Orange
    '#eab308', // Amber
    '#10b981', // Emerald
    '#06b6d4', // Cyan
    '#3b82f6', // Blue
    '#8b5cf6', // Violet
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
    <div class="w-full h-[calc(100vh-4rem)] overflow-y-auto pb-8 pr-1 space-y-4">

        <Head title="Tag Management" />

        <div class="sticky top-0 z-20 bg-slate-50 dark:bg-zinc-950 -mt-2 pt-2 pb-1">
            <InventoryNavTabs />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1 pb-1">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-zinc-100">
                    Tags
                </h1>
            </div>

            <div class="flex items-center gap-2.5">
                <Button size="sm" @click="openModal()"
                    class="h-8 text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 gap-1.5 px-3 shadow-none">
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Tag</span>
                </Button>
            </div>
        </div>

        <div
            class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/80 dark:border-zinc-800 overflow-x-auto shadow-none">
            <Table>
                <TableHeader class="bg-slate-50/50 dark:bg-zinc-900/50">
                    <TableRow class="border-b border-slate-200/80 dark:border-zinc-800 hover:bg-transparent">
                        <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-10">Tag</TableHead>
                        <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-10">Slug</TableHead>
                        <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-10">Color</TableHead>
                        <TableHead class="text-xs font-medium text-slate-500 dark:text-zinc-400 h-10">Assigned Products</TableHead>
                        <TableHead class="text-right text-xs font-medium text-slate-500 dark:text-zinc-400 h-10">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="tag in tags.data" :key="tag.id"
                        class="border-b border-slate-100 dark:border-zinc-800/60 hover:bg-slate-50/50 dark:hover:bg-zinc-800/40 transition-colors">

                        <TableCell class="py-3">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                :style="{
                                    backgroundColor: `${tag.color_code || '#64748b'}15`,
                                    borderColor: `${tag.color_code || '#64748b'}40`,
                                    color: tag.color_code || '#64748b'
                                }"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full shrink-0"
                                    :style="{ backgroundColor: tag.color_code || '#64748b' }"
                                />
                                {{ tag.name }}
                            </span>
                        </TableCell>

                        <TableCell class="py-3 font-mono text-xs text-slate-500 dark:text-zinc-400">
                            {{ tag.slug }}
                        </TableCell>

                        <TableCell class="py-3 font-mono text-xs text-slate-500 dark:text-zinc-400 uppercase">
                            {{ tag.color_code || '#64748B' }}
                        </TableCell>

                        <TableCell class="py-3">
                            <span
                                class="inline-flex items-center text-[10px] font-medium text-slate-600 dark:text-zinc-400 bg-slate-100 dark:bg-zinc-800/80 px-2 py-0.5 rounded-md">
                                {{ tag.products_count }} {{ tag.products_count === 1 ? 'product' : 'products' }}
                            </span>
                        </TableCell>

                        <TableCell class="py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button variant="ghost" size="icon" @click="openModal(tag)"
                                    class="h-7 w-7 text-slate-500 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="deleteTag(tag)"
                                    class="h-7 w-7 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="!tags.data || tags.data.length === 0">
                        <TableCell colspan="5"
                            class="h-32 text-center text-xs font-normal text-slate-400 dark:text-zinc-500">
                            No tags found. Click "Add Tag" to create one.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Dialog :open="isModalOpen" @update:open="closeModal">
            <DialogContent
                class="sm:max-w-[400px] p-5 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-100 shadow-none">
                <DialogHeader class="pb-2 border-b border-slate-100 dark:border-zinc-800/80">
                    <DialogTitle class="text-sm font-semibold text-slate-900 dark:text-zinc-100">
                        {{ editingTag ? 'Edit Tag' : 'Create Tag' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="space-y-4 pt-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1.5">
                            Tag Name <span class="text-rose-500">*</span>
                        </label>
                        <Input v-model="form.name" placeholder="e.g., Bestseller"
                            class="h-8 text-xs dark:bg-zinc-800/60 dark:border-zinc-700/80 dark:text-zinc-100 focus-visible:ring-1 shadow-none" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-zinc-400 mb-1.5">
                            Badge Color
                        </label>
                        <div class="flex items-center gap-1.5 mb-2">
                            <button
                                v-for="color in presetColors"
                                :key="color"
                                type="button"
                                @click="form.color_code = color"
                                class="h-5 w-5 rounded-full border transition-transform hover:scale-110"
                                :class="form.color_code?.toLowerCase() === color ? 'ring-2 ring-offset-2 ring-slate-900 dark:ring-zinc-100 dark:ring-offset-zinc-900 border-transparent' : 'border-black/10'"
                                :style="{ backgroundColor: color }"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="color"
                                v-model="form.color_code"
                                class="h-8 w-10 cursor-pointer rounded border border-slate-200 dark:border-zinc-700 bg-transparent p-0.5"
                            />
                            <Input
                                v-model="form.color_code"
                                maxlength="7"
                                placeholder="#3b82f6"
                                class="h-8 font-mono text-xs uppercase dark:bg-zinc-800/60 dark:border-zinc-700/80 dark:text-zinc-100 shadow-none"
                            />
                        </div>
                        <p v-if="form.errors.color_code" class="text-xs text-rose-500 dark:text-rose-400 mt-1">
                            {{ form.errors.color_code }}
                        </p>
                    </div>

                    <DialogFooter class="pt-3 border-t border-slate-100 dark:border-zinc-800/80 gap-2">
                        <Button type="button" variant="ghost" size="sm" @click="closeModal"
                            class="h-8 text-xs font-medium text-slate-600 dark:text-zinc-400 dark:hover:text-zinc-100 shadow-none">
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing"
                            class="h-8 text-xs font-medium bg-slate-900 hover:bg-slate-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-none">
                            {{ editingTag ? 'Save Changes' : 'Create Tag' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </div>
</template>