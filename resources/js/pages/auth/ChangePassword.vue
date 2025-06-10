<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { Lock, Eye, EyeOff, CheckCircle } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const page = usePage()
const status = computed(() => page.props.status as string | undefined)

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put('/password', {
    onSuccess: () => {
      form.reset()
    },
    onError: () => {
      form.reset('current_password')
    }
  })
}

const togglePasswordVisibility = (field: 'current' | 'new' | 'confirm') => {
  switch (field) {
    case 'current':
      showCurrentPassword.value = !showCurrentPassword.value
      break
    case 'new':
      showNewPassword.value = !showNewPassword.value
      break
    case 'confirm':
      showConfirmPassword.value = !showConfirmPassword.value
      break
  }
}
</script>

<template>
  <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex items-center justify-center mb-6">
      <Lock size="32" class="text-[#00569D]" />
    </div>
    
    <h2 class="text-xl font-bold text-[#00569D] mb-2 text-center">
      Cambiar contraseña
    </h2>
    
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 text-center">
      Actualiza tu contraseña para mantener tu cuenta segura
    </p>
    
    <!-- Mensaje de éxito -->
    <div v-if="status" class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm flex items-center">
      <CheckCircle size="16" class="mr-2" />
      {{ status }}
    </div>
    
    <form @submit.prevent="submit" class="space-y-4">
      <!-- Contraseña actual -->
      <div>
        <label class="block mb-1 text-sm font-medium">Contraseña actual</label>
        <div class="relative">
          <input 
            v-model="form.current_password"
            :type="showCurrentPassword ? 'text' : 'password'"
            class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
            placeholder="Ingresa tu contraseña actual"
            required
          />
          <button
            type="button"
            @click="togglePasswordVisibility('current')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showCurrentPassword" size="20" />
            <EyeOff v-else size="20" />
          </button>
        </div>
        <div v-if="form.errors.current_password" class="text-sm text-red-600 mt-1">
          {{ form.errors.current_password }}
        </div>
      </div>
      
      <!-- Nueva contraseña -->
      <div>
        <label class="block mb-1 text-sm font-medium">Nueva contraseña</label>
        <div class="relative">
          <input 
            v-model="form.password"
            :type="showNewPassword ? 'text' : 'password'"
            class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
            placeholder="Mínimo 8 caracteres"
            required
          />
          <button
            type="button"
            @click="togglePasswordVisibility('new')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showNewPassword" size="20" />
            <EyeOff v-else size="20" />
          </button>
        </div>
        <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">
          {{ form.errors.password }}
        </div>
      </div>
      
      <!-- Confirmar nueva contraseña -->
      <div>
        <label class="block mb-1 text-sm font-medium">Confirmar nueva contraseña</label>
        <div class="relative">
          <input 
            v-model="form.password_confirmation"
            :type="showConfirmPassword ? 'text' : 'password'"
            class="w-full px-4 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00569D]"
            placeholder="Repite tu nueva contraseña"
            required
          />
          <button
            type="button"
            @click="togglePasswordVisibility('confirm')"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showConfirmPassword" size="20" />
            <EyeOff v-else size="20" />
          </button>
        </div>
        <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">
          {{ form.errors.password_confirmation }}
        </div>
      </div>
      
      <!-- Botón -->
      <button 
        type="submit"
        :disabled="form.processing"
        class="w-full bg-[#00569D] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#004a89] transition disabled:opacity-50"
      >
        <span v-if="form.processing">Actualizando contraseña...</span>
        <span v-else>Actualizar contraseña</span>
      </button>
    </form>
    
    <!-- Información de seguridad -->
    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
      <p class="text-sm text-blue-700">
        <strong>Consejos de seguridad:</strong>
        <br>• Usa al menos 8 caracteres
        <br>• Combina mayúsculas, minúsculas y números
        <br>• Evita usar información personal
        <br>• No compartas tu contraseña con nadie
      </p>
    </div>
  </div>
</template>