<script setup lang="ts">
import GuestLayout from '@/layouts/GuestLayout.vue';
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
    await axios.get('/sanctum/csrf-cookie'); // Necesario si no has iniciado sesión
    await axios.post('/register', form.value);
    window.location.href = '/';
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Error al registrarse';
  }
};
</script>

<template>
  <GuestLayout>
    <h2 class="text-xl font-bold text-[#00569D] mb-4">Crea tu cuenta</h2>

    <form @submit.prevent="submit" class="space-y-4 text-left">
      <!-- Nombre -->
      <div>
        <label class="block mb-1 text-sm font-medium">Nombre</label>
        <input 
          v-model="form.name" 
          type="text" 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          required
        />
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-1 text-sm font-medium">Correo electrónico</label>
        <input 
          v-model="form.email" 
          type="email" 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          required
        />
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
      </div>

      <!-- Confirmación -->
      <div>
        <label class="block mb-1 text-sm font-medium">Confirmar contraseña</label>
        <input 
          v-model="form.password_confirmation" 
          type="password" 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          required
        />
      </div>

      <!-- Errores -->
      <div v-if="error" class="text-red-600 font-medium text-sm">
        {{ error }}
      </div>

      <!-- Botón -->
      <button 
        type="submit" 
        class="w-full bg-[#00569D] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#004a89] transition"
      >
        Registrarse
      </button>
    </form>

    <!-- Ir al login -->
    <div class="mt-6 text-sm text-gray-600 dark:text-gray-300 text-center">
      ¿Ya tienes una cuenta?
      <a href="/login" class="text-[#00569D] hover:underline">Inicia sesión</a>
    </div>
  </GuestLayout>
</template>
