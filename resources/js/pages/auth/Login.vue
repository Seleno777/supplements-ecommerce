<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';

const form = ref({ email: '', password: '' });
const error = ref('');

onMounted(async () => {
  await axios.get('/sanctum/csrf-cookie') // <- fuerza regeneración del token al cargar login
})

const submit = async () => {
    error.value = '';
    try {
        await axios.post('/login', form.value);
        window.location.href = '/';
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Credenciales inválidas';
    }
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto mt-20 max-w-md rounded bg-white p-6 shadow dark:bg-gray-800">
            <h1 class="mb-4 text-2xl font-bold">Iniciar sesión</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Email</label>
                    <input v-model="form.email" type="email" class="w-full rounded border p-2" required />
                </div>

                <div>
                    <label>Contraseña</label>
                    <input v-model="form.password" type="password" class="w-full rounded border p-2" required />
                </div>

                <div v-if="error" class="font-semibold text-red-600">{{ error }}</div>

                <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 text-white">Iniciar sesión</button>
            </form>

            <div class="text-muted-foreground mt-4 text-center text-sm">
                ¿No tienes una cuenta?
                <Link href="/register" class="text-blue-600 hover:underline">Regístrate aquí</Link>
            </div>
        </div>
    </AppLayout>
</template>
