<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

// Usar useForm de Inertia en lugar de axios manual
const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            console.error('Login errors:', errors);
        }
    });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-md p-6 mx-auto mt-20 bg-white rounded shadow dark:bg-gray-800">
            <h1 class="mb-4 text-2xl font-bold">Iniciar sesión</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label>Email</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        class="w-full p-2 border rounded" 
                        required 
                    />
                    <div v-if="form.errors.email" class="text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label>Contraseña</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        class="w-full p-2 border rounded" 
                        required 
                    />
                    <div v-if="form.errors.password" class="text-sm text-red-600">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div v-if="form.errors.general" class="font-semibold text-red-600">
                    {{ form.errors.general }}
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full px-4 py-2 text-white bg-blue-600 rounded disabled:opacity-50"
                >
                    <span v-if="form.processing">Iniciando sesión...</span>
                    <span v-else>Iniciar sesión</span>
                </button>
            </form>

            <div class="mt-4 text-sm text-center text-muted-foreground">
                ¿No tienes una cuenta?
                <Link href="/register" class="text-blue-600 hover:underline">Regístrate aquí</Link>
            </div>
        </div>
    </AppLayout>
</template>