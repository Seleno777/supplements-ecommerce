<script setup>
import { Dumbbell } from 'lucide-vue-next'
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-md p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md text-center">
      
      <!-- Icono de pesas + nombre -->
      <div class="flex items-center justify-center mb-6 space-x-2">
        <Dumbbell size="32" color="#00569D"/>
        <h1 class="text-2xl font-bold text-[#00569D]">NutriForge</h1>
      </div>

      <!-- Aquí se carga el contenido del login o registro -->
      <slot />
    </div>
  </div>
</template>
