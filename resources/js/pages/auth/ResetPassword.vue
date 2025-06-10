<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/layouts/GuestLayout.vue'
import { Lock, Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'

interface Props {
  email: string
  token: string
}

const props = defineProps<Props>()

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/reset-password')
}

const togglePasswordVisibility = (field: 'password' | 'confirmation') => {
  if (field === 'password') {
    showPassword.value = !showPassword.value
  } else {
    showPasswordConfirmation.value = !showPasswordConfirmation.value
  }
}
</script>

<template>
  <GuestLayout>
    <div class="flex items-center justify-center mb-6">
      <Lock size="32" class="text-[#00569D]" />
    </div>
    
    <h2 class="text-xl font-bold text-[#00569D] mb-2 text-center">
      Restablecer contraseña
    </h2>
    
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 text-center">
      Ingresa tu nueva contraseña para {{ props.email }}
    </p>
    
    <form @submit.prevent="submit" class="space-y-4 text-left">
      <!-- Nueva contraseña -->
      <div>
        <label class="block mb-1 text-sm font-medium">Nueva contraseña</label>
        <div class="relative">
          <input 
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
            placeholder="Mínimo 8 caracteres"
            required
          />
          <button
            type="button"
            @click="togglePasswordVisibility('password')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showPassword" size="20" />
            <EyeOff v-else size="20" />
          </button>
        </div>
        <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">
          {{ form.errors.password }}
        </div>
      </div>
      
      <!-- Confirmar contraseña -->
      <div>
        <label class="block mb-1 text-sm font-medium">Confirmar contraseña</label>
        <div class="relative">
          <input 
            v-model="form.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
            placeholder="Repite tu contraseña"
            required
          />
          <button
            type="button"
            @click="togglePasswordVisibility('confirmation')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showPasswordConfirmation" size="20" />
            <EyeOff v-else size="20" />
          </button>
        </div>
        <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">
          {{ form.errors.password_confirmation }}
        </div>
      </div>
      
      <!-- Errores generales -->
      <div v-if="form.errors.email" class="text-sm text-red-600">
        {{ form.errors.email }}
      </div>
      
      <!-- Botón -->
      <button 
        type="submit"
        :disabled="form.processing"
        class="w-full bg-[#00569D] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#004a89] transition disabled:opacity-50"
      >
        <span v-if="form.processing">Restableciendo contraseña...</span>
        <span v-else>Restablecer contraseña</span>
      </button>
    </form>
    
    <!-- Información de seguridad -->
    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
      <p class="text-sm text-blue-700">
        <strong>Requisitos de contraseña:</strong>
        <br>• Mínimo 8 caracteres
        <br>• Se recomienda usar mayúsculas, minúsculas y números
      </p>
    </div>
  </GuestLayout>
</template>