<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from '@/lib/axios';
import { ref } from 'vue';

const form = ref({ email: '', password: '' });
const error = ref('');

const submit = async () => {
    error.value = '';

    try {
        // 🔁 Refresca el token antes del login
        await axios.get('/sanctum/csrf-cookie', {withCredentials:true});

        // Envia el login
        await axios.post('/login', form.value, {withCredentials:true});

        // Redirige
        window.location.href = '/';
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Credenciales inválidas';
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-md p-6 mx-auto mt-20 bg-white rounded shadow dark:bg-gray-800">
            <h1 class="mb-4 text-2xl font-bold">Iniciar sesión</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Email</label>
                    <input v-model="form.email" type="email" class="w-full p-2 border rounded" required />
                </div>

                <div>
                    <label>Contraseña</label>
                    <input v-model="form.password" type="password" class="w-full p-2 border rounded" required />
                </div>

                <div v-if="error" class="font-semibold text-red-600">{{ error }}</div>

                <button type="submit" class="w-full px-4 py-2 text-white bg-blue-600 rounded">Iniciar sesión</button>
            </form>

            <div class="mt-4 text-sm text-center text-muted-foreground">
                ¿No tienes una cuenta?
                <Link href="/register" class="text-blue-600 hover:underline">Regístrate aquí</Link>
            </div>
        </div>
    </AppLayout>
</template>
