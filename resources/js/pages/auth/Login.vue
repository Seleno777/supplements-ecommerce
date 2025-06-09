<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/layouts/GuestLayout.vue'

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
    onError: (errors) => {
      console.error('Login errors:', errors)
    }
  })
}
</script>

<template>
  <GuestLayout>
    <h2 class="text-xl font-bold text-[#00569D] mb-4">Iniciar sesión</h2>

    <form @submit.prevent="submit" class="space-y-4 text-left">
      <!-- Email -->
      <div>
        <label class="block mb-1 text-sm font-medium">Correo electrónico</label>
        <input 
          v-model="form.email"
          type="email"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          required
        />
        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
          {{ form.errors.email }}
        </div>
      </div>

      <!-- Contraseña -->
      <div>
        <label class="block mb-1 text-sm font-medium">Contraseña</label>
        <input 
          v-model="form.password"
          type="password"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          required
        />
        <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">
          {{ form.errors.password }}
        </div>
      </div>

      <!-- Errores generales -->
      <div v-if="form.errors.general" class="font-semibold text-red-600">
        {{ form.errors.general }}
      </div>

      <!-- Botón -->
      <button 
        type="submit"
        :disabled="form.processing"
        class="w-full bg-[#00569D] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#004a89] transition disabled:opacity-50"
      >
        <span v-if="form.processing">Iniciando sesión...</span>
        <span v-else>Iniciar sesión</span>
      </button>
    </form>

    <!-- Registro -->
    <div class="mt-6 text-sm text-gray-600 dark:text-gray-300 text-center">
      ¿No tienes una cuenta?
      <Link href="/register" class="text-[#00569D] hover:underline">Regístrate aquí</Link>
    </div>
  </GuestLayout>
</template>
