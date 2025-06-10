<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/layouts/GuestLayout.vue'
import { Mail, ArrowLeft } from 'lucide-vue-next'

interface Props {
  status?: string
}

const props = defineProps<Props>()

const form = useForm({
  email: '',
})

const submit = () => {
  form.post('/forgot-password')
}
</script>

<template>
  <GuestLayout>
    <div class="flex items-center justify-center mb-6">
      <Mail size="32" class="text-[#00569D]" />
    </div>
    
    <h2 class="text-xl font-bold text-[#00569D] mb-2 text-center">
      ¿Olvidaste tu contraseña?
    </h2>
    
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 text-center">
      No te preocupes, te enviaremos un enlace para restablecer tu contraseña.
    </p>
    
    <!-- Mensaje de éxito -->
    <div v-if="props.status" class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
      {{ props.status }}
    </div>
    
    <form @submit.prevent="submit" class="space-y-4 text-left">
      <!-- Email -->
      <div>
        <label class="block mb-1 text-sm font-medium">Correo electrónico</label>
        <input 
          v-model="form.email"
          type="email"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
          placeholder="tu@email.com"
          required
        />
        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
          {{ form.errors.email }}
        </div>
      </div>
      
      <!-- Botón -->
      <button 
        type="submit"
        :disabled="form.processing"
        class="w-full bg-[#00569D] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#004a89] transition disabled:opacity-50"
      >
        <span v-if="form.processing">Enviando enlace...</span>
        <span v-else>Enviar enlace de recuperación</span>
      </button>
    </form>
    
    <!-- Volver al login -->
    <div class="mt-6 text-center">
      <Link 
        href="/login" 
        class="inline-flex items-center text-sm text-[#00569D] hover:underline"
      >
        <ArrowLeft size="16" class="mr-1" />
        Volver al inicio de sesión
      </Link>
    </div>
  </GuestLayout>
</template>