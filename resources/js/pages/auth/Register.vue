<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ref } from 'vue';

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const error = ref('');

const submit = async () => {
    error.value = '';
    try {
        await axios.get('/sanctum/csrf-cookie'); // <- Importante
        await axios.post('/register', form.value);
        window.location.href = '/';
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Error al registrarse';
    }
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto mt-20 max-w-md rounded bg-white p-6 shadow dark:bg-gray-800">
            <h1 class="mb-4 text-2xl font-bold">Registrarse</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Nombre</label>
                    <input v-model="form.name" type="text" class="w-full rounded border p-2" required />
                </div>

                <div>
                    <label>Email</label>
                    <input v-model="form.email" type="email" class="w-full rounded border p-2" required />
                </div>

                <div>
                    <label>Contraseña</label>
                    <input v-model="form.password" type="password" class="w-full rounded border p-2" required />
                </div>

                <div>
                    <label>Confirmar Contraseña</label>
                    <input v-model="form.password_confirmation" type="password" class="w-full rounded border p-2" required />
                </div>

                <div v-if="error" class="font-semibold text-red-600">{{ error }}</div>

                <button type="submit" class="w-full rounded bg-green-600 px-4 py-2 text-white">Registrarse</button>
            </form>
        </div>
    </AppLayout>
</template>
