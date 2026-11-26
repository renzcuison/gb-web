<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Plus, Pencil, Trash2, Search, X } from 'lucide-vue-next'
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
    categories: {
        type: Object,
        default: () => ({ data: [], total: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
})

const search = ref(props.filters?.search || '')
const isModalOpen = ref(false)
const editingCategory = ref(null)

let timer = null
watch(search, (val) => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(
            route('categories.index'),
            { search: val || undefined },
            { preserveState: true, preserveScroll: true, replace: true }
        )
    }, 300)
})

const form = useForm({
    name: '',
    description: '',
})

const openModal = (category = null) => {
    form.clearErrors()
    editingCategory.value = category
    if (category) {
        form.name = category.name
        form.description = category.description || ''
    } else {
        form.reset()
    }
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    editingCategory.value = null
    form.reset()
    form.clearErrors()
}

const handleSubmit = () => {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('categories.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        })
    }
}

const deleteCategory = (category) => {
    if (category.products_count > 0) {
        alert(`Cannot delete ${category.name} because it contains ${category.products_count} product(s).`)
        return
    }

    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
        router.delete(route('categories.destroy', category.id), {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <div class="w-full space-y-5">

        <Head title="Categories" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                    Categories
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Organize products into storefront and catalog groups.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative w-56 sm:w-64">
                    <Search
                        class="h-3.5 w-3.5 text-zinc-400 dark:text-zinc-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <Input v-model="search" type="text" placeholder="Search categories..."
                        class="h-8 pl-8 pr-7 text-xs bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:ring-1 shadow-none" />
                    <button v-if="search" type="button" @click="search = ''"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300">
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>

                <Button size="sm" @click="openModal()"
                    class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 gap-1.5 px-3 shadow-none">
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Category</span>
                </Button>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow
                            class="border-b border-zinc-200 dark:border-zinc-800 hover:bg-transparent bg-zinc-50/60 dark:bg-zinc-900">
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Category Name
                            </TableHead>
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Slug</TableHead>
                            <TableHead class="text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">Products
                            </TableHead>
                            <TableHead class="text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 h-9">
                                Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="category in (categories?.data || [])" :key="category.id"
                            class="border-b border-zinc-100 dark:border-zinc-800/70 hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <TableCell class="py-2.5 text-xs font-medium text-zinc-900 dark:text-zinc-100">
                                {{ category.name }}
                            </TableCell>

                            <TableCell class="py-2.5 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                {{ category.slug }}
                            </TableCell>

                            <TableCell class="py-2.5 font-mono text-xs text-zinc-600 dark:text-zinc-400">
                                {{ category.products_count }}
                            </TableCell>

                            <TableCell class="py-2.5 text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <Button variant="ghost" size="icon" @click="openModal(category)"
                                        class="h-7 w-7 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteCategory(category)"
                                        class="h-7 w-7 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="!categories?.data?.length">
                            <TableCell colspan="4" class="h-28 text-center text-xs text-zinc-400 dark:text-zinc-500">
                                <div v-if="search">
                                    No categories matched "{{ search }}".
                                    <button type="button" @click="search = ''"
                                        class="underline text-zinc-800 dark:text-zinc-300 ml-1">
                                        Clear
                                    </button>
                                </div>
                                <span v-else>No categories found. Click "Add Category" to create one.</span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="(categories?.total ?? 0) > 0"
                class="flex items-center justify-between px-4 py-2.5 border-t border-zinc-200 dark:border-zinc-800 text-[11px] text-zinc-500 dark:text-zinc-400">
                <span class="font-mono">
                    {{ categories.from }}–{{ categories.to }} of {{ categories.total }}
                </span>

                <div v-if="(categories?.last_page ?? 1) > 1" class="flex items-center gap-4">
                    <Link v-if="categories.prev_page_url" :href="categories.prev_page_url" preserve-scroll
                        preserve-state class="hover:text-zinc-900 dark:hover:text-zinc-100">
                        Previous
                    </Link>
                    <span v-else class="opacity-40">Previous</span>

                    <span class="font-mono">{{ categories.current_page }} / {{ categories.last_page }}</span>

                    <Link v-if="categories.next_page_url" :href="categories.next_page_url" preserve-scroll
                        preserve-state class="hover:text-zinc-900 dark:hover:text-zinc-100">
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
                        {{ editingCategory ? 'Edit Category' : 'Create Category' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="space-y-3.5 pt-2">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Category Name <span class="text-rose-500">*</span>
                        </label>
                        <Input v-model="form.name" placeholder="e.g., Perfumes & Mists"
                            class="h-8 text-xs bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100 focus-visible:ring-1 shadow-none" />
                        <p v-if="form.errors.name" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">
                            Description
                        </label>
                        <textarea v-model="form.description" rows="2" placeholder="Optional summary..."
                            class="flex min-h-[64px] w-full rounded-md border border-zinc-200 dark:border-zinc-800 bg-transparent px-2.5 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-400 shadow-none" />
                    </div>

                    <DialogFooter class="pt-3 border-t border-zinc-100 dark:border-zinc-800 gap-2">
                        <Button type="button" variant="ghost" size="sm" @click="closeModal"
                            class="h-8 text-xs font-medium text-zinc-600 dark:text-zinc-400 shadow-none">
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing"
                            class="h-8 text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-none">
                            {{ editingCategory ? 'Save' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>