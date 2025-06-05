<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
    name: '',
    description: '',
    category: '',
    price: 0,
    stock: 0,
});

const loading = ref(false);
const error = ref('');

const submit = async () => {
    loading.value = true;
    error.value = '';

    try {
        await axios.post('/api/products', form.value);
        alert('Producto creado exitosamente ✅');
        router.push('/my-products');
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Error inesperado';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto mt-10 max-w-xl rounded bg-white p-6 shadow dark:bg-gray-800">
            <h1 class="mb-6 text-2xl font-bold">Crear nuevo producto 🏋️‍♂️</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block font-semibold">Nombre</label>
                    <input v-model="form.name" type="text" class="w-full rounded border p-2" />
                </div>

                <div>
                    <label class="mb-1 block font-semibold">Descripción</label>
                    <textarea v-model="form.description" class="w-full rounded border p-2"></textarea>
                </div>

                <div>
                    <label class="mb-1 block font-semibold">Categoría</label>
                    <input v-model="form.category" type="text" class="w-full rounded border p-2" />
                </div>

                <div>
                    <label class="mb-1 block font-semibold">Precio (USD)</label>
                    <input v-model.number="form.price" type="number" class="w-full rounded border p-2" />
                </div>

                <div>
                    <label class="mb-1 block font-semibold">Stock</label>
                    <input v-model.number="form.stock" type="number" class="w-full rounded border p-2" />
                </div>

                <div v-if="error" class="font-semibold text-red-600">{{ error }}</div>

                <button type="submit" :disabled="loading" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">
                    {{ loading ? 'Guardando...' : 'Guardar producto' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
