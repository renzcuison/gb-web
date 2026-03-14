<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    inventory: Object
});

const selectedVariant = ref(null);
const showModal = ref(false);

const form = useForm({
    variant_id: '',
    quantity: 1,
    type: 'in',
    reason: ''
});

const openAdjustModal = (variant) => {
    selectedVariant.value = variant;
    form.variant_id = variant.id;
    showModal.value = true;
};

const submit = () => {
    form.post(route('inventory.adjust'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Inventory Management</h1>

        <table class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">Label</th>
                    <th class="px-4 py-2 text-center">Stock</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in inventory.data" :key="item.id" class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-sm">{{ item.sku }}</td>
                    <td class="px-4 py-2">{{ item.product.name }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ item.variant_label }}</td>
                    <td class="px-4 py-2 text-center">
                        <span :class="item.stock_qty <= item.low_stock_threshold ? 'text-red-600 font-bold' : 'text-green-600'">
                            {{ item.stock_qty }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <button @click="openAdjustModal(item)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            Adjust Stock
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg w-96">
                <h2 class="text-lg font-bold mb-4">Adjust Stock: {{ selectedVariant?.sku }}</h2>
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-sm">Adjustment Type</label>
                        <select v-model="form.type" class="w-full border rounded p-2">
                            <option value="in">Add (Stock In)</option>
                            <option value="out">Remove (Stock Out)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm">Quantity</label>
                        <input v-model="form.quantity" type="number" min="1" class="w-full border rounded p-2" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm">Reason (Optional)</label>
                        <input v-model="form.reason" type="text" class="w-full border rounded p-2" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>